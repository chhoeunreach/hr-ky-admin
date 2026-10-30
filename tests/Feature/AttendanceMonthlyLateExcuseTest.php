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

class AttendanceMonthlyLateExcuseTest extends TestCase
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
        parent::setUp(); DB::purge('sqlite'); app()->setLocale('en');
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

    private function late(string $date, int $approvedAttendance = 1): void
    {
        DB::table('attendances')->insert(['user_id' => 20, 'attendance_date' => $date, 'check_in_at' => '09:35:00', 'check_out_at' => '17:00:00', 'attendance_status' => $approvedAttendance]);
    }

    private function timeLeave(string $date, string $status, int $employeeId = 20): int
    {
        return DB::table('time_leaves')->insertGetId(['requested_by' => $employeeId, 'issue_date' => $date, 'status' => $status, 'start_time' => '09:00:00', 'end_time' => '10:00:00']);
    }

    private function leave(string $from, string $to, string $status): void
    {
        DB::table('leave_requests_master')->insert(['requested_by' => 20, 'leave_type_id' => 1, 'leave_from' => $from, 'leave_to' => $to, 'status' => $status]);
    }

    private function invoke(string $method, ...$arguments)
    {
        $controller = app(AttendanceMonthlyController::class);
        return (new \ReflectionMethod($controller, $method))->invoke($controller, ...$arguments);
    }

    private function row(): array
    {
        return $this->invoke('buildRows', collect([$this->employee]), Carbon::parse('2026-10-01'))->first();
    }

    public function test_pending_review_and_approved_time_leave_exclude_only_their_days_from_late(): void
    {
        foreach (['pending', 'review', 'approved'] as $index => $status) {
            $date = '2026-10-0'.($index + 1); $this->late($date); $this->timeLeave($date, $status);
        }
        $row = $this->row();
        $this->assertSame(0, $row['totals']['late']);
        $this->assertSame(3, $row['totals']['present']);
        $this->assertSame(3, $row['excused_late_days']);
        $this->assertSame(1, $row['approved_late_requests']);
        $this->assertSame(0, $row['late_minutes_total']);
        $this->assertSame(0, array_sum($row['late_breakdown']));
        $this->assertSame(0, array_sum($row['count_reduction']));
        foreach (array_slice($row['days'], 0, 3) as $day) {
            $this->assertSame('present', $day['status']);
            $this->assertStringContainsString('09:35', implode(' ', $day['details']));
        }
    }

    public function test_leave_requests_and_multi_day_approved_leave_exclude_late_for_each_matching_date(): void
    {
        foreach (range(1, 4) as $day) $this->late('2026-10-0'.$day);
        $this->leave('2026-09-30', '2026-10-02', 'approved');
        $this->leave('2026-10-03', '2026-10-03', 'pending');
        $this->leave('2026-10-04', '2026-10-04', 'review');
        $row = $this->row();
        $this->assertSame(0, $row['totals']['late']);
        $this->assertSame(4, $row['excused_late_days']);
        $this->assertSame(0, $row['effective_late_count']);
    }

    public function test_requests_on_other_days_do_not_cancel_unexcused_lates_or_reduction_exports(): void
    {
        foreach (range(1, 4) as $day) $this->late('2026-10-0'.$day);
        $this->timeLeave('2026-10-08', 'approved');
        $this->timeLeave('2026-10-08', 'approved');
        $row = $this->row();
        $this->assertSame(4, $row['totals']['late']);
        $this->assertSame(4, $row['effective_late_count']);
        $this->assertSame(0, $row['approved_late_requests']);
        $this->assertSame(140, $row['late_minutes_total']);
        $time = $this->invoke('reductionExportRows', collect([$row]), Carbon::parse('2026-10-01'));
        $count = $this->invoke('countReductionExportRows', collect([$row]), Carbon::parse('2026-10-01'));
        $this->assertCount(1, $time); $this->assertCount(1, $count);
        $this->assertEquals(1.2, $time[0][9]); $this->assertEquals(36, $count[0][9]);
    }

    public function test_multiple_requests_on_one_day_are_excluded_once_without_a_second_monthly_subtraction(): void
    {
        foreach (range(1, 5) as $day) $this->late('2026-10-0'.$day);
        $this->timeLeave('2026-10-01', 'approved'); $this->timeLeave('2026-10-01', 'approved');
        $this->leave('2026-10-01', '2026-10-01', 'approved');
        $row = $this->row();
        $this->assertSame(1, $row['excused_late_days']);
        $this->assertSame(1, $row['approved_late_requests']);
        $this->assertSame(4, $row['totals']['late']);
        $this->assertSame(4, $row['effective_late_count']);
        $this->assertSame(4, array_sum($row['count_reduction']));
    }

    public function test_rejected_cancelled_and_other_employees_requests_do_not_excuse_lateness(): void
    {
        $this->late('2026-10-01'); $this->late('2026-10-02');
        $this->timeLeave('2026-10-01', 'rejected'); $this->timeLeave('2026-10-02', 'cancelled');
        $this->leave('2026-10-01', '2026-10-02', 'rejected');
        $this->timeLeave('2026-10-01', 'approved', 21);
        $row = $this->row();
        $this->assertSame(2, $row['totals']['late']);
        $this->assertSame(0, $row['excused_late_days']);
        $this->assertSame(2, $row['effective_late_count']);
    }

    public function test_rejected_request_makes_the_day_count_as_late_again(): void
    {
        $this->late('2026-10-01'); $id = $this->timeLeave('2026-10-01', 'pending');
        $this->assertSame(0, $this->row()['totals']['late']);
        DB::table('time_leaves')->where('id', $id)->update(['status' => 'rejected']);
        $this->assertSame(1, $this->row()['totals']['late']);
    }

    public function test_a_request_does_not_create_present_attendance_or_override_rejected_attendance(): void
    {
        $this->leave('2026-10-01', '2026-10-01', 'pending');
        $this->leave('2026-10-02', '2026-10-02', 'approved');
        $this->late('2026-10-03', 0); $this->timeLeave('2026-10-03', 'approved');
        $row = $this->row();
        $this->assertSame('absent', $row['days'][0]['status']);
        $this->assertSame('leave', $row['days'][1]['status']);
        $this->assertSame('absent', $row['days'][2]['status']);
        $this->assertSame(0, $row['totals']['present']);
    }
}
