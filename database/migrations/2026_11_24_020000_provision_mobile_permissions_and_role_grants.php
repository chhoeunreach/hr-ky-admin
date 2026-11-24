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

        $definitions = [
            'store-phone-numbers' => [
                'group' => 'Mobile Store Phones',
                'permission_key' => 'mobile.menu.store-phone-numbers',
                'name' => 'Mobile Store Phones & SIMs Menu',
            ],
            'company-documents' => [
                'group' => 'Mobile Company Documents',
                'permission_key' => 'mobile.menu.company-documents',
                'name' => 'Mobile Company Documents Menu',
            ],
            'payroll-queue' => [
                'group' => 'Mobile Payday Queue',
                'permission_key' => 'mobile.menu.payroll-queue',
                'name' => 'Mobile Payday Queue Menu',
            ],
            'certificate' => [
                'group' => 'Mobile Certificates',
                'permission_key' => 'mobile.menu.certificate',
                'name' => 'Mobile Certificates Menu',
            ],
        ];

        $allRoleIds = DB::table('roles')->pluck('id')->all();
        $permissionIdsToGrant = [];

        foreach ($definitions as $menuKey => $def) {
            $groupId = DB::table('permission_groups')->where('name', $def['group'])->value('id');
            if (!$groupId) {
                $groupId = DB::table('permission_groups')->insertGetId([
                    'name' => $def['group'],
                    'group_type_id' => $typeId,
                ]);
            }

            $perm = DB::table('permissions')->where('permission_key', $def['permission_key'])->first();
            if (!$perm) {
                $permId = DB::table('permissions')->insertGetId([
                    'name' => $def['name'],
                    'permission_key' => $def['permission_key'],
                    'permission_groups_id' => $groupId,
                ]);
            } else {
                $permId = $perm->id;
            }

            $permissionIdsToGrant[] = $permId;
        }

        // Also ensure certificate_list is granted to all active roles so users can view issued certificates
        $certificateListPermId = DB::table('permissions')->where('permission_key', 'certificate_list')->value('id');
        if ($certificateListPermId) {
            $permissionIdsToGrant[] = $certificateListPermId;
        }

        // Grant to all active roles
        if (Schema::hasTable('permission_roles')) {
            $inserts = [];
            foreach ($permissionIdsToGrant as $pId) {
                foreach ($allRoleIds as $rId) {
                    $inserts[] = [
                        'permission_id' => $pId,
                        'role_id' => $rId,
                    ];
                }
            }
            DB::table('permission_roles')->insertOrIgnore($inserts);
        }
    }

    public function down(): void
    {
        $keys = [
            'mobile.menu.store-phone-numbers',
            'mobile.menu.company-documents',
            'mobile.menu.payroll-queue',
            'mobile.menu.certificate',
        ];

        $ids = DB::table('permissions')->whereIn('permission_key', $keys)->pluck('id');
        if (Schema::hasTable('permission_roles')) {
            DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        }
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
