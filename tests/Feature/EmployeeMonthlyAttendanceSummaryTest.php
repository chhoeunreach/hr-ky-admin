<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\AttendanceMonthlyController;
use App\Models\Branch;
use App\Models\OfficeTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeMonthlyAttendanceSummaryTest extends TestCase
{
    private User $employee;

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
        parent::setUp(); $this->withoutMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class]); DB::purge('sqlite'); app()->setLocale('en');
        $this->travelTo(Carbon::parse('2026-10-30 12:00:00'));
        $this->actingAs((new User())->forceFill(['id' => 1, 'company_id' => 1, 'name' => 'HR']));
        Schema::create('companies', function (Blueprint $table) { $table->id(); $table->string('email'); $table->text('weekend'); });
        DB::table('companies')->insert(['id' => 1, 'email' => 'company@example.test', 'weekend' => '[]']);
        Schema::create('attendance_settings', function (Blueprint $table) { $table->id(); $table->string('slug'); $table->string('value')->nullable(); $table->boolean('status'); });
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); $table->integer('user_id'); $table->date('attendance_date');
            foreach (['check_in_at', 'check_out_at', 'night_checkin', 'night_checkout'] as $column) $table->string($column)->nullable();
            $table->boolean('attendance_status')->nullable();
        });
        Schema::create('leave_types', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('slug'); });
        DB::table('leave_types')->insert(['id' => 1, 'name' => 'Paid Leave', 'slug' => 'paid-leave']);
        Schema::create('leave_requests_master', function (Blueprint $table) {
            $table->id(); $table->integer('requested_by'); $table->integer('leave_type_id');
            $table->date('leave_from'); $table->date('leave_to'); $table->boolean('early_exit')->default(false);
            $table->string('status'); $table->string('reasons')->nullable(); $table->string('admin_remark')->nullable();
        });
        Schema::create('time_leaves', function (Blueprint $table) {
            $table->id(); $table->integer('requested_by'); $table->date('issue_date'); $table->string('status');
            foreach (['start_time', 'end_time', 'reasons', 'admin_remark'] as $column) $table->string($column)->nullable();
        });
        Schema::create('holidays', function (Blueprint $table) {
            $table->id(); $table->integer('company_id'); $table->boolean('is_active'); $table->string('event'); $table->date('event_date');
        });
        $this->employee = (new User())->forceFill(['id' => 20, 'name' => 'Employee', 'username' => 'EMP20', 'company_id' => 1]);
        $this->employee->setRelation('branch', (new Branch())->forceFill(['name' => 'Main']));
        $this->employee->setRelation('officeTime', (new OfficeTime())->forceFill(['opening_time' => '09:00:00', 'closing_time' => '17:00:00']));
    }

    private function summary(): array
    {
        return app(\App\Services\Attendance\EmployeeMonthlyAttendanceSummary::class)->forEmployee($this->employee);
    }

    private function attendance(string $date, string $checkIn = '09:00:00', ?string $checkOut = '17:00:00', int $userId = 20, int $status = 1): void
    {
        DB::table('attendances')->insert(['user_id' => $userId, 'attendance_date' => $date,
            'check_in_at' => $checkIn, 'check_out_at' => $checkOut, 'attendance_status' => $status]);
    }

    public function test_summary_matches_admin_report_with_excuses_rejections_and_warning_threshold(): void
    {
        foreach ([1, 2, 3, 4] as $day) $this->attendance(sprintf('2026-10-%02d', $day), '09:35:00');
        foreach (['pending', 'review', 'approved'] as $i => $status) {
            $date = '2026-10-'.(10 + $i);
            $this->attendance($date, '09:35:00');
            DB::table('time_leaves')->insert(['requested_by' => 20, 'issue_date' => $date, 'status' => $status]);
        }
        $this->attendance('2026-10-15', '09:35:00', '17:00:00', 20, 0);
        $this->attendance('2026-10-16', '09:35:00', '17:00:00', 99);
        $controller = app(AttendanceMonthlyController::class);
        $row = (new \ReflectionMethod($controller, 'buildRows'))->invoke($controller, collect([$this->employee]), Carbon::parse('2026-10-01'))->first();
        $amount = (new \ReflectionMethod($controller, 'lateReductionPayment'))->invoke($controller, $row['late_breakdown']);
        $summary = $this->summary();
        $this->assertSame(4, $summary['late_count']);
        $this->assertSame($row['total_late_records'], $summary['late_count']);
        $this->assertSame($amount, $summary['deduction']['amount']);
        $this->assertSame(31.2, $amount);
        $this->assertSame(3, $summary['excused_late_count']);
        $this->assertSame(1, $summary['approved_late_count']);
        $this->assertSame(1, $summary['approved_time_leave_count']);
        $this->assertSame($row['bonus_eligible_days'], $summary['bonus']['completed_days']);
    }

    public function test_current_month_progress_counts_full_month_target_and_ignores_future_and_other_employees(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 18:00:00'));
        $this->attendance('2026-10-01');
        $this->attendance('2026-10-02');
        $this->attendance('2026-10-05', '09:00:00', null);
        $this->attendance('2026-10-06', '09:35:00');
        $this->attendance('2026-09-30', '09:35:00');
        $this->attendance('2026-10-03', '09:35:00', '17:00:00', 99);
        $summary = $this->summary();
        $this->assertSame('2026-10', $summary['month']);
        $this->assertSame(0, $summary['late_count']);
        $this->assertSame(2, $summary['bonus']['completed_days']);
        $this->assertSame(31, $summary['bonus']['working_days']);
        $this->assertSame(6.5, $summary['bonus']['progress_percent']);
        $this->assertSame(20.0, $summary['bonus']['target_amount']);
        $this->assertTrue($summary['deduction']['estimated']);
    }

    public function test_approved_totals_count_dates_without_pending_future_or_other_employee_requests(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 18:00:00'));
        foreach ([1, 2, 3, 4] as $day) $this->attendance(sprintf('2026-10-%02d', $day), '09:35:00');
        DB::table('leave_requests_master')->insert(['requested_by' => 20, 'leave_type_id' => 1,
            'leave_from' => '2026-10-01', 'leave_to' => '2026-10-01', 'status' => 'approved']);
        foreach ([
            [20, '2026-10-01', 'approved'],
            [20, '2026-10-01', 'approved'],
            [20, '2026-10-02', 'approved'],
            [20, '2026-10-03', 'pending'],
            [20, '2026-10-04', 'review'],
            [20, '2026-10-05', 'approved'], // Approved time leave without a late attendance.
            [20, '2026-10-06', 'approved'],
            [20, '2026-09-30', 'approved'],
            [99, '2026-10-04', 'approved'],
        ] as [$employeeId, $date, $status]) {
            DB::table('time_leaves')->insert(['requested_by' => $employeeId, 'issue_date' => $date, 'status' => $status]);
        }
        $summary = $this->summary();
        $this->assertSame(2, $summary['approved_late_count']);
        $this->assertSame(3, $summary['approved_time_leave_count']);
        $this->assertSame(4, $summary['excused_late_count']);
        $this->assertSame(0, $summary['late_count']);
        $this->assertSame(0.0, $summary['deduction']['amount']);
    }

    public function test_bonus_target_follows_admin_setting_and_excludes_company_weekends_and_holidays(): void
    {
        DB::table('attendance_settings')->insert(['slug' => 'monthly_attendance_bonus_amount', 'value' => '35', 'status' => 1]);
        DB::table('companies')->where('id', 1)->update(['weekend' => '[0]']);
        DB::table('holidays')->insert(['company_id' => 1, 'is_active' => 1, 'event' => 'Holiday', 'event_date' => '2026-10-01']);
        $this->attendance('2026-10-01');
        $this->attendance('2026-10-02');
        $this->attendance('2026-10-04');
        $summary = $this->summary();
        $this->assertSame(35.0, $summary['bonus']['target_amount']);
        $this->assertSame(26, $summary['bonus']['working_days']);
        $this->assertSame(1, $summary['bonus']['completed_days']);
    }

    public function test_full_month_qualifying_days_reaches_one_hundred_without_writing_payroll(): void
    {
        $this->travelTo(Carbon::parse('2026-10-31 18:00:00'));
        for ($day = 1; $day <= 31; $day++) $this->attendance(sprintf('2026-10-%02d', $day));
        $summary = $this->summary();
        $this->assertSame(100.0, $summary['bonus']['progress_percent']);
        $this->assertSame(31, $summary['bonus']['completed_days']);
        $this->assertSame(0.0, $summary['deduction']['amount']);
        $this->assertSame(31, DB::table('attendances')->count());
    }

    public function test_missing_shift_is_unavailable_instead_of_showing_zero_deductions(): void
    {
        $this->employee->setRelation('officeTime', null);
        $this->assertFalse($this->summary()['available']);
    }

    public function test_endpoint_requires_authentication_and_ignores_requested_employee_id(): void
    {
        $this->attendance('2026-10-01', '09:35:00', '17:00:00', 99);
        Schema::create('permission_roles', function (Blueprint $table) { $table->integer('role_id'); $table->integer('permission_id'); });
        Schema::create('permissions', function (Blueprint $table) { $table->id(); $table->string('permission_key'); });
        $this->employee->role_id = 2;
        $this->employee->setRelation('role', (new \App\Models\Role)->forceFill(['id' => 2, 'slug' => 'employee']));
        $this->actingAs($this->employee, 'api');
        $this->getJson('/api/dashboard/attendance-summary?user_id=99&month=2025-01')
            ->assertOk()->assertJsonPath('data.late_count', 0)->assertJsonPath('data.month', '2026-10')
            ->assertJsonPath('data.approved_late_count', 0)->assertJsonPath('data.approved_time_leave_count', 0)
            ->assertJsonMissingPath('data.employee')->assertJsonMissingPath('data.days');
        auth('api')->forgetUser();
        $this->getJson('/api/dashboard/attendance-summary')->assertUnauthorized();
    }
}
