<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\User;
use App\Support\AttendanceCountReduction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/** Shared rules for the admin monthly report and the employee home summary. */
trait MonthlyAttendanceRules
{
    public const LATE_CHECK_IN_GRACE_MINUTES = 16;
    private const LATE_WARNING_THRESHOLD = 4;
    private const MONTHLY_BONUS_AMOUNT_SETTING = 'monthly_attendance_bonus_amount';
    private const MONTHLY_BONUS_RULE_SETTINGS = [
        'require_check_in' => 'monthly_attendance_require_check_in',
        'require_check_out' => 'monthly_attendance_require_check_out',
        'require_no_late_check_in' => 'monthly_attendance_require_no_late_check_in',
        'require_no_early_check_out' => 'monthly_attendance_require_no_early_check_out',
        'require_no_early_check_in' => 'monthly_attendance_require_no_early_check_in',
    ];
    private const LATE_REDUCTION_RATES = [
        self::LATE_CHECK_IN_GRACE_MINUTES => 0.10,
        20 => 0.20,
        30 => 0.30,
        40 => 0.40,
        50 => 0.50,
        60 => 1.00,
        120 => 5.00,
    ];

    private function bonusDay(User $employee, string $date, Collection $attendances, ?array $leaveRequests, Collection $timeLeaves, ?string $nonAttendanceReason): array
    {
        $leaveRequests = $leaveRequests ?? [];
        $approvedLeave = collect($leaveRequests)->firstWhere('status', 'approved');
        $approvedTimeLeave = $timeLeaves->firstWhere('status', 'approved');
        $hasLeave = collect($leaveRequests)->isNotEmpty();
        $hasTimeLeave = $timeLeaves->isNotEmpty();
        $workingDay = !$nonAttendanceReason && !Carbon::parse($date)->isFuture();
        $validAttendances = $attendances->reject(fn (Attendance $attendance) => !is_null($attendance->attendance_status) && (int) $attendance->attendance_status === Attendance::ATTENDANCE_REJECTED);
        $firstAttendance = $validAttendances->first();
        $checkIns = $validAttendances
            ->map(fn (Attendance $attendance) => $attendance->check_in_at ?: $attendance->night_checkin)
            ->filter()
            ->sortBy(fn ($time) => Carbon::parse($time)->timestamp)
            ->values();
        $checkOuts = $validAttendances
            ->map(fn (Attendance $attendance) => $attendance->check_out_at ?: $attendance->night_checkout)
            ->filter()
            ->sortByDesc(fn ($time) => Carbon::parse($time)->timestamp)
            ->values();
        $checkIn = $checkIns->first();
        $checkOut = $checkOuts->first();
        $shift = $employee->officeTime;
        $checkInOnTime = false;
        $notEarlyCheckout = false;
        $lateStatus = 'Missing check-in';
        $checkoutStatus = 'Missing check-out';
        $reason = null;

        if (!$workingDay) {
            $reason = $nonAttendanceReason ?: 'Upcoming';
        } elseif ($hasLeave) {
            $reason = $approvedLeave ? 'Leave taken' : 'Leave request';
        } elseif ($hasTimeLeave) {
            $reason = $approvedTimeLeave ? 'Time leave taken' : 'Time leave request';
        } elseif (!$firstAttendance) {
            $reason = 'No approved attendance';
        } elseif (!$checkIn) {
            $reason = 'Missing check-in';
        } elseif (!$checkOut) {
            $reason = 'Missing check-out';
        } elseif (!$shift?->opening_time || !$shift?->closing_time) {
            $reason = 'Missing office time';
        } else {
            $openingAt = Carbon::parse($date . ' ' . $shift->opening_time);
            $closingAt = Carbon::parse($date . ' ' . $shift->closing_time);
            if ($closingAt->lte($openingAt)) {
                $closingAt->addDay();
            }

            $checkInAt = $this->attendanceMoment($date, $checkIn);
            $checkOutAt = $this->attendanceMoment($date, $checkOut);
            if ($checkOutAt->lt($checkInAt)) {
                $checkOutAt->addDay();
            }
            $checkInOnTime = $checkInAt->lte($openingAt);
            $notEarlyCheckout = $checkOutAt->gte($closingAt);
            $lateStatus = $checkInOnTime ? 'On time' : 'Late';
            $checkoutStatus = $notEarlyCheckout ? 'Full checkout' : 'Early checkout';

            if (!$checkInOnTime) {
                $reason = 'Late check-in';
            } elseif (!$notEarlyCheckout) {
                $reason = 'Early checkout';
            }
        }

        $eligible = $workingDay
            && !$hasLeave
            && !$hasTimeLeave
            && $firstAttendance
            && $checkIn
            && $checkOut
            && $shift?->opening_time
            && $shift?->closing_time
            && $checkInOnTime
            && $notEarlyCheckout;

        return [
            'value' => $eligible ? 1 : 0,
            'working_day' => $workingDay,
            'check_in' => $checkIn ? Carbon::parse($checkIn)->format('H:i') : 'N/A',
            'check_out' => $checkOut ? Carbon::parse($checkOut)->format('H:i') : 'N/A',
            'leave_status' => $hasTimeLeave ? 'Time leave' : ($hasLeave ? 'Leave' : 'No leave'),
            'late_status' => $lateStatus,
            'checkout_status' => $checkoutStatus,
            'reason' => $eligible ? 'Eligible' : ($reason ?: 'Not eligible'),
        ];
    }

