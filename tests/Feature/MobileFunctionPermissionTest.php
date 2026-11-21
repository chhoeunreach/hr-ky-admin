<?php

namespace Tests\Feature;

use App\Http\Middleware\MobileFunctionPermission;
use App\Http\Middleware\Admin;
use App\Http\Middleware\SPAuthGateMW;
use App\Http\Middleware\SuperAdmin;
use App\Models\User;
use App\Services\Authorization\MobilePermissions;
use App\Services\Payroll\PayrollQueueService;
use DomainException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
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

    public function test_legacy_action_permission_implies_its_menu_when_no_explicit_menu_grant_exists(): void
    {
        $this->abilities = ['read_groups'];

        $this->getJson('/_test/mobile/read')->assertOk();
        $this->postJson('/_test/mobile/write')->assertForbidden();
    }

    public function test_revoked_menu_denies_direct_function_request(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('mobile_feature_controls', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->boolean('enabled');
        });
        DB::table('mobile_feature_controls')->insert(['key' => 'groups', 'enabled' => false]);
        $this->abilities = ['write_groups'];
        $this->postJson('/_test/mobile/write')->assertForbidden();
        DB::table('mobile_feature_controls')->where('key', 'groups')->update(['enabled' => true]);
        $this->postJson('/_test/mobile/write')->assertOk();
    }

    public function test_admin_global_switches_control_mobile_api_and_feature_snapshot(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'app_security.enabled' => false]);
        DB::purge('sqlite');
        (require database_path('migrations/2024_05_02_162309_create_features_table.php'))->up();
        (require database_path('migrations/2026_11_20_110000_create_mobile_feature_controls.php'))->up();
        DB::table('features')->insert(['key' => 'groups', 'name' => 'Groups', 'group' => 'Mobile', 'status' => true]);
        $this->withoutMiddleware([Admin::class, SPAuthGateMW::class, SuperAdmin::class]);
        $this->actingAs((new User)->forceFill(['id' => 10]), 'api');
        $this->abilities = ['mobile.menu.groups', 'read_groups', 'write_groups'];

        $this->putJson('/admin/feature/status', ['key' => 'read_groups', 'enabled' => false])->assertForbidden();
        $this->getJson('/_test/mobile/read')->assertOk();

        $this->abilities[] = 'feature_control';
        $this->putJson('/admin/feature/status', ['key' => 'missing', 'enabled' => false])->assertNotFound();
        $this->put('/admin/feature/status', ['key' => 'read_groups', 'enabled' => 0])->assertRedirect();
        $this->getJson('/_test/mobile/read')->assertForbidden();
        $this->postJson('/_test/mobile/write')->assertOk();

        $snapshot = collect($this->getJson('/api/mobile-features')->assertOk()->json('data'))->keyBy('key');
        $this->assertSame('0', $snapshot['read_groups']['status']);
        $this->assertSame('1', $snapshot['write_groups']['status']);

        $this->put('/admin/feature/status', ['key' => 'groups', 'enabled' => 0])->assertRedirect();
        $this->assertDatabaseHas('features', ['key' => 'groups', 'status' => false]);
        $this->postJson('/_test/mobile/write')->assertForbidden();
        $this->getJson('/_test/mobile/bootstrap')->assertOk();

        $this->put('/admin/feature/status', ['key' => 'groups', 'enabled' => 1])->assertRedirect();
        $this->postJson('/_test/mobile/write')->assertOk();
        $this->getJson('/_test/mobile/read')->assertForbidden();
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

    public function test_payroll_qr_join_reaches_the_service_only_with_the_employee_payroll_permission(): void
    {
        config(['app_security.enabled' => false]);
        $this->withoutMiddleware(SPAuthGateMW::class);
        $this->actingAs((new User)->forceFill(['id' => 10]), 'api');
        $this->mock(PayrollQueueService::class, function ($service): void {
            $service->shouldReceive('joinQueue')->once()->with('test-token', 10)
                ->andReturn(['status' => 'already_paid', 'message' => 'Already paid.', 'ticket' => null]);
        });

        $this->abilities = ['mobile.menu.payroll-management'];
        $this->postJson('/api/v1/payroll-queue/join', ['qr_token' => 'test-token'])
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to use this function.');

        $this->abilities[] = 'view_payslip_detail';
        $this->postJson('/api/v1/payroll-queue/join', ['qr_token' => 'test-token', 'employee_id' => 99])
            ->assertOk()
            ->assertJsonPath('status', 'already_paid');
        $this->postJson('/api/v1/hr-queue/queue/call-next', ['counter_number' => 1])->assertForbidden();
        $this->postJson('/api/v1/hr-queue/payroll/disburse', ['ticket_id' => 1])->assertForbidden();
    }

    public function test_web_and_mobile_return_a_readable_error_for_an_ineligible_payroll_scan(): void
    {
        config(['app_security.enabled' => false]);
        $this->withoutMiddleware(SPAuthGateMW::class);
        $employee = (new User)->forceFill(['id' => 10]);
        $this->mock(PayrollQueueService::class, function ($service): void {
            $service->shouldReceive('joinQueue')->twice()->with('closed-token', 10)
                ->andThrow(new DomainException('This session is closed.'));
        });

        $this->actingAs($employee, 'web');
        $this->postJson('/payroll-queue/join/closed-token')->assertUnprocessable()
            ->assertJsonPath('message', 'This session is closed.');

        $this->actingAs($employee, 'api');
        $this->abilities = ['mobile.menu.payroll-management', 'view_payslip_detail'];
        $this->postJson('/api/v1/payroll-queue/join', ['qr_token' => 'closed-token'])->assertUnprocessable()
            ->assertJsonPath('message', 'This session is closed.');
    }
}

class MobilePermissionProbeController
{
    public function read() { return response()->json(['ok' => true]); }
    public function write() { return response()->json(['ok' => true]); }
    public function unknown() { return response()->json(['ok' => true]); }
    public function bootstrap() { return response()->json(['ok' => true]); }
}
