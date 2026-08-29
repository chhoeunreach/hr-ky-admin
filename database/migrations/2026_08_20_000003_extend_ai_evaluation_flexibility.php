<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_job_descriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluation_job_descriptions', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('position_id')->constrained('departments')->nullOnDelete();
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('department_id')->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'weekly_tasks')) {
                $table->json('weekly_tasks')->nullable()->after('daily_tasks');
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'monthly_tasks')) {
                $table->json('monthly_tasks')->nullable()->after('weekly_tasks');
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'required_knowledge')) {
                $table->json('required_knowledge')->nullable()->after('required_skills');
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'tools')) {
                $table->json('tools')->nullable()->after('required_knowledge');
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'reporting_responsibilities')) {
                $table->json('reporting_responsibilities')->nullable()->after('customer_responsibilities');
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'special_responsibilities')) {
                $table->json('special_responsibilities')->nullable()->after('leadership_responsibilities');
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('special_responsibilities');
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'status')) {
                $table->string('status')->default('draft')->after('version');
            }
            if (!Schema::hasColumn('evaluation_job_descriptions', 'document_number')) {
                $table->string('document_number')->nullable()->after('status');
            }
        });

        Schema::table('evaluation_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluation_templates', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('position_id')->constrained('departments')->nullOnDelete();
            }
            if (!Schema::hasColumn('evaluation_templates', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('department_id')->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('evaluation_templates', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('title');
            }
            if (!Schema::hasColumn('evaluation_templates', 'evaluation_type')) {
                $table->string('evaluation_type')->nullable()->after('version');
            }
        });

        Schema::table('evaluation_template_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluation_template_questions', 'question_type')) {
                $table->string('question_type')->default('score_1_5')->after('question_en');
            }
            if (!Schema::hasColumn('evaluation_template_questions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });

        Schema::table('employee_evaluations', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_evaluations', 'evaluation_type')) {
                $table->string('evaluation_type')->nullable()->after('evaluation_period');
            }
            if (!Schema::hasColumn('employee_evaluations', 'strengths')) {
                $table->text('strengths')->nullable()->after('evaluator_comment');
            }
            if (!Schema::hasColumn('employee_evaluations', 'areas_for_improvement')) {
                $table->text('areas_for_improvement')->nullable()->after('strengths');
            }
            if (!Schema::hasColumn('employee_evaluations', 'next_review_goals')) {
                $table->text('next_review_goals')->nullable()->after('areas_for_improvement');
            }
            if (!Schema::hasColumn('employee_evaluations', 'support_needed')) {
                $table->text('support_needed')->nullable()->after('next_review_goals');
            }
            if (!Schema::hasColumn('employee_evaluations', 'final_decision')) {
                $table->string('final_decision')->nullable()->after('support_needed');
            }
            if (!Schema::hasColumn('employee_evaluations', 'decision_reason')) {
                $table->text('decision_reason')->nullable()->after('final_decision');
            }
        });
    }

    public function down(): void
    {
        // Intentionally additive; existing production data should not be dropped automatically.
    }
};
