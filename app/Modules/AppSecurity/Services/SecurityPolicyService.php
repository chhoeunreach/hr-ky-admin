<?php

namespace App\Modules\AppSecurity\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Validated database overrides for operational policy; secrets and hard gates stay in env. */
class SecurityPolicyService
{
    private const MISSING = '__app_security_setting_missing__';
    private const RISK_SIGNAL_CONFIG = [
        'risk_points_attestation_missing' => 'attestation_missing',
        'risk_points_attestation_verification_failed' => 'attestation_verification_failed',
        'risk_points_attestation_os_policy_failed' => 'attestation_os_policy_failed',
        'risk_points_root_detected' => 'root_detected',
        'risk_points_emulator_detected' => 'emulator_detected',
        'risk_points_instrumentation_detected' => 'instrumentation_detected',
        'risk_points_debug_build' => 'debug_build',
        'risk_points_debugger_attached' => 'debugger_attached',
        'risk_points_hooking_detected' => 'hooking_detected',
        'risk_points_vpn_detected' => 'vpn_detected',
        'risk_points_request_nonce_replayed' => 'request_nonce_replayed',
        'risk_points_security_session_invalid' => 'security_session_invalid',
        'risk_points_request_timestamp_expired' => 'request_timestamp_expired',
        'risk_points_device_not_authorized' => 'device_not_authorized',
        'risk_points_registration_velocity' => 'registration_velocity',
        'risk_points_cross_account_key_reuse' => 'cross_account_key_reuse',
    ];

    private const SETTINGS = [
        'device_approval_required' => ['type' => 'bool', 'config' => 'device_approval'],
        'customer_auto_approval' => ['type' => 'bool', 'config' => 'customer_auto_approval'],
        'max_devices_staff' => ['type' => 'int', 'min' => 0, 'max' => 50, 'config' => 'max_devices_staff'],
        'max_devices_customer' => ['type' => 'int', 'min' => 0, 'max' => 50, 'config' => 'max_devices_customer'],
        'max_devices_admin' => ['type' => 'int', 'min' => 0, 'max' => 50, 'config' => 'max_devices_admin'],
        'hardware_attestation' => ['type' => 'enum', 'values' => ['preferred', 'required'], 'config' => 'hardware_attestation'],
        'heartbeat_seconds' => ['type' => 'int', 'min' => 30, 'max' => 900, 'config' => 'heartbeat_seconds'],
        'session_ttl_seconds' => ['type' => 'int', 'min' => 60, 'max' => 3600, 'config' => 'session_ttl_seconds'],
        'max_sessions_per_device' => ['type' => 'int', 'min' => 1, 'max' => 20, 'config' => 'max_sessions_per_device'],
        'offline_grace_seconds' => ['type' => 'int', 'min' => 0, 'max' => 86400, 'config' => 'offline_grace_seconds'],
        'refresh_ttl_seconds' => ['type' => 'int', 'min' => 300, 'max' => 7776000, 'config' => 'refresh_ttl_seconds'],
        'challenge_ttl_seconds' => ['type' => 'int', 'min' => 30, 'max' => 600, 'config' => 'challenge_ttl_seconds'],
        'nonce_ttl_seconds' => ['type' => 'int', 'min' => 60, 'max' => 600, 'config' => 'nonce_ttl_seconds'],
        'risk_enabled' => ['type' => 'bool', 'config' => 'risk_enabled'],
        'risk_window_hours' => ['type' => 'int', 'min' => 1, 'max' => 720, 'config' => 'risk_window_hours'],
        'risk_review_threshold' => ['type' => 'int', 'min' => 0, 'max' => 100, 'config' => 'risk_review_threshold'],
        'risk_block_threshold' => ['type' => 'int', 'min' => 1, 'max' => 100, 'config' => 'risk_block_threshold'],
        'risk_policy_version' => ['type' => 'string', 'max' => 40, 'config' => 'risk_policy_version'],
        'risk_points_attestation_missing' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_attestation_verification_failed' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_attestation_os_policy_failed' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_root_detected' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_emulator_detected' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_instrumentation_detected' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_debug_build' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_debugger_attached' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_hooking_detected' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_vpn_detected' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_request_nonce_replayed' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_security_session_invalid' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_request_timestamp_expired' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_device_not_authorized' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_registration_velocity' => ['type' => 'int', 'min' => 0, 'max' => 100],
        'risk_points_cross_account_key_reuse' => ['type' => 'int', 'min' => 0, 'max' => 100],
    ];

    public function value(string $key, bool $useCache = true): mixed
    {
        $riskSignal = array_key_exists($key, self::RISK_SIGNAL_CONFIG);
        if (!$riskSignal && !isset(self::SETTINGS[$key])) {
            throw new \InvalidArgumentException("Unknown App Security policy key: {$key}");
        }
        $cacheKey = 'app-security-policy:'.$key;
        $read = function () use ($key) {
            $settingKey = $key;
            if (Schema::hasTable('app_security_settings')) {
                $raw = DB::table('app_security_settings')->where('setting_key', $settingKey)->value('setting_value');
                if ($raw !== null) {
                    if (!is_string($raw)) return $raw;
                    $decoded = json_decode($raw, true);
                    return json_last_error() === JSON_ERROR_NONE ? $decoded : self::MISSING;
                }
            }
            return self::MISSING;
        };
        $stored = $useCache ? Cache::remember($cacheKey, now()->addSeconds(30), $read) : $read();
        if ($stored !== self::MISSING) return $stored;
        if ($riskSignal) {
            $signal = self::RISK_SIGNAL_CONFIG[$key];
            return (int) config("app_security.risk_signals.{$signal}.points", 0);
        }
        return config('app_security.'.self::SETTINGS[$key]['config']);
    }

    public function signalDefinition(string $signal): ?array
    {
        $definition = config("app_security.risk_signals.{$signal}");
        if (!is_array($definition)) return null;
        foreach (self::RISK_SIGNAL_CONFIG as $setting => $configuredSignal) {
            if ($configuredSignal === $signal) {
                $definition['points'] = (int) $this->value($setting);
                break;
            }
        }
        return $definition;
    }

    public function snapshot(bool $useCache = true): array
    {
        $values = [];
        foreach (self::SETTINGS as $key => $_definition) $values[$key] = $this->value($key, $useCache);
        // This deployment gate intentionally cannot be changed from the dashboard.
        $values['enabled'] = (bool) config('app_security.enabled', false);
        return $values;
    }

    public function update(array $values, ?int $adminId, ?callable $audit = null): array
    {
        $change = DB::transaction(function () use ($values, $adminId, $audit): array {
            $before = $this->snapshot(useCache: false);
            foreach ($values as $key => $value) {
                if (!isset(self::SETTINGS[$key])) continue;
                $definition = self::SETTINGS[$key];
                if ($definition['type'] === 'bool') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } elseif ($definition['type'] === 'int') {
                    $value = (int) $value;
                } elseif ($definition['type'] === 'string') {
                    $value = trim((string) $value);
                }
                $attributes = ['setting_value' => json_encode($value, JSON_THROW_ON_ERROR),
                    'updated_by' => $adminId, 'updated_at' => now()];
                DB::table('app_security_settings')->upsert(
                    [$attributes + ['setting_key' => $key, 'created_at' => now()]],
                    ['setting_key'],
                    ['setting_value', 'updated_by', 'updated_at'],
                );
            }
            $after = $this->snapshot(useCache: false);
            if ($audit !== null) $audit($before, $after);
            return compact('before', 'after');
        }, 3);
        foreach (array_keys($values) as $key) Cache::forget('app-security-policy:'.$key);
        return $change;
    }
}
