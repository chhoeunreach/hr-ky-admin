<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\DepartmentController;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepartmentDeletionTest extends TestCase
{
    private array $abilities = ['delete_department', 'delete_post'];

    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', ':memory:');
            $app['config']->set('database.connections.sqlite.foreign_key_constraints', true);
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
            $table->id(); $table->string('dept_name'); $table->integer('company_id'); $table->foreignId('branch_id')->constrained('branches');
        });
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); $table->string('post_name'); $table->boolean('is_active')->default(true);
            $table->foreignId('dept_id')->constrained('departments');
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->boolean('is_active')->default(true);
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('post_id')->nullable()->constrained('posts'); $table->softDeletes();
        });
        Schema::create('history_records', function (Blueprint $table) {
            $table->id(); $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('post_id')->nullable()->constrained('posts');
        });
        DB::table('branches')->insert([['id' => 1, 'name' => 'Main'], ['id' => 2, 'name' => 'Other'], ['id' => 3, 'name' => 'Outside']]);
        DB::table('departments')->insert([
            ['id' => 1, 'dept_name' => 'Sales', 'company_id' => 1, 'branch_id' => 1],
            ['id' => 2, 'dept_name' => 'Sales', 'company_id' => 1, 'branch_id' => 2],
            ['id' => 3, 'dept_name' => 'Finance', 'company_id' => 1, 'branch_id' => 1],
            ['id' => 4, 'dept_name' => 'Sales', 'company_id' => 2, 'branch_id' => 3],
        ]);
        DB::table('posts')->insert([
            ['id' => 10, 'post_name' => 'Sales staff', 'dept_id' => 1, 'is_active' => true],
            ['id' => 11, 'post_name' => 'Sales manager', 'dept_id' => 2, 'is_active' => false],
            ['id' => 12, 'post_name' => 'Finance staff', 'dept_id' => 3, 'is_active' => true],
        ]);
        $this->actingAs((new User())->forceFill(['id' => 99, 'company_id' => 1]));
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        Route::match(['get', 'delete'], '/_test/departments/delete/{id}', [DepartmentController::class, 'delete']);
    }

    public function test_preview_lists_active_and_inactive_positions_and_never_deletes(): void
    {
        $this->getJson('/_test/departments/delete/1')->assertOk()
            ->assertJsonPath('positions_count', 2)->assertJsonPath('departments_count', 2)
            ->assertJsonPath('can_remove_positions', true)->assertJsonCount(0, 'blockers')
            ->assertJsonFragment(['name' => 'Sales manager', 'branch' => 'Other', 'active' => false]);
        $this->assertSame(4, DB::table('departments')->count());
        $this->assertSame(3, DB::table('posts')->count());
    }

    public function test_unused_positions_require_an_explicit_removal_option(): void
    {
        $this->deleteJson('/_test/departments/delete/1')->assertUnprocessable();
        $this->assertSame(4, DB::table('departments')->count());
        $this->assertSame(3, DB::table('posts')->count());
    }

    public function test_confirmed_removal_deletes_only_the_target_departments_and_unused_positions(): void
    {
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertOk();
        $this->assertEquals([3, 4], DB::table('departments')->orderBy('id')->pluck('id')->all());
        $this->assertEquals([12], DB::table('posts')->pluck('id')->all());
    }

    public function test_a_department_without_dependencies_can_be_deleted_without_removing_positions(): void
    {
        $this->deleteJson('/_test/departments/delete/4')->assertNotFound();
        DB::table('departments')->insert(['id' => 5, 'dept_name' => 'Empty', 'company_id' => 1, 'branch_id' => 1]);
        $this->deleteJson('/_test/departments/delete/5')->assertOk();
        $this->assertFalse(DB::table('departments')->where('id', 5)->exists());
    }

    public function test_soft_deleted_and_inactive_employee_assignments_still_block_deletion(): void
    {
        DB::table('users')->insert(['name' => 'Former employee', 'department_id' => 1, 'is_active' => false, 'deleted_at' => now()]);
        $this->getJson('/_test/departments/delete/1')->assertOk()->assertJsonPath('blockers.0.count', 1);
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertUnprocessable();
        $this->assertSame(4, DB::table('departments')->count());
        $this->assertSame(3, DB::table('posts')->count());
        $this->assertSame(1, DB::table('users')->count());
    }

    public function test_employees_assigned_only_to_a_linked_position_are_protected(): void
    {
        DB::table('users')->insert(['name' => 'Employee', 'department_id' => 3, 'post_id' => 11]);
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertUnprocessable();
        $this->assertTrue(DB::table('posts')->where('id', 11)->exists());
    }

    public function test_department_history_links_are_protected(): void
    {
        DB::table('history_records')->insert(['department_id' => 2]);
        $this->getJson('/_test/departments/delete/1')->assertOk()->assertJsonPath('blockers.0.label', 'History Records');
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertUnprocessable();
        $this->assertSame(1, DB::table('history_records')->count());
        $this->assertSame(4, DB::table('departments')->count());
    }

    public function test_position_history_links_are_protected(): void
    {
        DB::table('history_records')->insert(['post_id' => 11]);
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertUnprocessable();
        $this->assertSame(3, DB::table('posts')->count());
        $this->assertSame(1, DB::table('history_records')->count());
    }

    public function test_reassign_then_refresh_clears_the_block_and_allows_deletion(): void
    {
        DB::table('users')->insert(['name' => 'Employee', 'department_id' => 1, 'post_id' => 10]);
        $this->getJson('/_test/departments/delete/1')->assertOk()->assertJsonCount(1, 'blockers');
        DB::table('users')->update(['department_id' => 3, 'post_id' => 12]);
        $this->getJson('/_test/departments/delete/1')->assertOk()->assertJsonCount(0, 'blockers');
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertOk();
        $this->assertSame(1, DB::table('users')->where('department_id', 3)->where('post_id', 12)->count());
    }

    public function test_new_assignments_after_preview_are_rechecked_before_deletion(): void
    {
        $this->getJson('/_test/departments/delete/1')->assertOk()->assertJsonCount(0, 'blockers');
        DB::table('users')->insert(['name' => 'New employee', 'department_id' => 2]);
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertUnprocessable();
        $this->assertSame(4, DB::table('departments')->count());
    }

    public function test_removing_positions_requires_position_delete_permission(): void
    {
        $this->abilities = ['delete_department'];
        $this->getJson('/_test/departments/delete/1')->assertOk()->assertJsonPath('can_remove_positions', false);
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertForbidden();
        $this->assertSame(4, DB::table('departments')->count());
    }

    public function test_delete_department_permission_is_required_for_preview_and_delete(): void
    {
        $this->abilities = [];
        $this->getJson('/_test/departments/delete/1')->assertForbidden();
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertForbidden();
    }

    public function test_departments_from_another_company_are_not_accessible(): void
    {
        $this->getJson('/_test/departments/delete/4')->assertNotFound();
        $this->deleteJson('/_test/departments/delete/4', ['remove_positions' => true])->assertNotFound();
    }

    public function test_failure_rolls_back_deleted_positions_and_departments(): void
    {
        Department::deleting(function () {throw new \RuntimeException('Simulated failure');});
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertStatus(500);
        $this->assertSame(4, DB::table('departments')->count());
        $this->assertSame(3, DB::table('posts')->count());
    }

    private function leaveRecords(): void
    {
        Schema::create('leave_requests_master', function (Blueprint $table) {
            $table->id(); $table->foreignId('department_id')->nullable()->constrained('departments'); $table->string('status');
        });
        Schema::create('time_leaves', function (Blueprint $table) {
            $table->id(); $table->foreignId('department_id')->nullable()->constrained('departments'); $table->integer('minutes');
        });
        foreach (range(1, 23) as $index) DB::table('leave_requests_master')->insert(['department_id' => 1, 'status' => 'approved']);
        DB::table('time_leaves')->insert(['department_id' => 2, 'minutes' => 60]);
    }
    public function test_optional_leave_links_are_previewed_as_unlinkable(): void
    {
        $this->leaveRecords();
        $this->getJson('/_test/departments/delete/1')->assertOk()->assertJsonFragment([
            'label' => 'Leave Requests Master', 'count' => 23, 'can_unlink' => true,
        ])->assertJsonFragment(['label' => 'Time Leaves', 'count' => 1, 'can_unlink' => true]);
    }
    public function test_unlink_and_delete_keeps_all_leave_records_and_unrelated_links(): void
    {
        $this->leaveRecords();
        DB::table('leave_requests_master')->insert(['department_id' => 3, 'status' => 'pending']);
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true, 'unlink_records' => true])->assertOk();
        $this->assertSame(23, DB::table('leave_requests_master')->whereNull('department_id')->where('status', 'approved')->count());
        $this->assertSame(1, DB::table('leave_requests_master')->where('department_id', 3)->where('status', 'pending')->count());
        $this->assertSame(1, DB::table('time_leaves')->whereNull('department_id')->where('minutes', 60)->count());
        $this->assertSame(2, DB::table('departments')->count());
        $this->assertSame(1, DB::table('posts')->count());
    }
    public function test_unlink_is_opt_in_and_no_leave_links_change_without_consent(): void
    {
        $this->leaveRecords();
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true])->assertUnprocessable();
        $this->assertSame(23, DB::table('leave_requests_master')->where('department_id', 1)->count());
    }
    public function test_unlink_never_detaches_employee_assignments(): void
    {
        $this->leaveRecords();
        DB::table('users')->insert(['name' => 'Employee', 'department_id' => 1, 'post_id' => 10]);
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true, 'unlink_records' => true])->assertUnprocessable();
        $this->assertSame(1, DB::table('users')->where('department_id', 1)->where('post_id', 10)->count());
        $this->assertSame(23, DB::table('leave_requests_master')->where('department_id', 1)->count());
    }
    public function test_required_history_links_cannot_be_cleared(): void
    {
        Schema::create('required_history', function (Blueprint $table) {$table->id(); $table->foreignId('department_id')->constrained('departments');});
        DB::table('required_history')->insert(['department_id' => 1]);
        $this->getJson('/_test/departments/delete/1')->assertOk()->assertJsonFragment(['label' => 'Required History', 'count' => 1, 'can_unlink' => false]);
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true, 'unlink_records' => true])->assertUnprocessable();
        $this->assertSame(1, DB::table('required_history')->where('department_id', 1)->count());
    }
    public function test_unlink_clears_only_matching_department_and_position_references(): void
    {
        DB::table('history_records')->insert([['department_id' => 3, 'post_id' => 11], ['department_id' => 2, 'post_id' => 12]]);
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true, 'unlink_records' => true])->assertOk();
        $this->assertSame(1, DB::table('history_records')->where('department_id', 3)->whereNull('post_id')->count());
        $this->assertSame(1, DB::table('history_records')->whereNull('department_id')->where('post_id', 12)->count());
    }
    public function test_failed_deletion_rolls_back_unlinked_leave_references(): void
    {
        $this->leaveRecords();
        Department::deleting(function () {throw new \RuntimeException('Simulated failure');});
        $this->deleteJson('/_test/departments/delete/1', ['remove_positions' => true, 'unlink_records' => true])->assertStatus(500);
        $this->assertSame(23, DB::table('leave_requests_master')->where('department_id', 1)->count());
        $this->assertSame(1, DB::table('time_leaves')->where('department_id', 2)->count());
        $this->assertSame(3, DB::table('posts')->count());
    }
}
