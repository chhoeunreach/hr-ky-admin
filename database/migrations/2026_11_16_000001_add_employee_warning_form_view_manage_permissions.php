<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('permissions')->where('permission_key', 'list_employee')->value('permission_groups_id') ?: 5;
        $permissions = [
            'employee.warning_form.view' => 'Employee Staff Warning Form View',
            'employee.warning_form.manage' => 'Employee Staff Warning Form Manage',
        ];

        foreach ($permissions as $key => $name) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['name' => $name, 'permission_groups_id' => $groupId]
            );
        }

        $permissionIds = DB::table('permissions')->whereIn('permission_key', array_keys($permissions))->pluck('id', 'permission_key');
        $roleIdsFor = static function (string $permissionKey): array {
            return DB::table('permission_roles')
                ->join('permissions', 'permissions.id', '=', 'permission_roles.permission_id')
                ->where('permissions.permission_key', $permissionKey)
                ->pluck('permission_roles.role_id')->all();
        };

        foreach ($roleIdsFor('employee.discipline.view') as $roleId) {
            DB::table('permission_roles')->updateOrInsert([
                'permission_id' => $permissionIds['employee.warning_form.view'],
                'role_id' => $roleId,
            ]);
        }

        foreach ($roleIdsFor('employee.discipline.manage') as $roleId) {
            foreach (['employee.warning_form.view', 'employee.warning_form.manage'] as $key) {
                DB::table('permission_roles')->updateOrInsert([
                    'permission_id' => $permissionIds[$key],
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('permission_key', ['employee.warning_form.view', 'employee.warning_form.manage'])
            ->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
