<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('evaluation_period')->nullable();
            $table->string('status')->default('draft');
            $table->json('ai_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluation_interview_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained('evaluation_interviews')->cascadeOnDelete();
            $table->string('role');
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('evaluation_job_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->nullable()->constrained('evaluation_interviews')->nullOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('job_title')->nullable();
            $table->text('main_purpose')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('daily_tasks')->nullable();
            $table->json('kpis')->nullable();
            $table->json('required_skills')->nullable();
            $table->json('common_problems')->nullable();
            $table->json('customer_responsibilities')->nullable();
            $table->json('leadership_responsibilities')->nullable();
            $table->boolean('ai_generated')->default(true);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluation_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_description_id')->nullable()->constrained('evaluation_job_descriptions')->nullOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('evaluation_template_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('evaluation_templates')->cascadeOnDelete();
            $table->string('section');
            $table->text('question_kh');
            $table->text('question_en')->nullable();
            $table->decimal('weight', 8, 2)->default(0);
            $table->unsignedTinyInteger('max_score')->default(5);
            $table->text('reason')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('employee_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('evaluation_templates')->nullOnDelete();
            $table->foreignId('job_description_id')->nullable()->constrained('evaluation_job_descriptions')->nullOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('evaluation_period')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('total_score', 8, 2)->nullable();
            $table->string('result_label')->nullable();
            $table->json('ai_summary')->nullable();
            $table->text('evaluator_comment')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_evaluation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('employee_evaluations')->cascadeOnDelete();
            $table->foreignId('question_id')->nullable()->constrained('evaluation_template_questions')->nullOnDelete();
            $table->string('section');
            $table->text('question_kh');
            $table->text('question_en')->nullable();
            $table->decimal('weight', 8, 2)->default(0);
            $table->unsignedTinyInteger('max_score')->default(5);
            $table->unsignedTinyInteger('score')->nullable();
            $table->boolean('is_na')->default(false);
            $table->text('comment')->nullable();
            $table->decimal('weighted_score', 8, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_evaluation_answers');
        Schema::dropIfExists('employee_evaluations');
        Schema::dropIfExists('evaluation_template_questions');
        Schema::dropIfExists('evaluation_templates');
        Schema::dropIfExists('evaluation_job_descriptions');
        Schema::dropIfExists('evaluation_interview_messages');
        Schema::dropIfExists('evaluation_interviews');
    }
};
