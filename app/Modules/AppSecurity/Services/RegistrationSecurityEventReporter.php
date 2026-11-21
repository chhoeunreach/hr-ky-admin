<?php

namespace App\Modules\AppSecurity\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/** Records a small, safe audit/alert after a registration transaction is rejected. */
class RegistrationSecurityEventReporter
{
    private const EVENTS = [
        'registration_challenge_rejected' => [
            'severity' => 'medium', 'title' => 'Device registration challenge rejected',
            'message' => 'A device registration challenge was invalid, expired, or already used.',
        ],
        'registration_proof_invalid' => [
            'severity' => 'critical', 'title' => 'Device registration proof failed',
            'message' => 'A device registration key proof failed cryptographic verification.',
        ],
        'registration_attestation_failed' => [
            'severity' => 'high', 'title' => 'Device attestation rejected',
            'message' => 'A device registration did not meet the configured hardware attestation policy.',
        ],
        'registration_release_rejected' => [
            'severity' => 'high', 'title' => 'Unrecognized application release rejected',
            'message' => 'A device registration used an application package, release, or signing certificate that is not trusted.',
        ],
        'registration_device_limit_exceeded' => [
            'severity' => 'medium', 'title' => 'Device registration limit reached',
            'message' => 'A registration was rejected because this account reached its configured device limit.',
        ],
    ];

    public function report(User $user, string $challengeId, string $event, ?string $observedIp = null): void
    {
        $definition = self::EVENTS[$event] ?? null;
        if ($definition === null) return;

        $context = ['challenge_id' => $challengeId];
        try {
            if (Schema::hasTable('app_security_logs')) {
                DB::table('app_security_logs')->insert([
                    'event_type' => $event,
                    'severity' => $definition['severity'],
                    'user_id' => $user->id,
                    'device_id' => null,
                    'request_uuid' => (string) Str::uuid(),
                    'ip_address' => $observedIp,
                    'details' => json_encode($context, JSON_THROW_ON_ERROR),
                    'occurred_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            app(SecurityAlertService::class)->record(
                'registration-failure:'.$user->id.':'.$challengeId.':'.$event,
                $event,
                $definition['severity'],
                $definition['title'],
                $definition['message'],
                null,
                (int) $user->id,
                $context
            );
        } catch (Throwable $exception) {
            // A telemetry outage must not change the original registration denial.
            report($exception);
        }
    }
}
