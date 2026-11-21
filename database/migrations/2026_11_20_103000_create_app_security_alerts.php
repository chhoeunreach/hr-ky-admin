<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('app_security_alerts')) return;

        Schema::create('app_security_alerts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('dedupe_key', 191)->unique();
            $table->unsignedBigInteger('device_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('alert_type', 64)->index();
            $table->string('severity', 16)->index();
            $table->string('title', 160);
            $table->text('message');
            $table->json('context')->nullable();
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable()->index();
            $table->string('acknowledgement_reason', 512)->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamps();
            $table->index(['severity', 'acknowledged_at', 'created_at'], 'app_sec_alert_review_idx');
        });
        if (Schema::hasTable('app_security_schema_migration_objects')) {
            DB::table('app_security_schema_migration_objects')->insert([
                'table_name' => 'app_security_alerts', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $tracking = 'app_security_schema_migration_objects';
        if (!Schema::hasTable($tracking)
            || !DB::table($tracking)->where('table_name', 'app_security_alerts')->exists()) return;
        if (Schema::hasTable('app_security_alerts') && DB::table('app_security_alerts')->exists()) {
            throw new RuntimeException('App Security alert history exists; archive it before rolling back this migration.');
        }
        Schema::dropIfExists('app_security_alerts');
        DB::table($tracking)->where('table_name', 'app_security_alerts')->delete();
    }
};
