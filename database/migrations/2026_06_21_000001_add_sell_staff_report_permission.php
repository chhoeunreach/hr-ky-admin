<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['permission_key' => 'view_sell_staff_report'],
            [
                'name' => 'View Sell Staff Report',
                'permission_key' => 'view_sell_staff_report',
                'permission_groups_id' => 38,
            ]
        );

        $newPermissionId = DB::table('permissions')
            ->where('permission_key', 'view_sell_staff_report')
            ->value('id');
        $sourcePermissionId = DB::table('permissions')
            ->where('permission_key', 'view_tax_report')
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
            ->where('permission_key', 'view_sell_staff_report')
            ->value('id');

        if ($permissionId) {
            DB::table('permission_roles')->where('permission_id', $permissionId)->delete();
        }

        DB::table('permissions')
            ->where('permission_key', 'view_sell_staff_report')
            ->delete();
    }
};
