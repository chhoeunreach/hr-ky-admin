<?php

use App\Models\AttendanceSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        AttendanceSetting::query()->firstOrCreate(
            ['slug' => 'attendance_location_radius'],
            [
                'name' => 'Attendance Location Radius',
                'value' => 100,
                'values' => null,
                'status' => 1,
            ]
        );
    }

    public function down(): void
    {
        AttendanceSetting::query()
            ->where('slug', 'attendance_location_radius')
            ->delete();
    }
};
