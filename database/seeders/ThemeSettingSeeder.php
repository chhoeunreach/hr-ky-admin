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
                'primary_color' => '#0F766E',
                'hover_color' => '#115E59',
                'dark_primary_color' => '#14B8A6',
                'dark_hover_color' => '#0F766E',
            ]
        );
    }
}
