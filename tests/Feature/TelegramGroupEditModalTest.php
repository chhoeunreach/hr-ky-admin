<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Department;
use App\Models\TelegramGroup;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TelegramGroupEditModalTest extends TestCase
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

    public function test_index_exposes_telegram_group_edit_modal_trigger_and_shell(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $group = TelegramGroup::create([
            'name' => 'Branch Alert Group',
            'chat_id' => '-100987654321',
            'topic_id' => 88,
            'is_active' => true,
        ]);

        $this->get(route('admin.telegram-groups.index'))
            ->assertOk()
            ->assertSee('data-open-telegram-group-modal', false)
            ->assertSee('id="telegramGroupActionModal"', false)
            ->assertSee('data-telegram-group-action-frame', false)
            ->assertSee('telegram-group-action-modal.js', false)
            ->assertSee('window.refreshTelegramGroupList', false)
            ->assertSee('telegram-group:refresh', false)
            ->assertSee('data-directory-refresh', false)
            ->assertSee('id="telegramGroupPaginationArea"', false)
            ->assertSee('https://t.me/c/987654321', false)
            ->assertSee('https://t.me/c/987654321/88', false)
            ->assertSee(route('admin.telegram-groups.edit', $group->id), false);
    }

    public function test_telegram_url_helper_methods_and_links(): void
    {
        $group = new TelegramGroup([
            'chat_id' => '-1001234567890',
            'topic_id' => 42,
        ]);

        $this->assertSame('https://t.me/c/1234567890/42', $group->getTelegramUrl());
        $this->assertSame('https://t.me/c/1234567890/42', $group->getTelegramTopicUrl());

        // Without topic (includes /1 to prevent @c not found routing error in Telegram web/clients)
        $groupNoTopic = new TelegramGroup(['chat_id' => '-1001234567890']);
        $this->assertSame('https://t.me/c/1234567890/1', $groupNoTopic->getTelegramUrl());
        $this->assertSame('tg://openmessage?chat_id=-1001234567890', $groupNoTopic->getTelegramAppUrl());
        $this->assertNull($groupNoTopic->getTelegramTopicUrl());

        // With topic app url
        $this->assertSame('tg://openmessage?chat_id=-1001234567890&message_id=42', $group->getTelegramAppUrl());

        // Username handle
        $groupHandle = new TelegramGroup(['chat_id' => '@hr_notifications', 'topic_id' => 99]);
        $this->assertSame('https://t.me/hr_notifications/99', $groupHandle->getTelegramUrl());
        $this->assertSame('https://t.me/hr_notifications/99', $groupHandle->getTelegramTopicUrl());
        $this->assertSame('tg://resolve?domain=hr_notifications', $groupHandle->getTelegramAppUrl());

        // Direct url
        $groupDirect = new TelegramGroup(['chat_id' => 'https://t.me/+AbCdEfGhIjKlMnOp']);
        $this->assertSame('https://t.me/+AbCdEfGhIjKlMnOp', $groupDirect->getTelegramUrl());
    }

    public function test_edit_with_telegram_group_modal_renders_embedded_form_without_chrome(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $group = TelegramGroup::create([
            'name' => 'Branch Alert Group',
            'chat_id' => '-100987654321',
            'topic_id' => 88,
            'is_active' => true,
        ]);

        // Embedded in modal
        $this->get(route('admin.telegram-groups.edit', $group->id) . '?telegram_group_modal=1')
            ->assertOk()
            ->assertSee('name="telegram_group_modal" value="1"', false)
            ->assertDontSee('page-breadcrumb', false)
            ->assertDontSee('<nav class="sidebar">', false);

        // Standard standalone page
        $this->get(route('admin.telegram-groups.edit', $group->id))
            ->assertOk()
            ->assertSee('page-breadcrumb', false)
            ->assertDontSee('name="telegram_group_modal"', false);
    }

    public function test_update_from_modal_returns_modal_saved_view_and_updates_telegram_group(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $group = TelegramGroup::create([
            'name' => 'Original Group Name',
            'chat_id' => '-100987654321',
            'topic_id' => 88,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Renamed Group via Modal',
            'chat_ids' => ['-100987654321'],
            'topic_id' => 88,
            'event_keys' => [TelegramGroup::EVENT_LEAVE_REQUEST],
            'send_for_all' => 1,
            'send_location' => 1,
            'send_selfie' => 1,
            'is_active' => 1,
            'telegram_group_modal' => '1',
        ];

        $this->put(route('admin.telegram-groups.update', $group->id), $payload)
            ->assertOk()
            ->assertSee('telegram-group-action-saved', false)
            ->assertSee(route('admin.telegram-groups.index'), false);

        $group->refresh();
        $this->assertSame('Renamed Group via Modal', $group->name);
    }

    public function test_update_without_modal_redirects_to_index(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $group = TelegramGroup::create([
            'name' => 'Plain Edit Group',
            'chat_id' => '-100987654321',
            'topic_id' => 88,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Plain Update Name',
            'chat_ids' => ['-100987654321'],
            'topic_id' => 88,
            'event_keys' => [TelegramGroup::EVENT_LEAVE_REQUEST],
            'send_for_all' => 1,
            'send_location' => 1,
            'send_selfie' => 1,
            'is_active' => 1,
        ];

        $this->put(route('admin.telegram-groups.update', $group->id), $payload)
            ->assertRedirect(route('admin.telegram-groups.index'))
            ->assertSessionHas('success');

        $group->refresh();
        $this->assertSame('Plain Update Name', $group->name);
    }

    public function test_toggle_status_via_ajax_returns_json_response(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $group = TelegramGroup::create([
            'name' => 'Toggle Status Group',
            'chat_id' => '-1001122334455',
            'is_active' => true,
        ]);

        $response = $this->getJson(route('admin.telegram-groups.toggle-status', $group->id));
        $response->assertOk()
            ->assertJson([
                'status' => true,
            ]);

        $group->refresh();
        $this->assertFalse((bool) $group->is_active);
    }

    public function test_delete_via_ajax_returns_json_response(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $group = TelegramGroup::create([
            'name' => 'Group to Delete via AJAX',
            'chat_id' => '-10099887766',
            'is_active' => true,
        ]);

        $response = $this->getJson(route('admin.telegram-groups.delete', $group->id));
        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Telegram group deleted successfully.',
            ]);

        $this->assertNull(TelegramGroup::find($group->id));
    }

    public function test_telegram_groups_paginate_all_returns_all_records(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        for ($i = 1; $i <= 15; $i++) {
            TelegramGroup::create([
                'name' => "Pagination Group {$i}",
                'chat_id' => "-100{$i}00000",
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('admin.telegram-groups.index', ['per_page' => 'all']));
        $response->assertOk();

        $paginator = $response->viewData('telegramGroups');
        $this->assertGreaterThanOrEqual(15, $paginator->total());
        $this->assertCount($paginator->total(), $paginator->items(), 'When per_page=all is passed, all records are returned.');

        // Test with page=2: should force page 1 and still return all records
        $responseWithPage = $this->get(route('admin.telegram-groups.index', ['per_page' => 'all', 'page' => 2]));
        $responseWithPage->assertOk();
        $paginatorWithPage = $responseWithPage->viewData('telegramGroups');
        $this->assertSame(1, $paginatorWithPage->currentPage(), 'Page is forced to 1 when per_page=all');
        $this->assertCount($paginatorWithPage->total(), $paginatorWithPage->items(), 'All items returned even if page=2 is in query.');
    }

    public function test_deleting_telegram_group_cleans_up_channel_id_from_rules(): void
    {
        Gate::before(fn () => true);

        $admin = Admin::firstOrCreate(
            ['id' => 1],
            ['name' => 'Super Admin', 'email' => 'admin@test.com', 'username' => 'superadmin', 'is_active' => true]
        );
        $this->actingAs($admin, 'admin');

        $groupA = TelegramGroup::create([
            'name' => 'Channel A to be deleted',
            'chat_id' => '-1001111111',
            'is_active' => true,
        ]);

        $groupB = TelegramGroup::create([
            'name' => 'Channel B to keep',
            'chat_id' => '-1002222222',
            'is_active' => true,
        ]);

        if (!Schema::hasTable('telegram_notification_rules')) {
            Schema::create('telegram_notification_rules', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('event_key', 100);
                $table->json('event_keys')->nullable();
                $table->string('branch_scope', 20)->default('all');
                $table->json('branch_ids')->nullable();
                $table->json('channel_ids')->nullable();
                $table->boolean('send_location')->default(true);
                $table->boolean('send_selfie')->default(true);
                $table->boolean('is_silent')->default(false);
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        $rule = \App\Models\TelegramNotificationRule::create([
            'name' => 'Test Rule for Cleanup',
            'event_key' => TelegramGroup::EVENT_LEAVE_REQUEST,
            'branch_scope' => 'all',
            'channel_ids' => [$groupA->id, $groupB->id],
            'is_active' => true,
        ]);

        // Delete groupA
        $this->getJson(route('admin.telegram-groups.delete', $groupA->id))->assertOk();

        $rule->refresh();
        $this->assertNotContains($groupA->id, $rule->channel_ids, 'Deleted group ID is automatically cleaned up from rule.');
        $this->assertContains($groupB->id, $rule->channel_ids, 'Remaining group ID is retained.');
    }
}
