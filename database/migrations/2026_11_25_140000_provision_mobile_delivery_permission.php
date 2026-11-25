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

        $typeId = DB::table('permission_group_types')->where('slug', 'api')->value('id');
        if (!$typeId) {
            $typeId = DB::table('permission_group_types')->insertGetId([
                'name' => 'API',
                'slug' => 'api',
            ]);
        }

        // 1. Create or find Permission Group
        $groupName = 'Mobile Delivery Service';
        $groupId = DB::table('permission_groups')->where('name', $groupName)->value('id');
        if (!$groupId) {
            $groupId = DB::table('permission_groups')->insertGetId([
                'name' => $groupName,
                'group_type_id' => $typeId,
            ]);
        }

        // 2. Insert Permissions
        $permissionKey = 'mobile.menu.delivery';
        $perm = DB::table('permissions')->where('permission_key', $permissionKey)->first();
        if (!$perm) {
            $permId = DB::table('permissions')->insertGetId([
                'name' => 'Mobile Delivery Feature',
                'permission_key' => $permissionKey,
                'permission_groups_id' => $groupId,
            ]);
        } else {
            $permId = $perm->id;
        }

        // 3. Auto-grant to all active roles so existing users have access immediately
        if (Schema::hasTable('permission_roles')) {
            $allRoleIds = DB::table('roles')->pluck('id')->all();
            $inserts = [];
            foreach ($allRoleIds as $rId) {
                $inserts[] = [
                    'permission_id' => $permId,
                    'role_id' => $rId,
                ];
            }
            DB::table('permission_roles')->insertOrIgnore($inserts);
        }
    }

    public function down(): void
    {
        $perm = DB::table('permissions')->where('permission_key', 'mobile.menu.delivery')->first();
        if ($perm) {
            if (Schema::hasTable('permission_roles')) {
                DB::table('permission_roles')->where('permission_id', $perm->id)->delete();
            }
            DB::table('permissions')->where('id', $perm->id)->delete();
        }
        DB::table('permission_groups')->where('name', 'Mobile Delivery Service')->delete();
    }
};
