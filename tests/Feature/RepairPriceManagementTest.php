<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\RepairPriceWebController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RepairPriceManagementTest extends TestCase
{
    private array $abilities = ['repair_price.manage_prices'];

    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', ':memory:');
            $app['config']->set('database.connections.sqlite.foreign_key_constraints', true);
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        DB::purge('sqlite');
        (require database_path('migrations/2026_09_20_000001_create_repair_price_tables.php'))->up();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
        });
        DB::table('repair_brands')->insert(['id' => 1, 'name' => 'Apple', 'slug' => 'apple']);
        DB::table('repair_device_types')->insert(['id' => 1, 'name' => 'Phone', 'slug' => 'phone']);
        DB::table('repair_devices')->insert(['id' => 1, 'name' => 'iPhone', 'repair_brand_id' => 1, 'repair_device_type_id' => 1]);
        DB::table('repair_categories')->insert(['id' => 1, 'name' => 'Screen', 'slug' => 'screen']);
        DB::table('repair_services')->insert(['id' => 1, 'name' => 'Replacement', 'repair_category_id' => 1]);
        $this->actingAs((new User())->forceFill(['id' => 99, 'name' => 'Manager']));
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        Route::middleware('web')->group(function () {
            Route::post('/_test/repair-prices', [RepairPriceWebController::class, 'storePrice']);
            Route::put('/_test/repair-prices/{id}', [RepairPriceWebController::class, 'updatePrice']);
            Route::delete('/_test/repair-prices/{id}', [RepairPriceWebController::class, 'deletePrice']);
        });
    }

    private function payload(array $changes = []): array
    {
        return $changes + [
            'repair_device_id' => 1, 'repair_service_id' => 1,
            'part_type' => 'Original', 'part_cost' => 30, 'service_fee' => 10,
            'selling_price' => 60, 'availability_status' => 'available',
        ];
    }

    public function test_manager_can_create_update_with_history_and_delete(): void
    {
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('success');
        $id = DB::table('repair_prices')->value('id');
        $this->assertNotNull($id);
        $this->put('/_test/repair-prices/'.$id, $this->payload(['selling_price' => 75]))->assertSessionHas('success');
        $this->assertDatabaseHas('repair_prices', ['id' => $id, 'selling_price' => 75]);
        $this->assertDatabaseHas('repair_price_histories', ['repair_price_id' => $id, 'old_selling_price' => 60, 'new_selling_price' => 75, 'changed_by' => 99]);
        $this->delete('/_test/repair-prices/'.$id)->assertSessionHas('success');
        $this->assertDatabaseCount('repair_prices', 0);
        $this->assertDatabaseCount('repair_price_histories', 0);
    }

    public function test_individual_permissions_remain_independent(): void
    {
        $this->abilities = ['repair_price.create'];
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('success');
        $id = DB::table('repair_prices')->value('id');
        $this->put('/_test/repair-prices/'.$id, $this->payload())->assertForbidden();
        $this->delete('/_test/repair-prices/'.$id)->assertForbidden();
        $this->abilities = ['repair_price.update'];
        $this->put('/_test/repair-prices/'.$id, $this->payload(['selling_price' => 80]))->assertSessionHas('success');
        $this->abilities = ['repair_price.delete'];
        $this->delete('/_test/repair-prices/'.$id)->assertSessionHas('success');
    }

    public function test_viewers_cannot_change_prices(): void
    {
        $this->abilities = ['repair_price.view'];
        $this->post('/_test/repair-prices', $this->payload())->assertForbidden();
        $this->put('/_test/repair-prices/1', $this->payload())->assertForbidden();
        $this->delete('/_test/repair-prices/1')->assertForbidden();
        $this->assertDatabaseCount('repair_prices', 0);
    }

    public function test_duplicates_and_negative_prices_are_rejected(): void
    {
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('success');
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('danger');
        $this->post('/_test/repair-prices', $this->payload(['selling_price' => -1]))->assertSessionHasErrors('selling_price');
        $this->assertDatabaseCount('repair_prices', 1);
    }

    public function test_delete_routes_require_delete_requests(): void
    {
        foreach (['brands', 'categories', 'devices', 'services', 'prices'] as $entity) {
            $route = Route::getRoutes()->getByName('admin.repair-price.'.$entity.'.delete');
            $this->assertSame(['DELETE'], $route->methods());
        }
    }
}
