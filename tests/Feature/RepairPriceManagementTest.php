<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\RepairPriceWebController;
use App\Http\Controllers\Web\RepairCatalogWebController;
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
        app('view')->getFinder()->prependLocation(base_path('tests/fixtures/repair-price-views'));
        DB::purge('sqlite');
        (require database_path('migrations/2026_09_20_000001_create_repair_price_tables.php'))->up();
        (require database_path('migrations/2026_09_21_000003_add_unique_to_repair_devices.php'))->up();
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
            Route::get('/_test/repair-management', [RepairPriceWebController::class, 'index']);
            Route::post('/_test/repair-prices', [RepairPriceWebController::class, 'storePrice']);
            Route::put('/_test/repair-prices/{id}', [RepairPriceWebController::class, 'updatePrice']);
            Route::delete('/_test/repair-prices/{id}', [RepairPriceWebController::class, 'deletePrice']);
            foreach (['brands' => 'Brand', 'categories' => 'Category', 'devices' => 'Device', 'services' => 'Service', 'types' => 'Type', 'series' => 'Series'] as $entity => $singular) {
                $controller = in_array($entity, ['types', 'series']) ? RepairCatalogWebController::class : RepairPriceWebController::class;
                Route::post('/_test/repair/'.$entity, [$controller, 'store'.$singular]);
                Route::put('/_test/repair/'.$entity.'/{id}', [$controller, 'update'.$singular]);
                Route::delete('/_test/repair/'.$entity.'/{id}', [$controller, 'delete'.$singular]);
            }
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
        foreach (['brands', 'categories', 'devices', 'services', 'prices', 'types', 'series'] as $entity) {
            $route = Route::getRoutes()->getByName('admin.repair-price.'.$entity.'.delete');
            $this->assertSame(['DELETE'], $route->methods());
        }
    }

    public static function catalogs(): array
    {
        return [
            'brands' => ['brands', 'repair_brands', 'repair_price.manage_brands', ['name' => 'Samsung']],
            'categories' => ['categories', 'repair_categories', 'repair_price.manage_categories', ['name' => 'Battery']],
            'devices' => ['devices', 'repair_devices', 'repair_price.manage_devices', ['name' => 'Galaxy', 'repair_brand_id' => 1, 'repair_device_type_id' => 1]],
            'services' => ['services', 'repair_services', 'repair_price.manage_services', ['name' => 'Battery Replacement', 'repair_category_id' => 1]],
            'types' => ['types', 'repair_device_types', 'repair_price.manage_devices', ['name' => 'Laptop']],
            'series' => ['series', 'repair_device_series', 'repair_price.manage_devices', ['name' => 'Pro', 'repair_brand_id' => 1, 'repair_device_type_id' => 1]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('catalogs')]
    public function test_catalog_add_update_and_delete(string $entity, string $table, string $permission, array $data): void
    {
        $this->abilities = [$permission];
        $this->post('/_test/repair/'.$entity, $data + ['status' => 1])->assertSessionHas('success');
        $id = DB::table($table)->where('name', $data['name'])->value('id');
        $this->assertNotNull($id);
        $data['name'] .= ' Updated';
        $this->put('/_test/repair/'.$entity.'/'.$id, $data)->assertSessionHas('success');
        $this->assertDatabaseHas($table, ['id' => $id, 'name' => $data['name'], 'status' => 0]);
        $this->delete('/_test/repair/'.$entity.'/'.$id)->assertSessionHas('success');
        $this->assertDatabaseMissing($table, ['id' => $id]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('catalogs')]
    public function test_catalog_changes_require_permission(string $entity, string $table, string $permission, array $data): void
    {
        $this->abilities = ['repair_price.view'];
        $this->post('/_test/repair/'.$entity, $data)->assertForbidden();
        $this->put('/_test/repair/'.$entity.'/1', $data)->assertForbidden();
        $this->delete('/_test/repair/'.$entity.'/1')->assertForbidden();
    }

    public function test_linked_records_cannot_be_deleted(): void
    {
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('success');
        $this->abilities = ['repair_price.manage_devices', 'repair_price.manage_services', 'repair_price.manage_brands', 'repair_price.manage_categories'];
        foreach (['devices', 'services', 'brands', 'categories', 'types'] as $entity) {
            $this->delete('/_test/repair/'.$entity.'/1')->assertSessionHas('danger');
        }
        $this->assertDatabaseCount('repair_prices', 1);
    }

    public function test_device_series_must_match_brand_and_type(): void
    {
        $this->abilities = ['repair_price.manage_devices'];
        DB::table('repair_brands')->insert(['id' => 2, 'name' => 'Samsung', 'slug' => 'samsung']);
        DB::table('repair_device_series')->insert(['id' => 1, 'name' => 'Galaxy', 'slug' => 'galaxy', 'repair_brand_id' => 2, 'repair_device_type_id' => 1]);
        $data = ['name' => 'Another iPhone', 'repair_brand_id' => 1, 'repair_device_type_id' => 1, 'repair_device_series_id' => 1];
        $this->post('/_test/repair/devices', $data)->assertSessionHasErrors('repair_device_series_id');
        $this->put('/_test/repair/devices/1', $data)->assertSessionHasErrors('repair_device_series_id');
        $data['repair_brand_id'] = 2;
        $this->post('/_test/repair/devices', $data)->assertSessionHas('success');
        $this->delete('/_test/repair/series/1')->assertSessionHas('danger');
        $this->put('/_test/repair/series/1', ['name' => 'Galaxy', 'repair_brand_id' => 1, 'repair_device_type_id' => 1])->assertSessionHasErrors('repair_brand_id');
    }

    public function test_generated_slug_collisions_are_validation_errors(): void
    {
        $this->abilities = ['repair_price.manage_brands'];
        $this->post('/_test/repair/brands', ['name' => 'APPLE', 'slug' => ''])->assertSessionHasErrors('slug');
        $this->assertDatabaseCount('repair_brands', 1);
    }

    public function test_pagination_keeps_devices_tab_and_filters_and_inactive_references(): void
    {
        $this->abilities = ['repair_price.view', 'repair_price.manage_devices'];
        for ($i = 2; $i <= 26; $i++) {
            DB::table('repair_devices')->insert(['name' => 'Model '.$i, 'repair_brand_id' => 1, 'repair_device_type_id' => 1]);
        }
        DB::table('repair_brands')->where('id', 1)->update(['status' => 0]);
        $response = $this->get('/_test/repair-management?devices_page=2&brand_id=1');
        $response->assertOk()->assertViewHas('filterParameters', fn ($filters) => $filters['active_tab'] === 'devices');
        $response->assertViewHas('brands', fn ($brands) => $brands->contains('id', 1));
        $response->assertViewHas('devicesList', function ($devices) {
            parse_str(parse_url($devices->previousPageUrl(), PHP_URL_QUERY), $query);
            return $devices->currentPage() === 2 && $query['active_tab'] === 'devices' && $query['brand_id'] == 1;
        });
    }

    public function test_management_forms_render_for_managers_and_are_hidden_from_viewers(): void
    {
        $this->abilities = ['repair_price.view', 'repair_price.manage_prices', 'repair_price.manage_devices',
            'repair_price.manage_brands', 'repair_price.manage_categories', 'repair_price.manage_services'];
        $response = $this->get('/_test/repair-management?active_tab=series')->assertOk();
        foreach (['Price', 'Device', 'Service', 'Brand', 'Category', 'Catalogtypes', 'Catalogseries'] as $entity) {
            $response->assertSee('id="add'.$entity.'Modal"', false)->assertSee('id="edit'.$entity.'Modal"', false);
        }
        $this->abilities = ['repair_price.view'];
        $response = $this->get('/_test/repair-management')->assertOk();
        foreach (['Price', 'Device', 'Service', 'Brand', 'Category', 'Catalogtypes', 'Catalogseries'] as $entity) {
            $response->assertDontSee('id="add'.$entity.'Modal"', false)->assertDontSee('id="edit'.$entity.'Modal"', false);
        }
    }

    public function test_toolbar_page_sizes_search_and_all_results_for_exports(): void
    {
        $this->withoutExceptionHandling();
        $this->abilities = ['repair_price.view'];
        for ($i = 1; $i <= 61; $i++) {
            DB::table('repair_prices')->insert([
                'repair_device_id' => 1, 'repair_service_id' => 1,
                'part_name' => 'Screen '.$i, 'part_type' => 'Type '.$i,
                'selling_price' => $i, 'part_cost' => 10,
            ]);
        }
        $this->get('/_test/repair-management')->assertOk()
            ->assertViewHas('prices', fn ($prices) => $prices->perPage() === 25 && $prices->count() === 25 && $prices->total() === 61)
            ->assertSee('id="repairFilterCollapse" class="collapse "', false)
            ->assertSee('data-directory-table="repair-prices-table"', false)
            ->assertSee('data-directory-export="excel"', false)
            ->assertSee('data-directory-export="pdf"', false)
            ->assertDontSee('text-end text-danger">'.__('index.part_cost'), false);
        $this->get('/_test/repair-management?per_page=50')->assertOk()
            ->assertViewHas('prices', fn ($prices) => $prices->count() === 50);
        $this->get('/_test/repair-management?per_page=all&search=Screen&brand_id=1')->assertOk()
            ->assertViewHas('prices', fn ($prices) => $prices->count() === 61 && !$prices->hasMorePages())
            ->assertSee('id="repairFilterCollapse" class="collapse show"', false);
        $this->get('/_test/repair-management?search=Screen+61')->assertOk()
            ->assertViewHas('prices', fn ($prices) => $prices->total() === 1);
        $this->get('/_test/repair-management?per_page=bad')->assertOk()
            ->assertViewHas('filterParameters', fn ($filters) => $filters['per_page'] === '25');
    }

    public function test_each_tab_has_its_own_toolbar_and_search_form(): void
    {
        $this->abilities = ['repair_price.view', 'repair_price.manage_devices', 'repair_price.manage_services',
            'repair_price.manage_brands', 'repair_price.manage_categories'];
        $response = $this->get('/_test/repair-management')->assertOk();
        foreach (['prices', 'brands', 'categories', 'devices', 'services', 'types', 'series'] as $entity) {
            $response->assertSee('id="repair-'.$entity.'-table"', false)
                ->assertSee('id="repair-'.$entity.'-form"', false)
                ->assertSee('id="repair-'.$entity.'-search"', false)
                ->assertSee('id="repair-'.$entity.'-per-page"', false);
        }
        $this->get('/_test/repair-management?active_tab=devices&search=missing')->assertOk()
            ->assertViewHas('devicesList', fn ($records) => $records->total() === 0);
        $this->get('/_test/repair-management?active_tab=services&search=Replacement')->assertOk()
            ->assertViewHas('servicesList', fn ($records) => $records->total() === 1);
        $this->get('/_test/repair-management?active_tab=brands&search=missing')->assertOk()
            ->assertViewHas('brandsList', fn ($records) => $records->total() === 0);
    }
}
