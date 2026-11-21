<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Models\SecuritySession;
use App\Modules\AppSecurity\Services\AuthorizationLeaseService;
use Illuminate\Support\Str;
use Tests\TestCase;

class AppSecurityAuthorizationLeaseTest extends TestCase
{
    private string $privateKeyPath;
    private string $publicKeyPem;

    protected function setUp(): void
    {
        parent::setUp();
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        self::assertNotFalse($key);
        self::assertTrue(openssl_pkey_export($key, $privatePem));
        $this->publicKeyPem = openssl_pkey_get_details($key)['key'];
        $this->privateKeyPath = tempnam(sys_get_temp_dir(), 'ky-lease-test-');
        file_put_contents($this->privateKeyPath, $privatePem);
        config([
            'app_security.offline_grace_seconds' => 600,
            'app_security.lease_signing_private_key_path' => $this->privateKeyPath,
            'app_security.lease_signing_key_id' => 'test-key-1',
        ]);
    }

    protected function tearDown(): void
    {
        @unlink($this->privateKeyPath);
        parent::tearDown();
    }

    public function test_lease_is_signed_and_bound_to_the_active_device_session(): void
    {
        $device = new RegisteredDevice();
        $device->forceFill([
            'id' => 4,
            'uuid' => (string) Str::uuid(),
            'user_id' => 42,
            'installation_uuid' => (string) Str::uuid(),
            'public_key_fingerprint' => str_repeat('A', 64),
            'package_name' => 'com.kneayerng.hrms',
            'version_code' => 18,
            'device_status' => 'approved',
        ]);
        $session = new SecuritySession();
        $session->forceFill([
            'device_id' => 4,
            'user_id' => 42,
            'uuid' => (string) Str::uuid(),
            'expires_at' => now()->addSeconds(240),
        ]);

        $lease = app(AuthorizationLeaseService::class)->issue($device, $session);
        self::assertSame('test-key-1', $lease['key_id']);
        $payload = base64_decode(strtr($lease['payload'], '-_', '+/'));
        $claims = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        $signature = base64_decode(strtr($lease['signature'], '-_', '+/'));

        $this->assertSame(42, $claims['user_id']);
        $this->assertSame($device->uuid, $claims['device_id']);
        $this->assertSame($device->installation_uuid, $claims['installation_uuid']);
        $this->assertSame($session->uuid, $claims['session_id']);
        $this->assertSame('offline_app_access', $claims['scope']);
        $this->assertLessThanOrEqual(240, $claims['lease_seconds']);
        $this->assertSame(1, openssl_verify($payload, $signature, $this->publicKeyPem, OPENSSL_ALGO_SHA256));
    }

    public function test_lease_is_not_issued_when_offline_grace_is_disabled_or_key_missing(): void
    {
        $device = new RegisteredDevice();
        $device->forceFill([
            'id' => 4,
            'uuid' => (string) Str::uuid(),
            'user_id' => 42,
            'device_status' => 'approved',
        ]);
        $session = new SecuritySession();
        $session->forceFill([
            'uuid' => (string) Str::uuid(),
            'device_id' => 4,
            'user_id' => 42,
            'expires_at' => now()->addMinutes(5),
        ]);

        config(['app_security.offline_grace_seconds' => 0]);
        $this->assertNull(app(AuthorizationLeaseService::class)->issue($device, $session));

        config([
            'app_security.offline_grace_seconds' => 60,
            'app_security.lease_signing_private_key_path' => '/not/configured/private-key.pem',
        ]);
        $this->assertNull(app(AuthorizationLeaseService::class)->issue($device, $session));
    }
}
