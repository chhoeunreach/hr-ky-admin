<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('national_id')->nullable();
            $table->string('nationality')->nullable();
            $table->string('education_level')->nullable();
            $table->string('telegram')->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('employment_status')->nullable();
            $table->unsignedInteger('probation_period')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('weekly_day_off')->nullable();
            $table->decimal('starting_salary', 15, 2)->nullable();
            $table->decimal('current_base_salary', 15, 2)->nullable();
            $table->decimal('allowances', 15, 2)->nullable();
            $table->decimal('commission', 15, 2)->nullable();
            $table->decimal('attendance_bonus', 15, 2)->nullable();
            $table->decimal('punctuality_bonus', 15, 2)->nullable();
            $table->decimal('overtime', 15, 2)->nullable();
            $table->text('other_benefits')->nullable();
            $table->string('payment_method')->nullable();
            $table->unsignedTinyInteger('salary_payment_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_employment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('effective_date')->nullable();
            $table->foreignId('old_position_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->foreignId('new_position_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->foreignId('old_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('new_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('old_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('new_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('old_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_type')->default('other');
            $table->text('reason')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'effective_date']);
        });

        Schema::create('employee_salary_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('effective_date')->nullable();
            $table->decimal('old_base_salary', 15, 2)->nullable();
            $table->decimal('increase_amount', 15, 2)->nullable();
            $table->decimal('increase_percentage', 8, 2)->nullable();
            $table->decimal('new_base_salary', 15, 2)->nullable();
            $table->decimal('allowance_before', 15, 2)->nullable();
            $table->decimal('allowance_after', 15, 2)->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('draft');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'effective_date']);
        });

        Schema::create('employee_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('interview_date')->nullable();
            $table->string('interview_stage')->nullable();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('interviewer_name')->nullable();
            $table->string('interviewer_position')->nullable();
            $table->string('recruitment_source')->nullable();
            $table->string('result')->default('pending');
            $table->decimal('score', 8, 2)->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('final_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_job_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('kpi_target')->nullable();
            $table->decimal('weight', 8, 2)->default(0);
            $table->string('status')->default('active');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('review_period_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('actual_value', 15, 2)->nullable();
            $table->string('unit')->default('number');
            $table->decimal('weight', 8, 2)->default(0);
            $table->decimal('achievement_percentage', 8, 2)->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->text('manager_comment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('review_type')->default('monthly');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('review_date')->nullable();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('hr_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_score', 8, 2)->default(0);
            $table->string('grade')->nullable();
            $table->string('status')->default('draft');
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('manager_comment')->nullable();
            $table->text('employee_comment')->nullable();
            $table->text('final_recommendation')->nullable();
            $table->date('next_review_date')->nullable();
            $table->timestamp('employee_acknowledged_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'period_start', 'period_end']);
        });

        Schema::create('performance_review_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_review_id')->constrained('performance_reviews')->cascadeOnDelete();
            $table->string('category')->nullable();
            $table->string('criteria');
            $table->text('description')->nullable();
            $table->decimal('max_score', 8, 2)->default(0);
            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('weight', 8, 2)->default(0);
            $table->text('comment')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('employee_training_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('training_date')->nullable();
            $table->string('training_title');
            $table->string('training_type')->default('internal');
            $table->string('trainer_name')->nullable();
            $table->foreignId('trainer_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider')->nullable();
            $table->text('objective')->nullable();
            $table->string('result')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->string('certificate')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('reward_date')->nullable();
            $table->string('reward_type')->default('praise');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('reward_amount', 15, 2)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_disciplinary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('incident_date')->nullable();
            $table->string('record_type')->default('verbal_warning');
            $table->string('severity')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('action_taken')->nullable();
            $table->string('warning_level')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->timestamp('employee_acknowledged_at')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('performance_review_id')->nullable()->constrained('performance_reviews')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('target')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('status')->default('not_started');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('employee_comment')->nullable();
            $table->text('manager_comment')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_improvement_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('performance_review_id')->nullable()->constrained('performance_reviews')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('expectations')->nullable();
            $table->text('support_required')->nullable();
            $table->text('progress_notes')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('document_type')->default('other');
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->date('document_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_profile_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('module');
            $table->string('action');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['employee_id', 'module', 'created_at']);
        });

        $groupId = DB::table('permissions')->where('permission_key', 'list_employee')->value('permission_groups_id');
        $permissions = [
            'employee.profile.view' => 'Employee 360 Profile View',
            'employee.profile.edit' => 'Employee 360 Profile Edit',
            'employee.employment.view' => 'Employee Employment View',
            'employee.employment.manage' => 'Employee Employment Manage',
            'employee.salary.view' => 'Employee Salary View',
            'employee.salary.manage' => 'Employee Salary Manage',
            'employee.salary.history.view' => 'Employee Salary History View',
            'employee.salary.history.manage' => 'Employee Salary History Manage',
            'employee.interview.view' => 'Employee Interview View',
            'employee.interview.manage' => 'Employee Interview Manage',
            'employee.kpi.view' => 'Employee KPI View',
            'employee.kpi.manage' => 'Employee KPI Manage',
            'employee.performance.view' => 'Employee Performance View',
            'employee.performance.create' => 'Employee Performance Create',
            'employee.performance.edit' => 'Employee Performance Edit',
            'employee.performance.submit' => 'Employee Performance Submit',
            'employee.performance.approve' => 'Employee Performance Approve',
            'employee.performance.hr_approve' => 'Employee Performance HR Approve',
            'employee.training.view' => 'Employee Training View',
            'employee.training.manage' => 'Employee Training Manage',
            'employee.reward.view' => 'Employee Reward View',
            'employee.reward.manage' => 'Employee Reward Manage',
            'employee.discipline.view' => 'Employee Discipline View',
            'employee.discipline.manage' => 'Employee Discipline Manage',
            'employee.goal.view' => 'Employee Goal View',
            'employee.goal.manage' => 'Employee Goal Manage',
            'employee.document.view' => 'Employee Document View',
            'employee.document.manage' => 'Employee Document Manage',
            'employee.audit.view' => 'Employee Audit View',
        ];

        foreach ($permissions as $key => $name) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['name' => $name, 'permission_groups_id' => $groupId]
            );
        }

        $sourcePermissionId = DB::table('permissions')->where('permission_key', 'show_detail_employee')->value('id');
        if ($sourcePermissionId) {
            $roleIds = DB::table('permission_roles')->where('permission_id', $sourcePermissionId)->pluck('role_id');
            $newPermissionIds = DB::table('permissions')->whereIn('permission_key', array_keys($permissions))->pluck('id');

            foreach ($roleIds as $roleId) {
                foreach ($newPermissionIds as $permissionId) {
                    DB::table('permission_roles')->updateOrInsert([
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $permissionKeys = [
            'employee.profile.view',
            'employee.profile.edit',
            'employee.employment.view',
            'employee.employment.manage',
            'employee.salary.view',
            'employee.salary.manage',
            'employee.salary.history.view',
            'employee.salary.history.manage',
            'employee.interview.view',
            'employee.interview.manage',
            'employee.kpi.view',
            'employee.kpi.manage',
            'employee.performance.view',
            'employee.performance.create',
            'employee.performance.edit',
            'employee.performance.submit',
            'employee.performance.approve',
            'employee.performance.hr_approve',
            'employee.training.view',
            'employee.training.manage',
            'employee.reward.view',
            'employee.reward.manage',
            'employee.discipline.view',
            'employee.discipline.manage',
            'employee.goal.view',
            'employee.goal.manage',
            'employee.document.view',
            'employee.document.manage',
            'employee.audit.view',
        ];
        $permissionIds = DB::table('permissions')->whereIn('permission_key', $permissionKeys)->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();

        Schema::dropIfExists('employee_profile_audit_logs');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_improvement_plans');
        Schema::dropIfExists('employee_goals');
        Schema::dropIfExists('employee_disciplinary_records');
        Schema::dropIfExists('employee_rewards');
        Schema::dropIfExists('employee_training_history');
        Schema::dropIfExists('performance_review_items');
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('employee_kpis');
        Schema::dropIfExists('employee_job_responsibilities');
        Schema::dropIfExists('employee_interviews');
        Schema::dropIfExists('employee_salary_history');
        Schema::dropIfExists('employee_employment_history');
        Schema::dropIfExists('employee_profiles');
    }
};
