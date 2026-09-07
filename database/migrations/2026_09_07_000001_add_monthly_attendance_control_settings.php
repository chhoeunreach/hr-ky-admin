<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'name' => 'Monthly Attendance Bonus Amount',
                'slug' => 'monthly_attendance_bonus_amount',
                'value' => 20,
                'values' => null,
                'status' => 1,
            ],
            [
                'name' => 'Require Check In',
                'slug' => 'monthly_attendance_require_check_in',
                'value' => null,
                'values' => null,
                'status' => 1,
            ],
            [
                'name' => 'Require Check Out',
                'slug' => 'monthly_attendance_require_check_out',
                'value' => null,
                'values' => null,
                'status' => 1,
            ],
            [
                'name' => 'Control Late Check In',
                'slug' => 'monthly_attendance_require_no_late_check_in',
                'value' => null,
                'values' => null,
                'status' => 1,
            ],
            [
                'name' => 'Control Check Out Before Time Out',
                'slug' => 'monthly_attendance_require_no_early_check_out',
                'value' => null,
                'values' => null,
                'status' => 1,
            ],
            [
                'name' => 'Control Check In Before Time Start',
                'slug' => 'monthly_attendance_require_no_early_check_in',
                'value' => null,
                'values' => null,
                'status' => 1,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('attendance_settings')->updateOrInsert(
                ['slug' => $setting['slug']],
                $setting + ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('attendance_settings')
            ->whereIn('slug', [
                'monthly_attendance_bonus_amount',
                'monthly_attendance_require_check_in',
                'monthly_attendance_require_check_out',
                'monthly_attendance_require_no_late_check_in',
                'monthly_attendance_require_no_early_check_out',
                'monthly_attendance_require_no_early_check_in',
            ])
            ->delete();
    }
};
