<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('app_security_refresh_token_history')) return;

        Schema::create('app_security_refresh_token_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('app_security_sessions')->cascadeOnDelete();
            $table->char('token_sha256', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('reuse_detected_at')->nullable();
            $table->timestamps();
            $table->index(['expires_at', 'reuse_detected_at']);
        });

        if (Schema::hasTable('app_security_schema_migration_objects')) {
            DB::table('app_security_schema_migration_objects')->insert([
                'table_name' => 'app_security_refresh_token_history',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $tracking = 'app_security_schema_migration_objects';
        if (!Schema::hasTable($tracking)
            || !DB::table($tracking)->where('table_name', 'app_security_refresh_token_history')->exists()) return;

        if (Schema::hasTable('app_security_refresh_token_history')
            && DB::table('app_security_refresh_token_history')->exists()) {
            throw new RuntimeException('Refresh-token reuse history exists; archive it before rolling back this migration.');
        }

        Schema::dropIfExists('app_security_refresh_token_history');
        DB::table($tracking)->where('table_name', 'app_security_refresh_token_history')->delete();
    }
};
