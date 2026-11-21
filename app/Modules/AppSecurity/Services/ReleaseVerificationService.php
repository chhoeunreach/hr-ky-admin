<?php

namespace App\Modules\AppSecurity\Services;

use App\Modules\AppSecurity\Models\SecurityRelease;

class ReleaseVerificationService
{
    public function check(string $packageName, string $signingSha256, int $versionCode, ?int $androidSdk = null): array
    {
        $packageName = trim($packageName);
        if ($packageName !== (string) config('app_security.package_name')) {
            throw new AppSecurityException('Application package name is not authorized.', 403);
        }
        $signingSha256 = strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $signingSha256) ?? '');
        if ($packageName === '' || strlen($signingSha256) !== 64 || $versionCode < 1) {
            throw new AppSecurityException('Release identity is invalid.', 422);
        }

        $release = SecurityRelease::query()
            ->where('package_name', $packageName)
            ->where('version_code', $versionCode)
            ->first();

        if (!$release || !in_array($release->release_status, ['active', 'archived'], true)) {
            throw new AppSecurityException('This application release is not recognized.', 403);
        }
        if (!hash_equals(strtoupper($release->signing_sha256), $signingSha256)) {
            throw new AppSecurityException('The application signing certificate is not recognized.', 403);
        }

        $latest = SecurityRelease::query()
            ->where('package_name', $packageName)
            ->where('release_status', 'active')
            ->orderByDesc('version_code')
            ->first();
        if (!$latest) {
            throw new AppSecurityException('No active application release is available.', 503);
        }

        if ($androidSdk !== null && $androidSdk < (int) $release->minimum_android_sdk) {
            throw new AppSecurityException('This Android version cannot run the installed application release.', 428);
        }

        $minimumVersion = (int) $latest->minimum_supported_version;
        $mandatory = $versionCode < (int) $latest->version_code
            && ((bool) $latest->is_mandatory || $versionCode < $minimumVersion);
        $latestCompatible = $androidSdk === null || $androidSdk >= (int) $latest->minimum_android_sdk;
        if ($mandatory && !$latestCompatible) {
            throw new AppSecurityException('This Android version cannot install the required application update.', 428);
        }

        return [
            'recognized' => true,
            'mandatory_update' => $mandatory,
            'release' => $release,
            'latest' => $latest,
            'minimum_supported_version' => $minimumVersion,
            'latest_compatible' => $latestCompatible,
        ];
    }
}
