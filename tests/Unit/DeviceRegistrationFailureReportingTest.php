<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\AppSecurity\Services\AndroidKeyAttestationVerifier;
use App\Modules\AppSecurity\Services\AppSecurityException;
use App\Modules\AppSecurity\Services\DeviceRegistrationService;
use App\Modules\AppSecurity\Services\DeviceRiskEngine;
use App\Modules\AppSecurity\Services\RegistrationVelocityDetector;
use App\Modules\AppSecurity\Services\ReleaseVerificationService;
use App\Modules\AppSecurity\Services\SecurityPolicyService;
use App\Modules\AppSecurity\Services\SecuritySignatureService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeviceRegistrationFailureReportingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'cache.default' => 'array',
            'app_security.alert_notifications_enabled' => false,
            'app_security.alert_recipients' => [],
        ]);
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('app_device_challenges', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('purpose');
            $table->string('challenge_sha256');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('public_key_fingerprint');
            $table->uuid('installation_uuid')->nullable();
            $table->string('device_status')->default('approved');
            $table->timestamp('revoked_at')->nullable();
        });
        Schema::connection('sqlite')->create('app_security_block_rules', function (Blueprint $table): void {
            $table->id(); $table->string('target_type'); $table->string('target_value'); $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
        });
        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
        });
        Schema::connection('sqlite')->create('app_security_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type');
            $table->string('severity');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->uuid('request_uuid')->unique();
            $table->string('ip_address')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_alerts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('dedupe_key', 191)->unique();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('alert_type');
            $table->string('severity');
            $table->string('title');
            $table->text('message');
            $table->text('context')->nullable();
            $table->timestamps();
        });

        DB::table('app_device_challenges')->insert([
            'uuid' => '95bf0c89-6d6d-4995-ad38-cd91e94c44f2',
            'user_id' => 56,
            'purpose' => 'registration',
            'challenge_sha256' => hash('sha256', 'issued-challenge'),
            'expires_at' => now()->addMinutes(2),
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_invalid_key_proof_rolls_back_and_emits_audit_alert_after_denial(): void
    {
        $signature = \Mockery::mock(SecuritySignatureService::class);
        $signature->shouldReceive('publicKeyFingerprint')->once()->andReturn('fingerprint');
        $signature->shouldReceive('verify')->once()->andReturnFalse();
        $releases = \Mockery::mock(ReleaseVerificationService::class);
        $releases->shouldReceive('check')->once()->andReturn(['mandatory_update' => false]);

        $service = new DeviceRegistrationService(
            $signature,
            $releases,
            \Mockery::mock(AndroidKeyAttestationVerifier::class),
            \Mockery::mock(DeviceRiskEngine::class),
            \Mockery::mock(RegistrationVelocityDetector::class),
            \Mockery::mock(SecurityPolicyService::class),
        );
        $user = (new User())->forceFill(['id' => 56]);

        try {
            $service->register($user, [
                'challenge_id' => '95bf0c89-6d6d-4995-ad38-cd91e94c44f2',
                'challenge' => 'issued-challenge',
                'challenge_signature' => 'invalid-proof',
                'installation_uuid' => 'd8640d10-ed56-41b9-bf9d-dd59a27e7471',
                'public_key' => 'submitted-key-material',
                'package_name' => 'com.kneayerng.hrms',
                'signing_sha256' => str_repeat('A', 64),
                'version_name' => '1.0.0',
                'version_code' => 1,
                'android_sdk' => 35,
            ], '203.0.113.7');
            $this->fail('Invalid device proof must be denied.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(401, $exception->status);
            $this->assertSame('Device proof could not be verified.', $exception->getMessage());
        }

        $this->assertSame(0, DB::table('app_registered_devices')->count());
        $this->assertSame(0, DB::table('app_device_challenges')->whereNotNull('used_at')->count());
        $this->assertDatabaseHas('app_security_logs', [
            'event_type' => 'registration_proof_invalid', 'user_id' => 56, 'ip_address' => '203.0.113.7',
        ]);
        $details = (string) DB::table('app_security_logs')->value('details');
        $this->assertStringNotContainsString('submitted-key-material', $details);
        $this->assertSame(1, DB::table('app_security_alerts')
            ->where('alert_type', 'registration_proof_invalid')->count());
    }

    public function test_registration_enforces_the_customer_device_limit_after_validating_proof(): void
    {
        DB::table('users')->insert(['id' => 56]);
        DB::table('app_registered_devices')->insert([
            'user_id' => 56,
            'public_key_fingerprint' => 'an-existing-key',
            'installation_uuid' => '1674240f-7e8c-4296-8b67-62fe25b75673',
            'device_status' => 'approved',
        ]);

        $signature = \Mockery::mock(SecuritySignatureService::class);
        $signature->shouldReceive('publicKeyFingerprint')->once()->andReturn('new-key');
        $signature->shouldReceive('verify')->once()->andReturnTrue();
        $releases = \Mockery::mock(ReleaseVerificationService::class);
        $releases->shouldReceive('check')->once()->andReturn(['mandatory_update' => false]);
        $policy = \Mockery::mock(SecurityPolicyService::class);
        $policy->shouldReceive('value')->with('max_devices_customer')->once()->andReturn(1);

        $user = (new User())->forceFill(['id' => 56]);
        $user->setRelation('role', (object) ['slug' => 'customer']);
        $service = new DeviceRegistrationService(
            $signature,
            $releases,
            \Mockery::mock(AndroidKeyAttestationVerifier::class),
            \Mockery::mock(DeviceRiskEngine::class),
            \Mockery::mock(RegistrationVelocityDetector::class),
            $policy,
        );

        try {
            $service->register($user, [
                'challenge_id' => '95bf0c89-6d6d-4995-ad38-cd91e94c44f2',
                'challenge' => 'issued-challenge',
                'challenge_signature' => 'valid-proof',
                'installation_uuid' => 'd8640d10-ed56-41b9-bf9d-dd59a27e7471',
                'public_key' => 'new-key-material',
                'package_name' => 'com.kneayerng.hrms',
                'signing_sha256' => str_repeat('A', 64),
                'version_name' => '1.0.0',
                'version_code' => 1,
                'android_sdk' => 35,
            ], '203.0.113.7');
            $this->fail('A second customer device must be rejected when the configured maximum is one.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(429, $exception->status);
            $this->assertSame('registration_device_limit_exceeded', $exception->securityEvent);
        }

        $this->assertSame(1, DB::table('app_registered_devices')->where('user_id', 56)->count());
        $this->assertSame(0, DB::table('app_device_challenges')->whereNotNull('used_at')->count());
        $this->assertDatabaseHas('app_security_logs', [
            'event_type' => 'registration_device_limit_exceeded', 'user_id' => 56, 'ip_address' => '203.0.113.7',
        ]);
        $this->assertDatabaseHas('app_security_alerts', [
            'alert_type' => 'registration_device_limit_exceeded', 'severity' => 'medium', 'user_id' => 56,
        ]);
    }

    public function test_registration_rejects_a_retired_device_key_even_with_valid_proof(): void
    {
        DB::table('app_security_block_rules')->insert([
            'target_type' => 'key_fingerprint', 'target_value' => 'retired-key', 'is_active' => true,
        ]);
        $signature = \Mockery::mock(SecuritySignatureService::class);
        $signature->shouldReceive('publicKeyFingerprint')->once()->andReturn('retired-key');
        $signature->shouldReceive('verify')->once()->andReturnTrue();
        $releases = \Mockery::mock(ReleaseVerificationService::class);
        $releases->shouldReceive('check')->once()->andReturn(['mandatory_update' => false]);
        $service = new DeviceRegistrationService(
            $signature,
            $releases,
            \Mockery::mock(AndroidKeyAttestationVerifier::class),
            \Mockery::mock(DeviceRiskEngine::class),
            \Mockery::mock(RegistrationVelocityDetector::class),
            \Mockery::mock(SecurityPolicyService::class),
        );
        $user = (new User())->forceFill(['id' => 56]);

        try {
            $service->register($user, [
                'challenge_id' => '95bf0c89-6d6d-4995-ad38-cd91e94c44f2',
                'challenge' => 'issued-challenge',
                'challenge_signature' => 'valid-proof',
                'installation_uuid' => 'd8640d10-ed56-41b9-bf9d-dd59a27e7471',
                'public_key' => 'retired-key-material',
                'package_name' => 'com.kneayerng.hrms',
                'signing_sha256' => str_repeat('A', 64),
                'version_name' => '1.0.0',
                'version_code' => 1,
                'android_sdk' => 35,
            ], '203.0.113.7');
            $this->fail('A retired key must not register even when its proof is valid.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(423, $exception->status);
            $this->assertSame('device_key_blocked', $exception->securityEvent);
        }

        $this->assertSame(0, DB::table('app_registered_devices')->count());
        $this->assertSame(0, DB::table('app_device_challenges')->whereNotNull('used_at')->count());
    }
}
