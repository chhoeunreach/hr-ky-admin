<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $webType = DB::table('permission_group_types')->where('slug', 'web')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'Web', 'slug' => 'web']);

        // 1. Create dedicated "Certificate Management" group under Web
        $certGroupId = DB::table('permission_groups')->where('name', 'Certificate Management')->value('id');
        if (!$certGroupId) {
            $certGroupId = DB::table('permission_groups')->insertGetId([
                'name' => 'Certificate Management',
                'group_type_id' => $webType,
            ]);
        }

        // Move all certificate permissions to Certificate Management group
        $certKeys = [
            'certificate_list',
            'certificate_create',
            'certificate_edit',
            'certificate_delete',
            'certificate_download',
            'certificate_verify',
            'certificate_studio',
        ];

        DB::table('permissions')
            ->whereIn('permission_key', $certKeys)
            ->update(['permission_groups_id' => $certGroupId]);

        // 2. Ensure "Mobile Tool" group under Web contains both banner and quote management
        $mobileToolGroupId = DB::table('permission_groups')->where('name', 'Mobile Tool')->value('id');
        if (!$mobileToolGroupId) {
            $mobileToolGroupId = DB::table('permission_groups')->insertGetId([
                'name' => 'Mobile Tool',
                'group_type_id' => $webType,
            ]);
        }

        DB::table('permissions')
            ->whereIn('permission_key', ['banner.manage', 'quote.manage'])
            ->update(['permission_groups_id' => $mobileToolGroupId]);

        // Remove empty "Banner Manager" group if it exists and has no permissions
        $bannerManagerGroup = DB::table('permission_groups')->where('name', 'Banner Manager')->first();
        if ($bannerManagerGroup) {
            $hasPerms = DB::table('permissions')->where('permission_groups_id', $bannerManagerGroup->id)->exists();
            if (!$hasPerms) {
                DB::table('permission_groups')->where('id', $bannerManagerGroup->id)->delete();
            }
        }
    }

    public function down(): void
    {
        // Reversible if needed
    }
};
