<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('user_locations', 'device_key')) {
            Schema::table('user_locations', function (Blueprint $table) {
                $table->string('device_key', 64)->nullable()->after('user_id');
            });
        }

        if (!Schema::hasColumn('user_locations', 'device_type')) {
            Schema::table('user_locations', function (Blueprint $table) {
                $table->string('device_type', 20)->nullable()->after('device_key');
            });
        }

        // MySQL requires a non-unique user_id index to keep supporting the
        // foreign key after the original unique index is removed.
        if (!Schema::hasIndex('user_locations', 'user_locations_user_id_index')) {
            Schema::table('user_locations', function (Blueprint $table) {
                $table->index('user_id', 'user_locations_user_id_index');
            });
        }

        if (Schema::hasIndex('user_locations', 'user_locations_user_id_unique')) {
            Schema::table('user_locations', function (Blueprint $table) {
                $table->dropUnique('user_locations_user_id_unique');
            });
        }

        if (!Schema::hasIndex('user_locations', 'user_locations_user_device_unique')) {
            Schema::table('user_locations', function (Blueprint $table) {
                $table->unique(['user_id', 'device_key'], 'user_locations_user_device_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('user_locations', 'user_locations_user_device_unique')) {
            Schema::table('user_locations', function (Blueprint $table) {
                $table->dropUnique('user_locations_user_device_unique');
            });
        }

        Schema::table('user_locations', function (Blueprint $table) {
            $table->dropColumn(['device_key', 'device_type']);
        });
    }
};
