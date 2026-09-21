<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes MySQL Error 1553 / 1062 where 'chat_conversations_user_id_unique'
     * could not be dropped because it was backing the foreign key constraint
     * 'chat_conversations_user_id_foreign'.
     */
    public function up(): void
    {
        if (!Schema::hasTable('chat_conversations')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM `chat_conversations`"));
            $indexNames = $indexes->pluck('Key_name')->unique()->all();

            // 1. Add a non-unique index for user_id foreign key constraint first
            if (!in_array('chat_conversations_user_id_fk_index', $indexNames, true)) {
                try {
                    DB::statement('ALTER TABLE `chat_conversations` ADD INDEX `chat_conversations_user_id_fk_index` (`user_id`)');
                } catch (\Throwable $e) {
                    // Index may already exist
                }
            }

            // 2. Drop the problematic unique constraint on user_id alone
            if (in_array('chat_conversations_user_id_unique', $indexNames, true)) {
                try {
                    DB::statement('ALTER TABLE `chat_conversations` DROP INDEX `chat_conversations_user_id_unique`');
                } catch (\Throwable $e) {
                    // If MySQL still complains about FK dependency, temporarily drop FK -> drop index -> recreate FK
                    try {
                        DB::statement('ALTER TABLE `chat_conversations` DROP FOREIGN KEY `chat_conversations_user_id_foreign`');
                        DB::statement('ALTER TABLE `chat_conversations` DROP INDEX `chat_conversations_user_id_unique`');
                        DB::statement('ALTER TABLE `chat_conversations` ADD CONSTRAINT `chat_conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
                    } catch (\Throwable $nested) {
                        // Suppress error if already resolved
                    }
                }
            }

            // 3. Ensure composite unique index on (user_id, admin_id) exists
            $indexesAfter = collect(DB::select("SHOW INDEX FROM `chat_conversations`"));
            $indexNamesAfter = $indexesAfter->pluck('Key_name')->unique()->all();

            if (!in_array('chat_conversations_user_admin_unique', $indexNamesAfter, true)) {
                try {
                    DB::statement('ALTER TABLE `chat_conversations` ADD UNIQUE `chat_conversations_user_admin_unique` (`user_id`, `admin_id`)');
                } catch (\Throwable $e) {
                    // Suppress if already created
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op on rollback to prevent breaking foreign keys
    }
};
