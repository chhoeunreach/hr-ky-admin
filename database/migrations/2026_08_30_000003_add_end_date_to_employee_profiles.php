<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_profiles', 'last_working_date')) {
                $table->date('last_working_date')->nullable()->after('employment_status');
            }
            if (!Schema::hasColumn('employee_profiles', 'employment_end_reason')) {
                $table->text('employment_end_reason')->nullable()->after('last_working_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('employee_profiles', 'employment_end_reason')) {
                $table->dropColumn('employment_end_reason');
            }
            if (Schema::hasColumn('employee_profiles', 'last_working_date')) {
                $table->dropColumn('last_working_date');
            }
        });
    }
};
