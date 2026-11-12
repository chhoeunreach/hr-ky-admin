<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunityPermissionSeeder extends Seeder
{
    /**
     * Rebuild the mobile/community permission catalog after PermissionSeeder.
     *
     * PermissionSeeder intentionally truncates the catalog so a fresh install
     * starts from the canonical legacy list. Migrations run before seeders,
     * therefore this small idempotent pass restores the newer catalog after
     * that reset and before PermissionRoleSeeder grants admin permissions.
     */
    public function run(): void
    {
        $catalog = require config_path('mobile_permissions.php');
        $typeId = DB::table('permission_group_types')->where('slug', 'api')->value('id');
        if (!$typeId) {
            $typeId = DB::table('permission_group_types')->insertGetId([
                'name' => 'API',
                'slug' => 'api',
            ]);
        }

        $groups = [];
        foreach ($catalog['menus'] as $menu => $ability) {
            $name = 'Mobile '.ucwords(str_replace('-', ' ', $menu));
            $groupId = DB::table('permission_groups')->where('name', $name)->value('id');
            if (!$groupId) {
                $groupId = DB::table('permission_groups')->insertGetId([
                    'name' => $name,
                    'group_type_id' => $typeId,
                ]);
            }
            $groups[$menu] = $groupId;
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $ability],
                ['name' => $name.' Menu', 'permission_groups_id' => $groupId]
            );
        }

        foreach ($catalog['functions'] as $action => $rule) {
            if ($rule === null) {
                continue;
            }
            [$menu, $ability] = $rule;
            [$controller, $method] = explode('@', $action);
            $controller = preg_replace('/(?:Api)?Controller$/', '', $controller);
            $label = ucwords(preg_replace('/(?<!^)[A-Z]/', ' $0', $controller.' '.ucfirst($method)));
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $ability],
                [
                    'name' => 'Mobile '.ucwords(str_replace('-', ' ', $menu)).': '.$label,
                    'permission_groups_id' => $groups[$menu],
                ]
            );
        }

        // The web moderation permissions are created by the feature migration;
        // ensure they are present if a deployment seeds an older migration set.
        $webType = DB::table('permission_group_types')->where('slug', 'web')->value('id');
        if (!$webType) {
            $webType = DB::table('permission_group_types')->insertGetId([
                'name' => 'Web',
                'slug' => 'web',
            ]);
        }
        foreach ([
            ['Flash View', 'flash_view.manage', 'Manage Flash View Popups'],
            ['Social Feed', 'social_feed.moderate', 'Moderate Social Feed'],
        ] as [$name, $key, $label]) {
            $groupId = DB::table('permission_groups')->where('name', $name)->value('id');
            if (!$groupId) {
                $groupId = DB::table('permission_groups')->insertGetId([
                    'name' => $name,
                    'group_type_id' => $webType,
                ]);
            }
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['name' => $label, 'permission_groups_id' => $groupId]
            );
        }
    }
}
