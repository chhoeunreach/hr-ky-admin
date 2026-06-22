<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('chat_id');
            $table->string('action_key')->default('general');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('branch_name')->nullable();
            $table->string('department_name')->nullable();
            $table->boolean('send_for_all')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['action_key', 'is_active']);
            $table->index(['branch_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_groups');
    }
};
