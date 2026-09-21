<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $webGroupTypeId = DB::table('permission_group_types')
            ->where('slug', 'web')
            ->value('id') ?: 1;

        DB::table('permission_groups')->updateOrInsert(
            ['name' => 'Repair Price'],
            [
                'name' => 'Repair Price',
                'group_type_id' => $webGroupTypeId,
            ]
        );

        $groupId = DB::table('permission_groups')
            ->where('name', 'Repair Price')
            ->value('id');

        $permissions = [
            'repair_price.view' => 'Repair Price View',
            'repair_price.view_cost' => 'Repair Price View Cost',
            'repair_price.create' => 'Repair Price Create',
            'repair_price.update' => 'Repair Price Update',
            'repair_price.delete' => 'Repair Price Delete',
            'repair_price.manage_brands' => 'Repair Price Manage Brands',
            'repair_price.manage_categories' => 'Repair Price Manage Categories',
            'repair_price.manage_devices' => 'Repair Price Manage Devices',
            'repair_price.manage_services' => 'Repair Price Manage Services',
            'repair_price.manage_prices' => 'Repair Price Manage Prices',
        ];

        foreach ($permissions as $key => $name) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                [
                    'name' => $name,
                    'permission_key' => $key,
                    'permission_groups_id' => $groupId,
                ]
            );
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('permission_key', array_keys($permissions))
            ->pluck('id');

        $adminRoleIds = DB::table('roles')
            ->whereIn('slug', ['admin', 'supper-admin'])
            ->orWhereIn('id', [1, 7])
            ->pluck('id')
            ->unique();

        foreach ($adminRoleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_roles')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionKeys = [
            'repair_price.view',
            'repair_price.view_cost',
            'repair_price.create',
            'repair_price.update',
            'repair_price.delete',
            'repair_price.manage_brands',
            'repair_price.manage_categories',
            'repair_price.manage_devices',
            'repair_price.manage_services',
            'repair_price.manage_prices',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('permission_key', $permissionKeys)
            ->pluck('id');

        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        DB::table('permission_groups')
            ->where('name', 'Repair Price')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('permissions')
                    ->whereColumn('permissions.permission_groups_id', 'permission_groups.id');
            })
            ->delete();
    }
};
