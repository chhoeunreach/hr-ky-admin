<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            [
                'name' => 'List Telegram Group',
                'permission_key' => 'list_telegram_group',
                'permission_groups_id' => 6,
            ],
            [
                'name' => 'Create Telegram Group',
                'permission_key' => 'create_telegram_group',
                'permission_groups_id' => 6,
            ],
            [
                'name' => 'Edit Telegram Group',
                'permission_key' => 'edit_telegram_group',
                'permission_groups_id' => 6,
            ],
            [
                'name' => 'Delete Telegram Group',
                'permission_key' => 'delete_telegram_group',
                'permission_groups_id' => 6,
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
            ->whereIn('permission_key', [
                'list_telegram_group',
                'create_telegram_group',
                'edit_telegram_group',
                'delete_telegram_group',
            ])
            ->delete();
    }
};
