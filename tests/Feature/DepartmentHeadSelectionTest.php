<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\DepartmentController;
use App\Models\Department;
use App\Models\User;
use App\Requests\Department\DepartmentStoreRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class DepartmentHeadSelectionTest extends TestCase
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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            foreach (['company_id', 'branch_id', 'dept_head_id', 'created_by', 'updated_by'] as $column) {
                $table->integer($column)->nullable();
            }
            foreach (['dept_name', 'slug', 'address', 'phone'] as $column) {
                $table->string($column);
            }
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('company_id');
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('company_id'); $table->integer('branch_id');
            $table->boolean('is_active')->default(true); $table->string('status')->default('verified'); $table->softDeletes();
        });
        DB::table('branches')->insert([
            ['id' => 1, 'name' => 'Main', 'company_id' => 1],
            ['id' => 2, 'name' => 'Other', 'company_id' => 1],
            ['id' => 3, 'name' => 'Outside', 'company_id' => 2],
        ]);
        DB::table('users')->insert([
            ['id' => 20, 'name' => 'First Head', 'company_id' => 1, 'branch_id' => 1],
            ['id' => 21, 'name' => 'Second Head', 'company_id' => 1, 'branch_id' => 2],
            ['id' => 22, 'name' => 'Outside Head', 'company_id' => 2, 'branch_id' => 3],
        ]);
        $this->actingAs((new User())->forceFill(['id' => 1, 'company_id' => 1]));
        Gate::before(fn () => true);
    }

    private function requestFor(array $branches, array $heads = [1 => 20, 2 => 21]): DepartmentStoreRequest
    {
        $request = Mockery::mock(DepartmentStoreRequest::class);
        $request->shouldReceive('validated')->once()->andReturn([
            'dept_name' => 'Sales', 'address' => 'Office', 'phone' => '123',
            'branch_id' => $branches, 'dept_head_ids' => array_intersect_key($heads, array_flip($branches)), 'is_active' => 1,
        ]);
        return $request;
    }

    public function test_each_branch_saves_its_own_selected_head(): void
    {
        app(DepartmentController::class)->store($this->requestFor([1, 2]));

        $this->assertEquals([1 => 20, 2 => 21], Department::orderBy('branch_id')->pluck('dept_head_id', 'branch_id')->all());
        $this->assertEquals([1, 2], Department::orderBy('branch_id')->pluck('branch_id')->all());
    }

    public function test_grouped_department_can_keep_its_head_when_updated(): void
    {
        $controller = app(DepartmentController::class);
        $controller->store($this->requestFor([1, 2]));
        session()->forget('danger');
        $controller->update($this->requestFor([1, 2]), Department::first()->id);

        $this->assertFalse(session()->has('danger'));
        $this->assertEquals([1 => 20, 2 => 21], Department::orderBy('branch_id')->pluck('dept_head_id', 'branch_id')->all());
    }

    public function test_head_of_an_unrelated_department_is_still_rejected(): void
    {
        Department::create([
            'company_id' => 1, 'branch_id' => 3, 'dept_name' => 'Finance',
            'slug' => 'finance', 'address' => 'Office', 'phone' => '123', 'dept_head_id' => 20,
        ]);
        app(DepartmentController::class)->store($this->requestFor([1, 2]));

        $this->assertTrue(session()->has('danger'));
        $this->assertSame(0, Department::where('dept_name', 'Sales')->count());
    }

    public function test_single_branch_still_saves_selected_head(): void
    {
        app(DepartmentController::class)->store($this->requestFor([1]));

        $this->assertSame(1, Department::where('branch_id', 1)->where('dept_head_id', 20)->count());
    }

    public function test_edit_form_preserves_heads_and_separates_employee_options_by_branch(): void
    {
        $controller = app(DepartmentController::class);
        $controller->store($this->requestFor([1, 2]));
        $data = $controller->edit(Department::first()->id)->getData();

        $this->assertEquals([1 => 20, 2 => 21], $data['departmentHeadsByBranch']);
        $this->assertSame([20], $data['headEmployeesByBranch']->get(1)->pluck('id')->all());
        $this->assertSame([21], $data['headEmployeesByBranch']->get(2)->pluck('id')->all());
        $this->assertFalse($data['headEmployeesByBranch']->has(3));

        $html = view('admin.department.common.form', $data + ['errors' => new \Illuminate\Support\ViewErrorBag()])->render();
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame('20', $xpath->query('//select[@name="dept_head_ids[1]"]/option[@selected]')->item(0)->getAttribute('value'));
        $this->assertSame('21', $xpath->query('//select[@name="dept_head_ids[2]"]/option[@selected]')->item(0)->getAttribute('value'));
        $this->assertSame(0, $xpath->query('//select[@name="dept_head_ids[1]"]/option[@value="21"]')->length);
        $this->assertSame(0, $xpath->query('//select[@name="dept_head_ids[2]"]/option[@value="20"]')->length);
    }

    public function test_clearing_one_branch_head_keeps_the_other_branch_head(): void
    {
        $controller = app(DepartmentController::class);
        $controller->store($this->requestFor([1, 2]));
        $controller->update($this->requestFor([1, 2], [1 => null, 2 => 21]), Department::first()->id);

        $this->assertEquals([1 => null, 2 => 21], Department::orderBy('branch_id')->pluck('dept_head_id', 'branch_id')->all());
    }

    public function test_a_failed_assignment_rolls_back_changes_to_other_branches(): void
    {
        $controller = app(DepartmentController::class);
        $controller->store($this->requestFor([1, 2]));
        Department::create([
            'company_id' => 1, 'branch_id' => 2, 'dept_name' => 'Finance',
            'slug' => 'finance', 'address' => 'Office', 'phone' => '123', 'dept_head_id' => 23,
        ]);
        $controller->update($this->requestFor([1, 2], [1 => null, 2 => 23]), Department::where('dept_name', 'Sales')->first()->id);

        $this->assertTrue(session()->has('danger'));
        $this->assertEquals([1 => 20, 2 => 21], Department::where('dept_name', 'Sales')->orderBy('branch_id')->pluck('dept_head_id', 'branch_id')->all());
    }

    private function validateHeads(array $heads, array $branches = [1, 2])
    {
        $request = DepartmentStoreRequest::create('/', 'POST', [
            'dept_name' => 'Sales', 'address' => 'Office', 'phone' => '123',
            'branch_id' => $branches, 'dept_head_ids' => $heads,
        ]);
        return Validator::make($request->all(), $request->rules());
    }

    public function test_validation_accepts_separate_heads_from_their_own_branches(): void
    {
        $this->assertTrue($this->validateHeads([1 => 20, 2 => 21])->passes());
    }

    public function test_validation_rejects_a_head_from_another_branch(): void
    {
        $validator = $this->validateHeads([1 => 21]);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('dept_head_ids.1'));
    }

    public function test_validation_rejects_a_head_for_an_unselected_branch(): void
    {
        $this->assertTrue($this->validateHeads([2 => 21], [1])->fails());
    }

    public function test_validation_rejects_an_inactive_employee(): void
    {
        DB::table('users')->where('id', 20)->update(['is_active' => false]);
        $this->assertTrue($this->validateHeads([1 => 20])->fails());
    }
}
