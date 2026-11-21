<?php

namespace App\Modules\AppSecurity\Services;

use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Models\SecuritySession;

/** Issues short-lived, device/account-bound leases for limited offline app access. */
class AuthorizationLeaseService
{
    public function __construct(private readonly SecurityPolicyService $policy) {}

    public function issue(RegisteredDevice $device, SecuritySession $session): ?array
    {
        if ($device->device_status !== 'approved' || $device->revoked_at || $device->blocked_at
            || $session->revoked_at || (int) $session->device_id !== (int) $device->id
            || (int) $session->user_id !== (int) $device->user_id) {
            return null;
        }
        $configuredGrace = min(86400, max(0, (int) $this->policy->value('offline_grace_seconds')));
        if ($configuredGrace === 0) return null;

        $privateKeyPath = config('app_security.lease_signing_private_key_path');
        if (!is_string($privateKeyPath) || $privateKeyPath === '' || !is_file($privateKeyPath)) {
            return null;
        }
        if (!is_readable($privateKeyPath)) return null;
        $privatePem = file_get_contents($privateKeyPath);
        if (!is_string($privatePem)) return null;
        $privateKey = openssl_pkey_get_private($privatePem);
        if ($privateKey === false) return null;
        $keyDetails = openssl_pkey_get_details($privateKey);
        if (($keyDetails['type'] ?? null) !== OPENSSL_KEYTYPE_EC || (int) ($keyDetails['bits'] ?? 0) < 256) {
            return null;
        }
        $keyId = (string) config('app_security.lease_signing_key_id', 'lease-1');
        if (!preg_match('/^[A-Za-z0-9._-]{1,64}$/', $keyId)) return null;

        $issuedAt = now()->timestamp;
        $sessionExpiresAt = $session->expires_at?->getTimestamp() ?? $issuedAt;
        $leaseSeconds = min($configuredGrace, $sessionExpiresAt - $issuedAt);
        if ($leaseSeconds < 1) return null;

        $claims = [
            'version' => 1,
            'lease_id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => (int) $device->user_id,
            'device_id' => (string) $device->uuid,
            'installation_uuid' => (string) $device->installation_uuid,
            'public_key_fingerprint' => (string) $device->public_key_fingerprint,
            'package_name' => (string) $device->package_name,
            'signing_sha256' => strtoupper((string) $device->signing_sha256),
            'version_code' => (int) $device->version_code,
            'session_id' => (string) $session->uuid,
            'device_status' => (string) $device->device_status,
            'scope' => 'offline_app_access',
            'issued_at' => $issuedAt,
            'expires_at' => $issuedAt + $leaseSeconds,
            'lease_seconds' => $leaseSeconds,
        ];
        $payload = json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256) !== true) return null;

        return [
            'key_id' => $keyId,
            'payload' => self::base64UrlEncode($payload),
            'signature' => self::base64UrlEncode($signature),
        ];
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
