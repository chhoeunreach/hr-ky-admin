<?php

namespace App\Services\Attendance;

use App\Helpers\AttendanceHelper;
use App\Models\TelegramGroup;
use App\Models\User;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceTelegramNotificationService
{
    public function __construct(protected TelegramService $telegramService)
    {
    }

    public function notify(string $type, User $user, mixed $attendance): void
    {
        try {
            $latitude = null;
            $longitude = null;
            $checkTimeRaw = null;

            if ($type === 'check_in') {
                $checkTimeRaw = $attendance->check_in_at ?? $attendance->night_checkin ?? null;
                $latitude = $attendance->check_in_latitude ?? null;
                $longitude = $attendance->check_in_longitude ?? null;
            } elseif ($type === 'check_out') {
                $checkTimeRaw = $attendance->check_out_at ?? $attendance->night_checkout ?? null;
                $latitude = $attendance->check_out_latitude ?? null;
                $longitude = $attendance->check_out_longitude ?? null;
            }

            $name = (string) $user->name;
            $branchName = (string) optional($user->branch)->name;
            $departmentName = (string) optional($user->department)->dept_name;

            $checkTime = $checkTimeRaw ? AttendanceHelper::changeTimeFormatForAttendanceView($checkTimeRaw) : '';
            $officeStartRaw = optional($user->officeTime)->opening_time;
            $officeStartTime = $officeStartRaw ? AttendanceHelper::changeTimeFormatForAttendanceView($officeStartRaw) : '';
            $officeEndRaw = optional($user->officeTime)->closing_time;
            $officeEndTime = $officeEndRaw ? AttendanceHelper::changeTimeFormatForAttendanceView($officeEndRaw) : '';

            $toTimeOnly = static function (?string $value): ?Carbon {
                if (! $value) {
                    return null;
                }
                $parsed = Carbon::parse($value);
                return Carbon::createFromTimeString($parsed->format('H:i:s'));
            };

            $status = '';
            $timeDifference = '';

            if ($type === 'check_in' && $checkTimeRaw && $officeStartRaw) {
                $checkInAt = $toTimeOnly($checkTimeRaw);
                $officeStartAt = $toTimeOnly($officeStartRaw);

                if ($checkInAt && $officeStartAt) {
                    if ($checkInAt->gt($officeStartAt)) {
                        $minutes = $officeStartAt->diffInMinutes($checkInAt);
                        $status = 'យឺត';
                        $timeDifference = $minutes > 0 ? " {$minutes}នាទី" : '';
                    } elseif ($checkInAt->lt($officeStartAt)) {
                        $minutes = $checkInAt->diffInMinutes($officeStartAt);
                        $status = 'មុនម៉ោង';
                        $timeDifference = $minutes > 0 ? " {$minutes}នាទី" : '';
                    } else {
                        $status = 'ទាន់ម៉ោង';
                    }
                }
            }

            if ($type === 'check_out' && $checkTimeRaw && $officeEndRaw) {
                $checkOutAt = $toTimeOnly($checkTimeRaw);
                $officeEndAt = $toTimeOnly($officeEndRaw);

                if ($checkOutAt && $officeEndAt) {
                    if ($checkOutAt->gt($officeEndAt)) {
                        $minutes = $officeEndAt->diffInMinutes($checkOutAt);
                        $status = 'លើសម៉ោង';
                        $timeDifference = $minutes > 0 ? " {$minutes}នាទី" : '';
                    } elseif ($checkOutAt->lt($officeEndAt)) {
                        $minutes = $checkOutAt->diffInMinutes($officeEndAt);
                        $status = 'មុនម៉ោង';
                        $timeDifference = $minutes > 0 ? " {$minutes}នាទី" : '';
                    } else {
                        $status = 'ទាន់ម៉ោង';
                    }
                }
            }

            $locationLink = null;
            if ($latitude !== null && $longitude !== null) {
                $locationLink = 'https://www.google.com/maps?q=' . $latitude . ',' . $longitude;
            }

            $locationInfo = [
                'address' => null,
                'link' => $locationLink,
            ];

            if ($type === 'check_in') {
                $messageText = "👤 ឈ្មោះ: {$name} \n" .
                    "🟢 បានស្កែនចូលនៅម៉ោង {$checkTime} \n" .
                    "🏢 សាខា: {$branchName} \n" .
                    "🛒 ផ្នែក: {$departmentName} \n" .
                    "🕑 ម៉ោងចូលការងារ: {$officeStartTime} \n" .
                    "🕑 ស្ថានភាព: {$status}{$timeDifference} \n";
            } else {
                $messageText = "👤 ឈ្មោះ: {$name} \n" .
                    "🔴 បានស្កែនចេញនៅម៉ោង {$checkTime} \n" .
                    "🏢 សាខា: {$branchName} \n" .
                    "🛒 ផ្នែក: {$departmentName} \n" .
                    "🕑 ម៉ោងបិទការងារ: {$officeEndTime} \n" .
                    "📊 ស្ថានភាព: {$status}{$timeDifference} \n";
            }
            $messageText .= "🗺️ ផែនទី: " . ($locationInfo['link'] ?? '');

            $this->telegramService->sendToAction(
                $type === 'check_out' ? TelegramGroup::EVENT_ATTENDANCE_CHECKOUT : TelegramGroup::EVENT_ATTENDANCE_CHECKIN,
                $messageText,
                null,
                $branchName,
                $departmentName,
                $latitude !== null ? (float) $latitude : null,
                $longitude !== null ? (float) $longitude : null,
            );
        } catch (\Throwable $e) {
            Log::error('Telegram attendance notification error.', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
