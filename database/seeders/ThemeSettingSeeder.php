<?php

namespace Database\Seeders;

use App\Models\ThemeSetting;
use Illuminate\Database\Seeder;

class ThemeSettingSeeder extends Seeder
{
    /**
     * Seed the application's default theme colors.
     *
     * @return void
     */
    public function run()
    {
        ThemeSetting::updateOrCreate(
            ['id' => 1],
            [
                'primary_color' => '#0B5FFF',
                'hover_color' => '#0A4FD6',
                'dark_primary_color' => '#5EB1FF',
                'dark_hover_color' => '#0B5FFF',
            ]
        );
    }
}
