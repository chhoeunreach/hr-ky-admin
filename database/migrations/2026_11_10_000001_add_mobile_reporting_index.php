<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sell_out_reports', fn (Blueprint $table) => $table->index(['user_id','created_at','id'], 'sell_out_mobile_reporting_idx'));
    }
    public function down(): void
    {
        Schema::table('sell_out_reports', fn (Blueprint $table) => $table->dropIndex('sell_out_mobile_reporting_idx'));
    }
};
