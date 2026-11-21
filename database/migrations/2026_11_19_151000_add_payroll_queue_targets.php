<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('payroll_queue_sessions', 'department_id')) {
            Schema::table('payroll_queue_sessions', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('branch_id')->index();
            });
        }
        if (!Schema::hasColumn('payroll_queue_sessions', 'target_employee_id')) {
            Schema::table('payroll_queue_sessions', function (Blueprint $table) {
                $table->unsignedBigInteger('target_employee_id')->nullable()->after('department_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payroll_queue_sessions', 'target_employee_id')) {
            Schema::table('payroll_queue_sessions', fn (Blueprint $table) => $table->dropColumn('target_employee_id'));
        }
        if (Schema::hasColumn('payroll_queue_sessions', 'department_id')) {
            Schema::table('payroll_queue_sessions', fn (Blueprint $table) => $table->dropColumn('department_id'));
        }
    }
};
