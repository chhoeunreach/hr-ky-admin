<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $group = DB::table('permissions')->where('permission_key', 'view_payroll_list')->value('permission_groups_id') ?: 30;
        foreach (['view_monthly_payroll' => 'View Monthly Payroll', 'export_monthly_payroll' => 'Export Monthly Payroll'] as $key => $name) {
            DB::table('permissions')->updateOrInsert(['permission_key' => $key], ['name' => $name, 'permission_groups_id' => $group]);
        }
    }
    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('permission_key', ['view_monthly_payroll', 'export_monthly_payroll'])->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
