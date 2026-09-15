<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attendance_settings')->updateOrInsert(
            ['slug' => 'attendance_map'],
            [
                'name' => 'Attendance Map',
                'value' => null,
                'values' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('attendance_settings')
            ->where('slug', 'attendance_map')
            ->delete();
    }
};
