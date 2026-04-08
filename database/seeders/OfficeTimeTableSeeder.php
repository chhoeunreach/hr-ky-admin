<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeTimeTableSeeder extends Seeder
{
    public function run(): void
    {
        $officeTimeTemplates = [
            ['opening_time' => '08:30:00', 'closing_time' => '20:00:00', 'shift' => '8:30 AM - 8:00 PM'],
            ['opening_time' => '17:00:00', 'closing_time' => '23:59:59', 'shift' => '5:00 PM - 11:59 PM'],
            ['opening_time' => '08:00:00', 'closing_time' => '20:00:00', 'shift' => '8:00 AM - 8:00 PM'],
            ['opening_time' => '07:00:00', 'closing_time' => '19:00:00', 'shift' => '7:00 AM - 7:00 PM'],
            ['opening_time' => '08:00:00', 'closing_time' => '19:30:00', 'shift' => '8:00 AM - 7:30 PM'],
            ['opening_time' => '08:00:00', 'closing_time' => '19:00:00', 'shift' => '8:00 AM - 7:00 PM'],
            ['opening_time' => '08:30:00', 'closing_time' => '19:30:00', 'shift' => '8:30 AM - 7:30 PM'],
            ['opening_time' => '09:00:00', 'closing_time' => '20:00:00', 'shift' => '9:00 AM - 8:00 PM'],
            ['opening_time' => '09:00:00', 'closing_time' => '21:00:00', 'shift' => '9:00 AM - 9:00 PM'],
            ['opening_time' => '13:00:00', 'closing_time' => '19:00:00', 'shift' => '1:00 PM - 7:00 PM'],
            ['opening_time' => '09:00:00', 'closing_time' => '19:00:00', 'shift' => '9:00 AM - 7:00 PM'],
            ['opening_time' => '18:00:00', 'closing_time' => '23:00:00', 'shift' => '6:00 PM - 11:00 PM'],
            ['opening_time' => '13:00:00', 'closing_time' => '23:00:00', 'shift' => '1:00 PM - 11:00 PM'],
            ['opening_time' => '08:00:00', 'closing_time' => '17:00:00', 'shift' => '8:00 AM - 5:00 PM'],
            ['opening_time' => '08:00:00', 'closing_time' => '23:00:00', 'shift' => '8:00 AM - 11:00 PM'],
            ['opening_time' => '08:00:00', 'closing_time' => '18:00:00', 'shift' => '8:00 AM - 6:00 PM'],
            ['opening_time' => '18:00:00', 'closing_time' => '12:00:00', 'shift' => '6:00 PM - 12:00 PM'],
            ['opening_time' => '08:30:00', 'closing_time' => '21:00:00', 'shift' => '8:30 AM - 9:00 PM'],
            ['opening_time' => '08:30:00', 'closing_time' => '17:00:00', 'shift' => '8:30 AM - 5:00 PM'],
            ['opening_time' => '14:00:00', 'closing_time' => '20:00:00', 'shift' => '2:00 PM - 8:00 PM'],
            ['opening_time' => '08:00:00', 'closing_time' => '12:00:00', 'shift' => '8:00 AM - 12:00 PM'],
            ['opening_time' => '08:00:00', 'closing_time' => '14:00:00', 'shift' => '8:00 AM - 2:00 PM'],
            ['opening_time' => '13:00:00', 'closing_time' => '20:00:00', 'shift' => '1:00 PM - 8:00 PM'],
            ['opening_time' => '12:00:00', 'closing_time' => '20:00:00', 'shift' => '12:00 PM - 8:00 PM'],
            ['opening_time' => '06:00:00', 'closing_time' => '23:00:00', 'shift' => '6:00 AM - 11:00 PM'],
            ['opening_time' => '09:30:00', 'closing_time' => '20:00:00', 'shift' => '9:30 AM - 8:00 PM'],
            ['opening_time' => '18:00:00', 'closing_time' => '23:50:00', 'shift' => '6:00 PM - 11:50 PM'],
            ['opening_time' => '08:30:00', 'closing_time' => '23:00:00', 'shift' => '8:30 AM - 11:00 PM'],
            ['opening_time' => '12:30:00', 'closing_time' => '23:00:00', 'shift' => '12:30 PM - 11:00 PM'],
            ['opening_time' => '08:00:00', 'closing_time' => '13:00:00', 'shift' => '8:00 AM - 1:00 PM'],
            ['opening_time' => '09:00:00', 'closing_time' => '17:00:00', 'shift' => '9:00 AM - 5:00 PM'],
            ['opening_time' => '08:30:00', 'closing_time' => '20:30:00', 'shift' => '8:30 AM - 8:30 PM'],
            ['opening_time' => '14:00:00', 'closing_time' => '22:00:00', 'shift' => '2:00 PM - 10:00 PM'],
            ['opening_time' => '18:00:00', 'closing_time' => '23:30:00', 'shift' => '6:00 PM - 11:30 PM'],
            ['opening_time' => '08:30:00', 'closing_time' => '19:00:00', 'shift' => '8:30 AM - 7:00 PM'],
            ['opening_time' => '11:59:00', 'closing_time' => '20:00:00', 'shift' => '11:59 AM - 8:00 PM'],
            ['opening_time' => '18:30:00', 'closing_time' => '23:59:00', 'shift' => '6:30 PM - 11:59 PM'],
            ['opening_time' => '08:12:21', 'closing_time' => '20:00:00', 'shift' => '8:12 AM - 8:00 PM'],
            ['opening_time' => '12:00:00', 'closing_time' => '21:00:00', 'shift' => '12:00 PM - 9:00 PM'],
            ['opening_time' => '20:00:00', 'closing_time' => '18:00:00', 'shift' => '8:00 PM - 6:00 PM'],
            ['opening_time' => '20:00:00', 'closing_time' => '20:00:00', 'shift' => '8:00 PM - 8:00 PM'],
        ];

        $branches = DB::table('branches')->select('id', 'company_id')->get();
        $now = now();

        foreach ($branches as $branch) {
            foreach ($officeTimeTemplates as $template) {
                $exists = DB::table('office_times')
                    ->where('branch_id', $branch->id)
                    ->where('opening_time', $template['opening_time'])
                    ->where('closing_time', $template['closing_time'])
                    ->where('shift', $template['shift'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('office_times')->insert([
                    'opening_time' => $template['opening_time'],
                    'closing_time' => $template['closing_time'],
                    'shift' => $template['shift'],
                    'category' => 'full_timer',
                    'holiday_count' => 1,
                    'description' => null,
                    'company_id' => $branch->company_id,
                    'is_active' => 1,
                    'created_by' => null,
                    'updated_by' => null,
                    'branch_id' => $branch->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
