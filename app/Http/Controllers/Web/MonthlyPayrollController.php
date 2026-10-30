<?php

namespace App\Http\Controllers\Web;

use App\Exports\EmployeeSalaryTableExport;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\MonthlyPayrollRecord;
use App\Services\Payroll\MonthlyPayrollReportService;
use App\Services\Payroll\MonthlyPayrollRecordService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class MonthlyPayrollController extends Controller
{
    use CustomAuthorizesRequests;

    public function index(Request $request, MonthlyPayrollReportService $service)
    {
        $this->authorize('view_monthly_payroll');
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'], 'branch_id' => ['nullable', 'integer', 'min:1'],
            'department_id' => ['nullable', 'integer', 'min:1'], 'q' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', 'in:generated,review,locked,paid'], 'per_page' => ['nullable', 'in:10,25,50,100,all'],
            'export' => ['nullable', 'in:csv,excel,print,pdf'], 'orientation' => ['nullable', 'in:portrait,landscape'],
            'paper' => ['nullable', 'in:A3,A4'],
            'view' => ['nullable', 'in:monthly,yearly'], 'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        if ($request->filled('export')) $this->authorize('export_monthly_payroll');
        $allowedBranchId = !auth('admin')->check() ? auth()->user()?->branch_id : null;
        $branchId = $allowedBranchId ?: $request->input('branch_id');
        $filters = ['month' => $request->input('month') ?: now()->format('Y-m'), 'branch_id' => $branchId,
            'department_id' => $request->input('department_id'), 'q' => trim((string) $request->input('q')),
            'status' => $request->input('status'), 'per_page' => $request->input('per_page') ?: '25', 'export' => $request->input('export')];
        $year = (int) ($request->input('year') ?: substr($filters['month'], 0, 4));
        if ($request->input('view') === 'yearly') {
            $filters['month'] = sprintf('%04d-%02d', $year, (int) substr($filters['month'], 5));
            $branches = Branch::query()->when($allowedBranchId, fn ($q) => $q->whereKey($allowedBranchId))->orderBy('name')->get(['id', 'name']);
            $departments = Department::query()->when($allowedBranchId, fn ($q) => $q->where('branch_id', $allowedBranchId))->orderBy('dept_name')->get(['id', 'dept_name', 'branch_id']);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            return view('admin.payroll.monthly.yearly', $service->yearly($filters, $year) + compact('branches', 'departments', 'filters', 'currency', 'year'));
        }
        $report = $service->report($filters);
        $currency = $report['savedCurrency'] ?? AppHelper::getCompanyPaymentCurrencySymbol();
        if ($filters['export']) {
            if (in_array($filters['export'], ['csv', 'excel'], true)) {
                $table = $service->exportTable($report);
                if ($filters['export'] === 'csv') $table['rows'] = array_map(fn ($row) => array_map(fn ($value) => preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value, $row), $table['rows']);
                return Excel::download(new EmployeeSalaryTableExport($table['headings'], $table['rows']), 'monthly-payroll-'.$filters['month'].($filters['export'] === 'csv' ? '.csv' : '.xlsx'));
            }
            return view('admin.payroll.monthly.export', $report + compact('currency', 'service', 'filters') + [
                'format' => $filters['export'], 'orientation' => $request->input('orientation') ?: 'landscape',
                'paper' => $request->input('paper') ?: 'A3']);
        }
        $data = $report + compact('filters', 'currency', 'service');
        if ($request->header('X-Monthly-Payroll-Fragment') === '1') return view('admin.payroll.monthly.fragment', $data);
        $branches = Branch::query()->when($allowedBranchId, fn ($q) => $q->whereKey($allowedBranchId))->orderBy('name')->get(['id', 'name']);
        $departments = Department::query()->when($allowedBranchId, fn ($q) => $q->where('branch_id', $allowedBranchId))->orderBy('dept_name')->get(['id', 'dept_name', 'branch_id']);
        return view('admin.payroll.monthly.index', $data + compact('branches', 'departments'));
    }

    public function generate(Request $request, MonthlyPayrollRecordService $service)
    {
        $this->authorize('view_monthly_payroll');
        $this->authorize('generate_payroll');
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'], 'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'return_view' => ['nullable', 'in:monthly,yearly'],
            'attendance' => ['nullable', 'boolean'], 'include_tds' => ['nullable', 'boolean'],
            'include_ssf' => ['nullable', 'boolean'], 'include_pf' => ['nullable', 'boolean'],
            'include_tada' => ['nullable', 'boolean'], 'include_advance_salary' => ['nullable', 'boolean'],
        ]);
        $allowedBranchId = !auth('admin')->check() ? auth()->user()?->branch_id : null;
        abort_if($allowedBranchId && (int) $data['branch_id'] !== (int) $allowedBranchId, 403);
        $branch = Branch::findOrFail($data['branch_id']);
        $options = ['absent_paid' => 0, 'approved_paid_leaves' => 0];
        foreach (['attendance', 'include_tds', 'include_ssf', 'include_pf', 'include_tada', 'include_advance_salary'] as $key) $options[$key] = (int) ($data[$key] ?? 0);
        $payload = $service->preview($data['month'], $branch, $options, AppHelper::getCompanyPaymentCurrencySymbol());
        $token = (string) Str::uuid();
        Cache::put('monthly-payroll-preview:'.$token, [
            'owner' => $this->previewOwner(), 'payload' => $payload, 'return_view' => $data['return_view'] ?? 'monthly',
        ], now()->addMinutes(30));
        return redirect()->route('admin.monthly-payroll.preview', ['token' => $token]);
    }

    private function previewOwner(): string
    {
        return auth('admin')->check() ? 'admin:'.auth('admin')->id() : 'user:'.auth()->id();
    }

    private function previewData(string $token): array
    {
        $this->authorize('view_monthly_payroll');
        $this->authorize('generate_payroll');
        $preview = Cache::get('monthly-payroll-preview:'.$token);
        abort_unless($preview, 410, __('index.monthly_payroll_preview_expired'));
        abort_unless($preview['owner'] === $this->previewOwner(), 403);
        $allowedBranchId = !auth('admin')->check() ? auth()->user()?->branch_id : null;
        abort_if($allowedBranchId && (int) $preview['payload']['branch_id'] !== (int) $allowedBranchId, 403);
        return $preview;
    }

    public function preview(string $token, MonthlyPayrollReportService $service)
    {
        $preview = $this->previewData($token);
        $payload = $preview['payload'];
        $filters = ['month' => $payload['month'], 'branch_id' => $payload['branch_id'], 'department_id' => null,
            'q' => '', 'status' => null, 'per_page' => 'all', 'export' => null];
        $report = $service->savedReport(collect([(new MonthlyPayrollRecord())->forceFill($payload)]), $filters);
        $returnUrl = route('admin.monthly-payroll.index', ['month' => $payload['month'], 'branch_id' => $payload['branch_id'],
            'view' => $preview['return_view'], 'year' => substr($payload['month'], 0, 4)]);
        return view('admin.payroll.monthly.preview', array_replace($report, [
            'isSavedReport' => false, 'isPreview' => true, 'currency' => $payload['currency'],
            'service' => $service, 'filters' => $filters, 'token' => $token, 'payload' => $payload, 'returnUrl' => $returnUrl,
        ]));
    }

    public function savePreview(string $token, MonthlyPayrollRecordService $service)
    {
        $preview = $this->previewData($token);
        $data = $preview['payload'];
        $branch = Branch::findOrFail($data['branch_id']);
        $service->generate($data['month'], $branch, $data['options'], $data['currency'], $data);
        Cache::forget('monthly-payroll-preview:'.$token);
        return redirect()->route('admin.monthly-payroll.index', [
            'view' => $preview['return_view'], 'year' => substr($data['month'], 0, 4),
            'month' => $data['month'], 'branch_id' => $branch->id,
        ])->with('success', __('index.monthly_payroll_saved'));
    }
}
