<?php

namespace App\Services\Payroll;

use App\Models\AdvanceSalary;
use Carbon\Carbon;

class MonthlyPayrollLoanService
{
    public const SOURCE = 'approved_released_month_v1';

    public function amounts(string $month, iterable $employeeIds): array
    {
        $employeeIds = collect($employeeIds)->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->unique()->values();
        if ($employeeIds->isEmpty()) return [];
        $start = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        // Released loans belong to their grant month even after repayment.
        // The caller supplies employee IDs from its already authorized report.
        return AdvanceSalary::withoutGlobalScopes()->whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')->where('released_amount', '>', 0)
            ->where('amount_granted_date', '>=', $start->toDateString())
            ->where('amount_granted_date', '<', $start->copy()->addMonth()->toDateString())
            ->selectRaw('employee_id, SUM(released_amount) as loan_total')->groupBy('employee_id')
            ->get()->mapWithKeys(fn ($loan) => [$loan->employee_id => round((float) $loan->loan_total, 2)])->all();
    }

    public function correctedRows(string $month, array $rows): array
    {
        $amounts = $this->amounts($month, array_map(fn ($row) => $row['attributes']['employee_id'] ?? null, $rows));
        foreach ($rows as &$row) $row['values']['advance_salary'] = $amounts[$row['attributes']['employee_id'] ?? 0] ?? 0.0;
        unset($row);
        return $rows;
    }

    public function totalRows(iterable $rows): float
    {
        $amounts = [];
        foreach ($rows as $row) {
            $employeeId = $row['attributes']['employee_id'] ?? $row['employee']['id'] ?? null;
            if ($employeeId !== null) $amounts[$employeeId] = (float) ($row['values']['advance_salary'] ?? 0);
        }
        return round(array_sum($amounts), 2);
    }
}
