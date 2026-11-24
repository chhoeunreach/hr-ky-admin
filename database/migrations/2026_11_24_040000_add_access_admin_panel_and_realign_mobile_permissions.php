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

        $webTypeId = DB::table('permission_group_types')->where('slug', 'web')->value('id') ?? 1;

        // 1. Create or retrieve "Admin Portal Access" group
        $groupId = DB::table('permission_groups')->where('name', 'Admin Portal Access')->value('id');
        if (!$groupId) {
            $groupId = DB::table('permission_groups')->insertGetId([
                'name' => 'Admin Portal Access',
                'group_type_id' => $webTypeId,
            ]);
        }

        // 2. Create or update "access_admin_panel" permission
        $existing = DB::table('permissions')->where('permission_key', 'access_admin_panel')->first();
        if (!$existing) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'Access Web Admin Portal',
                'permission_key' => 'access_admin_panel',
                'permission_groups_id' => $groupId,
            ]);
        } else {
            $permissionId = $existing->id;
            DB::table('permissions')->where('id', $permissionId)->update([
                'name' => 'Access Web Admin Portal',
                'permission_groups_id' => $groupId,
            ]);
        }

        // 3. Grant access_admin_panel to roles that currently have backend_login_authorize = 1 or admin slugs
        if (Schema::hasTable('roles') && Schema::hasTable('permission_roles')) {
            $roles = DB::table('roles')->get();
            $eligibleRoleIds = [];

            foreach ($roles as $r) {
                $slug = strtolower(trim((string) $r->slug));
                $name = strtolower(trim((string) $r->name));
                $hasBackendAuth = (bool) ($r->backend_login_authorize ?? false);

                $isAdminSlug = in_array($slug, ['admin', 'super-admin', 'supper-admin', 'administrator', 'heard', 'hr', 'hr-assistance'], true)
                    || in_array($name, ['admin', 'super admin', 'administrator', 'hr', 'hr assistance'], true);

                if ($hasBackendAuth || $isAdminSlug || (int) $r->id === 1) {
                    $eligibleRoleIds[] = (int) $r->id;
                    // Ensure backend_login_authorize is 1
                    DB::table('roles')->where('id', $r->id)->update(['backend_login_authorize' => 1]);
                }
            }

            foreach ($eligibleRoleIds as $rId) {
                $alreadyGranted = DB::table('permission_roles')
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $rId)
                    ->exists();

                if (!$alreadyGranted) {
                    DB::table('permission_roles')->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $rId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $perm = DB::table('permissions')->where('permission_key', 'access_admin_panel')->first();
        if ($perm) {
            DB::table('permission_roles')->where('permission_id', $perm->id)->delete();
            DB::table('permissions')->where('id', $perm->id)->delete();
        }

        DB::table('permission_groups')->where('name', 'Admin Portal Access')->delete();
    }
};
