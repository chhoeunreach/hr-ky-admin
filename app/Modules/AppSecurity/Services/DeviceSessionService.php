<?php

namespace App\Modules\AppSecurity\Services;

use App\Models\User;
use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Models\SecuritySession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DeviceSessionService
{
    public function __construct(
        private readonly SecurityPolicyService $policy,
        private readonly AttestationOsPolicyService $attestationOsPolicy,
    ) {}

    public function open(User $user, string $deviceUuid): array
    {
        return DB::transaction(function () use ($user, $deviceUuid): array {
            $device = RegisteredDevice::query()
                ->where('uuid', $deviceUuid)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();
            if (!$device) {
                throw new AppSecurityException('Device registration was not found.', 404);
            }
            if ($device->device_status !== 'approved' || $device->revoked_at || $device->blocked_at) {
                throw new AppSecurityException('This device is not authorized.', 423);
            }

            $this->assertAttestationOsPolicy($device);

            $maximum = max(1, (int) $this->policy->value('max_sessions_per_device'));
            $activeSessions = SecuritySession::query()
                ->where('device_id', $device->id)
                ->where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->orderByRaw('CASE WHEN last_used_at IS NULL THEN created_at ELSE last_used_at END ASC')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $revokeCount = max(0, $activeSessions->count() - $maximum + 1);
            if ($revokeCount > 0) {
                $revokedAt = now();
                $ids = $activeSessions->take($revokeCount)->pluck('id');
                SecuritySession::query()->whereIn('id', $ids)->whereNull('revoked_at')->update([
                    'revoked_at' => $revokedAt,
                    'revocation_reason' => 'concurrency_limit',
                    'session_token_sha256' => hash('sha256', random_bytes(32)),
                    'refresh_token_sha256' => hash('sha256', random_bytes(32)),
                    'updated_at' => $revokedAt,
                ]);
                if (\Illuminate\Support\Facades\Schema::hasTable('app_security_logs')) {
                    DB::table('app_security_logs')->insert([
                        'event_type' => 'session_concurrency_limit',
                        'severity' => 'low',
                        'user_id' => $user->id,
                        'device_id' => $device->id,
                        'request_uuid' => (string) Str::uuid(),
                        'ip_address' => null,
                        'details' => json_encode([
                            'revoked_session_count' => $revokeCount,
                            'maximum_sessions_per_device' => $maximum,
                        ], JSON_THROW_ON_ERROR),
                        'occurred_at' => $revokedAt,
                        'created_at' => $revokedAt,
                        'updated_at' => $revokedAt,
                    ]);
                }
            }

            $sessionToken = $this->token();
            $refreshToken = $this->token();
            $session = SecuritySession::query()->create([
                'uuid' => (string) Str::uuid(),
                'device_id' => $device->id,
                'user_id' => $user->id,
                'session_token_sha256' => hash('sha256', $sessionToken),
                'refresh_token_sha256' => hash('sha256', $refreshToken),
                'expires_at' => now()->addSeconds((int) $this->policy->value('session_ttl_seconds')),
                'refresh_expires_at' => now()->addSeconds((int) $this->policy->value('refresh_ttl_seconds')),
                'last_used_at' => now(),
            ]);

            return [
                'session_id' => $session->uuid,
                'session_token' => $sessionToken,
                'refresh_token' => $refreshToken,
                'expires_at' => $session->expires_at->toIso8601String(),
                'refresh_expires_at' => $session->refresh_expires_at->toIso8601String(),
            ];
        }, 3);
    }

    public function refresh(User $user, string $refreshToken, string $signature): array
    {
        if (!Schema::hasTable('app_security_refresh_token_history')) {
            throw new AppSecurityException('Refresh-token reuse protection is not initialized.', 503);
        }

        $result = DB::transaction(function () use ($user, $refreshToken, $signature): array {
            $hash = hash('sha256', $refreshToken);
            $session = SecuritySession::query()
                ->where('refresh_token_sha256', $hash)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();
            if (!$session) {
                $this->detectRefreshTokenReuse($user, $refreshToken, $hash, $signature);
                return ['_invalid_refresh' => true];
            }
            if ($session->revoked_at || $session->refresh_expires_at?->isPast()) {
                throw new AppSecurityException('Refresh session is invalid or expired.', 401);
            }

            $device = RegisteredDevice::query()->find($session->device_id);
            if (!$device || $device->device_status !== 'approved' || $device->revoked_at) {
                throw new AppSecurityException('This device is not authorized.', 423);
            }
            $this->assertAttestationOsPolicy($device);

            $message = "ky-session-refresh-v1\n{$session->uuid}\n{$refreshToken}";
            $verifier = app(SecuritySignatureService::class);
            if (!$verifier->verify($message, $signature, $device->public_key)) {
                throw new AppSecurityException('Refresh proof could not be verified.', 401);
            }

            $sessionToken = $this->token();
            $nextRefreshToken = $this->token();
            if ($session->refresh_expires_at) {
                DB::table('app_security_refresh_token_history')->insertOrIgnore([
                    'session_id' => $session->id,
                    'token_sha256' => $hash,
                    'expires_at' => $session->refresh_expires_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $session->forceFill([
                'session_token_sha256' => hash('sha256', $sessionToken),
                'refresh_token_sha256' => hash('sha256', $nextRefreshToken),
                'expires_at' => now()->addSeconds((int) $this->policy->value('session_ttl_seconds')),
                'refresh_expires_at' => now()->addSeconds((int) $this->policy->value('refresh_ttl_seconds')),
                'last_used_at' => now(),
            ])->save();

            return [
                'session_id' => $session->uuid,
                'session_token' => $sessionToken,
                'refresh_token' => $nextRefreshToken,
                'expires_at' => $session->expires_at->toIso8601String(),
                'refresh_expires_at' => $session->refresh_expires_at->toIso8601String(),
            ];
        }, 3);

        if (($result['_invalid_refresh'] ?? false) === true) {
            throw new AppSecurityException('Refresh session is invalid or expired.', 401);
        }

        return $result;
    }

    private function detectRefreshTokenReuse(User $user, string $refreshToken, string $tokenHash, string $signature): void
    {
        $history = DB::table('app_security_refresh_token_history')
            ->where('token_sha256', $tokenHash)
            ->where('expires_at', '>', now())
            ->lockForUpdate()
            ->first();
        if (!$history) return;

        $session = SecuritySession::query()->whereKey($history->session_id)
            ->where('user_id', $user->id)->lockForUpdate()->first();
        if (!$session) return;

        $device = RegisteredDevice::query()->find($session->device_id);
        if (!$device) return;
        $message = "ky-session-refresh-v1\n{$session->uuid}\n{$refreshToken}";
        if (!app(SecuritySignatureService::class)->verify($message, $signature, $device->public_key)) return;

        $now = now();
        $previousRevokedAt = $session->revoked_at?->toIso8601String();
        $firstDetection = DB::table('app_security_refresh_token_history')
            ->where('id', $history->id)
            ->whereNull('reuse_detected_at')
            ->update(['reuse_detected_at' => $now, 'updated_at' => $now]) === 1;
        if (!$firstDetection) return;

        if (!$session->revoked_at) {
            $session->forceFill([
                'revoked_at' => $now,
                'revocation_reason' => 'refresh_token_reuse',
                'session_token_sha256' => hash('sha256', random_bytes(32)),
                'refresh_token_sha256' => hash('sha256', random_bytes(32)),
            ])->save();
        }

        if (Schema::hasTable('app_security_logs')) {
            DB::table('app_security_logs')->insert([
                'event_type' => 'refresh_token_reuse',
                'severity' => 'high',
                'user_id' => $user->id,
                'device_id' => $device->id,
                'request_uuid' => (string) Str::uuid(),
                'ip_address' => request()->ip(),
                'details' => json_encode(['session_uuid' => $session->uuid], JSON_THROW_ON_ERROR),
                'occurred_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        app(SecurityAlertService::class)->record(
            'refresh-reuse:'.$session->uuid.':'.$tokenHash,
            'refresh_token_reuse',
            'high',
            'Device refresh token reuse detected',
            'A previously rotated device refresh token was presented with a valid device signature. The associated session was revoked.',
            (int) $device->id,
            (int) $user->id,
            ['session_uuid' => $session->uuid]
        );

        if (Schema::hasTable('app_security_audit_logs')) {
            \App\Modules\AppSecurity\Models\SecurityAuditLog::query()->create([
                'admin_id' => null,
                'actor_type' => 'system',
                'actor_id' => null,
                'action' => 'security.session.refresh_reuse_detected',
                'target_type' => 'session',
                'target_id' => $session->uuid,
                'previous_state' => ['revoked_at' => $previousRevokedAt],
                'new_state' => ['revoked_at' => $now->toIso8601String(), 'device_id' => $device->id],
                'reason' => 'Previously rotated refresh token was reused with a valid device signature.',
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 512),
                'occurred_at' => $now,
            ]);
        }
    }

    public function revoke(User $user, string $sessionUuid): void
    {
        SecuritySession::query()
            ->where('uuid', $sessionUuid)
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'revocation_reason' => 'user_logout',
                'session_token_sha256' => hash('sha256', random_bytes(32)),
                'refresh_token_sha256' => hash('sha256', random_bytes(32)),
                'updated_at' => now(),
            ]);
    }

    private function assertAttestationOsPolicy(RegisteredDevice $device): void
    {
        $result = $this->attestationOsPolicy->forDevice($device);
        if (!$result['compliant']) {
            throw new AppSecurityException(AttestationOsPolicyService::MESSAGE, 428);
        }
    }

    private function token(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
