<?php

namespace App\Services\Payroll;

use App\Models\EmployeeProfile;
use App\Models\EmployeeProfileAuditLog;
use App\Models\EmployeeSalary;
use App\Models\EmployeeSalaryHistory;
use App\Models\SalaryReviseHistory;
use App\Models\SalaryGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeSalaryManagementService
{
    public function earningStructure(EmployeeSalary $salary): array
    {
        $structure = ['fixed' => 0.0, 'basic' => 0.0, 'ctc' => 0.0];
        if (!$salary->salary_group_id) return $structure;
        $group = SalaryGroup::with(['salaryComponents' => fn ($query) => $query->where('status', true)->where('component_type', 'earning')])
            ->find($salary->salary_group_id);
        foreach ($group?->salaryComponents ?? [] as $component) {
            if (array_key_exists($component->value_type, $structure)) {
                $structure[$component->value_type] += (float) $component->annual_component_value;
            }
        }
        return $structure;
    }

    public function amounts(EmployeeSalary $salary): array
    {
        $weekly = $salary->payment_type === 'weekly';
        return [
            'base' => round((float) ($weekly ? $salary->weekly_basic_salary : $salary->monthly_basic_salary), 2),
            'allowance' => round((float) ($weekly ? $salary->weekly_fixed_allowance : $salary->monthly_fixed_allowance), 2),
            'periods' => $weekly ? 52 : 12,
        ];
    }

    public function syncProfile(EmployeeSalary $salary, ?array $before = null): void
    {
        $amounts = $this->amounts($salary);
        $profile = EmployeeProfile::firstOrNew(['employee_id' => $salary->employee_id]);
        $profile->fill(['current_base_salary' => $amounts['base'], 'allowances' => $amounts['allowance'], 'updated_by' => $this->actorId()]);
        if (!$profile->exists) $profile->created_by = $this->actorId();
        $profile->save();
        $this->audit($salary->employee_id, $before ? 'payroll_update' : 'payroll_create', $salary->id, $before, $salary->toArray());
    }

    public function revise(array $data): void
    {
        DB::transaction(function () use ($data) {
            $this->lockEmployee($data['employee_id']);
            $salary = EmployeeSalary::where('employee_id', $data['employee_id'])->lockForUpdate()->first();
            if (!$salary) $this->invalid('employee_id', 'salary_manage_setup_required');
            $this->checkEffectiveDate($data['date_from'] ?? now()->toDateString(), $salary->employee_id);
            if (isset($data['current_salary']) && abs((float) $data['current_salary'] - (float) $salary->annual_salary) > 0.01) {
                $this->invalid('current_salary', 'salary_manage_stale');
            }
            $increase = ($data['increase_type'] ?? 'amount') === 'percent'
                ? round((float) $salary->annual_salary * (float) $data['increment_percent'] / 100, 2)
                : round((float) $data['increment_amount'] * (($data['increase_period'] ?? 'annual') === 'period' ? $this->amounts($salary)['periods'] : 1), 2);
            if ($increase <= 0) $this->invalid('increment_amount', 'salary_increase_must_be_greater');
            $annual = round((float) $salary->annual_salary + $increase, 2);
            $annualBasic = $salary->basic_salary_type === 'percent'
                ? round($annual * (float) $salary->basic_salary_value / 100, 2)
                : round((float) $salary->basic_salary_value * 12, 2);
            $before = $salary->toArray();
            $old = $this->amounts($salary);
            $this->updateAmounts($salary, $annual, $annualBasic);
            $this->recordRevision($salary, $before, $data['date_from'] ?? now()->toDateString(), $data['remark'] ?? null);
            $after = $this->amounts($salary);
            $record = EmployeeSalaryHistory::create([
                'employee_id' => $salary->employee_id, 'effective_date' => $data['date_from'] ?? now()->toDateString(),
                'old_base_salary' => $old['base'], 'new_base_salary' => $after['base'],
                'increase_amount' => round($after['base'] - $old['base'], 2),
                'increase_percentage' => $old['base'] > 0 ? round(($after['base'] - $old['base']) / $old['base'] * 100, 2) : null,
                'allowance_before' => $old['allowance'], 'allowance_after' => $after['allowance'],
                'approval_status' => 'approved', 'requested_by' => $this->actorId(), 'approved_by' => $this->actorId(),
                'requested_by_name' => $this->actorName(), 'approved_by_name' => $this->actorName(),
                'reason' => $data['remark'] ?? null,
            ]);
            $this->syncProfile($salary, $before);
            $this->audit($salary->employee_id, 'approve', $record->id, null, $record->toArray());
        });
    }

    public function approve(EmployeeSalaryHistory $record): void
    {
        DB::transaction(function () use ($record) {
            $this->lockEmployee($record->employee_id);
            $salary = EmployeeSalary::where('employee_id', $record->employee_id)->lockForUpdate()->first();
            if (!$salary) $this->invalid('new_base_salary', 'salary_manage_setup_required');
            $record = EmployeeSalaryHistory::whereKey($record->id)->lockForUpdate()->firstOrFail();
            if (!in_array($record->approval_status, ['draft', 'pending'], true)) $this->invalid('approval_status', 'salary_manage_closed');
            $this->checkEffectiveDate($record->effective_date?->toDateString(), $record->employee_id);
            $old = $this->amounts($salary);
            if (abs((float) $record->old_base_salary - $old['base']) > 0.01
                || ($record->allowance_before !== null && abs((float) $record->allowance_before - $old['allowance']) > 0.01)) {
                $this->invalid('old_base_salary', 'salary_manage_stale');
            }
            $newBase = round((float) $record->new_base_salary, 2);
            if ($newBase <= $old['base']) $this->invalid('new_base_salary', 'salary_increase_must_be_greater');
            $allowance = $record->allowance_after === null ? $old['allowance'] : round((float) $record->allowance_after, 2);
            $structure = $this->earningStructure($salary);
            if ($structure['ctc'] >= 100) $this->invalid('allowance_after', 'salary_manage_invalid_amount');
            $annualBasic = round($newBase * $old['periods'], 2);
            $annual = round((($newBase + $allowance) * $old['periods'] + $structure['fixed'] + $annualBasic * $structure['basic'] / 100)
                / (1 - $structure['ctc'] / 100), 2);
            if ($annual < (float) $salary->annual_salary - 0.01) $this->invalid('allowance_after', 'salary_manage_total_decrease');
            $before = $salary->toArray();
            // Keep the new basic amount stable through later payroll edits.
            $salary->basic_salary_type = 'fixed';
            $salary->basic_salary_value = round($annualBasic / 12, 2);
            $this->updateAmounts($salary, $annual, $annualBasic);
            $oldRecord = $record->toArray();
            $record->fill([
                'increase_amount' => round($newBase - $old['base'], 2),
                'increase_percentage' => $old['base'] > 0 ? round(($newBase - $old['base']) / $old['base'] * 100, 2) : null,
                'allowance_before' => $old['allowance'], 'allowance_after' => $allowance,
                'approval_status' => 'approved', 'approved_by' => $this->actorId(), 'reviewed_by' => $this->actorId(),
                'approved_by_name' => $this->actorName(),
            ])->save();
            $this->recordRevision($salary, $before, $record->effective_date->toDateString(), $record->reason);
            $this->syncProfile($salary, $before);
            $this->audit($salary->employee_id, 'approve', $record->id, $oldRecord, $record->toArray());
        });
    }

    private function updateAmounts(EmployeeSalary $salary, float $annual, float $annualBasic): void
    {
        if ($annual > 99999999.99 || $annualBasic < 0 || $annualBasic > $annual) $this->invalid('increment_amount', 'salary_manage_invalid_amount');
        $structure = $this->earningStructure($salary);
        $earnings = round($structure['fixed'] + $annualBasic * $structure['basic'] / 100 + $annual * $structure['ctc'] / 100, 2);
        $allowance = round($annual - $annualBasic - $earnings, 2);
        if ($allowance < 0) $this->invalid('increment_amount', 'salary_manage_invalid_amount');
        $data = [
            'annual_salary' => $annual, 'annual_basic_salary' => $annualBasic,
            'monthly_basic_salary' => round($annualBasic / 12, 2), 'weekly_basic_salary' => round($annualBasic / 52, 2),
            'annual_fixed_allowance' => $allowance,
            'monthly_fixed_allowance' => round($allowance / 12, 2), 'weekly_fixed_allowance' => round($allowance / 52, 2),
        ];
        if ($salary->payroll_type === 'hourly') {
            $hours = (float) ($salary->payment_type === 'weekly' ? $salary->weekly_hours : $salary->monthly_hours);
            if ($hours <= 0) $this->invalid('increment_amount', 'salary_manage_hours_required');
            $data['hour_rate'] = round($annual / ($salary->payment_type === 'weekly' ? 52 : 12) / $hours, 2);
        }
        $salary->fill($data)->save();
    }

    private function recordRevision(EmployeeSalary $salary, array $before, string $date, ?string $reason): void
    {
        SalaryReviseHistory::create([
            'employee_id' => $salary->employee_id, 'salary_revised_on' => now()->toDateString(),
            'base_salary' => $before['annual_salary'], 'revised_salary' => $salary->annual_salary,
            'increment_amount' => round((float) $salary->annual_salary - (float) $before['annual_salary'], 2),
            'increment_percent' => (float) $before['annual_salary'] > 0
                ? round(((float) $salary->annual_salary - (float) $before['annual_salary']) / (float) $before['annual_salary'] * 100, 2) : 0,
            'base_monthly_salary' => $before['monthly_basic_salary'], 'base_weekly_salary' => $before['weekly_basic_salary'],
            'base_monthly_allowance' => $before['monthly_fixed_allowance'], 'base_weekly_allowance' => $before['weekly_fixed_allowance'],
            'hour_rate' => $before['hour_rate'], 'monthly_hours' => $before['monthly_hours'], 'weekly_hours' => $before['weekly_hours'],
            'date_from' => $date, 'remark' => $reason,
        ]);
    }

    private function checkEffectiveDate(?string $date, int $employeeId): void
    {
        if (!$date || $date > now()->toDateString()) $this->invalid('effective_date', 'salary_manage_future');
        if (SalaryReviseHistory::where('employee_id', $employeeId)->where('date_from', '>', $date)->exists()) {
            $this->invalid('effective_date', 'salary_manage_backdated');
        }
    }

    private function actorId(): ?int
    {
        return auth('admin')->check() ? null : auth()->id();
    }

    private function actorName(): ?string
    {
        return (auth('admin')->user() ?? auth()->user())?->name;
    }

    private function lockEmployee(int $employeeId): User
    {
        $employee = User::whereKey($employeeId)->lockForUpdate()->firstOrFail();
        if (!auth('admin')->check() && auth()->user()?->branch_id) {
            abort_unless((int) $employee->branch_id === (int) auth()->user()->branch_id, 403);
        }
        return $employee;
    }

    public function remove(EmployeeSalary $salary): void
    {
        DB::transaction(function () use ($salary) {
            $this->lockEmployee($salary->employee_id);
            $salary = EmployeeSalary::whereKey($salary->id)->lockForUpdate()->firstOrFail();
            $before = $salary->toArray();
            EmployeeProfile::where('employee_id', $salary->employee_id)->update([
                'current_base_salary' => null, 'allowances' => null, 'updated_by' => $this->actorId(),
            ]);
            $salary->delete();
            $this->audit($salary->employee_id, 'payroll_delete', $salary->id, $before, []);
        });
    }

    private function audit(int $employeeId, string $action, int $recordId, ?array $before, array $after): void
    {
        EmployeeProfileAuditLog::create([
            'employee_id' => $employeeId, 'module' => 'salary', 'action' => $action, 'record_id' => $recordId,
            'old_values' => $before, 'new_values' => $after + ['performed_by_name' => (auth('admin')->user() ?? auth()->user())?->name],
            'performed_by' => $this->actorId(), 'created_at' => now(),
        ]);
    }

    private function invalid(string $field, string $key): never
    {
        throw ValidationException::withMessages([$field => __('index.'.$key)]);
    }
}
