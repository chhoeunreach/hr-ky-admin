<?php

namespace App\Modules\AppSecurity\Services;

use App\Modules\AppSecurity\Models\DeviceAttestation;
use App\Modules\AppSecurity\Models\RegisteredDevice;
use Illuminate\Support\Facades\Schema;

/** Enforces server-configured minimums against verified Key Attestation claims. */
class AttestationOsPolicyService
{
    public const MESSAGE = 'This Android OS version or security patch does not meet the current device policy.';

    public function enabled(): bool
    {
        return $this->minimumOsVersion() > 0 || $this->minimumPatchLevel() > 0;
    }

    public function evaluate(?array $claims, bool $verified = true): array
    {
        $minimumOs = $this->minimumOsVersion();
        $minimumPatch = $this->minimumPatchLevel();
        $reasons = [];
        $osVersion = is_array($claims) && is_numeric($claims['os_version'] ?? null)
            ? (int) $claims['os_version'] : null;
        $patchLevel = is_array($claims) && is_numeric($claims['os_patch_level'] ?? null)
            ? (int) $claims['os_patch_level'] : null;

        if ($minimumOs === 0 && $minimumPatch === 0) {
            return ['compliant' => true, 'reasons' => [], 'os_version' => $osVersion, 'os_patch_level' => $patchLevel];
        }
        if (!$verified || !is_array($claims)) {
            $reasons[] = 'verified_attestation_required';
        } else {
            if ($minimumOs > 0 && ($osVersion === null || $osVersion < $minimumOs)) {
                $reasons[] = $osVersion === null ? 'os_version_claim_missing' : 'os_version_below_minimum';
            }
            if ($minimumPatch > 0 && ($patchLevel === null || $patchLevel < $minimumPatch)) {
                $reasons[] = $patchLevel === null ? 'os_patch_claim_missing' : 'os_patch_below_minimum';
            }
        }

        return [
            'compliant' => $reasons === [],
            'reasons' => $reasons,
            'minimum_os_version' => $minimumOs,
            'minimum_os_patch_level' => $minimumPatch,
            'os_version' => $osVersion,
            'os_patch_level' => $patchLevel,
        ];
    }

    public function forDevice(RegisteredDevice $device): array
    {
        if (!$this->enabled()) {
            return ['compliant' => true, 'reasons' => []];
        }
        if (!Schema::hasTable('app_device_attestations')) {
            return $this->evaluate(null, false);
        }

        $attestation = DeviceAttestation::query()
            ->where('device_id', $device->id)
            ->where('verification_status', 'verified')
            ->orderByDesc('verified_at')
            ->orderByDesc('id')
            ->first();

        return $this->evaluate($attestation?->verification_summary, $attestation !== null);
    }

    private function minimumOsVersion(): int
    {
        return max(0, (int) config('app_security.minimum_attested_android_os_version', 0));
    }

    private function minimumPatchLevel(): int
    {
        return max(0, (int) config('app_security.minimum_attested_android_patch_level', 0));
    }
}
