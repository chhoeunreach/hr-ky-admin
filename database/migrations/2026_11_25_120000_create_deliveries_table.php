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
        if (!Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('tracking_token', 64)->unique()->index();
                $table->string('tracking_code', 50)->nullable()->index();
                $table->string('invoice_number', 100)->nullable();
                $table->string('customer_name', 255);
                $table->string('customer_phone', 50);
                $table->string('destination_address', 500)->nullable();
                $table->decimal('destination_latitude', 10, 7)->nullable();
                $table->decimal('destination_longitude', 10, 7)->nullable();
                $table->string('origin_address', 500)->nullable();
                $table->decimal('origin_latitude', 10, 7)->nullable();
                $table->decimal('origin_longitude', 10, 7)->nullable();
                $table->decimal('current_latitude', 10, 7)->nullable();
                $table->decimal('current_longitude', 10, 7)->nullable();
                $table->decimal('heading', 6, 2)->nullable();
                $table->decimal('speed', 6, 2)->nullable();
                $table->timestamp('last_ping_at')->nullable();
                $table->string('status', 30)->default('in_transit')->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('proof_photo_path', 500)->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
