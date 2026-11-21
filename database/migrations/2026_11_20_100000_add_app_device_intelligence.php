<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('app_device_intelligence')) {
            return;
        }

        Schema::create('app_device_intelligence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')->constrained('app_registered_devices')->cascadeOnDelete();
            $table->date('observed_date');
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->unsignedInteger('authorized_request_count')->default(0);
            $table->unsignedSmallInteger('network_change_count')->default(0);
            $table->char('last_ip_hash', 64)->nullable();
            $table->unsignedBigInteger('version_code')->nullable();
            $table->unsignedSmallInteger('android_sdk')->nullable();
            $table->json('observations')->nullable();
            $table->timestamps();
            $table->unique(['device_id', 'observed_date'], 'appsec_intel_device_date_unique');
            $table->index(['observed_date', 'authorized_request_count']);
        });
        if (Schema::hasTable('app_security_schema_migration_objects')) {
            DB::table('app_security_schema_migration_objects')->insert([
                'table_name' => 'app_device_intelligence',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $tracking = 'app_security_schema_migration_objects';
        if (!Schema::hasTable($tracking)) return;
        $createdBySecurity = DB::table($tracking)
            ->where('table_name', 'app_device_intelligence')->exists();
        if (!$createdBySecurity) return;
        Schema::dropIfExists('app_device_intelligence');
        DB::table($tracking)->where('table_name', 'app_device_intelligence')->delete();
    }
};
