<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            [
                'name' => 'Monthly Attendance',
                'permission_key' => 'list_monthly_attendance',
                'permission_groups_id' => 7,
                'source_permission_key' => 'list_attendance',
            ],
            [
                'name' => 'Monthly Attendance CSV Export',
                'permission_key' => 'monthly_attendance_csv_export',
                'permission_groups_id' => 7,
                'source_permission_key' => 'attendance_csv_export',
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $permission['permission_key']],
                [
                    'name' => $permission['name'],
                    'permission_key' => $permission['permission_key'],
                    'permission_groups_id' => $permission['permission_groups_id'],
                ]
            );

            $newPermissionId = DB::table('permissions')
                ->where('permission_key', $permission['permission_key'])
                ->value('id');
            $sourcePermissionId = DB::table('permissions')
                ->where('permission_key', $permission['source_permission_key'])
                ->value('id');

            if (!$newPermissionId || !$sourcePermissionId) {
                continue;
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
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('permission_key', ['list_monthly_attendance', 'monthly_attendance_csv_export'])
            ->pluck('id');

        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
