<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RolePermissionSeparationTest extends TestCase
{
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

        Schema::create('admins', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Admin');
            $t->string('email')->default('admin@example.com');
            $t->string('username')->default('admin');
            $t->string('password')->default('password');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug');
            $t->boolean('backend_login_authorize')->default(1);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Test User');
            $t->string('email')->default('user@example.com');
            $t->string('username')->default('testuser');
            $t->string('password')->default('secret');
            $t->unsignedBigInteger('role_id')->nullable();
            $t->boolean('status')->default(true);
            $t->timestamps();
        });

        Schema::create('permission_group_types', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug');
            $t->timestamps();
        });

        Schema::create('permission_groups', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedBigInteger('group_type_id');
            $t->timestamps();
        });

        Schema::create('permissions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('permission_key');
            $t->unsignedBigInteger('permission_groups_id');
            $t->timestamps();
        });

        Schema::create('permission_roles', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('permission_id');
            $t->unsignedBigInteger('role_id');
            $t->timestamps();
        });

        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Default Company');
            $t->timestamps();
        });

        Schema::create('theme_settings', function (Blueprint $t) {
            $t->id();
            $t->timestamps();
        });

        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->default(1);
            $t->string('name')->default('Main Branch');
            $t->timestamps();
        });

        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->default(1);
            $t->unsignedBigInteger('branch_id')->default(1);
            $t->string('dept_name')->default('HR');
            $t->timestamps();
        });

        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('department_id')->default(1);
            $t->string('post_name')->default('Officer');
            $t->timestamps();
        });

        Schema::create('features', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->string('name');
            $t->string('group')->nullable();
            $t->string('status')->default('1');
        });

        Schema::create('role_mobile_layouts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('role_id');
            $t->string('menu_key', 100);
            $t->string('zone', 30);
            $t->unsignedSmallInteger('position')->default(0);
            $t->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('value')->nullable();
            $t->boolean('status')->default(0);
            $t->timestamps();
        });

        // Seed permission group types
        DB::table('permission_group_types')->insert([
            ['id' => 1, 'name' => 'Web', 'slug' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'API', 'slug' => 'api', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed permission groups
        DB::table('permission_groups')->insert([
            ['id' => 1, 'name' => 'Admin Portal Access', 'group_type_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Attendance', 'group_type_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Mobile Attendance', 'group_type_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed permissions
        DB::table('permissions')->insert([
            ['id' => 1, 'name' => 'Access Web Admin Portal', 'permission_key' => 'access_admin_panel', 'permission_groups_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Role and Permission', 'permission_key' => 'role_permission', 'permission_groups_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Attendance List', 'permission_key' => 'attendance_list', 'permission_groups_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Mobile Attendance Menu', 'permission_key' => 'mobile.menu.attendance', 'permission_groups_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Gate::before(fn () => true);
    }

    public function test_access_admin_panel_permission_exists_under_web_type(): void
    {
        $perm = DB::table('permissions')->where('permission_key', 'access_admin_panel')->first();
        $this->assertNotNull($perm, 'access_admin_panel permission must exist');

        $group = DB::table('permission_groups')->where('id', $perm->permission_groups_id)->first();
        $this->assertNotNull($group);

        $groupType = DB::table('permission_group_types')->where('id', $group->group_type_id)->first();
        $this->assertNotNull($groupType);
        $this->assertEquals('web', $groupType->slug, 'access_admin_panel must belong to a Web group type');
    }

    public function test_assign_permissions_syncs_backend_login_authorize_flag(): void
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::create([
            'name' => 'Custom Field Staff ' . uniqid(),
            'slug' => 'custom-field-staff-' . uniqid(),
            'backend_login_authorize' => 0,
            'is_active' => 1,
        ]);

        $adminPanelPerm = DB::table('permissions')->where('permission_key', 'access_admin_panel')->first();
        $this->assertNotNull($adminPanelPerm);

        // 1. Assign permissions WITH access_admin_panel -> backend_login_authorize should become 1
        $response = $this->actingAs($admin, 'admin')->put(route('admin.role.assign-permissions', $role->id), [
            'permission_value' => [$adminPanelPerm->id],
        ]);
        $response->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertEquals(1, $role->backend_login_authorize, 'Role should have backend_login_authorize = 1 when access_admin_panel is granted');

        // 2. Assign permissions WITHOUT access_admin_panel -> backend_login_authorize should become 0
        $otherPerm = DB::table('permissions')->where('permission_key', 'attendance_list')->first();
        $response = $this->actingAs($admin, 'admin')->put(route('admin.role.assign-permissions', $role->id), [
            'permission_value' => [$otherPerm->id],
        ]);
        $response->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertEquals(0, $role->backend_login_authorize, 'Role should have backend_login_authorize = 0 when access_admin_panel is omitted');
    }

    public function test_role_permission_view_renders_platform_tabs_and_presets(): void
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'username' => 'super_admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'backend_login_authorize' => 1,
            'is_active' => 1,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.roles.permission', $role->id));
        $response->assertOk();

        // Check for presence of Platform tabs
        $response->assertSee('Web Portal (Back-Office Admin)');
        $response->assertSee('Mobile App (Frontline & Self-Service)');

        // Check for presence of 1-Click Role Presets
        $response->assertSee('btnPresetSuperAdmin', false);
        $response->assertSee('btnPresetHrManager', false);
        $response->assertSee('btnPresetBranchSupervisor', false);
        $response->assertSee('btnPresetEmployeeMobile', false);
        $response->assertSee('btnPresetPayrollCounter', false);

        // Check for search filter and gatekeeper badge
        $response->assertSee('permissionSearchFilter', false);
        $response->assertSee('Gatekeeper (Web Access)');

        // Check for presence of Theme Modes Studio card and checkboxes
        $response->assertSee('role-theme-modes-grid');
        $response->assertSee('allowed_themes[]', false);
        $response->assertSee('btn-select-all-themes', false);
        $response->assertSee('btn-deselect-all-themes', false);
    }

    public function test_assign_permissions_syncs_allowed_theme_modes(): void
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin_theme@test.com',
            'username' => 'super_theme',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::create([
            'name' => 'Driver',
            'slug' => 'driver',
            'backend_login_authorize' => 0,
            'is_active' => 1,
        ]);

        $themeController = app(\App\Http\Controllers\Web\ThemeModeController::class);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.role.assign-permissions', $role->id), [
            'permission_value' => [],
            'theme_modes_submitted' => '1',
            'allowed_themes' => ['sunset', 'amoled'],
        ]);
        $response->assertSessionHasNoErrors();

        $config = $themeController->config();
        $this->assertTrue($themeController->isThemeAllowedForRole($config['sunset'], $role));
        $this->assertTrue($themeController->isThemeAllowedForRole($config['amoled'], $role));
        $this->assertFalse($themeController->isThemeAllowedForRole($config['royal_blue_gold'], $role));
    }

    public function test_mobile_only_user_without_admin_access_is_blocked_by_middleware(): void
    {
        $mobileRole = Role::create([
            'name' => 'Mobile Field Staff',
            'slug' => 'mobile-field-staff',
            'backend_login_authorize' => 0,
            'is_active' => 1,
        ]);

        $user = User::create([
            'name' => 'Mobile Worker',
            'email' => 'worker@mobile.com',
            'username' => 'worker_mobile',
            'password' => bcrypt('password'),
            'role_id' => $mobileRole->id,
            'status' => true,
        ]);

        // Attempt to access web admin dashboard route as mobile worker
        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        // Middleware should block and redirect to admin.login with error
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('danger');
    }
}
