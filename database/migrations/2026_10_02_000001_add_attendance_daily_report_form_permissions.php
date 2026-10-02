<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('permissions')
            ->where('permission_key', 'list_employee')
            ->value('permission_groups_id') ?: 5;

        $permissionNames = [
            'employee.attendance_daily_report.view' => 'Employee Attendance Daily Report View',
            'employee.attendance_daily_report.print' => 'Employee Attendance Daily Report Print',
            'employee.attendance_daily_report.export' => 'Employee Attendance Daily Report Export Word',
        ];

        foreach ($permissionNames as $key => $name) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['name' => $name, 'permission_groups_id' => $groupId]
            );
        }

        $sourcePermissionIds = DB::table('permissions')
            ->whereIn('permission_key', [
                'list_attendance',
                'attendance_report',
                'employee.profile.view',
                'list_monthly_attendance',
            ])
            ->pluck('id');

        $roleIds = DB::table('permission_roles')
            ->whereIn('permission_id', $sourcePermissionIds)
            ->pluck('role_id')
            ->unique();

        $newPermissionIds = DB::table('permissions')
            ->whereIn('permission_key', array_keys($permissionNames))
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
                'employee.attendance_daily_report.view',
                'employee.attendance_daily_report.print',
                'employee.attendance_daily_report.export',
            ])
            ->pluck('id');

        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
