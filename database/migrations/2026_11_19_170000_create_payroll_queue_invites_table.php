<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_queue_invites')) {
            return;
        }

        Schema::create('payroll_queue_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('payroll_queue_sessions')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('invited_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_queue_invites');
    }
};
