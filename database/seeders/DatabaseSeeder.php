<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PermissionSeeder::class,
            RoleTableSeeder::class,
            PermissionRoleSeeder::class,
            AdminTableSeeder::class,
            CompanySeeder::class,
            BranchTableSeeder::class,
            DepartmentTableSeeder::class,
            PostTableSeeder::class,
            OfficeTimeTableSeeder::class,
            AppSettingSeeder::class,
            GeneralSettingSeeder::class,
            AttendanceSettingSeeder::class,
            LeaveTypeSeeder::class,
            FeatureSeeder::class,
            ThemeSettingSeeder::class,
            TelegramGroupSeeder::class,
        ]);
    }
}
