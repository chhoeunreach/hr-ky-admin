<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Modules\AppSecurity\Models\RegisteredDevice;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityIndexPageTest extends TestCase
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
            'app_security.enabled' => true,
        ]);
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('theme_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('primary_color')->default('#0d6efd');
            $table->string('hover_color')->default('#0b5ed7');
            $table->string('dark_primary_color')->default('#0d6efd');
            $table->string('dark_hover_color')->default('#0b5ed7');
            $table->timestamps();
        });

        DB::table('theme_settings')->insert([
            'primary_color' => '#0d6efd',
            'hover_color' => '#0b5ed7',
            'dark_primary_color' => '#0d6efd',
            'dark_hover_color' => '#0b5ed7',
        ]);

        Schema::connection('sqlite')->create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('Test Company');
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        DB::table('companies')->insert([
            'name' => 'Test Company',
            'logo' => null,
        ]);

        Schema::connection('sqlite')->create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('permission_key')->unique();
        });

        Schema::connection('sqlite')->create('permission_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });

        Schema::connection('sqlite')->create('app_security_admin_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id')->unique();
            $table->string('security_role');
            $table->string('assignment_reason')->nullable();
        });

        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('installation_uuid')->nullable();
            $table->string('user_type')->default('staff');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('package_name')->nullable();
            $table->string('device_model')->nullable();
            $table->string('version_name')->nullable();
            $table->unsignedBigInteger('version_code')->nullable();
            $table->string('signing_sha256')->nullable();
            $table->string('public_key_fingerprint')->nullable();
            $table->text('public_key')->nullable();
            $table->string('attestation_level')->nullable();
            $table->integer('risk_score')->default(0);
            $table->string('risk_level')->default('low');
            $table->string('device_status')->default('approved');
            $table->string('last_ip_address')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('app_security_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_token_sha256')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('app_security_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('setting_key')->unique();
            $table->json('setting_value');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

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

        Schema::connection('sqlite')->create('app_security_alerts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('dedupe_key')->unique();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('alert_type');
            $table->string('severity');
            $table->string('title');
            $table->text('message');
            $table->text('context')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('workspace_type')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('online_status')->nullable();
            $table->string('uuid')->nullable();
            $table->string('device_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_app_security_index_page_loads_without_500_error(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        DB::table('app_security_admin_roles')->insert([
            'admin_id' => $admin->id,
            'security_role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.app-security.index'));

        $response->assertStatus(200);
        $response->assertSee('App Security');
        $response->assertViewHas('securityEnabled', true);
        $response->assertViewHas('settings');
    }
}
