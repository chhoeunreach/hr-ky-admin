<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceMapUrlSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('attendance_settings')->updateOrInsert(
            ['slug' => 'attendance_map_url'],
            [
                'name' => 'Attendance Map URL',
                'slug' => 'attendance_map_url',
                'value' => null,
                'values' => null,
                'status' => 1,
            ]
        );
    }
}
