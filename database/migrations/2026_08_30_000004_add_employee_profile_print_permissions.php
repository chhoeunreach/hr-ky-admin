<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('permissions')->where('permission_key', 'list_employee')->value('permission_groups_id');
        $permissions = [
            'employee.profile.print' => 'Employee Profile Directory Print',
            'employee.complete_form.print' => 'Employee Complete Form Print',
            'employee.warning_overview.print' => 'Employee Warning Overview Print',
            'employee.warning_form.print' => 'Employee Staff Warning Form Print',
            'employee.contract_form.print' => 'Employee Contract Form Print',
        ];

        foreach ($permissions as $key => $name) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['name' => $name, 'permission_groups_id' => $groupId]
            );
        }

        $sourcePermissionId = DB::table('permissions')->where('permission_key', 'show_detail_employee')->value('id');
        if (!$sourcePermissionId) {
            return;
        }

        $roleIds = DB::table('permission_roles')->where('permission_id', $sourcePermissionId)->pluck('role_id');
        $permissionIds = DB::table('permissions')->whereIn('permission_key', array_keys($permissions))->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
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
            'employee.profile.print',
            'employee.complete_form.print',
            'employee.warning_overview.print',
            'employee.warning_form.print',
            'employee.contract_form.print',
        ];
        $permissionIds = DB::table('permissions')->whereIn('permission_key', $permissionKeys)->pluck('id');

        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
