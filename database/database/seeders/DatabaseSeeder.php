<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            RoleTableSeeder::class,
            PermissionSeeder::class,
            CompanySeeder::class,
            UserTableSeeder::class,
            AppSettingSeeder::class,
            GeneralSettingSeeder::class,
            LeaveTypeSeeder::class,
            FeatureSeeder::class,
        ]);
    }
}
