<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramEventNotification;
use App\Models\Admin;
use App\Models\TelegramGroup;
use App\Models\User;
use App\Services\Telegram\TelegramNotificationHub;
use App\Services\TelegramService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TelegramEventNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app_security.enabled' => false]);

        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('username')->nullable();
                $table->string('password')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('telegram_groups')) {
            Schema::create('telegram_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('chat_id')->nullable();
                $table->json('chat_ids')->nullable();
                $table->string('action_key')->nullable();
                $table->json('action_keys')->nullable();
                $table->json('event_keys')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->json('branch_ids')->nullable();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->json('department_ids')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('department_name')->nullable();
                $table->boolean('send_for_all')->default(false);
                $table->boolean('send_location')->default(true);
                $table->boolean('send_selfie')->default(true);
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('logo')->nullable();
                $table->timestamps();
            });
            DB::table('companies')->insert([
                'id' => 1,
                'name' => 'KY HR',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('dept_name')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('theme_settings')) {
            Schema::create('theme_settings', function (Blueprint $table) {
                $table->id();
                $table->string('theme_mode')->default('light');
                $table->timestamps();
            });
            DB::table('theme_settings')->insert([
                'theme_mode' => 'light',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_telegram_group_model_categorized_events_and_options(): void
    {
        $categories = TelegramGroup::categorizedEventOptions();

        $this->assertArrayHasKey('attendance', $categories);
        $this->assertArrayHasKey('leave', $categories);
        $this->assertArrayHasKey('payroll', $categories);
        $this->assertArrayHasKey('employee', $categories);
        $this->assertArrayHasKey('discipline', $categories);
        $this->assertArrayHasKey('sell_out', $categories);
        $this->assertArrayHasKey('branch_route', $categories);
        $this->assertArrayHasKey('growth', $categories);
        $this->assertArrayHasKey('system', $categories);

        $events = TelegramGroup::eventOptions();

        $this->assertArrayHasKey(TelegramGroup::EVENT_ATTENDANCE_CHECKIN, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_LEAVE_REQUEST, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_LEAVE_APPROVED, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_RESIGNATION_REQUEST, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_RESIGNATION_APPROVED, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_WARNING_CREATED, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_COMPLAINT_CREATED, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_BRANCH_ROUTE_SUBMITTED, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_BRANCH_ROUTE_COMPLETED, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_PAYROLL_QUEUE_CALLED, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_PAYROLL_QUEUE_SERVED, $events);
        $this->assertArrayHasKey(TelegramGroup::EVENT_SYSTEM_ALERT, $events);

        $this->assertSame(TelegramGroup::ACTION_RESIGNATION, TelegramGroup::parentActionForEvent(TelegramGroup::EVENT_RESIGNATION_REQUEST));
        $this->assertSame(TelegramGroup::ACTION_PAYROLL_QUEUE, TelegramGroup::parentActionForEvent(TelegramGroup::EVENT_PAYROLL_QUEUE_CALLED));
        $this->assertSame(TelegramGroup::ACTION_WARNING, TelegramGroup::parentActionForEvent(TelegramGroup::EVENT_WARNING_CREATED));
        $this->assertSame(TelegramGroup::ACTION_COMPLAINT, TelegramGroup::parentActionForEvent(TelegramGroup::EVENT_COMPLAINT_CREATED));
        $this->assertSame(TelegramGroup::ACTION_BRANCH_ROUTE, TelegramGroup::parentActionForEvent(TelegramGroup::EVENT_BRANCH_ROUTE_SUBMITTED));
    }

    public function test_telegram_service_group_matching_for_new_events(): void
    {
        $service = app(TelegramService::class);
        $refMethod = new \ReflectionMethod($service, 'groupMatchesEvent');
        $refMethod->setAccessible(true);

        // Group with exact event key
        $groupA = new TelegramGroup([
            'event_keys' => [TelegramGroup::EVENT_RESIGNATION_REQUEST],
            'action_keys' => [TelegramGroup::ACTION_RESIGNATION],
        ]);
        $this->assertTrue($refMethod->invoke($service, $groupA, TelegramGroup::EVENT_RESIGNATION_REQUEST));
        $this->assertFalse($refMethod->invoke($service, $groupA, TelegramGroup::EVENT_ATTENDANCE_CHECKIN));

        // Group with parent action (e.g. 'resignation') but without exact event_keys
        $groupB = new TelegramGroup([
            'event_keys' => [],
            'action_keys' => [TelegramGroup::ACTION_RESIGNATION],
        ]);
        $this->assertTrue($refMethod->invoke($service, $groupB, TelegramGroup::EVENT_RESIGNATION_REQUEST));
        $this->assertTrue($refMethod->invoke($service, $groupB, TelegramGroup::EVENT_RESIGNATION_APPROVED));
        $this->assertFalse($refMethod->invoke($service, $groupB, TelegramGroup::EVENT_ATTENDANCE_CHECKIN));

        // Group with general action
        $groupGeneral = new TelegramGroup([
            'action_keys' => [TelegramGroup::ACTION_GENERAL],
        ]);
        $this->assertTrue($refMethod->invoke($service, $groupGeneral, TelegramGroup::EVENT_PAYROLL_QUEUE_CALLED));
        $this->assertTrue($refMethod->invoke($service, $groupGeneral, TelegramGroup::EVENT_WARNING_CREATED));
    }

    public function test_telegram_notification_hub_dispatches_queued_job(): void
    {
        Queue::fake([SendTelegramEventNotification::class]);

        // Configure bot token
        config(['services.telegram.bot_token' => 'test-bot-token-123456']);

        TelegramNotificationHub::dispatch(
            TelegramGroup::EVENT_RESIGNATION_REQUEST,
            '<b>Test Resignation Message</b>',
            'Tuol Kouk',
            'IT Department'
        );

        Queue::assertPushed(SendTelegramEventNotification::class, function (SendTelegramEventNotification $job) {
            return $job->eventKey === TelegramGroup::EVENT_RESIGNATION_REQUEST
                && $job->branchName === 'Tuol Kouk'
                && $job->departmentName === 'IT Department'
                && str_contains($job->messageText, 'Test Resignation Message');
        });
    }

    public function test_telegram_group_web_crud_and_categorized_events_matrix(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $payload = [
            'name' => 'HR All Events Channel',
            'chat_ids' => ['-100192837465'],
            'event_keys' => [
                TelegramGroup::EVENT_LEAVE_REQUEST,
                TelegramGroup::EVENT_RESIGNATION_REQUEST,
                TelegramGroup::EVENT_PAYROLL_QUEUE_CALLED,
            ],
            'send_for_all' => 1,
            'send_location' => 1,
            'send_selfie' => 0,
            'is_active' => 1,
            'description' => 'Test Channel for HR Lifecycle Events',
        ];

        $response = $this->post(route('admin.telegram-groups.store'), $payload);
        $response->assertRedirect(route('admin.telegram-groups.index'));
        $response->assertSessionHas('success');

        $created = TelegramGroup::where('name', 'HR All Events Channel')->first();
        $this->assertNotNull($created);
        $this->assertContains(TelegramGroup::EVENT_LEAVE_REQUEST, $created->event_keys);
        $this->assertContains(TelegramGroup::EVENT_RESIGNATION_REQUEST, $created->event_keys);
        $this->assertContains(TelegramGroup::EVENT_PAYROLL_QUEUE_CALLED, $created->event_keys);
        // Assert derived action keys were automatically set
        $this->assertNotEmpty($created->action_keys);

        // Test index listing renders successfully
        $this->withoutExceptionHandling();
        $indexResponse = $this->get(route('admin.telegram-groups.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('HR All Events Channel');

        // Test edit page renders the categorized events matrix form
        $editResponse = $this->get(route('admin.telegram-groups.edit', $created->id));
        $editResponse->assertOk();
        $editResponse->assertSee('Notification Events Matrix');
        $editResponse->assertSee('btn-select-all-events');
        $editResponse->assertSee('btn-deselect-all-events');
        $editResponse->assertSee(TelegramGroup::EVENT_RESIGNATION_REQUEST);
    }

    public function test_telegram_notification_hub_formatters_and_events(): void
    {
        Queue::fake([SendTelegramEventNotification::class]);
        config(['services.telegram.bot_token' => 'test-token-active']);

        $user = (new User)->forceFill([
            'id' => 10,
            'name' => 'John Doe',
        ]);
        $user->setRelation('branch', (new \App\Models\Branch)->forceFill(['id' => 1, 'name' => 'Main Branch']));
        $user->setRelation('department', (new \App\Models\Department)->forceFill(['id' => 1, 'dept_name' => 'Engineering']));

        // 1. Leave Request & Status
        $leave = (new \App\Models\LeaveRequestMaster)->forceFill([
            'id' => 1,
            'leave_from' => '2026-12-01',
            'leave_to' => '2026-12-03',
            'no_of_days' => 3,
            'reasons' => 'Vacation with family',
        ]);
        $leave->setRelation('leaveRequestedBy', $user);
        $leave->setRelation('leaveType', (new \App\Models\LeaveType)->forceFill(['name' => 'Annual Leave']));
        TelegramNotificationHub::notifyLeaveRequest($leave);
        TelegramNotificationHub::notifyLeaveStatus($leave, 'approved', 'Manager Bob', 'Enjoy your trip');

        // 2. Time Leave Request & Status
        $timeLeave = (new \App\Models\TimeLeave)->forceFill([
            'id' => 2,
            'issue_date' => '2026-12-01',
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'reasons' => 'Doctor appointment',
        ]);
        $timeLeave->setRelation('leaveRequestedBy', $user);
        TelegramNotificationHub::notifyTimeLeaveRequest($timeLeave);
        TelegramNotificationHub::notifyTimeLeaveStatus($timeLeave, 'rejected', 'Manager Bob', 'Too busy today');

        // 3. Advance Salary Request & Status
        $advSalary = (new \App\Models\AdvanceSalary)->forceFill([
            'id' => 3,
            'requested_amount' => 150.00,
            'released_amount' => 150.00,
            'advance_requested_date' => '2026-12-01',
            'description' => 'Personal emergency',
        ]);
        $advSalary->setRelation('requestedBy', $user);
        TelegramNotificationHub::notifyAdvanceSalaryRequest($advSalary);
        TelegramNotificationHub::notifyAdvanceSalaryStatus($advSalary, 'approved', 'Finance Admin');

        // 4. Resignation Request & Status
        $resignation = (new \App\Models\Resignation)->forceFill([
            'id' => 4,
            'resignation_date' => '2026-12-01',
            'last_working_day' => '2026-12-31',
            'reason' => 'Pursuing further studies',
        ]);
        $resignation->setRelation('employee', $user);
        TelegramNotificationHub::notifyResignationRequest($resignation);
        TelegramNotificationHub::notifyResignationStatus($resignation, 'approved', 'HR Director');

        // 5. Warning
        $warning = (new \App\Models\Warning)->forceFill([
            'id' => 5,
            'subject' => 'Repeated late check-in',
            'warning_date' => '2026-12-01',
        ]);
        $warning->setRelation('branch', (new \App\Models\Branch)->forceFill(['name' => 'Main Branch']));
        $warning->setRelation('createdBy', (new \App\Models\User)->forceFill(['name' => 'HR Manager']));
        TelegramNotificationHub::notifyWarningCreated($warning);

        // 6. Complaint
        $complaint = (new \App\Models\Complaint)->forceFill([
            'id' => 6,
            'subject' => 'AC unit broken in office',
            'complaint_date' => '2026-12-01',
        ]);
        $complaint->setRelation('complainFrom', $user);
        $complaint->setRelation('branch', (new \App\Models\Branch)->forceFill(['name' => 'Main Branch']));
        TelegramNotificationHub::notifyComplaintCreated($complaint);

        // 7. Payroll Queue Ticket
        $ticket = (new \App\Models\PayrollQueueTicket)->forceFill([
            'id' => 7,
            'ticket_number' => 'Q007',
            'counter_number' => 2,
            'net_amount_paid' => 850.00,
        ]);
        $ticket->setRelation('employee', $user);
        $ticket->setRelation('session', (new \App\Models\PayrollQueueSession)->forceFill(['branch_id' => 1]));
        TelegramNotificationHub::notifyPayrollQueueCalled($ticket);
        TelegramNotificationHub::notifyPayrollQueueServed($ticket);

        // 8. Branch Route
        TelegramNotificationHub::notifyBranchRoute($user, [
            'origin_branch' => 'Main Branch',
            'target_branches' => ['Toul Kork Branch'],
            'purpose' => 'Deliver new stock and replace router',
            'departure_time' => '09:00',
            'arrival_time' => '10:15',
            'status' => 'completed',
            'total_minutes' => 75,
            'formatted_duration' => '1 ម៉ោង 15 នាទី',
            'checkpoints_count' => 2,
        ]);

        // 9. System Alert
        TelegramNotificationHub::notifySystemAlert('Disk Space Low', 'Disk /var/log is at 95% usage', ['server' => 'app-prod-01']);

        // Assert jobs were dispatched (13 previous + 2 for branch route: origin + target)!
        Queue::assertPushed(SendTelegramEventNotification::class, 15);
    }

    public function test_send_test_message_endpoint(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Admin Tester',
            'email' => 'admin_tester@test.com',
            'username' => 'admin_tester',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($admin, 'admin');

        $mockTelegram = $this->createMock(TelegramService::class);
        $mockTelegram->expects($this->once())
            ->method('sendMessage')
            ->with('-100999888', $this->stringContains('HR Admin'))
            ->willReturn(true);
        $this->app->instance(TelegramService::class, $mockTelegram);

        $res = $this->postJson(route('admin.telegram-groups.send-test'), [
            'name' => 'Test Group',
            'chat_ids' => ['-100999888'],
        ]);

        $res->assertOk()->assertJsonPath('status', true);
    }
}
