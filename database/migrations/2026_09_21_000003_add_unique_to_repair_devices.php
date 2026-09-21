<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_devices', function (Blueprint $table) {
            $table->unique(['repair_brand_id', 'name'], 'repair_devices_brand_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('repair_devices', function (Blueprint $table) {
            $table->dropUnique('repair_devices_brand_name_unique');
        });
    }
};
