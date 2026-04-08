<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $generalSetting = [
            [
                'name' => 'Firebase Key',
                'type' => 'configuration',
                'key' => 'firebase_key',
                'value' => config('firebase.server_key') ?? "",
                'description' => 'Firebase key is needed to send notification in mobile.'
            ],
            [
                'name' => 'Set Number Of Days for local Push Notification',
                'type' => 'configuration',
                'key' => 'attendance_notify',
                'value' => "7",
                'description' => 'Setting no of days will automatically send the data of those days to the mobile application.Receiving this data on the mobile end will allow the mobile application to set local push notification for those dates. The local push notification will help employees remember to check in on time as well as to check out when the shift is about to end.'
            ],
            [
                'name' => 'Advance Salary Limit(%)',
                'type' => 'general',
                'key' => 'advance_salary_limit',
                'value' => "50",
                'description' => 'Set the maximum amount in percent a employee can request in advance based on gross salary.'
            ],
            [
                'name' => 'Employee Code Prefix',
                'type' => 'general',
                'key' => 'employee_code_prefix',
                'value' => "EMP",
                'description' => 'This prefix will be used to make employee code.'
            ],
            [
                'name' => 'Attendance Limit',
                'type' => 'general',
                'key' => 'attendance_limit',
                'value' => "2",
                'description' => 'attendance limit for checkin and checkout.'
            ],
            [
                'name' => 'Award Display Limit',
                'type' => 'general',
                'key' => 'award_display_limit',
                'value' => "14",
                'description' => 'award display limit in mobile app.'
            ],
        ];


        $existingKeys = DB::table('general_settings')->pluck('key')->toArray();


        $newSettings = array_filter($generalSetting, function ($setting) use ($existingKeys) {
            return !in_array($setting['key'], $existingKeys);
        });

        if (!empty($newSettings)) {
            DB::table('general_settings')->insert($newSettings);
        }

    }

}
