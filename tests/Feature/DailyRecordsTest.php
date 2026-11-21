<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\DailyRecords\RecordAccess;
use App\Modules\DailyRecords\RecordTime;
use App\Modules\DailyRecords\Reporting;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class DailyRecordsTest extends TestCase
{
    private const API = '/api/v1/daily-records';
    private const WEB = '/admin/daily-records';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.sqlite.database' => ':memory:', 'app.debug' => true,
            'app_security.mode' => 'disabled', 'logging.default' => 'stderr',
            'excel.temporary_files.local_path' => sys_get_temp_dir().'/hrky-daily-excel']);
        DB::purge('sqlite');
        $layout = sys_get_temp_dir().'/hrky-daily-test-'.bin2hex(random_bytes(6));
        mkdir($layout.'/layouts', 0700, true);
        file_put_contents($layout.'/layouts/master.blade.php', "@yield('main-content')");
        $this->app['view']->getFinder()->prependLocation($layout);
        $this->beforeApplicationDestroyed(static function () use ($layout) {
            unlink($layout.'/layouts/master.blade.php');
            rmdir($layout.'/layouts');
            rmdir($layout);
        });
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-20 18:30:00', 'UTC'));
        Cache::put('role', ['manager', 'employee']);
        $this->schema();
        $migration = require database_path('migrations/2026_11_20_170000_create_daily_records.php');
        $migration->up();
        (require database_path('migrations/2026_11_20_180000_extend_daily_work_records.php'))->up();
        (require database_path('migrations/2026_11_20_190000_add_daily_report_submission_permission.php'))->up();
        DB::table('permission_roles')->insert([
            'role_id' => 1, 'permission_id' => DB::table('permissions')->where('permission_key', RecordAccess::MANAGE)->value('id'),
        ]);
        DB::table('roles')->insert([['id' => 1, 'slug' => 'manager'], ['id' => 2, 'slug' => 'employee']]);
        DB::table('branches')->insert([
            ['id' => 1, 'name' => 'Phnom Penh', 'company_id' => 1],
            ['id' => 2, 'name' => 'Siem Reap', 'company_id' => 1],
            ['id' => 3, 'name' => 'Other company', 'company_id' => 2],
        ]);
        DB::table('departments')->insert([
            ['id' => 1, 'dept_name' => 'Operations', 'dept_head_id' => 1],
            ['id' => 2, 'dept_name' => 'Finance', 'dept_head_id' => null],
        ]);
        foreach ([
            [1, 'Manager', 1, 1, 1, null, 1],
            [2, 'Operations One', 1, 1, 1, 1, 2],
            [3, 'Operations Two', 1, 1, 1, null, 2],
            [4, 'Other branch', 1, 2, 1, 1, 2],
            [5, 'Other department', 1, 1, 2, null, 2],
            [6, 'Other company', 2, 3, 1, 1, 2],
        ] as [$id, $name, $company, $branch, $dept, $supervisor, $role]) {
            DB::table('users')->insert(['id' => $id, 'name' => $name, 'company_id' => $company,
                'branch_id' => $branch, 'department_id' => $dept, 'supervisor_id' => $supervisor, 'role_id' => $role]);
        }
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_assignment_checklist_return_resubmit_approval_and_closure(): void
    {
        $this->asMobile(1)->getJson(self::API.'/capabilities')->assertOk()->assertJsonPath('data.assign_tasks', true);
        $this->postJson(self::API.'/assign', $this->task(['employee_ids' => [2], 'checklist' => ['Inspect signage']]))->assertCreated();
        $id = DB::table('daily_task_assignments')->value('id');
        $path = self::API."/tasks/$id/actions";

        $this->asMobile(2)->getJson(self::API.'/tasks?tab=today')->assertOk()->assertJsonCount(1, 'data.data');
        $this->postJson($path, ['action' => 'approve'])->assertUnprocessable();
        $this->postJson($path, ['action' => 'submit', 'message' => 'Skipped'])->assertConflict();
        $this->postJson($path, ['action' => 'acknowledge'])->assertOk()->assertJsonPath('data.task.status', 'acknowledged');
        $this->postJson($path, ['action' => 'start'])->assertOk();
        $this->postJson($path, ['action' => 'submit', 'message' => 'Checklist missing'])->assertUnprocessable();
        $this->postJson($path, ['action' => 'progress', 'progress' => 100, 'checked' => [0]])->assertOk()
            ->assertJsonPath('data.task.status', 'in_progress')->assertJsonPath('data.task.approved_at', null);
        $this->postJson($path, ['action' => 'submit', 'message' => 'Photo pending'])->assertOk()
            ->assertJsonPath('data.task.status', 'submitted')->assertJsonPath('data.task.approved_at', null);
        $this->asManager(1)->post(self::WEB."/tasks/$id/actions", ['action' => 'return', 'message' => 'Add a photo.'])->assertRedirect();
        $this->asMobile(2)->getJson(self::API.'/tasks?tab=returned')->assertJsonCount(1, 'data.data');
        $this->postJson($path, ['action' => 'start'])->assertOk();
        $this->postJson($path, ['action' => 'submit', 'message' => 'Photo attached'])->assertOk();
        $pending = DB::table('daily_entries')->where('status', 'submitted')->value('id');
        $old = DB::table('daily_entries')->where('status', 'returned')->value('id');
        $this->asManager(1)->postJson(self::WEB.'/tasks/review', ['approvals' => [$id => $old]])->assertConflict();
        $this->get(self::WEB."?assignment=$id")->assertOk()->assertSee('Current submission')->assertSee('Photo attached');
        $this->post(self::WEB.'/tasks/review', ['approvals' => [$id => $pending]])->assertRedirect();
        $this->assertDatabaseHas('daily_task_assignments', ['id' => $id, 'status' => 'approved', 'approved_at' => RecordTime::now()]);
        $this->post(self::WEB."/tasks/$id/actions", ['action' => 'close'])->assertRedirect();
        $this->assertDatabaseHas('daily_task_assignments', ['id' => $id, 'status' => 'closed']);
        $this->assertDatabaseCount('daily_entries', 2);
        $this->assertDatabaseHas('daily_entries', ['assignment_id' => $id, 'status' => 'returned']);
        $this->assertDatabaseHas('daily_entries', ['assignment_id' => $id, 'status' => 'approved']);
    }

    public function test_department_location_scope_multi_assignment_reassignment_and_cancel(): void
    {
        $this->asMobile(2)->getJson(self::API.'/capabilities')->assertJsonPath('data.assign_tasks', false);
        $this->postJson(self::API.'/assign', $this->task(['employee_ids' => [3]]))->assertForbidden();

        $this->asMobile(1)->getJson(self::API.'/employees')->assertJsonCount(2, 'data.data');
        $this->postJson(self::API.'/assign', $this->task(['employee_ids' => [2, 4, 5, 6]]))->assertUnprocessable();
        $this->postJson(self::API.'/assign', $this->task(['department_id' => 1, 'branch_id' => 1]))->assertCreated();
        $this->assertDatabaseCount('daily_task_assignments', 2);
        $summary = app(Reporting::class);
        [$tasks, $records] = $summary->queries(User::withoutGlobalScope('branch')->findOrFail(1), 'tasks', []);
        $counts = $summary->summary($tasks, $records, null, null);
        $this->assertSame(1, (int) $counts['Total tasks']);
        $this->assertSame(2, (int) $counts['Total assignments']);
        $this->postJson(self::API.'/assign', $this->task(['employee_ids' => [2]]))->assertCreated();
        $id = DB::table('daily_task_assignments')->max('id');
        $action = self::WEB."/tasks/$id/actions";
        $this->asManager(1)->post($action, ['action' => 'reassign', 'employee_id' => 4])->assertNotFound();
        $this->post($action, ['action' => 'reassign', 'employee_id' => 3])->assertRedirect();
        $newId = DB::table('daily_task_assignments')->max('id');
        $this->assertDatabaseHas('daily_task_assignments', ['id' => $id, 'status' => 'cancelled', 'superseded_at' => RecordTime::now()]);
        $this->post(self::WEB."/tasks/$newId/actions", ['action' => 'cancel'])->assertRedirect();
        $this->assertDatabaseHas('daily_task_assignments', ['id' => $newId, 'employee_id' => 3, 'status' => 'cancelled']);

        $this->asMobile(2)->getJson(self::API."/tasks/$newId")->assertNotFound();
        DB::table('users')->where('id', 3)->update(['branch_id' => 2]);
        $this->asManager(1)->post(self::WEB."/tasks/$newId/actions", ['action' => 'reopen'])->assertNotFound();
    }

    public function test_recurring_tasks_overdue_reminders_and_inbox_delivery_are_idempotent(): void
    {
        $this->asMobile(1)->postJson(self::API.'/assign', $this->task([
            'employee_ids' => [2], 'frequency' => 'daily',
            'remind_at' => '2026-11-21T01:20', 'due_at' => '2026-11-21T02:00',
        ]))->assertCreated();
        $this->artisan('daily-records:process')->assertSuccessful();
        $this->artisan('daily-records:process')->assertSuccessful();
        $this->assertDatabaseCount('user_notifications', 2);
        $this->assertDatabaseHas('notifications', ['title' => 'New task assignment', 'is_active' => false]);

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-21 18:30:00', 'UTC'));
        $this->artisan('daily-records:process')->assertSuccessful();
        $this->artisan('daily-records:process')->assertSuccessful();
        $this->assertDatabaseCount('daily_tasks', 2);
        $this->assertDatabaseCount('daily_task_assignments', 2);
        $this->assertDatabaseCount('user_notifications', 5);
        $this->assertDatabaseHas('notifications', ['title' => 'Employee task overdue', 'created_by' => 1]);

        $this->asMobile(2)->getJson(self::API.'/tasks?tab=overdue')->assertJsonCount(1, 'data.data');
        $this->getJson(self::API.'/tasks?tab=today')->assertJsonCount(1, 'data.data');
    }

    public function test_creation_retries_return_the_same_task_and_record(): void
    {
        $input = $this->task(['employee_ids' => [2], 'request_id' => '0bd06319-0545-4f8e-bf7d-60c20bc1f4eb']);
        $this->asMobile(1)->postJson(self::API.'/assign', $input)->assertCreated()->assertJsonPath('data.id', 1);
        $this->postJson(self::API.'/assign', $input)->assertCreated()->assertJsonPath('data.id', 1);
        $this->assertDatabaseCount('daily_task_assignments', 1);
        $this->assertDatabaseCount('daily_record_notifications', 1);

        $record = ['kind' => 'note', 'title' => 'My note', 'request_id' => 'dd188a42-94b0-44b1-9130-f492084da112'];
        $this->asMobile(2)->postJson(self::API.'/records', $record)->assertCreated()->assertJsonPath('data.id', 1);
        $this->postJson(self::API.'/records', $record)->assertCreated()->assertJsonPath('data.id', 1);
        $this->asMobile(3)->postJson(self::API.'/records', $record)->assertCreated()->assertJsonPath('data.id', 2);
        $this->assertDatabaseCount('daily_entries', 2);
    }

    public function test_private_notes_reports_incidents_extensions_and_exports(): void
    {
        Storage::fake('local', ['root' => sys_get_temp_dir().'/hrky-daily-test-files']);
        $this->asMobile(2)->post(self::API.'/records', [
            'kind' => 'note', 'title' => 'Private personal note', 'body' => 'Private content', 'private' => 1,
            'files' => [UploadedFile::fake()->createWithContent('private.txt', 'Private attachment')],
        ], ['Accept' => 'application/json'])->assertCreated();
        $private = DB::table('daily_entries')->value('id');
        $file = DB::table('daily_record_files')->value('id');
        $this->get(self::API."/records/$private/files/$file")->assertOk();
        $this->asMobile(3)->getJson(self::API."/records/$private")->assertNotFound();

        $this->grantForRole(RecordAccess::SUBMIT_REPORT, 2);
        $this->asMobile(2)->postJson(self::API.'/records', ['kind' => 'report', 'title' => 'Shift report', 'body' => 'Inventory checked'])->assertCreated();
        $report = DB::table('daily_entries')->where('kind', 'report')->value('id');
        $this->postJson(self::API.'/records', ['kind' => 'incident', 'title' => 'Stock issue', 'body' => 'Missing items', 'critical' => true])->assertCreated();
        $incident = DB::table('daily_entries')->where('kind', 'incident')->value('id');
        $this->postJson(self::API."/records/$incident/actions", ['action' => 'resolve', 'message' => 'Reconciled inventory'])->assertOk();
        $this->postJson(self::API.'/records', ['kind' => 'todo', 'title' => 'Personal reminder'])->assertCreated();
        $todo = DB::table('daily_entries')->where('kind', 'todo')->value('id');
        $this->postJson(self::API."/records/$todo/actions", ['action' => 'complete'])->assertJsonPath('data.entry.status', 'completed');

        $this->asMobile(1)->postJson(self::API.'/assign', $this->task(['employee_ids' => [2]]))->assertCreated();
        $assignment = DB::table('daily_task_assignments')->value('id');
        $this->asMobile(2)->postJson(self::API.'/records', ['kind' => 'extension', 'assignment_id' => $assignment,
            'title' => 'Need another hour', 'body' => 'Waiting for supplier', 'requested_due_at' => '2026-11-21T16:00'])->assertCreated();
        $extension = DB::table('daily_entries')->where('kind', 'extension')->value('id');

        $this->asManager(1)->get(self::WEB."?entry=$private")->assertNotFound();
        $this->get(self::WEB."/records/$private/files/$file")->assertNotFound();
        $this->post(self::WEB."/records/$report/review", ['action' => 'approve'])->assertRedirect();
        $this->post(self::WEB."/records/$extension/review", ['action' => 'approve'])->assertRedirect();
        $this->assertDatabaseHas('daily_task_assignments', ['id' => $assignment, 'due_at' => '2026-11-21 09:00:00']);
        $this->get(self::WEB.'?view=daily&date=2026-11-21')->assertOk()
            ->assertSee('Shift report')->assertSee('Stock issue')->assertDontSee('Private personal note')->assertDontSee('Personal reminder');
        $this->get(self::WEB.'?view=monthly&month=2026-11')->assertOk()->assertSee('Shift report');
        $this->get(self::WEB.'?view=performance&month=2026-11')->assertOk()->assertSee('Operations One');
        $this->get(self::WEB.'?view=department')->assertOk()->assertSee('Operations');
        $this->get(self::WEB.'?view=location')->assertOk()->assertSee('Phnom Penh');
        $this->get(self::WEB.'?view=calendar&month=2026-11')->assertOk()->assertSee('Inventory');
        $this->get(self::WEB.'?view=timeline&employee_id=2')->assertOk()->assertSee('Stock issue');
        $this->get(self::WEB.'?view=daily&date=2026-11-20')->assertOk()->assertDontSee('Shift report');
        $this->get(self::WEB.'/export/xlsx?view=monthly&month=2026-11')->assertOk()->assertDownload('daily-records-monthly.xlsx');
        $this->get(self::WEB.'/export/pdf?view=monthly&month=2026-11')->assertOk()->assertDownload('daily-records-monthly.pdf');
        $this->get(self::WEB.'/export/xlsx?view=performance&month=2026-11')->assertOk()->assertDownload('daily-records-performance.xlsx');

        $reports = app(Reporting::class);
        [$tasks, $entries] = $reports->queries(User::withoutGlobalScope('branch')->findOrFail(1), 'monthly', ['month' => '2026-11']);
        $rows = $reports->exportRows($tasks, $entries)->get();
        $this->assertSame(1, $rows->where('title', 'Inventory')->count());
        $this->assertSame(1, $rows->where('title', 'Shift report')->count());
        $this->assertSame(0, $rows->where('title', 'Private personal note')->count());
    }

    public function test_classified_activity_filters_by_occurrence_and_retains_historical_classification(): void
    {
        [$category, $sub] = $this->category('activity', 'Sales', 'Sales follow-up');
        $input = ['kind' => 'activity', 'category_id' => $category, 'subcategory_id' => $sub,
            'title' => 'Customer follow-up', 'body' => 'Confirmed the delivery time', 'activity_date' => '2026-11-20',
            'start_time' => '11:00', 'end_time' => '12:15', 'employee_id' => 3];
        $this->asMobile(2)->getJson(self::API.'/catalog?kind=activity')->assertOk()->assertJsonFragment(['name_en' => 'Sales']);
        $this->postJson(self::API.'/work-records', $input)->assertCreated();
        $id = DB::table('daily_entries')->value('id');
        $this->assertDatabaseHas('daily_entries', ['id' => $id, 'employee_id' => 2, 'occurred_at' => '2026-11-20 04:00:00']);
        $this->asMobile(3)->postJson(self::API."/work-records/$id", $input)->assertNotFound();
        $this->asManager(1)->get(self::WEB.'?view=daily&date=2026-11-20&kind=activity')->assertOk()->assertSee('Customer follow-up');
        $this->get(self::WEB.'?view=daily&date=2026-11-21&kind=activity&fragment=1')->assertOk()->assertDontSee('Customer follow-up');

        $this->get(self::WEB.'/categories')->assertForbidden();
        $this->grant('daily_records.categories');
        $this->get(self::WEB."/categories?edit=$category")->assertOk()->assertSee('Sales');
        $this->post(self::WEB.'/categories', [
            'id' => $category, 'kind' => 'activity', 'name_en' => 'New sales', 'name_km' => 'ការលក់',
            'active' => 0, 'position' => 0, 'sensitive' => 0, 'fields' => '[]',
        ])->assertRedirect();
        $this->asMobile(2)->postJson(self::API.'/work-records', $input)->assertUnprocessable();
        $this->postJson(self::API."/work-records/$id", $input + ['revision' => 1])->assertOk();
        $this->assertStringContainsString('"en":"Sales"', DB::table('daily_entries')->where('id', $id)->value('classification'));
    }

    public function test_incident_workflow_confidential_findings_and_scoped_assignment(): void
    {
        [$category, $sub] = $this->category('incident', 'Customer Issues', 'Buyback request declined');
        $input = ['kind' => 'incident', 'title' => 'Customer enquiry', 'body' => 'Customer asked to sell a phone',
            'category_id' => $category, 'subcategory_id' => $sub, 'occurred_at' => '2026-11-21T01:20'];
        $this->asMobile(2)->postJson(self::API.'/work-records', $input)->assertUnprocessable();
        $this->postJson(self::API.'/work-records', $input + ['fields' => ['buyback_reason' => 'Price disagreement']])->assertCreated();
        $public = DB::table('daily_entries')->max('id');
        [$category, $sub] = $this->category('incident', 'Employee Issues', 'Workplace conflict');
        $this->postJson(self::API.'/work-records', array_replace($input, [
            'title' => 'Confidential evidence', 'category_id' => $category, 'subcategory_id' => $sub,
        ]))->assertCreated();
        $secret = DB::table('daily_entries')->max('id');

        $this->asManager(1)->get(self::WEB."?entry=$secret")->assertNotFound();
        $this->get(self::WEB."/incidents/$secret")->assertNotFound();
        $this->get(self::WEB.'?kind=incident')->assertOk()->assertDontSee('Confidential evidence');
        $this->postJson(self::WEB."/incidents/$public", ['action' => 'state', 'state' => 'closed'])->assertConflict();
        $this->post(self::WEB."/incidents/$public", ['action' => 'state', 'state' => 'acknowledged'])->assertRedirect();
        $this->get(self::WEB."/incidents/$public")->assertOk()->assertSee('Assign responsibility');
        $this->postJson(self::WEB."/incidents/$public", ['action' => 'assign', 'responsible_id' => 4])->assertNotFound();
        $this->post(self::WEB."/incidents/$public", ['action' => 'assign', 'responsible_id' => 3])->assertRedirect();
        $this->asMobile(3)->getJson(self::API."/assigned-incidents/$public")->assertOk();
        $this->getJson(self::API."/records/$public")->assertNotFound();
        $this->postJson(self::API."/assigned-incidents/$public/actions", ['message' => 'Contacted customer'])->assertOk();

        $this->grant('daily_records.sensitive');
        $this->asManager(1)->get(self::WEB."/incidents/$secret")->assertOk()->assertSee('Confidential verification');
        $this->post(self::WEB."/incidents/$secret", ['action' => 'verify', 'verification' => 'under_investigation',
            'message' => 'Restricted finding text'])->assertRedirect();
        $this->assertDatabaseHas('daily_entries', ['id' => $secret, 'status' => 'reported', 'verification' => 'under_investigation']);
        $this->asMobile(2)->getJson(self::API."/records/$secret")->assertOk()->assertDontSee('Restricted finding text');
        $this->getJson(self::API.'/summaries/preview?date=2026-11-21')->assertJsonCount(1, 'data.records.data');
        $this->asManager(1)->post(self::WEB."/incidents/$public", ['action' => 'state', 'state' => 'resolved', 'solution' => 'Agreed next steps'])->assertRedirect();
        $this->post(self::WEB."/incidents/$public", ['action' => 'state', 'state' => 'closed'])->assertRedirect();
        $this->assertDatabaseHas('daily_entries', ['id' => $public, 'status' => 'closed', 'solution' => 'Agreed next steps']);
    }

    public function test_daily_summary_versioning_idempotency_scope_and_legacy_review_guard(): void
    {
        DB::table('permission_roles')->insert([
            'role_id' => 2,
            'permission_id' => DB::table('permissions')->where('permission_key', RecordAccess::SUBMIT_REPORT)->value('id'),
        ]);
        [$category, $sub] = $this->category('activity', 'Sales', 'Sales follow-up');
        $this->asMobile(2)->postJson(self::API.'/work-records', ['kind' => 'activity', 'title' => 'Sales work',
            'body' => 'Confirmed quote', 'category_id' => $category, 'subcategory_id' => $sub,
            'activity_date' => '2026-11-21'])->assertCreated();
        $work = DB::table('daily_entries')->value('id');
        $this->getJson(self::API.'/summaries/preview?date=2026-11-21')->assertOk()->assertJsonCount(1, 'data.records.data');
        $data = ['report_date' => '2026-11-21', 'revision' => 0, 'action' => 'save', 'record_ids' => [$work], 'summary' => 'Draft work'];
        $this->postJson(self::API.'/summaries', $data)->assertOk();
        $id = DB::table('daily_report_submissions')->value('entry_id');
        $this->asMobile(1)->getJson(self::API."/summary-inbox/$id")->assertNotFound();
        $data = array_replace($data, ['revision' => 1, 'action' => 'submit', 'summary' => 'Submitted work']);
        $this->asMobile(2)->postJson(self::API.'/summaries', $data)->assertOk()->assertJsonPath('data.id', $id);
        $this->postJson(self::API.'/summaries', $data)->assertOk()->assertJsonPath('data.id', $id);
        $this->assertDatabaseCount('daily_report_versions', 1);
        $this->postJson(self::API."/summary-inbox/$id/actions", ['action' => 'approve','version' => 1])->assertForbidden();
        $this->asManager(1)->get(self::WEB."/submissions/$id")->assertOk()->assertSee('Sales work')->assertSee('Submitted work');
        $this->postJson(self::WEB."/records/$id/review", ['action' => 'approve'])->assertConflict();
        $this->post(self::WEB."/submissions/$id/review", ['action' => 'start', 'version' => 1])->assertRedirect();
        $this->post(self::WEB."/submissions/$id/review", ['action' => 'return', 'version' => 1, 'message' => 'Explain the quote'])->assertRedirect();

        $data['revision'] = (int) DB::table('daily_entries')->where('id', $id)->value('revision');
        $data['summary'] = 'Corrected summary';
        $this->asMobile(2)->postJson(self::API.'/summaries', $data)->assertOk();
        $this->assertDatabaseCount('daily_report_versions', 2);
        $this->asMobile(1)->postJson(self::API."/summary-inbox/$id/actions", ['action' => 'approve', 'version' => 1])->assertConflict();
        $this->postJson(self::API."/summary-inbox/$id/actions", ['action' => 'approve', 'version' => 2])
            ->assertOk()->assertJsonPath('data.report.status', 'approved');
        $this->getJson(self::API."/summary-inbox/$id?version=1")->assertOk()->assertJsonPath('data.content.summary', 'Submitted work');
        $this->asManager(1)->get(self::WEB.'/export/xlsx?view=monthly&month=2026-11&kind=report')->assertDownload('daily-records-monthly.xlsx');
    }

    public function test_daily_report_permission_and_current_day_submission_policy(): void
    {
        $this->asMobile(2)->getJson(self::API.'/capabilities')->assertOk()
            ->assertJsonPath('data.can_submit_report', false)
            ->assertJsonPath('data.reporting_today', '2026-11-21');
        $this->asMobile(1)->getJson(self::API.'/capabilities')->assertOk()
            ->assertJsonPath('data.assign_tasks', true)
            ->assertJsonPath('data.can_submit_report', false);

        $this->asMobile(2)->postJson(self::API.'/summaries', [
            'report_date' => '2026-11-21', 'revision' => 0, 'action' => 'submit', 'summary' => 'Shift work',
        ])->assertForbidden();

        DB::table('permission_roles')->insert([
            'role_id' => 2,
            'permission_id' => DB::table('permissions')->where('permission_key', RecordAccess::SUBMIT_REPORT)->value('id'),
        ]);
        $this->getJson(self::API.'/capabilities')->assertOk()
            ->assertJsonPath('data.can_submit_report', true);

        foreach (['2026-11-20', '2026-11-22'] as $date) {
            $this->postJson(self::API.'/summaries', [
                'report_date' => $date, 'revision' => 0, 'action' => 'submit', 'summary' => 'Shift work',
            ])->assertUnprocessable();
            $this->postJson(self::API.'/records', [
                'kind' => 'report', 'report_date' => $date, 'title' => 'Daily report', 'body' => 'Shift work',
            ])->assertUnprocessable();
        }

        $this->postJson(self::API.'/summaries', [
            'report_date' => '2026-11-21', 'revision' => 0, 'action' => 'save', 'summary' => 'Draft for today',
        ])->assertOk();
        $this->postJson(self::API.'/summaries', [
            'report_date' => '2026-11-21', 'revision' => 1, 'action' => 'submit', 'summary' => 'Submitted today',
        ])->assertOk();
        $this->postJson(self::API.'/records', [
            'kind' => 'report', 'report_date' => '2026-11-21', 'title' => 'Daily report', 'body' => 'Shift work',
        ])->assertCreated();
    }

    public function test_reclassified_incident_is_removed_from_older_report_versions(): void
    {
        Storage::fake('local', ['root' => sys_get_temp_dir().'/hrky-work-records-restricted']);
        [$category, $child] = $this->category('incident', 'Other Incidents', 'Other incident');
        $this->asMobile(2)->postJson(self::API.'/work-records', [
            'kind' => 'incident', 'category_id' => $category, 'subcategory_id' => $child,
            'title' => 'Confidential after review', 'body' => 'An incident for review', 'occurred_at' => '2026-11-21T01:00',
        ])->assertCreated();
        $case = DB::table('daily_entries')->value('id');
        $this->grantForRole(RecordAccess::SUBMIT_REPORT, 2);
        $this->postJson(self::API.'/summaries', [
            'report_date' => '2026-11-21', 'revision' => 0, 'action' => 'submit', 'record_ids' => [$case], 'summary' => 'Shift summary',
        ])->assertOk();
        $summary = DB::table('daily_report_submissions')->value('entry_id');
        $this->asMobile(1)->getJson(self::API."/summary-inbox/$summary")->assertJsonCount(1, 'data.records');

        [$category, $child] = $this->category('incident', 'Employee Issues', 'Workplace conflict');
        $this->asManager(1)->postJson(self::WEB."/incidents/$case", ['action' => 'reclassify',
            'category_id' => $category, 'subcategory_id' => $child])->assertForbidden();
        $this->grant('daily_records.sensitive');
        $this->post(self::WEB."/incidents/$case", ['action' => 'reclassify',
            'category_id' => $category, 'subcategory_id' => $child])->assertRedirect();
        $this->post(self::WEB."/incidents/$case", ['action' => 'verify', 'verification' => 'under_investigation',
            'message' => 'Internal investigation', 'files' => [UploadedFile::fake()->createWithContent('restricted.txt', 'Private evidence')]])->assertRedirect();
        $file = DB::table('daily_record_files')->where('entry_id', $case)->value('id');
        $this->get(self::WEB."/records/$case/files/$file")->assertOk();
        $this->asMobile(2)->getJson(self::API."/records/$case")->assertJsonCount(0, 'data.files')->assertDontSee('Internal investigation');
        $this->get(self::API."/records/$case/files/$file")->assertNotFound();
        $this->asMobile(1)->getJson(self::API."/summary-inbox/$summary?version=1")->assertJsonCount(0, 'data.records')
            ->assertDontSee('Confidential after review');
        $this->asMobile(3)->getJson(self::API."/summary-inbox/$summary")->assertForbidden();
        $this->asManager(3)->get(self::WEB.'/export/pdf?kind=incident')->assertForbidden();
    }

    public function test_private_note_drafts_pinning_archiving_search_and_recovery(): void
    {
        $this->asMobile(2)->postJson(self::API.'/drafts/note', ['payload' => '{"kind":"note","title":"Unfinished note"}'])->assertOk();
        $this->assertStringNotContainsString('Unfinished note', DB::table('daily_record_drafts')->value('payload'));
        $this->getJson(self::API.'/drafts/note')->assertJsonPath('data.draft.title', 'Unfinished note');
        $this->asMobile(3)->getJson(self::API.'/drafts/note')->assertJsonPath('data.draft', null);

        $this->asMobile(2)->postJson(self::API.'/work-records', ['kind' => 'note', 'title' => 'Private follow-up', 'tags' => ['follow-up']])->assertCreated();
        $id = DB::table('daily_entries')->value('id');
        $this->postJson(self::API."/work-records/$id/note", ['action' => 'pin'])->assertOk();
        $this->getJson(self::API.'/work-records?search=follow&tag=follow-up')->assertJsonPath('data.data.0.id', $id);
        $this->postJson(self::API."/work-records/$id/note", ['action' => 'archive'])->assertOk();
        $this->getJson(self::API.'/work-records?kind=note')->assertJsonCount(0, 'data.data');
        $this->getJson(self::API.'/work-records?archive=archived')->assertJsonCount(1, 'data.data');
        $this->postJson(self::API."/work-records/$id/note", ['action' => 'restore'])->assertOk();
        $this->asManager(1)->get(self::WEB."?entry=$id")->assertNotFound();
    }

    public function test_rich_personal_note_preserves_formatting_and_private_inline_image(): void
    {
        Storage::fake('local', ['root' => sys_get_temp_dir().'/hrky-rich-note-files']);
        $name = 'note-image-1f376b00-30b8-4e08-a651-1a00d6b17b4d.png';
        $ops = [
            ['insert' => 'Plan', 'attributes' => ['bold' => true, 'font' => 'GoogleSans']],
            ['insert' => "\n", 'attributes' => ['header' => 2]],
            ['insert' => 'Reference', 'attributes' => ['link' => 'https://example.test/reference']],
            ['insert' => "\n"],
            ['insert' => ['image' => $name]],
            ['insert' => "\n"],
        ];
        $data = ['kind' => 'note', 'title' => 'Private plan', 'note_document' => json_encode($ops, JSON_THROW_ON_ERROR)];
        $this->asMobile(2)->post(self::API.'/work-records', [
            'payload' => json_encode($data, JSON_THROW_ON_ERROR),
            'files' => [UploadedFile::fake()->image($name)],
        ], ['Accept' => 'application/json'])->assertCreated();
        $id = DB::table('daily_entries')->value('id');
        $file = DB::table('daily_record_files')->where('entry_id', $id)->value('id');
        $get = $this->getJson(self::API."/records/$id")->assertOk()->assertJsonPath('data.entry.body', "Plan\nReference");
        $this->assertSame($ops, json_decode($get->json('data.entry.details'), true)['note_document']);
        $this->get(self::API."/records/$id/files/$file")->assertOk();

        $this->asMobile(3)->get(self::API."/records/$id/files/$file")->assertNotFound();
        $this->postJson(self::API.'/work-records', $data)->assertUnprocessable();
        $this->asManager(1)->get(self::WEB."?entry=$id")->assertNotFound();

        $data['revision'] = 1;
        $this->asMobile(2)->postJson(self::API."/work-records/$id", $data)->assertOk();
        $this->assertDatabaseCount('daily_record_files', 1);
        $data['note_document'] = json_encode([
            ['insert' => "Unsafe\n", 'attributes' => ['link' => 'javascript:alert(1)']],
        ], JSON_THROW_ON_ERROR);
        $this->postJson(self::API.'/work-records', $data)->assertUnprocessable();
    }

    private function category(string $kind, string $parent, string $child): array
    {
        $id = DB::table('daily_record_categories')->where('kind', $kind)->whereNull('parent_id')->where('name_en', $parent)->value('id');
        return [$id, DB::table('daily_record_categories')->where('parent_id', $id)->where('name_en', $child)->value('id')];
    }

    private function grant(string $permission): void
    {
        $this->grantForRole($permission, 1);
    }

    private function grantForRole(string $permission, int $role): void
    {
        DB::table('permission_roles')->insert(['role_id' => $role,
            'permission_id' => DB::table('permissions')->where('permission_key', $permission)->value('id')]);
    }

    private function task(array $values): array
    {
        return $values + ['title' => 'Inventory', 'priority' => 'normal', 'due_at' => '2026-11-21T14:00'];
    }

    private function asMobile(int $id): self
    {
        return $this->actingAs(User::withoutGlobalScope('branch')->findOrFail($id), 'api');
    }

    private function asManager(int $id): self
    {
        return $this->actingAs(User::withoutGlobalScope('branch')->findOrFail($id), 'web');
    }

    private function schema(): void
    {
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
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('slug');
            $t->boolean('backend_login_authorize')->default(true);
        });
        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedBigInteger('company_id');
        });
        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->string('dept_name');
            $t->unsignedBigInteger('dept_head_id')->nullable();
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
        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('description');
            $t->string('type');
            $t->dateTime('notification_publish_date');
            $t->boolean('is_active');
            $t->unsignedBigInteger('company_id');
            $t->unsignedBigInteger('created_by');
            $t->timestamps();
        });
        Schema::create('user_notifications', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('notification_id');
            $t->unsignedBigInteger('user_id');
            $t->boolean('is_seen');
            $t->timestamps();
        });
    }
}
