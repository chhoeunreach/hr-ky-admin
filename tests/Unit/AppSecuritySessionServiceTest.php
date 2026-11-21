<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Models\SecuritySession;
use App\Modules\AppSecurity\Services\AppSecurityException;
use App\Modules\AppSecurity\Services\DeviceSessionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecuritySessionServiceTest extends TestCase
{
    private mixed $privateKey;
    private User $user;
    private RegisteredDevice $device;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app_security.session_ttl_seconds' => 300,
            'app_security.refresh_ttl_seconds' => 2592000,
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('deleted_at')->nullable();
        });
        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('device_status');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->longText('public_key');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('user_id');
            $table->char('session_token_sha256', 64)->unique();
            $table->char('refresh_token_sha256', 64)->nullable()->unique();
            $table->timestamp('expires_at');
            $table->timestamp('refresh_expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_refresh_token_history', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('session_id'); $table->char('token_sha256', 64)->unique();
            $table->timestamp('expires_at'); $table->timestamp('reuse_detected_at')->nullable(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_logs', function (Blueprint $table): void {
            $table->id(); $table->string('event_type'); $table->string('severity'); $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('device_id')->nullable(); $table->uuid('request_uuid')->nullable(); $table->string('ip_address')->nullable();
            $table->json('details')->nullable(); $table->timestamp('occurred_at'); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_alerts', function (Blueprint $table): void {
            $table->id(); $table->uuid('uuid')->unique(); $table->string('dedupe_key')->unique();
            $table->unsignedBigInteger('device_id')->nullable(); $table->unsignedBigInteger('user_id')->nullable();
            $table->string('alert_type'); $table->string('severity'); $table->string('title'); $table->text('message');
            $table->json('context')->nullable(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_audit_logs', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('admin_id')->nullable(); $table->string('actor_type')->nullable(); $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action'); $table->string('target_type'); $table->string('target_id'); $table->json('previous_state')->nullable();
            $table->json('new_state')->nullable(); $table->string('reason')->nullable(); $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable(); $table->timestamp('occurred_at'); $table->timestamps();
        });

        $this->privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        self::assertNotFalse($this->privateKey);
        $details = openssl_pkey_get_details($this->privateKey);
        $publicDer = preg_replace(
            '/-----BEGIN PUBLIC KEY-----|-----END PUBLIC KEY-----|\s+/',
            '',
            $details['key']
        );
        DB::table('users')->insert(['id' => 1]);
        $this->user = User::query()->findOrFail(1);
        $this->device = RegisteredDevice::query()->create([
            'uuid' => '23d3f9ad-349d-4dd3-909a-92ff71c10d3b',
            'user_id' => $this->user->id,
            'device_status' => 'approved',
            'public_key' => $publicDer,
        ]);
    }

    public function test_session_tokens_are_hashed_and_refresh_rotates_tokens(): void
    {
        $sessions = app(DeviceSessionService::class);
        $opened = $sessions->open($this->user, $this->device->uuid);
        $stored = SecuritySession::query()->where('uuid', $opened['session_id'])->firstOrFail();

        $this->assertSame(hash('sha256', $opened['session_token']), $stored->session_token_sha256);
        $this->assertSame(hash('sha256', $opened['refresh_token']), $stored->refresh_token_sha256);
        $this->assertNotSame($opened['session_token'], $stored->session_token_sha256);
        $this->assertNotSame($opened['refresh_token'], $stored->refresh_token_sha256);

        $rotated = $sessions->refresh(
            $this->user,
            $opened['refresh_token'],
            $this->sign("ky-session-refresh-v1\n{$opened['session_id']}\n{$opened['refresh_token']}"),
        );

        $this->assertSame($opened['session_id'], $rotated['session_id']);
        $this->assertNotSame($opened['session_token'], $rotated['session_token']);
        $this->assertNotSame($opened['refresh_token'], $rotated['refresh_token']);
        $this->assertSame(hash('sha256', $rotated['session_token']), $stored->fresh()->session_token_sha256);
    }

    public function test_configured_attestation_os_policy_prevents_session_issue_without_verified_claims(): void
    {
        config(['app_security.minimum_attested_android_os_version' => 130000]);

        try {
            app(DeviceSessionService::class)->open($this->user, $this->device->uuid);
            $this->fail('Session issuance must fail when an active OS policy has no verified attestation.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(428, $exception->status);
        }
    }

    public function test_refresh_token_replay_and_revoked_session_are_rejected(): void
    {
        $sessions = app(DeviceSessionService::class);
        $opened = $sessions->open($this->user, $this->device->uuid);
        $proof = $this->sign("ky-session-refresh-v1\n{$opened['session_id']}\n{$opened['refresh_token']}");
        $rotated = $sessions->refresh($this->user, $opened['refresh_token'], $proof);

        try {
            $sessions->refresh($this->user, $opened['refresh_token'], 'invalid-device-signature');
            $this->fail('A replay without a valid device signature must be rejected.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(401, $exception->status);
        }
        $this->assertDatabaseHas('app_security_sessions', ['uuid' => $opened['session_id'], 'revoked_at' => null]);
        $this->assertSame(0, DB::table('app_security_alerts')->count());

        try {
            $sessions->refresh($this->user, $opened['refresh_token'], $proof);
            $this->fail('A rotated refresh token must not be accepted again.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(401, $exception->status);
        }

        $this->assertDatabaseHas('app_security_refresh_token_history', [
            'session_id' => SecuritySession::query()->where('uuid', $opened['session_id'])->value('id'),
            'token_sha256' => hash('sha256', $opened['refresh_token']),
        ]);
        $this->assertDatabaseHas('app_security_sessions', ['uuid' => $opened['session_id'], 'revocation_reason' => 'refresh_token_reuse']);
        $this->assertDatabaseHas('app_security_logs', ['event_type' => 'refresh_token_reuse', 'device_id' => $this->device->id]);
        $this->assertDatabaseHas('app_security_alerts', ['alert_type' => 'refresh_token_reuse', 'device_id' => $this->device->id]);
        $this->assertDatabaseHas('app_security_audit_logs', [
            'action' => 'security.session.refresh_reuse_detected', 'target_id' => $opened['session_id'],
        ]);
        try {
            $sessions->refresh($this->user, $opened['refresh_token'], $proof);
            $this->fail('A repeated reuse attempt must remain rejected.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(401, $exception->status);
        }
        $this->assertSame(1, DB::table('app_security_alerts')->count());

        $sessions->revoke($this->user, $rotated['session_id']);
        try {
            $sessions->refresh(
                $this->user,
                $rotated['refresh_token'],
                $this->sign("ky-session-refresh-v1\n{$rotated['session_id']}\n{$rotated['refresh_token']}"),
            );
            $this->fail('A revoked session must not refresh.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(401, $exception->status);
        }
    }

    public function test_refresh_fails_closed_when_replay_history_migration_is_missing(): void
    {
        $opened = app(DeviceSessionService::class)->open($this->user, $this->device->uuid);
        Schema::drop('app_security_refresh_token_history');

        try {
            app(DeviceSessionService::class)->refresh(
                $this->user,
                $opened['refresh_token'],
                $this->sign("ky-session-refresh-v1\n{$opened['session_id']}\n{$opened['refresh_token']}"),
            );
            $this->fail('Refresh must fail closed if replay history is unavailable.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(503, $exception->status);
        }

        $session = SecuritySession::query()->where('uuid', $opened['session_id'])->firstOrFail();
        $this->assertSame(hash('sha256', $opened['refresh_token']), $session->refresh_token_sha256);
        $this->assertNull($session->revoked_at);
    }

    public function test_blocked_or_revoked_devices_cannot_open_a_session(): void
    {
        $sessions = app(DeviceSessionService::class);
        $this->device->forceFill(['device_status' => 'blocked'])->save();
        try {
            $sessions->open($this->user, $this->device->uuid);
            $this->fail('Blocked devices must not open sessions.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(423, $exception->status);
        }

        $this->device->forceFill(['device_status' => 'approved', 'revoked_at' => now()])->save();
        try {
            $sessions->open($this->user, $this->device->uuid);
            $this->fail('Revoked devices must not open sessions.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(423, $exception->status);
        }
    }

    public function test_opening_a_new_session_enforces_the_per_device_limit(): void
    {
        config(['app_security.max_sessions_per_device' => 1]);
        $sessions = app(DeviceSessionService::class);
        $first = $sessions->open($this->user, $this->device->uuid);
        $second = $sessions->open($this->user, $this->device->uuid);

        $oldSession = SecuritySession::query()->where('uuid', $first['session_id'])->firstOrFail();
        $currentSession = SecuritySession::query()->where('uuid', $second['session_id'])->firstOrFail();
        $this->assertNotNull($oldSession->revoked_at);
        $this->assertSame('concurrency_limit', $oldSession->revocation_reason);
        $this->assertSame(hash('sha256', $second['session_token']), $currentSession->session_token_sha256);
        $this->assertSame(1, SecuritySession::query()->where('device_id', $this->device->id)
            ->whereNull('revoked_at')->where('expires_at', '>', now())->count());

        try {
            $sessions->refresh(
                $this->user,
                $first['refresh_token'],
                $this->sign("ky-session-refresh-v1\n{$first['session_id']}\n{$first['refresh_token']}"),
            );
            $this->fail('A session revoked by the concurrency policy must not refresh.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(401, $exception->status);
        }
    }

    private function sign(string $message): string
    {
        openssl_sign($message, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        return rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }
}
