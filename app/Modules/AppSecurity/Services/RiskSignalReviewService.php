<?php

namespace App\Modules\AppSecurity\Services;

use App\Modules\AppSecurity\Models\SecurityAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Records human risk-signal dispositions; false-positive decisions affect future rolling scores only. */
class RiskSignalReviewService
{
    public function __construct(private readonly DeviceRiskEngine $riskEngine) {}

    public function review(int $checkId, int $adminId, string $disposition, string $reason, ?string $ip, ?string $userAgent): array
    {
        if (!in_array($disposition, ['false_positive', 'confirmed'], true)) {
            throw ValidationException::withMessages(['disposition' => 'Choose false positive or confirmed.']);
        }
        $reason = trim($reason);
        if (mb_strlen($reason) < 3 || mb_strlen($reason) > 512) {
            throw ValidationException::withMessages(['reason' => 'A reason from 3 to 512 characters is required.']);
        }

        return DB::transaction(function () use ($checkId, $adminId, $disposition, $reason, $ip, $userAgent): array {
            $signal = DB::table('app_device_integrity_checks')->where('id', $checkId)->lockForUpdate()->first();
            abort_if(!$signal, 404);
            abort_if($signal->review_status !== null, 409, 'This risk signal has already been reviewed.');
            if ($disposition === 'false_positive' && $signal->severity === 'critical') {
                throw ValidationException::withMessages([
                    'disposition' => 'Critical cryptographic signals cannot be dismissed as false positives.',
                ]);
            }

            $reviewedAt = now();
            DB::table('app_device_integrity_checks')->where('id', $checkId)->update([
                'review_status' => $disposition,
                'reviewed_by' => $adminId,
                'reviewed_at' => $reviewedAt,
                'review_reason' => trim($reason),
                'updated_at' => $reviewedAt,
            ]);

            $risk = $disposition === 'false_positive' ? $this->riskEngine->calculate((int) $signal->device_id) : null;
            SecurityAuditLog::query()->create([
                'admin_id' => $adminId,
                'actor_type' => 'admin',
                'actor_id' => $adminId,
                'action' => 'security.risk_signal.reviewed',
                'target_type' => 'integrity_check',
                'target_id' => (string) $checkId,
                'previous_state' => ['review_status' => null, 'signal' => $signal->signal],
                'new_state' => [
                    'review_status' => $disposition,
                    'reviewed_by' => $adminId,
                    'reviewed_at' => $reviewedAt->toISOString(),
                    'risk_score' => $risk['risk_score'] ?? null,
                ],
                'reason' => trim($reason),
                'ip_address' => $ip,
                'user_agent' => $userAgent === null ? null : mb_substr($userAgent, 0, 512),
                'occurred_at' => $reviewedAt,
            ]);

            return ['id' => $checkId, 'device_id' => (int) $signal->device_id,
                'review_status' => $disposition, 'risk_score' => $risk['risk_score'] ?? null];
        }, 3);
    }
}
