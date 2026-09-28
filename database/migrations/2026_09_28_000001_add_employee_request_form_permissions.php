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

        $forms = [
            'employee.leave_form' => [
                'name' => 'Employee Leave Request Form',
                'sources' => ['create_leave_request', 'leave_request_create', 'list_leave_request', 'access_admin_leave'],
            ],
            'employee.time_leave_form' => [
                'name' => 'Employee Time Leave Request Form',
                'sources' => ['create_time_leave_request', 'time_leave_list', 'access_admin_leave'],
            ],
            'employee.resignation_form' => [
                'name' => 'Employee Resignation Form',
                'sources' => ['add_resignation', 'employee.employment.view'],
            ],
        ];

        foreach ($forms as $prefix => $form) {
            $permissionNames = [
                "{$prefix}.view" => "{$form['name']} View",
                "{$prefix}.print" => "{$form['name']} Print",
                "{$prefix}.export" => "{$form['name']} Export Word",
            ];

            foreach ($permissionNames as $key => $name) {
                DB::table('permissions')->updateOrInsert(
                    ['permission_key' => $key],
                    ['name' => $name, 'permission_groups_id' => $groupId]
                );
            }

            $sourcePermissionIds = DB::table('permissions')
                ->whereIn('permission_key', $form['sources'])
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
    }

    public function down(): void
    {
        $permissionKeys = [
            'employee.leave_form.view',
            'employee.leave_form.print',
            'employee.leave_form.export',
            'employee.time_leave_form.view',
            'employee.time_leave_form.print',
            'employee.time_leave_form.export',
            'employee.resignation_form.view',
            'employee.resignation_form.print',
            'employee.resignation_form.export',
        ];
        $permissionIds = DB::table('permissions')->whereIn('permission_key', $permissionKeys)->pluck('id');

        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
