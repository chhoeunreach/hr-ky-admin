@once
<link rel="stylesheet" href="{{ asset('assets/css/asset-gallery.css') }}?v={{ filemtime(public_path('assets/css/asset-gallery.css')) }}">
<dialog id="assetImageViewer" class="asset-image-viewer" aria-labelledby="assetImageViewerTitle"
    data-empty="{{ __('index.asset_image_empty') }}" data-image-label="{{ __('index.asset_image') }}"
    data-zoom-label="{{ __('index.asset_image_zoom') }}" data-fit-label="{{ __('index.asset_image_fit') }}">
    <div class="asset-viewer-header">
        <div><h5 id="assetImageViewerTitle"></h5><span data-image-counter aria-live="polite"></span></div>
        <div class="asset-viewer-tools">
            <button type="button" data-image-zoom>{{ __('index.asset_image_zoom') }}</button>
            <a data-image-original target="_blank" rel="noopener">{{ __('index.asset_image_original') }} ↗</a>
            <a data-image-download download>{{ __('index.asset_image_download') }}</a>
            <button type="button" data-image-close aria-label="{{ __('index.close') }}">✕</button>
        </div>
    </div>
    <div class="asset-viewer-stage" data-image-stage>
        <img data-image-full alt="">
        <p data-image-error hidden role="alert">{{ __('index.asset_image_load_error') }}</p>
    </div>
    <div class="asset-viewer-footer">
        <button type="button" data-image-previous aria-label="{{ __('index.asset_image_previous') }}">← {{ __('index.asset_image_previous') }}</button>
        <span>{{ __('index.asset_image_keyboard') }}</span>
        <button type="button" data-image-next aria-label="{{ __('index.asset_image_next') }}">{{ __('index.asset_image_next') }} →</button>
    </div>
</dialog>
<script src="{{ asset('assets/js/asset-gallery.js') }}?v={{ filemtime(public_path('assets/js/asset-gallery.js')) }}" defer></script>
@endonce
