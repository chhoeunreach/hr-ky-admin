<?php

namespace App\Http\Controllers\Web;

use App\Exports\SellStaffReportExport;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\SellOutReport;
use App\Models\TelegramGroup;
use App\Services\TelegramService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SellStaffReportController extends Controller
{
    use CustomAuthorizesRequests;

    private string $view = 'admin.sellStaffReport.';

    public function __construct(private TelegramService $telegramService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('view_sell_staff_report');

        $filterData = $this->filterData($request);
        $query = $this->reportQuery($filterData);

        if ($request->boolean('download_excel')) {
            $reports = $query->get();

            return Excel::download(
                new SellStaffReportExport($reports),
                'sell-staff-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $reports = $query->paginate(25)->withQueryString();
        $summary = $this->summaryQuery($filterData)->first();
        $staffSummary = $this->staffSummaryQuery($filterData)->get();
        $branches = Branch::query()
            ->where('company_id', app(\App\Helpers\AppHelper::class)::getAuthUserCompanyId())
            ->where('is_active', Branch::IS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name']);

        $sellTypes = SellOutReport::query()
            ->whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type');

        return view($this->view . 'index', compact(
            'reports', 'summary', 'staffSummary', 'filterData',
            'branches', 'sellTypes'
        ));
    }

    public function create()
    {
        $this->authorize('view_sell_staff_report');

        return view($this->view . 'create');
    }

    public function store(Request $request)
    {
        $this->authorize('view_sell_staff_report');

        $validated = $request->validate([
            'original_invoice_no' => ['nullable', 'string', 'max:255'],
            'seller_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'service_type' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_name' => ['nullable', 'string', 'max:255'],
            'lines.*.sku' => ['nullable', 'string', 'max:255'],
            'lines.*.imei' => ['nullable', 'string', 'max:255'],
            'lines.*.imei2' => ['nullable', 'string', 'max:255'],
            'lines.*.serial_number' => ['nullable', 'string', 'max:255'],
            'lines.*.model_number' => ['nullable', 'string', 'max:255'],
            'lines.*.color' => ['nullable', 'string', 'max:255'],
            'lines.*.storage' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'numeric', 'min:1'],
            'lines.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
        ]);

        $lines = collect($validated['lines'])
            ->map(fn (array $line) => $this->trimLineValues($line))
            ->filter(fn (array $line) => $this->hasLineContent($line))
            ->values();

        if ($lines->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['lines' => 'At least one product line is required.']);
        }

        $missingIdentifierIndex = $lines->search(fn (array $line) => $this->resolvePrimaryIdentifier($line)['value'] === null);

        if ($missingIdentifierIndex !== false) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['lines.' . $missingIdentifierIndex . '.sku' => 'SKU, IMEI, or Serial Number is required for each product line.']);
        }

        DB::beginTransaction();

        try {
            $report = SellOutReport::create([
                'invoice_no' => $this->generateInvoiceNo(),
                'original_invoice_no' => $validated['original_invoice_no'] ?? null,
                'user_id' => auth()->id(),
                'seller_name' => $validated['seller_name'] ?? null,
                'branch_name' => $validated['branch_name'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'service_type' => $validated['service_type'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'note' => $validated['note'] ?? null,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($lines as $lineData) {
                $identifier = $this->resolvePrimaryIdentifier($lineData);
                $qty = (float) ($lineData['qty'] ?? 1);
                $unitPrice = array_key_exists('unit_price', $lineData) && $lineData['unit_price'] !== null
                    ? (float) $lineData['unit_price']
                    : null;
                $subtotal = $unitPrice !== null ? round($qty * $unitPrice, 2) : null;
                $totalAmount += $subtotal ?? 0;

                $report->lines()->create([
                    'product_name' => $lineData['product_name'] ?? null,
                    'sku' => $lineData['sku'] ?? null,
                    'identifier_type' => $identifier['type'],
                    'primary_identifier' => $identifier['value'],
                    'imei' => $lineData['imei'] ?? null,
                    'imei2' => $lineData['imei2'] ?? null,
                    'serial_number' => $lineData['serial_number'] ?? null,
                    'model_number' => $lineData['model_number'] ?? null,
                    'color' => $lineData['color'] ?? null,
                    'storage' => $lineData['storage'] ?? null,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            foreach ($request->file('photos', []) as $photo) {
                $photoPath = $photo->store('sell-out-reports', 'public');

                $report->photos()->create([
                    'photo_path' => $photoPath,
                    'original_name' => $photo->getClientOriginalName(),
                ]);
            }

            $report->update(['total_amount' => round($totalAmount, 2)]);

            DB::commit();

            $report = $report->fresh(['lines', 'photos', 'user']);
            $this->sendSellOutReportTelegram($report);

            return redirect()
                ->route('admin.sell-staff-report.index')
                ->with('success', 'Sell out report created successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('danger', $exception->getMessage());
        }
    }

    public function show(int $id)
    {
        $this->authorize('view_sell_staff_report');

        $report = SellOutReport::with(['lines', 'photos', 'user:id,name,employee_code,username'])
            ->findOrFail($id);

        return view($this->view . 'show', compact('report'));
    }

    public function edit(int $id)
    {
        $this->authorize('edit_sell_staff_report');

        $report = SellOutReport::with(['lines', 'photos'])->findOrFail($id);

        return view($this->view . 'edit', compact('report'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('edit_sell_staff_report');

        $report = SellOutReport::with(['lines', 'photos'])->findOrFail($id);

        $validated = $request->validate([
            'original_invoice_no' => ['nullable', 'string', 'max:255'],
            'seller_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'service_type' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_name' => ['nullable', 'string', 'max:255'],
            'lines.*.sku' => ['nullable', 'string', 'max:255'],
            'lines.*.imei' => ['nullable', 'string', 'max:255'],
            'lines.*.imei2' => ['nullable', 'string', 'max:255'],
            'lines.*.serial_number' => ['nullable', 'string', 'max:255'],
            'lines.*.model_number' => ['nullable', 'string', 'max:255'],
            'lines.*.color' => ['nullable', 'string', 'max:255'],
            'lines.*.storage' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'numeric', 'min:1'],
            'lines.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'delete_photos' => ['nullable', 'array'],
            'delete_photos.*' => ['integer'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
        ]);

        $lines = collect($validated['lines'])
            ->map(fn (array $line) => $this->trimLineValues($line))
            ->filter(fn (array $line) => $this->hasLineContent($line))
            ->values();

        if ($lines->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['lines' => 'At least one product line is required.']);
        }

        $missingIdentifierIndex = $lines->search(fn (array $line) => $this->resolvePrimaryIdentifier($line)['value'] === null);

        if ($missingIdentifierIndex !== false) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['lines.' . $missingIdentifierIndex . '.sku' => 'SKU, IMEI, or Serial Number is required for each product line.']);
        }

        DB::beginTransaction();

        try {
            $report->update([
                'original_invoice_no' => $validated['original_invoice_no'] ?? null,
                'seller_name' => $validated['seller_name'] ?? null,
                'branch_name' => $validated['branch_name'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'service_type' => $validated['service_type'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'note' => $validated['note'] ?? null,
            ]);

            $report->lines()->delete();

            $totalAmount = 0;

            foreach ($lines as $lineData) {
                $identifier = $this->resolvePrimaryIdentifier($lineData);
                $qty = (float) ($lineData['qty'] ?? 1);
                $unitPrice = array_key_exists('unit_price', $lineData) && $lineData['unit_price'] !== null
                    ? (float) $lineData['unit_price']
                    : null;
                $subtotal = $unitPrice !== null ? round($qty * $unitPrice, 2) : null;
                $totalAmount += $subtotal ?? 0;

                $report->lines()->create([
                    'product_name' => $lineData['product_name'] ?? null,
                    'sku' => $lineData['sku'] ?? null,
                    'identifier_type' => $identifier['type'],
                    'primary_identifier' => $identifier['value'],
                    'imei' => $lineData['imei'] ?? null,
                    'imei2' => $lineData['imei2'] ?? null,
                    'serial_number' => $lineData['serial_number'] ?? null,
                    'model_number' => $lineData['model_number'] ?? null,
                    'color' => $lineData['color'] ?? null,
                    'storage' => $lineData['storage'] ?? null,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            foreach ($validated['delete_photos'] ?? [] as $photoId) {
                $photo = $report->photos->firstWhere('id', (int) $photoId);

                if ($photo) {
                    Storage::disk('public')->delete($photo->photo_path);
                    $photo->delete();
                }
            }

            foreach ($request->file('photos', []) as $photo) {
                $photoPath = $photo->store('sell-out-reports', 'public');

                $report->photos()->create([
                    'photo_path' => $photoPath,
                    'original_name' => $photo->getClientOriginalName(),
                ]);
            }

            $report->update(['total_amount' => round($totalAmount, 2)]);

            DB::commit();

            return redirect()
                ->route('admin.sell-staff-report.index')
                ->with('success', 'Sell out report updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('danger', $exception->getMessage());
        }
    }

    public function delete(int $id)
    {
        $this->authorize('delete_sell_staff_report');

        $report = SellOutReport::with('photos')->findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($report->photos as $photo) {
                Storage::disk('public')->delete($photo->photo_path);
            }

            $report->photos()->delete();
            $report->lines()->delete();
            $report->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Sell out report deleted successfully.']);
            }

            return redirect()->back()->with('success', 'Sell out report deleted successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $exception->getMessage()], 500);
            }

            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function resendTelegram(int $id)
    {
        $this->authorize('view_sell_staff_report');

        $report = SellOutReport::with(['lines', 'photos', 'user:id,name,employee_code,username'])
            ->findOrFail($id);

        $success = $this->sendSellOutReportTelegram($report);

        $responseMessage = $success
            ? __('index.resend_telegram_success')
            : __('index.resend_telegram_failed');

        if (request()->wantsJson()) {
            return response()->json(['success' => $success, 'message' => $responseMessage], $success ? 200 : 500);
        }

        return redirect()->back()->with($success ? 'success' : 'danger', $responseMessage);
    }

    private function filterData(Request $request): array
    {
        return [
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'seller_name' => trim((string) $request->query('seller_name', '')),
            'branch_name' => trim((string) $request->query('branch_name', '')),
            'search' => trim((string) $request->query('search', '')),
            'ss_date_from' => $request->query('ss_date_from'),
            'ss_date_to' => $request->query('ss_date_to'),
            'ss_branch_id' => $request->query('ss_branch_id'),
            'ss_department_id' => $request->query('ss_department_id'),
            'service_type' => $request->query('service_type'),
        ];
    }

    private function reportQuery(array $filterData)
    {
        return $this->baseReportQuery($filterData)
            ->with(['user:id,name,employee_code,username', 'lines:id,sell_out_report_id,product_name,serial_number,qty'])
            ->withCount(['lines', 'photos'])
            ->latest();
    }

    private function baseReportQuery(array $filterData)
    {
        return SellOutReport::query()
            ->when($filterData['date_from'], function ($query, $date) {
                $query->whereDate('created_at', '>=', Carbon::parse($date)->toDateString());
            })
            ->when($filterData['date_to'], function ($query, $date) {
                $query->whereDate('created_at', '<=', Carbon::parse($date)->toDateString());
            })
            ->when($filterData['seller_name'], function ($query, $sellerName) {
                $query->where('seller_name', 'like', '%' . $sellerName . '%');
            })
            ->when($filterData['branch_name'], function ($query, $branchName) {
                $query->where('branch_name', 'like', '%' . $branchName . '%');
            })
            ->when($filterData['service_type'], function ($query, $type) {
                $query->where('service_type', $type);
            })
            ->when($filterData['search'], function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('invoice_no', 'like', '%' . $search . '%')
                        ->orWhere('original_invoice_no', 'like', '%' . $search . '%')
                        ->orWhere('seller_name', 'like', '%' . $search . '%')
                        ->orWhere('branch_name', 'like', '%' . $search . '%')
                        ->orWhere('customer_name', 'like', '%' . $search . '%')
                        ->orWhere('customer_phone', 'like', '%' . $search . '%')
                        ->orWhere('payment_method', 'like', '%' . $search . '%')
                        ->orWhere('note', 'like', '%' . $search . '%');
                });
            });
    }

    private function summaryQuery(array $filterData)
    {
        $query = $this->baseReportQuery($filterData)->toBase();

        return $query
            ->selectRaw('COUNT(*) as total_reports')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_amount');
    }

    private function staffSummaryQuery(array $filterData)
    {
        $branchName = null;
        if ($branchId = $filterData['ss_branch_id']) {
            $branch = Branch::find($branchId);
            $branchName = $branch?->name;
        }

        return SellOutReport::query()
            ->when($filterData['ss_date_from'], function ($query, $date) {
                $query->whereDate('created_at', '>=', Carbon::parse($date)->toDateString());
            })
            ->when($filterData['ss_date_to'], function ($query, $date) {
                $query->whereDate('created_at', '<=', Carbon::parse($date)->toDateString());
            })
            ->when($branchName, function ($query, $name) {
                $query->where('branch_name', $name);
            })
            ->selectRaw("COALESCE(NULLIF(seller_name, ''), 'Unknown') as seller_name")
            ->selectRaw('COUNT(*) as total_reports')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_amount')
            ->groupByRaw("COALESCE(NULLIF(seller_name, ''), 'Unknown')")
            ->orderByDesc('total_amount');
    }

    private function generateInvoiceNo(): string
    {
        $date = Carbon::now()->format('Ymd');
        $prefix = "SO-$date-";
        $lastInvoice = SellOutReport::query()
            ->where('invoice_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice, -4)) + 1 : 1;

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function resolvePrimaryIdentifier(array $line): array
    {
        if (! empty($line['imei'])) {
            return ['type' => 'imei', 'value' => $line['imei']];
        }

        if (! empty($line['serial_number'])) {
            return ['type' => 'serial', 'value' => $line['serial_number']];
        }

        return [
            'type' => ! empty($line['sku']) ? 'sku' : null,
            'value' => $line['sku'] ?? null,
        ];
    }

    private function trimLineValues(array $line): array
    {
        foreach ($line as $key => $value) {
            if (is_string($value)) {
                $line[$key] = trim($value) !== '' ? trim($value) : null;
            }
        }

        return $line;
    }

    private function hasLineContent(array $line): bool
    {
        foreach ($line as $key => $value) {
            if (! in_array($key, ['qty'], true) && $value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    private function sendSellOutReportTelegram(SellOutReport $report): bool
    {
        try {
            $message = $this->buildSellOutTelegramMessage($report);

            $photoPaths = $report->photos
                ->map(fn ($photo) => Storage::disk('public')->path($photo->photo_path))
                ->filter(fn (string $path): bool => is_file($path))
                ->values()
                ->all();

            $chatIds = $this->telegramService->chatIdsForAction(
                TelegramGroup::sellOutEventKeyForServiceType($report->service_type),
                (string) ($report->branch_name ?? ''),
                ''
            );

            foreach ($chatIds as $chatId) {
                if ($photoPaths !== []) {
                    $this->telegramService->sendMediaGroup($chatId, $photoPaths, $message);
                } else {
                    $this->telegramService->sendMessage($chatId, $message);
                }
            }

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Sell out report Telegram notification failed.', [
                'sell_out_report_id' => $report->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function buildSellOutTelegramMessage(SellOutReport $report): string
    {
        $lines = ['🛒 វិក្កយបត្រ'];
        $lines[] = "Invoice: {$report->invoice_no}";

        foreach ($report->lines as $line) {
            $lines[] = "ទំនិញ: {$line->product_name} ចំនួន{$line->qty} តម្លៃ: \$"
                . number_format((float) $line->unit_price, 2);

            if (! empty($line->serial_number)) {
                $lines[] = "SN: {$line->serial_number}";
            }
        }

        $userId = $this->userIdNumber($report->user->employee_code ?? null);
        $sellerName = $report->seller_name ?: 'N/A';
        $branchName = $report->branch_name ?: 'N/A';
        $lines[] = "ID: {$userId} {$sellerName} (សាខា៖ {$branchName})";

        if ($report->customer_phone) {
            $lines[] = "ទំនាក់ទំនង: {$report->customer_phone}";
        }

        $phoneLast4 = $this->phoneLast4Digits($report->customer_phone);
        $remark = trim($userId . '-' . $phoneLast4, '-');
        if ($remark !== '') {
            $lines[] = "សម្គាល់: {$remark}";
        }

        return implode("\n", $lines);
    }

    private function userIdNumber(?string $employeeCode): string
    {
        $digits = ltrim(preg_replace('/\D/', '', (string) $employeeCode), '0');

        return $digits !== '' ? $digits : '0';
    }

    private function phoneLast4Digits(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return $digits !== '' ? substr($digits, -4) : '';
    }
}
