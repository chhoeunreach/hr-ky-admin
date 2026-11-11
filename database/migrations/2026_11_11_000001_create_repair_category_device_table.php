<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_category_device', function (Blueprint $table) {
            $table->foreignId('repair_category_id')->constrained('repair_categories')->cascadeOnDelete();
            $table->foreignId('repair_device_id')->constrained('repair_devices')->cascadeOnDelete();
            $table->primary(['repair_category_id', 'repair_device_id']);
            $table->index('repair_device_id');
        });

        // Keep the category associations already represented by existing repair prices.
        DB::table('repair_category_device')->insertUsing(
            ['repair_category_id', 'repair_device_id'],
            DB::table('repair_prices')->join('repair_services', 'repair_services.id', '=', 'repair_prices.repair_service_id')
                ->select('repair_services.repair_category_id', 'repair_prices.repair_device_id')->distinct()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_category_device');
    }
};
