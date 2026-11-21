<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        if (DB::table('permissions')->where('permission_key', 'app_security_manage')->exists()) return;
        DB::table('permissions')->insert([
            'name' => 'Manage App Security',
            'permission_key' => 'app_security_manage',
            'permission_groups_id' => null,
        ]);
        if (Schema::hasTable('app_security_schema_migration_objects')) {
            DB::table('app_security_schema_migration_objects')->insert([
                'table_name' => 'permission:app_security_manage', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $tracking = 'app_security_schema_migration_objects';
        if (!Schema::hasTable($tracking) || !DB::table($tracking)->where('table_name', 'permission:app_security_manage')->exists()) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('permission_key', 'app_security_manage')
            ->value('id');
        if ($permissionId) {
            DB::table('permission_roles')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
        DB::table($tracking)->where('table_name', 'permission:app_security_manage')->delete();
    }
};
