<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\DepartmentController;
use App\Models\Admin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DepartmentEditModalTest extends TestCase
{
    private bool $allowed = true;

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
        Schema::create('app_settings', function (Blueprint $t) {$t->string('slug'); $t->boolean('status');});
        Schema::create('companies', function (Blueprint $t) {$t->id(); $t->string('name'); $t->string('logo')->nullable();});
        Schema::create('branches', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->integer('company_id'); $t->boolean('is_active')->default(true);
        });
        Schema::create('departments', function (Blueprint $t) {
            $t->id(); $t->string('dept_name'); $t->string('slug')->nullable();
            $t->integer('company_id'); $t->integer('branch_id'); $t->integer('dept_head_id')->nullable();
            $t->string('address')->nullable(); $t->string('phone')->nullable();
            $t->boolean('is_active')->default(true);
            $t->integer('created_by')->nullable(); $t->integer('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->integer('company_id'); $t->integer('branch_id')->nullable();
            $t->integer('department_id')->nullable(); $t->string('avatar')->nullable();
            $t->string('status')->default('pending'); $t->boolean('is_active')->default(true); $t->softDeletes();
        });
        Schema::create('admins', function (Blueprint $t) {$t->id(); $t->string('name'); $t->string('email')->nullable(); $t->boolean('is_active')->default(true);});
        Schema::create('theme_settings', function (Blueprint $t) {
            $t->id(); $t->string('primary_color')->nullable(); $t->string('hover_color')->nullable();
            $t->string('dark_primary_color')->nullable(); $t->string('dark_hover_color')->nullable(); $t->timestamps();
        });
        DB::table('companies')->insert(['id' => 1, 'name' => 'KY']);
        DB::table('branches')->insert(['id' => 1, 'name' => 'Main', 'company_id' => 1, 'is_active' => 1]);
        DB::table('departments')->insert([
            'id' => 1, 'dept_name' => 'HR', 'slug' => 'hr', 'company_id' => 1, 'branch_id' => 1,
            'address' => '12 Street', 'phone' => '012345678', 'is_active' => 1,
        ]);
        DB::table('users')->insert([
            'id' => 1, 'name' => 'Dara', 'company_id' => 1, 'branch_id' => 1, 'department_id' => 1,
            'status' => 'verified', 'is_active' => 1,
        ]);
        $this->actingAs((new Admin())->forceFill(['id' => 1, 'company_id' => 1]), 'admin');
        Gate::before(fn () => $this->allowed);
        Route::middleware('web')->group(function () {
            Route::get('/_test/departments', [DepartmentController::class, 'index']);
            Route::get('/_test/departments/{id}/edit', [DepartmentController::class, 'edit']);
            Route::put('/_test/departments/{id}', [DepartmentController::class, 'update']);
        });
    }

    private function payload(array $changes = []): array
    {
        return $changes + [
            'dept_name' => 'HR',
            'address' => '12 Street',
            'phone' => '012345678',
            'branch_id' => [1],
            'is_active' => 1,
        ];
    }

    public function test_index_exposes_department_edit_modal_trigger_and_shell(): void
    {
        $this->get('/_test/departments')->assertOk()
            ->assertSee('data-open-department-modal', false)
            ->assertSee('id="departmentActionModal"', false)
            ->assertSee('data-department-action-frame', false)
            ->assertSee('department-action-modal.js', false)
            ->assertSee('id="departmentDetailsEdit" data-open-department-modal', false)
            ->assertSee(route('admin.departments.edit', 1), false);
    }

    public function test_edit_with_department_modal_renders_embedded_form_without_chrome(): void
    {
        $this->get('/_test/departments/1/edit?department_modal=1')->assertOk()
            ->assertSee('name="department_modal" value="1"', false)
            ->assertSee('.page-wrapper { margin-left: 0 !important', false)
            ->assertDontSee('page-breadcrumb', false)
            ->assertDontSee('<nav class="sidebar">', false);

        $this->get('/_test/departments/1/edit')->assertOk()
            ->assertSee('page-breadcrumb', false)
            ->assertSee('<nav class="sidebar">', false)
            ->assertDontSee('name="department_modal"', false);
    }

    public function test_update_from_modal_returns_modal_saved_view_and_updates_department(): void
    {
        $this->put('/_test/departments/1', $this->payload(['dept_name' => 'Renamed HR', 'department_modal' => '1']))
            ->assertOk()
            ->assertSee('department-action-saved', false)
            ->assertSee(route('admin.departments.index'), false);

        $this->assertDatabaseHas('departments', ['id' => 1, 'dept_name' => 'Renamed HR', 'updated_by' => 1]);
    }

    public function test_update_without_modal_still_redirects_to_index(): void
    {
        $this->put('/_test/departments/1', $this->payload(['dept_name' => 'Plain Update']))
            ->assertRedirect(route('admin.departments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('departments', ['id' => 1, 'dept_name' => 'Plain Update']);
    }

    public function test_modal_update_validation_errors_stay_on_the_form(): void
    {
        $this->put('/_test/departments/1', $this->payload(['dept_name' => '', 'department_modal' => '1']))
            ->assertRedirect()
            ->assertSessionHasErrors('dept_name');

        $this->assertDatabaseHas('departments', ['id' => 1, 'dept_name' => 'HR']);
    }
}
