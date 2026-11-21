<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'notifications';
    private const MARKER = 'column:notifications.security_event_key';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE) || !Schema::hasTable('app_security_schema_migration_objects')
            || Schema::hasColumn(self::TABLE, 'security_event_key')) return;
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $table->string('security_event_key', 191)->nullable()->unique('notifications_security_event_key_unique');
        });
        if (!DB::table('app_security_schema_migration_objects')->where('table_name', self::MARKER)->exists()) {
            DB::table('app_security_schema_migration_objects')->insert([
                'table_name' => self::MARKER, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE) || !Schema::hasTable('app_security_schema_migration_objects')
            || !DB::table('app_security_schema_migration_objects')->where('table_name', self::MARKER)->exists()) return;
        if (DB::table(self::TABLE)->whereNotNull('security_event_key')->exists()) {
            throw new RuntimeException('App Security user notification history exists; archive it before rolling back this migration.');
        }
        Schema::table(self::TABLE, fn (Blueprint $table) => $table->dropUnique('notifications_security_event_key_unique'));
        Schema::table(self::TABLE, fn (Blueprint $table) => $table->dropColumn('security_event_key'));
        DB::table('app_security_schema_migration_objects')->where('table_name', self::MARKER)->delete();
    }
};
