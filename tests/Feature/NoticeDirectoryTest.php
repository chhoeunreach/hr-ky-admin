<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\NoticeController;
use App\Models\Notice;
use App\Models\Admin;
use App\Models\User;
use App\Repositories\NoticeRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NoticeDirectoryTest extends TestCase
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
        app('view')->getFinder()->prependLocation(base_path('tests/fixtures/repair-price-views'));
        DB::purge('sqlite');
        Schema::create('app_settings', function (Blueprint $t) {$t->string('slug'); $t->boolean('status');});
        Schema::create('companies', function (Blueprint $t) {$t->id(); $t->string('name');});
        Schema::create('branches', function (Blueprint $t) {$t->id(); $t->string('name'); $t->integer('company_id'); $t->boolean('is_active')->default(true);});
        Schema::create('users', function (Blueprint $t) {$t->id(); $t->string('name'); $t->integer('company_id'); $t->softDeletes();});
        Schema::create('notices', function (Blueprint $t) {
            $t->id(); $t->string('title'); $t->text('description'); $t->dateTime('notice_publish_date');
            $t->integer('company_id'); $t->integer('branch_id'); $t->boolean('is_active')->default(true);
            $t->integer('created_by')->nullable(); $t->integer('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('notice_receivers', function (Blueprint $t) {$t->id(); $t->integer('notice_id'); $t->integer('notice_receiver_id');});
        (require database_path('migrations/2026_11_13_000003_create_notice_branches_table.php'))->up();
        DB::table('companies')->insert(['id' => 1, 'name' => 'KY']);
        DB::table('branches')->insert([['id' => 1, 'name' => 'Main', 'company_id' => 1], ['id' => 2, 'name' => 'Other', 'company_id' => 1]]);
        DB::table('users')->insert([['id' => 1, 'name' => 'Dara', 'company_id' => 1], ['id' => 2, 'name' => 'Sok', 'company_id' => 1]]);
        $this->actingAs((new Admin())->forceFill(['id' => 1, 'company_id' => 1]), 'admin');
        Notice::clearBootedModels();
        Gate::before(fn () => $this->allowed);
        Route::middleware('web')->group(function () {
            Route::get('/_test/notices', [NoticeController::class, 'index']);
            Route::get('/_test/notices/create', [NoticeController::class, 'create']);
            Route::get('/_test/notices/{id}/edit', [NoticeController::class, 'edit']);
            Route::post('/_test/notices', [NoticeController::class, 'store']);
            Route::put('/_test/notices/{id}', [NoticeController::class, 'update']);
        });
    }

    private function seedNotices(): void
    {
        for ($id = 1; $id <= 31; $id++) {
            DB::table('notices')->insert(['id' => $id, 'title' => 'Meeting '.$id, 'description' => '<p>Important company news</p>',
                'notice_publish_date' => '2026-11-09 12:00:00', 'company_id' => 1, 'branch_id' => $id % 2 ? 1 : 2, 'is_active' => $id % 2]);
            DB::table('notice_receivers')->insert(['notice_id' => $id, 'notice_receiver_id' => $id % 2 ? 1 : 2]);
        }
    }

    private function payload(array $changes = []): array
    {
        return $changes + ['branch_id' => 1, 'title' => 'New notice', 'description' => '<p>Important notice details.</p>',
            'receiver' => [['notice_receiver_id' => 1]], 'is_active' => 1, 'send_work_alert' => 0];
    }

    public function test_search_branch_receiver_status_dates_and_all_page_sizes(): void
    {
        $this->seedNotices();
        $repo = app(NoticeRepository::class);
        $this->assertCount(25, $repo->getAllCompanyNotices([])->items());
        $this->assertCount(31, $repo->getAllCompanyNotices(['per_page' => 'all'])->items());
        $this->assertSame(50, $repo->getAllCompanyNotices(['per_page' => '50'])->perPage());
        $this->assertCount(16, $repo->getAllCompanyNotices(['branch_id' => 1, 'notice_receiver' => [1], 'is_active' => '1', 'search' => 'Dara', 'per_page' => 'all'])->items());
        $this->assertSame(0, $repo->getAllCompanyNotices(['branch_id' => 1, 'is_active' => '0'])->total());
        $this->assertSame(0, $repo->getAllCompanyNotices(['publish_date_from' => '2026-11-10'])->total());
        $this->assertSame(1, $repo->getAllCompanyNotices(['search' => 'Meeting 31'])->total());
    }

    public function test_column_sorting_is_allowlisted_and_keeps_filters(): void
    {
        $this->seedNotices();
        $repo = app(NoticeRepository::class);
        $this->assertSame(1, $repo->getAllCompanyNotices(['sort' => 'title', 'direction' => 'asc'])->first()->id);
        $this->assertSame(9, $repo->getAllCompanyNotices(['sort' => 'title', 'direction' => 'desc'])->first()->id);
        $this->assertSame(30, $repo->getAllCompanyNotices(['sort' => 'status', 'direction' => 'asc'])->first()->id);
        $this->assertSame(31, $repo->getAllCompanyNotices(['sort' => 'status', 'direction' => 'desc', 'branch_id' => 1])->first()->id);
        $this->assertSame(31, $repo->getAllCompanyNotices(['sort' => 'invalid', 'direction' => 'invalid'])->first()->id);
        $this->get('/_test/notices?branch_id=1&sort=title&direction=asc')->assertOk()
            ->assertSee('aria-sort="ascending"', false)->assertSee('data-notice-sort', false)
            ->assertSee('data-open-notice-form data-url=', false)
            ->assertSee('data-notice-description', false)->assertSee('notice-status is-active', false);
    }

    public function test_index_renders_toolbar_and_replaceable_list_in_both_languages(): void
    {
        $this->seedNotices();
        foreach (['en', 'km'] as $locale) {
            $this->withSession(['locale' => $locale])->get('/_test/notices')->assertOk()
                ->assertSee('id="notice-list-block"', false)->assertSee('data-directory-total="31"', false)
                ->assertSee('data-directory-table="noticeTable"', false)->assertSee('data-directory-export="excel"', false)
                ->assertSee('data-open-notice-form', false)->assertSee('id="noticeFormModal"', false)
                ->assertDontSee('id="dataTableExample"', false)->assertDontSee('notice.publish_from');
        }
    }

    public function test_modal_create_and_update_return_json_and_replace_receivers(): void
    {
        $this->postJson('/_test/notices', $this->payload())->assertCreated()->assertJsonPath('id', 1);
        $this->assertDatabaseHas('notices', ['title' => 'New notice', 'branch_id' => 1]);
        $this->putJson('/_test/notices/1', $this->payload(['title' => 'Updated notice', 'receiver' => [['notice_receiver_id' => 2]]]))->assertOk()->assertJsonPath('id', '1');
        $this->assertDatabaseHas('notices', ['id' => 1, 'title' => 'Updated notice']);
        $this->assertDatabaseHas('notice_receivers', ['notice_id' => 1, 'notice_receiver_id' => 2]);
        $this->assertDatabaseMissing('notice_receivers', ['notice_id' => 1, 'notice_receiver_id' => 1]);
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_validation_errors_keep_saved_notice_unchanged_and_modal_views_embed_editor(): void
    {
        $this->postJson('/_test/notices', $this->payload())->assertCreated();
        $this->putJson('/_test/notices/1', $this->payload(['title' => '', 'receiver' => []]))->assertUnprocessable()->assertJsonValidationErrors(['title', 'receiver']);
        $this->assertDatabaseHas('notices', ['id' => 1, 'title' => 'New notice']);
        foreach (['/_test/notices/create?notice_modal=1', '/_test/notices/1/edit?notice_modal=1'] as $url) {
            $this->get($url)->assertOk()->assertSee('id="notification"', false)->assertSee('notice-form.js', false)->assertSee('id="noticeDescription"', false);
        }
    }

    public function test_multiple_branches_are_saved_prefilled_filtered_and_replaced(): void
    {
        $this->postJson('/_test/notices', $this->payload(['branch_ids' => [1, 2]]))->assertCreated();
        $this->assertDatabaseCount('notices', 1);
        $this->assertDatabaseCount('notice_branches', 2);
        $this->assertSame(1, app(NoticeRepository::class)->getAllCompanyNotices(['branch_id' => 2])->total());
        $this->get('/_test/notices/1/edit?notice_modal=1')->assertOk()
            ->assertSee('name="branch_ids[]" multiple required', false)
            ->assertSee('value="1" selected>Main', false)
            ->assertSee('value="2" selected>Other', false);
        $this->get('/_test/notices?branch_id=2')->assertOk()->assertSee('Main, Other');
        $this->putJson('/_test/notices/1', $this->payload(['branch_ids' => [2]]))->assertOk();
        $this->assertDatabaseCount('notice_branches', 1);
        $this->assertDatabaseHas('notice_branches', ['notice_id' => 1, 'branch_id' => 2]);
        $this->assertSame(0, app(NoticeRepository::class)->getAllCompanyNotices(['branch_id' => 1])->total());
    }

    public function test_employee_in_secondary_branch_can_find_notice(): void
    {
        $this->postJson('/_test/notices', $this->payload(['branch_ids' => [1, 2]]))->assertCreated();
        auth('admin')->logout();
        $this->actingAs(User::find(2)->forceFill(['branch_id' => 2]), 'web');
        Notice::clearBootedModels();
        $this->assertSame(1, Notice::query()->count());
    }

    public function test_migration_preserves_existing_notice_branch(): void
    {
        $this->seedNotices();
        $migration = require database_path('migrations/2026_11_13_000003_create_notice_branches_table.php');
        $migration->down();
        $migration->up();
        $this->assertDatabaseCount('notice_branches', 31);
        $this->assertDatabaseHas('notice_branches', ['notice_id' => 1, 'branch_id' => 1]);
        $this->assertDatabaseHas('notice_branches', ['notice_id' => 2, 'branch_id' => 2]);
    }

    public function test_invalid_branch_selection_does_not_create_notice(): void
    {
        foreach ([[], [1, 1], [999]] as $branches) {
            $this->postJson('/_test/notices', $this->payload(['branch_ids' => $branches]))->assertUnprocessable();
        }
        DB::table('branches')->insert(['id' => 3, 'name' => 'Another company', 'company_id' => 2]);
        $this->postJson('/_test/notices', $this->payload(['branch_ids' => [3]]))->assertUnprocessable();
        $this->assertDatabaseCount('notices', 0);
    }

    public function test_modal_endpoints_keep_create_and_edit_permission_checks(): void
    {
        auth('admin')->logout();
        $this->actingAs(User::find(1)->forceFill(['branch_id' => 1]), 'web');
        $this->allowed = false;
        $this->get('/_test/notices/create?notice_modal=1')->assertForbidden();
        $this->get('/_test/notices/1/edit?notice_modal=1')->assertForbidden();
        $this->postJson('/_test/notices', $this->payload())->assertForbidden();
        $this->putJson('/_test/notices/1', $this->payload())->assertForbidden();
        $this->assertDatabaseCount('notices', 0);
    }

    public function test_edit_form_preserves_inactive_status_and_prefills_recipients_in_both_languages(): void
    {
        $this->postJson('/_test/notices', $this->payload(['is_active' => 0]))->assertCreated();
        foreach (['en', 'km'] as $locale) {
            $this->withSession(['locale' => $locale])->get('/_test/notices/1/edit?notice_modal=1')->assertOk()
                ->assertSee('notice-form-workspace', false)->assertSee('notice-form-sidebar', false)
                ->assertSee('data-notice-cancel', false)->assertSee('value="0" selected', false)
                ->assertSee('value="1" selected>Dara', false)
                ->assertSee('value="New notice"', false)->assertDontSee('notice.save_changes');
        }
    }
}
