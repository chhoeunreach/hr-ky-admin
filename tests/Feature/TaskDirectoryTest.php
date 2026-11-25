<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class TaskDirectoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.sqlite.database' => ':memory:', 'app.debug' => true,
            'app_security.mode' => 'disabled', 'logging.default' => 'stderr',
            'excel.temporary_files.local_path' => sys_get_temp_dir().'/hrky-directory-excel']);
        DB::purge('sqlite');
        app()->setLocale('en');

        $layout = sys_get_temp_dir().'/hrky-directory-test-'.bin2hex(random_bytes(6));
        mkdir($layout.'/layouts', 0700, true);
        file_put_contents($layout.'/layouts/master.blade.php', "@yield('main-content')");
        $this->app['view']->getFinder()->prependLocation($layout);
        $this->beforeApplicationDestroyed(static function () use ($layout) {
            unlink($layout.'/layouts/master.blade.php');
            rmdir($layout.'/layouts');
            rmdir($layout);
        });

        Cache::put('role', ['admin']);
        $this->schema();

        (require database_path('migrations/2026_11_20_170000_create_daily_records.php'))->up();
        (require database_path('migrations/2026_11_20_180000_extend_daily_work_records.php'))->up();

        DB::table('roles')->insert(['id' => 1, 'name' => 'Admin', 'slug' => 'admin', 'backend_login_authorize' => 1]);
        DB::table('branches')->insert([
            ['id' => 1, 'name' => 'Phnom Penh', 'company_id' => 1],
            ['id' => 2, 'name' => 'Siem Reap', 'company_id' => 1],
        ]);
        DB::table('departments')->insert(['id' => 1, 'dept_name' => 'Operations', 'dept_head_id' => null]);
        DB::table('companies')->insert(['id' => 1, 'name' => 'Digital HR Co.']);
        foreach ([[1, 'Admin User'], [2, 'Sokha Chan']] as [$id, $name]) {
            DB::table('users')->insert(['id' => $id, 'name' => $name, 'company_id' => 1,
                'branch_id' => $id === 1 ? null : 1, 'department_id' => 1, 'role_id' => 1,
                'status' => 'verified', 'is_active' => 1]);
        }

        DB::table('projects')->insert(['id' => 1, 'name' => 'Website Revamp', 'branch_id' => 1, 'is_active' => 1]);
        DB::table('tasks')->insert([
            ['id' => 1, 'name' => 'Prepare Q4 payroll report', 'project_id' => 1, 'branch_id' => 1,
                'status' => 'in_progress', 'priority' => 'high', 'is_active' => 1,
                'start_date' => '2026-11-01', 'end_date' => '2026-12-31'],
            ['id' => 2, 'name' => 'Fix login bug', 'project_id' => 1, 'branch_id' => 1,
                'status' => 'completed', 'priority' => 'low', 'is_active' => 1,
                'start_date' => '2026-10-01', 'end_date' => '2026-10-31'],
        ]);
        DB::table('task_checklists')->insert(['id' => 1, 'task_id' => 1, 'name' => 'Collect payroll data',
            'assigned_to' => 2, 'is_completed' => 1]);
        DB::table('assigned_members')->insert(['id' => 1, 'member_id' => 2, 'assignable_id' => 1,
            'assignable_type' => 'task']);

        $this->actingAs(User::findOrFail(1), 'web');
        Model::clearBootedModels();
    }

    public function test_task_index_renders_filter_panel_toolbar_and_table(): void
    {
        $this->get('/admin/tasks')->assertOk()
            ->assertSee('directory-filter-panel', false)
            ->assertSee('id="taskFilterCollapse"', false)
            ->assertSee('id="taskFilterForm"', false)
            ->assertSee('directory-list-heading', false)
            ->assertSee('data-directory-table="taskTable"', false)
            ->assertSee('data-directory-form="taskFilterForm"', false)
            ->assertSee('id="taskListSearch"', false)
            ->assertSee('name="per_page"', false)
            ->assertSee('admin/tasks/create', false)
            ->assertSee('id="taskTable"', false)
            ->assertSee('data-directory-actions', false)
            ->assertSee('data-directory-count="taskTable">2<', false)
            ->assertSee('Prepare Q4 payroll report')
            ->assertSee('Fix login bug')
            ->assertSee('Website Revamp');
    }

    public function test_task_index_filters_search_status_priority_and_per_page(): void
    {
        $this->get('/admin/tasks?search=payroll')->assertOk()
            ->assertSee('Prepare Q4 payroll report')
            ->assertDontSee('Fix login bug')
            ->assertSee('data-directory-count="taskTable">1<', false);

        $this->get('/admin/tasks?status=completed')->assertOk()
            ->assertSee('Fix login bug')
            ->assertDontSee('Prepare Q4 payroll report')
            ->assertSee('id="taskFilterCollapse" class="collapse show"', false);

        $this->get('/admin/tasks?priority=urgent')->assertOk()
            ->assertSee('data-directory-count="taskTable">0<', false)
            ->assertSee(__('index.no_records_found'));

        $this->get('/admin/tasks?per_page=all')->assertOk()
            ->assertSee('Prepare Q4 payroll report')
            ->assertSee('Fix login bug');

        $page = $this->get('/admin/tasks?per_page=25');
        $page->assertOk();
        $this->assertMatchesRegularExpression('/value="25"\s+selected/', $page->getContent());
    }

    public function test_daily_record_pages_render_filter_panel_toolbar_and_table(): void
    {
        $this->get('/admin/daily-records/branch-routes')->assertOk()
            ->assertSee('directory-filter-panel', false)
            ->assertSee('id="branchRouteFilterForm"', false)
            ->assertSee('data-directory-table="branchRouteTable"', false)
            ->assertSee('id="branchRouteTable"', false)
            ->assertSee('data-directory-actions', false)
            ->assertSee('data-directory-count="branchRouteTable">0<', false);

        $this->get('/admin/daily-records/submissions')->assertOk()
            ->assertSee('id="submissionFilterForm"', false)
            ->assertSee('data-directory-table="submissionTable"', false)
            ->assertSee('id="submissionTable"', false)
            ->assertSee('data-directory-count="submissionTable">0<', false);

        $this->get('/admin/daily-records/categories')->assertOk()
            ->assertSee('id="categoryFilterForm"', false)
            ->assertSee('data-directory-table="categoryTable"', false)
            ->assertSee('id="categoryTable"', false)
            ->assertSee('data-directory-count="categoryTable">'.number_format(DB::table('daily_record_categories')->count()).'<', false)
            ->assertSee('Sales')
            ->assertSee('Customer Issues')
            ->assertSee('Stock counting');
    }

    public function test_daily_record_filters_round_trip_and_invalid_values_are_rejected(): void
    {
        $ongoing = $this->get('/admin/daily-records/branch-routes?status=ongoing');
        $ongoing->assertOk()
            ->assertSee('id="branchRouteFilterCollapse" class="collapse show"', false);
        $this->assertMatchesRegularExpression('/value="ongoing"\s+selected/', $ongoing->getContent());
        $this->get('/admin/daily-records/branch-routes?status=in_progress')->assertOk();
        $this->get('/admin/daily-records/branch-routes?status=delivered')->assertStatus(302);

        $this->get('/admin/daily-records/submissions?status=approved')->assertOk()
            ->assertSee('id="submissionFilterCollapse" class="collapse show"', false);
        $this->get('/admin/daily-records/submissions?status=delivered')->assertStatus(302);
        $this->get('/admin/daily-records/submissions?per_page=all')->assertOk();

        $incidentCount = number_format(DB::table('daily_record_categories')->where('kind', 'incident')->count());
        $this->get('/admin/daily-records/categories?kind=incident')->assertOk()
            ->assertSee('Late arrival')
            ->assertDontSee('Stock counting')
            ->assertSee('data-directory-count="categoryTable">'.$incidentCount.'<', false);

        $this->get('/admin/daily-records/categories?search='.urlencode('Stock counting'))->assertOk()
            ->assertSee('Stock counting')
            ->assertDontSee('Late arrival')
            ->assertSee('data-directory-count="categoryTable">1<', false);

        $this->get('/admin/daily-records/categories?is_active=1')->assertOk()
            ->assertSee('data-directory-count="categoryTable">'.number_format(DB::table('daily_record_categories')->where('active', 1)->count()).'<', false);
        $this->get('/admin/daily-records/categories?is_active=0')->assertOk()
            ->assertSee('data-directory-count="categoryTable">'.number_format(DB::table('daily_record_categories')->where('active', 0)->count()).'<', false);

        $this->get('/admin/daily-records/categories?is_active=2')->assertStatus(302);
        $this->get('/admin/daily-records/categories?per_page=all')->assertOk()
            ->assertSee('Late arrival');
    }

    private function schema(): void
    {
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('slug')->nullable();
            $t->boolean('is_active')->default(1);
            $t->boolean('backend_login_authorize')->default(true);
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            foreach (['role_id', 'company_id', 'branch_id', 'department_id', 'supervisor_id'] as $column) {
                $t->unsignedBigInteger($column)->nullable();
            }
            $t->json('additional_branch_ids')->nullable();
            $t->string('status')->default('verified');
            $t->boolean('is_active')->default(true);
            $t->string('fcm_token')->nullable();
            $t->softDeletes();
        });
        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedBigInteger('company_id');
            $t->boolean('is_active')->default(1);
            $t->timestamps();
        });
        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->string('dept_name');
            $t->unsignedBigInteger('dept_head_id')->nullable();
        });
        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('logo')->nullable();
            $t->timestamps();
        });
        Schema::create('general_settings', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('key');
            $t->string('value')->nullable();
            $t->timestamps();
        });
        Schema::create('app_settings', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('slug')->nullable();
            $t->text('value')->nullable();
            $t->boolean('status')->default(false);
            $t->timestamps();
        });
        Schema::create('permission_group_types', function (Blueprint $t) {
            $t->id();
            $t->string('slug');
            $t->string('name');
        });
        Schema::create('permission_groups', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedBigInteger('group_type_id');
        });
        Schema::create('permissions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('permission_key');
            $t->unsignedBigInteger('permission_groups_id');
        });
        Schema::create('permission_roles', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('permission_id');
            $t->unsignedBigInteger('role_id');
        });
        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->integer('branch_id')->nullable();
            $t->string('status')->nullable();
            $t->string('priority')->nullable();
            $t->boolean('is_active')->default(1);
            $t->timestamps();
        });
        Schema::create('tasks', function (Blueprint $t) {
            $t->id();
            $t->integer('project_id')->nullable();
            $t->string('name');
            $t->integer('branch_id')->nullable();
            $t->string('status')->nullable();
            $t->string('priority')->nullable();
            $t->boolean('is_active')->default(1);
            $t->date('start_date')->nullable();
            $t->dateTime('end_date')->nullable();
            $t->integer('created_by')->nullable();
            $t->integer('updated_by')->nullable();
            $t->timestamps();
        });
        Schema::create('task_checklists', function (Blueprint $t) {
            $t->id();
            $t->text('name');
            $t->unsignedBigInteger('task_id');
            $t->unsignedBigInteger('assigned_to');
            $t->boolean('is_completed')->default(0);
        });
        Schema::create('assigned_members', function (Blueprint $t) {
            $t->id();
            $t->integer('member_id');
            $t->integer('assignable_id');
            $t->string('assignable_type');
        });
        Schema::create('project_team_leaders', function (Blueprint $t) {
            $t->id();
            $t->integer('project_id');
            $t->integer('leader_id');
        });
    }
}
