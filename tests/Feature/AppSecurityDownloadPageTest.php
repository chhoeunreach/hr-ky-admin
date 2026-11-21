<?php

namespace Tests\Feature;

use App\Modules\AppSecurity\Models\SecurityRelease;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Modules\AppSecurity\Services\ApkInspectionService;
use Tests\TestCase;

class AppSecurityDownloadPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app_security.package_name' => 'com.kneayerng.hrms',
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_security_releases', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('package_name');
            $table->string('version_name');
            $table->unsignedBigInteger('version_code');
            $table->string('signing_sha256');
            $table->string('apk_sha256');
            $table->unsignedBigInteger('apk_size');
            $table->string('download_path')->nullable();
            $table->text('release_notes')->nullable();
            $table->unsignedBigInteger('minimum_supported_version')->default(0);
            $table->unsignedSmallInteger('minimum_android_sdk')->default(28);
            $table->unsignedSmallInteger('target_android_sdk')->default(36);
            $table->string('release_status')->default('draft');
            $table->boolean('is_mandatory')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_admin_roles', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('admin_id')->unique(); $table->string('security_role');
        });
        Schema::connection('sqlite')->create('app_security_audit_logs', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('admin_id')->nullable(); $table->string('actor_type');
            $table->string('actor_id')->nullable(); $table->string('action'); $table->string('target_type');
            $table->string('target_id'); $table->json('previous_state')->nullable(); $table->json('new_state')->nullable();
            $table->string('reason')->nullable(); $table->string('ip_address')->nullable(); $table->string('user_agent')->nullable();
            $table->timestamp('occurred_at'); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_alerts', function (Blueprint $table): void {
            $table->id(); $table->uuid('uuid')->unique(); $table->string('dedupe_key')->unique();
            $table->unsignedBigInteger('device_id')->nullable(); $table->unsignedBigInteger('user_id')->nullable();
            $table->string('alert_type'); $table->string('severity'); $table->string('title');
            $table->text('message'); $table->text('context')->nullable(); $table->timestamps();
        });
        Storage::fake('local');
    }

    public function test_download_page_shows_brand_supported_android_version_checksum_and_security_guidance(): void
    {
        $release = new SecurityRelease([
            'version_name' => '1.3.1',
            'version_code' => 18,
            'apk_size' => 171235527,
            'minimum_android_sdk' => 28,
            'target_android_sdk' => 36,
            'release_notes' => 'Security and reliability updates.',
            'apk_sha256' => str_repeat('a', 64),
        ]);

        $response = $this->view('app-security.download', ['release' => $release]);

        $response->assertSee('assets/images/kneayerng-app-logo.png')
            ->assertSee('KNEAYERNG for Android')
            ->assertSee('Android 9 (API 28) and later')
            ->assertSee('Target API 36')
            ->assertSee(str_repeat('a', 64))
            ->assertSee('When App Security enforcement is enabled')
            ->assertSee('/privacy');
    }

    public function test_public_routes_show_and_serve_only_the_active_release(): void
    {
        $this->createRelease('active', 'app-security/releases/official.apk');
        Storage::disk('local')->put('app-security/releases/official.apk', 'official apk bytes');

        $this->get('/app')->assertOk()->assertSee('Version 1.3.1 (build 18)');
        $this->getJson('/app/releases/latest')->assertOk()
            ->assertJsonPath('version_name', '1.3.1')
            ->assertJsonPath('apk_sha256', str_repeat('b', 64));
        $this->get('/app/releases/1.3.1/checksum')->assertOk()->assertSee(str_repeat('b', 64));
        $this->get('/app/download/android')->assertOk()->assertDownload('kneayerng-android-1.3.1.apk');
    }

    public function test_revoked_release_is_not_publicly_disclosed_or_downloadable(): void
    {
        $this->createRelease('revoked', 'app-security/releases/revoked.apk');
        Storage::disk('local')->put('app-security/releases/revoked.apk', 'revoked apk bytes');

        $this->get('/app')->assertOk()->assertSee('No Android release is currently available');
        $this->getJson('/app/releases/latest')->assertNotFound();
        $this->get('/app/releases/1.3.1/checksum')->assertNotFound();
        $this->get('/app/releases/1.3.1/download')->assertNotFound();
    }

    public function test_admin_json_api_uploads_only_an_inspected_draft_release(): void
    {
        DB::table('app_security_admin_roles')->insert(['admin_id' => 91, 'security_role' => 'it_admin']);
        $admin = (new User())->forceFill(['id' => 91, 'password' => bcrypt('Correct-Horse-77!')]);
        $this->actingAs($admin, 'admin');
        $inspector = \Mockery::mock(ApkInspectionService::class);
        $inspector->shouldReceive('inspect')->once()->andReturn([
            'package_name' => 'com.kneayerng.hrms', 'version_name' => '1.3.2', 'version_code' => 19,
            'signing_sha256' => str_repeat('A', 64), 'apk_sha256' => str_repeat('b', 64), 'apk_size' => 10,
            'minimum_android_sdk' => 28, 'target_android_sdk' => 36,
        ]);
        $this->app->instance(ApkInspectionService::class, $inspector);

        $response = $this->postJson('/api/admin/app-security/releases', [
            'apk' => UploadedFile::fake()->create('kneayerng.apk', 10, 'application/vnd.android.package-archive'),
            'release_notes' => 'Security update',
            'minimum_supported_version' => 18,
        ]);

        $response->assertCreated()->assertJsonPath('action', 'uploaded')
            ->assertJsonPath('release.release_status', 'draft')
            ->assertJsonPath('release.signing_sha256', str_repeat('A', 64));
        $this->assertDatabaseHas('app_security_releases', [
            'version_code' => 19, 'release_status' => 'draft', 'apk_sha256' => str_repeat('b', 64),
        ]);
        $this->assertDatabaseHas('app_security_audit_logs', ['action' => 'release.uploaded', 'admin_id' => 91]);
    }

    public function test_admin_json_api_activates_and_revokes_releases_with_step_up_and_audit(): void
    {
        DB::table('app_security_admin_roles')->insert(['admin_id' => 91, 'security_role' => 'it_admin']);
        $admin = (new User())->forceFill(['id' => 91, 'password' => bcrypt('Correct-Horse-77!')]);
        $this->actingAs($admin, 'admin');
        $this->createRelease('draft', 'app-security/releases/api-test.apk');
        $release = SecurityRelease::query()->firstOrFail();

        $this->postJson('/api/admin/app-security/releases/'.$release->uuid.'/activate', [
            'reason' => 'Publish verified test release', 'step_up_password' => 'Correct-Horse-77!',
        ])->assertOk()->assertJsonPath('action', 'activated');
        $this->assertDatabaseHas('app_security_releases', ['uuid' => $release->uuid, 'release_status' => 'active']);
        $this->assertDatabaseHas('app_security_alerts', ['alert_type' => 'release_published']);

        $this->postJson('/api/admin/app-security/releases/'.$release->uuid.'/revoke', [
            'reason' => 'Revoke verified test release', 'step_up_password' => 'Correct-Horse-77!',
        ])->assertOk()->assertJsonPath('action', 'revoked');
        $this->assertDatabaseHas('app_security_releases', ['uuid' => $release->uuid, 'release_status' => 'revoked']);
        $this->assertDatabaseHas('app_security_audit_logs', ['action' => 'release.revoked']);
        $this->assertDatabaseHas('app_security_alerts', ['alert_type' => 'release_revoked']);
    }

    private function createRelease(string $status, string $path): void
    {
        SecurityRelease::query()->create([
            'uuid' => (string) str()->uuid(),
            'package_name' => 'com.kneayerng.hrms',
            'version_name' => '1.3.1',
            'version_code' => 18,
            'signing_sha256' => str_repeat('c', 64),
            'apk_sha256' => str_repeat('b', 64),
            'apk_size' => 18,
            'download_path' => $path,
            'minimum_android_sdk' => 28,
            'target_android_sdk' => 36,
            'release_status' => $status,
            'is_mandatory' => false,
            'released_at' => now(),
        ]);
    }
}
