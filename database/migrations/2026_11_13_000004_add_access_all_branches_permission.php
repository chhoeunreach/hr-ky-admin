<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSION_KEY = 'access_all_branches';

    public function up(): void
    {
        $webTypeId = DB::table('permission_group_types')->where('slug', 'web')->value('id');

        $groupId = DB::table('permission_groups')
            ->where('name', 'Branch')
            ->where('group_type_id', $webTypeId)
            ->value('id');

        if (!$groupId) {
            $groupId = DB::table('permission_groups')->insertGetId([
                'name' => 'Branch',
                'group_type_id' => $webTypeId,
            ]);
        }

        DB::table('permissions')->updateOrInsert(
            ['permission_key' => self::PERMISSION_KEY],
            [
                'name' => 'Access All Branches',
                'permission_key' => self::PERMISSION_KEY,
                'permission_groups_id' => $groupId,
            ]
        );
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('permission_key', self::PERMISSION_KEY)
            ->value('id');

        if ($permissionId) {
            DB::table('permission_roles')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
