<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrations run before seeders on a fresh install, so the legacy
        // permission-group seed data may not exist yet. The relationship is
        // nullable and the canonical seeder will attach this permission to
        // the Attendance group after the base catalog is inserted.
        $attendanceGroupId = DB::table('permission_groups')
            ->where('id', 7)
            ->value('id');

        DB::table('permissions')->updateOrInsert(
            ['permission_key' => 'allow_attendance_without_checkout'],
            [
                'name' => 'Allow Attendance Without Checkout',
                'permission_groups_id' => $attendanceGroupId,
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
