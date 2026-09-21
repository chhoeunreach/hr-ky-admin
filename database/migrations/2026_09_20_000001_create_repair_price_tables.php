<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('repair_device_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('repair_device_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_brand_id')->constrained('repair_brands')->cascadeOnDelete();
            $table->foreignId('repair_device_type_id')->constrained('repair_device_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['repair_brand_id', 'repair_device_type_id', 'slug'], 'repair_series_unique');
        });

        Schema::create('repair_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_brand_id')->constrained('repair_brands')->cascadeOnDelete();
            $table->foreignId('repair_device_type_id')->constrained('repair_device_types')->cascadeOnDelete();
            $table->foreignId('repair_device_series_id')->nullable()->constrained('repair_device_series')->nullOnDelete();
            $table->string('name');
            $table->string('model_number')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->index(['repair_brand_id', 'repair_device_type_id', 'repair_device_series_id'], 'repair_devices_filters_idx');
        });

        Schema::create('repair_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_kh')->nullable();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('repair_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_category_id')->constrained('repair_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('name_kh')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('repair_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_device_id')->constrained('repair_devices')->cascadeOnDelete();
            $table->foreignId('repair_service_id')->constrained('repair_services')->cascadeOnDelete();
            $table->string('part_name')->nullable();
            $table->string('part_type')->nullable();
            $table->decimal('part_cost', 12, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->unsignedInteger('warranty_days')->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->text('note')->nullable();
            $table->string('availability_status')->default('available');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['repair_device_id', 'repair_service_id', 'part_type'], 'repair_prices_unique');
        });

        Schema::create('repair_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_price_id')->constrained('repair_prices')->cascadeOnDelete();
            $table->decimal('old_part_cost', 12, 2)->nullable();
            $table->decimal('new_part_cost', 12, 2)->nullable();
            $table->decimal('old_service_fee', 12, 2)->nullable();
            $table->decimal('new_service_fee', 12, 2)->nullable();
            $table->decimal('old_selling_price', 12, 2)->nullable();
            $table->decimal('new_selling_price', 12, 2)->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_price_histories');
        Schema::dropIfExists('repair_prices');
        Schema::dropIfExists('repair_services');
        Schema::dropIfExists('repair_categories');
        Schema::dropIfExists('repair_devices');
        Schema::dropIfExists('repair_device_series');
        Schema::dropIfExists('repair_device_types');
        Schema::dropIfExists('repair_brands');
    }
};
