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
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('seller_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
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
