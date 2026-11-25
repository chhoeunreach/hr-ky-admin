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
        if (!Schema::hasTable('company_document_attachments')) {
            Schema::create('company_document_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_document_id')->index();
                $table->string('file_path', 500);
                $table->string('file_name', 255)->nullable();
                $table->string('file_type', 50)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('company_document_id')
                      ->references('id')
                      ->on('company_documents')
                      ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_document_attachments');
    }
};
