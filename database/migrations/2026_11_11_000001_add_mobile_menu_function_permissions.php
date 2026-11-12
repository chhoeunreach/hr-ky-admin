<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $catalog = require __DIR__.'/../../config/mobile_permissions.php';
        $typeId = DB::table('permission_group_types')->where('slug', 'api')->value('id');
        if (!$typeId) {
            $typeId = DB::table('permission_group_types')->insertGetId(['name' => 'API', 'slug' => 'api']);
        }
        $groups = [];
        foreach ($catalog['menus'] as $menu => $ability) {
            $name = 'Mobile '.ucwords(str_replace('-', ' ', $menu));
            $groupId = DB::table('permission_groups')->where('name', $name)->value('id');
            if (!$groupId) {
                $groupId = DB::table('permission_groups')->insertGetId(['name' => $name, 'group_type_id' => $typeId]);
            }
            $groups[$menu] = $groupId;
            DB::table('permissions')->insertOrIgnore([
                'permission_key' => $ability, 'name' => $name.' Menu', 'permission_groups_id' => $groupId,
            ]);
        }
        foreach ($catalog['functions'] as $action => $rule) {
            if ($rule === null) continue;
            [$menu, $ability] = $rule;
            [$controller, $method] = explode('@', $action);
            $controller = preg_replace('/(?:Api)?Controller$/', '', $controller);
            $label = ucwords(preg_replace('/(?<!^)[A-Z]/', ' $0', $controller.' '.ucfirst($method)));
            DB::table('permissions')->insertOrIgnore([
                'permission_key' => $ability, 'name' => 'Mobile '.ucwords(str_replace('-', ' ', $menu)).': '.$label,
                'permission_groups_id' => $groups[$menu],
            ]);
        }
        // Deliberately do not grant new permissions to employee roles automatically.
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->where('permission_key', 'like', 'mobile.%')->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        $occupiedGroups = DB::table('permissions')->pluck('permission_groups_id');
        DB::table('permission_groups')->where('name', 'like', 'Mobile %')
            ->whereNotIn('id', $occupiedGroups)->delete();
    }
};
