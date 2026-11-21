<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const KEY = 'app_security_audit_export';
    private const MARKER = 'permission:app_security_audit_export';

    public function up(): void
    {
        if (!Schema::hasTable('permissions')
            || DB::table('permissions')->where('permission_key', self::KEY)->exists()) return;

        DB::table('permissions')->insert([
            'name' => 'Export App Security Audit Logs',
            'permission_key' => self::KEY,
            'permission_groups_id' => null,
        ]);
        if (Schema::hasTable('app_security_schema_migration_objects')
            && !DB::table('app_security_schema_migration_objects')->where('table_name', self::MARKER)->exists()
        ) {
            DB::table('app_security_schema_migration_objects')->insert([
                'table_name' => self::MARKER,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('app_security_schema_migration_objects')) return;
        if (!DB::table('app_security_schema_migration_objects')->where('table_name', self::MARKER)->exists()) return;
        $id = DB::table('permissions')->where('permission_key', self::KEY)->value('id');
        if ($id && Schema::hasTable('permission_roles')) {
            DB::table('permission_roles')->where('permission_id', $id)->delete();
        }
        DB::table('permissions')->where('permission_key', self::KEY)->delete();
        DB::table('app_security_schema_migration_objects')->where('table_name', self::MARKER)->delete();
    }
};
