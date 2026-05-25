<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_conversations')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            try {
                DB::statement('ALTER TABLE `chat_conversations` DROP INDEX `chat_conversations_user_id_unique`');
            } catch (Throwable $throwable) {
                // Index may already be removed.
            }
        } elseif ($driver === 'sqlite') {
            // Testing tables are managed separately; no-op here.
        }

        Schema::table('chat_conversations', function (Blueprint $table) {
            try {
                $table->unique(['user_id', 'admin_id'], 'chat_conversations_user_admin_unique');
            } catch (Throwable $throwable) {
                // Index may already exist.
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('chat_conversations')) {
            return;
        }

        Schema::table('chat_conversations', function (Blueprint $table) {
            try {
                $table->dropUnique('chat_conversations_user_admin_unique');
            } catch (Throwable $throwable) {
                // Index may already be missing.
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            try {
                DB::statement('ALTER TABLE `chat_conversations` ADD UNIQUE `chat_conversations_user_id_unique` (`user_id`)');
            } catch (Throwable $throwable) {
                // Legacy index may already exist.
            }
        }
    }
};
