<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\EmployeeProfileController;
use App\Http\Controllers\Web\EmployeeSalaryController;
use App\Http\Controllers\Web\SalaryHistoryController;
use App\Models\EmployeeProfile;
use App\Models\EmployeeProfileAuditLog;
use App\Models\EmployeeSalary;
use App\Models\EmployeeSalaryHistory;
use App\Models\SalaryReviseHistory;
use App\Models\User;
use App\Services\Payroll\EmployeeSalaryManagementService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmployeeSalaryManagementTest extends TestCase
{
    private array $abilities = ['salary_increment', 'employee.salary.history.manage'];

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
        $this->travelTo(now()->setDate(2026, 10, 25)->setTime(12, 0));
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('branch_id')->nullable();
            $table->boolean('is_active')->default(true); $table->string('status')->default('verified');
            $table->softDeletes(); $table->timestamps();
        });
        DB::table('users')->insert([['id' => 1, 'name' => 'Salary manager'], ['id' => 20, 'name' => 'Employee'], ['id' => 21, 'name' => 'Other employee']]);
        $this->actingAs(User::findOrFail(1));
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            foreach ((new EmployeeSalary)->getFillable() as $field) $table->string($field)->nullable();
            $table->timestamps();
        });
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id(); $table->integer('employee_id')->unique();
            $table->decimal('current_base_salary', 15, 2)->nullable(); $table->decimal('allowances', 15, 2)->nullable();
            $table->integer('created_by')->nullable(); $table->integer('updated_by')->nullable(); $table->timestamps();
        });
        Schema::create('employee_salary_history', function (Blueprint $table) {
            $table->id();
            foreach ((new EmployeeSalaryHistory)->getFillable() as $field) $table->string($field)->nullable();
            $table->timestamps();
        });
        Schema::create('salary_revise_histories', function (Blueprint $table) {
            $table->id();
            foreach ((new SalaryReviseHistory)->getFillable() as $field) $table->string($field)->nullable();
            $table->softDeletes();
        });
        Schema::create('employee_profile_audit_logs', function (Blueprint $table) {
            $table->id();
            foreach ((new EmployeeProfileAuditLog)->getFillable() as $field) $table->text($field)->nullable();
        });
        Route::post('/_test/salary-increase', [SalaryHistoryController::class, 'store']);
        Route::post('/_test/profile-salary', fn (Request $request) => app(EmployeeProfileController::class)->storeSalary($request, User::findOrFail(20)));
        Route::patch('/_test/salary-status/{employee}/{salary}', function (Request $request, $employee, $salary) {
            return app(EmployeeProfileController::class)->updateSalaryStatus($request, User::findOrFail($employee), EmployeeSalaryHistory::findOrFail($salary));
        });
        Route::put('/_test/salary-history/{employee}/{salary}', function (Request $request, $employee, $salary) {
            return app(EmployeeProfileController::class)->updateSalaryHistory($request, User::findOrFail($employee), EmployeeSalaryHistory::findOrFail($salary));
        });
        Route::get('/_test/salary-history/{employee}/{salary}/edit', function ($employee, $salary) {
            return app(EmployeeProfileController::class)->editSalaryHistory(User::findOrFail($employee), EmployeeSalaryHistory::findOrFail($salary));
        });
        Route::delete('/_test/salary-history/{employee}/{salary}', function (Request $request, $employee, $salary) {
            return app(EmployeeProfileController::class)->deleteSalaryHistory($request, User::findOrFail($employee), EmployeeSalaryHistory::findOrFail($salary));
        });
        Route::get('/_test/salary-setup/{employee}', fn ($employee) => app(EmployeeSalaryController::class)->createSalary($employee));
        Route::get('/_test/salary-edit/{employee}', fn ($employee) => app(EmployeeSalaryController::class)->editSalary($employee));
        Route::post('/_test/salary-setup/{employee}', fn (Request $request, $employee) => app(EmployeeSalaryController::class)->saveSalary($request, $employee));
        Route::put('/_test/salary-edit/{employee}', fn (Request $request, $employee) => app(EmployeeSalaryController::class)->updateSalary($request, $employee));
        Route::delete('/_test/salary-setup/{employee}', fn ($employee) => app(EmployeeSalaryController::class)->deleteSalary($employee));
        Route::patch('/_test/salary-cycle/{employee}', fn ($employee) => app(EmployeeSalaryController::class)->changeSalaryCycle($employee, 'monthly'));
        Route::get('/_test/salary-directory', [EmployeeSalaryController::class, 'index']);
        Route::get('/_test/revision-history/{employeeId}', [SalaryHistoryController::class, 'getEmployeeAllSalaryHistory']);
        Route::get('/_test/revision-history/{employeeId}/{history}/edit', fn ($employeeId, $history) => app(SalaryHistoryController::class)->edit($employeeId, SalaryReviseHistory::findOrFail($history)));
        Route::get('/_test/revision-history/{employeeId}/{history}/delete-confirmation', fn ($employeeId, $history) => app(SalaryHistoryController::class)->confirmDelete($employeeId, SalaryReviseHistory::findOrFail($history)));
        Route::put('/_test/revision-history/{employeeId}/{history}', fn (Request $request, $employeeId, $history) => app(SalaryHistoryController::class)->update($request, $employeeId, SalaryReviseHistory::findOrFail($history)));
        Route::delete('/_test/revision-history/{employeeId}/{history}', fn (Request $request, $employeeId, $history) => app(SalaryHistoryController::class)->destroy($request, $employeeId, SalaryReviseHistory::findOrFail($history)));
    }

    private function salary(array $overrides = []): EmployeeSalary
    {
        return EmployeeSalary::create(array_replace([
            'employee_id' => 20, 'payroll_type' => 'annual', 'payment_type' => 'monthly',
            'annual_salary' => 12000, 'basic_salary_type' => 'percent', 'basic_salary_value' => 80,
            'annual_basic_salary' => 9600, 'monthly_basic_salary' => 800, 'weekly_basic_salary' => 184.62,
            'annual_fixed_allowance' => 2400, 'monthly_fixed_allowance' => 200, 'weekly_fixed_allowance' => 46.15,
            'hour_rate' => null, 'weekly_hours' => null, 'monthly_hours' => null,
        ], $overrides));
    }

    private function increase(array $overrides = []): array
    {
        return array_replace(['employee_id' => 20, 'current_salary' => 12000, 'increase_type' => 'amount',
            'increase_period' => 'period', 'increment_amount' => 100, 'date_from' => '2026-10-25', 'remark' => 'Annual review'], $overrides);
    }

    private function requestRecord(array $overrides = []): EmployeeSalaryHistory
    {
        return EmployeeSalaryHistory::create(array_replace([
            'employee_id' => 20, 'effective_date' => '2026-10-25', 'old_base_salary' => 800,
            'new_base_salary' => 900, 'allowance_before' => 200, 'allowance_after' => 200,
            'approval_status' => 'pending', 'requested_by' => 1, 'reason' => 'Performance review',
        ], $overrides));
    }

    private function revisionRecord(): SalaryReviseHistory
    {
        \Illuminate\Support\Facades\Cache::put('payment_currency_symbol', '$');
        if (!Schema::hasTable('app_settings')) Schema::create('app_settings', function (Blueprint $table) {
            $table->id(); $table->string('slug'); $table->boolean('status');
        });
        return SalaryReviseHistory::create([
            'employee_id' => 20, 'salary_revised_on' => '2026-10-25', 'date_from' => '2026-10-25',
            'base_salary' => 12000, 'revised_salary' => 13200, 'increment_amount' => 1200,
            'increment_percent' => 10, 'remark' => 'Original reason',
        ]);
    }

    public function test_revision_history_modal_returns_data_and_permission_controlled_actions(): void
    {
        $record = $this->revisionRecord();
        $this->abilities = ['show_salary_history'];
        $this->get('/_test/revision-history/20?salary_history_modal=1', ['Accept' => 'application/json'])
            ->assertOk()->assertSee('data-salary-history-content', false)->assertSee('Original reason')
            ->assertDontSee('<html', false)->assertDontSee('data-salary-history-panel', false);
        $this->getJson('/_test/revision-history/20/'.$record->id.'/edit')->assertForbidden();
        $this->putJson('/_test/revision-history/20/'.$record->id, ['remark' => 'Changed'])->assertForbidden();
        $this->deleteJson('/_test/revision-history/20/'.$record->id)->assertForbidden();
        $this->abilities[] = 'employee.salary.history.edit';
        $this->get('/_test/revision-history/20?salary_history_modal=1', ['Accept' => 'application/json'])
            ->assertOk()->assertSee('data-salary-history-panel', false);
    }

    public function test_revision_edit_updates_only_remarks_with_an_audit_record(): void
    {
        $record = $this->revisionRecord(); $salary = $this->salary();
        $this->abilities = ['show_salary_history', 'employee.salary.history.edit'];
        $this->get('/_test/revision-history/20/'.$record->id.'/edit')->assertOk()->assertSee('Original reason');
        $this->put('/_test/revision-history/20/'.$record->id, ['revised_salary' => 99999])
            ->assertRedirect()->assertSessionHasErrors('revised_salary');
        $this->putJson('/_test/revision-history/20/'.$record->id, ['remark' => 'Corrected reason'])->assertOk();
        $this->assertSame('Corrected reason', $record->fresh()->remark);
        $this->assertEquals(13200, $record->fresh()->revised_salary);
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
        $this->assertSame('revision_update', EmployeeProfileAuditLog::firstOrFail()->action);
        $this->deleteJson('/_test/revision-history/20/'.$record->id)->assertForbidden();
    }

    public function test_revision_delete_hides_the_entry_but_preserves_payroll_snapshots_and_audit(): void
    {
        $record = $this->revisionRecord(); $salary = $this->salary();
        $this->abilities = ['show_salary_history', 'employee.salary.history.delete'];
        $this->get('/_test/revision-history/20/'.$record->id.'/delete-confirmation')->assertOk()->assertSee('data-salary-history-form', false);
        $this->deleteJson('/_test/revision-history/20/'.$record->id)->assertOk();
        $this->assertNull(SalaryReviseHistory::find($record->id));
        $this->assertTrue(SalaryReviseHistory::withTrashed()->findOrFail($record->id)->trashed());
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
        $snapshot = app(\App\Repositories\SalaryReviseHistoryRepository::class)->getSalaryHistoryByEmployee(20, '2026-10-01');
        $this->assertSame($record->id, $snapshot->id);
        $this->assertSame('revision_delete', EmployeeProfileAuditLog::firstOrFail()->action);
        $this->abilities[] = 'salary_increment';
        $this->postJson('/_test/salary-increase', $this->increase(['date_from' => '2026-10-24']))->assertUnprocessable();
        $this->deleteJson('/_test/revision-history/20/'.$record->id)->assertNotFound();
    }

    public function test_revision_actions_enforce_employee_and_branch_scope(): void
    {
        $record = $this->revisionRecord();
        $this->abilities = ['show_salary_history', 'employee.salary.history.edit', 'employee.salary.history.delete'];
        $this->putJson('/_test/revision-history/21/'.$record->id)->assertNotFound();
        $this->deleteJson('/_test/revision-history/21/'.$record->id)->assertNotFound();
        auth()->user()->branch_id = 1;
        DB::table('users')->where('id', 20)->update(['branch_id' => 2]);
        $this->getJson('/_test/revision-history/20?salary_history_modal=1')->assertForbidden();
        $this->putJson('/_test/revision-history/20/'.$record->id)->assertForbidden();
        $this->deleteJson('/_test/revision-history/20/'.$record->id)->assertForbidden();
        $this->assertNotNull($record->fresh());
    }

    private function setupSalaryGroup(): void
    {
        Schema::create('salary_groups', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->boolean('is_active');
        });
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id(); $table->string('component_type'); $table->string('value_type');
            $table->double('annual_component_value'); $table->boolean('status');
        });
        Schema::create('salary_group_component', function (Blueprint $table) {
            $table->integer('salary_group_id'); $table->integer('salary_component_id');
        });
        Schema::create('salary_group_employees', function (Blueprint $table) {
            $table->id(); $table->integer('salary_group_id'); $table->integer('employee_id');
        });
        DB::table('salary_groups')->insert(['id' => 1, 'name' => 'Benefits', 'is_active' => true]);
        DB::table('salary_components')->insert([
            ['id' => 1, 'component_type' => 'earning', 'value_type' => 'ctc', 'annual_component_value' => 5, 'status' => true],
            ['id' => 2, 'component_type' => 'earning', 'value_type' => 'fixed', 'annual_component_value' => 120, 'status' => true],
        ]);
        DB::table('salary_group_component')->insert([
            ['salary_group_id' => 1, 'salary_component_id' => 1], ['salary_group_id' => 1, 'salary_component_id' => 2],
        ]);
        DB::table('salary_group_employees')->insert(['salary_group_id' => 1, 'employee_id' => 20]);
    }

    private function setupSalaryDirectory(): void
    {
        \Illuminate\Support\Facades\Cache::put('payment_currency_symbol', '$');
        $this->setupSalaryGroup();
        Schema::create('roles', function (Blueprint $table) {$table->id(); $table->string('name');});
        DB::table('roles')->insert(['id' => 1, 'name' => 'Staff']);
        Schema::table('users', function (Blueprint $table) {
            $table->integer('role_id')->default(1); $table->integer('department_id')->default(1);
            $table->string('email')->nullable(); $table->string('marital_status')->nullable();
            $table->string('username')->nullable(); $table->string('avatar')->nullable();
        });
        Schema::create('employee_accounts', function (Blueprint $table) {
            $table->id(); $table->integer('user_id'); $table->decimal('salary')->nullable();
            $table->string('salary_cycle')->nullable(); $table->boolean('allow_generate_payroll')->default(true);
        });
        DB::table('users')->where('id', 1)->update(['is_active' => false]);
        DB::table('users')->where('id', 20)->update(['branch_id' => 1, 'email' => 'employee@ky.test', 'username' => 'ky.employee20']);
        DB::table('users')->where('id', 21)->update(['branch_id' => 2]);
        for ($id = 30; $id < 60; $id++) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Staff '.$id, 'branch_id' => 1]);
        }
        $this->salary();
        auth()->user()->branch_id = 1;
        $this->abilities = ['view_salary_list', 'export_salary'];
    }

    public function test_salary_directory_renders_the_toolbar_and_paginated_rows(): void
    {
        $this->setupSalaryDirectory();
        $this->mock(\App\Repositories\CompanyRepository::class, fn ($mock) => $mock->shouldReceive('getCompanyDetail')->andReturn(null));
        // Render this page without unrelated dashboard, notification and navigation data.
        $layoutRoot = sys_get_temp_dir().'/salary-layout-'.bin2hex(random_bytes(5));
        mkdir($layoutRoot.'/layouts', 0777, true);
        file_put_contents($layoutRoot.'/layouts/master.blade.php', "@yield('main-content')");
        $this->app['view']->getFinder()->prependLocation($layoutRoot);
        try {
            $this->get('/_test/salary-directory?per_page=10')->assertOk()
                ->assertSee('employeeSalaryToolkit')->assertSee('salarySearch')->assertSee('salaryRows')
                ->assertSee(__('index.export_excel'))->assertSee(__('index.column_visibility'))
                ->assertSee('salary-employee-avatar')->assertSee('ky.employee20')
                ->assertSee('Staff 38')->assertDontSee('Staff 39');
        } finally {
            unlink($layoutRoot.'/layouts/master.blade.php');
            rmdir($layoutRoot.'/layouts'); rmdir($layoutRoot);
        }
    }

    public function test_salary_fragment_refresh_returns_only_the_filtered_results_block(): void
    {
        $this->setupSalaryDirectory();
        $this->mock(\App\Repositories\CompanyRepository::class, fn ($mock) => $mock->shouldNotReceive('getCompanyDetail'));
        $this->get('/_test/salary-directory?per_page=10&q=Staff&page=2', ['X-Salary-Fragment' => '1', 'Accept' => 'application/json'])
            ->assertOk()->assertSee('employeeSalaryResults')->assertSee('employeeSalaryToolkit')
            ->assertSee('Staff 40')->assertDontSee('Staff 30')->assertDontSee('<html', false)
            ->assertDontSee('id="employeeSalaryFilters"', false);
        $this->abilities = [];
        $this->get('/_test/salary-directory', ['X-Salary-Fragment' => '1', 'Accept' => 'application/json'])->assertForbidden();
    }

    public function test_salary_delete_and_cycle_changes_return_json_for_block_refresh(): void
    {
        $this->setupSalaryDirectory();
        $this->abilities = ['delete_salary', 'change_salary_cycle'];
        DB::table('employee_accounts')->insert(['user_id' => 20, 'salary_cycle' => 'monthly']);
        $this->patchJson('/_test/salary-cycle/20')->assertOk()->assertJsonStructure(['message']);
        $this->deleteJson('/_test/salary-setup/20')->assertOk()->assertJsonStructure(['message']);
        $this->assertSame(0, EmployeeSalary::where('employee_id', 20)->count());
        $this->deleteJson('/_test/salary-setup/20')->assertNotFound();
    }

    public function test_salary_directory_paginates_and_filters_by_search_cycle_and_setup(): void
    {
        $this->setupSalaryDirectory();
        $repository = app(\App\Repositories\UserRepository::class);
        $page = $repository->getAllVerifiedActiveEmployeeWithSalaryGroup(['branch_id' => 1, 'per_page' => '10']);
        $this->assertCount(10, $page);
        $this->assertSame(31, $page->total());
        $filtered = $repository->getAllVerifiedActiveEmployeeWithSalaryGroup([
            'branch_id' => 1, 'q' => 'employee@ky.test', 'per_page' => 'all', 'payment_type' => 'monthly', 'salary_status' => 'configured',
        ]);
        $this->assertCount(1, $filtered);
        $this->assertEquals(20, $filtered->first()->employee_id);
        $this->assertCount(1, $repository->getAllVerifiedActiveEmployeeWithSalaryGroup(['branch_id' => 1, 'q' => 'ky.employee20', 'per_page' => 'all']));
        $this->assertCount(30, $repository->getAllVerifiedActiveEmployeeWithSalaryGroup(['branch_id' => 1, 'salary_status' => 'missing', 'per_page' => 'all']));
    }

    public function test_recommended_salary_columns_export_the_breakdown_for_the_selected_payment_cycle(): void
    {
        $this->setupSalaryDirectory();
        $this->getJson('/_test/salary-directory?export=json&employee_id=20')
            ->assertOk()->assertJsonCount(6, 'headings')
            ->assertJsonPath('rows.0', ['Employee', '1000.00', '800.00', '200.00', __('index.monthly'), __('index.salary_setup_ready')]);
        EmployeeSalary::where('employee_id', 20)->update([
            'payment_type' => 'weekly', 'annual_salary' => 10400,
            'weekly_basic_salary' => 160, 'weekly_fixed_allowance' => 40,
        ]);
        $this->getJson('/_test/salary-directory?export=json&employee_id=20')
            ->assertOk()->assertJsonPath('rows.0', ['Employee', '200.00', '160.00', '40.00', __('index.weekly'), __('index.salary_setup_ready')]);
    }

    public function test_salary_export_requires_export_permission_and_ignores_foreign_branch_override(): void
    {
        $this->setupSalaryDirectory();
        $this->abilities = ['view_salary_list'];
        $this->getJson('/_test/salary-directory?export=json')->assertForbidden();
        $this->abilities[] = 'export_salary';
        $response = $this->getJson('/_test/salary-directory?export=json&branch_id=2&per_page=10&page=2&columns[]=employee&columns[]=salary');
        $response->assertOk()->assertJsonCount(31, 'rows')->assertJsonCount(2, 'headings');
        $this->assertSame(['Employee', '1000.00'], $response->json('rows.0'));
        $this->assertNotContains('Other employee', array_column($response->json('rows'), 0));
        $this->getJson('/_test/salary-directory?export=json&q=employee@ky.test')->assertOk()->assertJsonCount(1, 'rows');
        $this->getJson('/_test/salary-directory?export=json&columns[]=password')->assertUnprocessable();
    }

    public function test_salary_csv_and_excel_export_selected_columns_and_escape_csv_formulas(): void
    {
        $this->setupSalaryDirectory();
        DB::table('users')->where('id', 20)->update(['name' => '=FORMULA']);
        \Maatwebsite\Excel\Facades\Excel::fake();
        $this->get('/_test/salary-directory?export=csv&employee_id=20&columns[]=employee')->assertOk();
        \Maatwebsite\Excel\Facades\Excel::assertDownloaded('employee-salaries-20261025.csv', function ($export) {
            $this->assertSame([["'=FORMULA"]], $export->array());
            return true;
        });
        $this->get('/_test/salary-directory?export=excel&employee_id=20&columns[]=employee')->assertOk();
        \Maatwebsite\Excel\Facades\Excel::assertDownloaded('employee-salaries-20261025.xlsx', fn ($export) => $export->array() === [['=FORMULA']]);
        $this->get('/_test/salary-directory?export=pdf&employee_id=20&orientation=portrait&columns[]=employee')
            ->assertOk()->assertSee('A4 portrait', false)->assertDontSee('Salary Group');
    }

    public function test_salary_list_modal_saves_return_to_the_list_without_profile_permissions(): void
    {
        $this->setupSalaryDirectory();
        $this->abilities = ['view_salary_list', 'edit_salary', 'salary_increment'];
        $destination = route('admin.employee-salaries.index', ['employee_modal' => '1', 'salary_modal' => '1']);
        $this->put('/_test/salary-edit/20', [
            'payroll_type' => 'annual', 'payment_type' => 'monthly', 'annual_salary' => 12000,
            'basic_salary_type' => 'percent', 'basic_salary_value' => 80,
            'employee_modal' => '1', 'salary_modal' => '1',
        ])->assertRedirect($destination)->assertSessionHas('success');
        $result = app(EmployeeSalaryController::class)->index(Request::create('/_test/salary-directory', 'GET', ['salary_modal' => '1']));
        $this->assertSame('admin.payroll.employeeSalary.partials.modal-saved', $result->name());
        $this->post('/_test/salary-increase', $this->increase(['employee_modal' => '1', 'salary_modal' => '1']))
            ->assertRedirect($destination)->assertSessionHasNoErrors();
    }

    public function test_salary_setup_and_edit_save_server_calculated_earnings_and_allowance(): void
    {
        $this->setupSalaryGroup(); $this->abilities = ['add_salary', 'edit_salary'];
        $data = ['payroll_type' => 'annual', 'payment_type' => 'monthly', 'annual_salary' => 12000,
            'basic_salary_type' => 'percent', 'basic_salary_value' => 80, 'annual_fixed_allowance' => 99999];
        $this->post('/_test/salary-setup/20', $data)->assertRedirect()->assertSessionHasNoErrors();
        $salary = EmployeeSalary::firstOrFail();
        $this->assertEquals(1680, $salary->annual_fixed_allowance);
        $this->assertEquals(140, EmployeeProfile::firstOrFail()->allowances);
        $data['annual_salary'] = 13200;
        $this->put('/_test/salary-edit/20', $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals(1860, $salary->fresh()->annual_fixed_allowance);
        $this->assertEquals(155, EmployeeProfile::firstOrFail()->allowances);
    }

    public function test_setup_rejects_earning_components_exceeding_total_salary(): void
    {
        $this->setupSalaryGroup(); $this->abilities = ['add_salary'];
        $this->post('/_test/salary-setup/20', [
            'payroll_type' => 'annual', 'payment_type' => 'monthly', 'annual_salary' => 12000,
            'basic_salary_type' => 'percent', 'basic_salary_value' => 100,
        ])->assertRedirect()->assertSessionHasErrors('basic_salary_value');
        $this->assertSame(0, EmployeeSalary::count());
    }

    public function test_salary_setup_routes_enforce_roles_and_branch_scope_before_processing(): void
    {
        $routes = [
            ['get', '/_test/salary-setup/20'], ['get', '/_test/salary-edit/20'],
            ['post', '/_test/salary-setup/20'], ['put', '/_test/salary-edit/20'],
            ['delete', '/_test/salary-setup/20'], ['patch', '/_test/salary-cycle/20'],
        ];
        $this->abilities = [];
        foreach ($routes as [$method, $url]) $this->json($method, $url)->assertForbidden();
        $this->abilities = ['add_salary', 'edit_salary', 'delete_salary', 'change_salary_cycle'];
        auth()->user()->branch_id = 1;
        DB::table('users')->where('id', 20)->update(['branch_id' => 2]);
        foreach ($routes as [$method, $url]) $this->json($method, $url)->assertForbidden();
    }

    public function test_setup_uses_annual_basic_salary_before_rounding_monthly_amounts(): void
    {
        $amounts = app(EmployeeSalaryManagementService::class)->setupAmounts([
            'payroll_type' => 'annual', 'payment_type' => 'monthly', 'annual_salary' => 1000,
            'basic_salary_type' => 'percent', 'basic_salary_value' => 80,
        ]);
        $this->assertEquals(800, $amounts['annual_basic_salary']);
        $this->assertEquals(66.67, $amounts['monthly_basic_salary']);
        $this->assertEquals(200, $amounts['annual_fixed_allowance']);
        $this->assertEquals(16.67, $amounts['monthly_fixed_allowance']);
    }

    public function test_hourly_setup_calculates_annual_salary_from_rate_and_hours(): void
    {
        $amounts = app(EmployeeSalaryManagementService::class)->setupAmounts([
            'payroll_type' => 'hourly', 'payment_type' => 'weekly', 'annual_salary' => 1,
            'hour_rate' => 5, 'weekly_hours' => 40, 'basic_salary_type' => 'percent', 'basic_salary_value' => 100,
        ]);
        $this->assertEquals(10400, $amounts['annual_salary']);
        $this->assertEquals(200, $amounts['weekly_basic_salary']);
        $this->assertEquals(0, $amounts['weekly_fixed_allowance']);
    }

    public function test_setup_rejects_hourly_amounts_above_the_supported_limit(): void
    {
        $this->expectException(ValidationException::class);
        app(EmployeeSalaryManagementService::class)->setupAmounts([
            'payroll_type' => 'hourly', 'payment_type' => 'weekly', 'annual_salary' => 1,
            'hour_rate' => 99999999, 'weekly_hours' => 40, 'basic_salary_type' => 'percent', 'basic_salary_value' => 100,
        ]);
    }

    public function test_draft_requests_must_be_submitted_before_approval(): void
    {
        $salary = $this->salary(); $record = $this->requestRecord(['approval_status' => 'draft']);
        $this->patchJson('/_test/salary-status/20/'.$record->id, ['approval_status' => 'approved'])->assertUnprocessable();
        $this->assertSame('draft', $record->fresh()->approval_status);
        $this->patch('/_test/salary-status/20/'.$record->id, ['approval_status' => 'pending'])->assertSessionHasNoErrors();
        $this->patch('/_test/salary-status/20/'.$record->id, ['approval_status' => 'approved'])->assertSessionHasNoErrors();
        $this->assertSame('approved', $record->fresh()->approval_status);
        $this->assertEquals(13200, $salary->fresh()->annual_salary);
    }

    public function test_salary_history_manage_does_not_grant_edit_or_delete(): void
    {
        $record = $this->requestRecord();
        $this->getJson('/_test/salary-history/20/'.$record->id.'/edit')->assertForbidden();
        $this->putJson('/_test/salary-history/20/'.$record->id, ['reason' => 'Changed'])->assertForbidden();
        $this->deleteJson('/_test/salary-history/20/'.$record->id)->assertForbidden();
        $this->assertSame('Performance review', $record->fresh()->reason);
    }

    public function test_history_edit_recalculates_pending_amounts_without_approving_or_changing_payroll(): void
    {
        $salary = $this->salary(); $record = $this->requestRecord();
        $this->abilities = ['employee.salary.history.edit'];
        $this->put('/_test/salary-history/20/'.$record->id, [
            'effective_date' => '2026-10-26', 'old_base_salary' => 800, 'new_base_salary' => 1000,
            'allowance_before' => 200, 'allowance_after' => 250, 'reason' => 'Corrected request', 'note' => 'Updated',
            'increase_amount' => 999, 'increase_percentage' => 999, 'approval_status' => 'approved',
            'employee_modal' => '1',
        ])->assertRedirect(route('admin.employees.profile.show', ['employee' => 20, 'tab' => 'salary', 'employee_modal' => '1']))
            ->assertSessionHasNoErrors();
        $this->assertEquals(200, $record->fresh()->increase_amount);
        $this->assertEquals(25, $record->fresh()->increase_percentage);
        $this->assertSame('pending', $record->fresh()->approval_status);
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
        $this->assertSame('update', EmployeeProfileAuditLog::firstOrFail()->action);
        $this->deleteJson('/_test/salary-history/20/'.$record->id)->assertForbidden();
    }

    public function test_history_edit_validates_amounts_and_locks_finalized_values(): void
    {
        $record = $this->requestRecord(); $this->abilities = ['employee.salary.history.edit'];
        $this->put('/_test/salary-history/20/'.$record->id, [
            'effective_date' => 'invalid', 'old_base_salary' => 800, 'new_base_salary' => 700,
        ])->assertRedirect()->assertSessionHasErrors(['effective_date', 'new_base_salary']);
        $record->update(['approval_status' => 'approved']);
        $this->put('/_test/salary-history/20/'.$record->id, ['new_base_salary' => 2000])
            ->assertRedirect()->assertSessionHasErrors('new_base_salary');
        session()->forget('errors');
        $this->put('/_test/salary-history/20/'.$record->id, ['reason' => 'Corrected reason', 'note' => 'Reviewed'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Corrected reason', $record->fresh()->reason);
        $this->assertEquals(900, $record->fresh()->new_base_salary);
    }

    public function test_history_delete_requires_its_own_permission_and_keeps_audit_and_payroll(): void
    {
        $salary = $this->salary(); $record = $this->requestRecord(['approval_status' => 'approved']);
        $this->abilities = ['employee.salary.history.delete'];
        $this->putJson('/_test/salary-history/20/'.$record->id, ['reason' => 'Change'])->assertForbidden();
        $this->delete('/_test/salary-history/20/'.$record->id, ['employee_modal' => '1'])
            ->assertRedirect(route('admin.employees.profile.show', ['employee' => 20, 'tab' => 'salary', 'employee_modal' => '1']));
        $this->assertNull($record->fresh());
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
        $audit = EmployeeProfileAuditLog::firstOrFail();
        $this->assertSame('delete', $audit->action);
        $this->assertEquals(900, $audit->old_values['new_base_salary']);
    }

    public function test_history_update_and_delete_enforce_employee_and_branch_scope(): void
    {
        $record = $this->requestRecord();
        $this->abilities = ['employee.salary.history.edit', 'employee.salary.history.delete'];
        $this->getJson('/_test/salary-history/21/'.$record->id.'/edit')->assertNotFound();
        $this->putJson('/_test/salary-history/21/'.$record->id)->assertNotFound();
        $this->deleteJson('/_test/salary-history/21/'.$record->id)->assertNotFound();
        auth()->user()->branch_id = 1;
        DB::table('users')->where('id', 20)->update(['branch_id' => 2]);
        $this->getJson('/_test/salary-history/20/'.$record->id.'/edit')->assertForbidden();
        $this->putJson('/_test/salary-history/20/'.$record->id)->assertForbidden();
        $this->deleteJson('/_test/salary-history/20/'.$record->id)->assertForbidden();
        $this->assertNotNull($record->fresh());
    }

    public function test_history_permissions_are_registered_for_roles_without_automatic_assignment(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id(); $table->string('permission_key')->unique();
            $table->string('name'); $table->integer('permission_groups_id');
        });
        Schema::create('permission_roles', function (Blueprint $table) {
            $table->integer('permission_id'); $table->integer('role_id');
        });
        DB::table('permissions')->insert([
            'permission_key' => 'employee.salary.history.manage', 'name' => 'Manage', 'permission_groups_id' => 5,
        ]);
        $migration = require database_path('migrations/2026_10_26_000001_add_salary_history_edit_delete_permissions.php');
        $migration->up(); $migration->up();
        $this->assertSame(3, DB::table('permissions')->count());
        $this->assertSame(0, DB::table('permission_roles')->count());
        $keys = array_column(\App\Helpers\RolePermissionHelper::permissionArray(), 'permission_key');
        foreach (['edit', 'delete'] as $action) {
            $this->assertContains('employee.salary.history.'.$action, $keys);
        }
        $migration->down();
        $this->assertSame(1, DB::table('permissions')->count());
    }

    public function test_modal_payroll_increase_returns_to_embedded_employee_salary(): void
    {
        $this->salary();
        $this->post('/_test/salary-increase', $this->increase(['employee_modal' => '1']))
            ->assertRedirect(route('admin.employees.profile.show', [
                'employee' => 20, 'tab' => 'salary', 'employee_modal' => '1',
            ]))->assertSessionHas('success');
        $this->assertEquals(13200, EmployeeSalary::where('employee_id', 20)->first()->annual_salary);
    }

    public function test_modal_salary_approval_returns_to_embedded_employee_salary(): void
    {
        $this->salary();
        $record = $this->requestRecord();
        $this->patch('/_test/salary-status/20/'.$record->id, [
            'approval_status' => 'approved', 'employee_modal' => '1',
        ])->assertRedirect(route('admin.employees.profile.show', [
            'employee' => 20, 'tab' => 'salary', 'employee_modal' => '1',
        ]))->assertSessionHas('success');
        $this->assertSame('approved', $record->fresh()->approval_status);
    }

    public function test_monthly_increase_updates_payroll_profile_and_both_histories_using_server_calculations(): void
    {
        $salary = $this->salary();
        $this->post('/_test/salary-increase', $this->increase(['revised_salary' => 999999]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals(13200, $salary->fresh()->annual_salary);
        $this->assertEquals(880, $salary->fresh()->monthly_basic_salary);
        $this->assertEquals(220, EmployeeProfile::firstOrFail()->allowances);
        $this->assertEquals(880, EmployeeProfile::firstOrFail()->current_base_salary);
        $this->assertEquals(1200, SalaryReviseHistory::firstOrFail()->increment_amount);
        $this->assertEquals(80, EmployeeSalaryHistory::firstOrFail()->increase_amount);
        $this->assertSame('approved', EmployeeSalaryHistory::firstOrFail()->approval_status);
        $this->assertEquals(1, EmployeeSalaryHistory::firstOrFail()->approved_by);
        $this->assertSame(2, EmployeeProfileAuditLog::count());
    }

    public function test_percentage_increase_preserves_fixed_basic_and_updates_allowance(): void
    {
        $salary = $this->salary(['basic_salary_type' => 'fixed', 'basic_salary_value' => 800]);
        app(EmployeeSalaryManagementService::class)->revise($this->increase(['increase_type' => 'percent', 'increment_percent' => 10]));
        $this->assertEquals(13200, $salary->fresh()->annual_salary);
        $this->assertEquals(800, $salary->fresh()->monthly_basic_salary);
        $this->assertEquals(300, EmployeeProfile::firstOrFail()->allowances);
    }

    public function test_weekly_hourly_increase_updates_weekly_monthly_and_hour_rate(): void
    {
        $salary = $this->salary(['payroll_type' => 'hourly', 'payment_type' => 'weekly', 'annual_salary' => 10400,
            'basic_salary_value' => 100, 'annual_basic_salary' => 10400, 'weekly_basic_salary' => 200,
            'weekly_fixed_allowance' => 0, 'hour_rate' => 5, 'weekly_hours' => 40]);
        app(EmployeeSalaryManagementService::class)->revise($this->increase(['current_salary' => 10400, 'increment_amount' => 20]));
        $this->assertEquals(11440, $salary->fresh()->annual_salary);
        $this->assertEquals(220, $salary->fresh()->weekly_basic_salary);
        $this->assertEquals(5.5, $salary->fresh()->hour_rate);
        $this->assertEquals(953.33, $salary->fresh()->monthly_basic_salary);
        $this->assertEquals(220, EmployeeProfile::firstOrFail()->current_base_salary);
    }

    public function test_approval_updates_payroll_and_preserves_original_request(): void
    {
        $salary = $this->salary(); $record = $this->requestRecord();
        $this->patch('/_test/salary-status/20/'.$record->id, ['approval_status' => 'approved'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals(13200, $salary->fresh()->annual_salary);
        $this->assertEquals(900, EmployeeProfile::firstOrFail()->current_base_salary);
        $this->assertSame('approved', $record->fresh()->approval_status);
        $this->assertEquals(12.5, $record->fresh()->increase_percentage);
        $this->assertSame(1, EmployeeSalaryHistory::count());
        $this->assertSame(1, SalaryReviseHistory::count());
        $this->patchJson('/_test/salary-status/20/'.$record->id, ['approval_status' => 'approved'])->assertUnprocessable();
        $this->assertSame(1, SalaryReviseHistory::count());
    }

    public function test_future_and_stale_approvals_leave_payroll_unchanged(): void
    {
        $salary = $this->salary();
        foreach ([['effective_date' => '2026-11-01'], ['old_base_salary' => 700], ['allowance_before' => 100]] as $override) {
            $record = $this->requestRecord($override);
            $this->patchJson('/_test/salary-status/20/'.$record->id, ['approval_status' => 'approved'])->assertUnprocessable();
            $this->assertSame('pending', $record->fresh()->approval_status);
        }
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
        $this->assertSame(0, SalaryReviseHistory::count());
    }

    public function test_cancellation_and_rejection_keep_history_without_changing_salary(): void
    {
        $salary = $this->salary();
        foreach (['cancelled', 'rejected'] as $status) {
            $record = $this->requestRecord();
            $this->patch('/_test/salary-status/20/'.$record->id, ['approval_status' => $status])->assertRedirect();
            $this->assertSame($status, $record->fresh()->approval_status);
        }
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
        $this->assertSame(2, EmployeeProfileAuditLog::count());
    }

    public function test_approval_requires_permission_and_employee_ownership(): void
    {
        $this->salary(); $record = $this->requestRecord();
        $this->patchJson('/_test/salary-status/21/'.$record->id, ['approval_status' => 'approved'])->assertNotFound();
        $this->abilities = ['employee.salary.history.manage'];
        $this->patchJson('/_test/salary-status/20/'.$record->id, ['approval_status' => 'approved'])->assertForbidden();
        $this->postJson('/_test/salary-increase', $this->increase())->assertForbidden();
    }

    public function test_invalid_or_stale_increases_do_not_create_history(): void
    {
        $salary = $this->salary();
        foreach ([['current_salary' => 10000], ['increment_amount' => -10], ['date_from' => '2026-11-01'],
            ['date_from' => 'invalid'], ['increment_amount' => 99999999], ['remark' => '']] as $override) {
            $this->postJson('/_test/salary-increase', $this->increase($override))->assertUnprocessable();
        }
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
        $this->assertSame(0, SalaryReviseHistory::count());
    }

    public function test_audit_failure_rolls_back_salary_profile_and_both_histories(): void
    {
        $salary = $this->salary();
        EmployeeProfileAuditLog::creating(function () {throw new \RuntimeException('Audit unavailable');});
        try {
            app(EmployeeSalaryManagementService::class)->revise($this->increase());
            $this->fail('Expected audit failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        } finally {
            EmployeeProfileAuditLog::flushEventListeners();
        }
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
        $this->assertSame(0, EmployeeProfile::count());
        $this->assertSame(0, EmployeeSalaryHistory::count());
        $this->assertSame(0, SalaryReviseHistory::count());
    }

    public function test_multiple_increases_resolve_the_correct_salary_for_past_payroll_periods(): void
    {
        $this->salary();
        $service = app(EmployeeSalaryManagementService::class);
        $service->revise($this->increase(['date_from' => '2026-10-10']));
        $service->revise($this->increase(['date_from' => '2026-10-20', 'current_salary' => 13200]));
        $repository = app(\App\Repositories\SalaryReviseHistoryRepository::class);
        $this->assertEquals(12000, $repository->getSalaryHistoryByEmployee(20, '2026-10-05')->base_salary);
        $this->assertEquals(13200, $repository->getSalaryHistoryByEmployee(20, '2026-10-15')->base_salary);
        $this->assertNull($repository->getSalaryHistoryByEmployee(20, '2026-10-25'));
        $this->postJson('/_test/salary-increase', $this->increase(['date_from' => '2026-10-15', 'current_salary' => 14400]))->assertUnprocessable();
        $this->assertSame(2, SalaryReviseHistory::count());
    }

    public function test_salary_group_earnings_are_kept_separate_from_fixed_allowance(): void
    {
        Schema::create('salary_groups', function (Blueprint $table) {$table->id(); $table->string('name');});
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id(); $table->string('component_type'); $table->string('value_type');
            $table->double('annual_component_value'); $table->boolean('status');
        });
        Schema::create('salary_group_component', function (Blueprint $table) {$table->integer('salary_group_id'); $table->integer('salary_component_id');});
        DB::table('salary_groups')->insert(['id' => 1, 'name' => 'Employee benefits']);
        DB::table('salary_components')->insert(['id' => 1, 'component_type' => 'earning', 'value_type' => 'ctc', 'annual_component_value' => 5, 'status' => true]);
        DB::table('salary_group_component')->insert(['salary_group_id' => 1, 'salary_component_id' => 1]);
        $salary = $this->salary(['salary_group_id' => 1, 'monthly_fixed_allowance' => 150, 'annual_fixed_allowance' => 1800]);
        app(EmployeeSalaryManagementService::class)->revise($this->increase());
        $this->assertEquals(165, $salary->fresh()->monthly_fixed_allowance);
        $this->assertEquals(165, EmployeeProfile::firstOrFail()->allowances);
        $this->assertEquals(880, $salary->fresh()->monthly_basic_salary);
    }

    public function test_zero_salary_can_receive_an_amount_increase_without_division_by_zero(): void
    {
        $salary = $this->salary(['annual_salary' => 0, 'basic_salary_value' => 100]);
        app(EmployeeSalaryManagementService::class)->revise($this->increase(['current_salary' => 0, 'increment_amount' => 100]));
        $this->assertEquals(1200, $salary->fresh()->annual_salary);
        $this->assertEquals(0, SalaryReviseHistory::firstOrFail()->increment_percent);
    }

    public function test_removing_payroll_preserves_history_and_clears_current_profile_amounts(): void
    {
        $salary = $this->salary();
        $service = app(EmployeeSalaryManagementService::class);
        $service->revise($this->increase());
        $service->remove($salary->fresh());
        $this->assertSame(0, EmployeeSalary::count());
        $this->assertNull(EmployeeProfile::firstOrFail()->current_base_salary);
        $this->assertSame(1, EmployeeSalaryHistory::count());
        $this->assertSame(1, SalaryReviseHistory::count());
        $this->assertSame(1, EmployeeProfileAuditLog::where('action', 'payroll_delete')->count());
    }

    public function test_profile_request_is_pending_and_does_not_accept_spoofed_approvers_or_totals(): void
    {
        $salary = $this->salary();
        $this->post('/_test/profile-salary', [
            'effective_date' => '2026-11-01', 'old_base_salary' => 800, 'new_base_salary' => 900,
            'increase_amount' => 9999, 'increase_percentage' => 9999, 'approved_by' => 21,
            'approval_status' => 'pending', 'reason' => 'Monthly review',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $record = EmployeeSalaryHistory::firstOrFail();
        $this->assertEquals(100, $record->increase_amount);
        $this->assertEquals(12.5, $record->increase_percentage);
        $this->assertNull($record->approved_by);
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
        $this->assertSame(0, SalaryReviseHistory::count());
    }

    public function test_profile_request_cannot_apply_salary_without_increment_permission(): void
    {
        $salary = $this->salary(); $this->abilities = ['employee.salary.history.manage'];
        $this->postJson('/_test/profile-salary', [
            'effective_date' => '2026-10-25', 'old_base_salary' => 800, 'new_base_salary' => 900,
            'approval_status' => 'approved',
        ])->assertForbidden();
        $this->assertSame(0, EmployeeSalaryHistory::count());
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
    }

    public function test_salary_increases_cannot_cross_the_managers_branch(): void
    {
        $salary = $this->salary(); $record = $this->requestRecord();
        auth()->user()->branch_id = 1;
        DB::table('users')->where('id', 20)->update(['branch_id' => 2]);
        $this->postJson('/_test/salary-increase', $this->increase())->assertForbidden();
        $this->patchJson('/_test/salary-status/20/'.$record->id, ['approval_status' => 'approved'])->assertForbidden();
        $this->assertEquals(12000, $salary->fresh()->annual_salary);
    }

    public function test_system_administrator_history_keeps_the_name_without_using_an_employee_id(): void
    {
        $this->salary();
        auth()->logout();
        $admin = (new \App\Models\Admin)->forceFill(['id' => 99, 'name' => 'System administrator']);
        $this->actingAs($admin, 'admin');
        app(EmployeeSalaryManagementService::class)->revise($this->increase());
        $revision = SalaryReviseHistory::firstOrFail();
        $this->assertNull($revision->created_by);
        $this->assertSame('System administrator', $revision->created_by_name);
        $record = EmployeeSalaryHistory::firstOrFail();
        $this->assertNull($record->approved_by);
        $this->assertSame('System administrator', $record->approved_by_name);
        $this->assertNull(EmployeeProfileAuditLog::firstOrFail()->performed_by);
    }

    public function test_actor_snapshot_migration_preserves_existing_history(): void
    {
        DB::table('salary_revise_histories')->insert(['employee_id' => 20, 'created_by' => 1, 'base_salary' => 12000]);
        Schema::table('salary_revise_histories', fn (Blueprint $table) => $table->dropColumn('created_by_name'));
        Schema::table('employee_salary_history', fn (Blueprint $table) => $table->dropColumn(['requested_by_name', 'approved_by_name']));
        $migration = require database_path('migrations/2026_10_25_000007_add_salary_history_actor_snapshots.php');
        $migration->up();
        $this->assertTrue(Schema::hasColumn('salary_revise_histories', 'created_by_name'));
        $this->assertTrue(Schema::hasColumn('employee_salary_history', 'approved_by_name'));
        $this->assertEquals(12000, DB::table('salary_revise_histories')->value('base_salary'));
        DB::table('salary_revise_histories')->insert(['employee_id' => 20, 'created_by' => null, 'created_by_name' => 'Administrator']);
        $this->assertSame(2, DB::table('salary_revise_histories')->count());
        $migration->down();
        $this->assertSame(2, DB::table('salary_revise_histories')->count());
    }
}
