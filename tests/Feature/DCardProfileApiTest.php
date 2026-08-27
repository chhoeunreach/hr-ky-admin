<?php

namespace Tests\Feature;

use App\Models\DCardEmployee;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DCardProfileApiTest extends TestCase
{
    protected function setUp(): void
    {
        $this->prepareTestDatabaseFile();
        parent::setUp();

        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/testing',
            '--force' => true,
        ]);

        $this->createDCardSupportSchema();
    }

    public function test_d_card_profile_requires_authenticated_bearer_token(): void
    {
        $this->getJson('/api/hr-ky-admin/d-card/me')
            ->assertStatus(401);
    }

    public function test_d_card_profile_returns_logged_in_employee_front_preview_payload(): void
    {
        $role = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'is_active' => 1,
        ]);
        $this->grantPermission($role, 'view_profile');

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Kneayerng',
            'website_url' => 'https://www.kneayerng.com',
            'address' => 'Phnom Penh',
            'phone' => '16910505',
        ]);
        $branchId = DB::table('branches')->insertGetId([
            'name' => 'Phone Shop',
            'logo' => 'branch-logo.png',
            'payment_qr_codes' => json_encode([]),
        ]);
        $departmentId = DB::table('departments')->insertGetId([
            'dept_name' => 'Management',
            'branch_id' => $branchId,
        ]);
        $postId = DB::table('posts')->insertGetId([
            'post_name' => 'Employee',
        ]);

        $loggedInUser = User::factory()->create([
            'name' => 'Reach',
            'fullname' => 'ឈឿន រីច',
            'english_name' => 'CHHOEUN REACH',
            'employee_code' => 'KY-00021',
            'username' => 'reach',
            'email' => 'reach@example.com',
            'phone' => '16469756',
            'avatar' => 'avatar-from-user.png',
            'role_id' => $role->id,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'post_id' => $postId,
        ]);

        User::factory()->create([
            'name' => 'Other Employee',
            'employee_code' => 'KY-99999',
            'role_id' => $role->id,
        ]);

        DCardEmployee::create([
            'employee_code' => 'KY-00021',
            'name_khmer' => 'រេច',
            'name_english' => 'CHHOEUN REACH',
            'position_khmer' => 'បុគ្គលិក',
            'position_english' => 'Employee',
            'department' => 'management',
            'branch' => 'Phone Shop',
            'profile_photo_url' => 'https://example.test/d-card/reach.png',
            'phone' => '16469756',
            'email' => 'reach@example.com',
        ]);

        Passport::actingAs($loggedInUser);

        $response = $this->getJson('/api/hr-ky-admin/d-card/me');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.employee_code', 'KY-00021')
            ->assertJsonPath('data.name', 'ឈឿន រីច')
            ->assertJsonPath('data.fullname', 'ឈឿន រីច')
            ->assertJsonPath('data.name_khmer', 'ឈឿន រីច')
            ->assertJsonPath('data.english_name', 'CHHOEUN REACH')
            ->assertJsonPath('data.position_khmer', 'បុគ្គលិក')
            ->assertJsonPath('data.position_english', 'Employee')
            ->assertJsonPath('data.post', 'បុគ្គលិក')
            ->assertJsonPath('data.department', 'management')
            ->assertJsonPath('data.branch', 'Phone Shop')
            ->assertJsonPath('data.phone', '16469756')
            ->assertJsonPath('data.photo_url', 'https://example.test/d-card/reach.png')
            ->assertJsonPath('data.profile_photo_url', 'https://example.test/d-card/reach.png')
            ->assertJsonPath('data.branch_logo_url', asset('uploads/branch/branch-logo.png'))
            ->assertJsonPath('data.company_website', 'https://www.kneayerng.com')
            ->assertJsonPath('data.website_qr_data', 'https://www.kneayerng.com')
            ->assertJsonPath('data.telegram_qr_data', 'https://t.me/kneayerng')
            ->assertJsonStructure([
                'data' => [
                    'employee_code',
                    'name',
                    'english_name',
                    'position_khmer',
                    'position_english',
                    'post',
                    'department',
                    'branch',
                    'phone',
                    'photo_url',
                    'profile_photo_url',
                    'branch_logo_url',
                    'website_qr_url',
                    'telegram_qr_url',
                    'company_website',
                ],
            ]);

        $this->assertStringStartsWith('https://api.qrserver.com/v1/create-qr-code/', $response->json('data.website_qr_url'));
        $this->assertStringContainsString(urlencode('https://www.kneayerng.com'), $response->json('data.website_qr_url'));
        $this->assertStringContainsString(urlencode('https://t.me/kneayerng'), $response->json('data.telegram_qr_url'));
    }

    public function test_d_card_profile_does_not_merge_another_employee_card(): void
    {
        $role = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'is_active' => 1,
        ]);

        $loggedInUser = User::factory()->create([
            'name' => 'Wrong Source Name',
            'fullname' => 'បុគ្គលិក KY 0191',
            'employee_code' => 'KY-0191',
            'username' => 'KY-0192',
            'role_id' => $role->id,
        ]);

        DCardEmployee::create([
            'employee_code' => 'KY-0192',
            'name_khmer' => 'បុគ្គលិក KY 0192',
            'name_english' => 'WRONG EMPLOYEE',
            'profile_photo_url' => 'https://example.test/d-card/wrong-user.png',
        ]);

        Passport::actingAs($loggedInUser);

        $response = $this->getJson('/api/hr-ky-admin/d-card/me');

        $response->assertOk()
            ->assertJsonPath('data.employee_code', 'KY-0191')
            ->assertJsonPath('data.name', 'បុគ្គលិក KY 0191')
            ->assertJsonPath('data.name_khmer', 'បុគ្គលិក KY 0191')
            ->assertJsonPath('data.photo_url', asset('assets/images/img.png'));
    }

    private function createDCardSupportSchema(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('english_name')->nullable();
            $table->string('fullname')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->string('status')->default('verified');
            $table->boolean('is_active')->default(true);
            $table->date('joining_date')->nullable();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('website_url')->nullable();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->json('payment_qr_codes')->nullable();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('dept_name')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('post_name')->nullable();
        });

        Schema::create('d_card_employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('name_khmer');
            $table->string('name_english')->nullable();
            $table->string('position_khmer')->nullable();
            $table->string('position_english')->nullable();
            $table->string('department')->nullable();
            $table->string('branch')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('khqr_account_id')->nullable();
            $table->string('profile_photo_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    private function prepareTestDatabaseFile(): void
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
    }

    private function grantPermission(Role $role, string $permissionKey): void
    {
        $permission = Permission::create([
            'name' => ucfirst(str_replace('_', ' ', $permissionKey)),
            'permission_key' => $permissionKey,
            'permission_groups_id' => null,
        ]);

        PermissionRole::create([
            'permission_id' => $permission->id,
            'role_id' => $role->id,
        ]);
    }
}
