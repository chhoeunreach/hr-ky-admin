<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('topic_id')->nullable()->after('chat_ids');
            $table->timestamp('last_tested_at')->nullable()->after('description');
            $table->string('last_test_status', 30)->nullable()->after('last_tested_at');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            $table->dropColumn(['topic_id', 'last_tested_at', 'last_test_status']);
        });
    }
};
