<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\DashboardApiController;
use App\Models\User;
use App\Repositories\FeatureRepository;
use App\Services\Authorization\MobileRoleLayout;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MobileRoleLayoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['mobile_permissions.menus' => [
            'attendance' => 'mobile.menu.attendance',
            'holiday' => 'mobile.menu.holiday',
            'notice' => 'mobile.menu.notice',
        ]]);

        Schema::dropIfExists('role_mobile_layouts');
        Schema::create('role_mobile_layouts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('menu_key', 100);
            $table->string('zone', 30);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('features');
        Schema::create('features', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('group')->nullable();
            $table->string('status')->default('1');
        });
        DB::table('features')->insert([
            ['key' => 'attendance', 'name' => 'Attendance', 'group' => 'HR', 'status' => '1'],
            ['key' => 'holiday', 'name' => 'Holiday', 'group' => 'HR', 'status' => '1'],
            ['key' => 'notice', 'name' => 'Notice', 'group' => 'HR', 'status' => '1'],
        ]);
        if (Schema::hasTable('mobile_feature_controls')) {
            DB::table('mobile_feature_controls')->delete();
        }
        Gate::before(fn () => true);
    }

    public function test_role_layout_saves_zone_placement_order_and_hidden_functions(): void
    {
        $layout = app(MobileRoleLayout::class);
        $this->assertTrue($layout->save(
            7,
            ['attendance' => ['bottom', 'top'], 'holiday' => ['bottom'], 'notice' => []],
            ['bottom' => ['holiday', 'attendance'], 'top' => ['attendance']],
        ));

        $this->assertTrue($layout->isConfigured(7));
        $roleLayout = $layout->forRole(7);
        $this->assertEqualsCanonicalizing(['bottom', 'top'], $roleLayout['attendance']['zones']);
        $this->assertEqualsCanonicalizing(['bottom' => 1, 'top' => 0], $roleLayout['attendance']['positions']);
        $this->assertSame(['bottom' => 0], $roleLayout['holiday']['positions']);
        $this->assertSame([], $roleLayout['notice']['zones']);
    }

    public function test_role_layout_rejects_unknown_menu_keys_and_clears_previous_rows(): void
    {
        $layout = app(MobileRoleLayout::class);
        $layout->save(9, ['attendance' => ['sidebar']]);
        $layout->save(9, ['unknown-menu' => ['sidebar']]);

        $this->assertSame(0, DB::table('role_mobile_layouts')->where('role_id', 9)->count());
        $this->assertFalse($layout->isConfigured(9));
    }

    public function test_mobile_layout_api_returns_allowed_zones_and_order(): void
    {
        app(MobileRoleLayout::class)->save(
            7,
            ['attendance' => ['bottom', 'home_quick'], 'holiday' => ['bottom'], 'notice' => []],
            ['bottom' => ['holiday', 'attendance'], 'home_quick' => ['attendance']],
        );

        $controller = (new \ReflectionClass(DashboardApiController::class))
            ->newInstanceWithoutConstructor();
        (new \ReflectionProperty(DashboardApiController::class, 'featureRepository'))
            ->setValue($controller, app(FeatureRepository::class));
        $request = Request::create('/api/mobile-layout', 'GET');
        $user = (new User())->forceFill(['id' => 20, 'role_id' => 7]);
        $request->setUserResolver(fn () => $user);
        $this->actingAs($user, 'api');

        $response = $controller->mobileLayout($request);
        $payload = $response->getData(true);

        $this->assertTrue($payload['configured']);
        $this->assertEqualsCanonicalizing(['bottom', 'home_quick'], $payload['data']['attendance']);
        $this->assertEqualsCanonicalizing(['bottom' => 1, 'home_quick' => 0], $payload['positions']['attendance']);
        $this->assertSame(['bottom' => 0], $payload['positions']['holiday']);
        $this->assertSame([], $payload['data']['notice']);
    }
}
