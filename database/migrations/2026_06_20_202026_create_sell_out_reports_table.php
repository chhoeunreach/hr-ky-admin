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
        Schema::create('sell_out_reports', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('original_invoice_no')->nullable()->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('seller_name');
            $table->string('branch_name');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('service_type');
            $table->string('payment_method');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('telegram_message_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sell_out_reports');
    }
};
