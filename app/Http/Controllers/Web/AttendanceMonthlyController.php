<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Exports\MonthlyAttendanceReductionExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\LeaveRequestMaster;
use App\Models\OfficeTime;
use App\Models\TimeLeave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceMonthlyController extends Controller
{
    public const LATE_CHECK_IN_GRACE_MINUTES = 16;
    private const LATE_WARNING_THRESHOLD = 4;
    private const LATE_REDUCTION_RATES = [
        self::LATE_CHECK_IN_GRACE_MINUTES => 0.10,
        20 => 0.20,
        30 => 0.30,
        40 => 0.40,
        50 => 0.50,
        60 => 1.00,
        120 => 5.00,
    ];

    public function index(Request $request)
    {
        $this->authorizeMonthlyAttendance();

        $month = $this->resolveMonth((string) $request->query('month', now()->format('Y-m')));
        $filter = [
            'month' => $month->format('Y-m'),
            'branch_id' => $request->query('branch_id'),
            'department_id' => $request->query('department_id'),
            'shift_id' => $request->query('shift_id'),
            'search' => trim((string) $request->query('search', '')),
            'per_page' => (int) $request->query('per_page', 25),
        ];

        $filter['per_page'] = in_array($filter['per_page'], [10, 25, 50, 100], true) ? $filter['per_page'] : 25;

        $employees = $this->filteredEmployees($filter)->get();
        $rows = $this->buildRows($employees, $month);

        $summary = $this->summary($rows);

        if ($request->query('export') === 'csv') {
            $this->authorizeMonthlyAttendanceExport();

            return $this->exportCsv($rows, $month);
        }

        if ($request->query('export') === 'reduc_xlsx') {
            $this->authorizeMonthlyAttendanceExport();

            return $this->exportReductionXlsx($rows, $month);
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
        $shifts = OfficeTime::query()
            ->where('company_id', AppHelper::getAuthUserCompanyId())
            ->where('is_active', 1)
            ->when($filter['branch_id'], fn ($query) => $query->where('branch_id', $filter['branch_id']))
            ->orderBy('shift')
            ->get(['id', 'shift', 'opening_time', 'closing_time', 'branch_id']);

        return view('admin.attendance.monthly', compact(
            'monthlyRows',
            'calendarDays',
            'summary',
            'filter',
            'month',
            'branches',
            'departments',
            'shifts'
        ));
    }

    private function authorizeMonthlyAttendance(): void
    {
        if (auth('admin')->check() || Gate::any(['list_attendance', 'list_monthly_attendance'])) {
            return;
        }

        abort(403);
    }

    private function authorizeMonthlyAttendanceExport(): void
    {
        if (auth('admin')->check() || Gate::any(['attendance_csv_export', 'monthly_attendance_csv_export'])) {
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
                'officeTime:id,shift,opening_time,closing_time,is_early_check_in,checkin_before,is_early_check_out,checkout_before,is_late_check_in,checkin_after,is_late_check_out,checkout_after',
            ])
            ->select(['id', 'name', 'email', 'employee_code', 'username', 'avatar', 'phone', 'company_id', 'branch_id', 'department_id', 'post_id', 'office_time_id', 'online_status'])
            ->where('company_id', AppHelper::getAuthUserCompanyId())
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->when($filter['branch_id'], fn ($query) => $query->where('branch_id', $filter['branch_id']))
            ->when($filter['department_id'], fn ($query) => $query->where('department_id', $filter['department_id']))
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
            ->get(['id', 'user_id', 'attendance_date', 'check_in_at', 'check_out_at', 'night_checkin', 'night_checkout', 'attendance_status'])
            ->groupBy(fn ($attendance) => $attendance->user_id . '|' . Carbon::parse($attendance->attendance_date)->format('Y-m-d'));

        $leaveByUserDate = $this->leaveMap($userIds, $startDate, $endDate);
        $nonAttendanceReasons = $this->nonAttendanceReasonMap($startDate, $endDate);
        $timeLeaveByUserDate = TimeLeave::query()
            ->whereIn('requested_by', $userIds)
            ->whereIn('status', ['pending', 'approved', 'review'])
            ->whereBetween('issue_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get(['id', 'requested_by', 'issue_date', 'start_time', 'end_time', 'reasons', 'admin_remark', 'status'])
            ->groupBy(fn ($leave) => $leave->requested_by . '|' . Carbon::parse($leave->issue_date)->format('Y-m-d'));
        $approvedLateRequestCounts = $timeLeaveByUserDate
            ->flatten(1)
            ->where('status', 'approved')
            ->groupBy('requested_by')
            ->map(fn (Collection $requests) => $requests->count());

        return $employees->map(function (User $employee) use ($month, $attendanceByUserDate, $leaveByUserDate, $timeLeaveByUserDate, $approvedLateRequestCounts, $nonAttendanceReasons) {
            $days = [];
            $totals = ['present' => 0, 'late' => 0, 'absent' => 0, 'leave' => 0, 'off_day' => 0];
            $lateBreakdown = $this->lateBreakdownTemplate();
            $lateMinutesTotal = 0;
            $signalTotals = [
                'pending_day_off' => 0,
                'pending_leave' => 0,
                'time_leave' => 0,
                'time_leave_request' => 0,
                'no_checkout' => 0,
            ];

            foreach ($this->calendarDays($month) as $day) {
                $key = $employee->id . '|' . $day['date'];
                $dayAttendances = $attendanceByUserDate->get($key, collect());
                $dayTimeLeaves = $timeLeaveByUserDate->get($key, collect());
                $cell = $this->statusCell(
                    $employee,
                    $day['date'],
                    $dayAttendances,
                    $leaveByUserDate[$key] ?? null,
                    $dayTimeLeaves,
                    $nonAttendanceReasons[$day['date']] ?? null
                );

                if (isset($totals[$cell['status']])) {
                    $totals[$cell['status']]++;
                }

                if ($dayAttendances->isNotEmpty()) {
                    $firstAttendance = $dayAttendances->first();
                    $lateMinutes = $this->lateMinutesForAttendance($employee, $firstAttendance);
                    $lateBreakdown = $this->addLateBreakdownCount($lateBreakdown, $employee, $firstAttendance);
                    if ($lateMinutes !== null) {
                        $lateMinutesTotal += $lateMinutes;
                    }
                }

                foreach ($cell['indicators'] ?? [] as $indicator) {
                    match ($indicator['short'] ?? null) {
                        'PO' => $signalTotals['pending_day_off']++,
                        'PL' => $signalTotals['pending_leave']++,
                        'TL' => $signalTotals['time_leave']++,
                        'TR' => $signalTotals['time_leave_request']++,
                        'NC' => $signalTotals['no_checkout']++,
                        default => null,
                    };
                }

                $days[] = $cell + $day + [
                    'detail_url' => route('admin.attendances.index', [
                        'attendance_date' => $day['date'],
                        'search' => $employee->username ?: ($employee->employee_code ?: $employee->name),
                    ]),
                ];
            }

            $totalLateRecords = array_sum($lateBreakdown);
            $approvedLateRequests = (int) ($approvedLateRequestCounts[$employee->id] ?? 0);

            return [
                'employee' => $employee,
                'days' => $days,
                'totals' => $totals,
                'late_breakdown' => $lateBreakdown,
                'late_minutes_total' => $lateMinutesTotal,
                'total_late_records' => $totalLateRecords,
                'approved_late_requests' => $approvedLateRequests,
                'effective_late_count' => max($totalLateRecords - $approvedLateRequests, 0),
                'signal_totals' => $signalTotals,
                'total_days' => count($days),
            ];
        });
    }

    private function lateBreakdownTemplate(): array
    {
        return [
            self::LATE_CHECK_IN_GRACE_MINUTES => 0,
            20 => 0,
            30 => 0,
            40 => 0,
            50 => 0,
            60 => 0,
            120 => 0,
        ];
    }

    private function addLateBreakdownCount(array $lateBreakdown, User $employee, Attendance $attendance): array
    {
        $lateMinutes = $this->lateMinutesForAttendance($employee, $attendance);
        if ($lateMinutes === null) {
            return $lateBreakdown;
        }

        if ($lateMinutes < 20) {
            $lateBreakdown[self::LATE_CHECK_IN_GRACE_MINUTES]++;
        } elseif ($lateMinutes < 30) {
            $lateBreakdown[20]++;
        } elseif ($lateMinutes < 40) {
            $lateBreakdown[30]++;
        } elseif ($lateMinutes < 50) {
            $lateBreakdown[40]++;
        } elseif ($lateMinutes < 60) {
            $lateBreakdown[50]++;
        } elseif ($lateMinutes > 120) {
            $lateBreakdown[120]++;
        } else {
            $lateBreakdown[60]++;
        }

        return $lateBreakdown;
    }

    private function lateMinutesForAttendance(User $employee, Attendance $attendance): ?int
    {
        if (!is_null($attendance->attendance_status) && (int) $attendance->attendance_status === Attendance::ATTENDANCE_REJECTED) {
            return null;
        }

        $shift = $employee->officeTime;
        $checkIn = $attendance->check_in_at ?: $attendance->night_checkin;

        if (!$shift?->opening_time || !$checkIn) {
            return null;
        }

        $lateMinutes = Carbon::parse($shift->opening_time)->diffInMinutes(Carbon::parse($checkIn), false);

        return $lateMinutes <= self::LATE_CHECK_IN_GRACE_MINUTES ? null : (int) $lateMinutes;
    }

    private function leaveMap(array $userIds, Carbon $startDate, Carbon $endDate): array
    {
        $leaveMap = [];
        $leaveRequests = LeaveRequestMaster::query()
            ->with('leaveType:id,name,slug')
            ->whereIn('requested_by', $userIds)
            ->whereIn('status', ['pending', 'approved', 'review'])
            ->where('early_exit', 0)
            ->whereDate('leave_from', '<=', $endDate->toDateString())
            ->whereDate('leave_to', '>=', $startDate->toDateString())
            ->get(['id', 'requested_by', 'leave_type_id', 'leave_from', 'leave_to', 'reasons', 'admin_remark', 'status']);

        foreach ($leaveRequests as $leave) {
            $from = Carbon::parse($leave->leave_from)->max($startDate);
            $to = Carbon::parse($leave->leave_to)->min($endDate);

            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                $leaveMap[$leave->requested_by . '|' . $date->format('Y-m-d')][] = [
                    'id' => $leave->id,
                    'status' => $leave->status,
                    'type' => str_contains(strtolower((string) $leave->leaveType?->name), 'day off') ? 'off_day' : 'leave',
                    'label' => $leave->leaveType?->name ?: 'Leave',
                    'pay_type' => $this->leavePayType($leave->leaveType?->name, $leave->leaveType?->slug),
                    'from' => Carbon::parse($leave->leave_from)->format('Y-m-d'),
                    'to' => Carbon::parse($leave->leave_to)->format('Y-m-d'),
                    'reason' => strip_tags((string) $leave->reasons),
                    'admin_remark' => (string) $leave->admin_remark,
                    'update_url' => route('admin.leave-request.update-status', $leave->id),
                    'approvers_url' => route('admin.leave-request.approval-details', $leave->id),
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

    private function statusCell(User $employee, string $date, Collection $attendances, ?array $leaveRequests, Collection $timeLeaves, ?string $nonAttendanceReason): array
    {
        $leaveRequests = $leaveRequests ?? [];
        $indicators = $this->statusIndicators($leaveRequests, $timeLeaves);
        $approvedLeave = collect($leaveRequests ?? [])->firstWhere('status', 'approved');
        $approvedTimeLeave = $timeLeaves->firstWhere('status', 'approved');
        $details = $this->dayDetails($employee, $date, $attendances, $leaveRequests, $timeLeaves, $nonAttendanceReason);
        $actions = $this->dayActions($leaveRequests, $timeLeaves);
        $canQuickLeave = empty($leaveRequests);
        $canQuickTimeLeave = empty($leaveRequests) && $timeLeaves->isEmpty();

        if ($attendances->isNotEmpty()) {
            $firstAttendance = $attendances->first();
            $openCheckout = $this->hasOpenCheckout($attendances);
            $attendanceIndicators = $openCheckout
                ? array_merge($indicators, [[
                    'type' => 'open-checkout',
                    'short' => 'NC',
                    'label' => 'Checked in but not checked out',
                ]])
                : $indicators;

            if (!is_null($firstAttendance->attendance_status) && (int) $firstAttendance->attendance_status === Attendance::ATTENDANCE_REJECTED) {
                return $this->cell('absent', 'Rejected', $attendanceIndicators, $details, $actions, false, $canQuickTimeLeave);
            }

            $checkIn = $firstAttendance->check_in_at ?: $firstAttendance->night_checkin;
            $checkInLabel = $checkIn ? ' - In ' . Carbon::parse($checkIn)->format('H:i') : '';
            $timingRules = $this->attendanceTimingRules($employee, $firstAttendance);
            $timingDetails = $this->attendanceTimingRuleDetails($timingRules);
            if ($timingDetails !== []) {
                $details = array_merge($details, $timingDetails);
            }

            if ($timingRules['late_check_in']) {
                $lateLabel = ($openCheckout ? 'Late - no checkout' : 'Late') . $checkInLabel;
                if ($timingRules['late_check_in_allowed']) {
                    $lateLabel .= ' (Allowed ' . $timingRules['late_check_in_allowed'] . ')';
                }

                return $this->cell('late', $lateLabel, $attendanceIndicators, $details, $actions, false, $canQuickTimeLeave);
            }

            return $this->cell('present', ($openCheckout ? 'Present - no checkout' : 'Present') . $checkInLabel, $attendanceIndicators, $details, $actions, false, $canQuickTimeLeave);
        }

        if ($approvedLeave) {
            $label = $this->leaveDisplayLabel($approvedLeave);

            return $this->cell($approvedLeave['type'], $label, $indicators, $details, $actions, false, false);
        }

        if ($approvedTimeLeave) {
            return $this->cell('leave', 'Approved Time Leave', $indicators, $details, $actions, false, false);
        }

        if (Carbon::parse($date)->isFuture()) {
            return $this->cell('empty', 'Upcoming', $indicators, $details, $actions, false, false);
        }

        if ($nonAttendanceReason) {
            return $this->cell('off_day', $nonAttendanceReason, $indicators, $details, $actions, $canQuickLeave, $canQuickTimeLeave);
        }

        return $this->cell('absent', 'Absent', $indicators, $details, $actions, $canQuickLeave, $canQuickTimeLeave);
    }

    private function cell(string $status, string $label, array $indicators = [], array $details = [], array $actions = [], bool $canQuickLeave = true, bool $canQuickTimeLeave = true): array
    {
        $tooltipParts = array_filter(array_merge([$label], array_column($indicators, 'label')));

        return [
            'status' => $status,
            'label' => $label,
            'indicators' => $indicators,
            'details' => $details ?: [$label],
            'actions' => $actions,
            'can_quick_leave' => $canQuickLeave,
            'can_quick_time_leave' => $canQuickTimeLeave,
            'tooltip' => implode(' | ', $tooltipParts),
        ];
    }

    private function dayDetails(User $employee, string $date, Collection $attendances, array $leaveRequests, Collection $timeLeaves, ?string $nonAttendanceReason): array
    {
        $details = [
            'Date: ' . Carbon::parse($date)->format('M d, Y'),
            'Employee: ' . $employee->name . ($employee->username ? ' (' . $employee->username . ')' : ''),
        ];

        foreach ($attendances as $attendance) {
            $checkIn = $attendance->check_in_at ?: $attendance->night_checkin;
            $checkOut = $attendance->check_out_at ?: $attendance->night_checkout;
            $details[] = 'Attendance: Check in ' . ($checkIn ?: 'N/A') . ', check out ' . ($checkOut ?: 'No checkout');
        }

        foreach ($leaveRequests as $leave) {
            $details[] = ucfirst($leave['status']) . ' ' . $this->leaveDisplayLabel($leave)
                . ' from ' . $leave['from'] . ' to ' . $leave['to']
                . ($leave['reason'] ? ' - ' . $leave['reason'] : '');
        }

        foreach ($timeLeaves as $timeLeave) {
            $details[] = ucfirst((string) $timeLeave->status) . ' time leave '
                . ($timeLeave->start_time ?: 'N/A') . ' - ' . ($timeLeave->end_time ?: 'N/A')
                . ($timeLeave->reasons ? ' - ' . strip_tags((string) $timeLeave->reasons) : '');
        }

        if ($nonAttendanceReason) {
            $details[] = 'Calendar note: ' . $nonAttendanceReason;
        }

        return $details;
    }

    private function dayActions(array $leaveRequests, Collection $timeLeaves): array
    {
        $actions = [];

        foreach ($leaveRequests as $leave) {
            if (!in_array($leave['status'], ['pending', 'review'], true)) {
                continue;
            }

            $actions[] = [
                'type' => 'leave',
                'id' => $leave['id'],
                'title' => ($leave['type'] === 'off_day' ? 'Day Off' : $this->leaveDisplayLabel($leave)) . ' Request',
                'reason' => $leave['reason'] ?: 'N/A',
                'remark' => $leave['admin_remark'] ?: '',
                'update_url' => $leave['update_url'],
                'approvers_url' => $leave['approvers_url'],
            ];
        }

        foreach ($timeLeaves as $timeLeave) {
            if (!in_array($timeLeave->status, ['pending', 'review'], true)) {
                continue;
            }

            $actions[] = [
                'type' => 'time_leave',
                'id' => $timeLeave->id,
                'title' => 'Time Leave Request',
                'reason' => strip_tags((string) $timeLeave->reasons) ?: 'N/A',
                'remark' => (string) $timeLeave->admin_remark,
                'update_url' => route('admin.time-leave-request.update-status', $timeLeave->id),
                'approvers_url' => null,
            ];
        }

        return $actions;
    }

    private function statusIndicators(array $leaveRequests, Collection $timeLeaves): array
    {
        $indicators = [];

        foreach ($leaveRequests as $leave) {
            $isApproved = $leave['status'] === 'approved';
            $isDayOff = ($leave['type'] ?? null) === 'off_day';
            $short = match (true) {
                $isApproved && $isDayOff => 'O',
                $isApproved => 'LV',
                $isDayOff => 'PO',
                default => 'PL',
            };

            $indicators[] = [
                'type' => $isApproved ? 'leave-approved' : 'leave-request',
                'short' => $short,
                'label' => ($isApproved ? 'Approved ' : 'Pending ') . ($isDayOff ? 'Day Off' : $this->leaveDisplayLabel($leave)),
            ];
        }

        foreach ($timeLeaves as $timeLeave) {
            $isApproved = $timeLeave->status === 'approved';
            $timeRange = trim(($timeLeave->start_time ?: '') . ' - ' . ($timeLeave->end_time ?: ''));
            $indicators[] = [
                'type' => $isApproved ? 'time-leave-approved' : 'time-leave-request',
                'short' => $isApproved ? 'TL' : 'TR',
                'label' => ($isApproved ? 'Approved Time Leave' : 'Pending Time Leave Request') . ($timeRange ? ' (' . $timeRange . ')' : ''),
            ];
        }

        return $indicators;
    }

    private function hasOpenCheckout(Collection $attendances): bool
    {
        return $attendances->contains(function (Attendance $attendance) {
            $hasRegularCheckIn = !empty($attendance->check_in_at);
            $hasNightCheckIn = !empty($attendance->night_checkin);

            return ($hasRegularCheckIn && empty($attendance->check_out_at))
                || ($hasNightCheckIn && empty($attendance->night_checkout));
        });
    }

    private function attendanceTimingRules(User $employee, Attendance $attendance): array
    {
        $shift = $employee->officeTime;
        $checkIn = $attendance->check_in_at ?: $attendance->night_checkin;
        $checkOut = $attendance->check_out_at ?: $attendance->night_checkout;
        $rules = [
            'early_check_in' => false,
            'early_check_in_allowed' => null,
            'late_check_in' => false,
            'late_check_in_allowed' => null,
            'early_check_out' => false,
            'early_check_out_allowed' => null,
            'late_check_out' => false,
            'late_check_out_allowed' => null,
        ];

        if (!$shift) {
            return $rules;
        }

        if ($checkIn && $shift->opening_time) {
            $checkInAt = Carbon::parse($checkIn);
            $openingTime = Carbon::parse($shift->opening_time);

            if ((int) $shift->is_early_check_in === 1 && $shift->checkin_before !== null) {
                $allowed = $openingTime->copy()->subMinutes((int) $shift->checkin_before);
                $rules['early_check_in'] = $checkInAt->lt($allowed);
                $rules['early_check_in_allowed'] = $allowed->format('H:i');
            }

            $manualLateGraceMinutes = self::LATE_CHECK_IN_GRACE_MINUTES;
            $allowedLateCheckIn = $openingTime->copy()->addMinutes($manualLateGraceMinutes);
            $rules['late_check_in'] = $checkInAt->gt($allowedLateCheckIn);
            $rules['late_check_in_allowed'] = $allowedLateCheckIn->format('H:i');
        }

        if ($checkOut && $shift->closing_time) {
            $checkOutAt = Carbon::parse($checkOut);
            $closingTime = Carbon::parse($shift->closing_time);

            if ((int) $shift->is_early_check_out === 1 && $shift->checkout_before !== null) {
                $allowed = $closingTime->copy()->subMinutes((int) $shift->checkout_before);
                $rules['early_check_out'] = $checkOutAt->lt($allowed);
                $rules['early_check_out_allowed'] = $allowed->format('H:i');
            }

            if ((int) $shift->is_late_check_out === 1 && $shift->checkout_after !== null) {
                $allowed = $closingTime->copy()->addMinutes((int) $shift->checkout_after);
                $rules['late_check_out'] = $checkOutAt->gt($allowed);
                $rules['late_check_out_allowed'] = $allowed->format('H:i');
            }
        }

        return $rules;
    }

    private function attendanceTimingRuleDetails(array $rules): array
    {
        $details = [];

        if ($rules['early_check_in']) {
            $details[] = 'Office Time: Early check-in before allowed time ' . $rules['early_check_in_allowed'];
        }

        if ($rules['late_check_in']) {
            $details[] = 'Office Time: Late check-in after allowed time ' . $rules['late_check_in_allowed'];
        }

        if ($rules['early_check_out']) {
            $details[] = 'Office Time: Early check-out before allowed time ' . $rules['early_check_out_allowed'];
        }

        if ($rules['late_check_out']) {
            $details[] = 'Office Time: Late check-out after allowed time ' . $rules['late_check_out_allowed'];
        }

        return $details;
    }

    private function leaveDisplayLabel(array $leave): string
    {
        $label = ucfirst((string) $leave['label']);
        $labelLower = strtolower($label);
        if (str_contains($labelLower, 'paid') || str_contains($labelLower, 'unpaid')) {
            return $label;
        }

        $payPrefix = match ($leave['pay_type'] ?? null) {
            'paid' => 'Paid ',
            'unpaid' => 'Unpaid ',
            default => '',
        };

        return $payPrefix . $label;
    }

    private function leavePayType(?string $name, ?string $slug): ?string
    {
        $text = strtolower(trim((string) $name . ' ' . (string) $slug));

        if (str_contains($text, 'unpaid')) {
            return 'unpaid';
        }

        if (str_contains($text, 'paid')) {
            return 'paid';
        }

        return null;
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
        $lateBreakdown = $this->lateBreakdownTemplate();
        foreach ($rows as $row) {
            foreach (($row['late_breakdown'] ?? []) as $minutes => $count) {
                $lateBreakdown[(int) $minutes] = ($lateBreakdown[(int) $minutes] ?? 0) + (int) $count;
            }
        }

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
            'late_breakdown' => $lateBreakdown,
            'late_breakdown_total' => array_sum($lateBreakdown),
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

    private function exportReductionXlsx(Collection $rows, Carbon $month)
    {
        $filename = 'monthly-attendance-reduc-' . $month->format('Y-m') . '.xlsx';

        return Excel::download(
            new MonthlyAttendanceReductionExport($this->reductionExportRows($rows, $month)),
            $filename
        );
    }

    private function reductionExportRows(Collection $rows, Carbon $month): Collection
    {
        $createdAt = now();
        $dateText = $createdAt->format('n/j/Y');
        $monthYear = $month->format('M-y');
        $companyEmail = (string) Company::query()
            ->where('id', AppHelper::getAuthUserCompanyId())
            ->value('email');
        $admin = auth('admin')->user();
        $webUser = auth()->user();
        $receiver = (string) ($admin?->name ?? $webUser?->name ?? 'Admin');

        return $rows
            ->map(function (array $row) use ($dateText, $month, $monthYear, $companyEmail, $receiver, $createdAt) {
                if (($row['effective_late_count'] ?? 0) < self::LATE_WARNING_THRESHOLD) {
                    return null;
                }

                $payment = $this->lateReductionPayment($row['late_breakdown'] ?? []);
                if ($payment <= 0) {
                    return null;
                }

                $employee = $row['employee'];
                $employeeId = $employee->employee_code ?: ($employee->username ?: (string) $employee->id);
                $number = $employeeId . '-' . $month->format('n-Y');
                $expenseType = 'កាត់';

                return [
                    $dateText,
                    $number,
                    $employeeId,
                    $employee->name,
                    $employee->username,
                    $employee->branch?->name,
                    $employee->phone,
                    $expenseType,
                    $this->lateReductionReason($row['late_breakdown'] ?? [], (int) ($row['approved_late_requests'] ?? 0), (int) ($row['effective_late_count'] ?? 0)),
                    round($payment, 2),
                    'Cash',
                    $receiver,
                    $monthYear,
                    $monthYear,
                    $number . $expenseType,
                    $employee->email,
                    $companyEmail,
                    $createdAt->format('n/j/Y H:i'),
                ];
            })
            ->filter()
            ->values();
    }

    private function lateReductionPayment(array $breakdown): float
    {
        $payment = 0;

        foreach (self::LATE_REDUCTION_RATES as $minutes => $rate) {
            $payment += ((int) ($breakdown[$minutes] ?? 0)) * $rate;
        }

        return $payment;
    }

    private function lateReductionReason(array $breakdown, int $approvedLateRequests = 0, ?int $effectiveLateCount = null): string
    {
        $parts = [];

        foreach (self::LATE_REDUCTION_RATES as $minutes => $rate) {
            $count = (int) ($breakdown[$minutes] ?? 0);
            if ($count <= 0) {
                continue;
            }

            $label = match ((int) $minutes) {
                self::LATE_CHECK_IN_GRACE_MINUTES => '16m',
                120 => '2h+',
                default => $minutes . 'm',
            };
            $parts[] = $label . ' x ' . $count;
        }

        $reason = 'Late check-in' . ($parts ? ' (' . implode(', ', $parts) . ')' : '');

        if ($approvedLateRequests > 0) {
            $reason .= ' - Approved late requests: ' . $approvedLateRequests;
        }

        if ($effectiveLateCount !== null) {
            $reason .= ' - Effective late count: ' . $effectiveLateCount;
        }

        return $reason;
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
            $headers = array_merge($headers, [
                'Present',
                'Late',
                'Absent',
                'Leave',
                'Off Day',
                'Total',
                'Pending Day Off',
                'Pending Leave',
                'Time Leave',
                'Time Leave Request',
                'No Checkout',
            ]);
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
                    $line[] = $day['tooltip'] ?? $day['label'];
                }
                fputcsv($handle, array_merge($line, [
                    $row['totals']['present'],
                    $row['totals']['late'],
                    $row['totals']['absent'],
                    $row['totals']['leave'],
                    $row['totals']['off_day'],
                    $row['total_days'],
                    $row['signal_totals']['pending_day_off'] ?? 0,
                    $row['signal_totals']['pending_leave'] ?? 0,
                    $row['signal_totals']['time_leave'] ?? 0,
                    $row['signal_totals']['time_leave_request'] ?? 0,
                    $row['signal_totals']['no_checkout'] ?? 0,
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
