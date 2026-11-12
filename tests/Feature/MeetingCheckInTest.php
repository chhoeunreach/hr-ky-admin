<?php
namespace Tests\Feature;

use App\Models\TeamMeeting;
use App\Models\User;
use App\Services\TeamMeeting\MeetingCheckInService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MeetingCheckInTest extends TestCase
{
    private MeetingCheckInService $service;
    private User $employee;
    private TeamMeeting $meeting;

    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', ':memory:');
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        DB::purge('sqlite');
        $this->travelTo(Carbon::parse('2026-11-12 10:00:00'));
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->integer('company_id'); $t->string('name'); $t->softDeletes(); });
        Schema::create('team_meetings', function (Blueprint $t) {
            $t->id(); $t->integer('company_id'); $t->date('meeting_date'); $t->dateTime('meeting_published_at');
            $t->string('title')->default('Team briefing'); $t->string('venue')->default('Meeting room');
            $t->time('meeting_start_time')->default('10:00:00');
            $t->timestamps();
        });
        (require database_path('migrations/2026_11_12_000005_add_meeting_checkin_controls.php'))->up();
        Schema::create('team_meeting_members', function (Blueprint $t) { $t->id(); $t->integer('team_meeting_id'); $t->integer('meeting_participator_id'); });
        Schema::create('app_settings', function (Blueprint $t) { $t->id(); $t->string('slug'); $t->boolean('status'); });
        // Use the actual attendance migration, including its uniqueness constraint.
        (require database_path('migrations/2026_08_17_000001_create_meeting_attendances_table.php'))->up();
        DB::table('users')->insert(['id' => 1, 'company_id' => 1, 'name' => 'Employee']);
        DB::table('team_meetings')->insert(['id' => 1, 'company_id' => 1, 'meeting_date' => '2026-11-12', 'meeting_published_at' => '2026-11-11 08:00:00']);
        DB::table('team_meeting_members')->insert(['team_meeting_id' => 1, 'meeting_participator_id' => 1]);
        $this->employee = User::findOrFail(1);
        $this->meeting = TeamMeeting::findOrFail(1);
        $this->service = app(MeetingCheckInService::class);
    }

    public function test_scan_records_once_and_preserves_original_time(): void
    {
        $qr = $this->service->qrPayload($this->meeting);
        $first = $this->service->scan($qr, $this->employee);
        $this->assertFalse($first['already_joined']);
        $this->travelTo(now()->addMinutes(5));
        $again = $this->service->scan($qr, $this->employee);
        $this->assertTrue($again['already_joined']);
        $this->assertSame($first['attendance']->id, $again['attendance']->id);
        $this->assertSame(1, DB::table('meeting_attendances')->count());
        $this->assertSame('10:00:00', $again['attendance']->checked_in_at->format('H:i:s'));
    }

    public function test_guessable_and_tampered_payloads_are_rejected(): void
    {
        foreach (['1', 'meeting:1', '{"meeting_id":1}', $this->service->qrPayload($this->meeting).'tampered'] as $qr) {
            try { $this->service->scan($qr, $this->employee); $this->fail('Accepted invalid QR'); }
            catch (ValidationException $e) { $this->assertArrayHasKey('qr_payload', $e->errors()); }
        }
        $this->assertSame(0, DB::table('meeting_attendances')->count());
    }

    public function test_rotating_qr_invalidates_previous_copies(): void
    {
        $qr = $this->service->qrPayload($this->meeting);
        DB::table('team_meetings')->where('id', 1)->update(['attendance_qr_version' => 2]);
        $this->expectException(ValidationException::class);
        $this->service->scan($qr, $this->employee);
    }

    public function test_non_participant_is_forbidden(): void
    {
        DB::table('team_meeting_members')->delete();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->scan($this->service->qrPayload($this->meeting), $this->employee);
    }

    public function test_other_company_cannot_check_in(): void
    {
        $this->employee->company_id = 2;
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->service->scan($this->service->qrPayload($this->meeting), $this->employee);
    }

    public function test_disabled_upcoming_and_closed_meetings_reject_check_in(): void
    {
        foreach ([['attendance_enabled' => false],
            ['attendance_enabled' => true, 'attendance_opens_at' => '2026-11-12 11:00:00'],
            ['attendance_opens_at' => null, 'attendance_closes_at' => '2026-11-12 09:00:00'],
        ] as $settings) {
            DB::table('team_meetings')->where('id', 1)->update($settings);
            try { $this->service->scan($this->service->qrPayload($this->meeting), $this->employee); $this->fail('Accepted closed check-in'); }
            catch (ValidationException $e) { $this->assertArrayHasKey('qr_payload', $e->errors()); }
        }
        $this->assertSame(0, DB::table('meeting_attendances')->count());
    }

    public function test_default_window_uses_meeting_date_and_custom_window_can_cross_midnight(): void
    {
        $this->assertSame('open', $this->service->status($this->meeting));
        $this->travelTo(Carbon::parse('2026-11-13 00:15:00'));
        $this->assertSame('closed', $this->service->status($this->meeting));
        $this->meeting->attendance_opens_at = '2026-11-12 23:00:00';
        $this->meeting->attendance_closes_at = '2026-11-13 01:00:00';
        $this->assertSame('open', $this->service->status($this->meeting));
    }
    public function test_admin_register_contains_secure_qr_and_export_has_pending_and_joined(): void
    {
        $this->actingAs($this->employee);
        \Illuminate\Support\Facades\Gate::define('show_team_meeting', fn () => true);
        $controller = app(\App\Http\Controllers\Web\MeetingAttendanceController::class);
        $data = $controller->show(1, $this->service)->getData();
        $this->assertStringStartsWith('meeting-qr:', $data['qrPayload']);
        $this->assertSame('open', $data['status']);
        ob_start(); $controller->export(1)->sendContent(); $pending = ob_get_clean();
        $this->assertStringContainsString('Employee,Pending,', $pending);
        $this->service->scan($data['qrPayload'], $this->employee);
        ob_start(); $controller->export(1)->sendContent(); $joined = ob_get_clean();
        $this->assertStringContainsString('Employee,Joined,', $joined);
    }

    public function test_admin_can_disable_check_in_and_rotate_qr(): void
    {
        $this->actingAs($this->employee);
        \Illuminate\Support\Facades\Gate::define('edit_team_meeting', fn () => true);
        $controller = app(\App\Http\Controllers\Web\MeetingAttendanceController::class);
        $controller->update(\Illuminate\Http\Request::create('/', 'PUT', ['attendance_enabled' => '0']), 1);
        $this->assertFalse($this->meeting->fresh()->attendance_enabled);
        $controller->rotate(1);
        $this->assertSame(2, (int) $this->meeting->fresh()->attendance_qr_version);
        $this->assertSame('2026-11-11 08:00:00', $this->meeting->fresh()->meeting_published_at);
    }

    public function test_admin_rejects_inverted_check_in_window(): void
    {
        $this->actingAs($this->employee);
        \Illuminate\Support\Facades\Gate::define('edit_team_meeting', fn () => true);
        $this->expectException(ValidationException::class);
        app(\App\Http\Controllers\Web\MeetingAttendanceController::class)->update(
            \Illuminate\Http\Request::create('/', 'PUT', ['attendance_enabled' => '1',
                'attendance_opens_at' => '2026-11-12T12:00', 'attendance_closes_at' => '2026-11-12T11:00']), 1);
    }

    public function test_admin_register_rejects_other_company_meeting(): void
    {
        $this->employee->company_id = 2;
        $this->actingAs($this->employee);
        \Illuminate\Support\Facades\Gate::define('show_team_meeting', fn () => true);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(\App\Http\Controllers\Web\MeetingAttendanceController::class)->show(1, $this->service);
    }

    public function test_admin_cannot_change_settings_without_edit_permission(): void
    {
        $this->actingAs($this->employee);
        \Illuminate\Support\Facades\Gate::define('edit_team_meeting', fn () => false);
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        app(\App\Http\Controllers\Web\MeetingAttendanceController::class)->rotate(1);
    }

    private function authenticateMobile(array $abilities): void
    {
        // Permission hydration has its own tests; retain the mobile permission guard.
        $this->withoutMiddleware(\App\Http\Middleware\SPAuthGateMW::class);
        \Laravel\Passport\Passport::actingAs($this->employee);
        \Illuminate\Support\Facades\Gate::before(fn ($user, $ability) => in_array($ability, $abilities, true));
    }

    public function test_mobile_scan_and_status_return_attendance_without_duplicate_records(): void
    {
        $this->authenticateMobile(['mobile.menu.meeting-attendance',
            'mobile.function.TeamMeeting.scanMeetingAttendance', 'mobile.function.TeamMeeting.meetingAttendanceStatus']);
        $this->getJson('/api/team-meetings/attendance/status')->assertOk()
            ->assertJsonPath('data.meetings.0.is_joined', false)
            ->assertJsonPath('data.meetings.0.attendance_status', 'open');
        $payload = ['qr_payload' => $this->service->qrPayload($this->meeting)];
        $this->postJson('/api/team-meetings/attendance/scan', $payload)->assertOk()
            ->assertJsonPath('data.already_joined', false)
            ->assertJsonPath('data.attendance.team_meeting_id', 1);
        $this->postJson('/api/team-meetings/attendance/scan', $payload)->assertOk()
            ->assertJsonPath('data.already_joined', true);
        $this->getJson('/api/team-meetings/attendance/status')->assertOk()
            ->assertJsonPath('data.meetings.0.is_joined', true)
            ->assertJsonPath('data.meetings.0.checked_in_at', '2026-11-12 10:00:00');
        $this->assertSame(1, DB::table('meeting_attendances')->count());
    }

    public function test_mobile_scan_requires_authentication_and_function_permission(): void
    {
        $payload = ['qr_payload' => $this->service->qrPayload($this->meeting)];
        $this->postJson('/api/team-meetings/attendance/scan', $payload)->assertUnauthorized();
        $this->authenticateMobile(['mobile.menu.meeting-attendance']);
        $this->postJson('/api/team-meetings/attendance/scan', $payload)->assertForbidden();
        $this->assertSame(0, DB::table('meeting_attendances')->count());
    }

    public function test_mobile_scan_returns_validation_errors_for_invalid_or_missing_qr(): void
    {
        $this->authenticateMobile(['mobile.menu.meeting-attendance', 'mobile.function.TeamMeeting.scanMeetingAttendance']);
        foreach ([[], ['qr_payload' => 'meeting:1'], ['qr_payload' => str_repeat('x', 4097)]] as $payload) {
            $this->postJson('/api/team-meetings/attendance/scan', $payload)->assertUnprocessable();
        }
        $this->assertSame(0, DB::table('meeting_attendances')->count());
    }

    public function test_mobile_scan_rejects_unassigned_employee_and_replaced_qr(): void
    {
        $this->authenticateMobile(['mobile.menu.meeting-attendance', 'mobile.function.TeamMeeting.scanMeetingAttendance']);
        $payload = ['qr_payload' => $this->service->qrPayload($this->meeting)];
        DB::table('team_meetings')->where('id', 1)->update(['attendance_qr_version' => 2]);
        $this->postJson('/api/team-meetings/attendance/scan', $payload)->assertUnprocessable();
        DB::table('team_meeting_members')->delete();
        $this->postJson('/api/team-meetings/attendance/scan', [
            'qr_payload' => $this->service->qrPayload($this->meeting->fresh()),
        ])->assertForbidden();
        $this->assertSame(0, DB::table('meeting_attendances')->count());
    }

    public function test_blank_settings_restore_default_window_without_republishing(): void
    {
        $this->actingAs($this->employee);
        \Illuminate\Support\Facades\Gate::define('edit_team_meeting', fn () => true);
        DB::table('team_meetings')->where('id', 1)->update([
            'attendance_opens_at' => '2026-11-12 09:00:00', 'attendance_closes_at' => '2026-11-12 11:00:00',
        ]);
        app(\App\Http\Controllers\Web\MeetingAttendanceController::class)->update(
            \Illuminate\Http\Request::create('/', 'PUT', ['attendance_enabled' => '1',
                'attendance_opens_at' => null, 'attendance_closes_at' => null]), 1);
        $meeting = $this->meeting->fresh();
        $this->assertNull($meeting->attendance_opens_at);
        $this->assertNull($meeting->attendance_closes_at);
        $this->assertSame('2026-11-11 08:00:00', $meeting->meeting_published_at);
        $this->assertSame('open', $this->service->status($meeting));
    }

    public function test_export_keeps_employee_names_from_becoming_spreadsheet_formulas(): void
    {
        DB::table('users')->where('id', 1)->update(['name' => '=SUM(1+1)']);
        $this->actingAs($this->employee);
        \Illuminate\Support\Facades\Gate::define('show_team_meeting', fn () => true);
        ob_start();
        app(\App\Http\Controllers\Web\MeetingAttendanceController::class)->export(1)->sendContent();
        $csv = ob_get_clean();
        $this->assertStringContainsString("'=SUM(1+1)", $csv);
    }

    public function test_mobile_status_excludes_unassigned_unpublished_and_other_company_meetings(): void
    {
        $this->authenticateMobile(['mobile.menu.meeting-attendance', 'mobile.function.TeamMeeting.meetingAttendanceStatus']);
        foreach ([['company_id' => 2], ['meeting_published_at' => '2026-11-13 10:00:00']] as $changes) {
            DB::table('team_meetings')->where('id', 1)->update(array_merge([
                'company_id' => 1, 'meeting_published_at' => '2026-11-11 08:00:00',
            ], $changes));
            $this->getJson('/api/team-meetings/attendance/status')->assertOk()->assertJsonCount(0, 'data.meetings');
        }
        DB::table('team_meetings')->where('id', 1)->update(['meeting_published_at' => '2026-11-11 08:00:00']);
        DB::table('team_meeting_members')->delete();
        $this->getJson('/api/team-meetings/attendance/status')->assertOk()->assertJsonCount(0, 'data.meetings');
    }

}
