<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\AssetController;
use App\Models\User;
use App\Repositories\AssetRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssetsToolkitTest extends TestCase
{
    private array $abilities = ['list_assets'];

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
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-25 10:00:00'));
        DB::purge('sqlite');
        app()->setLocale('en');
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id(); $table->string('slug'); $table->integer('status');
        });
        foreach (['branches', 'users', 'asset_types'] as $name) {
            Schema::create($name, function (Blueprint $table) { $table->id(); $table->string('name'); $table->integer('branch_id')->nullable(); });
        }
        Schema::table('users', fn (Blueprint $table) => $table->string('avatar')->nullable());
        Schema::table('asset_types', fn (Blueprint $table) => $table->boolean('is_active')->default(1));
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id(); $table->integer('branch_id'); $table->integer('asset_id'); $table->integer('user_id');
            $table->date('assigned_date'); $table->date('returned_date')->nullable();
            $table->integer('department_id')->nullable(); $table->string('status')->nullable(); $table->string('return_condition')->nullable(); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('assets', function (Blueprint $table) {
            $table->id(); $table->integer('branch_id')->nullable(); $table->integer('type_id');
            foreach (['name', 'asset_code', 'asset_serial_no', 'note', 'is_working'] as $key) $table->string($key)->nullable();
            $table->boolean('is_available'); $table->date('purchased_date'); $table->timestamps();
            $table->integer('updated_by')->nullable();
        });
        (require database_path('migrations/2026_10_24_000002_add_phone_numbers_to_assets_table.php'))->up();
        (require database_path('migrations/2026_10_25_000001_create_asset_activities_table.php'))->up();
        (require database_path('migrations/2026_10_25_000002_add_images_to_assets_table.php'))->up();
        (require database_path('migrations/2026_10_25_000006_add_device_setup_to_assets_table.php'))->up();
        DB::table('branches')->insert([['id' => 1, 'name' => 'Other branch'], ['id' => 2, 'name' => 'Current branch']]);
        DB::table('asset_types')->insert(['id' => 1, 'name' => 'Laptop', 'branch_id' => 2]);
        for ($index = 1; $index <= 32; $index++) {
            DB::table('assets')->insert([
                'id' => $index, 'branch_id' => $index === 32 ? 1 : 2, 'type_id' => 1,
                'name' => 'Laptop '.$index, 'asset_code' => $index === 1 ? '000123' : 'ASSET-'.$index,
                'asset_serial_no' => 'SERIAL-'.$index, 'note' => $index === 1 ? '<p>=SUM(A1)</p><p>Office equipment</p>' : '<p>Standard equipment</p>',
                'is_working' => 'yes', 'is_available' => 1, 'purchased_date' => '2026-10-01',
                'created_at' => '2026-10-01 00:00:00', 'updated_at' => '2026-10-01 00:00:00',
            ]);
        }
        $this->actingAs((new User())->forceFill(['id' => 10, 'name' => 'Asset officer', 'branch_id' => 2]));
        \Illuminate\Database\Eloquent\Model::clearBootedModels();
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        Route::get('/_test/assets-toolkit', [AssetController::class, 'index']);
    }

    public function test_search_and_row_count_cover_the_filtered_branch(): void
    {
        $repository = app(AssetRepository::class);
        $page = $repository->getAllAssetsPaginated(['per_page' => '10']);
        $this->assertCount(10, $page);
        $this->assertSame(31, $page->total());
        $this->assertCount(31, $repository->getAllAssetsPaginated(['per_page' => 'all']));
        foreach (['000123', 'SERIAL-1', 'Office equipment'] as $term) {
            $result = $repository->getAllAssetsPaginated(['q' => $term, 'per_page' => 'all']);
            $this->assertTrue($result->contains('id', 1));
            $this->assertFalse($result->contains('id', 32));
        }
    }

    public function test_copy_exports_every_matching_row_and_only_requested_columns(): void
    {
        $response = $this->getJson('/_test/assets-toolkit?'.http_build_query([
            'export' => 'json', 'per_page' => '10', 'branch_id' => 1, 'columns' => ['asset_code', 'asset_serial_no'],
        ]));
        $this->assertSame(200, $response->status(), (string) session('danger'));
        $response->assertJsonCount(31, 'rows');
        $this->assertSame(['Asset Code', 'Asset Serial Number'], $response->json('headings'));
        $this->assertContains(['000123', 'SERIAL-1'], $response->json('rows'));
        $this->assertNotContains(['ASSET-32', 'SERIAL-32'], $response->json('rows'));
        $this->getJson('/_test/assets-toolkit?'.http_build_query(['export' => 'json', 'q' => 'Office equipment', 'columns' => ['description']]))
            ->assertOk()->assertJsonPath('rows.0.0', "=SUM(A1)\nOffice equipment")->assertJsonCount(1, 'rows');
    }

    public function test_export_requires_asset_access_and_rejects_invalid_columns(): void
    {
        $this->getJson('/_test/assets-toolkit?export=json&columns[]=assigned_to')->assertUnprocessable();
        $this->getJson('/_test/assets-toolkit?export=json&columns[]=secret')->assertUnprocessable();
        $this->getJson('/_test/assets-toolkit?per_page=999')->assertUnprocessable();
        $this->abilities = [];
        $this->getJson('/_test/assets-toolkit?export=json')->assertForbidden();
    }

    public function test_excel_keeps_asset_identifiers_as_text_and_csv_escapes_formulas(): void
    {
        $query = http_build_query(['q' => '000123', 'columns' => ['asset_code', 'description']]);
        $response = $this->get('/_test/assets-toolkit?export=excel&'.$query)->assertOk();
        $file = $response->baseResponse->getFile()->getPathname();
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file)->getActiveSheet();
        $this->assertSame('000123', $sheet->getCell('A2')->getValue());
        $this->assertSame('s', $sheet->getCell('A2')->getDataType());
        $this->assertSame('s', $sheet->getCell('B2')->getDataType());
        $csv = $this->get('/_test/assets-toolkit?export=csv&'.$query)->assertOk();
        $content = file_get_contents($csv->baseResponse->getFile()->getPathname());
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString("'=SUM(A1)", $content);
    }

    public function test_toolkit_controls_are_available_in_both_languages(): void
    {
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $this->view('admin.assetManagement.assetDetail.common.toolkit', ['filterParameters' => ['q' => 'Laptop', 'per_page' => '25']])
                ->assertSee(__('index.column_visibility'))->assertSee(__('index.export_excel'))
                ->assertSee(__('index.export_word'))->assertSee(__('index.export_pdf'))
                ->assertSee(__('index.assets_search_placeholder'))->assertSee('value="25" selected', false)
                ->assertDontSee('index.');
        }
    }

    public function test_empty_branch_type_list_offers_active_types_and_preserves_legacy_selection(): void
    {
        Route::get('/_test/asset-types/{branchId}', [AssetController::class, 'getBranchAssetData']);
        DB::table('asset_types')->insert(['id' => 2, 'name' => 'Legacy phone', 'branch_id' => 1, 'is_active' => 0]);
        DB::table('assets')->where('id', 1)->update(['type_id' => 2]);
        $this->getJson('/_test/asset-types/2?asset_id=1')->assertOk()
            ->assertJsonCount(2, 'types')->assertJsonPath('types.1.name', 'Legacy phone');
        $this->getJson('/_test/asset-types/1')->assertOk()
            ->assertJsonCount(1, 'types')->assertJsonPath('types.0.name', 'Laptop');
    }

    public function test_edit_can_save_legacy_type_without_replacing_the_existing_image(): void
    {
        $this->abilities[] = 'edit_assets';
        Schema::table('assets', function (Blueprint $table) {
            $table->string('image')->nullable(); $table->boolean('warranty_available')->default(0);
            $table->date('warranty_end_date')->nullable();
        });
        DB::table('asset_types')->insert([
            ['id' => 2, 'name' => 'Legacy phone', 'branch_id' => 1, 'is_active' => 0],
            ['id' => 3, 'name' => 'Inactive type', 'branch_id' => 2, 'is_active' => 0],
        ]);
        DB::table('assets')->where('id', 1)->update(['type_id' => 2, 'image' => 'existing-phone.jpg']);
        Route::put('/_test/asset-edit/{asset}', [AssetController::class, 'update']);
        $data = ['branch_id' => 2, 'type_id' => 2, 'name' => 'Updated phone', 'purchased_date' => '2026-10-01',
            'warranty_available' => 0, 'is_available' => 1, 'is_working' => 'yes', 'image' => null, 'phone_numbers' => "012345678\n098765432"];
        $this->from('/_test/asset-edit/1')->put('/_test/asset-edit/1', array_replace($data, ['type_id' => 3]))
            ->assertRedirect('/_test/asset-edit/1')->assertSessionHasErrors('type_id');
        $this->putJson('/_test/asset-edit/1', $data)->assertRedirect(route('admin.assets.index'));
        $saved = DB::table('assets')->find(1);
        $this->assertSame('Updated phone', $saved->name);
        $this->assertSame(2, $saved->type_id);
        $this->assertSame('existing-phone.jpg', $saved->image);
        $this->assertSame("012345678\n098765432", $saved->phone_numbers);
        $this->putJson('/_test/asset-edit/1', array_replace($data, ['type_id' => 1]))
            ->assertRedirect(route('admin.assets.index'));
        $this->assertSame(1, DB::table('assets')->find(1)->type_id);
        $this->put('/_test/asset-edit/1', array_replace($data, ['asset_modal' => '1', 'name' => 'Saved from modal', 'type_id' => 1]))
            ->assertOk()->assertSee('asset-action-saved')->assertSessionHas('success', __('message.asset_update'));
        $this->assertSame('Saved from modal', DB::table('assets')->find(1)->name);
    }

    public function test_asset_phone_numbers_save_as_lines_and_export_as_text(): void
    {
        $asset = \App\Models\Asset::findOrFail(1);
        $asset->update(['phone_numbers' => "012345678, 098765432\r\n+855 12 345 678"]);
        $expected = "012345678\n098765432\n+855 12 345 678";
        $this->assertSame($expected, $asset->fresh()->phone_numbers);
        $query = http_build_query(['q' => '000123', 'columns' => ['phone_numbers']]);
        $this->getJson('/_test/assets-toolkit?export=json&'.$query)->assertOk()
            ->assertJsonPath('rows.0.0', $expected)->assertJsonPath('headings.0', __('index.phone_number'));
        $response = $this->get('/_test/assets-toolkit?export=excel&'.$query)->assertOk();
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();
        $this->assertSame($expected, $sheet->getCell('A2')->getValue());
        $this->assertSame('s', $sheet->getCell('A2')->getDataType());
    }

    public function test_sorting_applies_before_pagination_and_exports_and_rejects_unknown_columns(): void
    {
        DB::table('assets')->where('id', 1)->update(['name' => 'AAA phone']);
        DB::table('assets')->where('id', 31)->update(['name' => 'ZZZ phone']);
        $repository = app(AssetRepository::class);
        $ascending = $repository->getAllAssetsPaginated(['sort' => 'name', 'direction' => 'asc', 'per_page' => 10]);
        $this->assertSame(1, $ascending->first()->id);
        $this->assertSame(31, $ascending->total());
        $descending = $repository->getAllAssetsPaginated(['sort' => 'name', 'direction' => 'desc', 'per_page' => 10]);
        $this->assertSame(31, $descending->first()->id);
        $this->getJson('/_test/assets-toolkit?export=json&sort=name&direction=desc&columns[]=name&per_page=10')
            ->assertOk()->assertJsonCount(31, 'rows')->assertJsonPath('rows.0.0', 'ZZZ phone');
        $this->getJson('/_test/assets-toolkit?export=json&sort=receipt')->assertUnprocessable();
        $this->getJson('/_test/assets-toolkit?export=json&sort=name&direction=invalid')->assertUnprocessable();
    }

    public function test_employee_and_type_sorts_use_current_related_values(): void
    {
        $this->setupAssignmentSelection();
        DB::table('users')->where('id', 10)->update(['name' => 'Alpha employee']);
        DB::table('users')->where('id', 11)->update(['name' => 'Zulu employee']);
        DB::table('asset_assignments')->insert([
            ['asset_id' => 1, 'branch_id' => 2, 'user_id' => 11, 'assigned_date' => '2026-10-20', 'returned_date' => null],
            ['asset_id' => 2, 'branch_id' => 2, 'user_id' => 10, 'assigned_date' => '2026-10-20', 'returned_date' => null],
            ['asset_id' => 3, 'branch_id' => 2, 'user_id' => 11, 'assigned_date' => '2026-10-19', 'returned_date' => null],
            ['asset_id' => 3, 'branch_id' => 2, 'user_id' => 10, 'assigned_date' => '2026-10-20', 'returned_date' => '2026-10-21'],
        ]);
        $repository = app(AssetRepository::class);
        $sorted = $repository->getAllAssetsPaginated(['sort' => 'employee', 'direction' => 'desc', 'per_page' => 'all']);
        $this->assertSame([1, 2], $sorted->take(2)->pluck('id')->all());
        $this->getJson('/_test/assets-toolkit?export=json&sort=employee&direction=desc&columns[]=employee')
            ->assertOk()->assertJsonPath('rows.0.0', 'Zulu employee')->assertJsonPath('rows.1.0', 'Alpha employee');
        DB::table('asset_types')->insert(['id' => 2, 'name' => 'Camera', 'branch_id' => 2]);
        DB::table('assets')->where('id', 1)->update(['type_id' => 2]);
        $this->assertSame(1, $repository->getAllAssetsPaginated(['sort' => 'type', 'direction' => 'asc', 'per_page' => 10])->first()->id);
    }

    private function setupAssignmentSelection(): void
    {
        $this->abilities[] = 'assign_asset';
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); $table->string('dept_name'); $table->integer('branch_id'); $table->boolean('is_active');
        });
        Schema::table('users', function (Blueprint $table) {
            foreach (['english_name', 'employee_code', 'username', 'fcm_token'] as $key) $table->string($key)->nullable();
            $table->integer('department_id')->nullable(); $table->boolean('is_active')->default(1); $table->string('status')->default('verified'); $table->softDeletes();
        });
        DB::table('departments')->insert([
            ['id' => 5, 'dept_name' => 'HR', 'branch_id' => 2, 'is_active' => 1],
            ['id' => 6, 'dept_name' => 'IT', 'branch_id' => 2, 'is_active' => 1],
            ['id' => 7, 'dept_name' => 'Other branch', 'branch_id' => 1, 'is_active' => 1],
            ['id' => 8, 'dept_name' => 'Inactive department', 'branch_id' => 2, 'is_active' => 0],
        ]);
        foreach ([10 => [2, 5, 1], 11 => [2, 6, 1], 12 => [2, 5, 0], 13 => [1, 7, 1], 14 => [2, 8, 1]] as $id => [$branch, $department, $active]) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Employee '.$id, 'english_name' => 'Latin '.$id,
                'employee_code' => 'EMP'.$id, 'username' => 'user'.$id, 'branch_id' => $branch,
                'department_id' => $department, 'is_active' => $active, 'status' => 'verified']);
        }
        Route::get('/_test/asset-assignment/{assetId}/options', [\App\Http\Controllers\Web\AssetAssignmentController::class, 'options']);
        Route::post('/_test/asset-assignment/{assetId}', [\App\Http\Controllers\Web\AssetAssignmentController::class, 'store']);
    }

    public function test_assignment_modal_has_valid_configuration_in_both_languages(): void
    {
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $view = $this->view('admin.assetManagement.assetDetail.common.assignment')
                ->assertSee(__('index.asset_assignment_employee_search'))
                ->assertSee(__('index.asset_assignment_all_departments'))->assertDontSee('index.');
            preg_match('/<script type="application\/json" id="assetAssignmentConfig">(.*?)<\/script>/s', (string) $view, $matches);
            $config = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame(__('index.select_employee'), $config['employee']);
            $this->assertSame(now()->toDateString(), $config['today']);
        }
    }

    public function test_assignment_options_include_current_employee_and_full_searchable_identity(): void
    {
        $this->setupAssignmentSelection();
        $response = $this->getJson('/_test/asset-assignment/1/options')->assertOk()
            ->assertJsonCount(2, 'employees')->assertJsonCount(2, 'departments')->assertJsonPath('asset.branch_id', 2);
        $this->assertSame([10, 11], array_column($response->json('employees'), 'id'));
        $this->assertSame('EMP10', $response->json('employees.0.employee_code'));
        $this->assertSame('Latin 10', $response->json('employees.0.english_name'));
        $this->getJson('/_test/asset-assignment/32/options')->assertNotFound();
        $this->abilities = ['list_assets'];
        $this->getJson('/_test/asset-assignment/1/options')->assertForbidden();
    }

    public function test_assignment_validates_employee_department_and_asset_before_saving(): void
    {
        $this->setupAssignmentSelection();
        $data = ['asset_id' => 1, 'branch_id' => 2, 'department_id' => 5, 'user_id' => 10, 'assigned_date' => '2026-10-24'];
        foreach ([['user_id' => 11], ['user_id' => 12], ['user_id' => 13, 'department_id' => 7], ['user_id' => 14, 'department_id' => 8], ['asset_id' => 2], ['branch_id' => 1]] as $invalid) {
            $this->postJson('/_test/asset-assignment/1', array_replace($data, $invalid))->assertUnprocessable();
        }
        $this->assertSame(0, DB::table('asset_assignments')->count());
        $response = $this->postJson('/_test/asset-assignment/1', $data);
        $this->assertSame(200, $response->status(), $response->getContent());
        $saved = DB::table('asset_assignments')->first();
        $this->assertSame(10, $saved->user_id);
        $this->assertSame(5, $saved->department_id);
        $this->assertSame(2, $saved->branch_id);
        $this->assertSame(0, DB::table('assets')->where('id', 1)->value('is_available'));
    }

    public function test_branchless_asset_can_select_a_branch_and_complete_assignment(): void
    {
        $this->setupAssignmentSelection();
        auth()->user()->branch_id = null;
        \Illuminate\Database\Eloquent\Model::clearBootedModels();
        DB::table('assets')->where('id', 1)->update(['branch_id' => null]);
        $this->getJson('/_test/asset-assignment/1/options')->assertOk()
            ->assertJsonPath('asset.branch_id', null)->assertJsonPath('selected_branch_id', null)
            ->assertJsonPath('branch_locked', false)->assertJsonCount(2, 'branches')->assertJsonCount(3, 'employees')
            ->assertJsonPath('employees.0.branch_id', 2);
        $this->getJson('/_test/asset-assignment/1/options?branch_id=2')->assertOk()
            ->assertJsonCount(2, 'employees')->assertJsonCount(2, 'departments');
        $this->getJson('/_test/asset-assignment/1/options?branch_id=999')->assertUnprocessable();
        $data = ['asset_id' => 1, 'department_id' => 5, 'user_id' => 10, 'assigned_date' => '2026-10-24'];
        $this->postJson('/_test/asset-assignment/1', $data)->assertUnprocessable()->assertJsonValidationErrors('branch_id');
        $this->postJson('/_test/asset-assignment/1', $data + ['branch_id' => 2])->assertOk();
        $this->assertSame(2, DB::table('assets')->where('id', 1)->value('branch_id'));
        $this->assertSame(2, DB::table('asset_assignments')->first()->branch_id);
    }

    public function test_employee_export_is_before_name_and_only_shows_current_assignment(): void
    {
        $this->setupAssignmentSelection();
        DB::table('asset_assignments')->insert([
            'asset_id' => 1, 'branch_id' => 2, 'department_id' => 5, 'user_id' => 10,
            'assigned_date' => '2026-10-24', 'returned_date' => null,
        ]);
        $url = '/_test/assets-toolkit?' . http_build_query(['export' => 'json', 'q' => '000123', 'columns' => ['name', 'employee']]);
        $this->getJson($url)->assertOk()->assertJsonPath('headings.0', __('index.employee'))
            ->assertJsonPath('headings.1', __('index.name'))->assertJsonPath('rows.0.0', 'Employee 10');
        DB::table('asset_assignments')->update(['returned_date' => '2026-10-25']);
        $this->getJson($url)->assertOk()->assertJsonPath('rows.0.0', '');
    }

    public function test_edit_page_exposes_assignment_and_renders_the_existing_type(): void
    {
        $this->setupAssignmentSelection();
        $this->abilities[] = 'edit_assets';
        $asset = \App\Models\Asset::with(['type', 'latestAssignment.user'])->findOrFail(1);
        $this->view('admin.assetManagement.assetDetail.common.employee-assignment', ['assetDetail' => $asset])
            ->assertSee(__('index.asset_no_employee'))->assertSee('class="assignAsset btn btn-primary"', false)
            ->assertSee('data-options-url=', false);
        $view = $this->view('admin.assetManagement.assetDetail.common.form', [
            'assetDetail' => $asset, 'assetTypes' => collect([$asset->type]),
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->assertSee('value="1" selected', false)->assertSee('Laptop')->assertSee('name="is_working"', false);
        $this->assertSame(1, substr_count((string) $view, 'id="branch_id"'));
        $request = new \App\Requests\AssetManagement\AssetDetailRequest();
        $request->setMethod('PUT');
        $rules = $request->rules();
        $validator = \Illuminate\Support\Facades\Validator::make([
            'branch_id' => 2, 'type_id' => 1, 'name' => 'Laptop', 'purchased_date' => '2026-10-01',
            'warranty_available' => 0, 'is_available' => 1, 'image' => null,
        ], $rules);
        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
    }

    public function test_asset_update_keeps_the_selected_branch_for_accounts_without_a_default_branch(): void
    {
        auth()->user()->branch_id = null;
        $request = \App\Requests\AssetManagement\AssetDetailRequest::create('/_test/asset-edit', 'PUT', ['branch_id' => 2]);
        $request->prepareForValidation();
        $this->assertSame(2, $request->input('branch_id'));
    }

    public function test_receipt_prints_saved_employee_asset_and_assignment_details_in_both_languages(): void
    {
        $this->setupAssignmentSelection();
        $this->abilities[] = 'print_assets';
        Route::get('/_test/asset-receipt/{assetId}', [\App\Http\Controllers\Web\AssetAssignmentController::class, 'receipt']);
        $this->get('/_test/asset-receipt/1')->assertNotFound();
        DB::table('asset_assignments')->insert([
            'id' => 7, 'asset_id' => 1, 'branch_id' => 2, 'department_id' => 5, 'user_id' => 10,
            'assigned_date' => '2026-10-24', 'returned_date' => null,
        ]);
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $this->get('/_test/asset-receipt/1')->assertOk()->assertSee(__('index.asset_receipt'))
                ->assertSee('AR-000007')->assertSee('Employee 10')->assertSee('EMP10')->assertSee('HR')
                ->assertSee('000123')->assertSee('SERIAL-1')->assertSee('2026-10-24')
                ->assertSee(__('index.asset_received_by'))->assertSee(__('index.asset_issued_by'))->assertDontSee('index.');
        }
        $this->get('/_test/asset-receipt/32')->assertNotFound();
        $this->assertSame(1, DB::table('asset_assignments')->count());
    }

    public function test_push_notification_failure_does_not_report_a_saved_assignment_as_failed(): void
    {
        $this->setupAssignmentSelection();
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('fcm_token'));
        $this->postJson('/_test/asset-assignment/1', [
            'asset_id' => 1, 'branch_id' => 2, 'department_id' => 5, 'user_id' => 10, 'assigned_date' => '2026-10-24',
        ])->assertOk();
        $this->assertSame(1, DB::table('asset_assignments')->count());
        $this->assertSame(0, DB::table('assets')->where('id', 1)->value('is_available'));
    }

    public function test_reassignment_preserves_history_and_rejects_duplicate_or_backdated_changes(): void
    {
        $this->setupAssignmentSelection();
        Route::post('/_test/asset-transfer/{id}', [\App\Http\Controllers\Web\AssetAssignmentController::class, 'updateEmployee']);
        $data = ['asset_id' => 1, 'branch_id' => 2, 'department_id' => 5, 'user_id' => 10, 'assigned_date' => '2026-10-24'];
        $this->postJson('/_test/asset-assignment/1', $data)->assertOk();
        $this->postJson('/_test/asset-assignment/1', $data)->assertUnprocessable();
        $this->postJson('/_test/asset-transfer/1', $data)->assertUnprocessable();
        $change = array_replace($data, ['department_id' => 6, 'user_id' => 11]);
        $this->postJson('/_test/asset-transfer/1', array_replace($change, ['assigned_date' => '2026-10-23']))->assertUnprocessable();
        $this->assertSame(0, DB::transactionLevel());
        $this->assertSame(1, DB::table('asset_assignments')->count());
        $this->postJson('/_test/asset-transfer/1', $change)->assertOk();
        $this->assertSame(2, DB::table('asset_assignments')->count());
        $this->assertSame(11, DB::table('asset_assignments')->whereNull('returned_date')->value('user_id'));
        $this->assertSame('2026-10-24', DB::table('asset_assignments')->where('user_id', 10)->value('returned_date'));
        $this->assertSame(0, DB::table('assets')->where('id', 1)->value('is_available'));
        $events = \App\Models\AssetActivity::orderBy('id')->get();
        $this->assertSame(2, $events->where('event', 'assignment_created')->count());
        $returned = $events->firstWhere('event', 'assignment_updated');
        $this->assertSame('Employee 10', $returned->after['employee_name']);
        $this->assertSame('Asset officer', $returned->actor_name);
        $this->assertNull($returned->before['returned_date']);
        $this->assertSame('2026-10-24', $returned->after['returned_date']);
        $this->abilities = ['list_assets'];
        $this->postJson('/_test/asset-transfer/1', $data)->assertForbidden();
    }

    public function test_activity_keeps_asset_changes_and_deleted_snapshots_with_branch_permissions(): void
    {
        $this->abilities[] = 'asset_assign_list';
        Route::get('/_test/asset-activity', function (Request $request) {
            $view = app(AssetController::class)->activity($request);
            return view('admin.assetManagement.assetDetail.common.activity', $view->getData());
        });
        $asset = \App\Models\Asset::findOrFail(1);
        $asset->update(['name' => 'Renamed laptop']);
        $activity = \App\Models\AssetActivity::firstOrFail();
        $this->assertSame('Laptop 1', $activity->before['name']);
        $this->assertSame('Renamed laptop', $activity->after['name']);
        $asset->delete();
        $this->assertSame(2, \App\Models\AssetActivity::count());
        $this->get('/_test/asset-activity')->assertOk()->assertSee('Renamed laptop')->assertSee('Asset officer');
        \App\Models\AssetActivity::create(['asset_id' => 32, 'branch_id' => 1, 'asset_name' => 'Private other branch', 'event' => 'asset_deleted']);
        $this->get('/_test/asset-activity')->assertOk()->assertDontSee('Private other branch');
        $this->get('/_test/asset-activity?asset_id=32')->assertOk()->assertDontSee('Private other branch');
        $this->abilities = ['list_assets'];
        $this->getJson('/_test/asset-activity')->assertForbidden();
    }

    public function test_returns_are_authorized_and_cannot_modify_an_old_assignment(): void
    {
        $this->setupAssignmentSelection();
        Route::post('/_test/asset-return/{id}', [\App\Http\Controllers\Web\AssetAssignmentController::class, 'storeReturn']);
        $this->postJson('/_test/asset-assignment/1', ['asset_id' => 1, 'branch_id' => 2, 'department_id' => 5, 'user_id' => 10, 'assigned_date' => '2026-10-24'])->assertOk();
        $id = DB::table('asset_assignments')->value('id');
        $this->abilities = ['list_assets'];
        $this->postJson('/_test/asset-return/'.$id, ['is_working' => 'yes'])->assertForbidden();
        $this->abilities[] = 'assign_asset';
        $this->post('/_test/asset-return/'.$id, ['is_working' => 'yes'])->assertRedirect(route('admin.assets.index'));
        $count = \App\Models\AssetActivity::count();
        $this->post('/_test/asset-return/'.$id, ['is_working' => 'no'])->assertSessionHas('danger');
        $this->assertSame($count, \App\Models\AssetActivity::count());
        $this->assertSame('yes', DB::table('assets')->where('id', 1)->value('is_working'));
        $this->assertSame(1, DB::table('assets')->where('id', 1)->value('is_available'));
    }

    public function test_multiple_images_can_be_added_retained_and_removed(): void
    {
        $this->abilities[] = 'create_assets';
        $this->abilities[] = 'edit_assets';
        Schema::table('assets', function (Blueprint $table) {
            $table->string('image')->nullable(); $table->boolean('warranty_available')->default(0);
            $table->date('warranty_end_date')->nullable(); $table->integer('created_by')->nullable();
        });
        $temp = sys_get_temp_dir().'/asset-images-'.uniqid();
        app()->usePublicPath($temp);
        Route::post('/_test/asset-create', [AssetController::class, 'store']);
        Route::put('/_test/asset-edit/{asset}', [AssetController::class, 'update']);
        $data = ['branch_id' => 2, 'type_id' => 1, 'name' => 'Photo laptop', 'purchased_date' => '2026-10-01',
            'warranty_available' => 0, 'is_available' => 1, 'is_working' => 'yes'];
        try {
            $this->post('/_test/asset-create', $data + ['images' => [
                \Illuminate\Http\UploadedFile::fake()->image('front.jpg', 1800, 1200),
                \Illuminate\Http\UploadedFile::fake()->image('back.png'),
            ]])->assertRedirect(route('admin.assets.index'))->assertSessionHasNoErrors();
            $asset = \App\Models\Asset::where('name', 'Photo laptop')->firstOrFail();
            $original = $asset->imageFiles();
            $this->assertCount(2, $original);
            $dimensions = getimagesize(public_path(\App\Models\Asset::UPLOAD_PATH.$original[0]));
            $this->assertSame([1800, 1200], array_slice($dimensions, 0, 2));
            foreach ($original as $file) $this->assertFileExists(public_path(\App\Models\Asset::UPLOAD_PATH.$file));
            $this->put('/_test/asset-edit/'.$asset->id, $data + ['images' => [\Illuminate\Http\UploadedFile::fake()->image('side.webp')]])
                ->assertRedirect(route('admin.assets.index'))->assertSessionHasNoErrors();
            $this->assertCount(3, $asset->fresh()->imageFiles());
            $this->put('/_test/asset-edit/'.$asset->id, $data + ['remove_images' => [$original[0]]])
                ->assertRedirect(route('admin.assets.index'))->assertSessionHasNoErrors();
            $saved = $asset->fresh();
            $this->assertCount(2, $saved->imageFiles());
            $this->assertSame($original[1], $saved->image);
            $this->assertFileDoesNotExist(public_path(\App\Models\Asset::UPLOAD_PATH.$original[0]));
            $this->put('/_test/asset-edit/'.$asset->id, $data + ['remove_images' => ['../other.jpg']])->assertSessionHasErrors('remove_images.0');
            $this->post('/_test/asset-create', $data + ['images' => [\Illuminate\Http\UploadedFile::fake()->create('bad.txt', 1, 'text/plain')]])->assertSessionHasErrors('images.0');
            $this->post('/_test/asset-create', $data + ['images' => array_fill(0, 11, \Illuminate\Http\UploadedFile::fake()->image('many.jpg'))])->assertSessionHasErrors('images');
            $activity = \App\Models\AssetActivity::where('asset_id', $asset->id)->where('event', 'asset_created')->firstOrFail();
            $this->assertCount(2, json_decode($activity->after['images'], true));
        } finally {
            \Illuminate\Support\Facades\File::deleteDirectory($temp);
        }
    }

    public function test_asset_details_offer_gallery_and_full_image_controls_in_both_languages(): void
    {
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $this->view('admin.assetManagement.assetDetail.show')
                ->assertSee(__('index.asset_overview'))->assertSee(__('index.asset_image_gallery'))
                ->assertSee(__('index.asset_image_original'))->assertSee(__('index.asset_image_download'))
                ->assertSee(__('index.asset_image_zoom'))->assertSee(__('index.asset_image_previous'))
                ->assertSee('id="assetImageViewer"', false)->assertSee('data-asset-gallery', false)
                ->assertDontSee('index.');
        }
    }

    public function test_action_dropdown_follows_individual_permissions_and_precedes_employee(): void
    {
        $asset = \App\Models\Asset::with('type')->findOrFail(1);
        $assignment = (new \App\Models\AssetAssignment())->forceFill(['user_id' => 10]);
        $assignment->setRelation('user', auth()->user());
        $asset->setRelation('latestAssignment', $assignment);
        $labels = ['print_assets' => __('index.print'), 'show_asset' => __('index.view'),
            'edit_assets' => __('index.edit'), 'delete_assets' => __('index.delete')];
        foreach ($labels as $permission => $label) {
            $this->abilities = ['list_assets', $permission];
            $html = (string) $this->view('admin.assetManagement.assetDetail.common.actions', ['value' => $asset]);
            $this->assertStringContainsString($label, $html);
            foreach ($labels as $otherPermission => $otherLabel) {
                if ($otherPermission !== $permission) $this->assertStringNotContainsString('> '.$otherLabel, $html);
            }
        }
        $this->abilities = ['list_assets', ...array_keys($labels)];
        $html = (string) $this->view('admin.assetManagement.assetDetail.common.table', ['assetLists' => collect([$asset])]);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($dom);
        $headings = $xpath->query('//thead/tr/th');
        $this->assertSame('actions', $headings->item(0)->getAttribute('data-asset-column'));
        $this->assertSame('employee', $headings->item(1)->getAttribute('data-asset-column'));
        $this->assertSame($headings->length, $xpath->query('//tbody/tr[1]/td')->length);
        $this->assertSame(1, $xpath->query('//tbody/tr[1]/td[1]//button[@data-asset-actions]')->length);
        $this->abilities = ['list_assets'];
        $html = (string) $this->view('admin.assetManagement.assetDetail.common.table', ['assetLists' => collect([$asset])]);
        $this->assertStringNotContainsString('data-asset-actions', $html);
        $this->assertStringNotContainsString('data-asset-column="actions"', $html);
    }

    public function test_receipt_requires_print_permission_even_when_user_can_list_or_view_assets(): void
    {
        Route::get('/_test/print-permission/{assetId}', [\App\Http\Controllers\Web\AssetAssignmentController::class, 'receipt']);
        $this->abilities = ['list_assets', 'show_asset'];
        $this->getJson('/_test/print-permission/1')->assertForbidden();
        $this->abilities[] = 'print_assets';
        $this->getJson('/_test/print-permission/1')->assertNotFound();
    }

    public function test_print_permission_migration_adds_role_option_without_granting_existing_roles(): void
    {
        Schema::create('permission_groups', function (Blueprint $table) { $table->id(); $table->string('name'); });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('permission_key')->unique(); $table->integer('permission_groups_id')->nullable();
        });
        Schema::create('permission_roles', function (Blueprint $table) { $table->integer('permission_id'); $table->integer('role_id'); });
        DB::table('permission_groups')->insert(['id' => 26, 'name' => 'Asset Management']);
        DB::table('permissions')->insert(['name' => 'List Assets', 'permission_key' => 'list_assets', 'permission_groups_id' => 26]);
        $migration = require database_path('migrations/2026_10_25_000003_add_print_assets_permission.php');
        $migration->up(); $migration->up();
        $permission = DB::table('permissions')->where('permission_key', 'print_assets')->first();
        $this->assertSame('Print Assets', $permission->name);
        $this->assertSame(26, $permission->permission_groups_id);
        $this->assertSame(1, DB::table('permissions')->where('permission_key', 'print_assets')->count());
        $this->assertSame(0, DB::table('permission_roles')->count());
        DB::table('permission_roles')->insert(['permission_id' => $permission->id, 'role_id' => 1]);
        $migration->down();
        $this->assertSame(0, DB::table('permission_roles')->count());
        $this->assertSame(0, DB::table('permissions')->where('permission_key', 'print_assets')->count());
    }

    public function test_device_setup_credentials_are_encrypted_preserved_and_masked_in_exports_and_history(): void
    {
        $this->setupAssignmentSelection();
        $this->abilities[] = 'edit_assets';
        Schema::table('assets', function (Blueprint $table) {
            $table->string('image')->nullable(); $table->boolean('warranty_available')->default(0); $table->date('warranty_end_date')->nullable();
        });
        Route::put('/_test/device-setup/{asset}', [AssetController::class, 'update']);
        $data = ['branch_id' => 2, 'type_id' => 1, 'name' => 'Device setup', 'purchased_date' => '2026-10-01',
            'warranty_available' => 0, 'is_available' => 1, 'is_working' => 'yes', 'icloud' => 'device@example.test',
            'asset_password' => 'Example-device-password!', 'screen_time_passcode' => '0012', 'set_by' => 11];
        $this->put('/_test/device-setup/1', $data)->assertSessionHasNoErrors()->assertRedirect(route('admin.assets.index'));
        $asset = \App\Models\Asset::findOrFail(1);
        $this->assertSame('Example-device-password!', $asset->asset_password);
        $this->assertSame('0012', $asset->screen_time_passcode);
        $this->assertNotSame('0012', $asset->getRawOriginal('screen_time_passcode'));
        $this->assertNotSame('Example-device-password!', $asset->getRawOriginal('asset_password'));
        $this->assertSame('Employee 11', $asset->setBy->name);
        $this->assertArrayNotHasKey('asset_password', $asset->toArray());
        $this->assertArrayNotHasKey('screen_time_passcode', $asset->toArray());
        $activity = \App\Models\AssetActivity::firstOrFail();
        $this->assertSame('[redacted]', $activity->after['asset_password']);
        $this->assertSame('[redacted]', $activity->after['screen_time_passcode']);
        $query = http_build_query(['export' => 'json', 'q' => 'device@example.test', 'columns' => ['icloud', 'asset_password', 'screen_time_passcode', 'set_by']]);
        $this->getJson('/_test/assets-toolkit?'.$query)->assertOk()->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0', ['device@example.test', '••••••••', '••••', 'Employee 11'])
            ->assertDontSee('Example-device-password!')->assertDontSee('0012');
        $this->put('/_test/device-setup/1', array_replace($data, ['asset_password' => '', 'screen_time_passcode' => '']))->assertSessionHasNoErrors();
        $this->assertSame('Example-device-password!', $asset->fresh()->asset_password);
        $this->assertSame('0012', $asset->fresh()->screen_time_passcode);
        $this->view('admin.assetManagement.assetDetail.common.device-form', ['assetDetail' => $asset, 'setByOptions' => \App\Models\Asset::setupPeople($asset), 'errors' => new \Illuminate\Support\ViewErrorBag()])
            ->assertSee('Employee 11')->assertSee('name="screen_time_passcode"', false)->assertDontSee('Example-device-password!');
        $this->put('/_test/device-setup/1', array_replace($data, ['asset_password' => '', 'screen_time_passcode' => '', 'clear_asset_password' => 1, 'clear_screen_time_passcode' => 1]))->assertSessionHasNoErrors();
        $this->assertNull($asset->fresh()->asset_password);
        $this->assertNull($asset->fresh()->screen_time_passcode);
        $this->put('/_test/device-setup/1', array_replace($data, ['set_by' => 12]))->assertSessionHasErrors('set_by');
    }

    public function test_description_preview_limits_unicode_text_to_16_characters_and_keeps_full_text(): void
    {
        $short = str_repeat('ក', 16);
        $this->view('admin.assetManagement.assetDetail.common.description', ['note' => $short])->assertSee($short)->assertDontSee('<details', false);
        $long = $short.'EXTRA';
        $this->view('admin.assetManagement.assetDetail.common.description', ['note' => '<p>'.$long.'</p>'])
            ->assertSee('class="asset-description-excerpt">'.$short.'…</span>', false)
            ->assertSee('class="asset-description-full">'.$long.'</div>', false);
    }

    public function test_word_and_pdf_preserve_visible_columns_and_translated_headers(): void
    {
        app()->setLocale('km');
        $query = http_build_query(['q' => '000123', 'columns' => ['asset_code', 'asset_serial_no']]);
        $this->get('/_test/assets-toolkit?export=word&'.$query)->assertOk()
            ->assertHeader('Content-Type', 'application/msword; charset=UTF-8')
            ->assertSee(__('index.asset_code'))->assertSee('000123')->assertDontSee('Office equipment');
        $this->get('/_test/assets-toolkit?export=pdf&orientation=portrait&'.$query)->assertOk()
            ->assertSee('A4 portrait', false)->assertSee(__('index.assets_save_pdf_help'));
    }
}
