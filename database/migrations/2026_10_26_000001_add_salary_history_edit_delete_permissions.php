<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $groupId = DB::table('permissions')->where('permission_key', 'employee.salary.history.manage')
            ->value('permission_groups_id') ?: 5;
        foreach (['edit' => 'Edit Employee Salary History', 'delete' => 'Delete Employee Salary History'] as $action => $name) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => 'employee.salary.history.'.$action],
                ['name' => $name, 'permission_groups_id' => $groupId]
            );
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('permission_key', [
            'employee.salary.history.edit', 'employee.salary.history.delete',
        ])->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
