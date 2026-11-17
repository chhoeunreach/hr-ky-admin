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

        $permissions = [
            'employee.employment_certificate.view' => 'Employee Employment Certificate View',
            'employee.employment_certificate.print' => 'Employee Employment Certificate Print',
            'employee.employment_certificate.export' => 'Employee Employment Certificate Export Word',
        ];

        foreach ($permissions as $key => $name) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['name' => $name, 'permission_groups_id' => $groupId]
            );
        }

        $sourcePermissionIds = DB::table('permissions')
            ->whereIn('permission_key', [
                'employee.employment.view',
                'employee.salary.view',
                'view_salary_list',
                'list_employee',
                'employee.profile.view',
            ])
            ->pluck('id');

        $roleIds = DB::table('permission_roles')
            ->whereIn('permission_id', $sourcePermissionIds)
            ->pluck('role_id')
            ->unique()
            ->values()
            ->all();

        // Also ensure admin roles have them explicitly
        $adminRoleIds = DB::table('roles')
            ->whereIn('slug', ['admin', 'super-admin', 'supper-admin'])
            ->pluck('id')
            ->all();

        $allRoleIds = array_unique(array_merge($roleIds, $adminRoleIds));

        $newPermissionIds = DB::table('permissions')
            ->whereIn('permission_key', array_keys($permissions))
            ->pluck('id');

        foreach ($allRoleIds as $roleId) {
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
        $permissionKeys = [
            'employee.employment_certificate.view',
            'employee.employment_certificate.print',
            'employee.employment_certificate.export',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('permission_key', $permissionKeys)
            ->pluck('id');

        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
