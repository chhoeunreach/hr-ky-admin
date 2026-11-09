<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\DepartmentPositionController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepartmentPositionAssignmentTest extends TestCase
{
    private bool $canEdit = true;
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
            $table->id(); $table->string('post_name'); $table->integer('dept_id'); $table->integer('branch_id')->nullable();
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('department_id')->nullable(); $table->integer('post_id')->nullable();
            $table->boolean('is_active')->default(true); $table->softDeletes();
        });
        DB::table('branches')->insert([['id' => 1, 'name' => 'Main'], ['id' => 2, 'name' => 'Other']]);
        foreach ([[1, 'Sales', 1, 1, 1], [2, 'Finance', 1, 1, 1], [3, 'Sales', 1, 2, 1], [4, 'External', 2, 1, 1], [5, 'Inactive', 1, 1, 0], [6, 'Sales', 1, 1, 1]] as [$id, $name, $company, $branch, $active]) {
            DB::table('departments')->insert(['id' => $id, 'dept_name' => $name, 'company_id' => $company, 'branch_id' => $branch, 'is_active' => $active]);
        }
        DB::table('posts')->insert(['id' => 10, 'post_name' => 'Staff', 'dept_id' => 1, 'branch_id' => 1, 'is_active' => false]);
        DB::table('posts')->insert(['id' => 20, 'post_name' => 'Outside', 'dept_id' => 4, 'branch_id' => 1]);
        $this->actingAs((new User())->forceFill(['id' => 99, 'company_id' => 1]));
        Gate::before(fn ($user, $ability) => $ability === 'edit_post' && $this->canEdit);
        Route::get('/_test/position/{position}/assignment', [DepartmentPositionController::class, 'options']);
        Route::patch('/_test/position/{position}/assignment', [DepartmentPositionController::class, 'update']);
    }
    public function test_destination_choices_exclude_other_branches_companies_inactive_and_same_group(): void
    {
        $this->getJson('/_test/position/10/assignment')->assertOk()->assertJsonCount(1, 'departments')->assertJsonPath('departments.0.id', 2);
    }
    public function test_unused_inactive_position_moves_without_deleting_the_position(): void
    {
        $this->patchJson('/_test/position/10/assignment', ['department_id' => 2])->assertOk();
        $this->assertDatabaseHas('posts', ['id' => 10, 'dept_id' => 2, 'branch_id' => 1, 'is_active' => false]);
        $this->assertDatabaseCount('posts', 2);
    }
    public static function invalidDestinations(): array {return [[1], [3], [4], [5], [6], [999]];}
    /** @dataProvider invalidDestinations */
    public function test_invalid_destination_leaves_the_position_unchanged(int $department): void
    {
        $this->patchJson('/_test/position/10/assignment', ['department_id' => $department])->assertUnprocessable();
        $this->assertDatabaseHas('posts', ['id' => 10, 'dept_id' => 1]);
    }
    public function test_positions_with_employee_assignments_must_be_corrected_via_employees_first(): void
    {
        DB::table('users')->insert(['name' => 'Former employee', 'post_id' => 10, 'department_id' => 1, 'is_active' => false, 'deleted_at' => now()]);
        $this->patchJson('/_test/position/10/assignment', ['department_id' => 2])->assertUnprocessable();
        $this->assertDatabaseHas('posts', ['id' => 10, 'dept_id' => 1]);
        $this->assertDatabaseCount('users', 1);
    }
    public function test_duplicate_positions_in_the_destination_are_rejected_without_merging(): void
    {
        DB::table('posts')->insert(['post_name' => 'Staff', 'dept_id' => 2, 'branch_id' => 1]);
        $this->patchJson('/_test/position/10/assignment', ['department_id' => 2])->assertUnprocessable();
        $this->assertDatabaseCount('posts', 3);
        $this->assertDatabaseHas('posts', ['id' => 10, 'dept_id' => 1]);
    }
    public function test_existing_history_links_are_preserved(): void
    {
        Schema::create('history_records', function (Blueprint $table) {
            $table->id(); $table->foreignId('post_id')->constrained('posts'); $table->foreignId('department_id')->constrained('departments');
        });
        DB::table('history_records')->insert(['post_id' => 10, 'department_id' => 1]);
        $this->patchJson('/_test/position/10/assignment', ['department_id' => 2])->assertOk();
        $this->assertDatabaseHas('history_records', ['post_id' => 10, 'department_id' => 1]);
    }
    public function test_foreign_company_position_is_not_accessible(): void
    {
        $this->getJson('/_test/position/20/assignment')->assertNotFound();
        $this->patchJson('/_test/position/20/assignment', ['department_id' => 2])->assertNotFound();
    }
    public function test_position_edit_permission_is_required(): void
    {
        $this->canEdit = false;
        $this->getJson('/_test/position/10/assignment')->assertForbidden();
        $this->patchJson('/_test/position/10/assignment', ['department_id' => 2])->assertForbidden();
    }
}
