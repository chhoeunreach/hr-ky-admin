<?php

use App\Modules\DailyRecords\RecordAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function () {
            if (DB::table('permissions')->where('permission_key', RecordAccess::SUBMIT_REPORT)->exists()) {
                return;
            }

            $group = DB::table('permissions')->where('permission_key', RecordAccess::MANAGE)->value('permission_groups_id');
            DB::table('permissions')->insert([
                'name' => 'Submit Daily Reports (Current Day Only)',
                'permission_key' => RecordAccess::SUBMIT_REPORT,
                'permission_groups_id' => $group,
            ]);
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $permission = DB::table('permissions')->where('permission_key', RecordAccess::SUBMIT_REPORT)->value('id');
            if ($permission) {
                DB::table('permission_roles')->where('permission_id', $permission)->delete();
                DB::table('permissions')->where('id', $permission)->delete();
            }
        });
    }
};
