<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            [
                'name' => 'Toggle Telegram Group Status',
                'permission_key' => 'toggle_telegram_group_status',
                'permission_groups_id' => 6,
            ],
            [
                'name' => 'Test Telegram Group',
                'permission_key' => 'test_telegram_group',
                'permission_groups_id' => 6,
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $permission['permission_key']],
                $permission
            );
        }

        $sourcePermissionId = DB::table('permissions')
            ->where('permission_key', 'edit_telegram_group')
            ->value('id');

        if (! $sourcePermissionId) {
            return;
        }

        $roleIds = DB::table('permission_roles')
            ->where('permission_id', $sourcePermissionId)
            ->pluck('role_id');

        $newPermissionIds = DB::table('permissions')
            ->whereIn('permission_key', [
                'toggle_telegram_group_status',
                'test_telegram_group',
            ])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($newPermissionIds as $permissionId) {
                DB::table('permission_roles')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('permission_key', [
                'toggle_telegram_group_status',
                'test_telegram_group',
            ])
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        }

        DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->delete();
    }
};
