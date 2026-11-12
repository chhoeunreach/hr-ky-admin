<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\HomeHighlightController;
use App\Models\HomeHighlight;
use App\Models\Admin;
use App\Models\User;
use App\Services\Community\HomeHighlightAlert;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Laravel\Passport\Passport;
use Tests\TestCase;

class HomeHighlightTest extends TestCase
{
    private array $abilities = [];

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
        config(['mobile_permissions' => require __DIR__.'/../../config/mobile_permissions.php']);

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('company_id')->nullable();
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('dept_name');
            $table->integer('branch_id')->nullable();
            $table->integer('company_id')->nullable();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('role_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('verified');
            $table->string('fcm_token')->nullable();
        });
        Schema::create('permission_group_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
        });
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('group_type_id');
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('permission_key')->unique();
            $table->integer('permission_groups_id');
        });
        Schema::create('permission_roles', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->integer('role_id');
        });

        DB::table('roles')->insert(['id' => 2, 'name' => 'Employee', 'slug' => 'employee']);
        DB::table('branches')->insert(['id' => 3, 'name' => 'Head Office', 'company_id' => 1]);
        DB::table('departments')->insert(['id' => 4, 'dept_name' => 'Operations', 'branch_id' => 3, 'company_id' => 1]);
        DB::table('users')->insert([
            ['id' => 10, 'name' => 'Reader', 'company_id' => 1, 'role_id' => 2, 'branch_id' => 3, 'department_id' => 4],
            ['id' => 11, 'name' => 'Colleague', 'company_id' => 1, 'role_id' => 2, 'branch_id' => null, 'department_id' => null],
            ['id' => 12, 'name' => 'Other Company', 'company_id' => 2, 'role_id' => 2, 'branch_id' => null, 'department_id' => null],
        ]);

        (require __DIR__.'/../../database/migrations/2026_11_13_000001_create_home_highlights.php')->up();
        (require __DIR__.'/../../database/migrations/2026_11_13_000002_create_home_highlight_display_settings.php')->up();

        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        Passport::actingAs(User::findOrFail(10));

        Route::middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class)->group(function () {
            Route::get('/_test/home-highlights', [HomeHighlightController::class, 'index']);
            Route::get('/_test/home-highlights/create', [HomeHighlightController::class, 'create']);
            Route::get('/_test/home-highlights/{homeHighlight}/edit', [HomeHighlightController::class, 'edit']);
            Route::post('/_test/home-highlight-display-settings', [HomeHighlightController::class, 'saveDisplaySettings']);
            Route::post('/_test/home-highlights', [HomeHighlightController::class, 'store']);
            Route::post('/_test/home-highlights/{homeHighlight}', [HomeHighlightController::class, 'update']);
            Route::post('/_test/home-highlights/{homeHighlight}/toggle', [HomeHighlightController::class, 'toggle']);
            Route::post('/_test/home-highlights/{homeHighlight}/send-now', [HomeHighlightController::class, 'sendNow']);
            Route::delete('/_test/home-highlights/{homeHighlight}', [HomeHighlightController::class, 'destroy']);
        });
    }

    private function grantView(): void
    {
        $this->abilities = ['home_highlight.view', 'mobile.menu.home-highlights'];
    }

    private function grantManage(): void
    {
        $this->abilities = ['home_highlight.manage'];
    }

    private function make(array $overrides = []): HomeHighlight
    {
        return HomeHighlight::create($overrides + [
            'company_id' => 1,
            'title' => 'Highlight',
            'is_active' => true,
        ]);
    }

    public function test_api_index_returns_only_active_visible_highlights_for_the_company(): void
    {
        $this->grantView();
        $live = $this->make(['title' => 'Live', 'priority' => 5, 'role_ids' => [2], 'branch_ids' => [3], 'department_ids' => [4]]);
        $global = $this->make(['title' => 'Global', 'priority' => 1, 'company_id' => null]);
        $this->make(['title' => 'Draft', 'is_active' => false]);
        $this->make(['title' => 'Future', 'starts_at' => now()->addDay()]);
        $this->make(['title' => 'Expired', 'ends_at' => now()->subDay()]);
        $this->make(['title' => 'Wrong role', 'role_ids' => [99]]);
        $this->make(['title' => 'Wrong branch', 'branch_ids' => [9]]);
        $this->make(['title' => 'Wrong department', 'department_ids' => [9]]);
        $this->make(['title' => 'Other company', 'company_id' => 2]);

        $this->getJson('/api/home-highlights')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $live->id)
            ->assertJsonPath('data.1.id', $global->id);
    }

    public function test_api_history_includes_ended_highlights_but_not_drafts(): void
    {
        $this->grantView();
        $current = $this->make(['title' => 'Current', 'starts_at' => now()->subDay()]);
        $ended = $this->make(['title' => 'Ended', 'starts_at' => now()->subWeek(), 'ends_at' => now()->subDay()]);
        $this->make(['title' => 'Draft', 'is_active' => false]);
        $this->make(['title' => 'Future', 'starts_at' => now()->addDay()]);
        $this->make(['title' => 'Other company', 'company_id' => 2]);

        $response = $this->getJson('/api/home-highlights/history?per_page=1&page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.last_page', 2);

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($current->id, $ids);
        $this->assertNotContains($ended->id, $ids);

        $this->getJson('/api/home-highlights/history?per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('data.0.id', $ended->id);
    }

    public function test_api_show_respects_company_audience_and_schedule(): void
    {
        $this->grantView();
        $live = $this->make(['title' => 'Live', 'role_ids' => [2]]);
        $ended = $this->make(['title' => 'Ended', 'ends_at' => now()->subDay()]);
        $draft = $this->make(['title' => 'Draft', 'is_active' => false]);
        $future = $this->make(['title' => 'Future', 'starts_at' => now()->addDay()]);
        $wrongAudience = $this->make(['title' => 'Wrong audience', 'role_ids' => [99]]);
        $otherCompany = $this->make(['title' => 'Other company', 'company_id' => 2]);

        $this->getJson('/api/home-highlights/'.$live->id)->assertOk()->assertJsonPath('data.id', $live->id);
        $this->getJson('/api/home-highlights/'.$ended->id)->assertOk();
        $this->getJson('/api/home-highlights/'.$draft->id)->assertNotFound();
        $this->getJson('/api/home-highlights/'.$future->id)->assertNotFound();
        $this->getJson('/api/home-highlights/'.$wrongAudience->id)->assertNotFound();
        $this->getJson('/api/home-highlights/'.$otherCompany->id)->assertNotFound();
    }

    public function test_api_requires_mobile_menu_and_view_permissions(): void
    {
        $this->abilities = [];
        $this->getJson('/api/home-highlights')->assertForbidden();

        $this->abilities = ['mobile.menu.home-highlights'];
        $this->getJson('/api/home-highlights')->assertForbidden();

        $this->abilities = ['home_highlight.view'];
        $this->getJson('/api/home-highlights')->assertForbidden();
    }

    public function test_api_applies_audience_before_limiting_and_paginates_visible_results(): void
    {
        $this->grantView();
        for ($i = 0; $i < 12; $i++) {
            $this->make(['priority' => 100, 'user_ids' => [11]]);
        }
        $visible = [];
        for ($i = 0; $i < 12; $i++) {
            $visible[] = $this->make(['priority' => $i, 'user_ids' => [10]])->id;
        }

        $this->getJson('/api/home-highlights')->assertOk()
            ->assertJsonCount(10, 'data')->assertJsonPath('data.0.id', $visible[11]);
        $this->getJson('/api/home-highlights/history?per_page=10&page=2')->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('meta.total', 12);
        $this->getJson('/api/home-highlights/history?per_page=10&page=3')->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_schedule_boundaries_match_between_carousel_history_and_detail(): void
    {
        $this->grantView();
        $this->travelTo(now()->startOfSecond());
        $starting = $this->make(['starts_at' => now()]);
        $ending = $this->make(['ends_at' => now()]);

        $this->getJson('/api/home-highlights')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $starting->id);
        $this->getJson('/api/home-highlights/'.$starting->id)->assertOk();
        $this->getJson('/api/home-highlights/'.$ending->id)->assertOk();
        $this->getJson('/api/home-highlights/history')->assertOk()->assertJsonPath('meta.total', 2);
    }

    public function test_rich_text_keeps_formatting_and_removes_executable_content_including_legacy_records(): void
    {
        $this->grantManage();
        $unsafe = '<p onclick="alert(1)">Hello <strong>team</strong></p><script>alert(2)</script><img src=x onerror="alert(3)"><a href="javascript:alert(4)">Click</a>';
        $this->post('/_test/home-highlights', ['title' => 'Safe', 'body' => $unsafe, 'priority' => 0, 'is_active' => 1])->assertRedirect();
        $item = HomeHighlight::firstOrFail();
        $stored = DB::table('home_highlights')->where('id', $item->id)->value('body');
        $this->assertStringContainsString('<strong>team</strong>', $stored);
        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('onclick', $stored);
        $this->assertStringNotContainsString('javascript:', $stored);

        DB::table('home_highlights')->where('id', $item->id)->update(['body' => $unsafe]);
        $this->grantView();
        $body = $this->getJson('/api/home-highlights/'.$item->id)->assertOk()->json('data.body');
        $this->assertStringContainsString('<strong>team</strong>', $body);
        $this->assertStringNotContainsString('<script', $body);
        $this->assertStringNotContainsString('onerror', $body);
    }

    public function test_web_validates_schedule_urls_audience_and_visibility_flags(): void
    {
        $this->grantManage();
        foreach ([
            'ends_at' => ['starts_at' => '2026-11-20T10:00', 'ends_at' => '2026-11-20T09:00'],
            'image_url' => ['image_url' => 'https://'],
            'link_url' => ['link_url' => 'javascript:alert(1)'],
            'branch_ids.1' => ['branch_ids' => [3, 3]],
            'is_active' => ['is_active' => 'invalid'],
            'priority' => ['priority' => 1001],
        ] as $error => $invalid) {
            $this->post('/_test/home-highlights', $invalid + ['title' => 'Invalid', 'priority' => 0])
                ->assertSessionHasErrors($error);
        }
        $this->assertDatabaseCount('home_highlights', 0);
    }

    public function test_image_upload_replace_clear_and_delete_clean_up_only_unused_files(): void
    {
        $this->grantManage();
        Storage::fake('public');
        // Shared media references checked by the community cleanup service.
        foreach (['flash_views', 'social_posts', 'social_post_media'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                $table->text('media_url')->nullable();
                $table->softDeletes();
            });
        }
        $payload = ['title' => 'Cover', 'priority' => 0];
        $this->post('/_test/home-highlights', $payload + ['image' => UploadedFile::fake()->image('cover.jpg')])->assertRedirect();
        $item = HomeHighlight::firstOrFail();
        $image = $item->image_url;
        $path = 'community/'.basename($image);
        Storage::disk('public')->assertExists($path);
        $shared = $this->make(['image_url' => $image]);

        $this->post('/_test/home-highlights/'.$item->id, $payload + ['image_url' => 'https://example.com/cover.jpg'])->assertRedirect();
        $this->assertSame('https://example.com/cover.jpg', $item->fresh()->image_url);
        Storage::disk('public')->assertExists($path);
        $this->delete('/_test/home-highlights/'.$shared->id)->assertRedirect();
        Storage::disk('public')->assertMissing($path);

        $this->post('/_test/home-highlights/'.$item->id, $payload + ['image_url' => ''])->assertRedirect();
        $this->assertNull($item->fresh()->image_url);
        $this->post('/_test/home-highlights/'.$item->id, $payload + ['image' => UploadedFile::fake()->image('new.png')])->assertRedirect();
        $newPath = 'community/'.basename($item->fresh()->image_url);
        $this->post('/_test/home-highlights/'.$item->id, $payload + ['remove_image' => 1])->assertRedirect();
        $this->assertNull($item->fresh()->image_url);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_web_store_update_toggle_and_delete(): void
    {
        $this->grantManage();
        $payload = [
            'title' => 'Scheduled update',
            'body' => '<p>Hello</p>',
            'image_url' => null,
            'link_url' => null,
            'link_label' => null,
            'role_ids' => [2],
            'branch_ids' => [3],
            'department_ids' => [4],
            'user_ids' => [10],
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'priority' => 7,
            'is_active' => 1,
        ];

        $this->post('/_test/home-highlights', $payload)
            ->assertRedirect(route('admin.community.home-highlights.index'));

        $item = HomeHighlight::firstOrFail();
        $this->assertSame(1, (int) $item->company_id);
        $this->assertSame(10, (int) $item->created_by);
        $this->assertSame([2], $item->role_ids);
        $this->assertSame([3], $item->branch_ids);
        $this->assertSame([4], $item->department_ids);
        $this->assertSame([10], $item->user_ids);
        $this->assertTrue($item->is_active);

        $this->post('/_test/home-highlights/'.$item->id, array_merge($payload, [
            'title' => 'Updated',
            'role_ids' => [],
            'is_active' => 0,
        ]))->assertRedirect(route('admin.community.home-highlights.index'));

        $item->refresh();
        $this->assertSame('Updated', $item->title);
        $this->assertSame([], $item->role_ids);
        $this->assertFalse($item->is_active);

        $this->post('/_test/home-highlights/'.$item->id.'/toggle')->assertRedirect();
        $this->assertTrue($item->fresh()->is_active);

        $this->delete('/_test/home-highlights/'.$item->id)->assertRedirect();
        $this->assertDatabaseMissing('home_highlights', ['id' => $item->id]);
    }

    public function test_admin_views_render_and_escape_content(): void
    {
        $this->grantManage();
        $directory = sys_get_temp_dir().'/home-highlight-view-'.bin2hex(random_bytes(6));
        mkdir($directory.'/layouts', 0777, true);
        file_put_contents($directory.'/layouts/master.blade.php', "@yield('title') @yield('action') @yield('styles') @yield('main-content') @yield('scripts')");
        view()->getFinder()->prependLocation($directory);
        view()->getFinder()->flush();
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        try {
            $item = $this->make([
                'title' => '<script>alert(1)</script>',
                'body' => '<p>Hello</p>',
                'role_ids' => [2],
            ]);
            $other = $this->make(['company_id' => 2, 'title' => 'Other company secret']);

            $index = $this->get('/_test/home-highlights')->assertOk()->getContent();
            $this->assertStringContainsString('Home Highlights', $index);
            $this->assertStringContainsString('Employee home experience', $index);
            $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $index);
            $this->assertStringNotContainsString('Other company secret', $index);

            $this->make(['title' => 'Scheduled match', 'starts_at' => now()->addDay()]);
            $this->make(['title' => 'Draft match', 'is_active' => false]);
            $this->make(['title' => 'Expired match', 'ends_at' => now()->subDay()]);
            foreach (['live' => '&lt;script&gt;alert(1)&lt;/script&gt;', 'scheduled' => 'Scheduled match',
                'draft' => 'Draft match', 'expired' => 'Expired match'] as $status => $title) {
                $filtered = $this->get('/_test/home-highlights?status='.$status)->assertOk();
                $filtered->assertViewHas('items', fn ($items) => $items->total() === 1);
                $this->assertStringContainsString($title, $filtered->getContent());
            }
            $this->get('/_test/home-highlights?q=Scheduled')->assertOk()
                ->assertViewHas('items', fn ($items) => $items->total() === 1 && $items->first()->title === 'Scheduled match');

            $this->get('/_test/home-highlights/create')->assertOk()->assertSee('Publish highlight');
            $this->get('/_test/home-highlights/'.$item->id.'/edit')->assertOk()->assertSee('Save changes');
            $this->withSession(['_old_input' => ['title' => 'Retry', 'priority' => 'invalid', 'body' => '<script>alert(99)</script>']])
                ->get('/_test/home-highlights/'.$item->id.'/edit')->assertOk()
                ->assertDontSee('data-audience-value checked', false)
                ->assertDontSee('<script>alert(99)</script>', false);
            $this->get('/_test/home-highlights/'.$other->id.'/edit')->assertNotFound();
        } finally {
            unlink($directory.'/layouts/master.blade.php');
            rmdir($directory.'/layouts');
            rmdir($directory);
        }
    }

    public function test_web_cannot_manage_another_companys_highlight(): void
    {
        $this->grantManage();
        $other = $this->make(['company_id' => 2, 'title' => 'Other']);

        $this->post('/_test/home-highlights/'.$other->id, ['title' => 'Hijack', 'priority' => 0])->assertNotFound();
        $this->post('/_test/home-highlights/'.$other->id.'/toggle')->assertNotFound();
        $this->delete('/_test/home-highlights/'.$other->id)->assertNotFound();

        $this->assertDatabaseHas('home_highlights', ['id' => $other->id, 'title' => 'Other']);
    }

    public function test_admin_guard_can_open_and_manage_highlights_without_an_employee_login(): void
    {
        Schema::create('companies', fn (Blueprint $table) => $table->id());
        DB::table('companies')->insert(['id' => 1]);

        // Match the real admin session: the default employee guard is empty.
        Auth::forgetGuards();
        Auth::shouldUse('web');
        $admin = new Admin;
        $admin->id = 99;
        Auth::guard('admin')->setUser($admin);
        $this->assertNull(Auth::user());
        foreach (Route::getRoutes() as $route) {
            if (str_starts_with($route->uri(), '_test/home-highlights')) {
                $route->middleware(['admin.auth', 'permission']);
            }
        }

        $directory = sys_get_temp_dir().'/home-highlight-admin-'.bin2hex(random_bytes(6));
        mkdir($directory.'/layouts', 0777, true);
        file_put_contents($directory.'/layouts/master.blade.php', "@yield('main-content')");
        view()->getFinder()->prependLocation($directory);
        view()->getFinder()->flush();
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        try {
            $other = $this->make(['company_id' => 2, 'title' => 'Other company secret']);
            $this->get('/_test/home-highlights')->assertOk()->assertDontSee('Other company secret');
            $this->get('/_test/home-highlights/create')->assertOk();
            $this->post('/_test/home-highlights', [
                'title' => 'Admin highlight', 'priority' => 0, 'user_ids' => [10],
            ])->assertRedirect(route('admin.community.home-highlights.index'));
            $item = HomeHighlight::where('title', 'Admin highlight')->firstOrFail();
            $this->assertSame(1, (int) $item->company_id);
            $this->assertSame(99, (int) $item->created_by);
            $this->get('/_test/home-highlights/'.$item->id.'/edit')->assertOk();
            $this->post('/_test/home-highlights/'.$item->id, [
                'title' => 'Admin updated', 'priority' => 1,
            ])->assertRedirect();
            $this->assertSame(99, (int) $item->fresh()->updated_by);
            $this->post('/_test/home-highlights/'.$item->id.'/toggle')->assertRedirect();
            $this->assertTrue($item->fresh()->is_active);
            $this->mock(HomeHighlightAlert::class)->shouldReceive('send')->once()
                ->andReturn(['eligible' => 1, 'sent' => 1, 'failed' => 0, 'error' => null]);
            $this->post('/_test/home-highlights/'.$item->id.'/send-now')->assertRedirect()->assertSessionHas('success');
            $this->get('/_test/home-highlights/'.$other->id.'/edit')->assertNotFound();
            $this->post('/_test/home-highlights/'.$other->id.'/toggle')->assertNotFound();
            $this->delete('/_test/home-highlights/'.$other->id)->assertNotFound();
            $this->delete('/_test/home-highlights/'.$item->id)->assertRedirect();
            $this->assertDatabaseMissing('home_highlights', ['id' => $item->id]);
        } finally {
            unlink($directory.'/layouts/master.blade.php');
            rmdir($directory.'/layouts');
            rmdir($directory);
        }
    }

    public function test_web_rejects_audience_from_another_company(): void
    {
        $this->grantManage();
        DB::table('branches')->insert(['id' => 9, 'name' => 'Other branch', 'company_id' => 2]);
        DB::table('departments')->insert(['id' => 9, 'dept_name' => 'Other dept', 'branch_id' => 9, 'company_id' => 2]);

        $this->post('/_test/home-highlights', [
            'title' => 'Invalid branch',
            'branch_ids' => [9],
            'priority' => 0,
        ])->assertSessionHasErrors('branch_ids');

        $this->post('/_test/home-highlights', [
            'title' => 'Invalid department',
            'department_ids' => [9],
            'priority' => 0,
        ])->assertSessionHasErrors('department_ids');

        $this->post('/_test/home-highlights', [
            'title' => 'Invalid employee',
            'user_ids' => [12],
            'priority' => 0,
        ])->assertSessionHasErrors('user_ids');

        $this->assertDatabaseMissing('home_highlights', ['title' => 'Invalid branch']);
    }

    public function test_web_mutations_require_manage_permission(): void
    {
        $this->grantView();
        $item = $this->make();
        $this->get('/_test/home-highlights')->assertForbidden();
        $this->get('/_test/home-highlights/create')->assertForbidden();
        $this->get('/_test/home-highlights/'.$item->id.'/edit')->assertForbidden();
        $this->post('/_test/home-highlights', [
            'title' => 'Denied',
            'priority' => 0,
        ])->assertForbidden();
        $this->post('/_test/home-highlights/'.$item->id, ['title' => 'Denied', 'priority' => 0])->assertForbidden();
        $this->post('/_test/home-highlights/'.$item->id.'/toggle')->assertForbidden();
        $this->post('/_test/home-highlights/'.$item->id.'/send-now')->assertForbidden();
        $this->delete('/_test/home-highlights/'.$item->id)->assertForbidden();
        $this->assertDatabaseHas('home_highlights', ['id' => $item->id, 'title' => 'Highlight', 'is_active' => true]);
    }

    public function test_send_now_only_notifies_eligible_employees_matching_every_audience_group(): void
    {
        $this->grantManage();
        $permissionIds = DB::table('permissions')->whereIn('permission_key', ['home_highlight.view', 'mobile.menu.home-highlights'])->pluck('id');
        foreach ($permissionIds as $id) {
            DB::table('permission_roles')->insert(['permission_id' => $id, 'role_id' => 2]);
        }
        DB::table('roles')->insert(['id' => 3, 'name' => 'No highlights', 'slug' => 'no-highlights']);
        DB::table('users')->where('id', 10)->update(['fcm_token' => 'eligible-token']);
        DB::table('users')->where('id', 12)->update(['fcm_token' => 'other-company', 'branch_id' => 3, 'department_id' => 4]);
        foreach ([
            13 => ['role_id' => 3], 14 => ['deleted_at' => now()],
            15 => ['is_active' => false], 16 => ['status' => 'pending'],
            17 => ['fcm_token' => ''], 18 => ['department_id' => 99],
            19 => ['branch_id' => 99], 20 => [],
        ] as $id => $override) {
            DB::table('users')->insert($override + ['id' => $id, 'name' => 'Excluded', 'company_id' => 1,
                'role_id' => 2, 'branch_id' => 3, 'department_id' => 4, 'fcm_token' => 'excluded-'.$id]);
        }
        $item = $this->make(['role_ids' => [2, 3], 'branch_ids' => [3], 'department_ids' => [4],
            'user_ids' => [10, 12, 13, 14, 15, 16, 17, 18, 19]]);
        $alerts = \Mockery::mock(HomeHighlightAlert::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $alerts->shouldReceive('push')->once()->withArgs(fn ($highlight, $recipients) =>
            $highlight->id === $item->id && $recipients === [10 => 'eligible-token'])
            ->andReturn(['sent' => 1, 'failed' => 0, 'error' => null]);
        $this->app->instance(HomeHighlightAlert::class, $alerts);

        $this->post('/_test/home-highlights/'.$item->id.'/send-now')->assertRedirect()
            ->assertSessionHas('success', 'Firebase accepted the notification for 1 employee(s).');
    }

    public function test_send_now_enforces_company_and_schedule_before_delivery(): void
    {
        $this->grantManage();
        $this->mock(HomeHighlightAlert::class)->shouldNotReceive('send');
        foreach ([['is_active' => false], ['starts_at' => now()->addDay()], ['ends_at' => now()->subDay()]] as $override) {
            $item = $this->make($override);
            $this->post('/_test/home-highlights/'.$item->id.'/send-now')->assertRedirect()->assertSessionHas('warning');
        }
        $other = $this->make(['company_id' => 2]);
        $this->post('/_test/home-highlights/'.$other->id.'/send-now')->assertNotFound();
    }

    public function test_send_now_reports_empty_audience_failed_and_partial_delivery(): void
    {
        $this->grantManage();
        $item = $this->make();
        foreach ([
            ['eligible' => 0, 'sent' => 0, 'failed' => 0, 'error' => null, 'flash' => 'warning'],
            ['eligible' => 1, 'sent' => 0, 'failed' => 1, 'error' => 'Unavailable', 'flash' => 'danger'],
            ['eligible' => 2, 'sent' => 1, 'failed' => 1, 'error' => null, 'flash' => 'warning'],
        ] as $result) {
            $this->mock(HomeHighlightAlert::class)->shouldReceive('send')->once()->andReturn($result);
            $this->post('/_test/home-highlights/'.$item->id.'/send-now')->assertRedirect()->assertSessionHas($result['flash']);
        }
        $this->mock(HomeHighlightAlert::class)->shouldReceive('send')->once()
            ->andThrow(new \RuntimeException('Notification service unavailable'));
        $this->post('/_test/home-highlights/'.$item->id.'/send-now')->assertRedirect()->assertSessionHas('danger');
    }
    public function test_api_rebases_a_stale_highlight_image_host(): void
    {
        $this->grantView(); Storage::fake('public');
        Storage::disk('public')->put('community/highlight.jpg', 'image');
        $item = $this->make(['image_url' => 'http://192.168.0.118:8000/storage/community/highlight.jpg']);
        $this->getJson('/api/home-highlights')->assertOk()
            ->assertJsonPath('data.0.image_url', request()->getSchemeAndHttpHost().'/storage/community/highlight.jpg');
        $this->assertSame('http://192.168.0.118:8000/storage/community/highlight.jpg', $item->fresh()->image_url);
    }

    public function test_orphan_cleanup_preserves_images_referenced_by_home_highlights(): void
    {
        Storage::fake('public');
        foreach (['flash_views', 'social_posts', 'social_post_media'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id(); $table->string('media_url')->nullable();
                if ($name === 'social_posts') $table->softDeletes();
            });
        }
        Storage::disk('public')->put('community/keep.jpg', 'image');
        touch(Storage::disk('public')->path('community/keep.jpg'), now()->subDays(2)->timestamp);
        $this->make(['image_url' => 'https://previous-host.test/storage/community/keep.jpg']);
        $this->artisan('community:prune-media', ['--delete' => true])->assertSuccessful();
        Storage::disk('public')->assertExists('community/keep.jpg');
    }

    public function test_blank_title_and_invalid_schedule_are_rejected_without_creating_a_highlight(): void
    {
        $this->grantManage();
        $this->post('/_test/home-highlights', ['title' => '   ', 'priority' => 0])->assertSessionHasErrors('title');
        $this->post('/_test/home-highlights', ['title' => 'Schedule', 'priority' => 0,
            'starts_at' => '2026-11-12 10:00:00', 'ends_at' => '2026-11-12 09:00:00'])->assertSessionHasErrors('ends_at');
        $this->assertSame(0, HomeHighlight::count());
    }

    public function test_alert_targets_only_active_permitted_employees_in_the_selected_audience_without_sending_push(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'fcm_token')) $table->string('fcm_token')->nullable();
            if (!Schema::hasColumn('users', 'status')) $table->string('status')->nullable();
        });
        DB::table('users')->update(['status' => 'verified', 'fcm_token' => 'test-device']);
        $ids = DB::table('permissions')->whereIn('permission_key', ['home_highlight.view', 'mobile.menu.home-highlights'])->pluck('id');
        foreach ($ids as $id) DB::table('permission_roles')->insert(['permission_id' => $id, 'role_id' => 2]);
        $service = new class extends \App\Services\Community\HomeHighlightAlert {
            public array $recipients = [];
            protected function push(HomeHighlight $item, array $recipients): array {
                $this->recipients = $recipients;
                return ['sent' => count($recipients), 'failed' => 0, 'error' => null];
            }
        };
        $item = $this->make(['branch_ids' => [3]]);
        $this->assertSame(1, $service->send($item)['eligible']);
        $this->assertSame([10], array_keys($service->recipients));
        DB::table('users')->where('id', 10)->update(['is_active' => false]);
        $this->assertSame(0, $service->send($item)['eligible']);
        DB::table('users')->where('id', 10)->update(['is_active' => true]);
        DB::table('permission_roles')->where('permission_id', $ids->first())->delete();
        $this->assertSame(0, $service->send($item)['eligible']);
    }

    public function test_display_timing_is_company_scoped_and_returned_to_employees(): void
    {
        $this->grantView();
        $this->getJson('/api/home-highlights')->assertOk()
            ->assertJsonPath('display_settings.auto_switch_enabled', true)
            ->assertJsonPath('display_settings.late_bonus_seconds', 15)
            ->assertJsonPath('display_settings.highlights_seconds', 15);
        $this->grantManage();
        $this->post('/_test/home-highlight-display-settings', [
            'auto_switch_enabled' => '1', 'late_bonus_seconds' => 10, 'highlights_seconds' => 25,
            'company_id' => 2,
        ])->assertRedirect();
        $this->assertDatabaseHas('home_highlight_display_settings', ['company_id' => 1, 'highlights_seconds' => 25]);
        $this->assertDatabaseMissing('home_highlight_display_settings', ['company_id' => 2]);
        $this->grantView();
        $this->getJson('/api/home-highlights')->assertOk()->assertJsonPath('display_settings.highlights_seconds', 25);
        $this->assertSame(15, \App\Models\HomeHighlightDisplaySetting::forCompany(2)->highlights_seconds);
    }

    public function test_display_timing_validates_bounds_and_can_be_disabled(): void
    {
        $this->grantManage();
        foreach ([0, 4, 301, 'bad'] as $seconds) {
            $this->post('/_test/home-highlight-display-settings', [
                'auto_switch_enabled' => 1, 'late_bonus_seconds' => $seconds, 'highlights_seconds' => 15,
            ])->assertSessionHasErrors('late_bonus_seconds');
        }
        $this->post('/_test/home-highlight-display-settings', [
            'auto_switch_enabled' => 0, 'late_bonus_seconds' => 5, 'highlights_seconds' => 300,
        ])->assertRedirect();
        $this->grantView();
        $this->getJson('/api/home-highlights')->assertOk()->assertJsonPath('display_settings.auto_switch_enabled', false);
        $this->post('/_test/home-highlight-display-settings', [
            'auto_switch_enabled' => 1, 'late_bonus_seconds' => 10, 'highlights_seconds' => 10,
        ])->assertForbidden();
    }

}
