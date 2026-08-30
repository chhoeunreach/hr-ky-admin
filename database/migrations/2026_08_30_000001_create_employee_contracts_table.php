<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('contract_no')->nullable();
            $table->date('contract_date')->nullable();
            $table->string('shop_name')->nullable();
            $table->text('shop_address')->nullable();
            $table->string('shop_representative')->nullable();
            $table->string('birth_address')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('job_title')->nullable();
            $table->text('main_responsibilities')->nullable();
            $table->text('additional_responsibilities')->nullable();
            $table->text('asset_responsibilities')->nullable();
            $table->decimal('probation_salary', 15, 2)->nullable();
            $table->decimal('extra_salary', 15, 2)->nullable();
            $table->decimal('monthly_salary', 15, 2)->nullable();
            $table->string('salary_currency')->nullable();
            $table->string('probation_period_text')->nullable();
            $table->string('main_contract_period')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('payment_date_text')->nullable();
            $table->text('benefits')->nullable();
            $table->string('working_time')->nullable();
            $table->string('working_days')->nullable();
            $table->string('holiday_text')->nullable();
            $table->text('discipline_rules')->nullable();
            $table->text('confidentiality')->nullable();
            $table->text('termination_terms')->nullable();
            $table->text('general_duties')->nullable();
            $table->string('party_a_signature_name')->nullable();
            $table->string('party_b_signature_name')->nullable();
            $table->date('party_a_signed_date')->nullable();
            $table->date('party_b_signed_date')->nullable();
            $table->json('attachments')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
