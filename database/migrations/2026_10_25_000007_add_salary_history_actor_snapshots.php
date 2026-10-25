<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('salary_revise_histories', function (Blueprint $table) {
            // System administrators live in admins, rather than users.
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->string('created_by_name')->nullable();
        });
        Schema::table('employee_salary_history', function (Blueprint $table) {
            $table->string('requested_by_name')->nullable();
            $table->string('approved_by_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employee_salary_history', fn (Blueprint $table) => $table->dropColumn(['requested_by_name', 'approved_by_name']));
        Schema::table('salary_revise_histories', fn (Blueprint $table) => $table->dropColumn('created_by_name'));
        // Keep created_by nullable so existing administrator history remains valid.
    }
};
