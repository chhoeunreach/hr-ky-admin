<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('permissions')
            ->where('permission_key', 'list_attendance')
            ->value('permission_groups_id') ?: 7;

        $permissions = [
            [
                'name' => 'Attendance Logs',
                'permission_key' => 'list_attendance_log',
                'permission_groups_id' => $groupId,
            ],
            [
                'name' => 'Delete Attendance Log',
                'permission_key' => 'delete_attendance_log',
                'permission_groups_id' => $groupId,
            ],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $perm['permission_key']],
                $perm
            );
        }

        $sourcePermissionId = DB::table('permissions')
            ->where('permission_key', 'list_attendance')
            ->value('id');

        if (!$sourcePermissionId) {
            return;
        }

        $roleIds = DB::table('permission_roles')
            ->where('permission_id', $sourcePermissionId)
            ->pluck('role_id');

        foreach ($permissions as $perm) {
            $newPermissionId = DB::table('permissions')
                ->where('permission_key', $perm['permission_key'])
                ->value('id');

            if ($newPermissionId) {
                foreach ($roleIds as $roleId) {
                    DB::table('permission_roles')->updateOrInsert([
                        'permission_id' => $newPermissionId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $keys = ['list_attendance_log', 'delete_attendance_log'];
        $permissionIds = DB::table('permissions')
            ->whereIn('permission_key', $keys)
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }
};
