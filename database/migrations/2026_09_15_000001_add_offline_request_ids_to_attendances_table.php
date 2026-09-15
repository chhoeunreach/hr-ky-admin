<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('check_in_offline_request_id', 100)->nullable()->after('check_in_selfie');
            $table->string('check_out_offline_request_id', 100)->nullable()->after('check_out_selfie');
            $table->unique('check_in_offline_request_id', 'attendances_check_in_offline_request_unique');
            $table->unique('check_out_offline_request_id', 'attendances_check_out_offline_request_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_check_in_offline_request_unique');
            $table->dropUnique('attendances_check_out_offline_request_unique');
            $table->dropColumn([
                'check_in_offline_request_id',
                'check_out_offline_request_id',
            ]);
        });
    }
};
