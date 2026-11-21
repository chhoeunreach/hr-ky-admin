<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Department;
use App\Models\PayrollQueueSession;
use App\Models\PayrollQueueTicket;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PayrollQueueSessionTest extends TestCase
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
        parent::setUp();

        DB::purge('sqlite');

        Schema::create('admins', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Admin');
            $t->string('email')->default('admin@example.com');
            $t->string('username')->default('admin');
            $t->string('password')->default('password');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Default Company');
            $t->timestamps();
        });

        Schema::create('theme_settings', function (Blueprint $t) {
            $t->id();
            $t->timestamps();
        });

        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->default(1);
            $t->string('name');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->default(1);
            $t->unsignedBigInteger('branch_id')->nullable();
            $t->string('dept_name');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->default(1);
            $t->unsignedBigInteger('branch_id')->nullable();
            $t->unsignedBigInteger('department_id')->nullable();
            $t->string('name')->default('User');
            $t->string('english_name')->nullable();
            $t->string('username')->default('user1');
            $t->string('employee_code')->nullable();
            $t->string('fcm_token')->nullable();
            $t->string('status')->default('verified');
            $t->boolean('is_active')->default(true);
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('general_settings', function (Blueprint $t) {
            $t->id();
            $t->string('key');
            $t->string('value');
            $t->timestamps();
        });

        Schema::create('payroll_queue_sessions', function (Blueprint $t) {
            $t->id();
            $t->string('session_code', 50)->unique();
            $t->unsignedBigInteger('company_id')->nullable();
            $t->unsignedBigInteger('branch_id')->nullable();
            $t->unsignedBigInteger('department_id')->nullable();
            $t->unsignedBigInteger('target_employee_id')->nullable();
            $t->string('month', 7);
            $t->string('title');
            $t->string('qr_token', 100)->unique();
            $t->string('status')->default('open');
            $t->unsignedInteger('max_active_serving')->default(5);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
        });

        Schema::create('payroll_queue_tickets', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('session_id');
            $t->unsignedBigInteger('employee_id');
            $t->unsignedBigInteger('payslip_id')->nullable();
            $t->string('ticket_number', 20);
            $t->unsignedInteger('counter_number')->nullable();
            $t->string('status')->default('waiting');
            $t->timestamp('joined_at')->nullable();
            $t->timestamp('called_at')->nullable();
            $t->timestamp('served_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->unsignedBigInteger('payment_method_id')->nullable();
            $t->decimal('net_amount_paid', 12, 2)->nullable();
            $t->unsignedBigInteger('disbursed_by')->nullable();
            $t->text('remarks')->nullable();
            $t->timestamps();
        });

        DB::table('branches')->insert([
            'id' => 1,
            'name' => 'Main Branch',
            'is_active' => true,
        ]);

        DB::table('departments')->insert([
            'id' => 1,
            'branch_id' => 1,
            'dept_name' => 'IT Department',
            'is_active' => true,
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'company_id' => 1,
            'branch_id' => 1,
            'name' => 'Admin User',
            'username' => 'admin_test',
            'employee_code' => 'EMP001',
            'status' => 'verified',
            'is_active' => true,
        ]);

        $this->actingAs((new Admin)->forceFill(['id' => 1]), 'admin');
        Gate::before(fn () => true);
        $this->withoutExceptionHandling();
    }

    public function test_can_render_sessions_list_page(): void
    {
        $response = $this->get(route('admin.payroll-queue.sessions.index'));

        $response->assertOk();
        $response->assertSee('Payday Queue', false);
        $response->assertSee('payrollQueueTable', false);
        $response->assertSee('data-bs-toggle="modal" data-bs-target="#createSessionModal"', false);
        $response->assertSee('data-directory-live="false"', false);
    }

    public function test_can_create_new_payroll_queue_session(): void
    {
        $response = $this->post(route('admin.payroll-queue.sessions.store'), [
            'month' => '2026-11',
            'title' => 'Test Payday Session November',
            'branch_id' => 1,
            'max_active_serving' => 3,
            'status' => 'open',
        ]);

        $session = PayrollQueueSession::where('title', 'Test Payday Session November')->first();
        $this->assertNotNull($session);
        $this->assertSame('2026-11', $session->month);
        $this->assertSame(3, $session->max_active_serving);

        $response->assertRedirect(route('admin.payroll-queue.index', ['sessionId' => $session->id]));
    }

    public function test_list_filter_keeps_other_branches_available_in_the_create_form(): void
    {
        DB::table('branches')->insert(['id' => 2, 'name' => 'Second Branch']);
        DB::table('departments')->insert(['id' => 2, 'branch_id' => 2, 'dept_name' => 'Second Department']);
        $list = route('admin.payroll-queue.sessions.index', ['branch_id' => 1, 'search' => 'Existing']);

        $this->get($list)->assertOk()->assertSee('data-branch="2"', false);

        $this->withExceptionHandling()->from($list)->post(route('admin.payroll-queue.sessions.store'), [
            'month' => '2026-11',
            'title' => 'Retain this title',
            'branch_id' => 2,
            'department_id' => 2,
            'employee_code' => 'UNKNOWN',
            'max_active_serving' => 7,
            'status' => 'paused',
        ])->assertRedirect($list)->assertSessionHasErrorsIn('createPaydayQueue', 'employee_code');

        $this->get($list)->assertOk()
            ->assertSee('No active employee was found with that code.')
            ->assertSee('value="Retain this title"', false)
            ->assertSee('value="UNKNOWN"', false)
            ->assertSee("bootstrap.Modal.getOrCreateInstance(document.getElementById('createSessionModal')).show()", false);
        $this->assertDatabaseCount('payroll_queue_sessions', 0);

        $this->post(route('admin.payroll-queue.sessions.store'), [
            'month' => '2026-11',
            'title' => 'Retain this title',
            'branch_id' => 2,
            'department_id' => 2,
            'max_active_serving' => 7,
            'status' => 'paused',
        ])->assertRedirect();
        $this->get(route('admin.payroll-queue.sessions.index', ['branch_id' => 2, 'status' => 'paused']))
            ->assertOk()->assertSee('Retain this title');
        $this->get(route('admin.payroll-queue.sessions.index', ['branch_id' => 1]))
            ->assertOk()->assertDontSee('Retain this title');
    }

    public function test_can_render_dashboard_for_specific_session(): void
    {
        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-TEST-SPECIFIC',
            'company_id' => 1,
            'month' => '2026-11',
            'title' => 'Specific Session For Dashboard Test',
            'qr_token' => Str::random(32),
            'status' => 'open',
            'max_active_serving' => 4,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $response = $this->get(route('admin.payroll-queue.index', ['sessionId' => $session->id]));

        $response->assertOk();
        $response->assertSee('Specific Session For Dashboard Test');
        $response->assertSee('PQS-TEST-SPECIFIC');
    }

    public function test_can_view_session_details_via_ajax(): void
    {
        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-TEST-AJAX',
            'company_id' => 1,
            'month' => '2026-11',
            'title' => 'AJAX Detail Test',
            'qr_token' => Str::random(32),
            'status' => 'open',
            'max_active_serving' => 5,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $response = $this->getJson(route('admin.payroll-queue.sessions.show', ['sessionId' => $session->id]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'session_code' => 'PQS-TEST-AJAX',
                'title' => 'AJAX Detail Test',
            ],
        ]);
        $this->assertNotEmpty($response->json('qr_svg'));
        $this->assertNotEmpty($response->json('join_url'));
    }

    public function test_can_delete_payroll_queue_session_and_tickets(): void
    {
        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-TEST-DELETE',
            'company_id' => 1,
            'month' => '2026-11',
            'title' => 'Session To Be Deleted',
            'qr_token' => Str::random(32),
            'status' => 'open',
            'max_active_serving' => 3,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        PayrollQueueTicket::create([
            'session_id' => $session->id,
            'employee_id' => 1,
            'ticket_number' => 'A-001',
            'status' => 'waiting',
            'joined_at' => now(),
        ]);

        $this->assertSame(1, PayrollQueueTicket::where('session_id', $session->id)->count());

        $response = $this->delete(route('admin.payroll-queue.sessions.destroy', ['sessionId' => $session->id]));

        $response->assertRedirect(route('admin.payroll-queue.sessions.index'));
        $this->assertNull(PayrollQueueSession::find($session->id));
        $this->assertSame(0, PayrollQueueTicket::where('session_id', $session->id)->count());
    }

    public function test_can_delete_payroll_queue_session_via_get_route(): void
    {
        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-TEST-GET-DEL',
            'company_id' => 1,
            'month' => '2026-11',
            'title' => 'Session To Be Deleted via GET',
            'qr_token' => Str::random(32),
            'status' => 'open',
            'max_active_serving' => 3,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $response = $this->get(route('admin.payroll-queue.sessions.delete', ['sessionId' => $session->id]));

        $response->assertRedirect(route('admin.payroll-queue.sessions.index'));
        $this->assertNull(PayrollQueueSession::find($session->id));
    }

    public function test_can_render_tv_display_page_with_single_line_counters_and_four_panels(): void
    {
        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-TV-TEST',
            'company_id' => 1,
            'branch_id' => 1,
            'month' => '2026-11',
            'title' => 'Payday TV Display Screen',
            'qr_token' => Str::random(32),
            'status' => 'open',
            'max_active_serving' => 4,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        PayrollQueueTicket::create([
            'session_id' => $session->id,
            'employee_id' => 1,
            'ticket_number' => 'A-001',
            'status' => 'waiting',
            'joined_at' => now(),
        ]);

        $response = $this->get(route('admin.payroll-queue.display', ['sessionId' => $session->id]));

        $response->assertOk();
        $response->assertSee('tvCountersBar');
        $response->assertSee('SCAN QR');
        $response->assertSee('PENDING');
        $response->assertSee('WAITING');
        $response->assertSee('IN ROOM');
        $response->assertSee('DONE');
        $response->assertSee('A-001');
    }

    public function test_live_status_api_returns_structured_panels_data(): void
    {
        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-LIVE-TEST',
            'company_id' => 1,
            'branch_id' => 1,
            'month' => '2026-11',
            'title' => 'Live Status Test',
            'qr_token' => Str::random(32),
            'status' => 'open',
            'max_active_serving' => 3,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        PayrollQueueTicket::create([
            'session_id' => $session->id,
            'employee_id' => 1,
            'ticket_number' => 'A-001',
            'counter_number' => 1,
            'status' => 'calling',
            'joined_at' => now(),
            'called_at' => now(),
        ]);

        $response = $this->getJson(route('admin.payroll-queue.live-status', ['sessionId' => $session->id]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'calling_ticket' => [
                'ticket_number' => 'A-001',
                'counter_number' => 1,
            ],
        ]);
        $this->assertNotEmpty($response->json('counters'));
        $this->assertArrayHasKey('in_room_tickets', $response->json());
        $this->assertArrayHasKey('waiting_tickets', $response->json());
        $this->assertArrayHasKey('done_tickets', $response->json());
        $this->assertArrayHasKey('pending_employees', $response->json());
    }

    public function test_dashboard_calls_the_selected_ticket_and_protects_busy_counters(): void
    {
        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-COUNTERS',
            'month' => '2026-11',
            'title' => 'Counter test',
            'qr_token' => Str::random(32),
            'max_active_serving' => 8,
        ]);
        $first = $session->tickets()->create(['employee_id' => 1, 'ticket_number' => 'A-001', 'joined_at' => now()->subMinute()]);
        $selected = $session->tickets()->create(['employee_id' => 2, 'ticket_number' => 'A-002', 'joined_at' => now()]);

        $this->get(route('admin.payroll-queue.dashboard', ['sessionId' => $session->id]))
            ->assertOk()->assertSee('data-counter="8"', false);

        $callUrl = route('admin.payroll-queue.call-next', $session->id);
        $this->postJson($callUrl, ['counter_number' => 8, 'ticket_id' => $selected->id])
            ->assertOk()->assertJsonPath('ticket.id', $selected->id);
        $this->postJson($callUrl, ['counter_number' => 8])->assertUnprocessable();
        $this->postJson($callUrl, ['counter_number' => 9])->assertUnprocessable();
        $this->assertSame('waiting', $first->fresh()->status);
        $this->assertSame('calling', $selected->fresh()->status);

        $this->postJson(route('admin.payroll-queue.skip', $selected->id))->assertOk();
        $this->postJson(route('admin.payroll-queue.recall', $selected->id))->assertUnprocessable();
        $this->postJson($callUrl, ['counter_number' => 8, 'ticket_id' => $selected->id])->assertUnprocessable();
        $this->postJson($callUrl, ['counter_number' => 8])
            ->assertOk()->assertJsonPath('ticket.id', $first->id);

        $session->update(['status' => 'paused']);
        $this->postJson($callUrl, ['counter_number' => 1])->assertUnprocessable();
    }

    public function test_qr_join_review_and_payment_use_the_session_month(): void
    {
        Schema::create('employee_payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('salary_cycle')->default('monthly');
            $table->date('salary_from');
            $table->string('status')->default('generated');
            $table->decimal('gross_salary')->default(150);
            $table->decimal('bonus')->default(0);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->decimal('net_salary')->default(125);
            $table->date('paid_on')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->timestamps();
        });
        foreach (['employee_payslip_details', 'employee_payslip_additional_details'] as $tableName) {
            Schema::create($tableName, fn (Blueprint $table) => $table->unsignedBigInteger('employee_payslip_id'));
        }
        Schema::create('sell_out_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->string('service_type');
            $table->decimal('commission');
            $table->timestamps();
        });
        Schema::create('monthly_payroll_records', function (Blueprint $table) {
            $table->string('month');
            $table->unsignedBigInteger('branch_id');
        });
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status');
        });
        DB::table('payment_methods')->insert(['id' => 1, 'name' => 'Cash', 'status' => true]);
        DB::table('employee_payslips')->insert([
            ['id' => 1, 'employee_id' => 1, 'salary_from' => '2026-10-01'],
            ['id' => 2, 'employee_id' => 1, 'salary_from' => '2026-11-01'],
        ]);
        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-PAYMENT',
            'branch_id' => 1,
            'month' => '2026-11',
            'title' => 'November payroll',
            'qr_token' => Str::random(32),
        ]);

        $join = route('payroll-queue.submit-join', $session->qr_token);
        $this->actingAs(User::findOrFail(1))->postJson($join)
            ->assertOk()->assertJsonPath('ticket.payslip_id', 2);
        $ticket = $session->tickets()->sole();
        $this->postJson($join)->assertOk()->assertJsonPath('ticket.id', $ticket->id);

        $this->actingAs((new Admin)->forceFill(['id' => 1]), 'admin');
        $call = route('admin.payroll-queue.call-next', $session->id);
        $this->postJson($call, ['counter_number' => 1])->assertOk();
        $this->postJson(route('admin.payroll-queue.skip', $ticket->id))->assertOk();
        $this->actingAs(User::findOrFail(1))->postJson($join)
            ->assertOk()->assertJsonPath('ticket.id', $ticket->id)->assertJsonPath('ticket.status', 'waiting');

        $this->actingAs((new Admin)->forceFill(['id' => 1]), 'admin');
        $this->postJson($call, ['counter_number' => 1])->assertOk();
        $ticket->update(['payslip_id' => 1]);
        $this->getJson(route('admin.payroll-queue.breakdown', $ticket->id))
            ->assertOk()->assertJsonPath('data.payslip.id', 2);
        $this->postJson(route('admin.payroll-queue.recall', $ticket->id))
            ->assertOk()->assertJsonPath('ticket.status', 'serving');

        $quickUpdate = route('admin.payroll-queue.quick-update', $ticket->id);
        $bonusChange = ['bonus' => 5, 'expected_bonus' => 0, 'expected_net' => 125];
        $this->postJson($quickUpdate, $bonusChange)->assertOk()->assertJsonPath('data.net_salary', 130);
        $this->postJson($quickUpdate, $bonusChange)->assertStatus(400);

        $payment = route('admin.payroll-queue.disburse', $ticket->id);
        $data = ['payment_method_id' => 1, 'paid_on' => '2026-11-20'];
        $this->postJson($payment, $data + ['expected_net' => 125])
            ->assertStatus(400)->assertJsonPath('success', false);
        $next = $session->tickets()->create(['employee_id' => 2, 'ticket_number' => 'A-002', 'joined_at' => now()]);
        $this->postJson($payment, $data + ['expected_net' => 130])
            ->assertOk()
            ->assertJsonPath('ticket.status', 'paid')
            ->assertJsonPath('ticket.payslip_id', 2)
            ->assertJsonPath('next_ticket.id', $next->id)
            ->assertJsonPath('next_ticket.counter_number', 1);
        $this->postJson($payment, $data)->assertStatus(400)->assertJsonPath('success', false);
        $this->assertDatabaseHas('employee_payslips', ['id' => 1, 'status' => 'generated']);
        $this->assertDatabaseHas('employee_payslips', ['id' => 2, 'status' => 'paid']);
        $this->get(route('admin.payroll-queue.print-slip', $ticket->id))->assertOk()->assertSee('A-001');
    }

    public function test_can_update_session_active_serving_counters_flexibly(): void
    {
        $this->actingAs((new Admin)->forceFill(['id' => 1]), 'admin');
        Gate::define('view_monthly_payroll', fn () => true);
        Gate::define('view_payroll_list', fn () => true);

        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-FLEX-COUNTERS',
            'branch_id' => 1,
            'month' => '2026-11',
            'title' => 'Flex Counters Session',
            'qr_token' => Str::random(32),
            'max_active_serving' => 5,
        ]);

        $url = route('admin.payroll-queue.sessions.update-counters', ['sessionId' => $session->id]);

        // 1. Switch flexibly to ONLY ONE counter
        $res1 = $this->postJson($url, ['max_active_serving' => 1]);
        $res1->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('max_active_serving', 1);
        $this->assertCount(1, $res1->json('counters'));
        $this->assertEquals(1, $session->fresh()->max_active_serving);

        // 2. Switch flexibly to 3 counters
        $res2 = $this->postJson($url, ['max_active_serving' => 3]);
        $res2->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('max_active_serving', 3);
        $this->assertCount(3, $res2->json('counters'));
        $this->assertEquals(3, $session->fresh()->max_active_serving);

        // 3. Put an active ticket on counter 3
        $ticket = PayrollQueueTicket::create([
            'session_id' => $session->id,
            'employee_id' => 1,
            'ticket_number' => 'A-099',
            'counter_number' => 3,
            'status' => 'serving',
            'joined_at' => now(),
        ]);

        // Attempt to reduce to 2 counters while counter 3 is busy should be prevented (422)
        $res3 = $this->postJson($url, ['max_active_serving' => 2]);
        $res3->assertStatus(422)
            ->assertJsonPath('success', false);
        $this->assertEquals(3, $session->fresh()->max_active_serving);
    }
}
