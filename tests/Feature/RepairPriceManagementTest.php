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
        (require database_path('migrations/2026_11_11_000001_create_repair_category_device_table.php'))->up();
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
            Route::get('/_test/repair-import', [RepairPriceWebController::class, 'import']);
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

    private function bulkPricePayload(array $changes = []): array
    {
        $data = $this->payload($changes);
        unset($data['repair_device_id']);
        return $data;
    }

    private function addPriceModels(): void
    {
        DB::table('repair_devices')->insert([
            ['id' => 2, 'name' => 'iPhone Pro', 'repair_brand_id' => 1, 'repair_device_type_id' => 1],
            ['id' => 3, 'name' => 'iPhone Max', 'repair_brand_id' => 1, 'repair_device_type_id' => 1],
        ]);
    }

    public function test_complete_price_can_be_added_to_multiple_models_and_then_edited_independently(): void
    {
        $this->addPriceModels();
        $data = $this->bulkPricePayload([
            'repair_device_ids' => [1, 2, 3], 'repair_category_id' => 1,
            'part_name' => 'Screen assembly', 'warranty_days' => 90,
            'estimated_minutes' => 45, 'note' => 'Includes fitting', 'availability_status' => 'pre_order',
        ]);
        $this->post('/_test/repair-prices', $data)->assertSessionHas('success', __('repair.prices_added_to_models', ['count' => 3]));
        foreach ([1, 2, 3] as $deviceId) {
            $this->assertDatabaseHas('repair_prices', [
                'repair_device_id' => $deviceId, 'repair_service_id' => 1,
                'part_name' => 'Screen assembly', 'part_type' => 'Original',
                'part_cost' => 30, 'service_fee' => 10, 'selling_price' => 60,
                'warranty_days' => 90, 'estimated_minutes' => 45, 'note' => 'Includes fitting',
                'availability_status' => 'pre_order', 'created_by' => 99,
            ]);
            $this->assertDatabaseHas('repair_category_device', ['repair_device_id' => $deviceId, 'repair_category_id' => 1]);
        }
        $id = DB::table('repair_prices')->where('repair_device_id', 2)->value('id');
        $this->put('/_test/repair-prices/'.$id, $this->payload(['repair_device_id' => 2, 'selling_price' => 85, 'note' => 'Pro model only']))->assertSessionHas('success');
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 1, 'selling_price' => 60, 'note' => 'Includes fitting']);
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 2, 'selling_price' => 85, 'note' => 'Pro model only']);
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 3, 'selling_price' => 60]);
        $this->assertDatabaseCount('repair_price_histories', 1);
    }

    public function test_batch_saves_different_costs_fees_and_prices_for_each_selected_model(): void
    {
        $this->addPriceModels();
        $data = $this->bulkPricePayload([
            'repair_device_ids' => [1, 2, 3], 'per_model_pricing' => 1,
            'model_prices' => [
                1 => ['part_cost' => 20, 'service_fee' => 5, 'selling_price' => 45],
                2 => ['part_cost' => 35, 'service_fee' => 10, 'selling_price' => 80],
                3 => ['part_cost' => null, 'service_fee' => null, 'selling_price' => 0],
            ], 'note' => 'Model-specific prices', 'warranty_days' => 90,
        ]);
        unset($data['part_cost'], $data['service_fee'], $data['selling_price']);
        $this->post('/_test/repair-prices', $data)->assertSessionHas('success');
        foreach ([1 => [20, 5, 45], 2 => [35, 10, 80], 3 => [0, 0, 0]] as $model => [$cost, $fee, $price]) {
            $this->assertDatabaseHas('repair_prices', ['repair_device_id' => $model, 'part_cost' => $cost,
                'service_fee' => $fee, 'selling_price' => $price, 'note' => 'Model-specific prices', 'warranty_days' => 90]);
        }
        $this->assertDatabaseCount('repair_category_device', 3);
    }

    public function test_per_model_prices_require_every_selected_model_and_reject_invalid_rows_atomically(): void
    {
        $this->addPriceModels();
        $data = $this->bulkPricePayload(['repair_device_ids' => [1, 2], 'per_model_pricing' => 1]);
        unset($data['selling_price']);
        $this->post('/_test/repair-prices', $data)->assertSessionHasErrors('model_prices');
        $data['model_prices'] = [1 => ['selling_price' => 50]];
        $this->post('/_test/repair-prices', $data)->assertSessionHasErrors('model_prices.2');
        $data['model_prices'][2] = ['selling_price' => -10];
        $this->post('/_test/repair-prices', $data)->assertSessionHasErrors('model_prices.2.selling_price');
        $data['model_prices'][2] = ['selling_price' => 70, 'service_fee' => -1];
        $this->post('/_test/repair-prices', $data)->assertSessionHasErrors('model_prices.2.service_fee');
        $data['model_prices'][2] = ['selling_price' => 70];
        $data['model_prices'][3] = ['selling_price' => 90];
        $this->post('/_test/repair-prices', $data)->assertSessionHasErrors('model_prices');
        $this->assertDatabaseCount('repair_prices', 0);
        $this->assertDatabaseCount('repair_category_device', 0);
    }

    private function multiServicePayload(): array
    {
        $this->addPriceModels();
        DB::table('repair_categories')->insert(['id' => 2, 'name' => 'Battery', 'slug' => 'battery']);
        DB::table('repair_services')->insert(['id' => 2, 'name' => 'Battery Replacement', 'repair_category_id' => 2]);
        return [
            'repair_device_ids' => [1, 2], 'repair_service_ids' => [1, 2], 'per_pair_pricing' => 1,
            'part_type' => 'Original', 'availability_status' => 'available', 'warranty_days' => 90,
            'note' => 'Full repair package',
            'pair_prices' => [
                1 => [1 => ['part_cost' => 20, 'service_fee' => 5, 'selling_price' => 50],
                      2 => ['part_cost' => 8, 'service_fee' => 2, 'selling_price' => 25]],
                2 => [1 => ['part_cost' => 35, 'service_fee' => 10, 'selling_price' => 80],
                      2 => ['part_cost' => 15, 'service_fee' => 5, 'selling_price' => 40]],
            ],
        ];
    }

    public function test_multiple_models_categories_and_services_save_independent_complete_prices(): void
    {
        $data = $this->multiServicePayload();
        $this->post('/_test/repair-prices', $data)->assertSessionHas('success', __('repair.prices_added_to_pairs', ['count' => 4, 'models' => 2, 'services' => 2]));
        foreach ($data['pair_prices'] as $model => $prices) {
            foreach ($prices as $service => $amounts) {
                $this->assertDatabaseHas('repair_prices', $amounts + [
                    'repair_device_id' => $model, 'repair_service_id' => $service, 'part_type' => 'Original',
                    'warranty_days' => 90, 'note' => 'Full repair package', 'created_by' => 99,
                ]);
            }
            foreach ([1, 2] as $category) {
                $this->assertDatabaseHas('repair_category_device', ['repair_device_id' => $model, 'repair_category_id' => $category]);
            }
        }
        $this->assertDatabaseCount('repair_prices', 4);
        $this->abilities[] = 'repair_price.view';
        $this->get('/_test/repair-management?active_tab=prices')->assertOk()
            ->assertSee('name="repair_service_ids[]"', false)->assertSee('repair-pair-price-template', false)
            ->assertSee(__('repair.per_pair_prices'))->assertSee('data-pair-category', false);
    }

    public function test_multi_service_missing_invalid_and_unselected_pairs_are_rejected_without_partial_saves(): void
    {
        $data = $this->multiServicePayload();
        $invalid = $data;
        unset($invalid['pair_prices'][2][2]);
        $this->post('/_test/repair-prices', $invalid)->assertSessionHasErrors('pair_prices.2.2');
        $invalid = $data;
        $invalid['pair_prices'][1][2]['selling_price'] = -1;
        $this->post('/_test/repair-prices', $invalid)->assertSessionHasErrors('pair_prices.1.2.selling_price');
        $invalid = $data;
        $invalid['pair_prices'][1][999] = ['selling_price' => 90];
        $this->post('/_test/repair-prices', $invalid)->assertSessionHasErrors('pair_prices');
        $invalid = $data;
        $invalid['pair_prices'][3] = $data['pair_prices'][1];
        $this->post('/_test/repair-prices', $invalid)->assertSessionHasErrors('pair_prices');
        $invalid = $data;
        $invalid['repair_service_ids'] = [1, 1];
        $this->post('/_test/repair-prices', $invalid)->assertSessionHasErrors('repair_service_ids.0');
        $invalid = $data;
        $invalid['repair_service_ids'] = [999];
        $this->post('/_test/repair-prices', $invalid)->assertSessionHasErrors('repair_service_ids.0');
        $this->assertDatabaseCount('repair_prices', 0);
        $this->assertDatabaseCount('repair_category_device', 0);
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('success');
        $this->post('/_test/repair-prices', $data)->assertSessionHas('danger', __('repair.price_models_conflict', ['models' => 'iPhone — Replacement']));
        $this->assertDatabaseCount('repair_prices', 1);
        $this->assertDatabaseCount('repair_category_device', 1);
    }

    public function test_copy_to_multiple_services_only_reuses_private_source_costs_for_its_own_service(): void
    {
        $data = $this->multiServicePayload();
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('success');
        $data['source_price_id'] = DB::table('repair_prices')->value('id');
        $data['repair_device_ids'] = [2];
        unset($data['pair_prices'][1]);
        $this->abilities = ['repair_price.view', 'repair_price.create'];
        $this->post('/_test/repair-prices', $data)->assertSessionHas('success');
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 2, 'repair_service_id' => 1, 'part_cost' => 30, 'service_fee' => 10, 'selling_price' => 80]);
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 2, 'repair_service_id' => 2, 'part_cost' => 15, 'service_fee' => 5, 'selling_price' => 40]);
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 1, 'repair_service_id' => 1, 'selling_price' => 60]);
    }

    public function test_batch_price_conflicts_save_nothing_and_identify_models(): void
    {
        $this->addPriceModels();
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('success');
        $this->post('/_test/repair-prices', $this->bulkPricePayload(['repair_device_ids' => [2, 1, 3], 'selling_price' => 99]))
            ->assertSessionHas('danger', __('repair.price_models_conflict', ['models' => 'iPhone']))
            ->assertSessionHasInput('repair_device_ids', [2, 1, 3]);
        $this->assertDatabaseCount('repair_prices', 1);
        $this->assertDatabaseCount('repair_category_device', 1);
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 1, 'selling_price' => 60]);
        // Null part types must also be protected against duplicates.
        $this->post('/_test/repair-prices', $this->bulkPricePayload(['repair_device_ids' => [1], 'part_type' => null]))->assertSessionHas('success');
        $this->post('/_test/repair-prices', $this->bulkPricePayload(['repair_device_ids' => [1, 2], 'part_type' => null]))->assertSessionHas('danger');
        $this->assertDatabaseCount('repair_prices', 2);
    }

    public function test_bulk_price_models_and_complete_fields_are_validated(): void
    {
        foreach ([[], [1, 1], [999], ['wrong']] as $ids) {
            $this->post('/_test/repair-prices', $this->bulkPricePayload(['repair_device_ids' => $ids]))->assertSessionHasErrors();
        }
        $this->post('/_test/repair-prices', $this->payload(['repair_device_ids' => [1]]))->assertSessionHasErrors('repair_device_id');
        foreach (['part_cost', 'service_fee', 'selling_price', 'warranty_days', 'estimated_minutes'] as $field) {
            $this->post('/_test/repair-prices', $this->bulkPricePayload(['repair_device_ids' => [1], $field => -1]))->assertSessionHasErrors($field);
        }
        $this->assertDatabaseCount('repair_prices', 0);
        $this->abilities = ['repair_price.view'];
        $this->post('/_test/repair-prices', $this->bulkPricePayload(['repair_device_ids' => [1]]))->assertForbidden();
    }

    public function test_matrix_copy_preserves_complete_price_without_exposing_hidden_costs(): void
    {
        $this->addPriceModels();
        $this->post('/_test/repair-prices', $this->payload([
            'part_name' => 'Screen assembly', 'part_cost' => 37.25, 'service_fee' => 12.5,
            'warranty_days' => 180, 'estimated_minutes' => 50, 'availability_status' => 'out_of_stock',
            'note' => 'Full installation package',
        ]))->assertSessionHas('success');
        $sourceId = DB::table('repair_prices')->value('id');
        $this->abilities = ['repair_price.view', 'repair_price.create'];
        $response = $this->get('/_test/repair-management?active_tab=prices')->assertOk()
            ->assertSee(__('repair.copy_price_to_models'))->assertSee('name="repair_device_ids[]"', false);
        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $button = (new \DOMXPath($dom))->query('//*[@data-price-template]')->item(0);
        $this->assertNotNull($button);
        $template = json_decode($button->getAttribute('data-price-template'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayNotHasKey('part_cost', $template);
        $this->assertArrayNotHasKey('service_fee', $template);
        $this->assertSame($sourceId, $template['source_price_id']);
        unset($template['source_device_id'], $template['costs_hidden']);
        $template['repair_device_ids'] = [2, 3];
        $template['per_model_pricing'] = 1;
        $template['model_prices'] = [
            2 => ['selling_price' => 80, 'part_cost' => 999, 'service_fee' => 999],
            3 => ['selling_price' => 95],
        ];
        $this->post('/_test/repair-prices', $template)->assertSessionHas('success');
        foreach ([2, 3] as $deviceId) {
            $this->assertDatabaseHas('repair_prices', [
                'repair_device_id' => $deviceId, 'repair_service_id' => 1, 'part_name' => 'Screen assembly',
                'part_type' => 'Original', 'part_cost' => 37.25, 'service_fee' => 12.5,
                'selling_price' => $deviceId === 2 ? 80 : 95, 'warranty_days' => 180, 'estimated_minutes' => 50,
                'availability_status' => 'out_of_stock', 'note' => 'Full installation package',
            ]);
        }
        $this->assertDatabaseCount('repair_prices', 3);
        $this->abilities = ['repair_price.create'];
        $this->post('/_test/repair-prices', $template)->assertForbidden();
    }

    public function test_same_category_has_independent_prices_for_each_model(): void
    {
        DB::table('repair_devices')->insert(['id' => 2, 'name' => 'iPhone Pro', 'repair_brand_id' => 1, 'repair_device_type_id' => 1]);
        $this->post('/_test/repair-prices', $this->payload(['repair_category_id' => 1, 'selling_price' => 60]))->assertSessionHas('success');
        $this->post('/_test/repair-prices', $this->payload(['repair_category_id' => 1, 'repair_device_id' => 2, 'selling_price' => 95]))->assertSessionHas('success');
        $firstId = DB::table('repair_prices')->where('repair_device_id', 1)->value('id');
        $this->put('/_test/repair-prices/'.$firstId, $this->payload(['repair_category_id' => 1, 'selling_price' => 70]))->assertSessionHas('success');
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 1, 'repair_service_id' => 1, 'selling_price' => 70]);
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 2, 'repair_service_id' => 1, 'selling_price' => 95]);
        $this->assertDatabaseHas('repair_category_device', ['repair_device_id' => 1, 'repair_category_id' => 1]);
        $this->assertDatabaseHas('repair_category_device', ['repair_device_id' => 2, 'repair_category_id' => 1]);
        $this->abilities[] = 'repair_price.view';
        $this->get('/_test/repair-management?active_tab=prices&device_id=2&category_id=1')->assertOk()
            ->assertViewHas('prices', fn ($prices) => $prices->total() === 1 && (float) $prices->first()->selling_price === 95.0)
            ->assertSee('name="repair_category_id"', false)
            ->assertSee(__('repair.model_category_price_help'));
    }

    public function test_price_service_must_belong_to_selected_category(): void
    {
        DB::table('repair_categories')->insert(['id' => 2, 'name' => 'Battery', 'slug' => 'battery']);
        $this->post('/_test/repair-prices', $this->payload(['repair_category_id' => 2]))->assertSessionHasErrors('repair_service_id');
        $this->assertDatabaseCount('repair_prices', 0);
        $this->post('/_test/repair-prices', $this->payload(['repair_category_id' => 1]))->assertSessionHas('success');
        $id = DB::table('repair_prices')->value('id');
        $this->put('/_test/repair-prices/'.$id, $this->payload(['repair_category_id' => 2, 'selling_price' => 99]))->assertSessionHasErrors('repair_service_id');
        $this->assertDatabaseHas('repair_prices', ['id' => $id, 'selling_price' => 60]);
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

    public function test_device_categories_can_be_shared_replaced_and_cleared(): void
    {
        $this->abilities = ['repair_price.manage_devices', 'repair_price.view', 'repair_price.manage_categories'];
        DB::table('repair_categories')->insert(['id' => 2, 'name' => 'Battery', 'slug' => 'battery']);
        $data = ['name' => 'New Model', 'repair_brand_id' => 1, 'repair_device_type_id' => 1, 'category_ids' => [1, 2]];
        $this->post('/_test/repair/devices', $data)->assertSessionHas('success');
        $id = DB::table('repair_devices')->where('name', 'New Model')->value('id');
        $this->assertDatabaseHas('repair_category_device', ['repair_device_id' => $id, 'repair_category_id' => 1]);
        $this->assertDatabaseHas('repair_category_device', ['repair_device_id' => $id, 'repair_category_id' => 2]);
        $this->put('/_test/repair/devices/1', array_replace($data, ['name' => 'iPhone']))->assertSessionHas('success');
        $this->get('/_test/repair-management?active_tab=devices&category_id=2')->assertOk()
            ->assertViewHas('devicesList', fn ($devices) => $devices->total() === 2)
            ->assertSee('name="category_ids[]"', false);
        $this->get('/_test/repair-management?active_tab=categories&device_id=1')->assertOk()
            ->assertViewHas('categoriesList', fn ($categories) => $categories->total() === 2 && $categories->first()->devices_count === 2);
        $data['category_ids'] = [2];
        $this->put('/_test/repair/devices/'.$id, $data)->assertSessionHas('success');
        $this->assertDatabaseMissing('repair_category_device', ['repair_device_id' => $id, 'repair_category_id' => 1]);
        unset($data['category_ids']);
        $this->put('/_test/repair/devices/'.$id, $data)->assertSessionHas('success');
        $this->assertSame(0, DB::table('repair_category_device')->where('repair_device_id', $id)->count());
        $this->assertSame(2, \App\Models\RepairPrice\RepairDevice::find(1)->categories()->count());
        $this->delete('/_test/repair/devices/'.$id)->assertSessionHas('success');
    }

    public function test_device_can_be_added_with_multiple_category_prices(): void
    {
        $this->abilities = ['repair_price.manage_devices', 'repair_price.manage_prices'];
        DB::table('repair_categories')->insert(['id' => 2, 'name' => 'Battery', 'slug' => 'battery']);
        DB::table('repair_services')->insert(['id' => 2, 'name' => 'Battery Replacement', 'repair_category_id' => 2]);
        $data = ['name' => 'Priced Model', 'repair_brand_id' => 1, 'repair_device_type_id' => 1,
            'category_ids' => [1, 2], 'new_prices' => [1 => 65, 2 => 35]];
        $this->post('/_test/repair/devices', $data)->assertSessionHas('success');
        $id = DB::table('repair_devices')->where('name', 'Priced Model')->value('id');
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => $id, 'repair_service_id' => 1, 'selling_price' => 65]);
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => $id, 'repair_service_id' => 2, 'selling_price' => 35]);
        $this->assertDatabaseCount('repair_category_device', 2);
        $data['name'] = 'Other Priced Model';
        $data['new_prices'] = [1 => 85, 2 => null];
        $this->post('/_test/repair/devices', $data)->assertSessionHas('success');
        $this->assertDatabaseCount('repair_prices', 3);
    }

    public function test_edit_device_updates_existing_prices_and_history_without_changing_costs_or_other_models(): void
    {
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('success');
        $priceId = DB::table('repair_prices')->value('id');
        $this->abilities = ['repair_price.manage_devices', 'repair_price.update', 'repair_price.create', 'repair_price.view'];
        $data = ['name' => 'iPhone Updated', 'repair_brand_id' => 1, 'repair_device_type_id' => 1,
            'category_ids' => [1], 'existing_prices' => [$priceId => 90], 'new_prices' => [1 => 75]];
        $this->put('/_test/repair/devices/1', $data)->assertSessionHas('success');
        $this->assertDatabaseHas('repair_prices', ['id' => $priceId, 'selling_price' => 90, 'part_cost' => 30, 'service_fee' => 10, 'part_type' => 'Original']);
        $this->assertDatabaseHas('repair_price_histories', ['repair_price_id' => $priceId, 'old_selling_price' => 60, 'new_selling_price' => 90, 'changed_by' => 99]);
        $this->assertDatabaseHas('repair_prices', ['repair_device_id' => 1, 'part_type' => null, 'selling_price' => 75]);
        $this->get('/_test/repair-management?active_tab=devices')->assertOk()->assertSee('data-prices=', false)
            ->assertSee('repair-category-prices', false)->assertSee(__('repair.inline_prices_help'));
        unset($data['new_prices']);
        $this->put('/_test/repair/devices/1', $data)->assertSessionHas('success');
        $this->assertDatabaseCount('repair_price_histories', 1);
    }

    public function test_device_prices_reject_negative_foreign_and_unselected_entries_without_partial_changes(): void
    {
        $this->abilities = ['repair_price.manage_devices', 'repair_price.manage_prices'];
        $data = ['name' => 'Should Not Save', 'repair_brand_id' => 1, 'repair_device_type_id' => 1, 'category_ids' => [1]];
        $this->post('/_test/repair/devices', $data + ['new_prices' => [1 => -1]])->assertSessionHasErrors('new_prices.1');
        $this->post('/_test/repair/devices', $data + ['new_prices' => [999 => 40]])->assertSessionHasErrors('new_prices.999');
        $this->post('/_test/repair/devices', $data + ['existing_prices' => [1 => 40]])->assertSessionHasErrors('existing_prices.1');
        $this->post('/_test/repair/devices', array_replace($data, ['category_ids' => [], 'new_prices' => [1 => 40]]))->assertSessionHasErrors('new_prices.1');
        $this->assertDatabaseCount('repair_devices', 1);
        $this->assertDatabaseCount('repair_prices', 0);
        $this->post('/_test/repair-prices', $this->payload())->assertSessionHas('success');
        $priceId = DB::table('repair_prices')->value('id');
        $this->post('/_test/repair/devices', $data)->assertSessionHas('success');
        $id = DB::table('repair_devices')->where('name', 'Should Not Save')->value('id');
        $this->put('/_test/repair/devices/'.$id, $data + ['existing_prices' => [$priceId => 99]])->assertSessionHasErrors('existing_prices.'.$priceId);
        $this->assertDatabaseHas('repair_prices', ['id' => $priceId, 'selling_price' => 60]);
    }

    public function test_inline_device_pricing_requires_price_permissions_and_duplicate_rolls_back(): void
    {
        $data = ['name' => 'New Model', 'repair_brand_id' => 1, 'repair_device_type_id' => 1, 'category_ids' => [1]];
        $this->abilities = ['repair_price.manage_devices'];
        $this->post('/_test/repair/devices', $data + ['new_prices' => [1 => 60]])->assertForbidden();
        $this->assertDatabaseCount('repair_devices', 1);
        $this->abilities[] = 'repair_price.create';
        $this->post('/_test/repair/devices', $data + ['new_prices' => [1 => 60]])->assertSessionHas('success');
        $id = DB::table('repair_devices')->where('name', 'New Model')->value('id');
        $priceId = DB::table('repair_prices')->value('id');
        $this->put('/_test/repair/devices/'.$id, $data + ['existing_prices' => [$priceId => 80]])->assertForbidden();
        $data['name'] = 'Must Roll Back';
        $this->put('/_test/repair/devices/'.$id, $data + ['new_prices' => [1 => 90]])->assertSessionHasErrors('new_prices.1');
        $this->assertDatabaseHas('repair_devices', ['id' => $id, 'name' => 'New Model']);
        $this->assertDatabaseHas('repair_prices', ['id' => $priceId, 'selling_price' => 60]);
    }

    public function test_device_repository_preserves_omitted_categories_and_accepts_empty_selection(): void
    {
        $repository = app(\App\Repositories\RepairPrice\RepairPriceRepository::class);
        $device = $repository->storeDevice([
            'name' => 'API Model', 'repair_brand_id' => 1, 'repair_device_type_id' => 1, 'category_ids' => [1],
        ]);
        $this->assertSame([1], $device->categories->modelKeys());
        $device = $repository->updateDevice($device, ['name' => 'Updated API Model']);
        $this->assertSame([1], $device->categories->modelKeys());
        $device = $repository->updateDevice($device, ['category_ids' => []]);
        $this->assertCount(0, $device->categories);
    }

    public function test_invalid_category_assignments_do_not_change_the_device(): void
    {
        $this->abilities = ['repair_price.manage_devices'];
        $data = ['name' => 'Changed', 'repair_brand_id' => 1, 'repair_device_type_id' => 1];
        foreach ([[999], [1, 1], ['invalid']] as $ids) {
            $this->put('/_test/repair/devices/1', $data + ['category_ids' => $ids])->assertSessionHasErrors();
            $this->assertDatabaseHas('repair_devices', ['id' => 1, 'name' => 'iPhone']);
        }
        $this->assertDatabaseCount('repair_category_device', 0);
    }

    public function test_category_migration_preserves_existing_price_links(): void
    {
        $migration = require database_path('migrations/2026_11_11_000001_create_repair_category_device_table.php');
        $migration->down();
        DB::table('repair_prices')->insert([$this->payload(), $this->payload(['part_type' => 'Copy'])]);
        $migration->up();
        $this->assertDatabaseCount('repair_category_device', 1);
        $this->assertDatabaseHas('repair_category_device', ['repair_device_id' => 1, 'repair_category_id' => 1]);
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

    public function test_english_and_khmer_cover_forms_import_validation_and_notifications(): void
    {
        $this->abilities = ['repair_price.view', 'repair_price.manage_prices', 'repair_price.manage_devices',
            'repair_price.manage_services', 'repair_price.manage_brands', 'repair_price.manage_categories'];
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $this->withSession(['locale' => $locale])->get('/_test/repair-management')->assertOk()
                ->assertSee(__('repair.device_types'))->assertSee(__('repair.edit_series'))
                ->assertSee(__('repair.auto_slug'))->assertSee(__('repair.price_device_models'))
                ->assertSee(__('index.export_csv'))->assertDontSee('index.select_device')
                ->assertDontSee('repair.device_types');
            $this->withSession(['locale' => $locale])->get('/_test/repair-import')->assertOk()
                ->assertSee(__('repair.column_guide'))->assertSee(__('repair.import_options'))
                ->assertSee(__('repair.drop_file'))->assertSee(__('repair.import_tip'));
            $this->withSession(['locale' => $locale])->post('/_test/repair-prices', $this->payload(['selling_price' => -1]))
                ->assertSessionHasErrors(['selling_price' => __('validation.min.numeric', ['attribute' => __('repair.attributes.selling_price'), 'min' => 0])]);
            $this->withSession(['locale' => $locale])->post('/_test/repair/types', ['name' => 'Bilingual '.$locale])
                ->assertSessionHas('success', __('repair.type_saved'));
            $this->withSession(['locale' => $locale])->delete('/_test/repair/brands/1')
                ->assertSessionHas('danger', __('repair.brand_used'));
        }
    }
}
