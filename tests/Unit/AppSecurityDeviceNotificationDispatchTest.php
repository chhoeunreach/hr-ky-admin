<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\AppSecurity\Controllers\AppSecurityAdminController;
use App\Modules\AppSecurity\Jobs\SendAppSecurityUserNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityDeviceNotificationDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.foreign_key_constraints' => false]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_security_admin_roles', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('admin_id')->unique(); $table->string('security_role');
        });
        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id(); $table->uuid('uuid')->unique(); $table->unsignedBigInteger('user_id'); $table->string('user_type');
            $table->uuid('installation_uuid')->nullable(); $table->text('public_key')->nullable(); $table->string('public_key_fingerprint')->nullable();
            $table->string('package_name')->nullable(); $table->string('signing_sha256')->nullable(); $table->string('version_name')->nullable();
            $table->unsignedBigInteger('version_code')->default(1); $table->unsignedSmallInteger('android_sdk')->nullable();
            $table->string('android_version')->nullable(); $table->string('device_model')->nullable(); $table->string('attestation_level')->nullable();
            $table->string('device_status'); $table->string('pre_block_status')->nullable(); $table->unsignedTinyInteger('risk_score')->default(0);
            $table->unsignedBigInteger('approved_by')->nullable(); $table->timestamp('approved_at')->nullable();
            $table->timestamp('last_seen_at')->nullable(); $table->timestamp('registered_at')->nullable();
            $table->timestamp('blocked_at')->nullable(); $table->timestamp('revoked_at')->nullable(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_sessions', function (Blueprint $table): void {
            $table->id(); $table->uuid('uuid')->nullable(); $table->unsignedBigInteger('device_id'); $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_token_sha256')->nullable(); $table->string('refresh_token_sha256')->nullable();
            $table->timestamp('expires_at')->nullable(); $table->timestamp('refresh_expires_at')->nullable(); $table->timestamp('last_used_at')->nullable(); $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_audit_logs', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('admin_id')->nullable(); $table->string('actor_type'); $table->string('actor_id')->nullable();
            $table->string('action'); $table->string('target_type'); $table->string('target_id'); $table->json('previous_state')->nullable();
            $table->json('new_state')->nullable(); $table->string('reason'); $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable(); $table->timestamp('occurred_at'); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_alerts', function (Blueprint $table): void {
            $table->id(); $table->uuid('uuid')->unique(); $table->string('dedupe_key')->unique();
            $table->unsignedBigInteger('device_id')->nullable(); $table->unsignedBigInteger('user_id')->nullable();
            $table->string('alert_type'); $table->string('severity'); $table->string('title'); $table->text('message');
            $table->text('context')->nullable(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_block_rules', function (Blueprint $table): void {
            $table->id(); $table->string('target_type'); $table->string('target_value'); $table->string('reason');
            $table->boolean('is_active')->default(true); $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('expires_at')->nullable(); $table->timestamps();
            $table->unique(['target_type', 'target_value']);
        });
        DB::table('app_security_admin_roles')->insert(['admin_id' => 90, 'security_role' => 'it_admin']);
        DB::table('app_registered_devices')->insert([
            'uuid' => 'device-uuid', 'user_id' => 12, 'user_type' => 'customer', 'device_status' => 'approved',
            'public_key_fingerprint' => 'ABCD1234',
            'registered_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('app_security_sessions')->insert([
            'uuid' => 'session-uuid', 'device_id' => 1, 'user_id' => 12,
            'session_token_sha256' => str_repeat('a', 64), 'refresh_token_sha256' => str_repeat('b', 64),
            'expires_at' => now()->addMinutes(5), 'refresh_expires_at' => now()->addDays(30),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin = new User(); $admin->id = 90; $admin->password = \Illuminate\Support\Facades\Hash::make('Correct-Horse-77!');
        Auth::guard('admin')->setUser($admin);
    }

    public function test_wrong_step_up_password_leaves_device_sessions_and_audit_unchanged(): void
    {
        Queue::fake();
        $request = Request::create('/admin/app-security/devices/device-uuid/revoke', 'POST', [
            'reason' => 'Confirmed account security issue', 'step_up_password' => 'incorrect-password',
        ], [], [], ['REMOTE_ADDR' => '203.0.113.7']);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn (?string $guard = null) => Auth::guard('admin')->user());

        try {
            app(AppSecurityAdminController::class)->revoke($request, 'device-uuid');
            $this->fail('A wrong password must not perform the device action.');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertDatabaseHas('app_registered_devices', ['uuid' => 'device-uuid', 'device_status' => 'approved']);
            $this->assertDatabaseHas('app_security_sessions', ['device_id' => 1, 'revoked_at' => null]);
            $this->assertSame(0, DB::table('app_security_audit_logs')->count());
            $this->assertSame(0, DB::table('app_security_alerts')->count());
            Queue::assertNothingPushed();
        }
    }

    public function test_device_revocation_updates_state_audits_revokes_sessions_and_queues_user_notification(): void
    {
        Queue::fake();
        $request = Request::create('/admin/app-security/devices/device-uuid/revoke', 'POST', ['reason' => 'Confirmed account security issue', 'step_up_password' => 'Correct-Horse-77!'], [], [], ['REMOTE_ADDR' => '203.0.113.7']);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn (?string $guard = null) => Auth::guard('admin')->user());

        $response = app(AppSecurityAdminController::class)->revoke($request, 'device-uuid');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('app_registered_devices', ['uuid' => 'device-uuid', 'device_status' => 'revoked']);
        $this->assertDatabaseHas('app_security_sessions', ['device_id' => 1, 'revocation_reason' => 'device_revoked']);
        $this->assertDatabaseHas('app_security_audit_logs', ['action' => 'device.revoke', 'target_id' => '1']);
        $this->assertDatabaseHas('app_security_alerts', ['alert_type' => 'device_revoked', 'device_id' => 1]);
        Queue::assertPushed(SendAppSecurityUserNotification::class, fn (SendAppSecurityUserNotification $job): bool => $job->userId === 12);
    }

    public function test_admin_can_revoke_one_live_session_with_step_up_and_audit(): void
    {
        $request = Request::create('/admin/app-security/devices/device-uuid/sessions/session-uuid/revoke', 'POST', [
            'reason' => 'User reported an unrecognized device session', 'step_up_password' => 'Correct-Horse-77!',
        ], [], [], ['REMOTE_ADDR' => '203.0.113.7']);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn (?string $guard = null) => Auth::guard('admin')->user());

        $response = app(AppSecurityAdminController::class)->revokeDeviceSession($request, 'device-uuid', 'session-uuid', app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('app_security_sessions', [
            'uuid' => 'session-uuid', 'revocation_reason' => 'admin: User reported an unrecognized device session',
        ]);
        $session = DB::table('app_security_sessions')->where('uuid', 'session-uuid')->first();
        $this->assertNotNull($session->revoked_at);
        $this->assertNotSame(str_repeat('a', 64), $session->session_token_sha256);
        $this->assertNotSame(str_repeat('b', 64), $session->refresh_token_sha256);
        $this->assertDatabaseHas('app_security_audit_logs', [
            'action' => 'security.session.revoked', 'target_id' => 'session-uuid',
            'reason' => 'User reported an unrecognized device session',
        ]);
    }

    public function test_admin_can_retire_device_key_and_force_new_registration(): void
    {
        Queue::fake();
        $request = Request::create('/admin/app-security/devices/device-uuid/requireReregistration', 'POST', [
            'reason' => 'Rotate the device key after security review', 'step_up_password' => 'Correct-Horse-77!',
        ], [], [], ['REMOTE_ADDR' => '203.0.113.7']);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn (?string $guard = null) => Auth::guard('admin')->user());

        $response = app(AppSecurityAdminController::class)->requireReregistration($request, 'device-uuid');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('app_registered_devices', ['uuid' => 'device-uuid', 'device_status' => 're_registration_required']);
        $this->assertDatabaseHas('app_security_block_rules', ['target_type' => 'key_fingerprint', 'target_value' => 'abcd1234', 'is_active' => 1]);
        $this->assertDatabaseHas('app_security_sessions', ['device_id' => 1, 'revocation_reason' => 'device_reregistration_required']);
        $this->assertDatabaseHas('app_security_audit_logs', ['action' => 'device.require-reregistration', 'target_id' => '1']);
        $this->assertDatabaseHas('app_security_alerts', ['alert_type' => 'device_reregistration_required', 'device_id' => 1]);
        Queue::assertPushed(SendAppSecurityUserNotification::class, fn (SendAppSecurityUserNotification $job): bool => $job->userId === 12);
    }

    public function test_json_admin_api_exposes_step_up_protected_reregistration_action(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/admin/app-security/devices/device-uuid/requireReregistration', [
            'reason' => 'Rotate key through the administrator API',
            'step_up_password' => 'Correct-Horse-77!',
        ]);

        $response->assertOk()->assertJsonPath('action', 'require-reregistration');
        $this->assertDatabaseHas('app_registered_devices', [
            'uuid' => 'device-uuid', 'device_status' => 're_registration_required',
        ]);
        $this->assertDatabaseHas('app_security_block_rules', [
            'target_type' => 'key_fingerprint', 'target_value' => 'abcd1234',
        ]);
        $this->assertDatabaseHas('app_security_audit_logs', [
            'action' => 'device.require-reregistration', 'target_id' => '1',
        ]);
    }
}
