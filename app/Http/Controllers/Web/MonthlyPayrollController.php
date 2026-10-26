<?php

namespace App\Http\Controllers\Web;

use App\Exports\EmployeeSalaryTableExport;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Services\Payroll\MonthlyPayrollReportService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
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
        ]);
        if ($request->filled('export')) $this->authorize('export_monthly_payroll');
        $allowedBranchId = !auth('admin')->check() ? auth()->user()?->branch_id : null;
        $branchId = $allowedBranchId ?: $request->input('branch_id');
        $filters = ['month' => $request->input('month') ?: now()->format('Y-m'), 'branch_id' => $branchId,
            'department_id' => $request->input('department_id'), 'q' => trim((string) $request->input('q')),
            'status' => $request->input('status'), 'per_page' => $request->input('per_page') ?: '25', 'export' => $request->input('export')];
        $report = $service->report($filters);
        $currency = AppHelper::getCompanyPaymentCurrencySymbol();
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
        if ($request->header('X-Monthly-Payroll-Fragment') === '1') return view('admin.payroll.monthly.results', $data);
        $branches = Branch::query()->when($allowedBranchId, fn ($q) => $q->whereKey($allowedBranchId))->orderBy('name')->get(['id', 'name']);
        $departments = Department::query()->when($allowedBranchId, fn ($q) => $q->where('branch_id', $allowedBranchId))->orderBy('dept_name')->get(['id', 'dept_name', 'branch_id']);
        return view('admin.payroll.monthly.index', $data + compact('branches', 'departments'));
    }
}
