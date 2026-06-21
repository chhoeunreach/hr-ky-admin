<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->string('original_invoice_no')->nullable()->after('invoice_no')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->dropIndex(['original_invoice_no']);
            $table->dropColumn('original_invoice_no');
        });
    }
};
