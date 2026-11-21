<?php

namespace App\Modules\AppSecurity\Services;

use App\Models\User;
use App\Modules\AppSecurity\Models\DeviceChallenge;
use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Jobs\SendAppSecurityUserNotification;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceRegistrationService
{
    public function __construct(
        private readonly SecuritySignatureService $signatures,
        private readonly ReleaseVerificationService $releases,
        private readonly AndroidKeyAttestationVerifier $attestationVerifier,
        private readonly DeviceRiskEngine $riskEngine,
        private readonly RegistrationVelocityDetector $registrationVelocity,
        private readonly SecurityPolicyService $policy
    ) {
    }

    public function issueChallenge(User $user, string $purpose = 'registration'): array
    {
        if ($purpose !== 'registration') {
            throw new AppSecurityException('Challenge purpose is invalid.', 422);
        }
        $challenge = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $record = DeviceChallenge::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'purpose' => $purpose,
            'challenge_sha256' => hash('sha256', $challenge),
            'expires_at' => now()->addSeconds((int) $this->policy->value('challenge_ttl_seconds')),
        ]);

        return [
            'challenge_id' => $record->uuid,
            'challenge' => $challenge,
            'expires_at' => $record->expires_at->toIso8601String(),
        ];
    }

    public function register(User $user, array $data, ?string $observedIp = null): RegisteredDevice
    {
        $fingerprint = $this->signatures->publicKeyFingerprint($data['public_key']);
        $lockKey = 'app-security:public-key-binding:'.hash_hmac('sha256', $fingerprint, (string) config('app.key'));

        try {
            return Cache::lock($lockKey, 120)->block(10, function () use ($user, $data, $observedIp, $fingerprint): RegisteredDevice {
                return DB::transaction(function () use ($user, $data, $observedIp, $fingerprint): RegisteredDevice {
                    $challenge = DeviceChallenge::query()
                        ->where('uuid', $data['challenge_id'])
                        ->where('user_id', $user->id)
                        ->where('purpose', 'registration')
                        ->lockForUpdate()
                        ->first();

                    if (!$challenge || $challenge->used_at || $challenge->expires_at->isPast()) {
                        throw new AppSecurityException('The registration challenge is invalid or expired.', 401, 'registration_challenge_rejected');
                    }
                    if (!hash_equals($challenge->challenge_sha256, hash('sha256', $data['challenge']))) {
                        $challenge->increment('attempts');
                        throw new AppSecurityException('The registration challenge is invalid.', 401, 'registration_challenge_rejected');
                    }

                    try {
                        $releaseCheck = $this->releases->check(
                            $data['package_name'], $data['signing_sha256'],
                            (int) $data['version_code'], (int) $data['android_sdk']
                        );
                    } catch (AppSecurityException $exception) {
                        if (in_array($exception->status, [403, 422], true)) {
                            throw new AppSecurityException(
                                $exception->getMessage(),
                                $exception->status,
                                'registration_release_rejected'
                            );
                        }
                        throw $exception;
                    }
                    if ($releaseCheck['mandatory_update']) {
                        throw new AppSecurityException('A mandatory application update is required.', 426);
                    }

                    $crossAccountReuse = app(CrossAccountKeyReuseDetector::class)->inspect($fingerprint, (int) $user->id);
                    $proof = "ky-device-registration-v1\n{$challenge->uuid}\n{$data['challenge']}\n{$data['installation_uuid']}";
                    if (!$this->signatures->verify($proof, $data['challenge_signature'], $data['public_key'])) {
                        throw new AppSecurityException('Device proof could not be verified.', 401, 'registration_proof_invalid');
                    }

                    if (DB::table('app_security_block_rules')->where('target_type', 'key_fingerprint')
                        ->where('target_value', strtolower($fingerprint))->where('is_active', true)
                        ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                        ->exists()) {
                        throw new AppSecurityException('This device key has been retired or blocked.', 423, 'device_key_blocked');
                    }

                    $existing = RegisteredDevice::query()
                        ->where('installation_uuid', $data['installation_uuid'])
                        ->where('user_id', $user->id)
                        ->lockForUpdate()
                        ->first();
                    $replacingKey = false;
                    if ($existing) {
                        if (!hash_equals($existing->public_key_fingerprint, $fingerprint)
                            && $existing->device_status !== 're_registration_required') {
                            throw new AppSecurityException('This account installation is already bound to another key.', 409);
                        }
                        if ($existing->device_status === 're_registration_required') {
                            if (hash_equals($existing->public_key_fingerprint, $fingerprint)) {
                                throw new AppSecurityException('A new device key is required to re-register.', 409, 'new_device_key_required');
                            }
                            $replacingKey = true;
                        } else {
                            $existing->forceFill([
                                'last_seen_at' => now(),
                                'version_name' => $data['version_name'],
                                'version_code' => (int) $data['version_code'],
                            ])->save();
                            $challenge->update(['used_at' => now()]);
                            return $existing->refresh();
                        }
                    }

                    $roleSlug = strtolower((string) $user->role?->slug);
                    $userType = in_array($roleSlug, ['customer', 'client'], true) ? 'customer'
                        : (in_array($roleSlug, ['admin', 'super-admin', 'super_admin', 'supper-admin'], true) ? 'admin' : 'staff');
                    if (!$existing) {
                        // Serialize registrations for the account so parallel new installations cannot exceed its configured limit.
                        DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
                        $deviceLimit = max(0, (int) $this->policy->value('max_devices_'.$userType));
                        if ($deviceLimit > 0) {
                            $activeDevices = RegisteredDevice::query()->where('user_id', $user->id)
                                ->whereNull('revoked_at')->whereNotIn('device_status', ['revoked', 'rejected'])->count();
                            if ($activeDevices >= $deviceLimit) {
                                throw new AppSecurityException('This account has reached its registered device limit.', 429, 'registration_device_limit_exceeded');
                            }
                        }
                    }
                    $attestation = !empty($data['attestation_chain'])
                        ? $this->attestationVerifier->verify(
                            $data['attestation_chain'],
                            $data['challenge'],
                            $data['public_key'],
                            $data['package_name'],
                            $data['signing_sha256']
                        )
                        : null;
                    if ($this->policy->value('hardware_attestation') === 'required' && !($attestation['verified'] ?? false)) {
                        throw new AppSecurityException('Verified hardware key attestation is required for device registration.', 403, 'registration_attestation_failed');
                    }
                    $attestationVerified = (bool) ($attestation['verified'] ?? false);
                    $attestationPolicyAccepts = empty($data['attestation_chain']) || $attestationVerified;
                    $osPolicy = app(AttestationOsPolicyService::class)->evaluate($attestation, $attestationVerified);
                    if (!$osPolicy['compliant']) {
                        throw new AppSecurityException(AttestationOsPolicyService::MESSAGE, 428, 'registration_attestation_failed');
                    }
                    $velocity = (bool) $this->policy->value('risk_enabled')
                        ? $this->registrationVelocity->observe($observedIp) : null;
                    $velocityDetected = $velocity !== null;
                    $riskScore = (bool) $this->policy->value('risk_enabled')
                        ? (empty($data['attestation_chain']) ? (int) ($this->policy->signalDefinition('attestation_missing')['points'] ?? 10)
                            : ($attestationVerified ? 0 : (int) ($this->policy->signalDefinition('attestation_verification_failed')['points'] ?? 40)))
                            + ($velocityDetected ? (int) ($this->policy->signalDefinition('registration_velocity')['points'] ?? 20) : 0)
                            + ($crossAccountReuse['detected'] ? (int) ($this->policy->signalDefinition('cross_account_key_reuse')['points'] ?? 35) : 0)
                        : 0;
                    $autoApprove = $userType === 'customer'
                        && (bool) $this->policy->value('customer_auto_approval')
                        && $attestationPolicyAccepts
                        && !$velocityDetected
                        && !$crossAccountReuse['detected']
                        && $riskScore < (int) $this->policy->value('risk_review_threshold');
                    $requiresApproval = $userType !== 'customer'
                        && (bool) $this->policy->value('device_approval_required');

                    $registrationAttributes = [
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $user->id,
                        'user_type' => $userType,
                        'installation_uuid' => $data['installation_uuid'],
                        'public_key' => $data['public_key'],
                        'public_key_fingerprint' => $fingerprint,
                        'package_name' => $data['package_name'],
                        'signing_sha256' => strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $data['signing_sha256']) ?? ''),
                        'version_name' => $data['version_name'],
                        'version_code' => (int) $data['version_code'],
                        'android_sdk' => $data['android_sdk'] ?? null,
                        'android_version' => $data['android_version'] ?? null,
                        'device_model' => $data['device_model'] ?? null,
                        'device_status' => $requiresApproval ? 'pending' : ($autoApprove ? 'approved' : 'pending'),
                        'approved_at' => $autoApprove ? now() : null,
                        'attestation_level' => $attestation['security_level'] ?? 'unverified',
                        'risk_score' => $riskScore,
                        'registered_at' => now(),
                        'last_seen_at' => now(),
                    ];
                    if ($replacingKey && $existing) {
                        unset($registrationAttributes['uuid'], $registrationAttributes['registered_at']);
                        $device = $existing->forceFill($registrationAttributes)->save() ? $existing->refresh() : $existing;
                    } else {
                        $device = RegisteredDevice::query()->create($registrationAttributes);
                    }

                    app(SecurityAlertService::class)->record(
                        'device-registered:'.$device->uuid,
                        'device_registered',
                        $requiresApproval ? 'medium' : 'low',
                        $userType === 'staff' ? 'New staff installation registered' : 'New customer installation registered',
                        "A new {$userType} installation was registered with status {$device->device_status}. Review the device if approval is required.",
                        $device->id,
                        $user->id,
                        ['user_type' => $userType, 'device_status' => $device->device_status]
                    );

                    $this->riskEngine->recordSignal(
                        $device->id,
                        empty($data['attestation_chain']) ? 'attestation_missing'
                            : ($attestationVerified ? 'attestation_verified' : 'attestation_verification_failed'),
                        ['reason' => $attestation['reason'] ?? 'not_supplied']
                    );
                    if ($velocityDetected) {
                        $this->riskEngine->recordSignal($device->id, 'registration_velocity', [
                            'registrations_in_window' => $velocity['count'],
                            'threshold' => $velocity['threshold'],
                            'window_seconds' => $velocity['window_seconds'],
                        ]);
                    }

                    if ($crossAccountReuse['detected']) {
                        $this->riskEngine->recordSignal($device->id, 'cross_account_key_reuse', [
                            'linked_account_count' => $crossAccountReuse['linked_account_count'],
                        ]);
                    }

                    if ($device->device_status === 'pending' && in_array($userType, ['staff', 'customer'], true)) {
                        SendAppSecurityUserNotification::dispatch(
                            (int) $user->id,
                            'app-security:registration-pending:'.$device->uuid,
                            'App installation awaiting review',
                            'Your KNEAYERNG installation was registered and is awaiting review. You will be notified when its status changes.',
                        )->afterCommit();
                    }

                    if (!empty($data['attestation_chain'])) {
                        $device->attestations()->create([
                            'challenge_id' => $challenge->id,
                            'verification_status' => $attestationVerified ? 'verified' : 'pending',
                            'security_level' => $attestation['security_level'] ?? null,
                            'verification_summary' => [
                                'reason' => $attestation['reason'] ?? 'not_verified',
                                'root_fingerprint' => $attestation['root_fingerprint'] ?? null,
                                'attestation_version' => $attestation['attestation_version'] ?? null,
                                'os_version' => $attestation['os_version'] ?? null,
                                'os_patch_level' => $attestation['os_patch_level'] ?? null,
                            ],
                            'verified_at' => $attestationVerified ? now() : null,
                            'evidence_encrypted' => Crypt::encryptString(json_encode($data['attestation_chain'], JSON_THROW_ON_ERROR)),
                            'evidence_expires_at' => now()->addDays((int) config('app_security.attestation_evidence_retention_days', 90)),
                        ]);
                    }

                    $challenge->update(['device_id' => $device->id, 'used_at' => now()]);
                    return $device;
                }, 3);
            });
        } catch (AppSecurityException $exception) {
            if ($exception->securityEvent !== null) {
                app(RegistrationSecurityEventReporter::class)->report(
                    $user,
                    (string) $data['challenge_id'],
                    $exception->securityEvent,
                    $observedIp
                );
            }
            throw $exception;
        } catch (LockTimeoutException) {
            throw new AppSecurityException('Device registration is busy. Please retry shortly.', 503);
        }
    }

    public function status(User $user): array
    {
        $devices = RegisteredDevice::query()
            ->where('user_id', $user->id)
            ->latest('registered_at')
            ->get(['uuid', 'user_type', 'device_status', 'attestation_level', 'risk_score', 'registered_at', 'last_seen_at']);

        return [
            'authorized' => $devices->contains(fn (RegisteredDevice $device) => $device->device_status === 'approved'),
            'devices' => $devices,
        ];
    }
}
