<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $groupId = DB::table('permissions')->where('permission_key', 'list_assets')->value('permission_groups_id')
            ?: DB::table('permission_groups')->where('name', 'Asset Management')->value('id');
        DB::table('permissions')->updateOrInsert(
            ['permission_key' => 'print_assets'],
            ['name' => 'Print Assets', 'permission_groups_id' => $groupId]
        );
    }

    public function down(): void
    {
        $id = DB::table('permissions')->where('permission_key', 'print_assets')->value('id');
        if ($id) {
            DB::table('permission_roles')->where('permission_id', $id)->delete();
            DB::table('permissions')->where('id', $id)->delete();
        }
    }
};
