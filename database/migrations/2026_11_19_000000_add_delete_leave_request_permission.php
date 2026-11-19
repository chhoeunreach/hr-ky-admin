<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $group = DB::table('permission_groups')->where('name', 'Leave')->first();

        DB::table('permissions')->insertOrIgnore([
            'name' => 'Delete Leave Request',
            'permission_key' => 'delete_leave_request',
            'permission_groups_id' => $group->id ?? 8,
        ]);

        $permission = DB::table('permissions')->where('permission_key', 'delete_leave_request')->first();

        if (!$permission) {
            return;
        }

        $sourcePermissionIds = DB::table('permissions')
            ->whereIn('permission_key', ['update_leave_request', 'access_admin_leave'])
            ->pluck('id');

        $roleIds = DB::table('permission_roles')
            ->whereIn('permission_id', $sourcePermissionIds)
            ->pluck('role_id')
            ->unique();

        $rows = $roleIds->map(fn ($roleId) => [
            'permission_id' => $permission->id,
            'role_id' => $roleId,
        ])->all();

        if ($rows !== []) {
            DB::table('permission_roles')->insertOrIgnore($rows);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission = DB::table('permissions')->where('permission_key', 'delete_leave_request')->first();

        if (!$permission) {
            return;
        }

        DB::table('permission_roles')->where('permission_id', $permission->id)->delete();
        DB::table('permissions')->where('id', $permission->id)->delete();
    }
};
