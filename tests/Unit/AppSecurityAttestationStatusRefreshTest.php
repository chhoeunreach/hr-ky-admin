<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AppSecurityAttestationStatusRefreshTest extends TestCase
{
    public function test_refresh_writes_a_private_freshness_bounded_snapshot(): void
    {
        $directory = sys_get_temp_dir() . '/ky-attestation-' . bin2hex(random_bytes(5));
        $path = $directory . '/status.json';
        config(['app_security.attestation_crl_path' => $path]);
        Http::fake([
            'https://android.googleapis.com/attestation/status' => Http::response([
                'entries' => ['ab12cd' => ['status' => 'REVOKED', 'reason' => 'KEY_COMPROMISE']],
            ], 200, ['Cache-Control' => 'public, max-age=28800']),
        ]);

        $this->assertSame(0, Artisan::call('app-security:refresh-attestation-status'));
        $snapshot = json_decode((string) file_get_contents($path), true, 16, JSON_THROW_ON_ERROR);
        $this->assertSame('REVOKED', $snapshot['entries']['ab12cd']['status']);
        $this->assertSame(28800, $snapshot['cache_ttl_seconds']);
        $this->assertLessThanOrEqual(time(), $snapshot['fetched_at']);

        @unlink($path);
        @rmdir($directory);
    }

    public function test_invalid_remote_snapshot_does_not_replace_existing_data(): void
    {
        $directory = sys_get_temp_dir() . '/ky-attestation-' . bin2hex(random_bytes(5));
        mkdir($directory, 0700, true);
        $path = $directory . '/status.json';
        file_put_contents($path, '{"entries":{"trusted":{"status":"REVOKED"}}}');
        config(['app_security.attestation_crl_path' => $path]);
        Http::fake([
            'https://android.googleapis.com/attestation/status' => Http::response(['unexpected' => []], 200),
        ]);

        $this->assertSame(1, Artisan::call('app-security:refresh-attestation-status'));
        $this->assertSame('{"entries":{"trusted":{"status":"REVOKED"}}}', file_get_contents($path));

        @unlink($path);
        @rmdir($directory);
    }
}
