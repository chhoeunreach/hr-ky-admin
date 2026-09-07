<?php

namespace Database\Seeders;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendanceSetting = [

            [
                'name'=> 'Attendance Note',
                'slug' => 'attendance_note',
                'value'=>null,
                'values'=>null,
                'status' => 0
            ],
            [
                'name' => 'Attendance Limit',
                'slug' => 'attendance_limit',
                'value' => 1,
                'values'=>null,
                'status' => 1,
            ],

            [
                'name' => 'Attendance Method',
                'slug' => 'attendance_method',
                'value'=>1,
                'values' => json_encode(['default', 'qr']),
                'status' => 1,
//                'description' => 'Note: for wifi=> This setting will not affect field type users. Those type of users will still be able to perform check in checkout via mobile app.'
            ],
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

        foreach ($attendanceSetting as $setting) {
            DB::table('attendance_settings')->updateOrInsert(
                ['slug' => $setting['slug']],
                $setting
            );
        }
    }

}
