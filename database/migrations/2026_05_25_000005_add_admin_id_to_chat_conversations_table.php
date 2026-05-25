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
        Schema::table('chat_conversations', function (Blueprint $table) {

            if (!Schema::hasColumn('chat_conversations', 'admin_id')) {
                $table->unsignedBigInteger('admin_id')
                    ->nullable()
                    ->after('user_id');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {

            if (Schema::hasColumn('chat_conversations', 'admin_id')) {
                $table->dropColumn('admin_id');
            }

        });
    }
};
