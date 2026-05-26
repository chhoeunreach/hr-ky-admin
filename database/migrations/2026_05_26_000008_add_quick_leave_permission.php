<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['permission_key' => 'quick_leave'],
            [
                'name' => 'Quick Leave',
                'permission_key' => 'quick_leave',
                'permission_groups_id' => 8,
            ]
        );
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('permission_key', 'quick_leave')
            ->delete();
    }
};
