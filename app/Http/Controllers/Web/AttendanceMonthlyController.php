<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\LeaveRequestMaster;
use App\Models\TimeLeave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceMonthlyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeMonthlyAttendance();

        $month = $this->resolveMonth((string) $request->query('month', now()->format('Y-m')));
        $filter = [
            'month' => $month->format('Y-m'),
            'branch_id' => $request->query('branch_id'),
            'department_id' => $request->query('department_id'),
            'post_id' => $request->query('post_id'),
            'shift_id' => $request->query('shift_id'),
            'status' => $request->query('status'),
            'search' => trim((string) $request->query('search', '')),
            'per_page' => (int) $request->query('per_page', 25),
        ];

        $filter['per_page'] = in_array($filter['per_page'], [10, 25, 50, 100], true) ? $filter['per_page'] : 25;

        $employees = $this->filteredEmployees($filter)->get();
        $rows = $this->buildRows($employees, $month);

        if (in_array($filter['status'], ['present', 'late', 'absent', 'leave', 'off_day'], true)) {
            $rows = $rows->filter(fn ($row) => ($row['totals'][$filter['status']] ?? 0) > 0)->values();
        }

        $summary = $this->summary($rows);

        if ($request->query('export') === 'csv') {
            $this->authorizeMonthlyAttendanceExport();

            return $this->exportCsv($rows, $month);
        }

        $monthlyRows = $this->paginateRows($rows, $filter['per_page']);
        $calendarDays = $this->calendarDays($month);
        $branches = Branch::query()
            ->where('company_id', AppHelper::getAuthUserCompanyId())
            ->where('is_active', Branch::IS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name']);
        $departments = Department::query()
            ->where('company_id', AppHelper::getAuthUserCompanyId())
            ->where('is_active', Department::IS_ACTIVE)
            ->when($filter['branch_id'], fn ($query) => $query->where('branch_id', $filter['branch_id']))
            ->orderBy('dept_name')
            ->get(['id', 'dept_name', 'branch_id']);
        return view('admin.attendance.monthly', compact(
            'monthlyRows',
            'calendarDays',
            'summary',
            'filter',
            'month',
            'branches',
            'departments'
        ));
    }

    private function authorizeMonthlyAttendance(): void
    {
        if (auth('admin')->check() || Gate::allows('list_attendance')) {
            return;
        }

        abort(403);
    }

    private function authorizeMonthlyAttendanceExport(): void
    {
        if (auth('admin')->check() || Gate::allows('attendance_csv_export')) {
            return;
        }

        abort(403);
    }

    private function filteredEmployees(array $filter)
    {
        return User::query()
            ->with([
                'branch:id,name',
                'department:id,dept_name',
                'post:id,post_name',
                'officeTime:id,shift,opening_time,closing_time',
            ])
            ->select(['id', 'name', 'employee_code', 'username', 'avatar', 'company_id', 'branch_id', 'department_id', 'post_id', 'office_time_id'])
            ->where('company_id', AppHelper::getAuthUserCompanyId())
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->when($filter['branch_id'], fn ($query) => $query->where('branch_id', $filter['branch_id']))
            ->when($filter['department_id'], fn ($query) => $query->where('department_id', $filter['department_id']))
            ->when($filter['post_id'], fn ($query) => $query->where('post_id', $filter['post_id']))
            ->when($filter['shift_id'], fn ($query) => $query->where('office_time_id', $filter['shift_id']))
            ->when($filter['search'], function ($query) use ($filter) {
                $search = '%' . $filter['search'] . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('employee_code', 'like', $search)
                        ->orWhere('username', 'like', $search);
                });
            })
            ->orderBy('name');
    }

    private function buildRows(Collection $employees, Carbon $month): Collection
    {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        $userIds = $employees->pluck('id')->all();

        $attendanceByUserDate = Attendance::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('check_in_at')
            ->get(['id', 'user_id', 'attendance_date', 'check_in_at', 'night_checkin', 'attendance_status'])
            ->groupBy(fn ($attendance) => $attendance->user_id . '|' . Carbon::parse($attendance->attendance_date)->format('Y-m-d'));

        $leaveByUserDate = $this->leaveMap($userIds, $startDate, $endDate);
        $nonAttendanceReasons = $this->nonAttendanceReasonMap($startDate, $endDate);
        $timeLeaveByUserDate = TimeLeave::query()
            ->whereIn('requested_by', $userIds)
            ->where('status', 'approved')
            ->whereBetween('issue_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get(['id', 'requested_by', 'issue_date', 'start_time', 'end_time'])
            ->keyBy(fn ($leave) => $leave->requested_by . '|' . Carbon::parse($leave->issue_date)->format('Y-m-d'));

        return $employees->map(function (User $employee) use ($month, $attendanceByUserDate, $leaveByUserDate, $timeLeaveByUserDate, $nonAttendanceReasons) {
            $days = [];
            $totals = ['present' => 0, 'late' => 0, 'absent' => 0, 'leave' => 0, 'off_day' => 0];

            foreach ($this->calendarDays($month) as $day) {
                $key = $employee->id . '|' . $day['date'];
                $cell = $this->statusCell(
                    $employee,
                    $day['date'],
                    $attendanceByUserDate->get($key, collect()),
                    $leaveByUserDate[$key] ?? null,
                    $timeLeaveByUserDate->get($key),
                    $nonAttendanceReasons[$day['date']] ?? null
                );

                if (isset($totals[$cell['status']])) {
                    $totals[$cell['status']]++;
                }

                $days[] = $cell + $day;
            }

            return [
                'employee' => $employee,
                'days' => $days,
                'totals' => $totals,
                'total_days' => count($days),
            ];
        });
    }

    private function leaveMap(array $userIds, Carbon $startDate, Carbon $endDate): array
    {
        $leaveMap = [];
        $leaveRequests = LeaveRequestMaster::query()
            ->with('leaveType:id,name')
            ->whereIn('requested_by', $userIds)
            ->where('status', 'approved')
            ->where('early_exit', 0)
            ->whereDate('leave_from', '<=', $endDate->toDateString())
            ->whereDate('leave_to', '>=', $startDate->toDateString())
            ->get(['id', 'requested_by', 'leave_type_id', 'leave_from', 'leave_to']);

        foreach ($leaveRequests as $leave) {
            $from = Carbon::parse($leave->leave_from)->max($startDate);
            $to = Carbon::parse($leave->leave_to)->min($endDate);

            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                $leaveMap[$leave->requested_by . '|' . $date->format('Y-m-d')] = [
                    'type' => str_contains(strtolower((string) $leave->leaveType?->name), 'day off') ? 'off_day' : 'leave',
                    'label' => $leave->leaveType?->name ?: 'Leave',
                ];
            }
        }

        return $leaveMap;
    }

    private function nonAttendanceReasonMap(Carbon $startDate, Carbon $endDate): array
    {
        $companyId = AppHelper::getAuthUserCompanyId();
        $reasons = [];

        $holidays = Holiday::query()
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->whereBetween('event_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get(['event', 'event_date'])
            ->keyBy(fn ($holiday) => Carbon::parse($holiday->event_date)->format('Y-m-d'));

        $companyWeekend = Company::query()->where('id', $companyId)->value('weekend');
        $weekendDays = collect(is_array($companyWeekend) ? $companyWeekend : json_decode((string) $companyWeekend, true))
            ->filter(fn ($day) => $day !== null && $day !== '')
            ->map(fn ($day) => (int) $day)
            ->all();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');

            if ($holidays->has($dateKey)) {
                $reasons[$dateKey] = 'Holiday (' . ucfirst((string) $holidays[$dateKey]->event) . ')';
                continue;
            }

            if (in_array((int) $date->format('w'), $weekendDays, true)) {
                $reasons[$dateKey] = 'Weekend';
            }
        }

        return $reasons;
    }

    private function statusCell(User $employee, string $date, Collection $attendances, ?array $leave, ?TimeLeave $timeLeave, ?string $nonAttendanceReason): array
    {
        if ($leave) {
            return ['status' => $leave['type'], 'label' => $leave['label']];
        }

        if ($timeLeave) {
            return ['status' => 'leave', 'label' => 'Time Leave'];
        }

        if ($attendances->isNotEmpty()) {
            $firstAttendance = $attendances->first();
            if (!is_null($firstAttendance->attendance_status) && (int) $firstAttendance->attendance_status === Attendance::ATTENDANCE_REJECTED) {
                return ['status' => 'absent', 'label' => 'Rejected'];
            }

            $checkIn = $firstAttendance->check_in_at ?: $firstAttendance->night_checkin;
            $openingTime = $employee->officeTime?->opening_time;

            if ($checkIn && $openingTime && Carbon::parse($checkIn)->format('H:i:s') > Carbon::parse($openingTime)->format('H:i:s')) {
                return ['status' => 'late', 'label' => 'Late'];
            }

            return ['status' => 'present', 'label' => 'Present'];
        }

        if (Carbon::parse($date)->isFuture()) {
            return ['status' => 'empty', 'label' => 'Upcoming'];
        }

        if ($nonAttendanceReason) {
            return ['status' => 'off_day', 'label' => $nonAttendanceReason];
        }

        return ['status' => 'absent', 'label' => 'Absent'];
    }

    private function calendarDays(Carbon $month): array
    {
        $days = [];
        for ($day = $month->copy()->startOfMonth(); $day->lte($month->copy()->endOfMonth()); $day->addDay()) {
            $days[] = [
                'date' => $day->format('Y-m-d'),
                'day' => (int) $day->format('j'),
                'weekday' => $day->format('D'),
                'is_weekend' => $day->isWeekend(),
            ];
        }

        return $days;
    }

    private function summary(Collection $rows): array
    {
        $totals = [
            'employees' => $rows->count(),
            'present' => $rows->sum(fn ($row) => $row['totals']['present']),
            'late' => $rows->sum(fn ($row) => $row['totals']['late']),
            'absent' => $rows->sum(fn ($row) => $row['totals']['absent']),
            'leave' => $rows->sum(fn ($row) => $row['totals']['leave']),
            'off_day' => $rows->sum(fn ($row) => $row['totals']['off_day']),
        ];
        $workable = max($totals['present'] + $totals['late'] + $totals['absent'] + $totals['leave'], 1);

        return $totals + [
            'present_rate' => round((($totals['present'] + $totals['late']) / $workable) * 100, 2),
            'late_rate' => round(($totals['late'] / $workable) * 100, 2),
            'absent_rate' => round(($totals['absent'] / $workable) * 100, 2),
            'leave_rate' => round(($totals['leave'] / $workable) * 100, 2),
        ];
    }

    private function paginateRows(Collection $rows, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($items, $rows->count(), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    private function exportCsv(Collection $rows, Carbon $month): StreamedResponse
    {
        $filename = 'monthly-attendance-' . $month->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($rows, $month) {
            $handle = fopen('php://output', 'w');
            $headers = ['Employee Code', 'Employee', 'Department', 'Position'];
            foreach ($this->calendarDays($month) as $day) {
                $headers[] = $day['day'];
            }
            $headers = array_merge($headers, ['Present', 'Late', 'Absent', 'Leave', 'Off Day', 'Total']);
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                $employee = $row['employee'];
                $line = [
                    $employee->employee_code ?: $employee->username,
                    $employee->name,
                    $employee->department?->dept_name,
                    $employee->post?->post_name,
                ];
                foreach ($row['days'] as $day) {
                    $line[] = $day['label'];
                }
                fputcsv($handle, array_merge($line, [
                    $row['totals']['present'],
                    $row['totals']['late'],
                    $row['totals']['absent'],
                    $row['totals']['leave'],
                    $row['totals']['off_day'],
                    $row['total_days'],
                ]));
            }

            fclose($handle);
        }, $filename);
    }

    private function resolveMonth(string $month): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }
}
