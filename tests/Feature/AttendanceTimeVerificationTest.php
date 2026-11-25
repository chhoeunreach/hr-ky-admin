<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Middleware\MobileFunctionPermission;
use App\Http\Middleware\SPAuthGateMW;
use App\Http\Middleware\VerifyAppSecurityAccess;
use App\Jobs\SendAttendanceTelegramNotification;
use App\Models\KioskDevice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceTimeVerificationTest extends TestCase
{
    private const ENDPOINT = '/api/employees/attendance';

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'attendance_verification',
            'database.connections.attendance_verification' => ['driver' => 'sqlite', 'database' => ':memory:'],
        ]);
        $this->withoutMiddleware([VerifyAppSecurityAccess::class, SPAuthGateMW::class, MobileFunctionPermission::class]);
        Bus::fake([SendAttendanceTelegramNotification::class]);
        $this->createSchema();

        DB::table('branches')->insert([
            'id' => 1,
            'name' => 'Head Office',
            'branch_location_latitude' => 11.55,
            'branch_location_longitude' => 104.92,
        ]);
        DB::table('office_times')->insert([
            'id' => 1,
            'branch_id' => 1,
            'opening_time' => '09:00:00',
            'closing_time' => '17:00:00',
        ]);
        $this->employee = $this->createEmployee();
        $this->setting('attendance_limit', 1);
        $this->setting('attendance_method', 1, ['default', 'qr', 'nfc']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::purge('attendance_verification');
        parent::tearDown();
    }

    public function test_public_time_api_returns_millisecond_utc_time_and_local_timezone(): void
    {
        $this->freeze('2026-11-20 09:15:04.123456');

        $this->getJson('/api/time')->assertOk()->assertExactJson([
            'status' => true,
            'message' => 'ok',
            'status_code' => 200,
            'data' => [
                'server_time_utc' => '2026-11-20T02:15:04.123Z',
                'timezone' => 'Asia/Phnom_Penh',
                'timezone_offset' => '+07:00',
            ],
        ]);
    }

    public function test_past_and_future_client_times_do_not_change_official_check_in(): void
    {
        $this->freeze('2026-11-20 09:15:04.123');
        $secondEmployee = $this->createEmployee();

        foreach ([
            [$this->employee, '2020-01-01T22:00:00-05:00'],
            [$secondEmployee, '2035-01-01T00:00:00+10:00'],
        ] as [$employee, $claimedTime]) {
            $this->actingAs($employee, 'api')->postJson(self::ENDPOINT, $this->wifi(['recorded_at' => $claimedTime]))
                ->assertOk()
                ->assertJsonPath('data.server_time_utc', '2026-11-20T02:15:04.123Z')
                ->assertJsonPath('data.attendance_status', 'checked_in')
                ->assertJsonPath('data.idempotent_replay', false);

            $this->assertDatabaseHas('attendances', [
                'user_id' => $employee->id,
                'attendance_date' => '2026-11-20',
                'check_in_at' => '09:15:04',
            ]);
        }
    }

    public function test_day_selection_and_calculations_use_the_receipt_even_if_processing_crosses_midnight(): void
    {
        $this->actingAs($this->employee, 'api');
        $this->freeze('2026-11-20 18:00:00');
        $this->postJson(self::ENDPOINT, $this->wifi())->assertOk();

        $this->freeze('2026-11-20 23:59:59.250');
        $advanceClock = true;
        DB::listen(function ($query) use (&$advanceClock) {
            if ($advanceClock && str_contains($query->sql, 'from "users"')) {
                $advanceClock = false;
                $this->freeze('2026-11-21 00:00:01');
            }
        });

        $this->postJson(self::ENDPOINT, $this->wifi([
            'attendance_status_type' => 'checkOut',
            'recorded_at' => '2026-11-21T23:59:00+07:00',
        ]))->assertOk()
            ->assertJsonPath('data.server_time_utc', '2026-11-20T16:59:59.250Z')
            ->assertJsonPath('data.attendance_status', 'checked_out');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->employee->id,
            'attendance_date' => '2026-11-20',
            'check_in_at' => '18:00:00',
            'check_out_at' => '23:59:59',
            'worked_hour' => 359,
            'undertime' => 121,
        ]);
    }

    public function test_same_key_replays_in_multi_punch_mode_without_another_write_or_notification(): void
    {
        $this->setting('attendance_limit', 2);
        $this->actingAs($this->employee, 'api');
        $this->freeze('2026-11-20 09:00:00');
        $payload = $this->wifi(['offline_request_id' => 'punch-001']);
        $first = $this->postJson(self::ENDPOINT, $payload, ['Idempotency-Key' => 'ignored-header'])
            ->assertOk()->assertJsonPath('data.idempotent_replay', false);

        $this->postJson(self::ENDPOINT, $payload)
            ->assertOk()
            ->assertJsonPath('data.idempotent_replay', true)
            ->assertJsonPath('data.attendance_id', $first->json('data.attendance_id'));

        $this->postJson(self::ENDPOINT, [], ['Idempotency-Key' => 'punch-001'])
            ->assertOk()->assertJsonPath('data.idempotent_replay', true);

        $this->postJson(self::ENDPOINT, $this->wifi(['offline_request_id' => 'punch-002']))
            ->assertConflict()->assertJsonPath('status_code', 409);
        $this->assertDatabaseHas('attendances', ['check_in_offline_request_id' => 'punch-001', 'check_out_at' => null]);
        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseCount('attendance_logs', 1);
        Bus::assertDispatchedTimes(SendAttendanceTelegramNotification::class, 1);
    }

    public function test_key_collision_with_another_employee_returns_conflict_and_rolls_back(): void
    {
        $this->freeze('2026-11-20 09:00:00');
        $payload = $this->wifi(['offline_request_id' => 'shared-key']);
        $this->actingAs($this->employee, 'api')->postJson(self::ENDPOINT, $payload)->assertOk();

        $this->actingAs($this->createEmployee(), 'api')->postJson(self::ENDPOINT, $payload)
            ->assertConflict()->assertJsonMissingPath('data.attendance_id');

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseCount('employee_locations', 1);
    }

    public function test_invalid_registered_evidence_is_rejected_with_existing_messages(): void
    {
        $this->actingAs($this->employee, 'api');

        foreach (['qr', 'nfc'] as $method) {
            $this->postJson(self::ENDPOINT, ['attendance_type' => $method, 'identifier' => 'unknown-code'])
                ->assertStatus(400)
                ->assertJsonPath('message', __("index.invalid_$method"))
                ->assertJsonPath('status_code', 400);
        }

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_mobile_method_allowlist_rejects_disabled_methods_and_unauthenticated_face_evidence(): void
    {
        $this->actingAs($this->employee, 'api');
        $this->setting('attendance_method', 1, ['default', 'face']);

        $this->postJson(self::ENDPOINT, ['attendance_type' => 'qr', 'identifier' => 'unknown-code'])
            ->assertForbidden()->assertJsonPath('status_code', 403);
        $this->postJson(self::ENDPOINT, ['attendance_type' => 'face', 'kioskDevice' => ['id' => 1]])
            ->assertForbidden()->assertJsonPath('status_code', 403);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_authenticated_kiosk_delegation_can_use_face_when_mobile_face_is_disabled(): void
    {
        $this->actingAs($this->employee, 'api');
        $this->freeze('2026-11-20 09:15:04');
        $this->app['request']->attributes->set('kioskDevice', new KioskDevice());

        $response = app(AttendanceApiController::class)->employeeAttendance(
            Request::create(self::ENDPOINT, 'POST', ['attendance_type' => 'face'])
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('attendances', ['check_in_type' => 'face', 'check_in_at' => '09:15:04']);
    }

    public function test_required_location_checks_coordinates_accuracy_and_branch_radius(): void
    {
        $this->actingAs($this->employee, 'api');
        DB::table('app_settings')->insert(['slug' => 'enable-employee-location', 'status' => 1]);

        $this->postJson(self::ENDPOINT, $this->wifi())->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude', 'longitude']);
        $this->postJson(self::ENDPOINT, $this->wifi(['latitude' => 0, 'longitude' => 0]))->assertForbidden();

        $location = ['latitude' => 11.55, 'longitude' => 104.92];
        $this->postJson(self::ENDPOINT, $this->wifi($location + ['accuracy' => 1001]))->assertForbidden();

        $this->setting('attendance_location_accuracy', 30);
        $this->postJson(self::ENDPOINT, $this->wifi($location + ['accuracy' => 31]))->assertForbidden();
        $this->postJson(self::ENDPOINT, $this->wifi($location + ['accuracy' => 30]))
            ->assertOk()->assertJsonPath('data.within_branch_radius', true);
    }

    public function test_qr_policy_requires_in_radius_gps_and_hashes_the_identifier_in_the_log(): void
    {
        $this->actingAs($this->employee, 'api');
        $this->freeze('2026-11-20 09:15:04');
        $identifier = 'registered-site-qr';
        DB::table('qr_attendances')->insert(['identifier' => $identifier, 'branch_id' => 1]);
        $this->setting('require_nfc_or_gps_before_qr', 1);
        $this->setting('attendance_location_radius', 100, null, false);
        $payload = ['attendance_type' => 'qr', 'identifier' => $identifier];

        $this->postJson(self::ENDPOINT, $payload)->assertForbidden();
        $this->postJson(self::ENDPOINT, $payload + ['latitude' => 0, 'longitude' => 0])->assertForbidden();

        $this->postJson(self::ENDPOINT, $payload + ['latitude' => 11.55, 'longitude' => 104.92])
            ->assertOk()->assertJsonPath('data.within_branch_radius', true);
        $this->assertDatabaseHas('attendance_logs', [
            'identifier' => hash('sha256', $identifier),
            'action' => 'check_in',
            'source' => 'app',
            'created_at' => '2026-11-20 09:15:04',
        ]);
    }

    public function test_successful_nfc_log_contains_only_the_hashed_identifier(): void
    {
        $this->actingAs($this->employee, 'api');
        DB::table('nfc_attendances')->insert(['identifier' => 'registered-nfc', 'created_by' => $this->employee->id]);

        $this->postJson(self::ENDPOINT, ['attendance_type' => 'nfc', 'identifier' => 'registered-nfc'])
            ->assertOk();
        $this->assertDatabaseHas('attendance_logs', ['identifier' => hash('sha256', 'registered-nfc')]);
        $this->assertDatabaseMissing('attendance_logs', ['identifier' => 'registered-nfc']);
    }

    public function test_signed_qr_pass_is_bound_to_the_employee_and_receipt_time_window(): void
    {
        config(['app.key' => str_repeat('k', 32)]);
        $this->actingAs($this->employee, 'api');
        $this->freeze('2026-11-20 09:15:04');

        $employeeId = $this->employee->id;
        foreach ([
            [$employeeId, -301, 403],
            [$employeeId, 1, 403],
            [$employeeId + 1, -1, 403],
            [$employeeId, -300, 200],
        ] as [$userId, $offset, $expectedStatus]) {
            $timestamp = now()->timestamp + $offset;
            $this->postJson(self::ENDPOINT, [
                'attendance_type' => 'qr_pass',
                'identifier' => json_encode([
                    'type' => 'attendance_queue_pass',
                    'user_id' => $userId,
                    'timestamp' => $timestamp,
                    'location_token' => 'branch',
                    'signature' => hash_hmac('sha256', "$userId|$timestamp|branch", config('app.key')),
                ]),
            ])->assertStatus($expectedStatus);
        }

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_night_checkout_after_midnight_stays_on_the_check_in_date(): void
    {
        DB::table('office_times')->where('id', 1)->update([
            'shift_type' => 'night',
            'opening_time' => '20:00:00',
            'closing_time' => '05:00:00',
        ]);
        $this->actingAs($this->employee, 'api');

        $this->freeze('2026-11-20 20:00:00');
        $first = $this->postJson(self::ENDPOINT, $this->wifi(['recorded_at' => '2027-03-25T06:00:00Z']))
            ->assertOk()->assertJsonPath('data.attendance_status', 'checked_in_night')
            ->assertJsonPath('data.server_time_utc', '2026-11-20T13:00:00.000Z');

        $this->freeze('2026-11-21 05:00:00');
        $this->postJson(self::ENDPOINT, $this->wifi([
            'recorded_at' => '2024-04-01T06:00:00Z',
            'attendance_status_type' => 'checkOut',
            'offline_request_id' => 'night-out',
        ]))->assertOk()->assertJsonPath('data.attendance_status', 'checked_out_night')
            ->assertJsonPath('data.server_time_utc', '2026-11-20T22:00:00.000Z')
            ->assertJsonPath('data.attendance_id', $first->json('data.attendance_id'));

        $this->postJson(self::ENDPOINT, [], ['Idempotency-Key' => 'night-out'])
            ->assertOk()->assertJsonPath('data.idempotent_replay', true);
        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', [
            'attendance_date' => '2026-11-20',
            'night_checkin' => '2026-11-20 20:00:00',
            'night_checkout' => '2026-11-21 05:00:00',
            'worked_hour' => 540,
        ]);
    }

    public function test_branch_route_allows_attendance_at_target_branch_and_adapts_dashboard(): void
    {
        $this->actingAs($this->employee, 'api');
        $this->freeze('2026-11-20 09:15:04');

        DB::table('branches')->insert([
            'id' => 2,
            'name' => 'Branch B',
            'branch_location_latitude' => 11.60,
            'branch_location_longitude' => 104.95,
        ]);

        $this->setting('attendance_location_radius', 100, null, true);
        DB::table('app_settings')->insert(['slug' => 'enable-employee-location', 'status' => 1]);

        $branchBLocation = ['latitude' => 11.60, 'longitude' => 104.95];
        $this->postJson(self::ENDPOINT, $this->wifi($branchBLocation))->assertForbidden();

        DB::table('daily_entries')->insert([
            'employee_id' => $this->employee->id,
            'kind' => 'activity',
            'title' => 'Route to Branch B',
            'details' => json_encode([
                'fields' => [
                    'mission_type' => 'branch_route',
                    'route_id' => '11111111-2222-3333-4444-555555555555',
                    'origin_branch' => 'Head Office',
                    'target_branches' => ['Branch B'],
                    'departure_time' => '08:30',
                    'arrival_time' => '10:30',
                    'status' => 'in_progress',
                ],
            ]),
            'occurred_at' => '2026-11-20 08:30:00',
            'created_at' => '2026-11-20 08:30:00',
        ]);

        $response = $this->postJson(self::ENDPOINT, $this->wifi($branchBLocation))->assertOk();
        $response->assertJsonPath('data.within_branch_radius', true);
        $response->assertJsonPath('data.is_branch_route_attendance', true);
        $response->assertJsonPath('data.matched_branch_id', 2);
        $response->assertJsonPath('data.matched_branch_name', 'Branch B');

        $this->postJson(self::ENDPOINT, $this->wifi(['latitude' => 0, 'longitude' => 0]))->assertForbidden();

        $this->freeze('2026-11-20 17:05:00');
        $homeLocation = ['latitude' => 11.55, 'longitude' => 104.92, 'attendance_status_type' => 'checkOut'];
        $homeResponse = $this->postJson(self::ENDPOINT, $this->wifi($homeLocation))->assertOk();
        $homeResponse->assertJsonPath('data.within_branch_radius', true);
        $homeResponse->assertJsonPath('data.is_branch_route_attendance', false);
        $homeResponse->assertJsonPath('data.matched_branch_id', 1);

        $activeRoute = app(\App\Services\Attendance\AttendanceService::class)->getActiveBranchRouteForEmployee($this->employee->id);
        $this->assertNotNull($activeRoute);
        $this->assertSame('11111111-2222-3333-4444-555555555555', $activeRoute['route_id']);
        $this->assertSame('Branch B', $activeRoute['target_branches'][0]['name']);
        $this->assertEquals(11.60, $activeRoute['target_branches'][0]['latitude']);
    }

    public function test_branch_route_check_out_at_target_branch_completes_mission_and_blocks_further_target_scans(): void
    {
        $this->actingAs($this->employee, 'api');
        $this->freeze('2026-11-20 09:00:00');

        DB::table('branches')->insert([
            'id' => 3,
            'name' => 'Branch C',
            'branch_location_latitude' => 11.70,
            'branch_location_longitude' => 104.98,
        ]);

        $this->setting('attendance_location_radius', 100, null, true);
        DB::table('app_settings')->insert(['slug' => 'enable-employee-location', 'status' => 1]);

        $branchCLocation = ['latitude' => 11.70, 'longitude' => 104.98];

        // 1. Without route, scan at Branch C is blocked
        $this->postJson(self::ENDPOINT, $this->wifi($branchCLocation))->assertForbidden();

        // 2. Insert active branch route for today
        $entryId = DB::table('daily_entries')->insertGetId([
            'employee_id' => $this->employee->id,
            'kind' => 'activity',
            'title' => 'Mission to Branch C',
            'details' => json_encode([
                'fields' => [
                    'mission_type' => 'branch_route',
                    'route_id' => '22222222-3333-4444-5555-666666666666',
                    'origin_branch' => 'Head Office',
                    'target_branches' => ['Branch C'],
                    'departure_time' => '08:30',
                    'arrival_time' => '17:00',
                    'status' => 'in_progress',
                ],
            ]),
            'occurred_at' => '2026-11-20 08:30:00',
            'created_at' => '2026-11-20 08:30:00',
        ]);

        // 3. API endpoint returns active branch route
        $activeRouteRes = $this->getJson('/api/employees/active-branch-route')->assertOk();
        $activeRouteRes->assertJsonPath('data.has_active_route', true);
        $activeRouteRes->assertJsonPath('data.active_route.route_id', '22222222-3333-4444-5555-666666666666');

        // 4. Check-in at Branch C succeeds
        $inResponse = $this->postJson(self::ENDPOINT, $this->wifi($branchCLocation))->assertOk();
        $inResponse->assertJsonPath('data.is_branch_route_attendance', true);
        $inResponse->assertJsonPath('data.matched_branch_name', 'Branch C');

        // 5. Check-out at Branch C at 17:00 with complete_branch_route = true completes the mission
        $this->freeze('2026-11-20 17:00:00');
        $outPayload = array_merge($branchCLocation, [
            'attendance_status_type' => 'checkOut',
            'complete_branch_route' => true,
        ]);
        $outResponse = $this->postJson(self::ENDPOINT, $this->wifi($outPayload))->assertOk();
        $outResponse->assertJsonPath('data.is_branch_route_attendance', true);
        $outResponse->assertJsonPath('data.branch_route_completed', true);
        $outResponse->assertJsonPath('data.completed_branch_route.status', 'completed');

        // 6. DB entry is now completed
        $updatedEntry = DB::table('daily_entries')->where('id', $entryId)->first();
        $entryDetails = json_decode($updatedEntry->details, true);
        $this->assertSame('completed', $entryDetails['fields']['status']);
        $this->assertSame('17:00', $entryDetails['fields']['actual_arrival_time']);
        $this->assertTrue($entryDetails['fields']['completed_via_attendance']);

        // 7. Active route endpoint now returns false (mission is closed)
        $closedRouteRes = $this->getJson('/api/employees/active-branch-route')->assertOk();
        $closedRouteRes->assertJsonPath('data.has_active_route', false);

        // 8. Attempting to scan again at Branch C is now FORBIDDEN (permissions reverted to Home Branch!)
        $this->freeze('2026-11-20 17:05:00');
        $forbiddenRes = $this->postJson(self::ENDPOINT, $this->wifi($branchCLocation))->assertForbidden();
        $forbiddenRes->assertJsonPath('error_code', 'BRANCH_ROUTE_COMPLETED');
        $forbiddenRes->assertJsonPath('data.error_type', 'branch_route_completed');
        $forbiddenRes->assertJsonPath('data.attempted_branch', 'Branch C');

        // 9. Home branch scanning still works (e.g. next morning check-in)
        $this->freeze('2026-11-21 08:30:00');
        $homeLocation = ['latitude' => 11.55, 'longitude' => 104.92];
        $homeResponse = $this->postJson(self::ENDPOINT, $this->wifi($homeLocation))->assertOk();
        $homeResponse->assertJsonPath('data.is_branch_route_attendance', false);
        $homeResponse->assertJsonPath('data.matched_branch_id', 1);
    }

    public function test_geofence_and_accuracy_violations_return_structured_error_payload_for_mobile_alert(): void
    {
        $this->actingAs($this->employee, 'api');
        DB::table('app_settings')->insert(['slug' => 'enable-employee-location', 'status' => 1]);
        $this->setting('attendance_location_accuracy', 50);
        $this->setting('attendance_location_radius', 100, null, true);

        // 1. Weak GPS Accuracy returns INSUFFICIENT_GPS_ACCURACY
        $weakGps = $this->postJson(self::ENDPOINT, $this->wifi([
            'latitude' => 11.55,
            'longitude' => 104.92,
            'accuracy' => 85,
        ]))->assertForbidden();
        $weakGps->assertJsonPath('error_code', 'INSUFFICIENT_GPS_ACCURACY');
        $weakGps->assertJsonPath('data.error_type', 'insufficient_gps_accuracy');
        $weakGps->assertJsonPath('data.current_accuracy_meters', 85);

        // 2. Out of geofence returns OUT_OF_GEOFENCE with distance
        $farGps = $this->postJson(self::ENDPOINT, $this->wifi([
            'latitude' => 11.60,
            'longitude' => 104.98,
            'accuracy' => 15,
        ]))->assertForbidden();
        $farGps->assertJsonPath('error_code', 'OUT_OF_GEOFENCE');
        $farGps->assertJsonPath('data.error_type', 'out_of_radius');
        $this->assertGreaterThan(100, $farGps->json('data.distance_in_meter'));

        // 3. Field worker also strictly respects branch radius when radius is enabled
        $fieldEmployee = User::query()->create([
            'name' => 'Field Worker',
            'company_id' => 1,
            'branch_id' => 1,
            'office_time_id' => 1,
            'workspace_type' => User::FIELD,
            'allow_holiday_check_in' => true,
        ]);
        $this->actingAs($fieldEmployee, 'api');
        $fieldFarGps = $this->postJson(self::ENDPOINT, $this->wifi([
            'latitude' => 11.60,
            'longitude' => 104.98,
            'accuracy' => 15,
        ]))->assertForbidden();
        $fieldFarGps->assertJsonPath('error_code', 'OUT_OF_GEOFENCE');
    }

    private function freeze(string $localTime): void
    {
        Carbon::setTestNow(Carbon::parse($localTime, 'Asia/Phnom_Penh'));
    }

    private function wifi(array $overrides = []): array
    {
        return array_replace(['attendance_type' => 'wifi', 'attendance_status_type' => 'checkIn'], $overrides);
    }

    private function createEmployee(): User
    {
        return User::query()->create([
            'name' => 'Attendance Test Employee',
            'company_id' => 1,
            'branch_id' => 1,
            'office_time_id' => 1,
            'workspace_type' => User::OFFICE,
            'allow_holiday_check_in' => true,
        ]);
    }

    private function setting(string $slug, int $value, ?array $methods = null, bool $enabled = true): void
    {
        DB::table('attendance_settings')->updateOrInsert(['slug' => $slug], [
            'value' => $value,
            'values' => $methods === null ? null : json_encode($methods),
            'status' => $enabled,
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            foreach (['company_id', 'branch_id', 'office_time_id', 'department_id', 'updated_by'] as $column) {
                $table->foreignId($column)->nullable();
            }
            $table->boolean('workspace_type')->default(User::OFFICE);
            $table->boolean('online_status')->default(User::OFFLINE);
            $table->boolean('allow_holiday_check_in')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('branch_location_latitude');
            $table->double('branch_location_longitude');
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('dept_name');
        });
        Schema::create('office_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id');
            $table->boolean('is_active')->default(true);
            $table->time('opening_time');
            $table->time('closing_time');
            $table->string('shift_type')->default('day');
            foreach (['early_check_in', 'late_check_in', 'early_check_out', 'late_check_out'] as $rule) {
                $table->boolean("is_$rule")->default(false);
            }
            foreach (['checkin_before', 'checkin_after', 'checkout_before', 'checkout_after'] as $window) {
                $table->integer($window)->default(30);
            }
        });
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->string('slug')->primary();
            $table->string('value')->nullable();
            $table->json('values')->nullable();
            $table->boolean('status');
        });
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->boolean('status');
        });
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('company_id');
            $table->foreignId('office_time_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->date('attendance_date');
            foreach (['in', 'out'] as $direction) {
                $table->time("check_{$direction}_at")->nullable();
                $table->double("check_{$direction}_latitude")->nullable();
                $table->double("check_{$direction}_longitude")->nullable();
                $table->string("check_{$direction}_note")->nullable();
                $table->string("check_{$direction}_type")->nullable();
                $table->string("check_{$direction}_selfie")->nullable();
                $table->string("check_{$direction}_offline_request_id")->nullable()->unique();
            }
            $table->dateTime('night_checkin')->nullable();
            $table->dateTime('night_checkout')->nullable();
            foreach (['worked_hour', 'overtime', 'undertime'] as $column) {
                $table->double($column)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->foreignId('attendance_id');
            $table->foreignId('created_by');
            foreach (['attendance_type', 'identifier', 'action', 'note', 'source'] as $column) {
                $table->string($column)->nullable();
            }
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->timestamps();
        });
        Schema::create('employee_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->timestamps();
        });
        Schema::create('qr_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->foreignId('branch_id');
        });
        Schema::create('nfc_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->foreignId('created_by');
        });
        Schema::create('time_leaves', function (Blueprint $table) {
            $table->id();
            $table->date('issue_date');
            $table->string('status');
            $table->foreignId('requested_by');
            $table->foreignId('branch_id');
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('permission_key');
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active');
        });
        Schema::create('permission_roles', function (Blueprint $table) {
            $table->foreignId('role_id');
            $table->foreignId('permission_id');
        });
        Schema::create('daily_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('kind', 16);
            $table->string('title', 180)->nullable();
            $table->text('details')->nullable();
            $table->dateTime('occurred_at')->nullable();
            $table->dateTime('archived_at')->nullable();
            $table->timestamps();
        });
    }
}
