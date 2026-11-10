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
        Schema::create('branches', function (Blueprint $t) {$t->id(); $t->string('name'); $t->integer('company_id');});
        Schema::create('users', function (Blueprint $t) {$t->id(); $t->string('name'); $t->integer('company_id'); $t->softDeletes();});
        Schema::create('notices', function (Blueprint $t) {
            $t->id(); $t->string('title'); $t->text('description'); $t->dateTime('notice_publish_date');
            $t->integer('company_id'); $t->integer('branch_id'); $t->boolean('is_active')->default(true);
            $t->integer('created_by')->nullable(); $t->integer('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('notice_receivers', function (Blueprint $t) {$t->id(); $t->integer('notice_id'); $t->integer('notice_receiver_id');});
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
}
