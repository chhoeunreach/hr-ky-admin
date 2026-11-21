<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\AppSecurity\Controllers\AppSecurityController;
use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Services\DeviceRegistrationService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class AppSecurityRegistrationControllerTest extends TestCase
{
    public function test_registration_passes_only_the_server_observed_source_ip_to_the_service(): void
    {
        $user = new User();
        $device = new RegisteredDevice(['uuid' => '9f4c51e0-3e9e-4bf0-9da7-63def48ba144', 'device_status' => 'pending']);
        $payload = [
            'challenge_id' => '95bf0c89-6d6d-4995-ad38-cd91e94c44f2',
            'challenge' => 'server-issued-challenge',
            'challenge_signature' => 'proof',
            'installation_uuid' => '2c065558-4513-4da4-a72f-423c2b0d71b7',
            'public_key' => 'public-key',
            'package_name' => 'com.kneayerng.hrms',
            'signing_sha256' => str_repeat('A', 64),
            'version_name' => '1.0.0',
            'version_code' => 100,
            'android_sdk' => 35,
        ];
        $registration = Mockery::mock(DeviceRegistrationService::class);
        $registration->shouldReceive('register')
            ->once()
            ->with($user, Mockery::on(fn (array $data): bool => $data === $payload), '203.0.113.10')
            ->andReturn($device);

        $request = Request::create('/api/app-security/v1/devices/register', 'POST', $payload, [], [], [
            'REMOTE_ADDR' => '203.0.113.10',
        ]);
        $request->setUserResolver(fn (?string $guard = null) => $user);
        $request->headers->set('Accept', 'application/json');

        $response = app(AppSecurityController::class)->register($request, $registration);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['authorized']);
    }
}
