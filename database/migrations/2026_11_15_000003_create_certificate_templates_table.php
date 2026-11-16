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
        if (!Schema::hasTable('certificate_templates')) {
            Schema::create('certificate_templates', function (Blueprint $table) {
                $table->id();
                $table->string('template_code', 50)->unique();
                $table->string('title');
                $table->string('khmer_title')->nullable();
                $table->string('category', 50)->default('leader');
                $table->string('layout_template', 80)->default('executive-verify');
                $table->string('version_tag', 20)->default('V1');
                $table->string('language_mode', 20)->default('bilingual');
                $table->string('theme', 50)->default('navy-gold');
                $table->text('description')->nullable();
                $table->text('khmer_description')->nullable();

                // Issuer settings
                $table->string('issuer_name')->default('KNEAYERNG PHONE SHOP');
                $table->string('issuer_khmer_name')->nullable()->default('ហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)');
                $table->string('department')->nullable();
                $table->string('logo_url')->nullable();

                // Standard template titles & wording
                $table->string('certificate_title')->default('CERTIFICATE OF APPRECIATION');
                $table->string('khmer_certificate_title')->nullable()->default('វិញ្ញាបនបត្រកោតសរសើរ និងផ្ដល់ការតែងតាំង');
                $table->text('subtitle')->nullable();
                $table->text('khmer_subtitle')->nullable();
                $table->text('authority_preamble')->nullable();
                $table->text('khmer_preamble')->nullable();
                $table->string('course_or_achievement_title')->nullable();
                $table->string('khmer_course_or_achievement_title')->nullable();
                $table->text('citation')->nullable();
                $table->text('khmer_citation')->nullable();
                $table->text('citation_closing')->nullable();

                // Default HR & Accreditation
                $table->integer('training_hours')->default(45);
                $table->string('hr_competency_track')->nullable();
                $table->string('compliance_status')->default('Verified & Accredited');

                // JSON fields
                $table->json('signatories_json')->nullable();
                $table->json('styling_json')->nullable();
                $table->json('seal_json')->nullable();
                $table->longText('payload_json')->nullable();

                // Flags & Controls
                $table->string('preview_image')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('certificates') && !Schema::hasColumn('certificates', 'template_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->unsignedBigInteger('template_id')->nullable()->after('credential_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('certificates') && Schema::hasColumn('certificates', 'template_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropColumn('template_id');
            });
        }

        Schema::dropIfExists('certificate_templates');
    }
};
