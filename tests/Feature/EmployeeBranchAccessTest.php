<?php

namespace Tests\Feature;

use App\Models\User;
use App\Requests\User\UserCreateRequest;
use App\Requests\User\UserUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeBranchAccessTest extends TestCase
{
    private bool $canManage = true;

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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('company_id');
            $table->integer('branch_id'); $table->integer('updated_by')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        (require database_path('migrations/2026_11_13_000005_add_additional_branch_ids_to_users.php'))->up();
        Schema::create('branches', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('company_id');
        });
        DB::table('branches')->insert([
            ['id' => 1, 'name' => 'Home', 'company_id' => 1],
            ['id' => 2, 'name' => 'Additional', 'company_id' => 1],
            ['id' => 3, 'name' => 'Other company', 'company_id' => 2],
        ]);
        DB::table('users')->insert(['id' => 2, 'name' => 'Employee', 'company_id' => 1, 'branch_id' => 1]);
        $this->actingAs((new User)->forceFill(['id' => 1, 'company_id' => 1]));
        Model::clearBootedModels();
        Gate::before(fn ($user, $ability) => $ability === 'role_permission' && $this->canManage);
        Route::middleware('web')->group(function () {
            Route::post('/_test/employee-branch-access/create', function (BranchAccessCreateRequest $request) {
                return User::create($request->validated() + ['name' => 'New employee', 'company_id' => 1, 'branch_id' => 1]);
            });
            Route::put('/_test/employee-home-branch/{employee}', function (HomeBranchUpdateRequest $request, $employee) {
                $user = User::findOrFail($employee);
                $user->update($request->validated());
                return $user;
            });
            Route::put('/_test/employee-branch-access', function (BranchAccessUpdateRequest $request) {
                $employee = User::findOrFail(2);
                $employee->update($request->validated());
                return $employee;
            });
        });
    }

    public function test_create_update_clear_and_omitted_field_preserve_home_branch(): void
    {
        $this->postJson('/_test/employee-branch-access/create', ['branch_access_present' => 1, 'additional_branch_ids' => [2]])->assertSuccessful();
        $this->assertSame([2], User::latest('id')->first()->additional_branch_ids);
        $this->putJson('/_test/employee-branch-access', ['branch_access_present' => 1, 'additional_branch_ids' => [2]])->assertOk();
        $this->assertSame([2], User::find(2)->additional_branch_ids);
        $this->putJson('/_test/employee-branch-access', [])->assertOk();
        $this->assertSame([2], User::find(2)->additional_branch_ids);
        $this->putJson('/_test/employee-branch-access', ['branch_access_present' => 1])->assertOk();
        $this->assertSame([], User::find(2)->additional_branch_ids);
        $this->assertSame(1, User::find(2)->branch_id);
    }

    public function test_editing_employee_in_assigned_branch_preserves_their_home_branch(): void
    {
        auth()->user()->forceFill(['branch_id' => 1, 'additional_branch_ids' => [2]]);
        DB::table('users')->where('id', 2)->update(['branch_id' => 2]);
        Model::clearBootedModels();
        $this->putJson('/_test/employee-home-branch/2', ['branch_access_present' => 1])->assertOk();
        $this->assertSame(2, User::find(2)->branch_id);
        $this->putJson('/_test/employee-home-branch/2', ['branch_id' => 3])->assertForbidden();
        $this->assertSame(2, User::find(2)->branch_id);
    }

    public function test_assignments_reject_other_company_missing_duplicate_and_non_array_values(): void
    {
        foreach ([[3], [999], [2, 2], '2'] as $ids) {
            $this->putJson('/_test/employee-branch-access', ['additional_branch_ids' => $ids])->assertUnprocessable();
        }
        $this->assertNull(User::find(2)->additional_branch_ids);
    }

    public function test_employee_cannot_grant_or_remove_branch_access(): void
    {
        $this->canManage = false;
        foreach ([['additional_branch_ids' => [2]], ['additional_branch_ids' => []], ['branch_access_present' => 1]] as $data) {
            $this->putJson('/_test/employee-branch-access', $data)->assertForbidden();
            $this->postJson('/_test/employee-branch-access/create', $data)->assertForbidden();
        }
        $this->putJson('/_test/employee-branch-access', [])->assertOk();
    }

    public function test_field_prefills_assignments_and_is_hidden_without_permission(): void
    {
        $data = ['additionalAccessBranches' => DB::table('branches')->where('company_id', 1)->get(),
            'userDetail' => (new User)->forceFill(['additional_branch_ids' => [2]]),
            'errors' => new \Illuminate\Support\ViewErrorBag];
        $html = view('admin.employees.common.branch-access', $data)->render();
        $this->assertStringContainsString('name="additional_branch_ids[]" multiple', $html);
        $this->assertStringContainsString('value="2" selected', $html);
        $this->assertStringNotContainsString('Other company', $html);
        $this->canManage = false;
        $this->assertStringNotContainsString('additional_branch_ids', view('admin.employees.common.branch-access', $data)->render());
    }
}

class BranchAccessCreateRequest extends UserCreateRequest
{
    public function rules() { return $this->branchAccessRules(); }
}

class BranchAccessUpdateRequest extends UserUpdateRequest
{
    public function rules() { return $this->branchAccessRules(); }
}

class HomeBranchUpdateRequest extends UserUpdateRequest
{
    public function rules() { return $this->branchAccessRules() + ['branch_id' => 'nullable|integer']; }
}
