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
        DB::purge('sqlite');
        app()->setLocale('en');
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id(); $table->string('slug'); $table->integer('status');
        });
        foreach (['branches', 'users', 'asset_types'] as $name) {
            Schema::create($name, function (Blueprint $table) { $table->id(); $table->string('name'); $table->integer('branch_id')->nullable(); });
        }
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id(); $table->integer('branch_id'); $table->integer('asset_id'); $table->integer('user_id');
            $table->date('assigned_date'); $table->date('returned_date')->nullable();
            $table->integer('department_id')->nullable(); $table->string('status')->nullable(); $table->timestamps();
        });
        Schema::create('assets', function (Blueprint $table) {
            $table->id(); $table->integer('branch_id')->nullable(); $table->integer('type_id');
            foreach (['name', 'asset_code', 'asset_serial_no', 'note', 'is_working'] as $key) $table->string($key)->nullable();
            $table->boolean('is_available'); $table->date('purchased_date'); $table->timestamps();
            $table->integer('updated_by')->nullable();
        });
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
