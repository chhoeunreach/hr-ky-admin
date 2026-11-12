<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flash_views', function (Blueprint $table) {
            if (!Schema::hasColumn('flash_views', 'branch_ids')) {
                $table->json('branch_ids')->nullable()->after('role_ids');
            }
            if (!Schema::hasColumn('flash_views', 'department_ids')) {
                $table->json('department_ids')->nullable()->after('branch_ids');
            }
            if (!Schema::hasColumn('flash_views', 'last_alert_sent_at')) {
                $table->timestamp('last_alert_sent_at')->nullable()->after('revision');
            }
            if (!Schema::hasColumn('flash_views', 'last_alert_recipient_count')) {
                $table->unsignedInteger('last_alert_recipient_count')->default(0)->after('last_alert_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('flash_views', function (Blueprint $table) {
            $columns = [];
            foreach (['branch_ids', 'department_ids', 'last_alert_sent_at', 'last_alert_recipient_count'] as $column) {
                if (Schema::hasColumn('flash_views', $column)) {
                    $columns[] = $column;
                }
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
