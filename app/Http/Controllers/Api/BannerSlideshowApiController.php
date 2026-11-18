<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\BannerSlideshowTransitions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BannerSlideshowApiController extends Controller
{
    private const SETTING_SLUG = 'home-banner-slideshow';

    public function show(): JsonResponse
    {
        $value = AppSetting::query()->where('slug', self::SETTING_SLUG)->value('value');
        $config = is_string($value) ? json_decode($value, true) : null;

        return response()->json(['data' => is_array($config) ? $config : self::defaults()]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slides' => ['present', 'array', 'max:20'],
            'slides.*.id' => ['required', 'string', 'max:80'],
            'slides.*.imageUrl' => ['nullable', 'string', 'max:2048'],
            'slides.*.linkUrl' => ['nullable', 'string', 'max:2048'],
            'slides.*.title' => ['nullable', 'string', 'max:180'],
            'slides.*.enabled' => ['required', 'boolean'],
            'intervalSeconds' => ['required', 'integer', 'between:2,30'],
            'autoplay' => ['required', 'boolean'],
            'showIndicators' => ['required', 'boolean'],
            'pauseOnTouch' => ['required', 'boolean'],
            'loop' => ['required', 'boolean'],
            'transition' => ['required', Rule::in(array_keys(BannerSlideshowTransitions::OPTIONS))],
        ]);

        $config = [
            'slides' => array_values($validated['slides']),
            'intervalSeconds' => $validated['intervalSeconds'],
            'autoplay' => $validated['autoplay'],
            'showIndicators' => $validated['showIndicators'],
            'pauseOnTouch' => $validated['pauseOnTouch'],
            'loop' => $validated['loop'],
            'transition' => $validated['transition'],
        ];

        DB::transaction(function () use ($config): void {
            AppSetting::query()->updateOrCreate(
                ['slug' => self::SETTING_SLUG],
                ['name' => 'Home Banner Slideshow', 'value' => json_encode($config, JSON_THROW_ON_ERROR), 'status' => 1]
            );
        });

        return response()->json(['message' => 'Banner slideshow saved.', 'data' => $config]);
    }

    public function reset(): JsonResponse
    {
        AppSetting::query()->where('slug', self::SETTING_SLUG)->delete();

        return response()->json(['message' => 'Banner slideshow reset.', 'data' => self::defaults()]);
    }

    private static function defaults(): array
    {
        return [
            'slides' => [],
            'intervalSeconds' => 5,
            'autoplay' => true,
            'showIndicators' => true,
            'pauseOnTouch' => true,
            'loop' => true,
            'transition' => 'fade',
        ];
    }
}
