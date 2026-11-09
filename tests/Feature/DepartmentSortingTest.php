<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\DepartmentRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepartmentSortingTest extends TestCase
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
        Schema::create('branches', function (Blueprint $table) {$table->id(); $table->string('name');});
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); $table->string('dept_name'); $table->integer('company_id'); $table->integer('branch_id');
            $table->integer('dept_head_id')->nullable(); $table->string('address'); $table->string('phone')->default('123');
            $table->boolean('is_active');
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name')->default('Employee'); $table->integer('department_id')->nullable();
            $table->boolean('is_active')->default(true); $table->string('status')->default('verified'); $table->softDeletes();
        });
        DB::table('branches')->insert([['id' => 1, 'name' => 'Zebra'], ['id' => 2, 'name' => 'Apple']]);
        DB::table('users')->insert([['id' => 101, 'name' => 'Zoe'], ['id' => 102, 'name' => 'Adam'], ['id' => 103, 'name' => 'Bob']]);
        DB::table('departments')->insert([
            ['id' => 1, 'dept_name' => 'Alpha', 'company_id' => 1, 'branch_id' => 1, 'dept_head_id' => 101, 'address' => 'Zurich', 'is_active' => 0],
            ['id' => 2, 'dept_name' => 'Beta', 'company_id' => 1, 'branch_id' => 2, 'dept_head_id' => 102, 'address' => 'Berlin', 'is_active' => 0],
            ['id' => 3, 'dept_name' => 'Alpha', 'company_id' => 1, 'branch_id' => 2, 'dept_head_id' => 103, 'address' => 'Aachen', 'is_active' => 1],
            ['id' => 4, 'dept_name' => 'Gamma', 'company_id' => 1, 'branch_id' => 1, 'dept_head_id' => 103, 'address' => 'Paris', 'is_active' => 1],
            ['id' => 5, 'dept_name' => 'Delta', 'company_id' => 1, 'branch_id' => 1, 'dept_head_id' => null, 'address' => 'London', 'is_active' => 0],
            ['id' => 6, 'dept_name' => 'Alpha', 'company_id' => 2, 'branch_id' => 2, 'dept_head_id' => null, 'address' => 'Outside', 'is_active' => 1],
        ]);
        foreach ([1, 2, 3, 3, 4, 4, 6, 6, 6, 6] as $departmentId) {
            DB::table('users')->insert(['department_id' => $departmentId]);
        }
        DB::table('users')->insert(['department_id' => 5, 'is_active' => false]);
        DB::table('users')->insert(['department_id' => 5, 'status' => 'pending']);
        DB::table('users')->insert(['department_id' => 5, 'deleted_at' => now()]);
        $this->actingAs((new User())->forceFill(['id' => 1, 'company_id' => 1]));
    }

    private function results(array $parameters = [])
    {
        return app(DepartmentRepository::class)->getAllPaginatedDepartments($parameters + [
            'company_id' => 1, 'per_page' => 'all', 'is_active' => null, 'employee_status' => 'active',
        ]);
    }

    public static function columnOrders(): array
    {
        return [
            ['id', 'asc', ['Beta', 'Alpha', 'Gamma', 'Delta']],
            ['id', 'desc', ['Delta', 'Gamma', 'Alpha', 'Beta']],
            ['name', 'asc', ['Alpha', 'Beta', 'Delta', 'Gamma']],
            ['name', 'desc', ['Gamma', 'Delta', 'Beta', 'Alpha']],
            ['head', 'asc', ['Delta', 'Beta', 'Gamma', 'Alpha']],
            ['head', 'desc', ['Gamma', 'Alpha', 'Beta', 'Delta']],
            ['employees', 'asc', ['Delta', 'Beta', 'Gamma', 'Alpha']],
            ['employees', 'desc', ['Alpha', 'Gamma', 'Delta', 'Beta']],
            ['address', 'asc', ['Beta', 'Delta', 'Gamma', 'Alpha']],
            ['address', 'desc', ['Alpha', 'Gamma', 'Delta', 'Beta']],
            ['branch', 'asc', ['Alpha', 'Beta', 'Delta', 'Gamma']],
            ['branch', 'desc', ['Delta', 'Gamma', 'Alpha', 'Beta']],
            ['status', 'asc', ['Delta', 'Beta', 'Gamma', 'Alpha']],
            ['status', 'desc', ['Gamma', 'Alpha', 'Delta', 'Beta']],
        ];
    }

    /** @dataProvider columnOrders */
    public function test_columns_sort_grouped_rows_in_both_directions(string $column, string $direction, array $expected): void
    {
        $rows = $this->results(['sort_by' => $column, 'sort_direction' => $direction]);
        $this->assertSame($expected, $rows->pluck('dept_name')->all());
        $this->assertSame(4, $rows->total());
        $this->assertSame(3, $rows->firstWhere('dept_name', 'Alpha')->employees_count);
    }

    public function test_sorting_keeps_filters_and_uses_the_displayed_group_totals(): void
    {
        $rows = $this->results(['branch' => ['2'], 'sort_by' => 'employees', 'sort_direction' => 'desc']);
        $this->assertSame(['Alpha', 'Beta'], $rows->pluck('dept_name')->all());
        $this->assertSame(3, $rows->first()->employees_count);
        $this->assertSame(['Alpha'], $this->results(['search' => 'Alpha', 'sort_by' => 'head', 'sort_direction' => 'asc'])->pluck('dept_name')->all());
    }

    public function test_grouped_departments_list_all_assigned_heads_once(): void
    {
        DB::table('departments')->insert([
            'dept_name' => 'Alpha', 'company_id' => 1, 'branch_id' => 1,
            'dept_head_id' => 103, 'address' => 'Office', 'is_active' => 1,
        ]);
        $rows = $this->results();
        $this->assertSame(['Bob', 'Zoe'], $rows->firstWhere('dept_name', 'Alpha')->departmentHeads->pluck('name')->all());
        $this->assertCount(0, $rows->firstWhere('dept_name', 'Delta')->departmentHeads);
    }

    public function test_sorting_happens_before_pagination(): void
    {
        foreach (range(1, 11) as $index) {
            DB::table('departments')->insert(['dept_name' => sprintf('Extra%02d', $index), 'company_id' => 1, 'branch_id' => 1, 'address' => '', 'is_active' => 1]);
        }
        Paginator::currentPageResolver(fn () => 1);
        $first = $this->results(['per_page' => 10, 'sort_by' => 'name', 'sort_direction' => 'asc']);
        Paginator::currentPageResolver(fn () => 2);
        $second = $this->results(['per_page' => 10, 'sort_by' => 'name', 'sort_direction' => 'asc']);
        $this->assertSame(15, $first->total());
        $this->assertSame(['Extra08', 'Extra09', 'Extra10', 'Extra11', 'Gamma'], $second->pluck('dept_name')->all());
        Paginator::currentPageResolver(fn () => 1);
    }

    public function test_unknown_sort_inputs_fall_back_to_the_default_order(): void
    {
        $this->assertSame(['Delta', 'Gamma', 'Alpha', 'Beta'], $this->results([
            'sort_by' => 'name; DROP TABLE departments', 'sort_direction' => 'invalid',
        ])->pluck('dept_name')->all());
    }

    public function test_sort_heading_shows_direction_and_preserves_query_filters(): void
    {
        $this->app['request']->query->add(['branch' => ['2'], 'search' => 'Sales', 'page' => 3]);
        $html = view('admin.department.common.sort-heading', [
            'column' => 'name', 'label' => 'Name', 'filterParameters' => ['sort_by' => 'name', 'sort_direction' => 'asc'],
        ])->render();
        $this->assertStringContainsString('aria-sort="ascending"', $html);
        $this->assertStringContainsString('data-direction="desc"', $html);
        $this->assertStringContainsString('search=Sales', $html);
        $this->assertStringContainsString('branch%5B0%5D=2', $html);
        $this->assertStringNotContainsString('page=', $html);
    }

    public function test_employee_status_controls_totals_and_employee_count_sorting(): void
    {
        $inactive = $this->results(['employee_status' => 'inactive', 'sort_by' => 'employees', 'sort_direction' => 'desc']);
        $this->assertSame('Delta', $inactive->first()->dept_name);
        $this->assertSame(1, $inactive->first()->employees_count);
        $this->assertSame(0, $inactive->firstWhere('dept_name', 'Alpha')->employees_count);
        $all = $this->results(['employee_status' => 'all']);
        $this->assertSame(2, $all->firstWhere('dept_name', 'Delta')->employees_count);
        $this->assertSame(3, $all->firstWhere('dept_name', 'Alpha')->employees_count);
    }

    public function test_default_employee_totals_include_active_and_inactive_without_verification_filter(): void
    {
        $rows = app(DepartmentRepository::class)->getAllPaginatedDepartments(['company_id' => 1, 'per_page' => 'all', 'is_active' => null]);
        $this->assertSame(2, $rows->firstWhere('dept_name', 'Delta')->employees_count);
        $this->assertSame(2, $rows->firstWhere('dept_name', 'Delta')->employees->count());
    }

    public function test_compact_rows_keep_full_branch_details_available_without_employee_personal_data(): void
    {
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        DB::table('branches')->insert(['id' => 3, 'name' => 'Central']);
        DB::table('users')->insert(['id' => 200, 'name' => 'Alice']);
        DB::table('departments')->insert(['dept_name' => 'Alpha', 'company_id' => 1, 'branch_id' => 3, 'dept_head_id' => 200, 'address' => 'Central office', 'is_active' => 1]);
        $fullAddress = str_repeat('Full office address ', 8);
        DB::table('departments')->where('id', 1)->update(['address' => $fullAddress]);
        DB::table('users')->where('department_id', 1)->update(['name' => 'Employee private name']);
        $departments = $this->results();
        $html = view('admin.department.common.directory-row', [
            'value' => $departments->firstWhere('dept_name', 'Alpha'), 'departments' => $departments, 'key' => 0, 'filterParameters' => ['employee_status' => 'all'],
        ])->render();
        $this->assertStringNotContainsString('Employee private name', $html);
        $dom = new \DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?><table>'.$html.'</table>');
        $xpath = new \DOMXPath($dom);
        $this->assertSame(2, $xpath->query('//span[contains(@class,"department-person-name")]')->length);
        $this->assertSame(2, $xpath->query('//span[contains(@class,"department-branch-badge")]')->length);
        $data = json_decode($xpath->query('//tr[@data-department-details]')->item(0)->getAttribute('data-department-details'), true);
        $this->assertCount(3, $data['heads']); $this->assertCount(3, $data['branches']);
        $this->assertSame($fullAddress, $data['entries'][0]['address']);
    }
    public function test_directory_defaults_to_ten_rows_and_exposes_employee_status_controls(): void
    {
        $filters = ['company_id' => 1, 'is_active' => null, 'employee_status' => 'all'];
        $departments = app(DepartmentRepository::class)->getAllPaginatedDepartments($filters);
        $this->assertSame(10, $departments->perPage());
        $html = view('admin.department.common.directory-filters', ['filterParameters' => $filters, 'branch' => \App\Models\Branch::all()])->render();
        $this->assertStringContainsString('id="employee_status"', $html);
        $this->assertStringContainsString('id="is_active"', $html);
        $this->assertStringContainsString('value="all" selected', $html);
        $toolbar = view('admin.department.common.directory-toolbar', ['filterParameters' => $filters, 'departments' => $departments])->render();
        $this->assertStringContainsString('value="10" selected', $toolbar);
        $this->assertStringContainsString('id="departmentReload"', $toolbar);
    }

    private function exportRoute(bool $allowed = true): void
    {
        \Illuminate\Support\Facades\Gate::before(fn () => $allowed);
        \Illuminate\Support\Facades\Route::get('/_test/departments/export', [\App\Http\Controllers\Web\DepartmentController::class, 'export']);
    }
    public function test_export_copies_all_filtered_rows_in_sort_order_even_from_a_later_page(): void
    {
        $this->exportRoute();
        $response = $this->getJson('/_test/departments/export?format=json&page=3&per_page=10&sort_by=name&sort_direction=asc&columns[]=name&columns[]=employees')->assertOk();
        $this->assertSame([__('index.name'), __('index.total_employees')], $response->json('headings'));
        $this->assertSame(['Alpha', 'Beta', 'Delta', 'Gamma'], array_column($response->json('rows'), 0));
        $this->assertSame('2', $response->json('rows.2.1'));
        $filtered = $this->getJson('/_test/departments/export?format=json&search=Alpha&employee_status=inactive&columns[]=name&columns[]=head&columns[]=employees')->assertOk();
        $this->assertSame([['Alpha', "Bob\nZoe", '0']], $filtered->json('rows'));
    }
    public function test_export_respects_the_logged_in_branch(): void
    {
        $this->exportRoute();
        $this->actingAs((new User())->forceFill(['id' => 99, 'company_id' => 1, 'branch_id' => 2]));
        $response = $this->getJson('/_test/departments/export?format=json&branch[]=1&columns[]=name&sort_by=name&sort_direction=asc')->assertOk();
        $this->assertSame([['Alpha'], ['Beta']], $response->json('rows'));
    }
    public function test_export_rejects_unknown_columns_and_requires_list_permission(): void
    {
        $this->exportRoute();
        $this->getJson('/_test/departments/export?format=json&columns[]=password')->assertUnprocessable();
    }
    public function test_export_is_denied_without_department_list_permission(): void
    {
        $this->exportRoute(false);
        $this->getJson('/_test/departments/export?format=json')->assertForbidden();
    }
    public function test_csv_export_has_a_utf8_bom_and_escapes_formula_names(): void
    {
        $this->exportRoute();
        DB::table('departments')->where('id', 2)->update(['dept_name' => '=1+2']);
        $response = $this->get('/_test/departments/export?format=csv&columns[]=name&sort_by=name&sort_direction=asc')->assertOk();
        $file = $response->baseResponse->getFile()->getPathname();
        try {
            $csv = file_get_contents($file);
            $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
            $this->assertStringContainsString("'=1+2", $csv);
        } finally {if (is_file($file)) unlink($file);}
    }
    public function test_excel_export_writes_real_xlsx_cells_as_text(): void
    {
        $this->exportRoute();
        DB::table('departments')->where('id', 2)->update(['dept_name' => '=1+2']);
        $response = $this->get('/_test/departments/export?format=excel&columns[]=name&sort_by=name&sort_direction=asc')->assertOk();
        $file = $response->baseResponse->getFile()->getPathname();
        try {
            $workbook = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $workbook->getActiveSheet();
            $this->assertSame('=1+2', $sheet->getCell('A2')->getValue());
            $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $sheet->getCell('A2')->getDataType());
            $this->assertSame(5, $sheet->getHighestRow());
            $workbook->disconnectWorksheets();
        } finally {if (is_file($file)) unlink($file);}
    }
    public function test_print_and_pdf_views_keep_full_text_and_selected_orientation(): void
    {
        $this->exportRoute();
        $this->get('/_test/departments/export?format=pdf&orientation=portrait&columns[]=name')->assertOk()
            ->assertSee('A4 portrait', false)->assertSee(__('index.department_pdf_help'))->assertSee('Alpha');
        $this->get('/_test/departments/export?format=print&columns[]=name')->assertOk()->assertSee('A4 landscape', false);
    }
}
