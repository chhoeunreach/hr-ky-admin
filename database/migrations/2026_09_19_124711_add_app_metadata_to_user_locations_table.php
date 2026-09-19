<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('user_locations', 'app_name')) {
                $table->string('app_name')->nullable()->after('device_name');
            }
            if (!Schema::hasColumn('user_locations', 'app_version')) {
                $table->string('app_version', 80)->nullable()->after('app_name');
            }
            if (!Schema::hasColumn('user_locations', 'app_build')) {
                $table->string('app_build', 80)->nullable()->after('app_version');
            }
            if (!Schema::hasColumn('user_locations', 'device_model')) {
                $table->string('device_model')->nullable()->after('app_build');
            }
            if (!Schema::hasColumn('user_locations', 'os_version')) {
                $table->string('os_version', 120)->nullable()->after('device_model');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            foreach (['app_name', 'app_version', 'app_build', 'device_model', 'os_version'] as $column) {
                if (Schema::hasColumn('user_locations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
