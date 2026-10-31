<?php

namespace App\Services\Payroll;

use App\Models\Attendance;
use App\Models\EmployeePayslip;
use App\Models\EmployeePayslipAdditionalDetail;
use App\Models\SalaryComponent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyAttendanceBonusService
{
    private const NAMES = ['មិនឈប់', 'attendance bonus'];

    public function rate(string $position, string $department): float
    {
        $position = trim($position);
        if ($position === 'ថ្នាក់ដឹកនាំ') return 50.0;
        if ($position === 'ប្រធាន' || str_contains($department, 'ជាង')) return 30.0;
        return 20.0;
    }

    public function amount(User $employee, string $month): float
    {
        $start = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        // A partially completed month is not a full month, even with no absence so far.
        if ($end->isFuture() || !$employee->company_id || !$employee->joining_date
            || Carbon::parse($employee->joining_date)->gt($start)) return 0.0;

        $weekends = DB::table('companies')->where('id', $employee->company_id)->value('weekend');
        if ($weekends === null) return 0.0;
        $weekends = array_map('intval', json_decode($weekends, true) ?: []);
        $holidays = DB::table('holidays')->where('company_id', $employee->company_id)->where('is_active', 1)
            ->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])->pluck('event_date')->all();
        $leaves = DB::table('leave_requests_master as leave')->leftJoin('leave_types as type', 'type.id', '=', 'leave.leave_type_id')
            ->where('leave.requested_by', $employee->id)->whereIn('leave.status', ['pending', 'review', 'approved'])
            ->whereDate('leave.leave_from', '<=', $end->toDateString())->whereDate('leave.leave_to', '>=', $start->toDateString())
            ->get(['leave.leave_from', 'leave.leave_to', 'leave.status', 'type.name', 'type.slug']);
        $timeLeaves = DB::table('time_leaves')->where('requested_by', $employee->id)->whereIn('status', ['pending', 'review', 'approved'])
            ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])->pluck('issue_date')->all();
        $attendances = Attendance::where('user_id', $employee->id)->where('attendance_status', 1)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])->get()
            ->groupBy(fn ($row) => Carbon::parse($row->attendance_date)->toDateString());

        $workingDays = 0;
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $date = $day->toDateString();
            if (in_array($day->dayOfWeek, $weekends, true) || in_array($date, $holidays, true)) continue;
            $dayLeaves = $leaves->filter(fn ($leave) => substr($leave->leave_from, 0, 10) <= $date && substr($leave->leave_to, 0, 10) >= $date);
            // An approved scheduled day off is not an absence or a leave day.
            $dayOff = $dayLeaves->contains(fn ($leave) => $leave->status === 'approved'
                && (str_contains(mb_strtolower($leave->name ?? ''), 'day off') || $leave->slug === 'day-off'));
            if ($dayOff) continue;
            $workingDays++;
            if ($dayLeaves->isNotEmpty() || in_array($date, $timeLeaves, true)) return 0.0;
            $rows = $attendances->get($date, collect());
            if (!$rows->contains(fn ($row) => $row->check_in_at || $row->night_checkin)
                || !$rows->contains(fn ($row) => $row->check_out_at || $row->night_checkout)) return 0.0;
        }
        if (!$workingDays) return 0.0;
        return $this->rate((string) $employee->post?->post_name, (string) $employee->department?->dept_name);
    }

    /** Replace any previous attendance-bonus earning, adjusting net pay by the difference only. */
    public function apply(EmployeePayslip $payslip): void
    {
        if ($payslip->salary_cycle !== 'monthly' || !in_array($payslip->status, ['generated', 'review'], true)) return;
        $amount = $payslip->employee ? $this->amount($payslip->employee, substr((string) $payslip->salary_from, 0, 7)) : 0.0;
        $details = $payslip->payslipDetail->concat($payslip->additionalData)->filter(fn ($detail) =>
            $detail->salaryComponent?->component_type === 'earning'
            && in_array(mb_strtolower(trim($detail->salaryComponent->name)), self::NAMES, true));
        $oldAmount = round((float) $details->sum('amount'), 2);
        if ($oldAmount === $amount && ($amount === 0.0 || $details->count() === 1)) return;
        foreach ($details as $detail) $detail->delete();
        if ($amount > 0) {
            $component = SalaryComponent::firstOrCreate(['name' => 'មិនឈប់', 'component_type' => 'earning'], [
                'value_type' => 'fixed', 'annual_component_value' => 0, 'status' => 0, 'apply_for_all' => 0,
            ]);
            EmployeePayslipAdditionalDetail::create(['employee_payslip_id' => $payslip->id, 'salary_component_id' => $component->id, 'amount' => $amount]);
        }
        $payslip->update(['net_salary' => round((float) $payslip->net_salary + $amount - $oldAmount, 2)]);
        $payslip->unsetRelation('payslipDetail')->unsetRelation('additionalData');
    }
}
