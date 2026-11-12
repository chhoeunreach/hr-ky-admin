<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $keys = ['social_reward.view', 'social_reward.manage'];

    public function up(): void
    {
        $webType = DB::table('permission_group_types')->where('slug', 'web')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'Web', 'slug' => 'web']);

        $groupId = DB::table('permission_groups')->where('name', 'Social Media Marketing')->value('id')
            ?: DB::table('permission_groups')->insertGetId([
                'name' => 'Social Media Marketing',
                'group_type_id' => $webType,
            ]);

        foreach ([
            'social_reward.view' => 'View Social Media Marketing submissions',
            'social_reward.manage' => 'Manage Social Media Marketing submissions',
        ] as $key => $label) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['permission_key' => $key, 'name' => $label, 'permission_groups_id' => $groupId]
            );
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('permission_key', $this->keys)->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        $occupied = DB::table('permissions')->pluck('permission_groups_id');
        DB::table('permission_groups')->where('name', 'Social Media Marketing')
            ->whereNotIn('id', $occupied)->delete();
    }
};
