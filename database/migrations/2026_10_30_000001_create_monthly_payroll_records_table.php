<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('monthly_payroll_records', function (Blueprint $table) {
            $table->id();
            $table->char('month', 7);
            $table->unsignedBigInteger('branch_id');
            $table->string('branch_name');
            $table->string('currency', 16);
            $table->json('options');
            $table->json('columns');
            $table->json('rows');
            $table->json('summary');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->string('generated_by_name')->nullable();
            $table->string('generated_by_type', 16);
            $table->timestamps();
            $table->unique(['month', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_payroll_records');
    }
};
