<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kiosk_devices', function (Blueprint $table) {
            $table->timestamp('provisioned_at')->nullable()->after('last_seen_at')->index();
        });

        // Preserve devices created before one-time QR exchange was introduced.
        DB::table('kiosk_devices')
            ->whereNull('provisioned_at')
            ->update(['provisioned_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('kiosk_devices', function (Blueprint $table) {
            $table->dropColumn('provisioned_at');
        });
    }
};
