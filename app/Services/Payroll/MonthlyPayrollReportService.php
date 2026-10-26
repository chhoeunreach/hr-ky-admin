<?php

namespace App\Services\Payroll;

use App\Models\EmployeePayslip;
use App\Models\SellOutReport;
use Carbon\Carbon;

class MonthlyPayrollReportService
{
    public function report(array $filters): array
    {
        $month = Carbon::createFromFormat('!Y-m', $filters['month']);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $branchId = !auth('admin')->check() && auth()->user()?->branch_id ? auth()->user()->branch_id : $filters['branch_id'];
        $query = EmployeePayslip::withoutGlobalScope('branch')->where('salary_cycle', 'monthly')
            ->whereBetween('salary_from', [$start->toDateString(), $end->toDateString()])
            ->whereHas('employee', function ($employee) use ($filters, $branchId) {
                $employee->withTrashed()
                    ->when($branchId, fn ($q, $id) => $q->where('branch_id', $id))
                    ->when($filters['department_id'], fn ($q, $id) => $q->where('department_id', $id))
                    ->when($filters['q'], fn ($q, $term) => $q->where(function ($search) use ($term) {
                        $search->where('name', 'like', '%'.$term.'%')->orWhere('username', 'like', '%'.$term.'%')
                            ->orWhere('employee_code', 'like', '%'.$term.'%');
                    }));
            })->when($filters['status'], fn ($q, $status) => $q->where('status', $status));

        $summary = ['records' => (clone $query)->count(), 'gross' => (clone $query)->sum('gross_salary'),
            'net' => (clone $query)->sum('net_salary'), 'bonus' => (clone $query)->sum('bonus')];
        $sales = SellOutReport::query()->whereIn('user_id', (clone $query)->select('employee_id'))
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('user_id, service_type, SUM(commission) as commission_total')
            ->groupBy('user_id', 'service_type')->get();
        $commissionTypes = [];
        $commissions = [];
        foreach ($sales as $sale) {
            $type = trim((string) $sale->service_type) ?: SellOutReport::DEFAULT_SERVICE_TYPE;
            $commissionTypes[$type] = $type;
            $commissions[$sale->user_id][$type] = round(($commissions[$sale->user_id][$type] ?? 0) + (float) $sale->commission_total, 2);
        }
        ksort($commissionTypes);
        $summary['commission'] = array_sum(array_map('array_sum', $commissions));
        $query->with(['employee' => fn ($q) => $q->withTrashed()->with(['branch', 'department']),
            'payslipDetail.salaryComponent', 'additionalData.salaryComponent'])->orderBy('employee_id')->orderBy('id');
        $payslips = $filters['per_page'] === 'all' || $filters['export']
            ? $query->get() : $query->paginate((int) $filters['per_page'])->withQueryString();

        return compact('month', 'payslips', 'summary', 'commissionTypes', 'commissions');
    }

    public function componentTotal(EmployeePayslip $payslip, string $type): float
    {
        return round($payslip->payslipDetail->concat($payslip->additionalData)
            ->filter(fn ($detail) => $detail->salaryComponent?->component_type === $type)->sum('amount'), 2);
    }

    public function exportTable(array $report): array
    {
        $keys = ['employee_code', 'employee', 'salary_table_gross', 'present_days', 'absent_days', 'paid_leave', 'unpaid_leave', 'bonus'];
        $headings = array_map(fn ($key) => __('index.'.$key), $keys);
        foreach ($report['commissionTypes'] as $type) $headings[] = __('index.commission').' · '.$type;
        $moneyKeys = ['overtime', 'tada', 'advance_salary', 'absent_deduction', 'undertime', 'tds', 'ssf_deduction', 'pf_deduction'];
        foreach ($moneyKeys as $key) $headings[] = __('index.monthly_payroll_'.$key);
        $headings = array_merge($headings, [__('index.monthly_payroll_other_earnings'), __('index.monthly_payroll_other_deductions'), __('index.net_salary'), __('index.status'), __('index.remark')]);
        $rows = $report['payslips']->map(function ($payslip) use ($report, $moneyKeys) {
            $money = fn ($value) => number_format((float) $value, 2, '.', '');
            $row = [$payslip->employee?->employee_code ?? '', $payslip->employee?->name ?? '', $money($payslip->gross_salary),
                (string) $payslip->present_days, (string) $payslip->absent_days, (string) $payslip->paid_leave, (string) $payslip->unpaid_leave, $money($payslip->bonus)];
            foreach ($report['commissionTypes'] as $type) $row[] = $money($report['commissions'][$payslip->employee_id][$type] ?? 0);
            foreach ($moneyKeys as $key) $row[] = $money($payslip->$key);
            return array_merge($row, [$money($this->componentTotal($payslip, 'earning')), $money($this->componentTotal($payslip, 'deductions')),
                $money($payslip->net_salary), __('index.'.$payslip->status), (string) $payslip->remark]);
        })->all();
        return compact('headings', 'rows');
    }
}
