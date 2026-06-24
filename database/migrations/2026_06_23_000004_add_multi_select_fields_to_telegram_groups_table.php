<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            $table->json('chat_ids')->nullable()->after('chat_id');
            $table->json('action_keys')->nullable()->after('action_key');
            $table->json('branch_ids')->nullable()->after('branch_id');
            $table->json('department_ids')->nullable()->after('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            $table->dropColumn(['chat_ids', 'action_keys', 'branch_ids', 'department_ids']);
        });
    }
};
