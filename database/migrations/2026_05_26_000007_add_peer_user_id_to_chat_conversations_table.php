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

        Schema::table('chat_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_conversations', 'peer_user_id')) {
                $table->unsignedBigInteger('peer_user_id')
                    ->nullable()
                    ->after('admin_id');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            try {
                DB::statement('ALTER TABLE `chat_conversations` ADD UNIQUE `chat_conversations_user_peer_unique` (`user_id`, `peer_user_id`)');
            } catch (Throwable $throwable) {
                // Index may already exist.
            }
        } else {
            Schema::table('chat_conversations', function (Blueprint $table) {
                try {
                    $table->unique(['user_id', 'peer_user_id'], 'chat_conversations_user_peer_unique');
                } catch (Throwable $throwable) {
                    // Index may already exist.
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('chat_conversations')) {
            return;
        }

        Schema::table('chat_conversations', function (Blueprint $table) {
            try {
                $table->dropUnique('chat_conversations_user_peer_unique');
            } catch (Throwable $throwable) {
                // Index may already be missing.
            }

            if (Schema::hasColumn('chat_conversations', 'peer_user_id')) {
                $table->dropColumn('peer_user_id');
            }
        });
    }
};
