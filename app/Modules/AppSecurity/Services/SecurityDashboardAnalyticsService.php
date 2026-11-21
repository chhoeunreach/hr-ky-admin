<?php

namespace App\Modules\AppSecurity\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SecurityDashboardAnalyticsService
{
    public function snapshot(int $reviewThreshold, int $highRiskThreshold, int $days = 14): array
    {
        $days = min(90, max(7, $days));
        $today = CarbonImmutable::today();
        $from = $today->subDays($days - 1);
        $to = $today->endOfDay();

        $registeredByDay = collect();
        if (Schema::hasTable('app_registered_devices')) {
            $registeredByDay = DB::table('app_registered_devices')
                ->whereBetween('registered_at', [$from, $to])
                ->selectRaw('DATE(registered_at) as day, COUNT(*) as total')
                ->groupBy('day')->pluck('total', 'day');
        }

        $installations = collect(range(0, $days - 1))->map(function (int $offset) use ($from, $registeredByDay): array {
            $day = $from->addDays($offset)->toDateString();
            return ['date' => $day, 'count' => (int) ($registeredByDay[$day] ?? 0)];
        });

        $riskDistribution = ['low' => 0, 'medium' => 0, 'high' => 0];
        if (Schema::hasTable('app_registered_devices')) {
            $riskDistribution = [
                'low' => DB::table('app_registered_devices')->where('risk_score', '<', $reviewThreshold)->count(),
                'medium' => DB::table('app_registered_devices')->where('risk_score', '>=', $reviewThreshold)
                    ->where('risk_score', '<', $highRiskThreshold)->count(),
                'high' => DB::table('app_registered_devices')->where('risk_score', '>=', $highRiskThreshold)->count(),
            ];
        }

        $eventTypes = [
            'request_signature_invalid', 'request_nonce_replayed', 'registration_attestation_failed',
            'attestation_os_policy_failed', 'release_verification_failed', 'refresh_token_reuse',
            'session_concurrency_limit', 'security_session_invalid',
        ];
        $eventCounts = Schema::hasTable('app_security_logs')
            ? DB::table('app_security_logs')->whereIn('event_type', $eventTypes)
                ->whereBetween('occurred_at', [$from, $to])->selectRaw('event_type, COUNT(*) as total')
                ->groupBy('event_type')->pluck('total', 'event_type')
            : collect();

        $events = [
            'Invalid signatures' => (int) ($eventCounts['request_signature_invalid'] ?? 0),
            'Replay attempts' => (int) ($eventCounts['request_nonce_replayed'] ?? 0),
            'Attestation failures' => (int) ($eventCounts['registration_attestation_failed'] ?? 0)
                + (int) ($eventCounts['attestation_os_policy_failed'] ?? 0),
            'Release verification failures' => (int) ($eventCounts['release_verification_failed'] ?? 0),
            'Session anomalies' => (int) ($eventCounts['refresh_token_reuse'] ?? 0)
                + (int) ($eventCounts['session_concurrency_limit'] ?? 0)
                + (int) ($eventCounts['security_session_invalid'] ?? 0),
        ];

        $suspiciousRegistrations = Schema::hasTable('app_registered_devices')
            ? DB::table('app_registered_devices')->whereBetween('registered_at', [$from, $to])
                ->where('risk_score', '>=', $reviewThreshold)->count()
            : 0;

        return [
            'from' => $from->toDateString(),
            'to' => $today->toDateString(),
            'installations' => $installations,
            'risk_distribution' => $riskDistribution,
            'events' => $events,
            'suspicious_registrations' => $suspiciousRegistrations,
        ];
    }
}
