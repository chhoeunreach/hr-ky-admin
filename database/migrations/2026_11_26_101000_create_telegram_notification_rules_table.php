<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('telegram_notification_rules')) {
            Schema::create('telegram_notification_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191);
                $table->string('event_key', 100);
                $table->json('event_keys')->nullable();
                $table->string('branch_scope', 20)->default('all'); // 'all' or 'specific'
                $table->json('branch_ids')->nullable();
                $table->json('department_ids')->nullable();
                $table->json('channel_ids'); // array of telegram_group IDs
                $table->boolean('send_location')->default(true);
                $table->boolean('send_selfie')->default(true);
                $table->boolean('is_silent')->default(false);
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamp('last_triggered_at')->nullable();
                $table->timestamp('last_test_at')->nullable();
                $table->string('last_test_status', 20)->nullable();
                $table->timestamps();

                $table->index('event_key');
                $table->index('is_active');
            });

            try {
                \App\Services\Telegram\TelegramRuleSyncService::syncDefaultRules();
            } catch (\Throwable $e) {
                // Ignore if supporting tables are not yet migrated
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_notification_rules');
    }
};
