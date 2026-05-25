<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->nullable()
                ->after('user_id')
                ->constrained('admins')
                ->nullOnDelete();

            $table->dropUnique('chat_conversations_user_id_unique');
            $table->unique(['user_id', 'admin_id'], 'chat_conversations_user_admin_unique');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropUnique('chat_conversations_user_admin_unique');
            $table->unique('user_id');
            $table->dropConstrainedForeignId('admin_id');
        });
    }
};
