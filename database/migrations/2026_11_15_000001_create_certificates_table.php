<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('certificates')) {
            Schema::create('certificates', function (Blueprint $table) {
                $table->id();
                $table->string('credential_id')->unique();
                $table->enum('category', ['leader', 'service', 'course', 'custom'])->default('leader');
                $table->string('language_mode')->default('bilingual'); // bilingual, khmer, english
                $table->string('theme')->default('knea-yoeng-gold');
                
                // Issuer
                $table->string('issuer_name')->default('គ្នា​យើង · Knea Yoeng');
                $table->string('issuer_khmer_name')->nullable();
                $table->string('department')->nullable();
                $table->string('logo_url')->nullable();

                // Titles & Decrees
                $table->string('certificate_title');
                $table->string('khmer_title')->nullable();
                $table->text('authority_preamble')->nullable();
                $table->text('khmer_preamble')->nullable();
                $table->text('subtitle')->nullable();
                $table->text('khmer_subtitle')->nullable();

                // Recipient
                $table->string('recipient_name');
                $table->string('khmer_recipient_name')->nullable();
                $table->string('recipient_title')->nullable();
                $table->string('khmer_recipient_title')->nullable();

                // Achievement / Course
                $table->string('course_or_achievement_title');
                $table->string('khmer_course_or_achievement_title')->nullable();
                $table->text('citation')->nullable();
                $table->text('khmer_citation')->nullable();

                // Digital HR & Training Hours
                $table->string('employee_id')->nullable(); // Employee code or reference
                $table->unsignedBigInteger('user_id')->nullable(); // Foreign reference to users table
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->integer('training_hours')->default(0);
                $table->string('hr_competency_track')->nullable();
                $table->string('compliance_status')->default('Verified & Accredited');

                // Metadata & Dates
                $table->string('issue_date');
                $table->string('khmer_issue_date')->nullable();
                $table->string('expiry_date')->nullable();
                $table->string('qr_verification_url')->nullable();
                $table->json('signatories_json')->nullable();
                $table->json('metrics_json')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();

                $table->timestamps();
            });
        }

        // Register Certificate Permissions in permissions table
        if (Schema::hasTable('permissions')) {
            $group = DB::table('permission_groups')->where('name', 'like', '%training%')->orWhere('name', 'like', '%award%')->value('id') 
                     ?: DB::table('permissions')->value('permission_groups_id') ?: 1;

            $permissions = [
                'certificate_list' => 'List Certificates',
                'certificate_create' => 'Create Certificate',
                'certificate_edit' => 'Edit Certificate',
                'certificate_delete' => 'Delete Certificate',
                'certificate_download' => 'Download Certificate PDF',
                'certificate_verify' => 'Verify Certificate',
                'certificate_studio' => 'Open CertiCraft Studio',
            ];

            foreach ($permissions as $key => $name) {
                DB::table('permissions')->updateOrInsert(
                    ['permission_key' => $key],
                    ['name' => $name, 'permission_groups_id' => $group]
                );
            }

            // Assign permissions to Super Admin / Admin role (role_id = 1)
            $adminRoleId = DB::table('roles')->where('slug', 'admin')->orWhere('id', 1)->value('id') ?: 1;
            $newPermIds = DB::table('permissions')->whereIn('permission_key', array_keys($permissions))->pluck('id');
            foreach ($newPermIds as $pId) {
                DB::table('permission_roles')->updateOrInsert([
                    'role_id' => $adminRoleId,
                    'permission_id' => $pId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'certificate_list', 'certificate_create', 'certificate_edit',
            'certificate_delete', 'certificate_download', 'certificate_verify', 'certificate_studio'
        ];
        if (Schema::hasTable('permissions')) {
            $ids = DB::table('permissions')->whereIn('permission_key', $keys)->pluck('id');
            DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
        }
        Schema::dropIfExists('certificates');
    }
};