    private function monthlyBonusControls(): array
    {
        $settings = AttendanceSetting::query()
            ->whereIn('slug', array_merge([self::MONTHLY_BONUS_AMOUNT_SETTING], array_values(self::MONTHLY_BONUS_RULE_SETTINGS)))
            ->get(['slug', 'value', 'status'])
            ->keyBy('slug');

        $rules = [];
        foreach (self::MONTHLY_BONUS_RULE_SETTINGS as $rule => $slug) {
            $rules[$rule] = (bool) ($settings[$slug]->status ?? true);
        }

        return [
            'amount' => (float) ($settings[self::MONTHLY_BONUS_AMOUNT_SETTING]->value ?? 20),
            'rules' => $rules,
        ];
    }

    private function attendanceMoment(string $date, string $time): Carbon
    {
        if (preg_match('/^\d{1,2}:\d{2}/', $time)) {
            return Carbon::parse($date . ' ' . $time);
        }

        return Carbon::parse($time);
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

        return $lateMinutes < self::LATE_CHECK_IN_GRACE_MINUTES ? null : (int) $lateMinutes;
    }

    private function countLateMinutesForAttendance(User $employee, Attendance $attendance): ?int
    {
        if (!is_null($attendance->attendance_status) && (int) $attendance->attendance_status === Attendance::ATTENDANCE_REJECTED) {
            return null;
        }

        $shift = $employee->officeTime;
        $checkIn = $attendance->check_in_at ?: $attendance->night_checkin;

        if (!$shift?->opening_time || !$checkIn) {
            return null;
        }

        $date = Carbon::parse($attendance->attendance_date)->format('Y-m-d');
        $openingAt = Carbon::parse($date . ' ' . $shift->opening_time);
        $checkInAt = $this->attendanceMoment($date, $checkIn);

        if ($shift->closing_time) {
            $closingAt = Carbon::parse($date . ' ' . $shift->closing_time);
            if ($closingAt->lte($openingAt) && $checkInAt->lt($openingAt)) {
                $checkInAt->addDay();
            }
        }

        $lateMinutes = (int) $openingAt->diffInMinutes($checkInAt, false);

        return $lateMinutes < AttendanceCountReduction::MIN_LATE_MINUTES ? null : $lateMinutes;
    }

    private function hasLateExcuse(?array $leaveRequests, Collection $timeLeaves, array $statuses = ['pending', 'review', 'approved']): bool
    {
        // Both collections have already been matched to this employee and date.
        // Multiple requests on the same day excuse that day only once.
        return collect($leaveRequests ?? [])->contains(fn ($leave) => in_array($leave['status'] ?? null, $statuses, true))
            || $timeLeaves->contains(fn ($leave) => in_array($leave->status, $statuses, true));
    }

    private function lateReductionPayment(array $breakdown): float
    {
        $payment = 0;

        foreach (self::LATE_REDUCTION_RATES as $minutes => $rate) {
            $payment += ((int) ($breakdown[$minutes] ?? 0)) * $rate;
        }

        if (array_sum($breakdown) >= self::LATE_WARNING_THRESHOLD) {
            $payment += AttendanceCountReduction::FINAL_WARNING_RATE;
        }

        return $payment;
    }

}
