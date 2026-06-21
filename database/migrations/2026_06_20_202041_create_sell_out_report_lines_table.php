<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sell_out_report_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sell_out_report_id')->constrained('sell_out_reports')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->string('identifier_type')->nullable();
            $table->string('primary_identifier')->nullable();
            $table->string('imei')->nullable();
            $table->string('imei2')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('model_number')->nullable();
            $table->string('color')->nullable();
            $table->string('storage')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('sell_out_report_id');
            $table->index('product_id');
            $table->index('variation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sell_out_report_lines');
    }
};
