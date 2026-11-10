<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Read-only, current-month preview for the authenticated employee. */
class EmployeeMonthlyAttendanceSummary
{
    use MonthlyAttendanceRules;

    public function forEmployee(User $employee): array
    {
        $now = now();
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();
        $employee->loadMissing('officeTime');
        $weekend = DB::table('companies')->where('id', $employee->company_id)->value('weekend');
        // Missing company or shift data must not look like zero lateness or earned bonus.
        if ($weekend === null || !$employee->officeTime?->opening_time || !$employee->officeTime?->closing_time) {
            return ['available' => false, 'month' => $now->format('Y-m')];
        }
        $weekends = array_map('intval', is_array($weekend) ? $weekend : (json_decode($weekend, true) ?: []));
        $holidays = DB::table('holidays')->where('company_id', $employee->company_id)->where('is_active', 1)
            ->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])
            ->pluck('event_date')->map(fn ($date) => Carbon::parse($date)->toDateString())->all();
        $attendance = Attendance::query()->where('user_id', $employee->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $now->toDateString()])
            ->orderBy('check_in_at')->get(['attendance_date', 'check_in_at', 'check_out_at', 'night_checkin', 'night_checkout', 'attendance_status'])
            ->groupBy(fn ($row) => Carbon::parse($row->attendance_date)->toDateString());
        $leaves = DB::table('leave_requests_master')->where('requested_by', $employee->id)
            ->whereIn('status', ['pending', 'review', 'approved'])->where('early_exit', 0)
            ->whereDate('leave_from', '<=', $end->toDateString())->whereDate('leave_to', '>=', $start->toDateString())
            ->get(['leave_from', 'leave_to', 'status']);
        $timeLeaves = DB::table('time_leaves')->where('requested_by', $employee->id)
            ->whereIn('status', ['pending', 'review', 'approved'])
            ->whereBetween('issue_date', [$start->toDateString(), $now->toDateString()])
            ->get(['issue_date', 'status'])->groupBy(fn ($leave) => Carbon::parse($leave->issue_date)->toDateString());
        $breakdown = $this->lateBreakdownTemplate();
        $excused = $approvedLate = $eligible = $workingDays = $elapsedDays = 0;

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $date = $day->toDateString();
            $offDay = in_array($day->dayOfWeek, $weekends, true) || in_array($date, $holidays, true);
            if (!$offDay) $workingDays++;
            if ($day->isFuture()) continue;
            if (!$offDay) $elapsedDays++;
            $rows = $attendance->get($date, collect());
            $dayLeaves = $leaves->filter(fn ($leave) => substr($leave->leave_from, 0, 10) <= $date && substr($leave->leave_to, 0, 10) >= $date)
                ->map(fn ($leave) => ['status' => $leave->status])->values()->all();
            $dayTimeLeaves = $timeLeaves->get($date, collect());
            if ($first = $rows->first()) {
                if ($this->hasLateExcuse($dayLeaves, $dayTimeLeaves)) {
                    if ($this->lateMinutesForAttendance($employee, $first) !== null) {
                        $excused++;
                        if ($this->hasLateExcuse($dayLeaves, $dayTimeLeaves, ['approved'])) $approvedLate++;
                    }
                } else {
                    $breakdown = $this->addLateBreakdownCount($breakdown, $employee, $first);
                }
            }
            $bonus = $this->bonusDay($employee, $date, $rows, $dayLeaves, $dayTimeLeaves, $offDay ? 'Non-working day' : null);
            $eligible += $bonus['value'];
        }

        $controls = $this->monthlyBonusControls();
        return [
            'available' => true,
            'month' => $now->format('Y-m'),
            'as_of' => $now->toIso8601String(),
            'late_count' => array_sum($breakdown),
            'excused_late_count' => $excused,
            'approved_late_count' => $approvedLate,
            // Count dates, so multiple approved requests on one day count once.
            'approved_time_leave_count' => $timeLeaves
                ->filter(fn ($requests) => $requests->contains(fn ($leave) => $leave->status === 'approved'))->count(),
            'deduction' => [
                'amount' => round($this->lateReductionPayment($breakdown), 2),
                'currency' => 'USD',
                'rule' => 'monthly_attendance_minutes',
                'estimated' => true,
            ],
            'bonus' => [
                'target_amount' => $controls['amount'],
                'currency' => 'USD',
                'completed_days' => $eligible,
                'working_days' => $workingDays,
                'elapsed_working_days' => $elapsedDays,
                'progress_percent' => $workingDays ? round($eligible / $workingDays * 100, 1) : 0,
                'estimated' => true,
            ],
        ];
    }
}
