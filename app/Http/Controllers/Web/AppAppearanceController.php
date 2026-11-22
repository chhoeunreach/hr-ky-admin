<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AppAppearanceController extends Controller
{
    private const SETTING_SLUG = 'app-appearance';
    private const UPLOAD_DIR = 'mobile-tool/app-appearance';

    public function edit()
    {
        $this->authorize('appearance.manage');

        return view('admin.mobile_tool.app_appearance', [
            'config' => $this->config(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('appearance.manage');

        $validated = $request->validate([
            'background_type' => ['required', Rule::in(['none', 'image', 'svg'])],
            'background' => ['nullable', 'file', 'mimes:jpeg,png,gif,webp,svg', 'max:10240'],
            'remove_background' => ['sometimes', 'boolean'],
            'heat' => ['sometimes', 'boolean'],
            'snow' => ['sometimes', 'boolean'],
            'floating_hearts' => ['sometimes', 'boolean'],
            'rose_petals' => ['sometimes', 'boolean'],
            'heart_glow' => ['sometimes', 'boolean'],
            'sparkle_effects' => ['sometimes', 'boolean'],
            'emoji_effect' => ['sometimes', 'boolean'],
            'emoji_char' => ['nullable', 'string', 'max:8'],
        ]);

        $config = $this->config();
        $config['background_type'] = $validated['background_type'];

        if ($request->boolean('remove_background')) {
            $this->removeStoredBackground((string) ($config['background_url'] ?? ''));
            $config['background_url'] = null;
        } elseif ($request->hasFile('background')) {
            $file = $request->file('background');
            $isSvg = $config['background_type'] === 'svg'
                || strtolower((string) $file->getClientOriginalExtension()) === 'svg';
            $path = $file->store(self::UPLOAD_DIR, 'public');
            $finalUrl = url(Storage::disk('public')->url($path));
            $this->removeStoredBackground((string) ($config['background_url'] ?? ''), $finalUrl);
            $config['background_url'] = $finalUrl;
            $config['background_type'] = $isSvg ? 'svg' : 'image';
        } elseif ($config['background_type'] === 'none') {
            $config['background_url'] = null;
        }

        foreach (['heat', 'snow', 'floating_hearts', 'rose_petals', 'heart_glow', 'sparkle_effects', 'emoji_effect'] as $flag) {
            $config[$flag] = $request->boolean($flag, (bool) ($config[$flag] ?? false));
        }
        $config['emoji_char'] = $request->filled('emoji_char')
            ? mb_substr(trim((string) $request->input('emoji_char')), 0, 8)
            : ((string) ($config['emoji_char'] ?? '✨') ?: '✨');

        if ($config['background_type'] !== 'none' && empty($config['background_url'])) {
            return redirect()->route('admin.mobile-tools.app-appearance')
                ->with('danger', 'Please upload a background file before selecting this type.');
        }

        $this->saveConfig($config);

        return redirect()->route('admin.mobile-tools.app-appearance')
            ->with('success', 'App appearance settings saved. Changes apply to all users on their next app launch.');
    }

    public function reset()
    {
        $this->authorize('appearance.manage');

        $config = $this->config();
        $this->removeStoredBackground((string) ($config['background_url'] ?? ''));
        AppSetting::query()->where('slug', self::SETTING_SLUG)->delete();

        return redirect()->route('admin.mobile-tools.app-appearance')
            ->with('success', 'App appearance reset to defaults.');
    }

    private function saveConfig(array $config): void
    {
        DB::transaction(function () use ($config): void {
            AppSetting::query()->updateOrCreate(
                ['slug' => self::SETTING_SLUG],
                ['name' => 'App Appearance', 'value' => json_encode($config, JSON_THROW_ON_ERROR), 'status' => 1]
            );
        });
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

    /**
     * Delete a background previously stored under our upload directory unless
     * it is still referenced by the final configuration.
     */
    private function removeStoredBackground(?string $current, ?string $keep = null): void
    {
        if (!$current || $current === $keep) return;
        $path = parse_url($current, PHP_URL_PATH);
        if (!is_string($path) || !preg_match('~/storage/(mobile-tool/app-appearance/[a-zA-Z0-9._/-]+)$~', $path, $match)) return;
        $relative = $match[1];
        if (str_contains($relative, '..') || $current !== url(Storage::disk('public')->url($relative))) return;
        rescue(function () use ($relative): void {
            Storage::disk('public')->delete($relative);
        }, report: true);
    }
}
