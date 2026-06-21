<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->string('customer_phone', 50)->nullable()->after('customer_name')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->dropIndex(['customer_phone']);
            $table->dropColumn('customer_phone');
        });
    }
};
