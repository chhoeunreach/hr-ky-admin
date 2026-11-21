<?php

namespace App\Modules\AppSecurity\Services;

use Illuminate\Support\Facades\Cache;
use Throwable;

/** Counts successful new registrations per source address without storing the address. */
class RegistrationVelocityDetector
{
    public function observe(?string $ipAddress): ?array
    {
        $ipAddress = trim((string) $ipAddress);
        if ($ipAddress === '') return null;

        $windowSeconds = max(60, (int) config('app_security.registration_velocity_window_seconds', 900));
        $threshold = max(2, (int) config('app_security.registration_velocity_threshold', 5));
        $window = intdiv(now()->timestamp, $windowSeconds);
        $bucketEndsAt = ($window + 1) * $windowSeconds;
        $ttl = max(1, $bucketEndsAt - now()->timestamp + 30);
        $ipHash = hash_hmac('sha256', $ipAddress, (string) config('app.key'));
        $key = 'app-security:registration-velocity:'.$window.':'.$ipHash;

        try {
            if (Cache::add($key, 1, $ttl)) {
                $count = 1;
            } else {
                $count = (int) Cache::increment($key);
            }
        } catch (Throwable $exception) {
            // Velocity is supplemental review context. A cache outage must not prevent
            // legitimate account registration or weaken any cryptographic check.
            report($exception);
            return null;
        }

        if ($count < $threshold) return null;
        return ['count' => $count, 'threshold' => $threshold, 'window_seconds' => $windowSeconds];
    }
}
