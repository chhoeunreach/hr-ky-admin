<?php

namespace App\Services\SellOut;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MobileReportingService
{
    public function report(Request $request): array|StreamedResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable','date_format:Y-m-d'],
            'date_to' => ['nullable','date_format:Y-m-d','after_or_equal:date_from'],
            'branch_name' => ['nullable','string','max:255'],
            'seller_name' => ['nullable','string','max:255'],
            'search' => ['nullable','string','max:160'],
            'type' => ['nullable','in:all,sell,material,iron,repair,buy_in'],
            'report_status' => ['nullable','in:all,qualified,failed,not_applicable'],
            'commission_mode' => ['nullable','in:with_conditions,without_conditions'],
            'page' => ['nullable','integer','min:1'],
            'per_page' => ['nullable','integer','in:20,25,100,250,500'],
            'bucket' => ['nullable','in:day,week,month'],
            'export' => ['nullable','in:csv'],
        ]);
        $from = Carbon::parse($filters['date_from'] ?? now()->toDateString())->startOfDay();
        $to = Carbon::parse($filters['date_to'] ?? $from->toDateString())->endOfDay();
        abort_if($to->lt($from) || $to->diffInDays($from) > 3660, 422, 'Invalid reporting period.');
        $filters['date_from'] = $from->toDateString();
        $filters['date_to'] = $to->toDateString();
        $owned = DB::table('sell_out_reports')->where('user_id', $request->user()->id);
        $branches = (clone $owned)->whereNotNull('branch_name')->where('branch_name','<>','')->distinct()->orderBy('branch_name')->pluck('branch_name')->values();
        $employees = (clone $owned)->whereNotNull('seller_name')->where('seller_name','<>','')->distinct()->orderBy('seller_name')->pluck('seller_name')->values();
        if (!empty($filters['branch_name'])) abort_unless($branches->contains($filters['branch_name']), 403, 'Branch is outside your report scope.');
        if (!empty($filters['seller_name'])) abort_unless($employees->contains($filters['seller_name']), 403, 'Employee is outside your report scope.');

        $settings = app(CommissionSettingsService::class);
        $policy = $settings->policy($request->user());
        $filters['commission_mode'] = $settings->mode($request->user(),$filters['commission_mode'] ?? null);
        if (!$policy['visible']) $filters['report_status'] = 'all';
        $base = $this->base($request->user()->id, $filters, $from, $to);
        // Build qualification before status and pagination, using the complete filtered period.
        $period = DB::query()->fromSub(clone $base, 's')->select(['user_id','seller_key','category','type'])
            ->selectRaw('SUM('.ReportingRules::periodQuantitySql().') AS period_qty')
            ->groupBy('user_id','seller_key','category','type');
        $conditions = ($filters['commission_mode'] ?? 'with_conditions') === 'with_conditions';
        $eligible = ReportingRules::eligibleSql($conditions);
        $commission = ReportingRules::rateSql();
        $enabled = ReportingRules::enabledSql();
        $statusSql = $policy['can_calculate'] ? "CASE WHEN $eligible THEN 'qualified' WHEN $enabled THEN 'failed' ELSE 'not_applicable' END"
            : "CASE WHEN d.commission > 0 THEN 'qualified' WHEN $enabled THEN 'failed' ELSE 'not_applicable' END";
        $commissionSql = $policy['can_calculate'] ? "CASE WHEN $eligible THEN ($commission) ELSE 0 END" : 'd.commission';
        $evaluated = DB::query()->fromSub($base, 'd')->leftJoinSub($period, 'p', function ($join) {
            $join->on('d.user_id','=','p.user_id')->on('d.seller_key','=','p.seller_key')->on('d.category','=','p.category')->on('d.type','=','p.type');
        })->select('d.*')->selectRaw('COALESCE(p.period_qty,0) AS period_qty')
            ->selectRaw("$statusSql AS report_status")
            ->selectRaw("ROUND($commissionSql,2) AS report_commission");
        $query = DB::query()->fromSub($evaluated, 't');
        if (($filters['report_status'] ?? 'all') !== 'all') $query->where('report_status', $filters['report_status']);
        if (($filters['export'] ?? '') === 'csv') return $this->export($query,$policy['visible']);

        $categories = (clone $query)->select('type')->selectRaw('COUNT(*) AS transactions, COALESCE(SUM(total_qty),0) AS quantity, COALESCE(SUM(total_amount),0) AS amount, COALESCE(SUM(report_commission),0) AS commission')
            ->groupBy('type')->get()->keyBy('type');
        $stats = (clone $query)->selectRaw("COUNT(*) AS total_transactions,
            COALESCE(SUM(CASE WHEN type = 'sell' THEN 1 ELSE 0 END),0) AS reports,
            COALESCE(SUM(CASE WHEN type = 'sell' THEN total_qty ELSE 0 END),0) AS quantity,
            COALESCE(SUM(CASE WHEN type = 'sell' AND report_status = 'qualified' THEN 1 ELSE 0 END),0) AS qualified,
            COALESCE(SUM(CASE WHEN type = 'sell' AND report_status = 'failed' THEN 1 ELSE 0 END),0) AS failed,
            COALESCE(SUM(CASE WHEN type IN ('sell','material','iron','repair') THEN total_amount ELSE 0 END),0) AS sales_amount,
            COALESCE(SUM(report_commission),0) AS commission,
            COALESCE(SUM(total_qty),0) AS module_quantity,
            COALESCE(SUM(total_amount),0) AS module_amount,
            COALESCE(SUM(CASE WHEN report_status = 'qualified' THEN 1 ELSE 0 END),0) AS module_qualified,
            COALESCE(SUM(CASE WHEN report_status = 'failed' THEN 1 ELSE 0 END),0) AS module_failed")->first();
        $summary = (array) $stats;
        foreach ($summary as $key => $value) $summary[$key] = in_array($key, ['sales_amount','commission','module_amount']) ? round((float)$value, 2) : (int)$value;
        foreach (['material','iron','repair','buy_in'] as $type) $summary[$type.'_amount'] = round((float)($categories[$type]->amount ?? 0),2);
        if (!$policy['visible']) foreach (['commission','qualified','failed','module_qualified','module_failed'] as $key) unset($summary[$key]);
        $summary['gross_profit'] = null;
        $summary['gross_profit_available'] = false;
        $size = (int)($filters['per_page'] ?? 20);
        $total = $summary['total_transactions'];
        $page = min((int)($filters['page'] ?? 1), max(1, (int)ceil($total / $size)));
        $rows = (clone $query)->orderByDesc('created_at')->orderByDesc('id')->forPage($page,$size)->get();
        // The summary already counted the same filtered query; do not issue a second count.
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows,$total,$size,$page);
        return [
            'status' => true,
            'commission_policy' => $policy,
            'data' => array_map(fn ($r) => $this->transaction($r,$policy['visible']), $paginator->items()),
            'meta' => ['current_page' => $paginator->currentPage(), 'per_page' => $size, 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
            'summary' => $summary,
            'categories' => collect(['sell','material','iron','repair','buy_in'])->map(fn ($type) => [
                'type' => $type, 'transactions' => (int)($categories[$type]->transactions ?? 0),
                'quantity' => (int)($categories[$type]->quantity ?? 0), 'amount' => round((float)($categories[$type]->amount ?? 0),2),
                ...($policy['visible'] ? ['commission'=>round((float)($categories[$type]->commission ?? 0),2)] : []),
                'is_purchase' => $type === 'buy_in',
            ])->all(),
            'chart' => $this->chart($query, $from, $to, $filters),
            'filters' => $filters,
            'options' => ['branches' => $branches, 'employees' => $employees, 'types' => ['sell','material','iron','repair','buy_in'], 'statuses' => ['qualified','failed','not_applicable']],
            // Same own-record scope and create entitlement as the existing authenticated API.
            'permissions' => ['scope' => 'own_reports', 'can_create' => true, 'modules' => ['sell','material','iron','repair','buy_in']],
            'capabilities' => ['gross_profit' => false, 'operational_status' => false, 'payment_status' => false, 'repair_orders' => false],
            'currency_symbol' => (\Illuminate\Support\Facades\Cache::get('payment_currency_symbol') ?? \App\Models\PaymentCurrency::query()->value('symbol') ?? ''),
            'business_name' => $filters['branch_name'] ?? $request->user()->branch?->name ?? 'KNEAYERNG',
        ];
    }

    private function base(int $userId, array $f, Carbon $from, Carbon $to): Builder
    {
        $qty = DB::table('sell_out_report_lines as qty_lines')
            ->join('sell_out_reports as qty_reports','qty_reports.id','=','qty_lines.sell_out_report_id')
            ->where('qty_reports.user_id',$userId)->whereBetween('qty_reports.created_at',[$from,$to])
            ->select('qty_lines.sell_out_report_id')->selectRaw('SUM(qty_lines.qty) AS total_qty')->groupBy('qty_lines.sell_out_report_id');
        $length = DB::getDriverName() === 'sqlite' ? 'LENGTH' : 'CHAR_LENGTH';
        $q = DB::table('sell_out_reports as r')->leftJoinSub($qty,'l','l.sell_out_report_id','=','r.id')
            ->leftJoin('users as u','u.id','=','r.user_id')->leftJoin('office_times as o','o.id','=','u.office_time_id')
            ->where('r.user_id',$userId)->whereBetween('r.created_at',[$from,$to])
            ->select(['r.id','r.user_id','r.invoice_no','r.original_invoice_no','r.seller_name','r.branch_name','r.customer_name','r.customer_phone','r.service_type','r.payment_method','r.total_amount','r.commission','r.created_at'])
            ->selectRaw(ReportingRules::typeSql('r.service_type').' AS type')
            ->selectRaw("COALESCE(l.total_qty,0) AS total_qty, LOWER(TRIM(COALESCE(r.seller_name,''))) AS seller_key, ".ReportingRules::normalizedSql('o.category')." AS category, CASE WHEN $length(TRIM(COALESCE(r.customer_phone,''))) > 6 THEN 1 ELSE 0 END AS phone_valid");
        if (!empty($f['branch_name'])) $q->where('r.branch_name',$f['branch_name']);
        if (!empty($f['seller_name'])) $q->where('r.seller_name',$f['seller_name']);
        if (($f['type'] ?? 'all') !== 'all') $q->whereRaw('('.ReportingRules::typeSql('r.service_type').') = ?',[$f['type']]);
        else $q->whereRaw('('.ReportingRules::typeSql('r.service_type').") IN ('sell','material','iron','repair','buy_in')");
        if (!empty($f['search'])) {
            $term = '%'.trim($f['search']).'%';
            $q->where(function ($search) use ($term) {
                foreach (['invoice_no','original_invoice_no','customer_name','customer_phone','seller_name'] as $field) $search->orWhere('r.'.$field,'like',$term);
                $search->orWhereExists(function ($lines) use ($term) {
                    $lines->selectRaw('1')->from('sell_out_report_lines as search_lines')->whereColumn('search_lines.sell_out_report_id','r.id')
                        ->where(function ($fields) use ($term) { foreach (['product_name','sku','imei','serial_number'] as $field) $fields->orWhere('search_lines.'.$field,'like',$term); });
                });
            });
        }
        return $q;
    }

    private function chart(Builder $q, Carbon $from, Carbon $to, array $f): array
    {
        // Aggregate by day in SQL, then bucket at most 3661 scalar rows, never invoices.
        $quantity = ($f['type'] ?? 'all') === 'all' ? "CASE WHEN type = 'sell' THEN total_qty ELSE 0 END" : 'total_qty';
        $days = (clone $q)->selectRaw("DATE(created_at) AS day, SUM(CASE WHEN type IN ('sell','material','iron','repair') THEN total_amount ELSE 0 END) AS amount, SUM(CASE WHEN type = 'buy_in' THEN total_amount ELSE 0 END) AS buy_in_amount, SUM($quantity) AS quantity")->groupByRaw('DATE(created_at)')->get()->keyBy('day');
        $bucket = $f['bucket'] ?? ($from->diffInDays($to) > 90 ? 'month' : ($from->diffInDays($to) > 31 ? 'week' : 'day'));
        $points = [];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $key = match($bucket) { 'month' => $day->copy()->startOfMonth()->toDateString(), 'week' => $day->copy()->startOfWeek()->toDateString(), default => $day->toDateString() };
            $points[$key] ??= ['date' => $key, 'amount' => 0.0, 'buy_in_amount' => 0.0, 'quantity' => 0];
            $row = $days[$day->toDateString()] ?? null;
            $points[$key]['amount'] += (float)($row->amount ?? 0);
            $points[$key]['buy_in_amount'] += (float)($row->buy_in_amount ?? 0);
            $points[$key]['quantity'] += (int)($row->quantity ?? 0);
        }
        foreach ($points as &$point) {
            $point['amount'] = round($point['amount'],2);
            $point['buy_in_amount'] = round($point['buy_in_amount'],2);
        }
        unset($point);
        return ['bucket' => $bucket, 'points' => array_values($points)];
    }

    private function transaction(object $r, bool $visible): array
    {
        $data = [
            'id' => (int)$r->id, 'invoice_no' => $r->invoice_no ?? '', 'original_invoice_no' => $r->original_invoice_no ?? '',
            'customer_name' => $r->customer_name ?? '', 'customer_phone' => $r->customer_phone ?? '',
            'seller_name' => $r->seller_name, 'branch_name' => $r->branch_name ?? '', 'type' => $r->type,
            'service_type' => $r->service_type ?? 'Sell', 'total_qty' => (int)$r->total_qty,
            'total_amount' => round((float)$r->total_amount,2), 'report_commission' => round((float)$r->report_commission,2),
            'report_status' => $r->report_status, 'period_qty' => (int)$r->period_qty,
            'created_at' => $r->created_at, 'payment_method' => $r->payment_method ?? '',
            'operational_status' => null, 'payment_status' => null,
            'can_edit' => Carbon::parse($r->created_at)->isToday(),
        ];
        if (!$visible) foreach (['report_commission','report_status','period_qty'] as $key) unset($data[$key]);
        return $data;
    }

    private function export(Builder $query, bool $visible): StreamedResponse
    {
        return response()->streamDownload(function () use ($query,$visible) {
            $out = fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['Reference','Type','Customer','Phone','Qty','Amount',...($visible ? ['Report commission','Commission status'] : []),'Date','Branch','Employee']);
            foreach ((clone $query)->orderByDesc('created_at')->orderByDesc('id')->cursor() as $r) {
                $cells = [$r->invoice_no,$r->type,$r->customer_name,$r->customer_phone,$r->total_qty,$r->total_amount,...($visible ? [$r->report_commission,$r->report_status] : []),$r->created_at,$r->branch_name,$r->seller_name];
                fputcsv($out,array_map(fn ($v) => preg_match('/^[=+@\-\t\r]/',(string)$v) ? "'".$v : $v,$cells));
            }
            fclose($out);
        },'kneayerng-report.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
