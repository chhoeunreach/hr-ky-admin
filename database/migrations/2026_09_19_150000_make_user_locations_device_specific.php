<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            $table->dropUnique('user_locations_user_id_unique');
            $table->string('device_key', 64)->nullable()->after('user_id');
            $table->string('device_type', 20)->nullable()->after('device_key');
            $table->unique(['user_id', 'device_key'], 'user_locations_user_device_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            $table->dropUnique('user_locations_user_device_unique');
            $table->dropColumn(['device_key', 'device_type']);
        });
    }
};
