<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_record_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('daily_record_categories');
            $table->string('kind', 16);
            $table->string('name_en', 120);
            $table->string('name_km', 120);
            $table->string('icon', 24)->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('sensitive')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->json('fields')->nullable();
            $table->json('tags')->nullable();
            $table->json('workflow')->nullable();
            $table->unsignedSmallInteger('follow_up_days')->nullable();
            $table->index(['kind', 'parent_id', 'active', 'position'], 'daily_category_catalog_idx');
        });
        Schema::table('daily_entries', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('daily_record_categories');
            $table->foreignId('subcategory_id')->nullable()->constrained('daily_record_categories');
            $table->dateTime('occurred_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->dateTime('follow_up_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('archived_at')->nullable();
            $table->boolean('pinned')->default(false);
            $table->boolean('sensitive')->default(false);
            $table->string('priority', 12)->nullable();
            $table->string('verification', 32)->nullable();
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->json('classification')->nullable();
            $table->json('details')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedInteger('revision')->default(0);
            $table->index(['employee_id', 'kind', 'occurred_at'], 'daily_entries_employee_work_date');
            $table->index(['company_id', 'kind', 'occurred_at'], 'daily_entries_company_work_date');
            $table->index(['responsible_id', 'company_id', 'status'], 'daily_entries_responsible');
        });
        Schema::table('daily_record_files', fn (Blueprint $table) => $table->boolean('restricted')->default(false));
        Schema::create('daily_report_submissions', function (Blueprint $table) {
            $table->foreignId('entry_id')->primary()->constrained('daily_entries');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('company_id');
            $table->date('report_date');
            $table->unsignedInteger('version')->default(0);
            $table->dateTime('submitted_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('reviewer_type', 8)->nullable();
            $table->json('draft');
            $table->unique(['employee_id', 'company_id', 'report_date'], 'daily_report_one_per_employee_date');
            $table->index(['report_date', 'submitted_at']);
        });
        Schema::create('daily_report_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->references('entry_id')->on('daily_report_submissions');
            $table->unsignedInteger('version');
            $table->json('content');
            $table->dateTime('submitted_at');
            $table->unique(['submission_id', 'version']);
        });
        Schema::create('daily_report_items', function (Blueprint $table) {
            $table->foreignId('version_id')->constrained('daily_report_versions');
            $table->foreignId('entry_id')->constrained('daily_entries');
            $table->primary(['version_id', 'entry_id']);
        });
        Schema::create('daily_record_drafts', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('company_id');
            $table->string('draft_key', 48);
            $table->mediumText('payload');
            $table->dateTime('updated_at');
            $table->primary(['employee_id', 'company_id', 'draft_key'], 'daily_record_draft_account_key');
        });
        $group = DB::table('permissions')->where('permission_key', 'daily_records.manage')->value('permission_groups_id');
        foreach ([
            'daily_records.sensitive' => 'Review confidential employee incidents',
            'daily_records.categories' => 'Configure Daily Records categories and workflows',
        ] as $key => $name) {
            DB::table('permissions')->insert(['permission_key' => $key, 'name' => $name, 'permission_groups_id' => $group]);
        }

        require __DIR__.'/catalog/daily_work_categories.php';
    }

    public function down(): void
    {
        throw new LogicException('Keep the Daily Work Records schema and data during application rollback.');
    }
};
