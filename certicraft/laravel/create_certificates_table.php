<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('credential_id')->unique();
            $table->enum('category', ['leader', 'service', 'course', 'custom'])->default('leader');
            $table->string('language_mode')->default('bilingual'); // bilingual, khmer, english
            
            // Issuer
            $table->string('issuer_name')->default('គ្នា​យើង · Knea Yoeng');
            $table->string('issuer_khmer_name')->nullable();
            $table->string('department')->nullable();

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
            $table->text('citation');
            $table->text('khmer_citation')->nullable();

            // KNEAYERNG HRMS & Training Hours
            $table->string('employee_id')->nullable();
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

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
