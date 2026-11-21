<?php

namespace App\Modules\AppSecurity\Services;

use App\Modules\AppSecurity\Models\RegisteredDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Scores server-observed security events; scores inform review and never auto-block users. */
class DeviceRiskEngine
{
    public function __construct(private readonly SecurityPolicyService $policy) {}

    public function recordSignal(int $deviceId, string $signal, array $context = []): array
    {
        if (!(bool) $this->policy->value('risk_enabled')) {
            return ['risk_score' => null, 'risk_level' => null, 'reasons' => []];
        }

        $definition = $this->policy->signalDefinition($signal);
        if (!is_array($definition)) {
            return $this->calculate($deviceId);
        }

        $severity = in_array($definition['severity'] ?? null, ['info', 'low', 'medium', 'high', 'critical'], true)
            ? $definition['severity'] : 'medium';
        $points = min(100, max(0, (int) ($definition['points'] ?? 0)));
        $safeContext = [];
        foreach (array_slice($context, 0, 10, true) as $key => $value) {
            if (is_string($key) && (is_scalar($value) || $value === null)) {
                $safeContext[substr($key, 0, 48)] = is_string($value) ? substr($value, 0, 191) : $value;
            }
        }

        DB::table('app_device_integrity_checks')->insert([
            'device_id' => $deviceId,
            'signal' => $signal,
            'severity' => $severity,
            'risk_points' => $points,
            'context' => json_encode($safeContext, JSON_THROW_ON_ERROR),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Persist an admin-visible alert for serious server-observed security
        // events even when the aggregate device score has not crossed HIGH.
        // Keep noisy medium/low client indicators in the risk history only.
        $alertable = in_array($severity, ['high', 'critical'], true)
            || in_array($signal, ['registration_velocity', 'cross_account_key_reuse'], true);
        if ($alertable) {
            $device = RegisteredDevice::query()->find($deviceId);
            if ($device) {
                $hourBucket = now()->format('YmdH');
                $title = Str::headline($signal).' security signal';
                $message = match ($signal) {
                    'registration_velocity' => 'Several new installations registered from the same network in a short period. Review the devices; shared networks can produce false positives.',
                    'cross_account_key_reuse' => 'The same device signing key is associated with another account. Review the account link; distinct per-account keys on a shared installation are not flagged.',
                    default => "A {$severity}-severity {$title} was recorded for installation {$device->uuid}. Review the device risk history.",
                };
                $this->alerts()->record(
                    'risk-event:'.$deviceId.':'.$signal.':'.$hourBucket,
                    'risk_signal_'.$signal,
                    $severity,
                    $title,
                    $message,
                    $deviceId,
                    (int) $device->user_id,
                    ['signal' => $signal, 'risk_points' => $points, 'context' => $safeContext]
                );
            }
        }

        return $this->calculate($deviceId);
    }

    private function alerts(): SecurityAlertService
    {
        return app(SecurityAlertService::class);
    }

    public function calculate(int $deviceId): array
    {
        $windowHours = (int) $this->policy->value('risk_window_hours');
        $windowStart = now()->subHours($windowHours);
        $signals = DB::table('app_device_integrity_checks')
            ->where('device_id', $deviceId)
            ->where('occurred_at', '>=', $windowStart)
            ->when(Schema::hasColumn('app_device_integrity_checks', 'review_status'), fn ($query) => $query->where(function ($review) {
                $review->whereNull('review_status')
                    ->orWhere('review_status', '<>', 'false_positive')
                    ->orWhere('severity', 'critical');
            }))
            ->orderBy('occurred_at')
            ->get(['signal', 'severity', 'risk_points', 'occurred_at']);

        $total = 0;
        $groups = [];
        $criticalSignals = [];
        foreach ($signals as $signal) {
            $name = (string) $signal->signal;
            $groups[$name] ??= ['signal' => $name, 'count' => 0, 'points' => 0, 'severity' => $signal->severity];
            $groups[$name]['count']++;
            $groups[$name]['points'] += (int) $signal->risk_points;
            $total += (int) $signal->risk_points;
            if ($signal->severity === 'critical') {
                // The triggering request is denied independently of this score;
                // keeping it critical in history ensures reviewers see the event.
                $criticalSignals[$name] = true;
            }
        }

        $score = min(100, $total);
        $previousLevel = DB::table('app_device_risk_scores')->where('device_id', $deviceId)->orderByDesc('calculated_at')->value('risk_level');
        $mediumThreshold = (int) $this->policy->value('risk_review_threshold');
        $highThreshold = max($mediumThreshold, (int) $this->policy->value('risk_block_threshold'));
        $level = $score > 0 && $score >= $highThreshold ? 'high'
            : ($score > 0 && $score >= $mediumThreshold ? 'medium' : 'low');
        $reasons = array_values($groups);
        $policyVersion = (string) $this->policy->value('risk_policy_version');

        RegisteredDevice::query()->whereKey($deviceId)->update(['risk_score' => $score]);
        DB::table('app_device_risk_scores')->insert([
            'device_id' => $deviceId,
            'risk_score' => $score,
            'risk_level' => $level,
            'policy_version' => $policyVersion,
            'reasons' => json_encode([
                'signals' => $reasons,
                'critical_signals' => array_keys($criticalSignals),
                'window_hours' => $windowHours,
            ], JSON_THROW_ON_ERROR),
            'calculated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // A critical event already has a direct event alert. Avoid sending a
        // duplicate aggregate threshold alert for the same calculation.
        if ($level === 'high' && $previousLevel !== 'high' && $criticalSignals === []) {
            $device = RegisteredDevice::query()->find($deviceId);
            if ($device) app(SecurityAlertService::class)->highRiskThresholdCrossed($deviceId, $device->user_id, $score, $policyVersion, $reasons);
        }

        return ['risk_score' => $score, 'risk_level' => $level, 'reasons' => $reasons,
            'critical_signals' => array_keys($criticalSignals), 'policy_version' => $policyVersion];
    }
}
