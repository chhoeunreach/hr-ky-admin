<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Services\AppSecurityException;
use App\Modules\AppSecurity\Services\ReleaseVerificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityReleaseVerificationTest extends TestCase
{
    private const PACKAGE = 'com.kneayerng.hrms';
    private const SIGNER = 'AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app_security.package_name' => self::PACKAGE,
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_security_releases', function (Blueprint $table): void {
            $table->id();
            $table->string('package_name');
            $table->unsignedBigInteger('version_code');
            $table->string('release_status');
            $table->string('signing_sha256', 64);
            $table->unsignedBigInteger('minimum_supported_version')->default(0);
            $table->unsignedBigInteger('minimum_android_sdk')->default(28);
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();
        });
    }

    public function test_archived_releases_can_remain_optional_above_the_minimum_floor(): void
    {
        $this->release(18, 'archived');
        $this->release(20, 'active', false, 18);

        $result = app(ReleaseVerificationService::class)->check(self::PACKAGE, self::SIGNER, 18);

        $this->assertFalse($result['mandatory_update']);
        $this->assertSame(20, $result['latest']->version_code);
    }

    public function test_minimum_supported_version_forces_only_versions_below_the_floor(): void
    {
        $this->release(18, 'archived');
        $this->release(19, 'archived');
        $this->release(20, 'active', false, 19);

        $service = app(ReleaseVerificationService::class);
        $this->assertTrue($service->check(self::PACKAGE, self::SIGNER, 18)['mandatory_update']);
        $this->assertFalse($service->check(self::PACKAGE, self::SIGNER, 19)['mandatory_update']);
    }

    public function test_mandatory_active_release_forces_older_versions_regardless_of_status(): void
    {
        $this->release(19, 'archived');
        $this->release(20, 'active', true, 0);

        $result = app(ReleaseVerificationService::class)->check(self::PACKAGE, self::SIGNER, 19);

        $this->assertTrue($result['mandatory_update']);
    }

    public function test_revoked_releases_remain_rejected(): void
    {
        $this->release(18, 'revoked');
        $this->release(20, 'active');

        try {
            app(ReleaseVerificationService::class)->check(self::PACKAGE, self::SIGNER, 18);
            $this->fail('A revoked application release must not be recognized.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(403, $exception->status);
        }
    }

    public function test_android_below_the_installed_release_minimum_is_rejected(): void
    {
        $this->release(20, 'active', false, 0, 30);

        try {
            app(ReleaseVerificationService::class)->check(self::PACKAGE, self::SIGNER, 20, 29);
            $this->fail('An Android device below the APK minimum SDK must be rejected.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(428, $exception->status);
        }
    }

    public function test_mandatory_update_is_rejected_when_device_cannot_run_that_release(): void
    {
        $this->release(18, 'archived');
        $this->release(20, 'active', true, 0, 30);

        try {
            app(ReleaseVerificationService::class)->check(self::PACKAGE, self::SIGNER, 18, 29);
            $this->fail('A device unable to install a mandatory release must be reported unsupported.');
        } catch (AppSecurityException $exception) {
            $this->assertSame(428, $exception->status);
        }
    }

    public function test_optional_update_is_not_advertised_when_device_cannot_run_it(): void
    {
        $this->release(18, 'archived');
        $this->release(20, 'active', false, 0, 30);

        $result = app(ReleaseVerificationService::class)->check(self::PACKAGE, self::SIGNER, 18, 29);

        $this->assertFalse($result['mandatory_update']);
        $this->assertFalse($result['latest_compatible']);
    }

    private function release(
        int $version,
        string $status,
        bool $mandatory = false,
        int $minimumSupportedVersion = 0,
        int $minimumAndroidSdk = 28,
    ): void {
        DB::table('app_security_releases')->insert([
            'package_name' => self::PACKAGE,
            'version_code' => $version,
            'release_status' => $status,
            'signing_sha256' => self::SIGNER,
            'minimum_supported_version' => $minimumSupportedVersion,
            'minimum_android_sdk' => $minimumAndroidSdk,
            'is_mandatory' => $mandatory,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
