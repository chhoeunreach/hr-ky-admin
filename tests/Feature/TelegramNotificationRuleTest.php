<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\TelegramGroup;
use App\Models\TelegramNotificationRule;
use App\Services\TelegramService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TelegramNotificationRuleTest extends TestCase
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
                $table->unsignedBigInteger('topic_id')->nullable();
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
                $table->timestamp('last_tested_at')->nullable();
                $table->string('last_test_status', 20)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('telegram_groups', 'topic_id')) {
            Schema::table('telegram_groups', function (Blueprint $table) {
                $table->unsignedBigInteger('topic_id')->nullable();
                $table->timestamp('last_tested_at')->nullable();
                $table->string('last_test_status', 20)->nullable();
            });
        }

        if (!Schema::hasTable('telegram_notification_rules')) {
            Schema::create('telegram_notification_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191);
                $table->string('event_key', 100);
                $table->json('event_keys')->nullable();
                $table->string('branch_scope', 20)->default('all');
                $table->json('branch_ids')->nullable();
                $table->json('department_ids')->nullable();
                $table->json('channel_ids');
                $table->boolean('send_location')->default(true);
                $table->boolean('send_selfie')->default(true);
                $table->boolean('is_silent')->default(false);
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamp('last_triggered_at')->nullable();
                $table->timestamp('last_test_at')->nullable();
                $table->string('last_test_status', 20)->nullable();
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
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
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

    public function test_telegram_notification_rule_crud_and_views(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $channelA = TelegramGroup::create([
            'name' => 'HQ Management Group',
            'chat_id' => '-100111222333',
            'topic_id' => null,
            'is_active' => true,
        ]);

        $channelB = TelegramGroup::create([
            'name' => 'Toul Kork Forum Supergroup',
            'chat_id' => '-100444555666',
            'topic_id' => 12,
            'is_active' => true,
        ]);

        // 1. Store rule
        $response = $this->post(route('admin.telegram-rules.store'), [
            'name' => 'Leave Requests to Management & Toul Kork',
            'event_key' => TelegramGroup::EVENT_LEAVE_REQUEST,
            'branch_scope' => 'all',
            'channel_ids' => [$channelA->id, $channelB->id],
            'send_location' => 1,
            'send_selfie' => 1,
            'is_silent' => 0,
            'is_active' => 1,
            'description' => 'Dispatches leave requests across channels',
        ]);

        $response->assertRedirect(route('admin.telegram-rules.index'));
        $response->assertSessionHas('success');

        $rule = TelegramNotificationRule::where('name', 'Leave Requests to Management & Toul Kork')->first();
        $this->assertNotNull($rule);
        $this->assertSame(TelegramGroup::EVENT_LEAVE_REQUEST, $rule->event_key);
        $this->assertCount(2, $rule->channel_ids);

        // 2. Index listing
        $indexResponse = $this->get(route('admin.telegram-rules.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Leave Requests to Management & Toul Kork');
        $indexResponse->assertSee('HQ Management Group');
        $indexResponse->assertSee('#12');

        // 3. Edit view
        $this->withoutExceptionHandling();
        $editResponse = $this->get(route('admin.telegram-rules.edit', $rule->id));
        $editResponse->assertOk();
        $editResponse->assertSee('HQ Management Group');

        // 4. Update rule
        $updateResponse = $this->put(route('admin.telegram-rules.update', $rule->id), [
            'name' => 'Leave Requests to Management (Updated)',
            'event_key' => TelegramGroup::EVENT_LEAVE_REQUEST,
            'branch_scope' => 'all',
            'channel_ids' => [$channelA->id],
            'send_location' => 1,
            'send_selfie' => 0,
            'is_silent' => 1,
            'is_active' => 1,
        ]);
        $updateResponse->assertRedirect(route('admin.telegram-rules.index'));

        $rule->refresh();
        $this->assertSame('Leave Requests to Management (Updated)', $rule->name);
        $this->assertCount(1, $rule->channel_ids);
        $this->assertTrue($rule->is_silent);

        // 5. Toggle status (HTTP Redirect & AJAX JSON)
        $toggleResponse = $this->get(route('admin.telegram-rules.toggle-status', $rule->id));
        $toggleResponse->assertRedirect();
        $rule->refresh();
        $this->assertFalse($rule->is_active);

        $toggleAjaxResponse = $this->getJson(route('admin.telegram-rules.toggle-status', $rule->id));
        $toggleAjaxResponse->assertOk()->assertJson(['status' => true, 'is_active' => true]);
        $rule->refresh();
        $this->assertTrue($rule->is_active);

        // 6. Delete rule (AJAX JSON)
        $deleteAjaxResponse = $this->getJson(route('admin.telegram-rules.delete', $rule->id));
        $deleteAjaxResponse->assertOk()->assertJson(['status' => true]);
        $this->assertNull(TelegramNotificationRule::find($rule->id));
    }

    public function test_telegram_service_routes_recipients_via_notification_rules(): void
    {
        $channel = TelegramGroup::create([
            'name' => 'Sales Alert Channel',
            'chat_id' => '-100888999',
            'topic_id' => 99,
            'is_active' => true,
        ]);

        $rule = TelegramNotificationRule::create([
            'name' => 'Attendance Summary Rule',
            'event_key' => TelegramGroup::EVENT_ATTENDANCE_SUMMARY,
            'branch_scope' => 'all',
            'channel_ids' => [$channel->id],
            'is_active' => true,
        ]);

        $telegramService = app(TelegramService::class);
        $refMethod = new \ReflectionMethod($telegramService, 'resolveConfiguredRecipients');
        $refMethod->setAccessible(true);

        $recipients = $refMethod->invoke(
            $telegramService,
            TelegramGroup::EVENT_ATTENDANCE_SUMMARY,
            'All Branches',
            'All Departments'
        );

        $this->assertNotEmpty($recipients);
        $found = collect($recipients)->first(fn ($r) => $r['chat_id'] === '-100888999' && (int) $r['topic_id'] === 99);
        $this->assertNotNull($found, 'Recipient configured in TelegramNotificationRule was resolved by TelegramService.');
    }

    public function test_telegram_rule_test_action_sends_sample_messages(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $channel = TelegramGroup::create([
            'name' => 'Test Channel Target',
            'chat_id' => '-100777666',
            'topic_id' => 55,
            'is_active' => true,
        ]);

        $rule = TelegramNotificationRule::create([
            'name' => 'Test Notification Rule',
            'event_key' => TelegramGroup::EVENT_RESIGNATION_REQUEST,
            'branch_scope' => 'all',
            'channel_ids' => [$channel->id],
            'is_active' => true,
        ]);

        $mockTelegram = $this->createMock(TelegramService::class);
        $mockTelegram->expects($this->once())
            ->method('sendMessage')
            ->with('-100777666', $this->stringContains('Test Notification Rule'), 'HTML', 55)
            ->willReturn(true);
        $this->app->instance(TelegramService::class, $mockTelegram);

        $response = $this->get(route('admin.telegram-rules.test', $rule->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rule->refresh();
        $this->assertSame('success', $rule->last_test_status);
        $this->assertNotNull($rule->last_test_at);
    }

    public function test_sync_default_rules_from_existing_telegram_groups(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        // Create legacy channels with chat_ids
        $mgmtChannel = TelegramGroup::create([
            'name' => 'Management HQ',
            'chat_id' => '-1002799577548',
            'action_key' => TelegramGroup::ACTION_ATTENDANCE,
            'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN],
            'is_active' => true,
        ]);

        $leaveChannel = TelegramGroup::create([
            'name' => 'Leave Operations',
            'chat_id' => '-1002727901053',
            'action_key' => TelegramGroup::ACTION_LEAVE,
            'event_keys' => [TelegramGroup::EVENT_LEAVE_REQUEST],
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.telegram-rules.sync-default'));
        $response->assertRedirect(route('admin.telegram-rules.index'));
        $response->assertSessionHas('success');

        // Assert rules were generated automatically
        $leaveRule = TelegramNotificationRule::where('event_key', TelegramGroup::EVENT_LEAVE_REQUEST)->first();
        $this->assertNotNull($leaveRule, 'Leave rule was automatically generated from existing channel');
        $this->assertContains($leaveChannel->id, $leaveRule->channel_ids);

        $summaryRule = TelegramNotificationRule::where('event_key', TelegramGroup::EVENT_ATTENDANCE_SUMMARY)->first();
        $this->assertNotNull($summaryRule, 'Attendance summary digest rule was automatically created');
        $this->assertContains($mgmtChannel->id, $summaryRule->channel_ids);
    }

    public function test_deactivated_notification_rule_strictly_does_not_send_or_fallback(): void
    {
        // 1. Set fallback chat id in config
        config(['services.telegram.chat_id' => '-1009999999999']);

        // 2. Create a legacy group that would previously catch all events
        $legacyGroup = TelegramGroup::create([
            'name' => 'Legacy Catch All Group',
            'chat_id' => '-1001112223334',
            'action_key' => TelegramGroup::ACTION_GENERAL,
            'send_for_all' => true,
            'is_active' => true,
        ]);

        // 3. Create a channel for the rule
        $ruleChannel = TelegramGroup::create([
            'name' => 'Leave Channel',
            'chat_id' => '-100555666777',
            'is_active' => true,
        ]);

        // 4. Create an INACTIVE rule for leave request
        $rule = TelegramNotificationRule::create([
            'name' => 'Disabled Leave Rule',
            'event_key' => TelegramGroup::EVENT_LEAVE_REQUEST,
            'branch_scope' => 'all',
            'channel_ids' => [$ruleChannel->id],
            'is_active' => false, // USER TURNED THIS OFF!
        ]);

        $telegramService = app(TelegramService::class);
        $refMethod = new \ReflectionMethod($telegramService, 'resolveConfiguredRecipients');
        $refMethod->setAccessible(true);

        $recipients = $refMethod->invoke(
            $telegramService,
            TelegramGroup::EVENT_LEAVE_REQUEST,
            'All Branches',
            'All Departments'
        );

        // Strict deactivation: MUST BE EMPTY! No fallback to legacy group, no fallback to .env!
        $this->assertEmpty($recipients, 'When a notification rule for an event is deactivated, recipients must be strictly empty.');
    }

    public function test_active_notification_rule_only_sends_to_assigned_channels_and_ignores_other_groups(): void
    {
        // Legacy group that has send_for_all = true
        $legacyGroup = TelegramGroup::create([
            'name' => 'HQ General Catch-All Group',
            'chat_id' => '-100111111111',
            'action_key' => TelegramGroup::ACTION_GENERAL,
            'send_for_all' => true,
            'is_active' => true,
        ]);

        // Dedicated channel
        $targetChannel = TelegramGroup::create([
            'name' => 'Dedicated Advance Salary Channel',
            'chat_id' => '-100888888888',
            'is_active' => true,
        ]);

        // Active rule configured ONLY for Advance Salary to $targetChannel
        TelegramNotificationRule::create([
            'name' => 'Advance Salary Dedicated Rule',
            'event_key' => TelegramGroup::EVENT_ADVANCE_SALARY_REQUEST,
            'branch_scope' => 'all',
            'channel_ids' => [$targetChannel->id],
            'is_active' => true,
        ]);

        $telegramService = app(TelegramService::class);
        $refMethod = new \ReflectionMethod($telegramService, 'resolveConfiguredRecipients');
        $refMethod->setAccessible(true);

        $recipients = $refMethod->invoke(
            $telegramService,
            TelegramGroup::EVENT_ADVANCE_SALARY_REQUEST,
            'All Branches',
            'All Departments'
        );

        $chatIds = array_column($recipients, 'chat_id');
        $this->assertContains('-100888888888', $chatIds, 'Target channel receives the event.');
        $this->assertNotContains('-100111111111', $chatIds, 'Legacy catch-all group is strictly excluded when rules are active.');
    }

    public function test_branch_route_matching_cleans_auxiliary_text(): void
    {
        $branchId = DB::table('branches')->insertGetId([
            'name' => 'Phnom Penh HQ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rule = new TelegramNotificationRule([
            'branch_scope' => 'specific',
            'branch_ids' => [$branchId],
        ]);

        $this->assertTrue(
            $rule->matchesBranch('Phnom Penh HQ ➔ Toul Kork (ការងារចេញក្រៅ)'),
            'Branch route formatted name correctly extracts and matches base branch name.'
        );
        $this->assertTrue(
            $rule->matchesBranch('Phnom Penh HQ'),
            'Exact branch name matches.'
        );
        $this->assertFalse(
            $rule->matchesBranch('Siem Reap Branch'),
            'Different branch does not match.'
        );
    }

    public function test_telegram_rules_paginate_all_returns_all_records_and_resets_page(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        for ($i = 1; $i <= 15; $i++) {
            TelegramNotificationRule::create([
                'name' => "Rule Pagination {$i}",
                'event_key' => TelegramGroup::EVENT_LEAVE_REQUEST,
                'branch_scope' => 'all',
                'channel_ids' => [1],
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('admin.telegram-rules.index', ['per_page' => 'all', 'page' => 3]));
        $response->assertOk();
        $paginator = $response->viewData('rules');
        $this->assertGreaterThanOrEqual(15, $paginator->total());
        $this->assertSame(1, $paginator->currentPage());
        $this->assertCount($paginator->total(), $paginator->items());
    }

    public function test_telegram_rule_edit_modal_flow(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $channel = TelegramGroup::create([
            'name' => 'Channel for Rule Modal',
            'chat_id' => '-100987654321',
            'is_active' => true,
        ]);

        $rule = TelegramNotificationRule::create([
            'name' => 'Rule for Modal Edit',
            'event_key' => TelegramGroup::EVENT_LEAVE_REQUEST,
            'branch_scope' => 'all',
            'channel_ids' => [$channel->id],
            'is_active' => true,
        ]);

        // 1. Index contains trigger and modal markup
        $indexResponse = $this->get(route('admin.telegram-rules.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('data-open-telegram-rule-modal', false);
        $indexResponse->assertSee('id="telegramRuleActionModal"', false);

        // 2. Edit with modal query parameter
        $editModalResponse = $this->get(route('admin.telegram-rules.edit', [
            'telegram_rule' => $rule->id,
            'telegram_rule_modal' => '1',
        ]));
        $editModalResponse->assertOk();
        $editModalResponse->assertSee('name="telegram_rule_modal"', false);

        // 3. Update with modal returns modal-saved view
        $updateModalResponse = $this->put(route('admin.telegram-rules.update', $rule->id), [
            'name' => 'Rule Modal Updated',
            'event_key' => TelegramGroup::EVENT_LEAVE_REQUEST,
            'branch_scope' => 'all',
            'channel_ids' => [$channel->id],
            'send_location' => 1,
            'send_selfie' => 1,
            'is_silent' => 0,
            'is_active' => 1,
            'telegram_rule_modal' => '1',
        ]);
        $updateModalResponse->assertOk();
        $updateModalResponse->assertViewIs('admin.telegramRule.common.modal-saved');
        $updateModalResponse->assertSee('telegram-rule-action-saved');

        $rule->refresh();
        $this->assertSame('Rule Modal Updated', $rule->name);
    }

    public function test_telegram_rule_multi_select_events_crud_and_matching(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $channel = TelegramGroup::create([
            'name' => 'Multi Event Channel',
            'chat_id' => '-100555666777',
            'is_active' => true,
        ]);

        // 1. Create with multiple events
        $response = $this->post(route('admin.telegram-rules.store'), [
            'name' => 'Combined Attendance and Leave Rule',
            'event_keys' => [TelegramGroup::EVENT_LEAVE_REQUEST, TelegramGroup::EVENT_ATTENDANCE_CHECKIN],
            'branch_scope' => 'all',
            'channel_ids' => [$channel->id],
            'send_location' => 1,
            'send_selfie' => 1,
            'is_silent' => 0,
            'is_active' => 1,
        ]);
        $response->assertRedirect(route('admin.telegram-rules.index'));

        $rule = TelegramNotificationRule::where('name', 'Combined Attendance and Leave Rule')->first();
        $this->assertNotNull($rule);
        $this->assertCount(2, $rule->event_keys_list);
        $this->assertTrue($rule->matchesEvent(TelegramGroup::EVENT_LEAVE_REQUEST));
        $this->assertTrue($rule->matchesEvent(TelegramGroup::EVENT_ATTENDANCE_CHECKIN));
        $this->assertFalse($rule->matchesEvent(TelegramGroup::EVENT_ADVANCE_SALARY_REQUEST));

        // 2. Index renders multi-event badges
        $indexResponse = $this->get(route('admin.telegram-rules.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Combined Attendance and Leave Rule');

        // 3. Edit view shows both events selected
        $editResponse = $this->get(route('admin.telegram-rules.edit', $rule->id));
        $editResponse->assertOk();

        // 4. Update with three events
        $updateResponse = $this->put(route('admin.telegram-rules.update', $rule->id), [
            'name' => 'Combined Three Events Rule',
            'event_keys' => [
                TelegramGroup::EVENT_LEAVE_REQUEST,
                TelegramGroup::EVENT_ATTENDANCE_CHECKIN,
                TelegramGroup::EVENT_ADVANCE_SALARY_REQUEST,
            ],
            'branch_scope' => 'all',
            'channel_ids' => [$channel->id],
            'send_location' => 1,
            'send_selfie' => 1,
            'is_silent' => 0,
            'is_active' => 1,
        ]);
        $updateResponse->assertRedirect(route('admin.telegram-rules.index'));

        $rule->refresh();
        $this->assertSame('Combined Three Events Rule', $rule->name);
        $this->assertCount(3, $rule->event_keys_list);
        $this->assertTrue($rule->matchesEvent(TelegramGroup::EVENT_ADVANCE_SALARY_REQUEST));
    }
}
