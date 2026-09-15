<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['key' => 'attendance-map'],
            [
                'group' => 'Attendance',
                'name' => 'Attendance Map',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('features')
            ->where('key', 'attendance-map')
            ->delete();
    }
};
