<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hr_queue_attendance_logs')) {
            Schema::create('hr_queue_attendance_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->date('date');
                $table->timestamp('check_in_at')->nullable();
                $table->timestamp('check_out_at')->nullable();
                $table->string('method', 50)->default('qr_pass');
                $table->string('location_token', 150)->nullable();
                $table->string('status', 50)->default('present');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'date']);
                $table->index(['branch_id', 'date']);
            });
        }

        if (Schema::hasIndex('payroll_queue_tickets', ['session_id', 'ticket_number'], 'unique')) {
            return;
        }

        $duplicates = DB::table('payroll_queue_tickets')
            ->select('session_id', 'ticket_number')
            ->groupBy('session_id', 'ticket_number')
            ->havingRaw('COUNT(*) > 1')
            ->first();
        if ($duplicates) {
            throw new RuntimeException("Reconcile duplicate payroll ticket {$duplicates->ticket_number} in session {$duplicates->session_id} before retrying this migration.");
        }

        Schema::table('payroll_queue_tickets', function (Blueprint $table) {
            $table->unique(['session_id', 'ticket_number']);
        });
    }

    public function down(): void
    {
        // These objects can predate this migration and may hold live queue data.
    }
};
