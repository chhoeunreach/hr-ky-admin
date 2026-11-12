<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('permission_key', [
                'social_feed.stories.view',
                'social_feed.stories.create',
                'social_feed.stories.delete',
            ])
            ->pluck('id', 'permission_key');
        $feedRoleIds = DB::table('permission_roles as assigned')
            ->join('permissions as feed_permission', 'feed_permission.id', '=', 'assigned.permission_id')
            ->where('feed_permission.permission_key', 'social_feed.view')
            ->pluck('assigned.role_id')
            ->unique();
        foreach ($feedRoleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_roles')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('permission_key', [
                'social_feed.stories.view',
                'social_feed.stories.create',
                'social_feed.stories.delete',
            ])
            ->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $permissionIds)->delete();
    }
};
