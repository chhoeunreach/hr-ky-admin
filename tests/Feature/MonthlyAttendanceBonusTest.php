<?php

namespace Tests\Feature;

use App\Models\EmployeePayslip;
use App\Models\User;
use App\Services\Payroll\MonthlyAttendanceBonusService;
use App\Services\Payroll\MonthlyPayrollRecordService;
use App\Services\Payroll\GeneratePayrollService;
use App\Models\Branch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonthlyAttendanceBonusTest extends TestCase
{
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
        $this->travelTo(now()->setDate(2026, 11, 1)->startOfDay());
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('username')->nullable(); $t->string('employee_code')->nullable();
            $t->integer('company_id'); $t->integer('branch_id'); $t->integer('department_id'); $t->integer('post_id');
            $t->date('joining_date')->nullable(); $t->softDeletes();
        });
        Schema::create('companies', function (Blueprint $t) { $t->id(); $t->text('weekend'); });
        Schema::create('branches', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('posts', function (Blueprint $t) { $t->id(); $t->string('post_name'); });
        Schema::create('departments', function (Blueprint $t) { $t->id(); $t->string('dept_name'); });
        Schema::create('attendances', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->date('attendance_date'); $t->integer('attendance_status');
            foreach (['check_in_at', 'check_out_at', 'night_checkin', 'night_checkout'] as $field) $t->string($field)->nullable();
        });
        Schema::create('holidays', function (Blueprint $t) { $t->id(); $t->integer('company_id'); $t->integer('is_active'); $t->date('event_date'); });
        Schema::create('leave_types', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('slug'); });
        Schema::create('leave_requests_master', function (Blueprint $t) {
            $t->id(); $t->integer('requested_by'); $t->integer('leave_type_id'); $t->date('leave_from'); $t->date('leave_to'); $t->string('status');
        });
        Schema::create('time_leaves', function (Blueprint $t) { $t->id(); $t->integer('requested_by'); $t->date('issue_date'); $t->string('status'); });
        Schema::create('employee_payslips', function (Blueprint $t) {
            $t->id(); foreach ((new EmployeePayslip)->getFillable() as $field) $t->string($field)->nullable();
        });
        Schema::create('salary_components', function (Blueprint $t) {
            $t->id(); foreach (['name', 'component_type', 'value_type'] as $field) $t->string($field);
            $t->decimal('annual_component_value')->nullable(); $t->boolean('status'); $t->boolean('apply_for_all');
            $t->integer('created_by')->nullable(); $t->integer('updated_by')->nullable(); $t->timestamps();
        });
        foreach (['employee_payslip_details', 'employee_payslip_additional_details'] as $table) Schema::create($table, function (Blueprint $t) {
            $t->id(); $t->integer('employee_payslip_id'); $t->integer('salary_component_id'); $t->decimal('amount', 12, 2);
        });
        foreach (['sell_out_reports', 'advance_salaries'] as $table) Schema::create($table, function (Blueprint $t) {
            $t->id(); $t->integer('user_id')->nullable(); $t->integer('employee_id')->nullable(); $t->string('service_type')->nullable();
            $t->decimal('commission')->nullable(); $t->decimal('released_amount')->nullable(); $t->string('status')->nullable();
            $t->date('amount_granted_date')->nullable(); $t->timestamps();
        });
        (require database_path('migrations/2026_10_30_000001_create_monthly_payroll_records_table.php'))->up();
        DB::table('companies')->insert(['id' => 1, 'weekend' => '[0]']);
        DB::table('branches')->insert(['id' => 1, 'name' => 'HQ']);
        DB::table('posts')->insert(['id' => 1, 'post_name' => 'បុគ្គលិក']);
        DB::table('departments')->insert(['id' => 1, 'dept_name' => 'Sales']);
        DB::table('users')->insert(['id' => 20, 'name' => 'Staff', 'username' => 'EMP20', 'company_id' => 1,
            'branch_id' => 1, 'post_id' => 1, 'department_id' => 1, 'joining_date' => '2026-01-01']);
        for ($day = 1; $day <= 31; $day++) DB::table('attendances')->insert([
            'user_id' => 20, 'attendance_date' => sprintf('2026-10-%02d', $day), 'attendance_status' => 1,
            'check_in_at' => '09:00:00', 'check_out_at' => '17:00:00',
        ]);
    }

    private function amount(): float { return app(MonthlyAttendanceBonusService::class)->amount(User::findOrFail(20), '2026-10'); }

    private function payslip(): EmployeePayslip
    {
        return EmployeePayslip::create(['employee_id' => 20, 'salary_cycle' => 'monthly', 'salary_from' => '2026-10-01',
            'salary_to' => '2026-10-31', 'status' => 'generated', 'net_salary' => 300, 'gross_salary' => 300, 'bonus' => 10]);
    }

    public function test_rates_and_management_precedence_for_technician_department(): void
    {
        $this->assertSame(20.0, $this->amount());
        DB::table('posts')->update(['post_name' => 'ប្រធាន']);
        $this->assertSame(30.0, $this->amount());
        DB::table('posts')->update(['post_name' => 'បុគ្គលិក']);
        DB::table('departments')->update(['dept_name' => 'ផ្នែកជាង']);
        $this->assertSame(30.0, $this->amount());
        DB::table('posts')->update(['post_name' => 'ថ្នាក់ដឹកនាំ']);
        $this->assertSame(50.0, $this->amount());
    }

    public function test_incomplete_current_month_and_mid_month_joining_are_not_full_months(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 30));
        $this->assertSame(0.0, $this->amount());
        $this->travelTo(now()->setDate(2026, 11, 1));
        DB::table('users')->update(['joining_date' => '2026-10-02']);
        $this->assertSame(0.0, $this->amount());
    }

    public function test_missing_workday_checkout_or_rejected_attendance_fails_even_with_weekend_work(): void
    {
        DB::table('attendances')->where('attendance_date', '2026-10-02')->update(['check_out_at' => null]);
        $this->assertSame(0.0, $this->amount());
        DB::table('attendances')->where('attendance_date', '2026-10-02')->update(['check_out_at' => '17:00:00', 'attendance_status' => 0]);
        $this->assertSame(0.0, $this->amount());
        DB::table('attendances')->where('attendance_date', '2026-10-02')->delete();
        $this->assertSame(0.0, $this->amount());
    }

    public function test_holidays_weekends_and_approved_days_off_are_allowed(): void
    {
        DB::table('holidays')->insert(['company_id' => 1, 'is_active' => 1, 'event_date' => '2026-10-02']);
        DB::table('leave_types')->insert(['id' => 1, 'name' => 'Day Off', 'slug' => 'day-off']);
        DB::table('leave_requests_master')->insert(['requested_by' => 20, 'leave_type_id' => 1, 'leave_from' => '2026-10-03', 'leave_to' => '2026-10-03', 'status' => 'approved']);
        DB::table('attendances')->whereIn('attendance_date', ['2026-10-02', '2026-10-03', '2026-10-04'])->delete();
        $this->assertSame(20.0, $this->amount());
    }

    public function test_pending_review_and_approved_leave_or_time_leave_each_fail(): void
    {
        DB::table('leave_types')->insert(['id' => 1, 'name' => 'Paid Leave', 'slug' => 'paid-leave']);
        foreach (['pending', 'review', 'approved'] as $status) {
            DB::table('leave_requests_master')->insert(['requested_by' => 20, 'leave_type_id' => 1, 'leave_from' => '2026-10-02', 'leave_to' => '2026-10-02', 'status' => $status]);
            $this->assertSame(0.0, $this->amount());
            DB::table('leave_requests_master')->delete();
            DB::table('time_leaves')->insert(['requested_by' => 20, 'issue_date' => '2026-10-02', 'status' => $status]);
            $this->assertSame(0.0, $this->amount());
            DB::table('time_leaves')->delete();
        }
        DB::table('time_leaves')->insert(['requested_by' => 20, 'issue_date' => '2026-10-02', 'status' => 'rejected']);
        $this->assertSame(20.0, $this->amount());
    }

    public function test_apply_replaces_old_award_once_without_changing_manual_incentive(): void
    {
        $service = app(MonthlyAttendanceBonusService::class); $payslip = $this->payslip();
        $service->apply($payslip);
        $this->assertEquals(320, $payslip->fresh()->net_salary);
        $this->assertEquals(10, $payslip->fresh()->bonus);
        $service->apply($payslip->fresh());
        $this->assertDatabaseCount('employee_payslip_additional_details', 1);
        $this->assertEquals(320, $payslip->fresh()->net_salary);
        DB::table('attendances')->where('attendance_date', '2026-10-02')->delete();
        $service->apply($payslip->fresh());
        $this->assertEquals(300, $payslip->fresh()->net_salary);
        $this->assertDatabaseCount('employee_payslip_additional_details', 0);
        $payslip->update(['status' => 'paid']);
        $service->apply($payslip->fresh());
        $this->assertEquals(300, $payslip->fresh()->net_salary);
    }

    public function test_preview_rolls_back_award_and_save_freezes_it_once(): void
    {
        $payslip = $this->payslip();
        $this->mock(GeneratePayrollService::class, fn ($mock) => $mock->shouldReceive('getEmployeeSalariesToCreatePayslip')->twice()->andReturn([]));
        $service = app(MonthlyPayrollRecordService::class); $branch = Branch::findOrFail(1);
        $preview = $service->preview('2026-10', $branch, [], '$');
        $this->assertEquals(20, $preview['rows'][0]['values']['attendance_bonus']);
        $this->assertEquals(320, $preview['rows'][0]['values']['net_salary']);
        $this->assertEquals(300, $payslip->fresh()->net_salary);
        $this->assertDatabaseCount('employee_payslip_additional_details', 0);
        $saved = $service->generate('2026-10', $branch, [], '$', $preview);
        $this->assertEquals(320, $payslip->fresh()->net_salary);
        $this->assertEquals(20, $saved->rows[0]['values']['attendance_bonus']);
        $this->assertDatabaseCount('employee_payslip_additional_details', 1);
    }
}
