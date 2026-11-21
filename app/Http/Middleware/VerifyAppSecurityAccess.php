<?php

namespace App\Http\Middleware;

use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Models\SecuritySession;
use App\Modules\AppSecurity\Services\AppSecurityException;
use App\Modules\AppSecurity\Services\DeviceRiskEngine;
use App\Modules\AppSecurity\Services\ReleaseVerificationService;
use App\Modules\AppSecurity\Services\SecurityPolicyService;
use App\Modules\AppSecurity\Services\SecuritySignatureService;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class VerifyAppSecurityAccess
{
    public function __construct(private SecurityPolicyService $policy)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $enabled = (bool) $this->policy->value('enabled');
        } catch (Throwable) {
            $enabled = (bool) config('app_security.enabled', false);
        }
        if (!$enabled) {
            return $next($request);
        }

        $deviceUuid = (string) $request->header('X-KY-Device');
        $sessionUuid = (string) $request->header('X-KY-Session');
        $sessionToken = (string) $request->header('X-KY-Session-Token');
        $timestamp = (string) $request->header('X-KY-Timestamp');
        $nonce = (string) $request->header('X-KY-Nonce');
        $signature = (string) $request->header('X-KY-Signature');

        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $deviceUuid)
            || !preg_match('/^[0-9a-fA-F-]{36}$/', $sessionUuid)
            || strlen($sessionToken) < 32
            || !ctype_digit($timestamp)
            || !preg_match('/^[A-Za-z0-9_-]{22,128}$/', $nonce)
            || $signature === '') {
            $this->recordEvent($request, 'security_headers_invalid', 'high');
            return $this->deny('Security request headers are missing or invalid.', 401);
        }

        $now = now()->timestamp;
        $policy = app(\App\Modules\AppSecurity\Services\SecurityPolicyService::class);
        $maxSkew = max(60, (int) $policy->value('nonce_ttl_seconds'));
        if (abs($now - (int) $timestamp) > $maxSkew) {
            $this->recordEvent($request, 'request_timestamp_expired', 'medium');
            return $this->deny('Security request timestamp expired.', 401);
        }

        $user = $request->user();
        if (!$user) {
            $this->recordEvent($request, 'security_authentication_missing', 'medium');
            return $this->deny('Authentication is required.', 401);
        }

        $device = RegisteredDevice::query()
            ->where('uuid', $deviceUuid)
            ->where('user_id', $user->id)
            ->first();
        if (!$device || $device->device_status !== 'approved' || $device->revoked_at || $device->blocked_at) {
            $this->recordEvent($request, 'device_not_authorized', 'high', (int) $user->id, $device?->id);
            return $this->deny('This installation is not authorized.', 423);
        }

        $attestationPolicy = app(\App\Modules\AppSecurity\Services\AttestationOsPolicyService::class)
            ->forDevice($device);
        if (!$attestationPolicy['compliant']) {
            $this->recordEvent($request, 'attestation_os_policy_failed', 'high', (int) $user->id, $device->id);
            return $this->deny(\App\Modules\AppSecurity\Services\AttestationOsPolicyService::MESSAGE, 428);
        }

        $blocked = DB::table('app_security_block_rules')
            ->where('is_active', true)
            ->where(function ($query) use ($device): void {
                $query->where(function ($target) use ($device): void {
                    $target->where('target_type', 'installation')
                        ->where('target_value', $device->installation_uuid);
                })->orWhere(function ($target) use ($device): void {
                    $target->where('target_type', 'device')
                        ->where('target_value', $device->uuid);
                })->orWhere(function ($target) use ($device): void {
                    $target->where('target_type', 'certificate')
                        ->where('target_value', strtolower($device->signing_sha256));
                })->orWhere(function ($target) use ($device): void {
                    $target->where('target_type', 'key_fingerprint')
                        ->where('target_value', strtolower($device->public_key_fingerprint));
                });
            })
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
        if ($blocked) {
            $this->recordEvent($request, 'device_block_rule_matched', 'high', (int) $user->id, $device->id);
            return $this->deny('This installation has been blocked.', 423);
        }

        try {
            $release = app(ReleaseVerificationService::class)->check(
                $device->package_name, $device->signing_sha256, (int) $device->version_code
            );
            if ($release['mandatory_update']) {
                $this->recordEvent($request, 'mandatory_release_update', 'medium', (int) $user->id, $device->id);
                return $this->deny('A mandatory application update is required.', 426);
            }
        } catch (AppSecurityException $exception) {
            $this->recordEvent($request, 'release_verification_failed', 'high', (int) $user->id, $device->id);
            return $this->deny($exception->getMessage(), $exception->status);
        }

        $session = SecuritySession::query()
            ->where('uuid', $sessionUuid)
            ->where('device_id', $device->id)
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();
        if (!$session || !hash_equals($session->session_token_sha256, hash('sha256', $sessionToken))) {
            $this->recordEvent($request, 'security_session_invalid', 'high', (int) $user->id, $device->id);
            return $this->deny('The device security session is invalid or expired.', 401);
        }

        $canonical = app(SecuritySignatureService::class)->canonicalRequest(
            $request->method(),
            $request->getRequestUri(),
            $request->getContent(),
            $timestamp,
            $nonce,
            $deviceUuid,
            $sessionUuid
        );
        if (!app(SecuritySignatureService::class)->verify($canonical, $signature, $device->public_key)) {
            $this->recordEvent($request, 'request_signature_invalid', 'critical', (int) $user->id, $device->id);
            return $this->deny('Request signature verification failed.', 401);
        }

        try {
            DB::transaction(function () use ($device, $session, $sessionUuid, $nonce, $policy): void {
                DB::table('app_security_nonces')->insert([
                    'device_id' => $device->id,
                    'session_uuid' => $sessionUuid,
                    'nonce_sha256' => hash('sha256', $nonce),
                    'expires_at' => now()->addSeconds((int) $policy->value('nonce_ttl_seconds')),
                    'consumed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $session->forceFill(['last_used_at' => now()])->save();
                $device->forceFill(['last_seen_at' => now()])->save();
            }, 3);
        } catch (QueryException $exception) {
            $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
            $driverCode = (int) ($exception->errorInfo[1] ?? 0);
            if ($sqlState === '23000' || in_array($driverCode, [19, 1062], true)) {
                $this->recordEvent($request, 'request_nonce_replayed', 'critical', (int) $user->id, $device->id);
                return $this->deny('Request nonce has already been used.', 409);
            }

            report($exception);
            return $this->deny('Security verification is temporarily unavailable.', 503);
        }

        // Intelligence is deliberately based on requests that already passed
        // authentication, device policy, release checks, and cryptographic proof.
        try {
            app(\App\Modules\AppSecurity\Services\DeviceIntelligenceService::class)
                ->recordAuthorizedRequest($device, $request);
        } catch (Throwable $exception) {
            // Analytics failures must never change the protected API response.
            report($exception);
        }

        return $next($request);
    }

    private function recordEvent(
        Request $request,
        string $eventType,
        string $severity,
        ?int $userId = null,
        ?int $deviceId = null,
    ): void {
        if ($deviceId !== null && (bool) config('app_security.risk_enabled', true)) {
            try {
                app(DeviceRiskEngine::class)->recordSignal($deviceId, $eventType, [
                    'method' => $request->method(),
                    'path' => $request->path(),
                ]);
            } catch (Throwable $exception) {
                // Risk telemetry must never turn a denied request into an allow.
                report($exception);
            }
        }
        if (!(bool) config('app_security.audit_enabled', true) || ! Schema::hasTable('app_security_logs')) return;
        try {
            DB::table('app_security_logs')->insert([
                'event_type' => $eventType,
                'severity' => $severity,
                'user_id' => $userId,
                'device_id' => $deviceId,
                'request_uuid' => (string) Str::uuid(),
                'ip_address' => $request->ip(),
                'details' => json_encode([
                    'method' => $request->method(),
                    'path' => $request->path(),
                ], JSON_THROW_ON_ERROR),
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            // Logging must not convert an authorization denial into an allow.
            report($exception);
        }
    }

    private function deny(string $message, int $status): JsonResponse
    {
        return response()->json(['error' => 'app_security', 'message' => $message], $status);
    }
}
