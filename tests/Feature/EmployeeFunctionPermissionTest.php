<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\EmployeeProfileController;
use App\Http\Controllers\Web\EmployeeSalaryController;
use App\Http\Controllers\Web\UserController;
use App\Models\User;
use App\Models\Role;
use App\Http\Middleware\SPAuthGateMW;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class EmployeeFunctionPermissionTest extends TestCase
{
    private array $abilities = [];
    private bool $useRolePermissions = false;

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
        $this->actingAs((new User)->forceFill(['id' => 10, 'name' => 'Limited user']));
        Gate::before(fn ($user, $ability) => $this->useRolePermissions ? null : in_array($ability, $this->abilities, true));
    }

    public function test_profile_view_does_not_allow_attendance_reports(): void
    {
        $this->abilities = ['employee.profile.view'];
        Route::get('/_test/profile-report-access', [EmployeeProfileController::class, 'index']);
        $this->getJson('/_test/profile-report-access?attendance_report=1')->assertForbidden();
    }

    public function test_role_assignments_grant_only_the_allocated_employee_functions(): void
    {
        $this->useRolePermissions = true;
        Schema::create('permissions', function (Blueprint $table) {
            $table->id(); $table->string('permission_key');
        });
        Schema::create('permission_roles', function (Blueprint $table) {
            $table->integer('role_id'); $table->integer('permission_id');
        });
        DB::table('permissions')->insert([
            ['id' => 1, 'permission_key' => 'employee.profile.view'],
            ['id' => 2, 'permission_key' => 'edit_employee'],
            ['id' => 3, 'permission_key' => 'delete_employee'],
        ]);
        DB::table('permission_roles')->insert([
            ['role_id' => 2, 'permission_id' => 1],
            ['role_id' => 2, 'permission_id' => 2],
            ['role_id' => 3, 'permission_id' => 3],
        ]);
        auth()->user()->forceFill(['role_id' => 2])->setRelation('role', (new Role)->forceFill(['slug' => 'hr-officer']));
        (new SPAuthGateMW)->handle(Request::create('/_test/role-permissions'), fn () => response('OK'));
        $this->assertTrue(Gate::allows('employee.profile.view'));
        $this->assertTrue(Gate::allows('edit_employee'));
        $this->assertFalse(Gate::allows('delete_employee'));
        $this->assertFalse(Gate::allows('employee.salary.history.manage'));
    }

    public function test_profile_view_does_not_allow_any_profile_mutations(): void
    {
        $this->abilities = ['employee.profile.view'];
        $employee = (new User)->forceFill(['id' => 20]);
        $methods = ['updateProfile', 'storeEmployment', 'storeSalary', 'storeInterview',
            'storeResponsibility', 'storeKpi', 'storeReview', 'storeTraining', 'storeReward',
            'storeDiscipline', 'storeOverviewNote', 'storeGoal', 'storeImprovementPlan',
            'storeDocument', 'saveContract'];
        foreach ($methods as $method) {
            Route::post('/_test/profile-permission/'.$method, function (Request $request) use ($method, $employee) {
                return app(EmployeeProfileController::class)->{$method}($request, $employee);
            });
            $this->postJson('/_test/profile-permission/'.$method)->assertForbidden();
        }
        Route::post('/_test/profile-permission/defaults', fn () => app(EmployeeProfileController::class)->applyDepartmentDefaults($employee));
        $this->postJson('/_test/profile-permission/defaults')->assertForbidden();
    }

    public function test_payroll_helpers_deny_users_without_their_function_permissions(): void
    {
        $this->abilities = ['view_salary_list'];
        Route::post('/_test/payroll-generate-access', [EmployeeSalaryController::class, 'payrollCreate']);
        Route::get('/_test/payroll-tax-access', [EmployeeSalaryController::class, 'calculateTax']);
        Route::get('/_test/payroll-weeks-access/{year}', [EmployeeSalaryController::class, 'getWeeks']);
        Route::get('/_test/overtime-employees-access/{type}', [EmployeeSalaryController::class, 'getEmployeeSalaryData']);
        Route::post('/_test/overtime-departments-access', [EmployeeSalaryController::class, 'getEmployeeByDepartmentPayrollType']);
        $this->postJson('/_test/payroll-generate-access')->assertForbidden();
        $this->getJson('/_test/payroll-tax-access')->assertForbidden();
        $this->getJson('/_test/payroll-weeks-access/2026')->assertForbidden();
        $this->getJson('/_test/overtime-employees-access/monthly')->assertForbidden();
        $this->postJson('/_test/overtime-departments-access')->assertForbidden();
    }

    public function test_overtime_permission_allows_employee_lookup(): void
    {
        $this->abilities = ['overtime_setting'];
        $this->mock(EmployeeSalaryRepository::class, fn ($mock) => $mock
            ->shouldReceive('getPayrollTypeEmployee')->once()->with('monthly')->andReturn(collect()));
        Route::get('/_test/overtime-authorized/{type}', [EmployeeSalaryController::class, 'getEmployeeSalaryData']);
        $this->getJson('/_test/overtime-authorized/monthly')->assertOk()->assertExactJson(['users' => []]);
    }

    public function test_shared_employee_selectors_require_a_related_function_permission(): void
    {
        foreach (['getAllCompanyEmployeeDetail', 'getAllBranchEmployees', 'getAllEmployeeByDepartmentId', 'getBranchEmployeeData'] as $method) {
            Route::get('/_test/employee-selector/'.$method, fn () => app(UserController::class)->{$method}(1));
            $this->getJson('/_test/employee-selector/'.$method)->assertForbidden();
        }
        foreach (['fetchEmployeesByDepartment', 'fetchDepartmentEmployees'] as $method) {
            Route::post('/_test/employee-selector/'.$method, fn (Request $request) => app(UserController::class)->{$method}($request));
            $this->postJson('/_test/employee-selector/'.$method)->assertForbidden();
        }
    }

    public function test_department_employee_selector_allows_authorized_consumers(): void
    {
        $this->mock(UserRepository::class, fn ($mock) => $mock->shouldReceive('getAllActiveEmployeeOfDepartment')
            ->times(3)->with(1, ['name', 'username', 'id'])->andReturn(collect()));
        Route::get('/_test/authorized-employee-selector', fn () => app(UserController::class)->getAllEmployeeByDepartmentId(1));
        foreach (['edit_employee', 'create_resignation', 'create_trainer'] as $permission) {
            $this->abilities = [$permission];
            $this->getJson('/_test/authorized-employee-selector')->assertOk()->assertExactJson(['data' => []]);
        }
    }

    public function test_overview_does_not_render_restricted_audit_or_module_summaries(): void
    {
        $flags = array_fill_keys(['canViewAudit', 'canViewEmployment', 'canViewAttendanceDailyReport',
            'canViewPerformance', 'canViewLeaveRecords', 'canViewGoal', 'canViewTraining', 'canViewDiscipline'], false);
        $overview = ['activity' => collect([['title' => 'CONFIDENTIAL_AUDIT', 'date' => null, 'by' => 'Manager']]),
            'summary360' => array_fill_keys(['employment', 'attendance', 'performance', 'leave', 'goals', 'training', 'discipline'], 'CONFIDENTIAL_SUMMARY')];
        $html = view('admin.employees.profile.partials.overview-activity', $flags + ['overview' => $overview])->render();
        $this->assertStringNotContainsString('CONFIDENTIAL_', $html);
        $flags['canViewAudit'] = true;
        $flags['canViewDiscipline'] = true;
        $html = view('admin.employees.profile.partials.overview-activity', $flags + ['overview' => $overview])->render();
        $this->assertStringContainsString('CONFIDENTIAL_AUDIT', $html);
        $this->assertStringContainsString('CONFIDENTIAL_SUMMARY', $html);
    }

    public function test_overview_cards_do_not_access_restricted_attendance_or_leave_data(): void
    {
        $flags = array_fill_keys(['canViewEmployment', 'canViewAttendanceDailyReport', 'canViewLeaveRecords',
            'canViewPerformance', 'canViewSalary', 'canViewGoal', 'canViewDocument', 'canViewTraining', 'canViewDiscipline'], false);
        $overview = ['employment' => ['years_of_service' => null, 'employment_type' => null, 'status' => 'active'],
            'personal' => array_fill_keys(['name', 'english_name', 'gender', 'dob', 'phone', 'email', 'address', 'emergency_contact'], null)];
        // Omit restricted records entirely, so an accidental read also fails this test.
        foreach (['overview-kpi', 'overview-summaries'] as $partial) {
            $html = view('admin.employees.profile.partials.'.$partial, $flags + ['overview' => $overview])->render();
            $this->assertStringNotContainsString('attendanceChart', $html);
            $this->assertStringNotContainsString('emp-leave-summary', $html);
        }
    }
}
