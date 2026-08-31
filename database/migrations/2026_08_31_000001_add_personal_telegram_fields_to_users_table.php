<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('fcm_token');
            $table->string('telegram_username')->nullable()->after('telegram_chat_id');
            $table->timestamp('telegram_linked_at')->nullable()->after('telegram_username');

            $table->index('telegram_chat_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['telegram_chat_id']);
            $table->dropColumn(['telegram_chat_id', 'telegram_username', 'telegram_linked_at']);
        });
    }
};
