<?php

namespace App\Modules\AppSecurity\Services;

use Symfony\Component\Process\Process;

class ApkInspectionService
{
    /** Returns facts extracted from APK contents and its signed certificate. */
    public function inspect(string $apkPath): array
    {
        $aapt = (string) config('app_security.aapt_path');
        $apksigner = (string) config('app_security.apksigner_path');
        $trusted = array_filter(array_map(
            static fn (string $value): string => strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $value) ?? ''),
            explode(',', (string) config('app_security.trusted_signing_sha256', ''))
        ));
        if ($aapt === '' || $apksigner === '' || !$trusted) {
            throw new AppSecurityException('APK publication is not configured: set aapt, apksigner, and trusted signer SHA-256 fingerprints.', 503);
        }

        $badging = $this->run([$aapt, 'dump', 'badging', $apkPath]);
        if (!preg_match("/^package: name='([^']+)' versionCode='([0-9]+)' versionName='([^']*)'/m", $badging, $package)) {
            throw new AppSecurityException('Could not read the APK package and version metadata.', 422);
        }
        $sdk = [];
        preg_match("/^sdkVersion:'([0-9]+)'/m", $badging, $sdk);
        $targetSdk = [];
        preg_match("/^targetSdkVersion:'([0-9]+)'/m", $badging, $targetSdk);

        $signatureOutput = $this->run([$apksigner, 'verify', '--verbose', '--print-certs', $apkPath]);
        if (!preg_match('/certificate SHA-256 digest:\s*([a-fA-F0-9: ]{64,95})/i', $signatureOutput, $certificate)) {
            throw new AppSecurityException('APK signer certificate could not be verified.', 422);
        }
        $signingSha256 = strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $certificate[1]) ?? '');
        if (!in_array($signingSha256, $trusted, true)) {
            throw new AppSecurityException('APK signer is not in the trusted signing certificate registry.', 422);
        }
        if ($package[1] !== (string) config('app_security.package_name')) {
            throw new AppSecurityException('APK package does not match the configured application ID.', 422);
        }

        return [
            'package_name' => $package[1],
            'version_code' => (int) $package[2],
            'version_name' => $package[3],
            'signing_sha256' => $signingSha256,
            'minimum_android_sdk' => isset($sdk[1]) ? (int) $sdk[1] : 28,
            'target_android_sdk' => isset($targetSdk[1]) ? (int) $targetSdk[1] : 0,
            'apk_sha256' => hash_file('sha256', $apkPath),
            'apk_size' => filesize($apkPath),
        ];
    }

    private function run(array $command): string
    {
        try {
            $process = new Process($command);
            $process->setTimeout(60);
            $process->mustRun();
            return $process->getOutput() . "\n" . $process->getErrorOutput();
        } catch (\Throwable $exception) {
            throw new AppSecurityException('APK inspection tool failed: ' . $exception->getMessage(), 422);
        }
    }
}
