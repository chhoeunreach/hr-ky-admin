<?php

namespace Tests\Feature;

use App\Helpers\AppHelper;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssignedWorkBranchAccessTest extends TestCase
{
    private const ROLE_ASSISTANT = 5;

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

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(1);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('permission_key');
            $table->integer('permission_groups_id')->nullable();
        });

        Schema::create('permission_roles', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->integer('role_id');
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('branch_id')->nullable();
            $table->integer('role_id')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id')->nullable();
            $table->string('name');
            $table->integer('branch_id')->nullable();
            $table->string('status')->nullable();
            $table->string('priority')->nullable();
            $table->boolean('is_active')->default(1);
            $table->dateTime('end_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('branch_id')->nullable();
            $table->string('status')->nullable();
            $table->string('priority')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('assigned_members', function (Blueprint $table) {
            $table->id();
            $table->integer('member_id');
            $table->integer('assignable_id');
            $table->string('assignable_type');
        });

        Schema::create('project_team_leaders', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->integer('leader_id');
        });

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin', 'slug' => 'admin', 'is_active' => 1],
            ['id' => 2, 'name' => 'employee', 'slug' => 'employee', 'is_active' => 1],
            ['id' => self::ROLE_ASSISTANT, 'name' => 'hr assistance', 'slug' => 'hr-assistance', 'is_active' => 1],
        ]);

        DB::table('permissions')->insert([
            [
                'id' => 1,
                'name' => 'Access Assigned Work Across Branches',
                'permission_key' => AppHelper::ASSIGNED_WORK_BRANCH_ACCESS_PERMISSION,
                'permission_groups_id' => 2,
            ],
            [
                'id' => 2,
                'name' => 'Access All Branches',
                'permission_key' => AppHelper::ALL_BRANCH_ACCESS_PERMISSION,
                'permission_groups_id' => 2,
            ],
        ]);

        DB::table('branches')->insert([
            ['id' => 1, 'name' => 'Branch A'],
            ['id' => 2, 'name' => 'Branch B'],
        ]);

        DB::table('users')->insert([
            'id' => 10,
            'name' => 'Assistant',
            'branch_id' => 1,
            'role_id' => self::ROLE_ASSISTANT,
            'status' => 'verified',
            'is_active' => 1,
        ]);

        DB::table('projects')->insert([
            ['id' => 1, 'name' => 'Assigned other-branch project', 'branch_id' => 2, 'is_active' => 1],
            ['id' => 2, 'name' => 'Unassigned other-branch project', 'branch_id' => 2, 'is_active' => 1],
            ['id' => 3, 'name' => 'Own-branch project', 'branch_id' => 1, 'is_active' => 1],
            ['id' => 4, 'name' => 'Led other-branch project', 'branch_id' => 2, 'is_active' => 1],
        ]);

        DB::table('tasks')->insert([
            ['id' => 1, 'name' => 'Assigned other-branch task', 'branch_id' => 2, 'project_id' => 1, 'is_active' => 1],
            ['id' => 2, 'name' => 'Unassigned other-branch task', 'branch_id' => 2, 'project_id' => 2, 'is_active' => 1],
            ['id' => 3, 'name' => 'Own-branch task', 'branch_id' => 1, 'project_id' => 3, 'is_active' => 1],
            ['id' => 4, 'name' => 'Led other-branch task', 'branch_id' => 2, 'project_id' => 4, 'is_active' => 1],
        ]);

        DB::table('assigned_members')->insert([
            ['member_id' => 10, 'assignable_id' => 1, 'assignable_type' => 'task'],
            ['member_id' => 10, 'assignable_id' => 1, 'assignable_type' => 'project'],
        ]);

        DB::table('project_team_leaders')->insert([
            ['project_id' => 4, 'leader_id' => 10],
        ]);

        $this->actingAs((new User())->forceFill([
            'id' => 10,
            'name' => 'Assistant',
            'branch_id' => 1,
            'role_id' => self::ROLE_ASSISTANT,
        ]));

        Model::clearBootedModels();
    }

    public function test_employee_branch_assignments_include_home_and_selected_branches_only(): void
    {
        Schema::table('branches', fn (Blueprint $table) => $table->integer('company_id')->default(1));
        Schema::table('users', fn (Blueprint $table) => $table->integer('company_id')->default(1));
        (require database_path('migrations/2026_11_13_000005_add_additional_branch_ids_to_users.php'))->up();
        DB::table('branches')->insert([
            ['id' => 3, 'name' => 'Unassigned branch', 'company_id' => 1],
            ['id' => 4, 'name' => 'Other company', 'company_id' => 2],
        ]);
        DB::table('projects')->insert(['id' => 5, 'name' => 'Not accessible', 'branch_id' => 3]);
        DB::table('tasks')->insert(['id' => 5, 'name' => 'Not accessible', 'branch_id' => 3]);
        auth()->user()->forceFill(['company_id' => 1, 'additional_branch_ids' => [2, 4]]);
        Model::clearBootedModels();
        $this->assertSame([1, 2], AppHelper::accessibleBranchIds());
        $this->assertFalse(AppHelper::canAccessAllBranches());
        $this->assertEquals([1, 2], \App\Models\Branch::orderBy('id')->pluck('id')->all());
        $this->assertEquals([1, 2, 3, 4], Project::orderBy('id')->pluck('id')->all());
        $this->assertEquals([1, 2, 3, 4], Task::orderBy('id')->pluck('id')->all());
        $this->assertTrue(AppHelper::canAccessBranch(2));
        $this->assertFalse(AppHelper::canAccessBranch(3));
        $this->assertSame([2], AppHelper::branchIdsForFilter(2));
        $this->assertSame([], AppHelper::branchIdsForFilter(3));
        $this->assertSame(2, AppHelper::employeeBranchFilter(2));
        auth()->user()->forceFill(['additional_branch_ids' => []]);
        $this->assertEquals([3], Task::orderBy('id')->pluck('id')->all());
        $this->assertSame(1, AppHelper::employeeBranchFilter(2));
    }

    public function test_assigned_cross_branch_work_stays_hidden_without_the_permission(): void
    {
        $this->assertFalse(AppHelper::canAccessAssignedWorkAcrossBranches());
        $this->assertEquals([3], Task::orderBy('id')->pluck('id')->all());
        $this->assertEquals([3], Project::orderBy('id')->pluck('id')->all());
    }

    public function test_role_permission_reveals_assigned_work_from_other_branches(): void
    {
        DB::table('permission_roles')->insert([
            'permission_id' => 1,
            'role_id' => self::ROLE_ASSISTANT,
        ]);

        Model::clearBootedModels();

        $this->assertTrue(AppHelper::canAccessAssignedWorkAcrossBranches());
        $this->assertEquals([1, 3, 4], Task::orderBy('id')->pluck('id')->all());
        $this->assertEquals([1, 3, 4], Project::orderBy('id')->pluck('id')->all());
    }

    public function test_full_branch_access_is_disabled_without_the_permission(): void
    {
        $this->assertFalse(AppHelper::canAccessAllBranches());
    }

    public function test_full_branch_access_permission_reveals_every_branch_record(): void
    {
        DB::table('permission_roles')->insert([
            'permission_id' => 2,
            'role_id' => self::ROLE_ASSISTANT,
        ]);

        Model::clearBootedModels();

        $this->assertTrue(AppHelper::canAccessAllBranches());
        $this->assertEquals([1, 2, 3, 4], Task::orderBy('id')->pluck('id')->all());
        $this->assertEquals([1, 2, 3, 4], Project::orderBy('id')->pluck('id')->all());
    }
}
