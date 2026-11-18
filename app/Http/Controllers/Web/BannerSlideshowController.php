<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\BannerSlideshowTransitions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BannerSlideshowController extends Controller
{
    private const SETTING_SLUG = 'home-banner-slideshow';
    private const UPLOAD_DIR = 'mobile-tool/banner-slides';

    public function edit()
    {
        $this->authorize('banner.manage');

        $config = $this->config();
        $oldSlides = old('slides');
        $config['slides'] = is_array($oldSlides) ? $oldSlides : $config['slides'];

        return view('admin.mobile_tool.banner_slides', [
            'config' => $config,
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('banner.manage');

        $validated = $request->validate([
            'slides' => ['present', 'array', 'max:20'],
            'slides.*.id' => ['nullable', 'string', 'max:80'],
            'slides.*.imageUrl' => ['nullable', 'string', 'max:2048'],
            'slides.*.linkUrl' => ['nullable', 'string', 'max:2048'],
            'slides.*.title' => ['nullable', 'string', 'max:180'],
            'slides.*.enabled' => ['required', 'boolean'],
            'slides.*.image' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:10240'],
            'intervalSeconds' => ['required', 'integer', 'between:2,30'],
            'autoplay' => ['required', 'boolean'],
            'showIndicators' => ['required', 'boolean'],
            'pauseOnTouch' => ['required', 'boolean'],
            'loop' => ['required', 'boolean'],
            'transition' => ['required', Rule::in(array_keys(BannerSlideshowTransitions::OPTIONS))],
        ]);

        $previous = $this->config();
        $previousById = collect($previous['slides'])->keyBy('id');
        $slides = [];

        foreach ($validated['slides'] as $index => $slide) {
            $file = $request->file("slides.{$index}.image");
            $previousImage = (string) ($previousById->get($slide['id'])['imageUrl'] ?? '');
            $finalImage = $slide['imageUrl'] ?? null;

            if ($file) {
                $path = $file->store(self::UPLOAD_DIR, 'public');
                $finalImage = url(Storage::disk('public')->url($path));
                $this->removeStoredImage($previousImage, $finalImage);
            } elseif ($previousImage !== ($finalImage ?? '')) {
                $this->removeStoredImage($previousImage, $finalImage);
            }

            $slides[] = [
                'id' => $slide['id'] ?: 'slide_'.bin2hex(random_bytes(8)),
                'imageUrl' => $finalImage,
                'linkUrl' => $slide['linkUrl'] ?? null,
                'title' => $slide['title'] ?? null,
                'enabled' => (bool) $slide['enabled'],
            ];
        }

        $keptIds = array_column($slides, 'id');
        foreach ($previous['slides'] as $old) {
            if (!in_array($old['id'] ?? '', $keptIds, true)) {
                $this->removeStoredImage((string) ($old['imageUrl'] ?? ''), null);
            }
        }

        $config = [
            'slides' => $slides,
            'intervalSeconds' => $validated['intervalSeconds'],
            'autoplay' => (bool) $validated['autoplay'],
            'showIndicators' => (bool) $validated['showIndicators'],
            'pauseOnTouch' => (bool) $validated['pauseOnTouch'],
            'loop' => (bool) $validated['loop'],
            'transition' => $validated['transition'],
        ];

        DB::transaction(function () use ($config): void {
            AppSetting::query()->updateOrCreate(
                ['slug' => self::SETTING_SLUG],
                ['name' => 'Home Banner Slideshow', 'value' => json_encode($config, JSON_THROW_ON_ERROR), 'status' => 1]
            );
        });

        return redirect()->route('admin.mobile-tools.banner-slides')->with('success', 'Banner slideshow saved.');
    }

    public function reset()
    {
        $this->authorize('banner.manage');

        $config = $this->config();
        foreach ($config['slides'] as $slide) {
            $this->removeStoredImage((string) ($slide['imageUrl'] ?? ''), null);
        }
        AppSetting::query()->where('slug', self::SETTING_SLUG)->delete();

        return redirect()->route('admin.mobile-tools.banner-slides')->with('success', 'Banner slideshow reset to defaults.');
    }

    private function config(): array
    {
        $value = AppSetting::query()->where('slug', self::SETTING_SLUG)->value('value');
        $config = is_string($value) ? json_decode($value, true) : null;

        return is_array($config) ? array_merge(self::defaults(), $config) : self::defaults();
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

    /**
     * Delete an image previously stored under our upload directory unless it is
     * still referenced by the final configuration.
     */
    private function removeStoredImage(?string $current, ?string $keep): void
    {
        if (!$current || $current === $keep) return;
        $path = parse_url($current, PHP_URL_PATH);
        if (!is_string($path) || !preg_match('~/storage/(mobile-tool/banner-slides/[a-zA-Z0-9._/-]+)$~', $path, $match)) return;
        $relative = $match[1];
        if (str_contains($relative, '..') || $current !== url(Storage::disk('public')->url($relative))) return;
        rescue(function () use ($relative): void {
            Storage::disk('public')->delete($relative);
        }, report: true);
    }
}
