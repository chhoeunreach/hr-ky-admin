<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\ThemeModeApiController;
use App\Http\Controllers\Web\ThemeModeController;
use App\Http\Middleware\MobileFunctionPermission;
use App\Models\Admin;
use App\Models\AppSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ThemeModeManagementTest extends TestCase
{
    private Admin $admin;
    private string $viewDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobile_permissions' => require __DIR__.'/../../config/mobile_permissions.php']);

        Schema::dropIfExists('app_settings');
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('value')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('admins');
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::dropIfExists('roles');
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->admin = Admin::create([
            'name' => 'Theme Admin',
            'username' => 'themeadmin',
            'email' => 'theme@example.test',
            'password' => bcrypt('secret'),
            'is_active' => true,
        ]);

        Gate::before(fn ($user = null, $ability = null) => true);
        Auth::guard('admin')->login($this->admin);

        $this->viewDirectory = sys_get_temp_dir().'/theme-modes-view-'.bin2hex(random_bytes(6));
        mkdir($this->viewDirectory.'/layouts', 0777, true);
        file_put_contents(
            $this->viewDirectory.'/layouts/master.blade.php',
            "@yield('title')|@yield('action')|@yield('styles')|@yield('main-content')|@yield('scripts')"
        );
        view()->getFinder()->prependLocation($this->viewDirectory);
        view()->getFinder()->flush();

        Route::middleware(MobileFunctionPermission::class)
            ->get('/_test/mobile-tool/api-theme-modes', [ThemeModeApiController::class, 'index']);
    }

    protected function tearDown(): void
    {
        if (isset($this->viewDirectory)) {
            @unlink($this->viewDirectory.'/layouts/master.blade.php');
            @rmdir($this->viewDirectory.'/layouts');
            @rmdir($this->viewDirectory);
        }

        parent::tearDown();
    }

    public function test_theme_modes_page_renders_with_royal_blue_and_system_themes(): void
    {
        $response = $this->get(route('admin.mobile-tools.theme-modes'));
        $response->assertOk()
            ->assertSee('Royal Blue & Gold')
            ->assertSee('royal_blue_gold')
            ->assertSee('#061A40')
            ->assertSee('#0756C8')
            ->assertSee('#FFC857');
    }

    public function test_admin_can_store_custom_theme_mode(): void
    {
        Role::create(['name' => 'Manager', 'slug' => 'manager', 'is_active' => true]);

        $payload = [
            'key' => 'sunset_horizon',
            'name' => 'Sunset Horizon',
            'description' => 'Warm dusk palette for creative teams',
            'icon' => 'sun',
            'primary_color' => '#E11D48',
            'secondary_color' => '#BE123C',
            'accent_color' => '#F59E0B',
            'background_color' => '#FFFBEB',
            'card_color' => '#FEF3C7',
            'text_color' => '#78350F',
            'is_dark' => '0',
            'is_active' => '1',
            'allowed_roles' => ['manager'],
        ];

        $response = $this->post(route('admin.mobile-tools.theme-modes.store'), $payload);
        $response->assertRedirect(route('admin.mobile-tools.theme-modes'));

        $controller = new ThemeModeController();
        $themes = $controller->config();

        $this->assertArrayHasKey('sunset_horizon', $themes);
        $this->assertSame('Sunset Horizon', $themes['sunset_horizon']['name']);
        $this->assertSame('#E11D48', $themes['sunset_horizon']['primary_color']);
        $this->assertSame('#FEF3C7', $themes['sunset_horizon']['card_color']);
        $this->assertSame(['manager'], $themes['sunset_horizon']['allowed_roles']);
        $this->assertFalse($themes['sunset_horizon']['is_system']);
    }

    public function test_admin_can_update_theme_mode_colors_and_roles(): void
    {
        $updatePayload = [
            'name' => 'Royal Blue & Gold Luxury',
            'description' => 'Updated luxury corporate palette',
            'icon' => 'crown',
            'primary_color' => '#082D68',
            'secondary_color' => '#0B3975',
            'accent_color' => '#FFD700',
            'background_color' => '#0A192F',
            'card_color' => '#112240',
            'text_color' => '#E6F1FF',
            'is_dark' => '1',
            'is_active' => '1',
            'allowed_roles' => ['admin', 'manager'],
        ];

        $response = $this->post(route('admin.mobile-tools.theme-modes.update', 'royal_blue_gold'), $updatePayload);
        $response->assertRedirect(route('admin.mobile-tools.theme-modes'));

        $controller = new ThemeModeController();
        $themes = $controller->config();

        $this->assertSame('Royal Blue & Gold Luxury', $themes['royal_blue_gold']['name']);
        $this->assertSame('#082D68', $themes['royal_blue_gold']['primary_color']);
        $this->assertSame('#FFD700', $themes['royal_blue_gold']['accent_color']);
        $this->assertSame(['admin', 'manager'], $themes['royal_blue_gold']['allowed_roles']);
    }

    public function test_admin_can_toggle_theme_visibility(): void
    {
        $response = $this->post(route('admin.mobile-tools.theme-modes.toggle', 'forest_green'));
        $response->assertRedirect(route('admin.mobile-tools.theme-modes'));

        $controller = new ThemeModeController();
        $themes = $controller->config();
        $this->assertFalse($themes['forest_green']['is_active']);

        // Toggle back
        $this->post(route('admin.mobile-tools.theme-modes.toggle', 'forest_green'));
        $themes = $controller->config();
        $this->assertTrue($themes['forest_green']['is_active']);
    }

    public function test_admin_cannot_delete_system_theme_but_hides_it(): void
    {
        $response = $this->delete(route('admin.mobile-tools.theme-modes.destroy', 'amoled'));
        $response->assertRedirect(route('admin.mobile-tools.theme-modes'));

        $controller = new ThemeModeController();
        $themes = $controller->config();

        $this->assertArrayHasKey('amoled', $themes);
        $this->assertFalse($themes['amoled']['is_active']);
    }

    public function test_admin_can_delete_custom_theme(): void
    {
        $payload = [
            'key' => 'custom_to_delete',
            'name' => 'Temporary Theme',
            'primary_color' => '#112233',
            'secondary_color' => '#223344',
            'accent_color' => '#334455',
            'background_color' => '#445566',
            'card_color' => '#556677',
            'text_color' => '#667788',
        ];
        $this->post(route('admin.mobile-tools.theme-modes.store'), $payload);

        $response = $this->delete(route('admin.mobile-tools.theme-modes.destroy', 'custom_to_delete'));
        $response->assertRedirect(route('admin.mobile-tools.theme-modes'));

        $controller = new ThemeModeController();
        $themes = $controller->config();
        $this->assertArrayNotHasKey('custom_to_delete', $themes);
    }

    public function test_admin_reset_reverts_all_to_defaults(): void
    {
        $this->post(route('admin.mobile-tools.theme-modes.update', 'royal_blue_gold'), [
            'name' => 'Modified Name',
            'primary_color' => '#111111',
            'secondary_color' => '#222222',
            'accent_color' => '#333333',
            'background_color' => '#444444',
            'card_color' => '#555555',
            'text_color' => '#666666',
        ]);

        $this->post(route('admin.mobile-tools.theme-modes.reset'));

        $controller = new ThemeModeController();
        $themes = $controller->config();
        $this->assertSame('Royal Blue & Gold', $themes['royal_blue_gold']['name']);
        $this->assertSame('#0756C8', $themes['royal_blue_gold']['primary_color']);
        $this->assertSame('#FFC857', $themes['royal_blue_gold']['accent_color']);
    }

    public function test_admin_can_reset_single_core_theme_to_baseline(): void
    {
        // Modify royal_blue_gold
        $this->post(route('admin.mobile-tools.theme-modes.update', 'royal_blue_gold'), [
            'name' => 'Modified Royal',
            'primary_color' => '#123456',
            'secondary_color' => '#234567',
            'accent_color' => '#345678',
            'background_color' => '#456789',
            'card_color' => '#56789A',
            'text_color' => '#6789AB',
        ]);

        $controller = new ThemeModeController();
        $this->assertSame('Modified Royal', $controller->config()['royal_blue_gold']['name']);

        // Reset only royal_blue_gold
        $response = $this->post(route('admin.mobile-tools.theme-modes.reset-theme', 'royal_blue_gold'));
        $response->assertRedirect(route('admin.mobile-tools.theme-modes'));

        $themes = $controller->config();
        $this->assertSame('Royal Blue & Gold', $themes['royal_blue_gold']['name']);
        $this->assertSame('#0756C8', $themes['royal_blue_gold']['primary_color']);
        $this->assertSame('#FFC857', $themes['royal_blue_gold']['accent_color']);
    }

    public function test_mobile_api_filters_by_active_and_role(): void
    {
        $managerRole = Role::create(['name' => 'Manager', 'slug' => 'manager', 'is_active' => true]);
        $employeeRole = Role::create(['name' => 'Employee', 'slug' => 'employee', 'is_active' => true]);

        // Hide forest_green
        $this->post(route('admin.mobile-tools.theme-modes.toggle', 'forest_green'));

        // Restrict neo_glass to manager only
        $payload = [
            'name' => 'Neo Glass',
            'primary_color' => '#7C3AED',
            'secondary_color' => '#2563EB',
            'accent_color' => '#38BDF8',
            'background_color' => '#120B35',
            'card_color' => '#1FFFFFFF',
            'text_color' => '#FFFFFF',
            'allowed_roles' => ['manager'],
        ];
        $this->post(route('admin.mobile-tools.theme-modes.update', 'neo_glass'), $payload);

        // Test as Employee user
        $employee = User::create([
            'name' => 'Regular Employee',
            'email' => 'emp@example.test',
            'password' => bcrypt('secret'),
            'role_id' => $employeeRole->id,
            'is_admin' => false,
        ]);
        $employee->setRelation('role', $employeeRole);

        $response = $this->actingAs($employee)->getJson('/_test/mobile-tool/api-theme-modes');
        $response->assertOk()->assertJson(['status' => true]);

        $returnedKeys = collect($response->json('data'))->pluck('key')->all();

        // forest_green is hidden -> not in returned list
        $this->assertNotContains('forest_green', $returnedKeys);
        // neo_glass is manager only -> employee cannot see it
        $this->assertNotContains('neo_glass', $returnedKeys);
        // royal_blue_gold is active & open -> employee can see it
        $this->assertContains('royal_blue_gold', $returnedKeys);

        // Test as Manager user
        $manager = User::create([
            'name' => 'Branch Manager',
            'email' => 'mgr@example.test',
            'password' => bcrypt('secret'),
            'role_id' => $managerRole->id,
            'is_admin' => false,
        ]);
        $manager->setRelation('role', $managerRole);

        $mgrResponse = $this->actingAs($manager)->getJson('/_test/mobile-tool/api-theme-modes');
        $mgrKeys = collect($mgrResponse->json('data'))->pluck('key')->all();

        // Manager sees neo_glass
        $this->assertContains('neo_glass', $mgrKeys);
        // Still hides forest_green because forest_green is_active is false
        $this->assertNotContains('forest_green', $mgrKeys);
    }

    public function test_sync_role_themes_restricts_and_allows_themes_per_role(): void
    {
        $roleStaff = Role::create(['name' => 'Staff', 'slug' => 'staff']);
        $roleDriver = Role::create(['name' => 'Driver', 'slug' => 'driver']);
        $roleManager = Role::create(['name' => 'Manager', 'slug' => 'manager']);

        $controller = new ThemeModeController();

        // Initially royal_blue_gold and light are allowed for everyone (allowed_roles == [])
        $config = $controller->config();
        $this->assertTrue($controller->isThemeAllowedForRole($config['royal_blue_gold'], $roleStaff));
        $this->assertTrue($controller->isThemeAllowedForRole($config['royal_blue_gold'], $roleDriver));

        // Now Admin restricts Staff to only 'light' (unchecking royal_blue_gold)
        $controller->syncRoleThemes($roleStaff, ['light']);

        $updated = $controller->config();

        // royal_blue_gold should no longer be allowed for Staff, but still allowed for Driver and Manager
        $this->assertFalse($controller->isThemeAllowedForRole($updated['royal_blue_gold'], $roleStaff));
        $this->assertTrue($controller->isThemeAllowedForRole($updated['royal_blue_gold'], $roleDriver));
        $this->assertTrue($controller->isThemeAllowedForRole($updated['royal_blue_gold'], $roleManager));

        // 'light' remains allowed for Staff
        $this->assertTrue($controller->isThemeAllowedForRole($updated['light'], $roleStaff));

        // Now Admin re-grants royal_blue_gold to Staff
        $controller->syncRoleThemes($roleStaff, ['royal_blue_gold', 'light']);
        $reupdated = $controller->config();
        $this->assertTrue($controller->isThemeAllowedForRole($reupdated['royal_blue_gold'], $roleStaff));
    }

    public function test_end_to_end_role_theme_sync_affects_mobile_api(): void
    {
        $roleStaff = Role::create(['name' => 'Staff', 'slug' => 'staff']);
        $roleDriver = Role::create(['name' => 'Driver', 'slug' => 'driver']);

        $controller = new ThemeModeController();
        // Restrict Staff to sunset and light
        $controller->syncRoleThemes($roleStaff, ['sunset', 'light']);

        // Staff employee
        $staffUser = User::create([
            'name' => 'Staff User',
            'email' => 'staff_api@test.com',
            'password' => bcrypt('secret'),
            'role_id' => $roleStaff->id,
            'is_admin' => false,
        ]);
        $staffUser->setRelation('role', $roleStaff);

        // Driver employee
        $driverUser = User::create([
            'name' => 'Driver User',
            'email' => 'driver_api@test.com',
            'password' => bcrypt('secret'),
            'role_id' => $roleDriver->id,
            'is_admin' => false,
        ]);
        $driverUser->setRelation('role', $roleDriver);

        // 1. Staff calls Mobile API
        $staffResponse = $this->actingAs($staffUser)->getJson('/_test/mobile-tool/api-theme-modes');
        $staffResponse->assertOk();
        $staffKeys = collect($staffResponse->json('data'))->pluck('key')->all();

        $this->assertContains('sunset', $staffKeys);
        $this->assertContains('light', $staffKeys);
        $this->assertNotContains('royal_blue_gold', $staffKeys);
        $this->assertNotContains('ocean_blue', $staffKeys);

        // 2. Driver calls Mobile API (unrestricted)
        $driverResponse = $this->actingAs($driverUser)->getJson('/_test/mobile-tool/api-theme-modes');
        $driverResponse->assertOk();
        $driverKeys = collect($driverResponse->json('data'))->pluck('key')->all();

        $this->assertContains('royal_blue_gold', $driverKeys);
        $this->assertContains('ocean_blue', $driverKeys);
        $this->assertContains('sunset', $driverKeys);
        $this->assertContains('light', $driverKeys);
    }
}
