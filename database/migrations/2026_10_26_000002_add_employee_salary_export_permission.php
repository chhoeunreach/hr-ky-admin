<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(['permission_key' => 'export_salary'], [
            'name' => 'Export Employee Salary',
            'permission_groups_id' => DB::table('permissions')->where('permission_key', 'view_salary_list')->value('permission_groups_id') ?: 33,
        ]);
    }

    public function down(): void
    {
        $id = DB::table('permissions')->where('permission_key', 'export_salary')->value('id');
        if ($id) {
            DB::table('permission_roles')->where('permission_id', $id)->delete();
            DB::table('permissions')->where('id', $id)->delete();
        }
    }
};
