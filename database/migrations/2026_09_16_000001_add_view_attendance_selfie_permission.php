<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('permissions')
            ->where('permission_key', 'attendance_show')
            ->value('permission_groups_id') ?: 7;

        DB::table('permissions')->updateOrInsert(
            ['permission_key' => 'view_attendance_selfie'],
            [
                'name' => 'View Attendance Selfie',
                'permission_key' => 'view_attendance_selfie',
                'permission_groups_id' => $groupId,
            ]
        );

        $newPermissionId = DB::table('permissions')
            ->where('permission_key', 'view_attendance_selfie')
            ->value('id');
        $sourcePermissionId = DB::table('permissions')
            ->where('permission_key', 'attendance_show')
            ->value('id');

        if (!$newPermissionId || !$sourcePermissionId) {
            return;
        }

        $roleIds = DB::table('permission_roles')
            ->where('permission_id', $sourcePermissionId)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            DB::table('permission_roles')->updateOrInsert([
                'permission_id' => $newPermissionId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('permission_key', 'view_attendance_selfie')
            ->value('id');

        if (!$permissionId) {
            return;
        }

        DB::table('permission_roles')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
    }
};
