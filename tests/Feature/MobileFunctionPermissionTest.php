<?php

namespace Tests\Feature;

use App\Http\Middleware\MobileFunctionPermission;
use App\Models\User;
use App\Services\Authorization\MobilePermissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MobileFunctionPermissionTest extends TestCase
{
    private array $abilities = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobile_permissions' => require __DIR__.'/../../config/mobile_permissions.php']);
        $this->actingAs((new User)->forceFill(['id' => 10]));
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        Route::middleware(MobileFunctionPermission::class)->group(function () {
            Route::get('/_test/mobile/read', [MobilePermissionProbeController::class, 'read']);
            Route::post('/_test/mobile/write', [MobilePermissionProbeController::class, 'write']);
            Route::get('/_test/mobile/unknown', [MobilePermissionProbeController::class, 'unknown']);
            Route::get('/_test/mobile/bootstrap', [MobilePermissionProbeController::class, 'bootstrap']);
        });
        config(['mobile_permissions.functions.MobilePermissionProbeController@read' => ['groups', 'read_groups'],
            'mobile_permissions.functions.MobilePermissionProbeController@write' => ['groups', 'write_groups'],
            'mobile_permissions.functions.MobilePermissionProbeController@bootstrap' => null]);
    }

    public function test_menu_access_alone_does_not_grant_read_or_write(): void
    {
        $this->abilities = ['mobile.menu.groups'];
        $this->getJson('/_test/mobile/read')->assertForbidden();
        $this->postJson('/_test/mobile/write')->assertForbidden();
    }

    public function test_read_permission_does_not_grant_mutations(): void
    {
        $this->abilities = ['mobile.menu.groups', 'read_groups'];
        $this->getJson('/_test/mobile/read')->assertOk();
        $this->postJson('/_test/mobile/write')->assertForbidden();
    }

    public function test_revoked_menu_denies_direct_function_request(): void
    {
        $this->abilities = ['write_groups'];
        $this->postJson('/_test/mobile/write')->assertForbidden();
        $this->abilities[] = 'mobile.menu.groups';
        $this->postJson('/_test/mobile/write')->assertOk();
    }

    public function test_unconfigured_functions_deny_and_bootstrap_remains_available(): void
    {
        $this->getJson('/_test/mobile/unknown')->assertForbidden();
        $this->getJson('/_test/mobile/bootstrap')->assertOk();
    }

    public function test_role_features_respect_global_disable_and_do_not_mutate_global_models(): void
    {
        $this->abilities = ['mobile.menu.groups', 'mobile.menu.attendance'];
        $global = (object) ['key' => 'attendance', 'name' => 'Attendance', 'status' => 0];
        $features = app(MobilePermissions::class)->features(collect([$global]))->keyBy('key');
        $this->assertSame('0', $features['attendance']->status);
        $this->assertSame('1', $features['groups']->status);
        $this->assertSame('0', $features['payroll-management']->status);
        $this->assertSame(0, $global->status);
    }

    public function test_shared_ability_remains_granted_when_one_of_its_menus_is_denied(): void
    {
        $this->abilities = ['mobile.menu.advance-salary', 'advance_salary_list'];
        $features = app(MobilePermissions::class)->features(collect())->keyBy('key');
        $this->assertSame('1', $features['advance_salary_list']->status);
        $this->assertSame('0', $features['advance-salary-admin']->status);
    }

    public function test_all_mobile_routes_declare_a_function_rule_and_valid_menu(): void
    {
        $rules = config('mobile_permissions.functions');
        foreach (Route::getRoutes() as $route) {
            if (!in_array(MobileFunctionPermission::class, $route->gatherMiddleware(), true)
                || str_starts_with($route->uri(), '_test/')) continue;
            $action = class_basename(explode('@', $route->getActionName())[0]).'@'.$route->getActionMethod();
            $this->assertArrayHasKey($action, $rules, $route->uri());
            if ($rules[$action] !== null) {
                $this->assertArrayHasKey($rules[$action][0], config('mobile_permissions.menus'), $action);
            }
        }
    }
}

class MobilePermissionProbeController
{
    public function read() { return response()->json(['ok' => true]); }
    public function write() { return response()->json(['ok' => true]); }
    public function unknown() { return response()->json(['ok' => true]); }
    public function bootstrap() { return response()->json(['ok' => true]); }
}
