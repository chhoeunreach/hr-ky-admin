<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionRoleSeeder extends Seeder
{
    public function run()
    {
        $employeePermissionKeys = [
            'view_profile',
            'allow_change_password',
            'update_profile',
            'show_profile_detail',
            'list_team_sheet',
            'check_in',
            'check_out',
            'leave_request_create',
            'query_create',
            'tada_create',
            'tada_update',
            'delete_tada_attachment',
            'edit_task_status',
            'toggle_checklist_status',
            'submit_comment',
            'comment_delete',
            'reply_delete',
            'project_detail',
            'client_detail',
            'allow_attendance',
            'create_nfc',
            'create_leave_request',
            'advance_salary_list',
            'add_advance_salary',
            'update_advance_salary_api',
            'time_leave_list',
            'create_time_leave_request',
            'add_resignation',
            'add_warning',
            'add_complaint',
        ];

        $allPermissionIds = Permission::pluck('id')->all();
        $employeePermissionIds = Permission::whereIn('permission_key', $employeePermissionKeys)->pluck('id')->all();

        DB::table('permission_roles')->truncate();

        $rows = [];

        foreach ($allPermissionIds as $permissionId) {
            $rows[] = [
                'permission_id' => $permissionId,
                'role_id' => 1,
            ];
        }

        foreach ($employeePermissionIds as $permissionId) {
            $rows[] = [
                'permission_id' => $permissionId,
                'role_id' => 2,
            ];
        }

        PermissionRole::insert($rows);
    }
}
