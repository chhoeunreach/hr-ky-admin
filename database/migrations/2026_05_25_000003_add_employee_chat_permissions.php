<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            [
                'name' => 'View Employee Chat',
                'permission_key' => 'view_employee_chat',
                'permission_groups_id' => 5,
            ],
            [
                'name' => 'Send Employee Chat',
                'permission_key' => 'send_employee_chat',
                'permission_groups_id' => 5,
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $permission['permission_key']],
                $permission
            );
        }
    }

    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('permission_key', ['view_employee_chat', 'send_employee_chat'])
            ->delete();
    }
};
