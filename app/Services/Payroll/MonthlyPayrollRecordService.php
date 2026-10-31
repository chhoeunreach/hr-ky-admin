<?php

namespace App\Services\Payroll;

use App\Models\Branch;
use App\Models\EmployeePayslip;
use App\Models\MonthlyPayrollRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonthlyPayrollRecordService
{
    private const SNAPSHOT_FIELDS = ['month', 'branch_id', 'branch_name', 'currency', 'options', 'columns', 'rows', 'summary'];
    public function __construct(private GeneratePayrollService $generator, private MonthlyPayrollReportService $reports) {}

    public function preview(string $month, Branch $branch, array $options, string $currency): array
    {
        // Use the same calculator and persistence path as Save, but roll back
        // every database change before returning the private preview snapshot.
        $level = DB::transactionLevel();
        DB::beginTransaction();
        try {
            return $this->generate($month, $branch, $options, $currency)->only(self::SNAPSHOT_FIELDS);
        } finally {
            DB::rollBack($level);
        }
    }

    public function previewBranches(string $month, iterable $branches, array $options, string $currency): array
    {
        $payloads = $emptyBranches = [];
        foreach ($branches as $branch) {
            $saved = MonthlyPayrollRecord::where('month', $month)->where('branch_id', $branch->id)->first();
            if ($saved) {
                $payloads[] = $saved->only(self::SNAPSHOT_FIELDS) + ['already_saved' => true, 'record_id' => $saved->id];
                continue;
            }
            try {
                $payloads[] = $this->preview($month, $branch, $options, $currency);
            } catch (ValidationException $exception) {
                if (($exception->errors()['month'] ?? []) !== [__('index.monthly_payroll_nothing_to_save')]) throw $exception;
                $emptyBranches[] = $branch->name;
            }
        }
        if (!$payloads) throw ValidationException::withMessages(['month' => __('index.monthly_payroll_nothing_to_save')]);
        if (count(array_unique(array_column($payloads, 'currency'))) > 1) {
            throw ValidationException::withMessages(['branch_ids' => __('index.monthly_payroll_mixed_currencies')]);
        }
        return compact('payloads', 'emptyBranches');
    }

    public function saveBranches(array $payloads): void
    {
        DB::transaction(function () use ($payloads) {
            $payloads = collect($payloads)->sortBy('branch_id')->values();
            $branches = Branch::whereIn('id', $payloads->pluck('branch_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($branches->count() !== $payloads->count()) {
                throw ValidationException::withMessages(['branch_ids' => __('index.monthly_payroll_preview_changed')]);
            }
            $created = 0;
            foreach ($payloads as $payload) {
                if (!empty($payload['already_saved'])) {
                    $record = MonthlyPayrollRecord::whereKey($payload['record_id'])->lockForUpdate()->first();
                    if (!$record || !hash_equals($this->fingerprint($payload), $this->fingerprint($record->only(self::SNAPSHOT_FIELDS)))) {
                        throw ValidationException::withMessages(['preview' => __('index.monthly_payroll_preview_changed')]);
                    }
                    continue;
                }
                $this->generate($payload['month'], $branches[$payload['branch_id']], $payload['options'], $payload['currency'], $payload);
                $created++;
            }
            if (!$created) throw ValidationException::withMessages(['month' => __('index.monthly_payroll_already_saved')]);
        });
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
            $attendanceBonus = app(MonthlyAttendanceBonusService::class);
            EmployeePayslip::with(['employee', 'payslipDetail.salaryComponent', 'additionalData.salaryComponent'])
                ->where('salary_cycle', 'monthly')->where('salary_from', $month.'-01')
                ->whereIn('status', ['generated', 'review'])
                ->whereHas('employee', fn ($query) => $query->where('branch_id', $branch->id))
                ->get()->each(fn ($payslip) => $attendanceBonus->apply($payslip));
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
