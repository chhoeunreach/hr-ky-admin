<?php

namespace App\Modules\AppSecurity\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Services\AppSecurityException;
use App\Modules\AppSecurity\Services\DeviceRegistrationService;
use App\Modules\AppSecurity\Services\DeviceRiskEngine;
use App\Modules\AppSecurity\Services\DeviceSessionService;
use App\Modules\AppSecurity\Services\ReleaseVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AppSecurityController extends Controller
{
    public function releaseCheck(Request $request, ReleaseVerificationService $releases): JsonResponse
    {
        $data = $request->validate([
            'package_name' => ['required', 'string', 'max:191'],
            'signing_sha256' => ['required', 'string', 'max:95'],
            'version_code' => ['required', 'integer', 'min:1'],
            'android_sdk' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        try {
            $result = $releases->check(
                $data['package_name'], $data['signing_sha256'],
                (int) $data['version_code'], (int) $data['android_sdk']
            );
            $latest = $result['latest'];
            return response()->json([
                'recognized' => true,
                'mandatory_update' => $result['mandatory_update'],
                'minimum_supported_version' => $result['minimum_supported_version'],
                'latest_release' => [
                    'version_name' => $latest->version_name,
                    'version_code' => $latest->version_code,
                    'apk_sha256' => $latest->apk_sha256,
                    'apk_size' => $latest->apk_size,
                    'release_notes' => $latest->release_notes,
                    'minimum_android_sdk' => (int) $latest->minimum_android_sdk,
                    'compatible_with_device' => (bool) $result['latest_compatible'],
                    'download_url' => URL::to('/app/download/android'),
                ],
            ]);
        } catch (AppSecurityException $exception) {
            return $this->securityError($exception);
        }
    }

    public function challenge(Request $request, DeviceRegistrationService $devices): JsonResponse
    {
        return $this->runSecurity(fn () => response()->json(
            $devices->issueChallenge($request->user('api'))
        ));
    }

    public function register(Request $request, DeviceRegistrationService $devices): JsonResponse
    {
        $data = $request->validate([
            'challenge_id' => ['required', 'uuid'],
            'challenge' => ['required', 'string', 'max:128'],
            'challenge_signature' => ['required', 'string', 'max:2048'],
            'installation_uuid' => ['required', 'uuid'],
            'public_key' => ['required', 'string', 'max:8192'],
            'package_name' => ['required', 'string', 'max:191'],
            'signing_sha256' => ['required', 'string', 'max:95'],
            'version_name' => ['required', 'string', 'max:80'],
            'version_code' => ['required', 'integer', 'min:1'],
            'android_sdk' => ['required', 'integer', 'min:1', 'max:1000'],
            'android_version' => ['nullable', 'string', 'max:80'],
            'device_model' => ['nullable', 'string', 'max:191'],
            'attestation_chain' => ['nullable', 'array', 'max:10'],
            'attestation_chain.*' => ['string', 'max:20000'],
        ]);

        return $this->runSecurity(function () use ($request, $devices, $data) {
            $device = $devices->register($request->user('api'), $data, $request->ip());
            return response()->json([
                'device_id' => $device->uuid,
                'status' => $device->device_status,
                'user_type' => $device->user_type,
                'attestation_level' => $device->attestation_level,
                'risk_score' => $device->risk_score,
                'authorized' => $device->device_status === 'approved',
            ], $device->device_status === 'approved' ? 200 : 202);
        });
    }

    public function status(Request $request, DeviceRegistrationService $devices): JsonResponse
    {
        $data = $request->validate(['installation_uuid' => ['required', 'uuid']]);
        $device = RegisteredDevice::query()
            ->where('user_id', $request->user('api')->id)
            ->where('installation_uuid', $data['installation_uuid'])
            ->first();
        if (!$device || $device->device_status === 're_registration_required') {
            return response()->json(['status' => 'registration_required', 'authorized' => false], 404);
        }

        return response()->json([
            'device_id' => $device->uuid,
            'status' => $device->device_status,
            'user_type' => $device->user_type,
            'attestation_level' => $device->attestation_level,
            'public_key_fingerprint' => $device->public_key_fingerprint,
            'risk_score' => $device->risk_score,
            'authorized' => $device->device_status === 'approved' && !$device->revoked_at,
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
        ]);
    }

    public function reportIntegrity(Request $request, DeviceRiskEngine $riskEngine): JsonResponse
    {
        $data = $request->validate([
            'signals' => ['required', 'array', 'min:1', 'max:3'],
            'signals.*' => ['required', 'string', 'distinct', 'in:root_detected,emulator_detected,instrumentation_detected,debug_build,debugger_attached,hooking_detected,vpn_detected,network_transport_wifi,network_transport_cellular,network_transport_ethernet'],
        ]);
        $device = RegisteredDevice::query()
            ->where('uuid', (string) $request->header('X-KY-Device'))
            ->where('user_id', $request->user('api')->id)
            ->firstOrFail();

        $score = null;
        foreach ($data['signals'] as $signal) {
            $score = $riskEngine->recordSignal($device->id, $signal, ['source' => 'client_heuristic']);
        }

        return response()->json([
            'recorded' => count($data['signals']),
            'risk_score' => $score['risk_score'] ?? $device->fresh()->risk_score,
            'risk_level' => $score['risk_level'] ?? null,
            'automatic_block' => false,
        ]);
    }

    public function heartbeat(Request $request, \App\Modules\AppSecurity\Services\AuthorizationLeaseService $leases): JsonResponse
    {
        $device = RegisteredDevice::query()
            ->where('uuid', (string) $request->header('X-KY-Device'))
            ->where('user_id', $request->user('api')->id)
            ->firstOrFail();
        if ($device->device_status !== 'approved' || $device->revoked_at || $device->blocked_at) {
            return response()->json(['authorized' => false, 'status' => $device->device_status], 423);
        }
        $session = \App\Modules\AppSecurity\Models\SecuritySession::query()
            ->where('uuid', (string) $request->header('X-KY-Session'))
            ->where('device_id', $device->id)
            ->where('user_id', $request->user('api')->id)
            ->whereNull('revoked_at')->first();

        $authorizationLease = $session ? $leases->issue($device, $session) : null;
        $serverTime = now();

        return response()->json([
            'authorized' => true,
            'device_id' => $device->uuid,
            'status' => $device->device_status,
            'server_time' => $serverTime->toIso8601String(),
            'session_expires_at' => $session?->expires_at?->toIso8601String(),
            'authorization_lease' => $authorizationLease,
        ]);
    }

    public function openSession(Request $request, DeviceSessionService $sessions): JsonResponse
    {
        $data = $request->validate(['device_id' => ['required', 'uuid']]);
        return $this->runSecurity(fn () => response()->json(
            $sessions->open($request->user('api'), $data['device_id'])
        ));
    }

    public function refreshSession(Request $request, DeviceSessionService $sessions): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string', 'max:128'],
            'signature' => ['required', 'string', 'max:2048'],
        ]);
        return $this->runSecurity(fn () => response()->json(
            $sessions->refresh($request->user('api'), $data['refresh_token'], $data['signature'])
        ));
    }

    public function logout(Request $request, DeviceSessionService $sessions): JsonResponse
    {
        $data = $request->validate(['session_id' => ['required', 'uuid']]);
        $sessions->revoke($request->user('api'), $data['session_id']);
        return response()->json(['revoked' => true]);
    }

    public function config(\App\Modules\AppSecurity\Services\SecurityPolicyService $policy): JsonResponse
    {
        $enabled = (bool) config('app_security.enabled');
        $setting = fn (string $key, string $fallback) => $enabled
            ? $policy->value($key)
            : config('app_security.'.$fallback);

        return response()->json([
            'enabled' => $enabled,
            'device_approval_required' => (bool) $setting('device_approval_required', 'device_approval'),
            'hardware_attestation' => $setting('hardware_attestation', 'hardware_attestation'),
            'heartbeat_seconds' => (int) $setting('heartbeat_seconds', 'heartbeat_seconds'),
            'session_ttl_seconds' => (int) $setting('session_ttl_seconds', 'session_ttl_seconds'),
            'offline_grace_seconds' => (int) $setting('offline_grace_seconds', 'offline_grace_seconds'),
            'challenge_ttl_seconds' => (int) $setting('challenge_ttl_seconds', 'challenge_ttl_seconds'),
        ])->header('Cache-Control', 'no-store, private');
    }

    private function runSecurity(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (AppSecurityException $exception) {
            return $this->securityError($exception);
        }
    }

    private function securityError(AppSecurityException $exception): JsonResponse
    {
        return response()->json([
            'error' => 'app_security',
            'message' => $exception->getMessage(),
        ], $exception->status);
    }
}
