<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('permission_groups')) {
            return;
        }

        $webTypeId = DB::table('permission_group_types')->where('slug', 'web')->value('id');
        if (!$webTypeId) {
            $webTypeId = DB::table('permission_group_types')->insertGetId([
                'name' => 'Web',
                'slug' => 'web',
            ]);
        }

        $groups = [
            'Store Phone Number' => [
                'list_store_phone' => 'List Store Phone Numbers',
                'create_store_phone' => 'Create Store Phone Number',
                'edit_store_phone' => 'Edit Store Phone Number',
                'delete_store_phone' => 'Delete Store Phone Number',
            ],
            'App Links & Accounts' => [
                'list_app_link' => 'List App Links & Social Media Accounts',
                'create_app_link' => 'Create App Link',
                'edit_app_link' => 'Edit App Link',
                'delete_app_link' => 'Delete App Link',
            ],
            'Company Documents' => [
                'list_company_document' => 'List Company Enterprise Documents',
                'create_company_document' => 'Create Company Document',
                'edit_company_document' => 'Edit Company Document',
                'delete_company_document' => 'Delete Company Document',
            ],
            'Payroll Queue' => [
                'view_payroll_queue' => 'View Payroll Queue & Sessions',
                'manage_payroll_queue' => 'Manage Payroll Queue Sessions & Counters',
                'call_payroll_queue' => 'Call Next Ticket & Serve at Counter',
                'disburse_payroll_queue' => 'Disburse Payroll Payment',
            ],
        ];

        $createdPermissionIds = [];
        $permissionMap = [];

        foreach ($groups as $groupName => $permissionList) {
            $groupId = DB::table('permission_groups')->where('name', $groupName)->value('id');
            if (!$groupId) {
                $groupId = DB::table('permission_groups')->insertGetId([
                    'name' => $groupName,
                    'group_type_id' => $webTypeId,
                ]);
            }

            foreach ($permissionList as $key => $name) {
                $existing = DB::table('permissions')->where('permission_key', $key)->first();
                if (!$existing) {
                    $pId = DB::table('permissions')->insertGetId([
                        'name' => $name,
                        'permission_key' => $key,
                        'permission_groups_id' => $groupId,
                    ]);
                } else {
                    $pId = $existing->id;
                    DB::table('permissions')->where('id', $pId)->update([
                        'name' => $name,
                        'permission_groups_id' => $groupId,
                    ]);
                }
                $createdPermissionIds[] = $pId;
                $permissionMap[$key] = $pId;
            }
        }

        if (Schema::hasTable('permission_roles')) {
            $adminRoleIds = DB::table('roles')
                ->whereIn('slug', ['admin', 'super-admin', 'supper-admin', 'administrator'])
                ->pluck('id')->all();

            // Fallback: If no slug matched, find role with ID 1
            if (empty($adminRoleIds) && DB::table('roles')->where('id', 1)->exists()) {
                $adminRoleIds = [1];
            }

            $inserts = [];
            foreach ($createdPermissionIds as $pId) {
                foreach ($adminRoleIds as $rId) {
                    $inserts[] = [
                        'permission_id' => $pId,
                        'role_id' => $rId,
                    ];
                }
            }

            // Also inherit based on existing related permissions for non-admin roles:
            // 1. Roles with edit_assets inherit list_store_phone, create_store_phone, edit_store_phone
            $assetRoleIds = DB::table('permission_roles')
                ->join('permissions', 'permissions.id', '=', 'permission_roles.permission_id')
                ->where('permissions.permission_key', 'edit_assets')
                ->pluck('permission_roles.role_id')->unique()->all();
            foreach ($assetRoleIds as $rId) {
                foreach (['list_store_phone', 'create_store_phone', 'edit_store_phone'] as $k) {
                    if (isset($permissionMap[$k])) {
                        $inserts[] = ['permission_id' => $permissionMap[$k], 'role_id' => $rId];
                    }
                }
            }

            // 2. Roles with view_company inherit list_company_document
            $companyRoleIds = DB::table('permission_roles')
                ->join('permissions', 'permissions.id', '=', 'permission_roles.permission_id')
                ->where('permissions.permission_key', 'view_company')
                ->pluck('permission_roles.role_id')->unique()->all();
            foreach ($companyRoleIds as $rId) {
                if (isset($permissionMap['list_company_document'])) {
                    $inserts[] = ['permission_id' => $permissionMap['list_company_document'], 'role_id' => $rId];
                }
            }

            // 3. Roles with view_payroll_list inherit view_payroll_queue and call_payroll_queue
            $payrollRoleIds = DB::table('permission_roles')
                ->join('permissions', 'permissions.id', '=', 'permission_roles.permission_id')
                ->whereIn('permissions.permission_key', ['view_payroll_list', 'view_monthly_payroll'])
                ->pluck('permission_roles.role_id')->unique()->all();
            foreach ($payrollRoleIds as $rId) {
                foreach (['view_payroll_queue', 'call_payroll_queue'] as $k) {
                    if (isset($permissionMap[$k])) {
                        $inserts[] = ['permission_id' => $permissionMap[$k], 'role_id' => $rId];
                    }
                }
            }

            // 4. Roles with payroll_payment inherit disburse_payroll_queue
            $disburseRoleIds = DB::table('permission_roles')
                ->join('permissions', 'permissions.id', '=', 'permission_roles.permission_id')
                ->where('permissions.permission_key', 'payroll_payment')
                ->pluck('permission_roles.role_id')->unique()->all();
            foreach ($disburseRoleIds as $rId) {
                if (isset($permissionMap['disburse_payroll_queue'])) {
                    $inserts[] = ['permission_id' => $permissionMap['disburse_payroll_queue'], 'role_id' => $rId];
                }
            }

            if (!empty($inserts)) {
                DB::table('permission_roles')->insertOrIgnore($inserts);
            }
        }
    }

    public function down(): void
    {
        $keys = [
            'list_store_phone', 'create_store_phone', 'edit_store_phone', 'delete_store_phone',
            'list_app_link', 'create_app_link', 'edit_app_link', 'delete_app_link',
            'list_company_document', 'create_company_document', 'edit_company_document', 'delete_company_document',
            'view_payroll_queue', 'manage_payroll_queue', 'call_payroll_queue', 'disburse_payroll_queue',
        ];

        $ids = DB::table('permissions')->whereIn('permission_key', $keys)->pluck('id');
        if ($ids->isNotEmpty()) {
            DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
        }

        $groupNames = ['Store Phone Number', 'App Links & Accounts', 'Company Documents', 'Payroll Queue'];
        $groupIds = DB::table('permission_groups')->whereIn('name', $groupNames)->pluck('id');
        if ($groupIds->isNotEmpty()) {
            $occupied = DB::table('permissions')->whereIn('permission_groups_id', $groupIds)->pluck('permission_groups_id');
            $emptyGroupIds = $groupIds->diff($occupied);
            if ($emptyGroupIds->isNotEmpty()) {
                DB::table('permission_groups')->whereIn('id', $emptyGroupIds)->delete();
            }
        }
    }
};
