<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 50)->index();
            $table->string('operator', 50)->default('smart')->comment('smart, cellcard, metfone, etc.');
            $table->string('sim_slot', 50)->default('sim_1')->comment('sim_1, sim_2, esim, standalone');
            $table->string('purpose', 100)->nullable()->comment('sale, cashier, delivery, hotline, customer_service, general, other');
            $table->string('plan_type', 50)->default('prepaid')->comment('prepaid, postpaid');
            $table->string('serial_number', 100)->nullable()->comment('SIM ICCID');
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('assigned_date')->nullable();
            $table->string('status', 30)->default('active')->comment('active, inactive, lost');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_phone_numbers');
    }
};
