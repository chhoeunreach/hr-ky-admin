<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ThemeModeController extends Controller
{
    public const SETTING_SLUG = 'app-theme-modes';

    public function index()
    {
        $this->authorizePermission();

        $themes = $this->config();
        $roles = Role::query()->where('is_active', 1)->orderBy('name')->get(['id', 'name', 'slug']);

        return view('admin.mobile_tool.theme_modes', [
            'themes' => $themes,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission();

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:60'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'card_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6,8}$/'],
            'text_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_dark' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['string'],
        ]);

        $themes = $this->config();
        $key = strtolower(trim($validated['key']));

        if (isset($themes[$key])) {
            return redirect()->route('admin.mobile-tools.theme-modes')
                ->with('danger', "Theme mode with key '{$key}' already exists.")
                ->withInput();
        }

        $themes[$key] = [
            'key' => $key,
            'name' => trim($validated['name']),
            'description' => trim($validated['description'] ?? ''),
            'icon' => trim($validated['icon'] ?? 'palette'),
            'primary_color' => strtoupper($validated['primary_color']),
            'secondary_color' => strtoupper($validated['secondary_color']),
            'accent_color' => strtoupper($validated['accent_color']),
            'background_color' => strtoupper($validated['background_color']),
            'card_color' => strtoupper($validated['card_color']),
            'text_color' => strtoupper($validated['text_color']),
            'is_dark' => $request->boolean('is_dark'),
            'is_active' => $request->boolean('is_active', true),
            'allowed_roles' => array_values(array_filter((array) ($validated['allowed_roles'] ?? []))),
            'gradient_colors' => [
                strtoupper($validated['background_color']),
                strtoupper($validated['primary_color']),
                strtoupper($validated['secondary_color']),
            ],
            'is_system' => false,
        ];

        $this->saveConfig($themes);

        return redirect()->route('admin.mobile-tools.theme-modes')
            ->with('success', "Theme mode '{$themes[$key]['name']}' created successfully.");
    }

    public function update(Request $request, string $key)
    {
        $this->authorizePermission();

        $themes = $this->config();
        if (!isset($themes[$key])) {
            return redirect()->route('admin.mobile-tools.theme-modes')
                ->with('danger', "Theme mode '{$key}' not found.");
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:60'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'card_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6,8}$/'],
            'text_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_dark' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['string'],
        ]);

        $existing = $themes[$key];
        $themes[$key] = array_merge($existing, [
            'name' => trim($validated['name']),
            'description' => trim($validated['description'] ?? ''),
            'icon' => trim($validated['icon'] ?? $existing['icon'] ?? 'palette'),
            'primary_color' => strtoupper($validated['primary_color']),
            'secondary_color' => strtoupper($validated['secondary_color']),
            'accent_color' => strtoupper($validated['accent_color']),
            'background_color' => strtoupper($validated['background_color']),
            'card_color' => strtoupper($validated['card_color']),
            'text_color' => strtoupper($validated['text_color']),
            'is_dark' => $request->boolean('is_dark'),
            'is_active' => $request->boolean('is_active', true),
            'allowed_roles' => array_values(array_filter((array) ($validated['allowed_roles'] ?? []))),
            'gradient_colors' => [
                strtoupper($validated['background_color']),
                strtoupper($validated['primary_color']),
                strtoupper($validated['secondary_color']),
            ],
        ]);

        $this->saveConfig($themes);

        return redirect()->route('admin.mobile-tools.theme-modes')
            ->with('success', "Theme mode '{$themes[$key]['name']}' updated successfully.");
    }

    public function toggle(string $key)
    {
        $this->authorizePermission();

        $themes = $this->config();
        if (!isset($themes[$key])) {
            return response()->json(['success' => false, 'message' => "Theme '{$key}' not found."], 404);
        }

        $themes[$key]['is_active'] = !($themes[$key]['is_active'] ?? true);
        $this->saveConfig($themes);

        $statusText = $themes[$key]['is_active'] ? 'shown in mobile app' : 'hidden from mobile app';

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $themes[$key]['is_active'],
                'message' => "Theme '{$themes[$key]['name']}' is now {$statusText}.",
            ]);
        }

        return redirect()->route('admin.mobile-tools.theme-modes')
            ->with('success', "Theme '{$themes[$key]['name']}' is now {$statusText}.");
    }

    public function destroy(string $key)
    {
        $this->authorizePermission();

        $themes = $this->config();
        if (!isset($themes[$key])) {
            return redirect()->route('admin.mobile-tools.theme-modes')
                ->with('danger', "Theme mode '{$key}' not found.");
        }

        if (!empty($themes[$key]['is_system'])) {
            // System themes are reset to default colors and hidden if user tries to delete
            $themes[$key]['is_active'] = false;
            $this->saveConfig($themes);

            return redirect()->route('admin.mobile-tools.theme-modes')
                ->with('success', "Default theme '{$themes[$key]['name']}' cannot be deleted, but has been hidden.");
        }

        $name = $themes[$key]['name'];
        unset($themes[$key]);
        $this->saveConfig($themes);

        return redirect()->route('admin.mobile-tools.theme-modes')
            ->with('success', "Custom theme mode '{$name}' deleted successfully.");
    }

    public function reset()
    {
        $this->authorizePermission();

        AppSetting::query()->where('slug', self::SETTING_SLUG)->delete();

        return redirect()->route('admin.mobile-tools.theme-modes')
            ->with('success', 'All theme modes and colors reset to original defaults.');
    }

    public function resetTheme(string $key)
    {
        $this->authorizePermission();

        $defaults = self::defaults();
        if (!isset($defaults[$key])) {
            return redirect()->route('admin.mobile-tools.theme-modes')
                ->with('danger', "Core system theme '{$key}' cannot be reset because it is not a default core theme.");
        }

        $themes = $this->config();
        $themes[$key] = $defaults[$key];
        $this->saveConfig($themes);

        return redirect()->route('admin.mobile-tools.theme-modes')
            ->with('success', "Core theme '{$defaults[$key]['name']}' has been reset to factory baseline defaults.");
    }

    private function authorizePermission(): void
    {
        if (Gate::has('appearance.manage') && Gate::allows('appearance.manage')) {
            return;
        }
        if (Gate::has('theme_setting') && Gate::allows('theme_setting')) {
            return;
        }
        $this->authorize('appearance.manage');
    }

    public function config(): array
    {
        $value = AppSetting::query()->where('slug', self::SETTING_SLUG)->value('value');
        $stored = is_string($value) ? json_decode($value, true) : null;
        $defaults = self::defaults();

        if (!is_array($stored)) {
            return $defaults;
        }

        // Merge stored overrides with defaults, preserving new system themes and custom additions
        $merged = [];
        foreach ($defaults as $k => $def) {
            $merged[$k] = isset($stored[$k]) && is_array($stored[$k])
                ? array_merge($def, $stored[$k])
                : $def;
        }
        foreach ($stored as $k => $item) {
            if (!isset($merged[$k]) && is_array($item)) {
                $merged[$k] = $item;
            }
        }

        return $merged;
    }

    private function saveConfig(array $themes): void
    {
        DB::transaction(function () use ($themes): void {
            AppSetting::query()->updateOrCreate(
                ['slug' => self::SETTING_SLUG],
                ['name' => 'App Theme Modes', 'value' => json_encode($themes, JSON_THROW_ON_ERROR), 'status' => 1]
            );
        });
    }

    public static function defaults(): array
    {
        return [
            'royal_blue_gold' => [
                'key' => 'royal_blue_gold',
                'name' => 'Royal Blue & Gold',
                'description' => 'Angkor Wat, royal blue and metallic gold',
                'icon' => 'account_balance_rounded',
                'primary_color' => '#0756C8',
                'secondary_color' => '#082D68',
                'accent_color' => '#FFC857',
                'background_color' => '#061A40',
                'card_color' => '#0B3975',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#061A40', '#0756C8', '#082D68', '#061A40'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'royal_blue_day' => [
                'key' => 'royal_blue_day',
                'name' => 'Royal Blue Gold Day',
                'description' => 'Daylight blue and gold with Khmer architecture',
                'icon' => 'wb_sunny_outlined',
                'primary_color' => '#0D47A1',
                'secondary_color' => '#1976D2',
                'accent_color' => '#FFC107',
                'background_color' => '#E3F2FD',
                'card_color' => '#FFFFFF',
                'text_color' => '#102A56',
                'is_dark' => false,
                'gradient_colors' => ['#9CCFF8', '#E3F2FD', '#FFFFFF', '#E3F2FD'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'royal_blue_gold_khmer_day' => [
                'key' => 'royal_blue_gold_khmer_day',
                'name' => 'Royal Blue & Gold Khmer Luxury Day',
                'description' => 'Khmer ornaments, royal blue and metallic gold in daylight',
                'icon' => 'account_balance_rounded',
                'primary_color' => '#0056C7',
                'secondary_color' => '#003B91',
                'accent_color' => '#FFC83D',
                'background_color' => '#EAF6FF',
                'card_color' => '#FFFFFF',
                'text_color' => '#09234F',
                'is_dark' => false,
                'gradient_colors' => ['#80C8FF', '#D6ECFF', '#F5FAFF', '#DDEEFF'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'light' => [
                'key' => 'light',
                'name' => 'Original Mode',
                'description' => 'Crisp light with electric blue',
                'icon' => 'light_mode',
                'primary_color' => '#0B5FFF',
                'secondary_color' => '#0A1F44',
                'accent_color' => '#0284C7',
                'background_color' => '#F5F7FB',
                'card_color' => '#FFFFFF',
                'text_color' => '#0F172A',
                'is_dark' => false,
                'gradient_colors' => ['#F3F7FD', '#E2EBFA', '#CDDDF6'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'dark' => [
                'key' => 'dark',
                'name' => 'Dark Mode',
                'description' => 'Original dark mode',
                'icon' => 'dark_mode',
                'primary_color' => '#3B82F6',
                'secondary_color' => '#1E3A8A',
                'accent_color' => '#60A5FA',
                'background_color' => '#020617',
                'card_color' => '#0F172A',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#020617', '#0F172A', '#1E3A8A'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'ky_enterprise' => [
                'key' => 'ky_enterprise',
                'name' => 'KY Enterprise',
                'description' => 'Corporate navy with gold accents',
                'icon' => 'business_center',
                'primary_color' => '#0047AB',
                'secondary_color' => '#0A254F',
                'accent_color' => '#FBBF24',
                'background_color' => '#041C3C',
                'card_color' => '#0A254F',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#041C3C', '#0047AB', '#0A254F'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'neo_glass' => [
                'key' => 'neo_glass',
                'name' => 'Neo Glass',
                'description' => 'Glass-like violet and blue finish',
                'icon' => 'blur_on',
                'primary_color' => '#7C3AED',
                'secondary_color' => '#2563EB',
                'accent_color' => '#38BDF8',
                'background_color' => '#120B35',
                'card_color' => '#1FFFFFFF',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#120B35', '#312E81', '#7C3AED'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'premium_gradient' => [
                'key' => 'premium_gradient',
                'name' => 'Premium Gradient',
                'description' => 'Vivid teal, cyan and royal blue premium surface',
                'icon' => 'gradient',
                'primary_color' => '#06B6D4',
                'secondary_color' => '#1D4ED8',
                'accent_color' => '#00F5D4',
                'background_color' => '#031926',
                'card_color' => '#082F49',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#031926', '#0E7490', '#06B6D4'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'amoled' => [
                'key' => 'amoled',
                'name' => 'AMOLED Mode',
                'description' => 'Pure black with green highlights',
                'icon' => 'contrast',
                'primary_color' => '#22C55E',
                'secondary_color' => '#064E3B',
                'accent_color' => '#4ADE80',
                'background_color' => '#000000',
                'card_color' => '#101010',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#000000', '#052E16', '#000000'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'sunset' => [
                'key' => 'sunset',
                'name' => 'Sunset Mode',
                'description' => 'Warm orange daylight palette',
                'icon' => 'wb_twilight',
                'primary_color' => '#FB923C',
                'secondary_color' => '#FDBA74',
                'accent_color' => '#F97316',
                'background_color' => '#FFF7ED',
                'card_color' => '#FFEDD5',
                'text_color' => '#431407',
                'is_dark' => false,
                'gradient_colors' => ['#FFF7ED', '#FED7AA', '#FB923C'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'ocean_blue' => [
                'key' => 'ocean_blue',
                'name' => 'Ocean Blue',
                'description' => 'Deep blue operational theme',
                'icon' => 'water',
                'primary_color' => '#0284C7',
                'secondary_color' => '#075985',
                'accent_color' => '#38BDF8',
                'background_color' => '#082F49',
                'card_color' => '#0C4A6E',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#082F49', '#075985', '#0284C7'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'forest_green' => [
                'key' => 'forest_green',
                'name' => 'Forest Green',
                'description' => 'Dark green with fresh accents',
                'icon' => 'forest',
                'primary_color' => '#16A34A',
                'secondary_color' => '#14532D',
                'accent_color' => '#4ADE80',
                'background_color' => '#052E16',
                'card_color' => '#064E3B',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#052E16', '#14532D', '#16A34A'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'royal_purple' => [
                'key' => 'royal_purple',
                'name' => 'Royal Purple',
                'description' => 'Rich violet dashboard feel',
                'icon' => 'diamond',
                'primary_color' => '#9333EA',
                'secondary_color' => '#581C87',
                'accent_color' => '#C084FC',
                'background_color' => '#2E1065',
                'card_color' => '#4C1D95',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#2E1065', '#581C87', '#9333EA'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'copper_brown' => [
                'key' => 'copper_brown',
                'name' => 'Copper Brown',
                'description' => 'Grounded copper and espresso tones',
                'icon' => 'local_fire_department',
                'primary_color' => '#C2410C',
                'secondary_color' => '#7C2D12',
                'accent_color' => '#FDBA74',
                'background_color' => '#431407',
                'card_color' => '#7C2D12',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#431407', '#7C2D12', '#C2410C'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'rose_pink' => [
                'key' => 'rose_pink',
                'name' => 'Rose Pink Love 💕',
                'description' => 'Premium rose gradients with soft glass highlights',
                'icon' => 'favorite',
                'primary_color' => '#E91E63',
                'secondary_color' => '#880E4F',
                'accent_color' => '#FF80AB',
                'background_color' => '#880E4F',
                'card_color' => '#A31250',
                'text_color' => '#FFFFFF',
                'is_dark' => true,
                'gradient_colors' => ['#880E4F', '#C2185B', '#E91E63', '#F06292', '#AD1457'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
            'khmer_heritage' => [
                'key' => 'khmer_heritage',
                'name' => 'Khmer Heritage',
                'description' => 'Burgundy, cream and gold inspired by Cambodian ornament',
                'icon' => 'account_balance_outlined',
                'primary_color' => '#8A2844',
                'secondary_color' => '#421226',
                'accent_color' => '#EDC478',
                'background_color' => '#250B17',
                'card_color' => '#4A192E',
                'text_color' => '#FFF2DC',
                'is_dark' => true,
                'gradient_colors' => ['#290C1A', '#641D37', '#320E20'],
                'is_active' => true,
                'allowed_roles' => [],
                'is_system' => true,
            ],
        ];
    }
}
