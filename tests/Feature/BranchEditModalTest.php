<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\BranchController;
use App\Models\Admin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BranchEditModalTest extends TestCase
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
            $t->id(); $t->string('name'); $t->text('address'); $t->string('phone');
            $t->string('logo')->nullable(); $t->text('payment_qr_codes')->nullable();
            $t->integer('branch_head_id')->nullable(); $t->integer('company_id');
            $t->double('branch_location_latitude')->nullable(); $t->double('branch_location_longitude')->nullable();
            $t->boolean('is_active')->default(true);
            $t->integer('created_by')->nullable(); $t->integer('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->integer('company_id'); $t->integer('branch_id')->nullable();
            $t->string('avatar')->nullable(); $t->string('status')->default('pending');
            $t->boolean('is_active')->default(true); $t->softDeletes();
        });
        Schema::create('admins', function (Blueprint $t) {$t->id(); $t->string('name'); $t->string('email')->nullable(); $t->boolean('is_active')->default(true);});
        Schema::create('theme_settings', function (Blueprint $t) {
            $t->id(); $t->string('primary_color')->nullable(); $t->string('hover_color')->nullable();
            $t->string('dark_primary_color')->nullable(); $t->string('dark_hover_color')->nullable(); $t->timestamps();
        });
        DB::table('companies')->insert(['id' => 1, 'name' => 'KY']);
        DB::table('branches')->insert([
            'id' => 1, 'name' => 'Main', 'address' => '123 Street', 'phone' => '012345678',
            'company_id' => 1, 'branch_location_latitude' => 11.5564, 'branch_location_longitude' => 104.9282,
            'is_active' => 1,
        ]);
        DB::table('users')->insert(['id' => 1, 'name' => 'Dara', 'company_id' => 1, 'branch_id' => 1, 'status' => 'verified', 'is_active' => 1]);
        $this->actingAs((new Admin())->forceFill(['id' => 1, 'company_id' => 1]), 'admin');
        Gate::before(fn () => $this->allowed);
        Route::middleware('web')->group(function () {
            Route::get('/_test/branch', [BranchController::class, 'index']);
            Route::get('/_test/branch/{id}/edit', [BranchController::class, 'edit']);
            Route::put('/_test/branch/{id}', [BranchController::class, 'update']);
        });
    }

    private function payload(array $changes = []): array
    {
        return $changes + [
            'company_id' => 1,
            'name' => 'Main',
            'address' => '123 Street',
            'phone' => '012345678',
            'branch_location_latitude' => 11.5564,
            'branch_location_longitude' => 104.9282,
            'is_active' => 1,
        ];
    }

    public function test_index_exposes_branch_edit_modal_trigger_and_shell(): void
    {
        $this->get('/_test/branch')->assertOk()
            ->assertSee('data-open-branch-modal', false)
            ->assertSee('id="branchActionModal"', false)
            ->assertSee('data-branch-action-frame', false)
            ->assertSee('branch-action-modal.js', false)
            ->assertSee(route('admin.branch.edit', 1), false);
    }

    public function test_edit_with_branch_modal_renders_embedded_form_without_chrome(): void
    {
        $this->get('/_test/branch/1/edit?branch_modal=1')->assertOk()
            ->assertSee('name="branch_modal" value="1"', false)
            ->assertSee('.page-wrapper { margin-left: 0 !important', false)
            ->assertDontSee('page-breadcrumb', false)
            ->assertDontSee('<nav class="sidebar">', false);

        $this->get('/_test/branch/1/edit')->assertOk()
            ->assertSee('page-breadcrumb', false)
            ->assertSee('<nav class="sidebar">', false)
            ->assertDontSee('name="branch_modal"', false);
    }

    public function test_update_from_modal_returns_modal_saved_view_and_updates_branch(): void
    {
        $this->put('/_test/branch/1', $this->payload(['name' => 'Renamed Main', 'branch_modal' => '1']))
            ->assertOk()
            ->assertSee('branch-action-saved', false)
            ->assertSee(route('admin.branch.index'), false);

        $this->assertDatabaseHas('branches', ['id' => 1, 'name' => 'Renamed Main', 'updated_by' => 1]);
    }

    public function test_update_without_modal_still_redirects_to_index(): void
    {
        $this->put('/_test/branch/1', $this->payload(['name' => 'Plain Update']))
            ->assertRedirect(route('admin.branch.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('branches', ['id' => 1, 'name' => 'Plain Update']);
    }

    public function test_modal_update_validation_errors_stay_on_the_form(): void
    {
        $this->put('/_test/branch/1', $this->payload(['name' => '', 'branch_modal' => '1']))
            ->assertRedirect()
            ->assertSessionHasErrors('name');

        $this->assertDatabaseHas('branches', ['id' => 1, 'name' => 'Main']);
    }
}
