<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\TimeLeave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Read-only department ranking based on the monthly attendance bonus rules. */
class DepartmentMonthlyBonusLeaderboard
{
    use MonthlyAttendanceRules;

    public function forEmployeeDepartment(User $viewer, int $limit = 3): array
    {
        if (!$viewer->company_id || !$viewer->department_id) {
            return [];
        }

        $employees = User::query()
            ->with('officeTime')
            ->where('company_id', $viewer->company_id)
            ->where('department_id', $viewer->department_id)
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'english_name', 'avatar', 'employee_code', 'company_id', 'department_id', 'office_time_id']);

        if ($employees->isEmpty()) {
            return [];
        }

        $now = now();
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();
        $today = $now->copy()->startOfDay();
        $userIds = $employees->pluck('id')->all();
        $weekendValue = DB::table('companies')->where('id', $viewer->company_id)->value('weekend');
        if ($weekendValue === null) {
            return [];
        }
        $weekendDays = array_map('intval', is_array($weekendValue)
            ? $weekendValue
            : (json_decode((string) $weekendValue, true) ?: []));
        $holidays = Holiday::query()
            ->where('company_id', $viewer->company_id)
            ->where('is_active', 1)
            ->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])
            ->pluck('event_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        $attendanceByDate = Attendance::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('attendance_date', [$start->toDateString(), $today->toDateString()])
            ->orderBy('check_in_at')
            ->get(['user_id', 'attendance_date', 'check_in_at', 'check_out_at', 'night_checkin', 'night_checkout', 'attendance_status'])
            ->groupBy(fn ($row) => $row->user_id . '|' . Carbon::parse($row->attendance_date)->toDateString());

        $leaveByDate = [];
        $leaves = DB::table('leave_requests_master')
            ->whereIn('requested_by', $userIds)
            ->whereIn('status', ['pending', 'review', 'approved'])
            ->where('early_exit', 0)
            ->whereDate('leave_from', '<=', $today->toDateString())
            ->whereDate('leave_to', '>=', $start->toDateString())
            ->get(['requested_by', 'leave_from', 'leave_to', 'status']);
        foreach ($leaves as $leave) {
            $from = Carbon::parse($leave->leave_from)->max($start);
            $to = Carbon::parse($leave->leave_to)->min($today);
            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                $leaveByDate[$leave->requested_by . '|' . $date->toDateString()][] = [
                    'status' => $leave->status,
                ];
            }
        }

        $timeLeaveByDate = TimeLeave::query()
            ->whereIn('requested_by', $userIds)
            ->whereIn('status', ['pending', 'review', 'approved'])
            ->whereBetween('issue_date', [$start->toDateString(), $today->toDateString()])
            ->get(['requested_by', 'issue_date', 'status'])
            ->groupBy(fn ($row) => $row->requested_by . '|' . Carbon::parse($row->issue_date)->toDateString());

        $workingDays = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (!in_array($date->dayOfWeek, $weekendDays, true) && !$holidays->has($date->toDateString())) {
                $workingDays++;
            }
        }
        if ($workingDays < 1) {
            return [];
        }

        $ranking = $employees->map(function (User $employee) use (
            $start,
            $today,
            $weekendDays,
            $holidays,
            $attendanceByDate,
            $leaveByDate,
            $timeLeaveByDate,
            $workingDays
        ) {
            $completedDays = 0;
            for ($date = $start->copy(); $date->lte($today); $date->addDay()) {
                $dateKey = $date->toDateString();
                $key = $employee->id . '|' . $dateKey;
                $offDay = in_array($date->dayOfWeek, $weekendDays, true) || $holidays->has($dateKey);
                $dayAttendance = $attendanceByDate->get($key, collect());
                $dayTimeLeaves = $timeLeaveByDate->get($key, collect());
                $bonusDay = $this->bonusDay(
                    $employee,
                    $dateKey,
                    $dayAttendance,
                    $leaveByDate[$key] ?? [],
                    $dayTimeLeaves,
                    $offDay ? 'Non-working day' : null
                );
                if ($bonusDay['value'] === 1) {
                    $completedDays++;
                }
            }

            return [
                'id' => $employee->id,
                'name' => $employee->english_name ?: $employee->name,
                'avatar' => $employee->avatar
                    ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar)
                    : asset('assets/images/img.png'),
                'employee_code' => $employee->employee_code ?: '',
                'completed_days' => $completedDays,
                'working_days' => $workingDays,
                'progress_percent' => round(($completedDays / $workingDays) * 100, 1),
            ];
        });

        return $ranking
            ->filter(fn (array $row) => $row['completed_days'] > 0)
            ->sort(fn (array $a, array $b) =>
                ($b['completed_days'] <=> $a['completed_days']) ?: strcasecmp($a['name'], $b['name']))
            ->take(max(1, min($limit, 5)))
            ->values()
            ->all();
    }
}
