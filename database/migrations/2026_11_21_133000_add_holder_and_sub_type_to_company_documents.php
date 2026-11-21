<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('company_documents', 'holder_name')) {
                $table->string('holder_name', 255)->nullable()->after('title');
            }
            if (!Schema::hasColumn('company_documents', 'doc_sub_type')) {
                $table->string('doc_sub_type', 50)->nullable()->after('category_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            if (Schema::hasColumn('company_documents', 'holder_name')) {
                $table->dropColumn('holder_name');
            }
            if (Schema::hasColumn('company_documents', 'doc_sub_type')) {
                $table->dropColumn('doc_sub_type');
            }
        });
    }
};
