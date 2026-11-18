<?php

namespace App\Support;

use App\Models\AppSetting;

final class MobileQuotePlayback
{
    public const SLUG = 'mobile-quote-playback';

    public static function settings(): array
    {
        $value = AppSetting::query()->where('slug', self::SLUG)->value('value');
        $settings = is_string($value) ? json_decode($value, true) : null;

        return array_replace([
            'autoplay' => false,
            'intervalSeconds' => 5,
            'transition' => 'fade',
            'loop' => true,
            'pauseOnTouch' => true,
        ], is_array($settings) ? $settings : []);
    }
}
