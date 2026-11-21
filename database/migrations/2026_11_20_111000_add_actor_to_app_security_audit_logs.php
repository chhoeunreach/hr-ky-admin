<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('app_security_audit_logs', 'actor_type')) {
            Schema::table('app_security_audit_logs', function (Blueprint $table) {
                $table->string('actor_type', 24)->nullable();
            });
        }

        if (!Schema::hasColumn('app_security_audit_logs', 'actor_id')) {
            Schema::table('app_security_audit_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('actor_id')->nullable();
            });
        }

        DB::table('app_security_audit_logs')
            ->whereNotNull('admin_id')->whereNull('actor_type')->whereNull('actor_id')
            ->update(['actor_type' => 'admin', 'actor_id' => DB::raw('admin_id')]);
    }

    public function down(): void
    {
        // Fresh installations already own these columns. Keep recorded actor identities.
    }
};
