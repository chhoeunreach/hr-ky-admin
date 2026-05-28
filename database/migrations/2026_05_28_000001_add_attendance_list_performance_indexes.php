<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['user_id', 'attendance_date'], 'att_user_date_idx');
            $table->index(['attendance_date', 'user_id'], 'att_date_user_idx');
        });

        Schema::table('leave_requests_master', function (Blueprint $table) {
            $table->index(['requested_by', 'status', 'leave_from', 'leave_to'], 'lrm_user_status_dates_idx');
            $table->index(['leave_from', 'leave_to', 'status'], 'lrm_dates_status_idx');
        });

        Schema::table('time_leaves', function (Blueprint $table) {
            $table->index(['requested_by', 'issue_date', 'status'], 'tl_user_date_status_idx');
            $table->index(['issue_date', 'status'], 'tl_date_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['is_active', 'status', 'branch_id', 'department_id'], 'users_attendance_filter_idx');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_attendance_filter_idx');
        });

        Schema::table('time_leaves', function (Blueprint $table) {
            $table->dropIndex('tl_user_date_status_idx');
            $table->dropIndex('tl_date_status_idx');
        });

        Schema::table('leave_requests_master', function (Blueprint $table) {
            $table->dropIndex('lrm_user_status_dates_idx');
            $table->dropIndex('lrm_dates_status_idx');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('att_user_date_idx');
            $table->dropIndex('att_date_user_idx');
        });
    }
};
