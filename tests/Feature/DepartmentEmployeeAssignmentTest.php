<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\DepartmentEmployeeController;
use App\Models\EmployeeProfileAuditLog;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepartmentEmployeeAssignmentTest extends TestCase
{
    private array $abilities = ['list_department', 'edit_employee'];

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
        parent::setUp(); DB::purge('sqlite');
        Schema::create('branches', function (Blueprint $table) {$table->id(); $table->string('name');});
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); $table->string('dept_name'); $table->integer('company_id'); $table->integer('branch_id'); $table->boolean('is_active')->default(true);
        });
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); $table->string('post_name'); $table->integer('dept_id'); $table->integer('branch_id')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('company_id'); $table->integer('branch_id');
            $table->integer('department_id'); $table->integer('post_id'); $table->boolean('is_active')->default(true);
            $table->string('status')->default('verified'); $table->string('avatar')->nullable();
            $table->integer('updated_by')->nullable(); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('employee_profile_audit_logs', function (Blueprint $table) {
            $table->id(); $table->integer('employee_id'); $table->string('module'); $table->string('action'); $table->integer('record_id');
            $table->json('old_values'); $table->json('new_values'); $table->integer('performed_by')->nullable();
            $table->string('ip_address')->nullable(); $table->text('user_agent')->nullable(); $table->timestamp('created_at');
        });
        DB::table('branches')->insert([['id' => 1, 'name' => 'Main'], ['id' => 2, 'name' => 'Other'], ['id' => 3, 'name' => 'Outside']]);
        foreach ([[1, 'Sales', 1, 1, 1], [2, 'Sales', 1, 2, 1], [3, 'Finance', 1, 1, 1], [4, 'External', 2, 3, 1], [5, 'Inactive', 1, 1, 0], [6, 'Empty', 1, 1, 1]] as [$id, $name, $company, $branch, $active]) {
            DB::table('departments')->insert(['id' => $id, 'dept_name' => $name, 'company_id' => $company, 'branch_id' => $branch, 'is_active' => $active]);
        }
        foreach ([[10, 1, 1, 1], [11, 3, 1, 1], [12, 3, 1, 0], [20, 2, 2, 1], [40, 4, 3, 1], [50, 5, 1, 1], [60, 3, null, 1]] as [$id, $dept, $branch, $active]) {
            DB::table('posts')->insert(['id' => $id, 'post_name' => 'Staff', 'dept_id' => $dept, 'branch_id' => $branch, 'is_active' => $active]);
        }
        foreach ([[1, 1, 1, 1, 10], [2, 1, 2, 2, 20], [3, 1, 1, 3, 11], [4, 2, 3, 4, 40], [5, 1, 1, 1, 10], [6, 1, 1, 1, 10]] as [$id, $company, $branch, $dept, $post]) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Employee '.$id, 'company_id' => $company, 'branch_id' => $branch, 'department_id' => $dept, 'post_id' => $post]);
        }
        DB::table('users')->where('id', 5)->update(['is_active' => false]);
        DB::table('users')->where('id', 6)->update(['deleted_at' => now()]);
        $this->actingAs((new User())->forceFill(['id' => 99, 'company_id' => 1]));
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        Route::get('/_test/department/{id}/employees', [DepartmentEmployeeController::class, 'index']);
        Route::get('/_test/employee/{employee}/assignment', [DepartmentEmployeeController::class, 'options']);
        Route::patch('/_test/employee/{employee}/assignment', [DepartmentEmployeeController::class, 'update']);
    }

    public function test_total_employee_popup_lists_only_the_current_department_group(): void
    {
        $response = $this->getJson('/_test/department/1/employees?employee_status=active')->assertOk()->assertJsonPath('can_edit', true);
        $this->assertEqualsCanonicalizing([1, 2], array_column($response->json('employees'), 'id'));
    }

    public function test_options_are_limited_to_active_departments_and_positions_in_the_employee_branch(): void
    {
        $response = $this->getJson('/_test/employee/1/assignment')->assertOk();
        $this->assertEqualsCanonicalizing([1, 3, 6], array_column($response->json('departments'), 'id'));
        $this->assertEqualsCanonicalizing([10, 11, 60], array_column($response->json('positions'), 'id'));
    }

    public function test_quick_change_updates_department_and_position_and_records_an_audit(): void
    {
        $this->patchJson('/_test/employee/1/assignment', ['department_id' => 3, 'post_id' => 11])->assertOk()
            ->assertJsonPath('employee.department', 'Finance')->assertJsonPath('employee.branch', 'Main');
        $this->assertDatabaseHas('users', ['id' => 1, 'department_id' => 3, 'post_id' => 11, 'branch_id' => 1]);
        $audit = EmployeeProfileAuditLog::first();
        $this->assertSame(1, $audit->old_values['department_id']);
        $this->assertSame(3, $audit->new_values['department_id']);
        $this->assertSame(99, $audit->performed_by);
        $this->getJson('/_test/department/1/employees?employee_status=active')->assertOk()->assertJsonCount(1, 'employees')->assertJsonPath('employees.0.id', 2);
    }

    public static function invalidDestinations(): array
    {
        return [[2, 20], [4, 40], [5, 50]];
    }

    /** @dataProvider invalidDestinations */
    public function test_invalid_department_or_position_does_not_modify_the_employee(int $department, int $post): void
    {
        $this->patchJson('/_test/employee/1/assignment', ['department_id' => $department, 'post_id' => $post])->assertUnprocessable();
        $this->assertDatabaseHas('users', ['id' => 1, 'department_id' => 1, 'post_id' => 10]);
        $this->assertDatabaseCount('employee_profile_audit_logs', 0);
    }

    public function test_extra_branch_input_cannot_move_an_employee_to_another_branch(): void
    {
        $this->patchJson('/_test/employee/1/assignment', ['department_id' => 3, 'post_id' => 11, 'branch_id' => 2])->assertOk();
        $this->assertDatabaseHas('users', ['id' => 1, 'branch_id' => 1]);
    }

    public function test_another_company_employee_cannot_be_viewed_or_changed(): void
    {
        $this->getJson('/_test/employee/4/assignment')->assertNotFound();
        $this->patchJson('/_test/employee/4/assignment', ['department_id' => 3, 'post_id' => 11])->assertNotFound();
    }

    public function test_branch_bound_users_cannot_edit_employees_in_another_branch(): void
    {
        $this->actingAs((new User())->forceFill(['id' => 99, 'company_id' => 1, 'branch_id' => 1]));
        $this->getJson('/_test/employee/2/assignment')->assertNotFound();
        $this->patchJson('/_test/employee/2/assignment', ['department_id' => 3, 'post_id' => 11])->assertNotFound();
        $this->getJson('/_test/department/1/employees?employee_status=active')->assertOk()->assertJsonCount(1, 'employees');
    }

    public function test_view_only_users_can_see_employees_but_cannot_change_assignments(): void
    {
        $this->abilities = ['list_department'];
        $this->getJson('/_test/department/1/employees?employee_status=active')->assertOk()->assertJsonPath('can_edit', false);
        $this->getJson('/_test/employee/1/assignment')->assertForbidden();
        $this->patchJson('/_test/employee/1/assignment', ['department_id' => 3, 'post_id' => 11])->assertForbidden();
    }

    public function test_existing_history_is_preserved_when_an_employee_moves(): void
    {
        Schema::create('existing_history', function (Blueprint $table) {$table->id(); $table->integer('employee_id'); $table->integer('department_id');});
        DB::table('existing_history')->insert(['employee_id' => 1, 'department_id' => 1]);
        $this->patchJson('/_test/employee/1/assignment', ['department_id' => 3, 'post_id' => 11])->assertOk();
        $this->assertDatabaseHas('existing_history', ['employee_id' => 1, 'department_id' => 1]);
    }

    public function test_inactive_employees_can_be_reassigned_but_deleted_records_remain_protected(): void
    {
        $this->patchJson('/_test/employee/5/assignment', ['department_id' => 3, 'post_id' => 11])->assertOk();
        $this->assertDatabaseHas('users', ['id' => 5, 'is_active' => false, 'department_id' => 3]);
        $this->patchJson('/_test/employee/6/assignment', ['department_id' => 3, 'post_id' => 11])->assertNotFound();
    }

    public function test_employee_status_filters_the_popup_list(): void
    {
        $this->getJson('/_test/department/1/employees?employee_status=inactive')->assertOk()->assertJsonCount(1, 'employees')->assertJsonPath('employees.0.id', 5);
        $this->getJson('/_test/department/1/employees?employee_status=all')->assertOk()->assertJsonCount(3, 'employees');
        $this->getJson('/_test/department/1/employees?employee_status=invalid')->assertOk()->assertJsonCount(3, 'employees');
    }
    public function test_correction_list_also_includes_employees_with_a_mismatched_position_assignment(): void
    {
        DB::table('users')->where('id', 3)->update(['post_id' => 10]);
        $this->getJson('/_test/department/1/employees?employee_status=all&include_position_assignments=1')
            ->assertOk()->assertJsonCount(4, 'employees');
    }

    public function test_department_only_quick_change_keeps_the_existing_position_name(): void
    {
        $this->patchJson('/_test/employee/1/assignment', ['department_id' => 3])->assertOk()
            ->assertJsonPath('employee.department', 'Finance')->assertJsonPath('employee.position', 'Staff');
        $this->assertDatabaseHas('users', ['id' => 1, 'department_id' => 3, 'post_id' => 11, 'branch_id' => 1]);
        $this->assertDatabaseHas('posts', ['id' => 10, 'dept_id' => 1]);
    }
    public function test_missing_destination_position_is_added_without_changing_the_position_name(): void
    {
        $this->abilities[] = 'create_post';
        $response = $this->patchJson('/_test/employee/1/assignment', ['department_id' => 6])->assertOk()
            ->assertJsonPath('employee.position', 'Staff');
        $postId = $response->json('employee.post_id');
        $this->assertDatabaseHas('posts', ['id' => $postId, 'post_name' => 'Staff', 'dept_id' => 6, 'branch_id' => 1, 'is_active' => 1]);
        $this->assertDatabaseHas('users', ['id' => 1, 'department_id' => 6, 'post_id' => $postId]);
        $this->assertDatabaseHas('posts', ['id' => 10, 'post_name' => 'Staff', 'dept_id' => 1]);
    }
    public function test_adding_a_missing_position_requires_position_creation_permission(): void
    {
        $this->patchJson('/_test/employee/1/assignment', ['department_id' => 6])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => 1, 'department_id' => 1, 'post_id' => 10]);
    }
    public function test_an_arbitrary_post_input_cannot_change_the_employee_position_name(): void
    {
        DB::table('posts')->where('id', 60)->update(['post_name' => 'Another position']);
        $this->patchJson('/_test/employee/1/assignment', ['department_id' => 3, 'post_id' => 60])->assertOk()
            ->assertJsonPath('employee.position', 'Staff')->assertJsonPath('employee.post_id', 11);
    }
    public function test_an_inactive_matching_destination_position_is_not_silently_activated(): void
    {
        DB::table('posts')->where('dept_id', 3)->update(['is_active' => false]);
        $this->patchJson('/_test/employee/1/assignment', ['department_id' => 3])->assertUnprocessable();
        $this->assertDatabaseHas('users', ['id' => 1, 'department_id' => 1, 'post_id' => 10]);
    }

    public function test_initial_popup_includes_inactive_employees(): void
    {
        $this->getJson('/_test/department/1/employees')->assertOk()->assertJsonCount(3, 'employees')
            ->assertJsonFragment(['id' => 5, 'active' => false]);
    }
    public function test_inactive_employees_are_not_hidden_by_their_verification_status(): void
    {
        DB::table('users')->where('id', 5)->update(['status' => 'pending']);
        $this->getJson('/_test/department/1/employees?employee_status=inactive')->assertOk()
            ->assertJsonCount(1, 'employees')->assertJsonPath('employees.0.id', 5)->assertJsonPath('employees.0.active', false);
        $this->patchJson('/_test/employee/5/assignment', ['department_id' => 3])->assertOk();
        $this->assertDatabaseHas('users', ['id' => 5, 'department_id' => 3, 'status' => 'pending', 'is_active' => false]);
    }
}
