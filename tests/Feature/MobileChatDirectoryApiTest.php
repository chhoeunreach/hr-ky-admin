<?php

namespace Tests\Feature;

use App\Http\Middleware\SPAuthGateMW;
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

class MobileChatDirectoryApiTest extends TestCase
{
    protected function setUp(): void
    {
        $this->prepareTestDatabaseFile();
        parent::setUp();
        $this->withoutExceptionHandling();

        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/testing',
            '--force' => true,
        ]);

        $this->createMobileChatSupportTables();
        $this->extendUsersTableForMobileChat();

        DB::table('permission_roles')->delete();
        DB::table('permissions')->delete();
        DB::table('admins')->delete();
        DB::table('general_settings')->delete();
        DB::table('users')->delete();
        DB::table('roles')->delete();
        DB::table('posts')->delete();
        DB::table('departments')->delete();
        DB::table('branches')->delete();
        DB::table('companies')->delete();
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

    public function test_team_sheet_includes_employees_and_admins_with_admin_identity_fields(): void
    {
        $role = $this->makeRole('employee');
        $this->grantPermission($role, 'list_team_sheet');

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Main Company',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $branchId = DB::table('branches')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $departmentId = DB::table('departments')->insertGetId([
            'branch_id' => $branchId,
            'dept_name' => 'HR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $postId = DB::table('posts')->insertGetId([
            'post_name' => 'Officer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requester = $this->makeUser($role, [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'post_id' => $postId,
            'status' => 'verified',
            'is_active' => 1,
            'online_status' => 1,
        ]);

        $employee = $this->makeUser($role, [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'post_id' => $postId,
            'status' => 'verified',
            'is_active' => 1,
            'online_status' => 1,
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'username' => 'normal.user',
        ]);

        DB::table('admins')->insert([
            'name' => 'Admin User',
            'username' => 'admin.user',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Passport::actingAs($requester);

        $response = $this->withMiddleware([SPAuthGateMW::class])
            ->getJson('/api/users/company/team-sheet');

        $response->assertOk();
        $employees = $response->json('data.companyDetail.employee');

        $this->assertIsArray($employees);
        $this->assertCount(3, $employees);

        $normalEntry = collect($employees)->firstWhere('username', 'normal.user');
        $adminEntry = collect($employees)->firstWhere('username', 'admin.user');

        $this->assertNotNull($normalEntry);
        $this->assertSame('0', $normalEntry['is_admin']);
        $this->assertSame('employee', $normalEntry['role']);
        $this->assertSame('employee', $normalEntry['user_type']);
        $this->assertSame('1', $normalEntry['online_status']);

        $this->assertNotNull($adminEntry);
        $this->assertSame('1', $adminEntry['is_admin']);
        $this->assertSame('1', $adminEntry['admin']);
        $this->assertSame('admin', $adminEntry['role']);
        $this->assertSame('admin', $adminEntry['user_type']);
        $this->assertSame('Administration', $adminEntry['department']);
        $this->assertSame('Admin', $adminEntry['post']);
        $this->assertNotEmpty($adminEntry['conversation_id']);
    }

    public function test_chat_contacts_returns_all_employees_plus_admins(): void
    {
        $role = $this->makeRole('employee');

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Main Company',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $branchId = DB::table('branches')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $departmentId = DB::table('departments')->insertGetId([
            'branch_id' => $branchId,
            'dept_name' => 'HR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $postId = DB::table('posts')->insertGetId([
            'post_name' => 'Officer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('general_settings')->insert([
            'key' => 'mobile_chat_scope',
            'name' => 'Mobile Chat Scope',
            'type' => 'configuration',
            'value' => 'all_employees',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requester = $this->makeUser($role, [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'post_id' => $postId,
            'status' => 'verified',
            'is_active' => 1,
            'online_status' => 1,
            'username' => 'requester.user',
        ]);

        $onlineEmployee = $this->makeUser($role, [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'post_id' => $postId,
            'status' => 'verified',
            'is_active' => 1,
            'online_status' => 1,
            'name' => 'Online User',
            'email' => 'online@example.com',
            'username' => 'online.user',
        ]);

        DB::table('admins')->insert([
            'name' => 'Admin User',
            'username' => 'admin.user',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('admins')->insert([
            'name' => 'Admin User 2',
            'username' => 'admin.user2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'avatar' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Passport::actingAs($requester);

        $response = $this->getJson('/api/employee/chat/contacts');

        $response->assertOk();
        $contacts = $response->json('data.contacts');

        $this->assertIsArray($contacts);
        $this->assertCount(3, $contacts);

        $employeeEntry = collect($contacts)->firstWhere('username', 'online.user');
        $adminEntry = collect($contacts)->firstWhere('username', 'admin.user');
        $secondAdminEntry = collect($contacts)->firstWhere('username', 'admin.user2');

        $this->assertNotNull($employeeEntry);
        $this->assertSame('0', $employeeEntry['is_admin']);
        $this->assertSame('1', $employeeEntry['online_status']);
        $this->assertTrue($employeeEntry['is_online']);

        $this->assertNotNull($adminEntry);
        $this->assertSame('1', $adminEntry['is_admin']);
        $this->assertSame('admin', $adminEntry['role']);
        $this->assertSame('admin', $adminEntry['user_type']);
        $this->assertSame('admin', $adminEntry['directory_type']);
        $this->assertFalse($adminEntry['is_online']);
        $this->assertNotEmpty($adminEntry['conversation_id']);

        $this->assertNotNull($secondAdminEntry);
        $this->assertNotEmpty($secondAdminEntry['conversation_id']);
        $this->assertNotSame($adminEntry['conversation_id'], $secondAdminEntry['conversation_id']);
    }

    private function makeRole(string $slug): Role
    {
        return Role::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => 1,
        ]);
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

    private function makeUser(Role $role, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'username' => uniqid('u_', true),
            'phone' => '012345678',
            'status' => 'verified',
            'is_active' => 1,
            'user_type' => 'employee',
            'online_status' => 0,
        ], $overrides));
    }

    private function createMobileChatSupportTables(): void
    {
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('dept_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('post_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('username');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('avatar')->nullable();
                $table->boolean('is_active')->default(1);
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('general_settings')) {
            Schema::create('general_settings', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('key')->unique();
                $table->string('type')->nullable();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chat_conversations')) {
            Schema::create('chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();
            });
        }
    }

    private function extendUsersTableForMobileChat(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'post_id')) {
                $table->unsignedBigInteger('post_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->nullable();
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(1);
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type')->nullable();
            }
            if (!Schema::hasColumn('users', 'online_status')) {
                $table->boolean('online_status')->default(0);
            }
        });
    }
}
