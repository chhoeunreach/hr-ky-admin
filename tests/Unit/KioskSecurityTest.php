<?php

namespace Tests\Unit;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Http\Middleware\AuthenticateKioskDevice;
use App\Models\KioskDevice;
use Illuminate\Http\Request;
use Tests\TestCase;

class KioskSecurityTest extends TestCase
{
    public function test_missing_device_token_is_rejected_without_touching_the_database(): void
    {
        $response = (new AuthenticateKioskDevice())->handle(
            Request::create('/api/kiosk/v1/bootstrap'),
            fn () => response()->json(['status' => true]),
        );

        $this->assertSame(401, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['status']);
    }

    public function test_device_secrets_are_never_serialized(): void
    {
        $device = new KioskDevice([
            'name' => 'Front door',
            'token_hash' => str_repeat('a', 64),
            'admin_pin_hash' => 'secret',
        ]);

        $serialized = $device->toArray();

        $this->assertArrayNotHasKey('token_hash', $serialized);
        $this->assertArrayNotHasKey('admin_pin_hash', $serialized);
    }

    public function test_face_is_a_supported_attendance_type(): void
    {
        $this->assertSame('face', EmployeeAttendanceTypeEnum::face->value);
    }
}
