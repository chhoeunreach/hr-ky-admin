<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AttendanceApiController;
use App\Models\Attendance;
use App\Models\FaceProfile;
use App\Models\KioskDevice;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class FaceKioskAttendanceApiTest extends TestCase
{
    private string $originalConnection;
    private string $deviceToken;
    private KioskDevice $device;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = config('database.default');
        config([
            'database.default' => 'kiosk_testing',
            'database.connections.kiosk_testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('kiosk_testing');
        DB::reconnect('kiosk_testing');
        $this->createSchema();
        $this->seedKiosk();
    }

    protected function tearDown(): void
    {
        DB::disconnect('kiosk_testing');
        config(['database.default' => $this->originalConnection]);
        parent::tearDown();
    }

    public function test_admin_can_enroll_face_and_bootstrap_returns_decrypted_profile(): void
    {
        $embedding = array_fill(0, 192, 1 / sqrt(192));

        $this->withHeaders($this->headers([
            'X-Kiosk-Admin-Pin' => '123456',
        ]))->postJson(
            "/api/kiosk/v1/employees/{$this->employee->id}/face-profile",
            [
                'embedding' => $embedding,
                'model_version' => 'mobile_facenet_112_192_v1',
                'quality_score' => 0.94,
            ],
        )->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.employee_id', $this->employee->id);

        $rawEmbedding = DB::table('face_profiles')->value('embedding');
        $this->assertIsString($rawEmbedding);
        $this->assertStringNotContainsString((string) $embedding[0], $rawEmbedding);

        $this->withHeaders($this->headers())
            ->getJson('/api/kiosk/v1/bootstrap')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data.employees')
            ->assertJsonCount(192, 'data.employees.0.embedding');
    }

    public function test_qr_token_and_pin_are_exchanged_once_for_runtime_token(): void
    {
        $this->device->update(['provisioned_at' => null]);
        $qrToken = $this->deviceToken;

        $response = $this->withHeaders($this->headers([
            'X-Kiosk-Admin-Pin' => '123456',
        ]))->postJson('/api/kiosk/v1/provision')
            ->assertOk()
            ->assertJsonPath('status', true);

        $runtimeToken = $response->json('data.device_token');
        $this->assertIsString($runtimeToken);
        $this->assertNotSame($qrToken, $runtimeToken);

        $this->withHeaders([
            'Authorization' => "Bearer {$qrToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/kiosk/v1/bootstrap')->assertUnauthorized();

        $this->withHeaders([
            'Authorization' => "Bearer {$runtimeToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/kiosk/v1/bootstrap')
            ->assertOk()
            ->assertJsonPath('status', true);
    }

    public function test_first_face_scan_checks_in_second_checks_out_and_duplicate_is_idempotent(): void
    {
        FaceProfile::query()->create([
            'user_id' => $this->employee->id,
            'company_id' => $this->device->company_id,
            'branch_id' => $this->device->branch_id,
            'embedding' => array_fill(0, 192, 1 / sqrt(192)),
            'embedding_dimension' => 192,
            'model_version' => 'mobile_facenet_112_192_v1',
            'quality_score' => 0.93,
            'is_active' => true,
            'enrolled_by_device_id' => $this->device->id,
            'enrolled_at' => now(),
        ]);

        $this->mock(AttendanceApiController::class, function (MockInterface $mock) {
            $mock->shouldReceive('employeeAttendance')
                ->twice()
                ->andReturnUsing(function (Request $request) {
                    $attendance = Attendance::query()
                        ->where('user_id', $this->employee->id)
                        ->whereDate('attendance_date', today())
                        ->first();

                    if (!$attendance) {
                        $attendance = Attendance::query()->create([
                            'user_id' => $this->employee->id,
                            'company_id' => $this->device->company_id,
                            'attendance_date' => today(),
                            'check_in_at' => now()->format('H:i:s'),
                            'check_in_type' => $request->input('attendance_type'),
                            'created_by' => $this->employee->id,
                        ]);
                        $message = 'Checked in successfully.';
                    } else {
                        $attendance->update([
                            'check_out_at' => now()->format('H:i:s'),
                            'check_out_type' => $request->input('attendance_type'),
                        ]);
                        $message = 'Checked out successfully.';
                    }

                    return response()->json([
                        'status' => true,
                        'message' => $message,
                        'data' => ['attendance_id' => $attendance->id],
                    ]);
                });
        });

        $firstUuid = (string) Str::uuid();
        $secondUuid = (string) Str::uuid();

        $firstResponse = $this->withHeaders($this->headers())
            ->postJson('/api/kiosk/v1/attendance', $this->event($firstUuid))
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.action', 'check_in')
            ->assertJsonPath('data.employee_id', $this->employee->id);

        $this->withHeaders($this->headers())
            ->postJson('/api/kiosk/v1/attendance', $this->event($secondUuid))
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.action', 'check_out');

        $duplicateResponse = $this->withHeaders($this->headers())
            ->postJson('/api/kiosk/v1/attendance', $this->event($firstUuid))
            ->assertOk()
            ->assertJsonPath('data.action', 'check_in');

        $this->assertSame($firstResponse->json(), $duplicateResponse->json());
        $this->assertDatabaseCount('kiosk_attendance_events', 2);
        $this->assertDatabaseCount('attendances', 1);
        $this->assertNotNull(Attendance::query()->first()->check_out_at);
    }

    private function headers(array $extra = []): array
    {
        return [
            'Authorization' => "Bearer {$this->deviceToken}",
            'Accept' => 'application/json',
            ...$extra,
        ];
    }

    private function event(string $uuid): array
    {
        return [
            'event_uuid' => $uuid,
            'employee_id' => $this->employee->id,
            'captured_at' => now()->toIso8601String(),
            'match_score' => 0.91,
            'latitude' => 11.5564,
            'longitude' => 104.9282,
        ];
    }

    private function seedKiosk(): void
    {
        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Digital HRS',
            'is_active' => true,
        ]);
        $branchId = DB::table('branches')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Head Office',
            'is_active' => true,
            'branch_location_latitude' => 11.5564,
            'branch_location_longitude' => 104.9282,
        ]);

        $this->employee = User::query()->create([
            'name' => 'Face Test Employee',
            'email' => 'face-test@example.com',
            'password' => Hash::make('password'),
            'employee_code' => 'FACE-001',
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'is_active' => true,
            'status' => 'verified',
        ]);

        $this->deviceToken = 'kiosk_' . Str::random(72);
        $this->device = KioskDevice::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => 'Test Kiosk',
            'token_prefix' => substr($this->deviceToken, 0, 12),
            'token_hash' => hash('sha256', $this->deviceToken),
            'admin_pin_hash' => Hash::make('123456'),
            'is_active' => true,
            'provisioned_at' => now(),
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->double('branch_location_latitude')->nullable();
            $table->double('branch_location_longitude')->nullable();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('employee_code')->nullable();
            $table->foreignId('company_id');
            $table->foreignId('branch_id');
            $table->foreignId('department_id')->nullable();
            $table->foreignId('post_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('verified');
            $table->string('avatar')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('dept_name');
        });
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('post_name');
        });
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('company_id');
            $table->date('attendance_date');
            $table->time('check_in_at')->nullable();
            $table->time('check_out_at')->nullable();
            $table->dateTime('night_checkin')->nullable();
            $table->dateTime('night_checkout')->nullable();
            $table->string('check_in_type')->nullable();
            $table->string('check_out_type')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('kiosk_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->foreignId('branch_id');
            $table->string('name');
            $table->string('token_prefix', 12);
            $table->string('token_hash', 64)->unique();
            $table->string('admin_pin_hash');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('face_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique();
            $table->foreignId('company_id');
            $table->foreignId('branch_id');
            $table->longText('embedding');
            $table->unsignedSmallInteger('embedding_dimension');
            $table->string('model_version');
            $table->decimal('quality_score', 5, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('enrolled_by_device_id')->nullable();
            $table->timestamp('enrolled_at');
            $table->timestamps();
        });
        Schema::create('kiosk_attendance_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->foreignId('kiosk_device_id');
            $table->foreignId('user_id');
            $table->foreignId('company_id');
            $table->foreignId('branch_id');
            $table->foreignId('attendance_id')->nullable();
            $table->timestamp('captured_at');
            $table->decimal('match_score', 6, 5);
            $table->string('action')->nullable();
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamps();
        });
    }
}
