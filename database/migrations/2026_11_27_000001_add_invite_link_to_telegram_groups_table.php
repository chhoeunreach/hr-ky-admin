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
        if (Schema::hasTable('telegram_groups') && !Schema::hasColumn('telegram_groups', 'invite_link')) {
            Schema::table('telegram_groups', function (Blueprint $table) {
                $table->string('invite_link', 255)->nullable()->after('topic_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('telegram_groups') && Schema::hasColumn('telegram_groups', 'invite_link')) {
            Schema::table('telegram_groups', function (Blueprint $table) {
                $table->dropColumn('invite_link');
            });
        }
    }
};
