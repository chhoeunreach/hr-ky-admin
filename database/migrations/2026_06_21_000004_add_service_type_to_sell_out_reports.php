<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sell_out_reports', 'service_type')) {
            return;
        }

        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->string('service_type')->nullable()->after('customer_phone');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('sell_out_reports', 'service_type')) {
            return;
        }

        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};
