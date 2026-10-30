<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\EmployeeProfileController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeProfileDirectoryTest extends TestCase
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'english_name', 'username', 'employee_code', 'email', 'phone', 'joining_date'] as $column) {
                $table->string($column)->nullable();
            }
            foreach (['branch_id', 'department_id', 'post_id', 'supervisor_id'] as $column) {
                $table->integer($column)->nullable();
            }
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('logo')->nullable(); $table->softDeletes();
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); $table->string('dept_name'); $table->integer('branch_id')->nullable(); $table->softDeletes();
        });
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); $table->string('post_name'); $table->softDeletes();
        });
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id(); $table->integer('employee_id');
            $table->string('employment_status')->nullable(); $table->date('last_working_date')->nullable();
        });
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id(); $table->integer('employee_id');
            foreach (['review_type', 'period_end', 'review_date', 'next_review_date', 'status'] as $column) $table->string($column)->nullable();
        });
        $this->actingAs((new User())->forceFill(['id' => 999, 'name' => 'HR']));
        Gate::before(fn ($user, $ability) => $ability === 'employee.profile.view');
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'សុខ ដារ៉ា', 'english_name' => 'Sok Dara', 'employee_code' => 'KY-00474', 'is_active' => 1],
            ['id' => 2, 'name' => 'Sok Other', 'english_name' => 'Other Employee', 'employee_code' => 'KY-00475', 'is_active' => 1],
            ['id' => 3, 'name' => 'Inactive Employee', 'english_name' => 'Inactive', 'employee_code' => 'KY-00476', 'is_active' => 0],
        ]);
    }

    private function directory(array $query = [])
    {
        $request = Request::create('/admin/employees/profile-directory', 'GET', $query);
        $request->headers->set('X-Employee-Profile-Results', '1');
        app()->instance('request', $request);
        return app(EmployeeProfileController::class)->index($request);
    }

    private function ids(array $query = []): array
    {
        return $this->directory($query)->getData()['employees']->pluck('id')->all();
    }

    public function test_active_employees_remain_searchable_after_an_empty_profile_is_created(): void
    {
        $this->assertSame([1], $this->ids(['search' => '00474']));
        DB::table('employee_profiles')->insert(['employee_id' => 1, 'employment_status' => null]);
        DB::table('users')->where('id', 1)->update(['supervisor_id' => 2]);
        $this->assertSame([1], $this->ids(['search' => '00474']));
        DB::table('employee_profiles')->where('employee_id', 1)->update(['employment_status' => '']);
        $this->assertSame([1], $this->ids(['search' => '00474']));
    }

    public function test_explicit_status_overrides_the_account_status(): void
    {
        DB::table('employee_profiles')->insert(['employee_id' => 1, 'employment_status' => 'resigned']);
        $this->assertSame([], $this->ids(['search' => '00474']));
        $this->assertSame([1], $this->ids(['search' => '00474', 'employment_status' => 'resigned']));
        $this->assertSame([1], $this->ids(['search' => '00474', 'employment_status' => '']));
    }

    public function test_inactive_fallback_works_with_a_null_profile_status(): void
    {
        DB::table('employee_profiles')->insert(['employee_id' => 3]);
        $this->assertSame([], $this->ids(['search' => '00476']));
        $this->assertSame([3], $this->ids(['search' => '00476', 'employment_status' => 'inactive']));
    }

    public function test_names_allow_extra_spaces_and_words_across_both_languages(): void
    {
        $this->assertSame([1], $this->ids(['search' => '  Sok   Dara  ']));
        $this->assertSame([1], $this->ids(['search' => 'សុខ Dara']));
        $this->assertSame([], $this->ids(['search' => 'Sok Missing']));
    }

    public function test_codes_match_with_or_without_the_separator_and_zero_is_searchable(): void
    {
        $this->assertSame([1], $this->ids(['search' => 'KY00474']));
        $this->assertSame([1], $this->ids(['search' => 'KY-00474']));
        $this->assertSame([2, 1], $this->ids(['search' => '0']));
    }

    public function test_search_still_respects_explicit_branch_and_department_filters(): void
    {
        DB::table('branches')->insert([['id' => 1, 'name' => 'Head Office'], ['id' => 2, 'name' => 'Other Branch']]);
        DB::table('departments')->insert(['id' => 1, 'dept_name' => 'Sales', 'branch_id' => 1]);
        DB::table('users')->where('id', 1)->update(['branch_id' => 1, 'department_id' => 1]);
        $this->assertSame([1], $this->ids(['search' => '00474', 'branch_id' => 1, 'department_ids' => [1]]));
        $this->assertSame([], $this->ids(['search' => '00474', 'branch_id' => 2]));
    }

    public function test_fragment_renders_employee_results_without_the_search_form(): void
    {
        $view = $this->directory(['search' => '00474']);
        $this->assertSame('admin.employees.profile.partials.directory-results', $view->name());
        $html = $view->render();
        $this->assertStringContainsString('id="employeeProfileResults"', $html);
        $this->assertStringContainsString('KY-00474', $html);
        $this->assertStringNotContainsString('id="employeeProfileSearch"', $html);
    }
}
