<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('permissions')->where('permission_key', 'list_employee')->value('permission_groups_id');

        DB::table('permissions')->updateOrInsert(
            ['permission_key' => 'd_card_print'],
            [
                'name' => 'D Card Print',
                'permission_groups_id' => $groupId,
            ]
        );
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('permission_key', 'd_card_print')->value('id');

        if (!$permissionId) {
            return;
        }

        DB::table('permission_roles')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
    }
};
