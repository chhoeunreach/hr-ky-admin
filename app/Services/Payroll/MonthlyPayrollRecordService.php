<?php

namespace App\Services\Payroll;

use App\Models\Branch;
use App\Models\MonthlyPayrollRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonthlyPayrollRecordService
{
    public function __construct(private GeneratePayrollService $generator, private MonthlyPayrollReportService $reports) {}

    public function preview(string $month, Branch $branch, array $options, string $currency): array
    {
        // Use the same calculator and persistence path as Save, but roll back
        // every database change before returning the private preview snapshot.
        $level = DB::transactionLevel();
        DB::beginTransaction();
        try {
            return $this->generate($month, $branch, $options, $currency)->only([
                'month', 'branch_id', 'branch_name', 'currency', 'options', 'columns', 'rows', 'summary',
            ]);
        } finally {
            DB::rollBack($level);
        }
    }

    private function fingerprint(array $snapshot): string
    {
        $rows = array_map(fn ($row) => [
            'employee' => $row['employee'], 'values' => $row['values'],
            'status' => $row['attributes']['status'] ?? null,
            'salary_from' => $row['attributes']['salary_from'] ?? null,
            'salary_to' => $row['attributes']['salary_to'] ?? null,
        ], $snapshot['rows']);
        return hash('sha256', json_encode([
            $snapshot['month'], (int) $snapshot['branch_id'], $snapshot['currency'],
            $snapshot['columns'], $rows, $snapshot['summary'],
        ], JSON_THROW_ON_ERROR));
    }

    public function generate(string $month, Branch $branch, array $options, string $currency, ?array $reviewed = null): MonthlyPayrollRecord
    {
        return DB::transaction(function () use ($month, $branch, $options, $currency, $reviewed) {
            // Serialize generation for this branch, including simultaneous requests.
            Branch::whereKey($branch->id)->lockForUpdate()->firstOrFail();
            if (MonthlyPayrollRecord::where('month', $month)->where('branch_id', $branch->id)->exists()) {
                throw ValidationException::withMessages(['month' => __('index.monthly_payroll_already_saved')]);
            }
            [$year, $number] = explode('-', $month);
            $this->generator->getEmployeeSalariesToCreatePayslip($options + [
                'salary_cycle' => 'monthly', 'year' => $year, 'month' => $number,
                'branch_id' => $branch->id, 'department_id' => null,
                'preserve_existing' => true, 'is_bs_enabled' => false,
            ]);
            $report = $this->reports->report([
                'month' => $month, 'branch_id' => $branch->id, 'department_id' => null,
                'q' => '', 'status' => null, 'per_page' => 'all', 'export' => null, 'live' => true,
            ]);
            if ($report['payslips']->isEmpty()) {
                throw ValidationException::withMessages(['month' => __('index.monthly_payroll_nothing_to_save')]);
            }
            $rows = $report['payslips']->values()->map(function ($payslip, $index) use ($report) {
                $commissions = $report['commissions'][$payslip->employee_id] ?? [];
                return [
                    'attributes' => $payslip->getAttributes(),
                    'employee' => $payslip->employee?->only(['id', 'name', 'english_name', 'username', 'employee_code', 'branch_id', 'department_id']),
                    'values' => $this->reports->rowValues($payslip, $commissions, $index + 1),
                    'commission_total' => array_sum($commissions),
                ];
            })->all();
            if ($reviewed !== null && !hash_equals($this->fingerprint($reviewed), $this->fingerprint([
                'month' => $month, 'branch_id' => $branch->id, 'currency' => $currency,
                'columns' => $report['columns'], 'rows' => $rows, 'summary' => $report['summary'],
            ]))) {
                throw ValidationException::withMessages(['preview' => __('index.monthly_payroll_preview_changed')]);
            }
            $actor = auth('admin')->user() ?? auth()->user();
            return MonthlyPayrollRecord::create([
                'month' => $month, 'branch_id' => $branch->id, 'branch_name' => $branch->name,
                'currency' => $currency, 'options' => $options + ['loan_amount_source' => MonthlyPayrollLoanService::SOURCE], 'columns' => $report['columns'],
                'rows' => $rows, 'summary' => $report['summary'],
                'generated_by' => $actor?->id, 'generated_by_name' => $actor?->name,
                'generated_by_type' => auth('admin')->check() ? 'admin' : 'user',
            ]);
        });
    }
}
