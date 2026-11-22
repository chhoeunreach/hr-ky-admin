<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;

class AppAppearanceApiController extends Controller
{
    private const SETTING_SLUG = 'app-appearance';

    public function show(): JsonResponse
    {
        return response()->json(['data' => $this->config()]);
    }

    private function config(): array
    {
        $value = AppSetting::query()->where('slug', self::SETTING_SLUG)->value('value');
        $config = is_string($value) ? json_decode($value, true) : null;

        return is_array($config) ? array_replace(self::defaults(), $config) : self::defaults();
    }

    private static function defaults(): array
    {
        return [
            'background_type' => 'none',
            'background_url' => null,
            'heat' => false,
            'snow' => false,
            'floating_hearts' => true,
            'rose_petals' => false,
            'heart_glow' => true,
            'sparkle_effects' => true,
            'emoji_effect' => false,
            'emoji_char' => '✨',
        ];
    }
}
