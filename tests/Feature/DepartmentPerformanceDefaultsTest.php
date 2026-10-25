<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\EmployeeProfileController;
use App\Models\Department;
use App\Models\EmployeeJobResponsibility;
use App\Models\EmployeeKpi;
use App\Models\User;
use App\Services\DepartmentPerformanceDefaults;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepartmentPerformanceDefaultsTest extends TestCase
{
    private array $abilities = ['list_department', 'edit_department', 'employee.kpi.manage'];

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
        parent::setUp(); DB::purge('sqlite'); app()->setLocale('km');
        Schema::create('branches', function (Blueprint $table) {$table->id(); $table->string('name');});
        Schema::create('departments', function (Blueprint $table) {$table->id(); $table->string('dept_name'); $table->integer('branch_id'); $table->boolean('is_active')->default(1);});
        Schema::create('posts', function (Blueprint $table) {$table->id(); $table->string('post_name'); $table->integer('branch_id');});
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('branch_id')->nullable(); $table->integer('department_id')->nullable();
            $table->integer('post_id')->nullable(); $table->boolean('is_active')->default(1); $table->string('status')->default('verified'); $table->softDeletes();
        });
        Schema::create('employee_job_responsibilities', function (Blueprint $table) {
            $table->id(); $table->integer('employee_id'); $table->string('title'); $table->text('description')->nullable();
            $table->string('kpi_target')->nullable(); $table->float('weight')->nullable(); $table->string('status');
            $table->integer('assigned_by')->nullable(); $table->date('start_date')->nullable(); $table->date('end_date')->nullable(); $table->timestamps();
        });
        Schema::create('employee_kpis', function (Blueprint $table) {
            $table->id(); $table->integer('employee_id'); $table->string('name'); $table->text('description')->nullable();
            foreach (['target_value', 'actual_value', 'weight', 'achievement_percentage', 'score'] as $field) $table->float($field)->nullable();
            $table->string('unit'); $table->integer('review_period_id')->nullable(); $table->text('manager_comment')->nullable(); $table->integer('created_by')->nullable(); $table->timestamps();
        });
        Schema::create('employee_profile_audit_logs', function (Blueprint $table) {
            $table->id(); $table->integer('employee_id'); $table->string('module'); $table->string('action'); $table->integer('record_id')->nullable();
            $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->integer('performed_by')->nullable(); $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable(); $table->timestamp('created_at');
        });
        DB::table('branches')->insert([['id' => 1, 'name' => 'Main'], ['id' => 2, 'name' => 'Other']]);
        DB::table('departments')->insert([['id' => 1, 'dept_name' => 'ផ្នែកលក់', 'branch_id' => 1], ['id' => 2, 'dept_name' => 'មេឌៀ(KY)', 'branch_id' => 1], ['id' => 3, 'dept_name' => 'Delivery', 'branch_id' => 2]]);
        DB::table('posts')->insert([['id' => 1, 'post_name' => 'Sales staff', 'branch_id' => 1], ['id' => 2, 'post_name' => 'Video editor', 'branch_id' => 1]]);
        DB::table('users')->insert([
            ['id' => 20, 'name' => 'Sales', 'branch_id' => 1, 'department_id' => 1, 'post_id' => 1, 'is_active' => 1],
            ['id' => 21, 'name' => 'Editor', 'branch_id' => 1, 'department_id' => 2, 'post_id' => 2, 'is_active' => 1],
            ['id' => 22, 'name' => 'Driver', 'branch_id' => 2, 'department_id' => 3, 'post_id' => null, 'is_active' => 1],
            ['id' => 23, 'name' => 'Inactive', 'branch_id' => 1, 'department_id' => 1, 'post_id' => null, 'is_active' => 0],
        ]);
        $this->actingAs((new User())->forceFill(['id' => 10]));
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        Route::post('/_test/performance/{employeeId}/{kind}', function (\Illuminate\Http\Request $request, $employeeId, $kind) {
            $controller = app(EmployeeProfileController::class); $employee = User::findOrFail($employeeId);
            return $kind === 'responsibility' ? $controller->storeResponsibility($request, $employee) : $controller->storeKpi($request, $employee);
        });
        Route::match(['put', 'delete'], '/_test/performance/{employeeId}/{kind}/{id}', function (\Illuminate\Http\Request $request, $employeeId, $kind, $id) {
            $controller = app(EmployeeProfileController::class); $employee = User::findOrFail($employeeId);
            if ($kind === 'responsibility') {
                $record = EmployeeJobResponsibility::findOrFail($id);
                return $request->isMethod('delete') ? $controller->destroyResponsibility($request, $employee, $record) : $controller->updateResponsibility($request, $employee, $record);
            }
            $record = EmployeeKpi::findOrFail($id);
            return $request->isMethod('delete') ? $controller->destroyKpi($request, $employee, $record) : $controller->updateKpi($request, $employee, $record);
        });
        Route::post('/_test/employee-defaults/{id}', function ($id) {return app(EmployeeProfileController::class)->applyDepartmentDefaults(User::findOrFail($id));});
        Route::post('/_test/department-defaults/{id}', [DepartmentController::class, 'applyPerformanceDefaults']);
    }

    public function test_all_role_templates_are_complete_khmer_and_have_valid_weight_totals(): void
    {
        $service = app(DepartmentPerformanceDefaults::class);
        $this->assertCount(23, $service->catalog());
        foreach ($service->catalog() as $profile) {
            $preset = $service->forName($profile['aliases'][0]);
            $this->assertCount(6, $preset['responsibilities']); $this->assertCount(6, $preset['kpis']);
            $this->assertCount(6, $preset['evaluation_criteria']);
            $this->assertSame(100, array_sum(array_column($preset['evaluation_criteria'], 'max_score')));
            foreach ($preset['evaluation_criteria'] as $criterion) {
                $this->assertDoesNotMatchRegularExpression('/[a-z]/i', $criterion['criteria'].$criterion['description']);
                $this->assertStringContainsString('ភស្តុតាង', $criterion['description']);
            }
            $this->assertSame(100, array_sum(array_column($preset['responsibilities'], 'weight')));
            $this->assertSame(100, array_sum(array_column($preset['kpis'], 'weight')));
            foreach ($preset['kpis'] as $kpi) {
                $this->assertMatchesRegularExpression('/[\x{1780}-\x{17FF}]/u', $kpi['description']);
                $this->assertDoesNotMatchRegularExpression('/[a-z]/i', $kpi['name'].$kpi['description']);
                $this->assertNull($kpi['actual_value']); $this->assertNull($kpi['score']);
                $this->assertGreaterThan(0, $kpi['target_value']);
            }
        }
    }

    public function test_specialist_job_title_overrides_department_and_unknown_departments_have_baselines(): void
    {
        $service = app(DepartmentPerformanceDefaults::class);
        $this->assertSame('video_editing', $service->forEmployee(User::findOrFail(21))['key']);
        foreach (['sell' => 'sales', 'IT' => 'it', 'delivery' => 'delivery', 'manager' => 'management', 'digital maketing' => 'digital_marketing', 'media' => 'media', 'design' => 'design', 'camera man' => 'camera'] as $name => $expected) {
            $this->assertSame($expected, $service->forName($name)['key']);
        }
        $this->assertSame('general', $service->forName('Unrecognized department')['key']);
        $this->assertSame('sales', $service->forName('Sales', 'Company staff')['key']);
    }

    public function test_employee_defaults_are_audited_and_safe_to_repeat_without_fabricating_scores(): void
    {
        $this->post('/_test/employee-defaults/20')->assertRedirect();
        $this->assertSame(6, EmployeeKpi::where('employee_id', 20)->count());
        $this->assertSame(6, EmployeeJobResponsibility::where('employee_id', 20)->count());
        $this->assertSame(12, DB::table('employee_profile_audit_logs')->count());
        $this->assertSame(0, EmployeeKpi::whereNotNull('actual_value')->count());
        $this->assertSame(0, EmployeeKpi::whereNotNull('score')->count());
        $this->post('/_test/employee-defaults/20')->assertRedirect();
        $this->assertSame(6, EmployeeKpi::count());
        $this->assertSame(12, DB::table('employee_profile_audit_logs')->count());
    }

    public function test_department_bulk_apply_only_changes_active_employees_in_that_department(): void
    {
        $this->post('/_test/department-defaults/1')->assertRedirect();
        $this->assertSame([20], EmployeeKpi::distinct()->pluck('employee_id')->all());
        $this->assertSame(6, EmployeeJobResponsibility::count());
        $this->post('/_test/department-defaults/1')->assertRedirect();
        $this->assertSame(6, EmployeeKpi::count());
    }

    public function test_custom_sections_and_results_are_preserved_and_permissions_are_required(): void
    {
        $custom = EmployeeKpi::create(['employee_id' => 20, 'name' => 'Custom target', 'unit' => 'number', 'weight' => 100, 'target_value' => 10, 'actual_value' => 7, 'score' => 70]);
        $this->post('/_test/employee-defaults/20')->assertRedirect();
        $this->assertSame(1, EmployeeKpi::count());
        $this->assertEquals(7, $custom->fresh()->actual_value);
        $this->assertEquals(70, $custom->fresh()->score);
        $this->assertSame(6, EmployeeJobResponsibility::count());
        $this->abilities = ['list_department'];
        $this->postJson('/_test/employee-defaults/21')->assertForbidden();
        $this->postJson('/_test/department-defaults/2')->assertForbidden();
        $this->assertSame(1, EmployeeKpi::count());
    }
    public function test_employee_view_shows_full_khmer_responsibilities_and_measurements(): void
    {
        $employee = User::findOrFail(20);
        app(DepartmentPerformanceDefaults::class)->applyToEmployee($employee);
        $this->view('admin.employees.profile.partials.kpi', [
            'employee' => $employee, 'responsibilities' => EmployeeJobResponsibility::all(), 'kpis' => EmployeeKpi::all(),
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->assertSee('ការសម្រេចផែនការលក់')->assertSee('រូបមន្ត៖')->assertSee('ភស្តុតាង៖')->assertDontSee('index.');
    }

    public function test_branch_scoping_prevents_applying_defaults_to_other_branches(): void
    {
        auth()->user()->branch_id = 1;
        \Illuminate\Database\Eloquent\Model::clearBootedModels();
        $this->postJson('/_test/department-defaults/3')->assertNotFound();
        $this->postJson('/_test/employee-defaults/22')->assertNotFound();
        $this->assertSame(0, EmployeeKpi::count());
    }

    public function test_evaluation_form_uses_specialist_khmer_criteria_and_preserves_manual_changes(): void
    {
        $this->abilities[] = 'employee.performance.create';
        $employee = User::findOrFail(21);
        $defaults = app(DepartmentPerformanceDefaults::class)->forEmployee($employee);
        $items = array_map(fn ($criterion) => [$criterion['criteria'], $criterion['description'], $criterion['max_score']], $defaults['evaluation_criteria']);
        $data = ['employee' => $employee, 'reviews' => collect(), 'defaultItems' => $items,
            'evaluationDefaults' => $defaults, 'errors' => new \Illuminate\Support\ViewErrorBag()];
        $this->view('admin.employees.profile.partials.evaluation', $data)
            ->assertSee('ការកាត់តតាមសាច់រឿង')->assertSee('គុណភាពរូបភាពនិងសំឡេង')
            ->assertSee('ការបម្រុងទុកគម្រោងកាត់ត')->assertSee('name="max_score[]" value="25"', false)
            ->assertSee('name="score[]" value=""', false)->assertDontSee('index.');
        request()->setLaravelSession(app('session.store'));
        session()->put('_old_input', ['criteria' => ['លក្ខណៈវិនិច្ឆ័យផ្ទាល់ខ្លួន'], 'description' => ['ស្តង់ដារផ្ទាល់ខ្លួន'], 'max_score' => [100], 'score' => [80]]);
        $this->view('admin.employees.profile.partials.evaluation', $data)
            ->assertSee('name="criteria[]" value="លក្ខណៈវិនិច្ឆ័យផ្ទាល់ខ្លួន"', false)
            ->assertSee('ស្តង់ដារផ្ទាល់ខ្លួន')->assertSee('name="score[]" value="80"', false);
    }

    public function test_responsibility_create_update_and_delete_keep_permanent_audit_history(): void
    {
        $data = ['title' => 'ការងារប្រចាំថ្ងៃ', 'description' => 'ត្រួតពិនិត្យការងារ', 'weight' => 25, 'status' => 'active', 'start_date' => '2026-10-01', 'end_date' => '2026-10-31'];
        $this->post('/_test/performance/20/responsibility', $data)->assertRedirect()->assertSessionHasNoErrors();
        $record = EmployeeJobResponsibility::firstOrFail();
        $id = $record->id;
        $this->assertSame(10, $record->assigned_by);
        $this->put('/_test/performance/20/responsibility/'.$id, array_replace($data, ['title' => 'កែប្រែការងារ', 'weight' => 30, 'status' => 'inactive', 'employee_id' => 21, 'assigned_by' => 999]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $record = $record->fresh();
        $this->assertSame('កែប្រែការងារ', $record->title);
        $this->assertSame(20, $record->employee_id); $this->assertSame(10, $record->assigned_by);
        $this->assertSame('2026-10-31', $record->end_date->format('Y-m-d'));
        $this->delete('/_test/performance/20/responsibility/'.$id)->assertRedirect();
        $this->assertNull(EmployeeJobResponsibility::find($id));
        $logs = DB::table('employee_profile_audit_logs')->where('module', 'responsibility')->orderBy('id')->get();
        $this->assertSame(['create', 'update', 'delete'], $logs->pluck('action')->all());
        $this->assertSame('ការងារប្រចាំថ្ងៃ', json_decode($logs[1]->old_values, true)['title']);
        $this->assertSame('កែប្រែការងារ', json_decode($logs[2]->old_values, true)['title']);
        $this->assertNull($logs[2]->new_values);
    }

    public function test_kpi_updates_targets_results_and_comments_without_reassigning_the_record(): void
    {
        $data = ['name' => 'អត្រាការលក់', 'unit' => 'percentage', 'target_value' => 100, 'actual_value' => 0, 'score' => 0, 'weight' => ''];
        $this->post('/_test/performance/20/kpi', $data)->assertRedirect()->assertSessionHasNoErrors();
        $record = EmployeeKpi::firstOrFail(); $record->update(['review_period_id' => 9]);
        $this->assertEquals(0, $record->weight);
        $this->put('/_test/performance/20/kpi/'.$record->id, array_replace($data, ['actual_value' => 95, 'score' => 90, 'weight' => 25, 'achievement_percentage' => 95,
            'manager_comment' => 'លទ្ធផលបានផ្ទៀងផ្ទាត់', 'employee_id' => 21, 'created_by' => 999]))->assertRedirect()->assertSessionHasNoErrors();
        $fresh = $record->fresh();
        $this->assertEquals(95, $fresh->actual_value); $this->assertEquals(90, $fresh->score);
        $this->assertEquals(95, $fresh->achievement_percentage); $this->assertEquals(25, $fresh->weight);
        $this->assertSame(20, $fresh->employee_id); $this->assertSame(10, $fresh->created_by);
        $this->assertSame(9, $fresh->review_period_id); $this->assertSame('លទ្ធផលបានផ្ទៀងផ្ទាត់', $fresh->manager_comment);
        $this->delete('/_test/performance/20/kpi/'.$record->id)->assertRedirect();
        $this->assertNull($record->fresh());
        $this->assertSame(['create', 'update', 'delete'], DB::table('employee_profile_audit_logs')->where('module', 'kpi')->orderBy('id')->pluck('action')->all());
    }

    public function test_performance_crud_checks_ownership_permissions_and_validation(): void
    {
        $this->post('/_test/performance/20/responsibility', ['title' => 'Duty', 'status' => 'active']);
        $responsibility = EmployeeJobResponsibility::firstOrFail();
        $this->post('/_test/performance/20/kpi', ['name' => 'Target', 'unit' => 'number']);
        $kpi = EmployeeKpi::firstOrFail();
        foreach (['responsibility' => $responsibility->id, 'kpi' => $kpi->id] as $kind => $id) {
            $this->putJson('/_test/performance/21/'.$kind.'/'.$id, [])->assertNotFound();
            $this->deleteJson('/_test/performance/21/'.$kind.'/'.$id)->assertNotFound();
        }
        $this->putJson('/_test/performance/20/responsibility/'.$responsibility->id, ['title' => 'Wrong', 'status' => 'invalid', 'weight' => 101])->assertUnprocessable();
        $this->putJson('/_test/performance/20/kpi/'.$kpi->id, ['name' => 'Wrong', 'unit' => 'invalid', 'score' => -1])->assertUnprocessable();
        $this->assertSame('Duty', $responsibility->fresh()->title); $this->assertSame('Target', $kpi->fresh()->name);
        $this->assertSame(2, DB::table('employee_profile_audit_logs')->count());
        $this->abilities = ['employee.kpi.view'];
        foreach (['responsibility' => $responsibility->id, 'kpi' => $kpi->id] as $kind => $id) {
            $this->postJson('/_test/performance/20/'.$kind, [])->assertForbidden();
            $this->putJson('/_test/performance/20/'.$kind.'/'.$id, [])->assertForbidden();
            $this->deleteJson('/_test/performance/20/'.$kind.'/'.$id)->assertForbidden();
        }
    }

    public function test_read_only_kpi_view_hides_management_actions(): void
    {
        $employee = User::findOrFail(20); app(DepartmentPerformanceDefaults::class)->applyToEmployee($employee);
        $this->abilities = ['employee.kpi.view'];
        $this->view('admin.employees.profile.partials.kpi', ['employee' => $employee, 'responsibilities' => EmployeeJobResponsibility::all(), 'kpis' => EmployeeKpi::all(), 'errors' => new \Illuminate\Support\ViewErrorBag()])
            ->assertDontSee('data-performance-edit', false)->assertDontSee('data-performance-delete', false)->assertDontSee('data-performance-form=', false);
    }

    public function test_a_failed_audit_rolls_back_performance_changes(): void
    {
        Schema::drop('employee_profile_audit_logs');
        $this->postJson('/_test/performance/20/kpi', ['name' => 'Target', 'unit' => 'number'])->assertStatus(500);
        $this->assertSame(0, EmployeeKpi::count());
    }

    public function test_validation_errors_keep_trusted_edit_metadata_for_the_modal(): void
    {
        $this->post('/_test/performance/20/kpi', ['name' => 'Target', 'unit' => 'number']);
        $record = EmployeeKpi::firstOrFail();
        $this->from('/admin/employees/20/profile?tab=kpi')->put('/_test/performance/20/kpi/'.$record->id, [
            'name' => 'Changed target', 'unit' => 'number', 'weight' => 101,
            'performance_record_type' => 'responsibility', 'performance_record_id' => 999,
        ])->assertSessionHasErrors('weight')
            ->assertSessionHas('_old_input.performance_record_type', 'kpi')
            ->assertSessionHas('_old_input.performance_record_id', $record->id);
        $this->assertSame('Target', $record->fresh()->name);
    }

    public function test_history_displays_before_and_after_values_after_a_record_is_deleted(): void
    {
        $this->post('/_test/performance/20/responsibility', ['title' => 'ការងារសាកល្បង', 'status' => 'active']);
        $id = EmployeeJobResponsibility::firstOrFail()->id;
        $this->delete('/_test/performance/20/responsibility/'.$id)->assertRedirect();
        $log = \App\Models\EmployeeProfileAuditLog::where('action', 'delete')->firstOrFail();
        $this->view('admin.employees.profile.partials.performance-record-history', ['log' => $log])
            ->assertSee('ការងារសាកល្បង')->assertSee(__('index.asset_activity_before'))->assertSee(__('index.asset_activity_after'));
    }

}
