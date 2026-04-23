<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['permission_key' => 'allow_attendance_without_checkout'],
            [
                'name' => 'Allow Attendance Without Checkout',
                'permission_groups_id' => 7,
            ]
        );
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('permission_key', 'allow_attendance_without_checkout')
            ->delete();
    }
};
