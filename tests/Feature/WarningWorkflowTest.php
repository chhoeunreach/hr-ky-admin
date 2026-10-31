<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\WarningController;
use App\Models\User;
use App\Models\Warning;
use App\Repositories\WarningRepository;
use App\Requests\Warning\WarningRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WarningWorkflowTest extends TestCase
{
    private array $abilities = ['create_warning', 'update_warning', 'show_warning', 'delete_warning'];

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
        Schema::create('app_settings', function (Blueprint $table) { $table->id(); $table->string('slug'); $table->integer('status'); });
        Schema::create('branches', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('logo')->nullable(); });
        Schema::create('departments', function (Blueprint $table) { $table->id(); $table->string('dept_name'); $table->integer('branch_id'); });
        Schema::create('posts', function (Blueprint $table) { $table->id(); $table->string('post_name'); });
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name');
            foreach (['english_name', 'employee_code', 'username'] as $field) $table->string($field)->nullable();
            foreach (['branch_id', 'department_id', 'post_id'] as $field) $table->integer($field)->nullable();
            $table->softDeletes();
        });
        DB::table('branches')->insert([['id' => 1, 'name' => 'Branch A', 'logo' => 'branch-a.png'], ['id' => 2, 'name' => 'Branch B', 'logo' => null]]);
        DB::table('departments')->insert([['id' => 1, 'dept_name' => 'Sales', 'branch_id' => 1], ['id' => 2, 'dept_name' => 'Support', 'branch_id' => 2]]);
        DB::table('users')->insert([
            ['id' => 10, 'name' => 'HR Officer', 'branch_id' => null, 'department_id' => null, 'employee_code' => 'HR10'],
            ['id' => 20, 'name' => 'Employee A', 'branch_id' => 1, 'department_id' => 1, 'employee_code' => 'KY0020'],
            ['id' => 21, 'name' => 'Employee B', 'branch_id' => 1, 'department_id' => 1, 'employee_code' => 'KY0021'],
            ['id' => 30, 'name' => 'Other Branch', 'branch_id' => 2, 'department_id' => 2, 'employee_code' => 'KY0030'],
        ]);
        foreach (['2025_02_04_164535_create_warnings_table.php', '2025_02_04_170130_create_warning_departments_table.php', '2025_02_04_170452_create_warning_employees_table.php', '2025_02_04_170701_create_warning_responses_table.php', '2026_10_31_000001_expand_warning_message_column.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        $this->actingAs(User::findOrFail(10));
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        Route::post('/_test/warnings', fn (WarningRequest $request) => app(WarningController::class)->store($request));
        Route::put('/_test/warnings/{id}', fn (WarningRequest $request, $id) => app(WarningController::class)->update($request, $id));
        Route::get('/_test/warnings/{id}', fn ($id) => app(WarningController::class)->show($id));
    }

    private function payload(): array
    {
        return ['employee_id' => [20, 21], 'warning_date' => now()->format('Y-m-d'), 'subject' => 'Repeated late arrival', 'message' => '<p>'.str_repeat('Recorded warning details. ', 30).'</p>'];
    }

    public function test_create_and_update_infer_branch_and_departments_without_notification(): void
    {
        $this->post('/_test/warnings', $this->payload())->assertRedirect(route('admin.warning.index'))->assertSessionHasNoErrors();
        $warning = Warning::firstOrFail();
        $this->assertSame(1, $warning->branch_id);
        $this->assertGreaterThan(255, strlen($warning->message));
        $this->assertEquals([20, 21], $warning->warningEmployee->pluck('employee_id')->all());
        $this->assertEquals([1], $warning->warningDepartment->pluck('department_id')->all());
        $data = array_replace($this->payload(), ['employee_id' => [21], 'subject' => 'Updated warning']);
        $this->put('/_test/warnings/'.$warning->id, $data)->assertRedirect(route('admin.warning.index'));
        $this->assertSame('Updated warning', $warning->fresh()->subject);
        $this->assertEquals([21], $warning->fresh()->warningEmployee->pluck('employee_id')->all());
    }

    public function test_missing_and_malformed_dates_return_validation_errors(): void
    {
        foreach (['', 'not-a-date', '2026-02-31'] as $date) {
            $this->postJson('/_test/warnings', array_replace($this->payload(), ['warning_date' => $date]))->assertUnprocessable()->assertJsonPath('status', false);
        }
        $this->assertDatabaseCount('warnings', 0);
    }

    public function test_cross_branch_recipients_and_duplicate_ids_are_rejected(): void
    {
        $this->postJson('/_test/warnings', array_replace($this->payload(), ['employee_id' => [20, 30]]))->assertUnprocessable()->assertJsonPath('message', __('index.warning_same_branch_required'));
        $this->postJson('/_test/warnings', array_replace($this->payload(), ['employee_id' => [20, 20]]))->assertUnprocessable()->assertJsonPath('status', false);
    }

    public function test_print_letter_renders_each_saved_recipient_and_branch_logo_in_both_languages(): void
    {
        $this->post('/_test/warnings', $this->payload());
        $warning = app(WarningRepository::class)->find(Warning::firstOrFail()->id, ['*'], [
            'branch', 'createdBy', 'warningEmployee.employee.department', 'warningEmployee.employee.post', 'warningReply',
        ]);
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $html = view('admin.warning.common.print-form', ['warningDetail' => $warning])->render();
            $this->assertSame(2, substr_count($html, '<article class="warning-print-paper" data-warning-a4>'));
            $this->assertStringContainsString('uploads/branch/branch-a.png', $html);
            $this->assertStringContainsString('KY0020', $html);
            $this->assertStringContainsString(__('index.warning_number') . ': ' . $warning->warning_number, $html);
            $this->assertStringContainsString('KY0021', $html);
            $this->assertStringContainsString('Recorded warning details.', $html);
            $this->assertStringContainsString('A4 portrait', $html);
            $this->assertStringNotContainsString('index.warning_print_', $html);
        }
    }

    public function test_print_message_preserves_paragraphs_and_excludes_scripts(): void
    {
        $warning = new Warning(['message' => '<p>First &amp; second</p><p>Next<br>Line</p><script>alert(1)</script>']);
        $this->assertSame("First & second\nNext\nLine", $warning->printable_message);
    }

    public function test_existing_warning_numbers_are_stable_and_distinct_from_row_positions(): void
    {
        $warning = (new Warning())->forceFill(['id' => 72, 'subject' => 'Original']);
        $this->assertSame('WRN-00072', $warning->warning_number);
        $warning->subject = 'Edited';
        $this->assertSame('WRN-00072', $warning->warning_number);
        $other = (new Warning())->forceFill(['id' => 73]);
        $this->assertNotSame($warning->warning_number, $other->warning_number);
        $this->assertNull((new Warning())->warning_number);
    }

    public function test_print_details_require_permission_and_missing_warning_returns_404(): void
    {
        $this->get('/_test/warnings/999')->assertNotFound();
        $this->abilities = [];
        $this->get('/_test/warnings/999')->assertForbidden();
    }
}
