@php
    $defaultWallpaper = asset('assets/images/angkor_wat_banner.png');
    $wallpapers = [
        $defaultWallpaper => 'Angkor Wat Sunrise',
        'gradient:royal' => 'Dark Royal Navy Gradient',
        'gradient:sunset' => 'Sunset Horizon Gradient',
        'gradient:emerald' => 'Deep Emerald Gradient',
    ];
    $savedWallpaper = $bannerStyle['bannerImage'] ?? $defaultWallpaper;
    if (str_ends_with($savedWallpaper, '/assets/images/angkor_wat_banner.jpg')
        || str_ends_with($savedWallpaper, '/assets/images/angkor_wat_banner.png')) {
        $savedWallpaper = $defaultWallpaper;
    }
    $customWallpaper = !array_key_exists($savedWallpaper, $wallpapers);
    $overlayPercent = max(0, min(80, round(($bannerStyle['bannerOverlay'] ?? 0.35) * 100)));
@endphp
<fieldset class="border rounded-3 p-3 mt-3">
    <legend class="float-none w-auto px-1 fs-6 fw-bold">{{ __('index.banner_appearance') }}</legend>
    <div class="mb-3">
        <label class="form-label" for="banner_bg_{{ $scope }}">{{ __('index.banner_background_wallpaper') }}</label>
        <select class="form-select" id="banner_bg_{{ $scope }}" onchange="changeBannerBackground(@js($scope))">
            @foreach($wallpapers as $value => $label)
                <option value="{{ $value }}" @selected($savedWallpaper === $value)>{{ $label }}</option>
            @endforeach
            <option value="" @selected($customWallpaper)>{{ __('index.custom_image') }}</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="banner_custom_url_{{ $scope }}">{{ __('index.custom_image_url') }}</label>
        <input type="url" class="form-control" id="banner_custom_url_{{ $scope }}"
            value="{{ $customWallpaper ? $savedWallpaper : '' }}"
            placeholder="https://example.com/banner.jpg" oninput="changeBannerCustomUrl(@js($scope))">
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <label class="form-label mb-0" for="banner_overlay_slider_{{ $scope }}">{{ __('index.darkening_overlay_opacity') }}</label>
        <output id="overlay_val_{{ $scope }}" for="banner_overlay_slider_{{ $scope }}" class="text-primary fw-bold">{{ $overlayPercent }}%</output>
    </div>
    <input type="range" class="form-range" id="banner_overlay_slider_{{ $scope }}" min="0" max="80"
        value="{{ $overlayPercent }}" oninput="applyStyle(@js($scope))">
    <div class="form-text">{{ __('index.overlay_hint') }}</div>
</fieldset>
