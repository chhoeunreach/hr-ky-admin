<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocialMarketingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $webType = DB::table('permission_group_types')->where('slug', 'web')->value('id');
        if (!$webType) {
            $webType = DB::table('permission_group_types')->insertGetId([
                'name' => 'Web',
                'slug' => 'web',
            ]);
        }

        $groupId = DB::table('permission_groups')->where('name', 'Social Media Marketing')->value('id');
        if (!$groupId) {
            $groupId = DB::table('permission_groups')->insertGetId([
                'name' => 'Social Media Marketing',
                'group_type_id' => $webType,
            ]);
        }

        foreach ([
            'social_reward.view' => 'View Social Media Marketing submissions',
            'social_reward.manage' => 'Manage Social Media Marketing submissions',
        ] as $key => $label) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['permission_key' => $key, 'name' => $label, 'permission_groups_id' => $groupId]
            );
        }

        $feature = DB::table('features')->where('key', 'social-media-marketing')->first();
        if (!$feature) {
            DB::table('features')->insert([
                'group' => 'Additional',
                'name' => 'Social Media Marketing',
                'key' => 'social-media-marketing',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
