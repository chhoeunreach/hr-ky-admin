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
        // 1. Attendance Logs table
        if (!Schema::hasTable('attendance_logs')) {
            Schema::create('attendance_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->date('date');
                $table->timestamp('check_in_at')->nullable();
                $table->timestamp('check_out_at')->nullable();
                $table->string('method', 50)->default('qr_pass'); // qr_pass, biometric, manual
                $table->string('location_token', 150)->nullable();
                $table->string('status', 50)->default('present'); // present, late, excused
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'date']);
                $table->index(['branch_id', 'date']);
            });
        }

        // 2. HR Queues table
        if (!Schema::hasTable('hr_queues')) {
            Schema::create('hr_queues', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('date');
                $table->string('ticket_number', 20); // e.g. A-014, B-005
                $table->unsignedInteger('counter_number')->nullable(); // 1 to 5
                $table->string('status', 50)->default('waiting'); // waiting, calling, serving, completed, cancelled, skipped
                $table->timestamp('called_at')->nullable();
                $table->timestamp('served_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('qr_payload', 255)->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['date', 'status']);
                $table->index(['counter_number', 'status']);
                $table->index(['user_id', 'date']);
            });
        }

        // 3. Payroll Disbursements table
        if (!Schema::hasTable('payroll_disbursements')) {
            Schema::create('payroll_disbursements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('payslip_id')->nullable();
                $table->unsignedBigInteger('queue_ticket_id')->nullable();
                $table->string('month', 7); // e.g. 2026-11
                $table->timestamp('disbursed_at');
                $table->unsignedBigInteger('disbursed_by')->nullable();
                
                // Itemized figures
                $table->decimal('basic_salary', 12, 2)->default(0);
                
                // Deductions (Negative/Red)
                $table->decimal('leave_penalty', 12, 2)->default(0);         // ច្បាប់
                $table->decimal('late_penalty', 12, 2)->default(0);          // យឺត
                $table->decimal('loan_deduction', 12, 2)->default(0);         // ខ្ចី
                $table->decimal('installment_deduction', 12, 2)->default(0);  // រំលស់
                $table->decimal('total_deductions', 12, 2)->default(0);
                
                // Bonuses & Commissions (Positive/Green)
                $table->decimal('sales_commission', 12, 2)->default(0);      // លក់
                $table->decimal('iron_commission', 12, 2)->default(0);       // អ៊ុត
                $table->decimal('material_commission', 12, 2)->default(0);   // សម្ភារ
                $table->decimal('attendance_bonus', 12, 2)->default(0);      // មិនឈប់
                $table->decimal('punctuality_bonus', 12, 2)->default(0);     // ទៀងទាត់
                $table->decimal('total_bonuses', 12, 2)->default(0);
                
                // Net Received
                $table->decimal('net_received_salary', 12, 2)->default(0);
                
                $table->string('payment_method', 50)->default('Cash'); // Cash, ABA Bank, ACLEDA, Wing
                $table->string('transaction_ref', 100)->nullable();
                $table->string('status', 50)->default('paid'); // pending, paid, reversed
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'month']);
                $table->index(['queue_ticket_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_disbursements');
        Schema::dropIfExists('hr_queues');
        Schema::dropIfExists('attendance_logs');
    }
};
