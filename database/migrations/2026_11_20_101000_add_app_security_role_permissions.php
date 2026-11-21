<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $permissions = [
        'app_security_view' => 'View App Security',
        'app_security_device_approval' => 'Approve Staff App Devices',
        'app_security_devices' => 'Manage App Security Devices',
        'app_security_releases' => 'Manage App Security Releases',
        'app_security_settings' => 'Manage App Security Settings',
        'app_security_audit' => 'View App Security Audit Logs',
        'app_security_customer_support' => 'View Customer App Device Status',
        'app_security_alert_ack' => 'Acknowledge App Security Alerts',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('permissions')) return;
        foreach ($this->permissions as $key => $name) {
            if (DB::table('permissions')->where('permission_key', $key)->exists()) continue;
            DB::table('permissions')->insert([
                'name' => $name,
                'permission_key' => $key,
                'permission_groups_id' => null,
            ]);
            if (Schema::hasTable('app_security_schema_migration_objects')
                && !DB::table('app_security_schema_migration_objects')->where('table_name', 'permission:'.$key)->exists()
            ) {
                DB::table('app_security_schema_migration_objects')->insert([
                    'table_name' => 'permission:'.$key, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) return;
        if (!Schema::hasTable('app_security_schema_migration_objects')) return;
        $createdKeys = DB::table('app_security_schema_migration_objects')
            ->whereIn('table_name', array_map(fn ($key) => 'permission:'.$key, array_keys($this->permissions)))
            ->pluck('table_name')->map(fn ($marker) => substr($marker, strlen('permission:')))->all();
        if ($createdKeys === []) return;
        $ids = DB::table('permissions')->whereIn('permission_key', $createdKeys)->pluck('id');
        if (Schema::hasTable('permission_roles')) {
            DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        }
        DB::table('permissions')->whereIn('id', $ids)->delete();
        DB::table('app_security_schema_migration_objects')
            ->whereIn('table_name', array_map(fn ($key) => 'permission:'.$key, $createdKeys))->delete();
    }
};
