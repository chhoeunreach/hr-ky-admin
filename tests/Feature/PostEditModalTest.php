<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\PostController;
use App\Models\Admin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PostEditModalTest extends TestCase
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
        Schema::create('posts', function (Blueprint $t) {
            $t->id(); $t->string('post_name'); $t->integer('branch_id')->nullable(); $t->integer('dept_id')->nullable();
            $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->integer('company_id'); $t->integer('branch_id')->nullable();
            $t->integer('department_id')->nullable(); $t->integer('post_id')->nullable(); $t->string('avatar')->nullable();
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
            'id' => 1, 'dept_name' => 'Engineering', 'slug' => 'engineering', 'company_id' => 1, 'branch_id' => 1,
            'address' => '12 Street', 'phone' => '012345678', 'is_active' => 1,
        ]);
        DB::table('posts')->insert([
            'id' => 1, 'post_name' => 'Developer', 'branch_id' => 1, 'dept_id' => 1, 'is_active' => 1,
        ]);
        DB::table('users')->insert([
            'id' => 1, 'name' => 'Dara', 'company_id' => 1, 'branch_id' => 1, 'department_id' => 1, 'post_id' => 1,
            'status' => 'verified', 'is_active' => 1,
        ]);
        $this->actingAs((new Admin())->forceFill(['id' => 1, 'company_id' => 1]), 'admin');
        Gate::before(fn () => $this->allowed);
        Route::middleware('web')->group(function () {
            Route::get('/_test/posts', [PostController::class, 'index']);
            Route::get('/_test/posts/{id}/edit', [PostController::class, 'edit']);
            Route::put('/_test/posts/{id}', [PostController::class, 'update']);
        });
    }

    private function payload(array $changes = []): array
    {
        return $changes + [
            'post_name' => 'Developer',
            'branch_id' => [1],
            'dept_id' => [1],
            'is_active' => 1,
        ];
    }

    public function test_index_exposes_post_edit_modal_trigger_and_shell(): void
    {
        $this->get('/_test/posts')->assertOk()
            ->assertSee('data-open-post-modal', false)
            ->assertSee('id="postActionModal"', false)
            ->assertSee('data-post-action-frame', false)
            ->assertSee('post-action-modal.js', false)
            ->assertSee(route('admin.posts.edit', 1), false);
    }

    public function test_edit_with_post_modal_renders_embedded_form_without_chrome(): void
    {
        $this->get('/_test/posts/1/edit?post_modal=1')->assertOk()
            ->assertSee('name="post_modal" value="1"', false)
            ->assertSee('.page-wrapper { margin-left: 0 !important', false)
            ->assertDontSee('page-breadcrumb', false)
            ->assertDontSee('<nav class="sidebar">', false);

        $this->get('/_test/posts/1/edit')->assertOk()
            ->assertSee('page-breadcrumb', false)
            ->assertSee('<nav class="sidebar">', false)
            ->assertDontSee('name="post_modal"', false);
    }

    public function test_update_from_modal_returns_modal_saved_view_and_updates_post(): void
    {
        $this->put('/_test/posts/1', $this->payload(['post_name' => 'Senior Developer', 'post_modal' => '1']))
            ->assertOk()
            ->assertSee('post-action-saved', false)
            ->assertSee(route('admin.posts.index'), false);

        $this->assertDatabaseHas('posts', ['id' => 1, 'post_name' => 'Senior Developer']);
    }

    public function test_update_without_modal_still_redirects_to_index(): void
    {
        $this->put('/_test/posts/1', $this->payload(['post_name' => 'Plain Update']))
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', ['id' => 1, 'post_name' => 'Plain Update']);
    }

    public function test_modal_update_validation_errors_stay_on_the_form(): void
    {
        $this->put('/_test/posts/1', $this->payload(['post_name' => '', 'post_modal' => '1']))
            ->assertRedirect()
            ->assertSessionHasErrors('post_name');

        $this->assertDatabaseHas('posts', ['id' => 1, 'post_name' => 'Developer']);
    }
}
