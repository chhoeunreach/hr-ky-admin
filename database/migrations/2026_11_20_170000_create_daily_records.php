<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_task_series', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_type', 8);
            $table->string('frequency', 10);
            $table->string('anchor_date', 10);
            $table->dateTime('next_at')->nullable()->index();
            $table->dateTime('next_due_at');
            $table->unsignedBigInteger('reference_task_id')->nullable();
            $table->json('template');
            $table->timestamps();
        });

        Schema::create('daily_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->nullable()->constrained('daily_task_series');
            $table->dateTime('occurrence_at')->nullable();
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_type', 8);
            $table->string('title', 180);
            $table->text('instructions')->nullable();
            $table->string('priority', 10);
            $table->json('checklist');
            $table->unsignedBigInteger('follow_up_entry_id')->nullable();
            $table->uuid('request_id')->nullable();
            $table->timestamps();
            $table->unique(['series_id', 'occurrence_at']);
            $table->unique(['actor_type', 'actor_id', 'request_id'], 'daily_task_request_unique');
        });

        Schema::create('daily_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('daily_tasks');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status', 20)->default('assigned');
            $table->string('held_status', 20)->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->json('checked');
            $table->text('instructions_override')->nullable();
            $table->dateTime('due_at');
            $table->dateTime('remind_at')->nullable();
            $table->unsignedInteger('deadline_version')->default(0);
            $table->dateTime('reminder_sent_at')->nullable();
            $table->dateTime('overdue_sent_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('archived_at')->nullable();
            $table->dateTime('superseded_at')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'due_at']);
            $table->index(['task_id', 'employee_id', 'superseded_at']);
            $table->index(['company_id', 'branch_id', 'department_id'], 'daily_assignment_org_index');
            $table->index(['status', 'due_at']);
            $table->index(['remind_at', 'status']);
        });

        Schema::create('daily_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->nullable()->constrained('daily_task_assignments');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('kind', 16);
            $table->uuid('request_id')->nullable();
            $table->string('title', 180);
            $table->text('body')->nullable();
            $table->text('solution')->nullable();
            $table->boolean('private')->default(false);
            $table->boolean('critical')->default(false);
            $table->string('status', 20)->nullable();
            $table->dateTime('requested_due_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'created_at']);
            $table->unique(['employee_id', 'request_id'], 'daily_entry_request_unique');
            $table->index(['company_id', 'branch_id', 'department_id', 'created_at'], 'daily_entry_org_index');
            $table->index(['private', 'kind', 'status']);
            $table->index(['assignment_id', 'kind', 'status']);
        });

        Schema::create('daily_record_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->nullable()->constrained('daily_task_assignments');
            $table->foreignId('entry_id')->nullable()->constrained('daily_entries');
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_type', 8);
            $table->string('action', 32);
            $table->string('status', 20)->nullable();
            $table->json('details');
            $table->boolean('on_time')->nullable();
            $table->dateTime('created_at');
            $table->index(['assignment_id', 'action', 'created_at'], 'daily_event_assignment_action_index');
            $table->index(['assignment_id', 'created_at']);
            $table->index(['entry_id', 'created_at']);
        });

        Schema::create('daily_record_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->nullable()->constrained('daily_tasks');
            $table->foreignId('assignment_id')->nullable()->constrained('daily_task_assignments');
            $table->foreignId('entry_id')->nullable()->constrained('daily_entries');
            $table->string('path')->unique();
            $table->string('name');
            $table->unsignedInteger('bytes');
        });

        Schema::create('daily_record_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('event_key', 120)->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('message');
            $table->dateTime('created_at');
            $table->dateTime('delivered_at')->nullable()->index();
        });

        $type = DB::table('permission_group_types')->where('slug', 'web')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['slug' => 'web', 'name' => 'Web']);
        $group = DB::table('permission_groups')->insertGetId(['name' => 'Daily Records', 'group_type_id' => $type]);
        DB::table('permissions')->insert([
            'name' => 'Manage Employee Tasks & Daily Records',
            'permission_key' => 'daily_records.manage',
            'permission_groups_id' => $group,
        ]);
    }

    public function down(): void
    {
        $permission = DB::table('permissions')->where('permission_key', 'daily_records.manage')->first();
        if ($permission) {
            DB::table('permission_roles')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
            DB::table('permission_groups')->where('id', $permission->permission_groups_id)
                ->where('name', 'Daily Records')->delete();
        }
        Schema::dropIfExists('daily_record_notifications');
        Schema::dropIfExists('daily_record_files');
        Schema::dropIfExists('daily_record_events');
        Schema::dropIfExists('daily_entries');
        Schema::dropIfExists('daily_task_assignments');
        Schema::dropIfExists('daily_tasks');
        Schema::dropIfExists('daily_task_series');
    }
};
