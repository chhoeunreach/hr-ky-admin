<?php

namespace App\Services\Attendance;

use App\Helpers\AttendanceHelper;
use App\Models\Attendance;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class AttendanceTelegramNotifier
{
    public function notify(User $user, Attendance $attendance, array $notificationData): void
    {
        if (!config('attendance_telegram.enabled')) {
            return;
        }

        $token = (string) config('attendance_telegram.bot_token');
        if ($token === '') {
            return;
        }

        $chatId = $this->resolveChatIdForUser($user);
        if ($chatId === null || $chatId === '') {
            return;
        }

        $permissionKey = (string) ($notificationData['permissionKey'] ?? '');
        $time = $notificationData['time'] ?? null;
        $workedTime = $notificationData['workedTime'] ?? null;

        $branchName = $user->branch?->name ?? '-';
        $departmentName = $user->department?->name ?? '-';
        $officeStartTime = $user->officeTime?->opening_time ?? null;
        $officeStartText = $officeStartTime ? AttendanceHelper::changeTimeFormatForAttendanceView($officeStartTime) : '-';

        $isCheckOut = $permissionKey === 'employee_check_out';
        $eventTime = $this->resolveEventTimeText($notificationData, $attendance, $isCheckOut);
        [$statusText, $timeDiffText] = $this->resolveCheckInStatusText($attendance, $officeStartTime, $isCheckOut);

        [$lat, $lng] = $this->resolveCoordsForAction($attendance, $permissionKey);
        $mapLink = ($lat !== null && $lng !== null) ? "https://maps.google.com/?q={$lat},{$lng}" : null;
        $address = $this->resolveAddressFromCoords($lat, $lng);

        $message = "👤 ឈ្មោះ: {$user->name}\n";
        $message .= $isCheckOut
            ? "🔴 បានស្កែនចេញនៅម៉ោង {$eventTime}\n"
            : "🟢 បានស្កែនចូលនៅម៉ោង {$eventTime}\n";
        $message .= "🏢 សាខា: {$branchName}\n";
        $message .= "🛒 ផ្នែក: {$departmentName}\n";

        if (!$isCheckOut) {
            $message .= "🕑 ម៉ោងចូលការងារ: {$officeStartText}\n";
            $message .= "🕑 ស្ថានភាព: {$statusText}{$timeDiffText}\n";
        } elseif ($workedTime) {
            $message .= "⏱️ ម៉ោងធ្វើការ: {$workedTime}\n";
        }

        $note = $isCheckOut ? ($attendance->check_out_note ?? null) : ($attendance->check_in_note ?? null);
        if (is_string($note) && trim($note) !== '') {
            $message .= "📝 ចំណាំ: " . trim($note) . "\n";
        }

        $message .= "📍 ទីតាំងពិត: " . ($address ?? 'មិនអាចរកអាសយដ្ឋានបាន') . "\n";
        $message .= "🗺️ ផែនទី: " . ($mapLink ?? 'មិនមាន');

        $timeout = (int) config('attendance_telegram.timeout_seconds', 3);

        try {
            Http::timeout(max(1, $timeout))
                ->asForm()
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                ])
                ->throw();
        } catch (Exception $e) {
            Log::warning('Attendance Telegram notify failed', [
                'department_id' => $user->department_id ?? null,
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveChatIdForUser(User $user): ?string
    {
        $mappingRaw = (string) config('attendance_telegram.department_chat_ids', '');
        $mapping = $this->parseDepartmentChatIdMapping($mappingRaw);

        $candidates = [];
        if ($user->department_id !== null) {
            $candidates[] = (string) $user->department_id;
        }
        $departmentName = $user->department?->name;
        if (is_string($departmentName) && trim($departmentName) !== '') {
            $candidates[] = trim($departmentName);
            $candidates[] = strtolower(trim($departmentName));
        }

        foreach ($candidates as $key) {
            if (array_key_exists($key, $mapping) && (string) $mapping[$key] !== '') {
                return (string) $mapping[$key];
            }
        }

        $defaultChatId = (string) config('attendance_telegram.default_chat_id');
        return $defaultChatId !== '' ? $defaultChatId : null;
    }

    private function parseDepartmentChatIdMapping(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $json = json_decode($raw, true);
        if (is_array($json)) {
            $out = [];
            foreach ($json as $k => $v) {
                $k = trim((string) $k);
                $v = trim((string) $v);
                if ($k !== '' && $v !== '') {
                    $out[$k] = $v;
                }
            }
            return $out;
        }

        // CSV: 1:-100123,2:-100456
        $out = [];
        foreach (explode(',', $raw) as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }
            $parts = preg_split('/[:=]/', $pair, 2);
            if (!$parts || count($parts) !== 2) {
                continue;
            }
            $k = trim((string) $parts[0]);
            $v = trim((string) $parts[1]);
            if ($k !== '' && $v !== '') {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    private function resolveCoordsForAction(Attendance $attendance, string $permissionKey): array
    {
        if ($permissionKey === 'employee_check_out') {
            $lat = $attendance->check_out_latitude ?? null;
            $lng = $attendance->check_out_longitude ?? null;
        } else {
            $lat = $attendance->check_in_latitude ?? null;
            $lng = $attendance->check_in_longitude ?? null;
        }

        $lat = is_numeric($lat) ? (string) $lat : null;
        $lng = is_numeric($lng) ? (string) $lng : null;

        return [$lat, $lng];
    }

    private function resolveEventTimeText(array $notificationData, Attendance $attendance, bool $isCheckOut): string
    {
        $time = $notificationData['time'] ?? null;
        if (is_string($time) && trim($time) !== '') {
            return AttendanceHelper::changeTimeFormatForAttendanceView($time);
        }

        $fallback = $isCheckOut ? ($attendance->check_out_at ?? $attendance->night_checkout ?? null) : ($attendance->check_in_at ?? $attendance->night_checkin ?? null);
        return $fallback ? AttendanceHelper::changeTimeFormatForAttendanceView($fallback) : '-';
    }

    private function resolveCheckInStatusText(Attendance $attendance, $officeStartTime, bool $isCheckOut): array
    {
        if ($isCheckOut) {
            return ['-', ''];
        }

        if (!$officeStartTime) {
            return ['-', ''];
        }

        $checkInTime = $attendance->check_in_at ?? $attendance->night_checkin ?? null;
        if (!$checkInTime || !$attendance->attendance_date) {
            return ['-', ''];
        }

        try {
            $checkIn = Carbon::parse($attendance->attendance_date . ' ' . (string) $checkInTime);
            $officeStart = Carbon::parse($attendance->attendance_date . ' ' . (string) $officeStartTime);
        } catch (Exception $e) {
            return ['-', ''];
        }

        $status = '✅ ទាន់ម៉ោង';
        $diffText = '';

        if ($checkIn->greaterThan($officeStart)) {
            $lateMinutes = $officeStart->diffInMinutes($checkIn);
            $formatted = $this->formatKhmerHoursMinutes($lateMinutes);
            $status = '⏰ ពេលយឺត';
            $diffText = "\n⏳ ចំនួនម៉ោងយឺត: {$formatted}";
        } elseif ($checkIn->lessThan($officeStart)) {
            $earlyMinutes = $checkIn->diffInMinutes($officeStart);
            $formatted = $this->formatKhmerHoursMinutes($earlyMinutes);
            $status = '🕰️ ចូលមុន';
            $diffText = "\n⏳ ចំនួនម៉ោងមុន: {$formatted}";
        }

        return [$status, $diffText];
    }

    private function formatKhmerHoursMinutes(int $totalMinutes): string
    {
        $hours = intdiv(max(0, $totalMinutes), 60);
        $minutes = max(0, $totalMinutes) % 60;

        $out = '';
        if ($hours > 0) {
            $out .= "{$hours}ម៉ោង";
        }
        if ($minutes > 0) {
            $out .= ($out !== '' ? '' : '') . "{$minutes}នាទី";
        }
        return $out !== '' ? $out : '0នាទី';
    }

    private function resolveAddressFromCoords(?string $lat, ?string $lng): ?string
    {
        if ($lat === null || $lng === null) {
            return null;
        }
        if (!config('attendance_telegram.reverse_geocode_enabled')) {
            return null;
        }

        $timeout = (int) config('attendance_telegram.timeout_seconds', 3);
        $userAgent = (string) config('attendance_telegram.reverse_geocode_user_agent', 'hr-ky-admin-attendance-bot');

        try {
            $response = Http::timeout(max(1, $timeout))
                ->withHeaders([
                    'User-Agent' => $userAgent,
                    'Accept-Language' => 'km,en;q=0.8',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng,
                ])
                ->throw()
                ->json();

            $displayName = $response['display_name'] ?? null;
            return is_string($displayName) && trim($displayName) !== '' ? trim($displayName) : null;
        } catch (Exception $e) {
            Log::warning('Attendance Telegram reverse-geocode failed', [
                'lat' => $lat,
                'lng' => $lng,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
