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
        if (!Schema::hasTable('company_document_categories')) {
            Schema::create('company_document_categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name_km', 255);
                $table->string('name_en', 255);
                $table->string('icon', 50)->default('file-text');
                $table->string('color', 20)->default('#0f766e');
                $table->integer('order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default 5 categories
            DB::table('company_document_categories')->insert([
                [
                    'code' => 'master',
                    'name_km' => 'ឯកសារមេ (Master/Corporate)',
                    'name_en' => 'Corporate & Master Documents',
                    'icon' => 'award',
                    'color' => '#1d4ed8', // Blue
                    'order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'tax',
                    'name_km' => 'ឯកសារពន្ធដារ (Taxation)',
                    'name_en' => 'Tax Compliance (GDT)',
                    'icon' => 'dollar-sign',
                    'color' => '#b45309', // Amber/Gold
                    'order' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'business_license',
                    'name_km' => 'ឯកសារអាជីវកម្ម (Business Licenses)',
                    'name_en' => 'Business & Operating Licenses',
                    'icon' => 'briefcase',
                    'color' => '#0f766e', // Teal
                    'order' => 3,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'nssf',
                    'name_km' => 'ឯកសារ ប.ស.ស (NSSF & Labor)',
                    'name_en' => 'NSSF & Labor Compliance',
                    'icon' => 'shield',
                    'color' => '#dc2626', // Red
                    'order' => 4,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'contract',
                    'name_km' => 'ឯកសារកិច្ចសន្យានានា (Contracts & Leases)',
                    'name_en' => 'Contracts & Agreements',
                    'icon' => 'file-text',
                    'color' => '#7c3aed', // Purple
                    'order' => 5,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (!Schema::hasTable('company_documents')) {
            Schema::create('company_documents', function (Blueprint $table) {
                $table->id();
                $table->string('category_code', 50)->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->string('doc_number', 100)->nullable()->index(); // លេខកូដឯកសារ
                $table->string('title', 255);                          // ឈ្មោះឯកសារ
                $table->string('issuing_authority', 255)->nullable();   // ស្ថាប័នចេញ
                $table->date('issue_date')->nullable();                 // កាលបរិច្ឆេទចេញ
                $table->date('expiry_date')->nullable()->index();       // កាលបរិច្ឆេទផុតកំណត់
                $table->integer('alert_before_days')->default(30);       // រំលឹកមុន X ថ្ងៃ
                $table->decimal('cost_amount', 12, 2)->nullable();      // ទឹកប្រាក់ចំណាយ/ពន្ធ
                $table->string('currency', 10)->default('USD');         // USD / KHR
                $table->string('receipt_number', 100)->nullable();      // លេខបង្កាន់ដៃ
                $table->string('file_path', 500);                       // ទីតាំង File
                $table->string('file_name', 255)->nullable();           // ឈ្មោះ File ដើម
                $table->string('file_type', 50)->nullable();           // pdf, image, word, excel
                $table->unsignedBigInteger('file_size')->nullable();    // File size in bytes
                $table->string('confidentiality_level', 50)->default('internal'); // public, internal, restricted, confidential
                $table->string('status', 30)->default('valid')->index(); // valid, expiring_soon, expired, archived
                $table->text('description')->nullable();                // កំណត់សម្គាល់បន្ថែម
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_documents');
        Schema::dropIfExists('company_document_categories');
    }
};
