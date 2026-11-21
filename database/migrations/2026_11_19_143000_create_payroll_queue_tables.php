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
        if (!Schema::hasTable('payroll_queue_sessions')) {
            Schema::create('payroll_queue_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_code', 50)->unique();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('month', 7); // e.g. 2026-11
                $table->string('title');
                $table->string('qr_token', 100)->unique();
                $table->string('status')->default('open'); // open, paused, closed
                $table->unsignedInteger('max_active_serving')->default(5);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'branch_id', 'month', 'status']);
            });
        }

        if (!Schema::hasTable('payroll_queue_tickets')) {
            Schema::create('payroll_queue_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('payroll_queue_sessions')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('payslip_id')->nullable();
                $table->string('ticket_number', 20); // e.g. A-001
                $table->unsignedInteger('counter_number')->nullable(); // 1, 2, 3, 4, 5
                $table->string('status')->default('waiting'); // waiting, calling, serving, paid, skipped, cancelled
                $table->timestamp('joined_at')->nullable();
                $table->timestamp('called_at')->nullable();
                $table->timestamp('served_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->unsignedBigInteger('payment_method_id')->nullable();
                $table->decimal('net_amount_paid', 12, 2)->nullable();
                $table->unsignedBigInteger('disbursed_by')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['session_id', 'status']);
                $table->index(['session_id', 'counter_number']);
                $table->index(['employee_id', 'session_id']);
                $table->unique(['session_id', 'ticket_number']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_queue_tickets');
        Schema::dropIfExists('payroll_queue_sessions');
    }
};
