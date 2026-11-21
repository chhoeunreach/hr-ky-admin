<?php

namespace App\Modules\AppSecurity\Services;

use App\Modules\AppSecurity\Jobs\SendAppSecurityAlertEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SecurityAlertService
{
    public function highRiskThresholdCrossed(int $deviceId, ?int $userId, int $score, string $policyVersion, array $signals): void
    {
        $dedupeKey = 'high-risk:'.$deviceId.':'.$policyVersion.':'.now()->toDateString();
        $this->record(
            $dedupeKey, 'risk_threshold_crossed', 'high', 'Device crossed the high-risk threshold',
            "Device {$deviceId} reached risk score {$score} under policy {$policyVersion}. Review its risk history and device status.",
            $deviceId, $userId,
            ['risk_score' => $score, 'policy_version' => $policyVersion, 'signals' => $signals]
        );
    }

    public function record(
        string $dedupeKey,
        string $type,
        string $severity,
        string $title,
        string $message,
        ?int $deviceId = null,
        ?int $userId = null,
        array $context = []
    ): void {
        if (! Schema::hasTable('app_security_alerts')) return;

        $id = DB::table('app_security_alerts')->insertOrIgnore([
            'uuid' => (string) Str::uuid(), 'dedupe_key' => $dedupeKey,
            'device_id' => $deviceId, 'user_id' => $userId,
            'alert_type' => $type, 'severity' => $severity,
            'title' => $title, 'message' => $message,
            'context' => json_encode($context, JSON_THROW_ON_ERROR),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        if (!$id || !(bool) config('app_security.alert_notifications_enabled', false)
            || config('app_security.alert_recipients', []) === []) return;
        $alertId = DB::table('app_security_alerts')->where('dedupe_key', $dedupeKey)->value('id');
        if ($alertId) SendAppSecurityAlertEmail::dispatch((int) $alertId)->afterCommit();
    }

}
