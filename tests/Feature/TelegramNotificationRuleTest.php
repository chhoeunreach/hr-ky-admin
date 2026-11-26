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

        // 5. Toggle status
        $toggleResponse = $this->get(route('admin.telegram-rules.toggle-status', $rule->id));
        $toggleResponse->assertRedirect();
        $rule->refresh();
        $this->assertFalse($rule->is_active);

        // 6. Delete rule
        $deleteResponse = $this->get(route('admin.telegram-rules.delete', $rule->id));
        $deleteResponse->assertRedirect();
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
}
