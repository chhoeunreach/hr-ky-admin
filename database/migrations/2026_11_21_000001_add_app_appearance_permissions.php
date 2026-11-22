<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $keys = ['appearance.manage', 'appearance.view'];

    public function up(): void
    {
        $webType = DB::table('permission_group_types')->where('slug', 'web')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'Web', 'slug' => 'web']);
        $apiType = DB::table('permission_group_types')->where('slug', 'api')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'API', 'slug' => 'api']);
        $webGroup = DB::table('permission_groups')->where('name', 'App Appearance')->value('id')
            ?: DB::table('permission_groups')->insertGetId(['name' => 'App Appearance', 'group_type_id' => $webType]);
        $apiGroup = DB::table('permission_groups')->where('name', 'Mobile App Appearance')->value('id')
            ?: DB::table('permission_groups')->insertGetId(['name' => 'Mobile App Appearance', 'group_type_id' => $apiType]);

        foreach ([
            'appearance.manage' => ['Manage App Appearance', $webGroup],
            'appearance.view' => ['View App Appearance', $apiGroup],
        ] as $key => [$name, $groupId]) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['permission_key' => $key, 'name' => $name, 'permission_groups_id' => $groupId]
            );
        }

        $manageRoles = DB::table('permission_roles as assigned')
            ->join('permissions as permission', 'permission.id', '=', 'assigned.permission_id')
            ->whereIn('permission.permission_key', ['home_highlight.manage', 'app_setting'])
            ->pluck('assigned.role_id')->unique();
        $viewRoles = DB::table('permission_roles as assigned')
            ->join('permissions as permission', 'permission.id', '=', 'assigned.permission_id')
            ->where('permission.permission_key', 'mobile.menu.attendance')
            ->pluck('assigned.role_id')->unique();
        $ids = DB::table('permissions')->whereIn('permission_key', $this->keys)->pluck('id', 'permission_key');

        foreach ($manageRoles as $roleId) {
            DB::table('permission_roles')->insertOrIgnore(['permission_id' => $ids['appearance.manage'], 'role_id' => $roleId]);
        }
        foreach ($viewRoles as $roleId) {
            DB::table('permission_roles')->insertOrIgnore(['permission_id' => $ids['appearance.view'], 'role_id' => $roleId]);
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('permission_key', $this->keys)->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        $occupied = DB::table('permissions')->pluck('permission_groups_id');
        DB::table('permission_groups')->whereIn('name', ['App Appearance', 'Mobile App Appearance'])
            ->whereNotIn('id', $occupied)->delete();
    }
};
