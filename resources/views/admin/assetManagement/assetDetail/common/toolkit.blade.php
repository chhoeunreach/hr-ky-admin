<link rel="stylesheet" href="{{ asset('assets/css/assets-toolkit.css') }}?v={{ filemtime(public_path('assets/css/assets-toolkit.css')) }}">
<div class="assets-toolkit" id="assetsToolkit" data-copy-success="{{ __('index.copied') }}" data-copy-error="{{ __('index.copy_failed') }}" data-export-error="{{ __('index.assets_export_error') }}">
    <div class="assets-toolkit-actions" role="group" aria-label="{{ __('index.assets_table_tools') }}">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-assets-copy><i data-feather="copy"></i> {{ __('index.copy') }}</button>
        @foreach(['csv' => 'export_csv', 'excel' => 'export_excel', 'word' => 'export_word'] as $format => $label)
            <a class="btn btn-outline-secondary btn-sm" data-assets-export="{{ $format }}" href="{{ request()->fullUrlWithQuery(['export' => $format, 'page' => null]) }}"><i data-feather="file-text"></i> {{ __('index.' . $label) }}</a>
        @endforeach
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="printer"></i> {{ __('index.print') }}</button>
            <div class="dropdown-menu">
                @foreach(['portrait', 'landscape'] as $orientation)
                    <a class="dropdown-item" data-open-asset-modal data-asset-modal-title="{{ __('index.print') }} — {{ __('index.' . $orientation) }}" data-assets-export="print" data-orientation="{{ $orientation }}" href="{{ request()->fullUrlWithQuery(['export' => 'print', 'orientation' => $orientation, 'page' => null]) }}">{{ __('index.' . $orientation) }}</a>
                @endforeach
            </div>
        </div>
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><i data-feather="columns"></i> {{ __('index.column_visibility') }}</button>
            <div class="dropdown-menu assets-column-menu" data-assets-columns></div>
        </div>
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="file-text"></i> {{ __('index.export_pdf') }}</button>
            <div class="dropdown-menu">
                @foreach(['landscape', 'portrait'] as $orientation)
                    <a class="dropdown-item" data-open-asset-modal data-asset-modal-title="{{ __('index.export_pdf') }} — {{ __('index.' . $orientation) }}" data-assets-export="pdf" data-orientation="{{ $orientation }}" href="{{ request()->fullUrlWithQuery(['export' => 'pdf', 'orientation' => $orientation, 'page' => null]) }}">{{ __('index.' . $orientation) }}</a>
                @endforeach
            </div>
        </div>
    </div>
    <form method="get" action="{{ route('admin.assets.index') }}" class="assets-toolkit-search" id="assetsToolkitSearch">
        @foreach(request()->except(['q', 'per_page', 'page', 'export', 'columns', 'orientation']) as $key => $value)
            @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
        @endforeach
        <label for="assetsRows">{{ __('index.rows') }}</label>
        <select class="form-select" id="assetsRows" name="per_page">
            @foreach(['10', '25', '50', '100', 'all'] as $size)
                <option value="{{ $size }}" @selected((string) $filterParameters['per_page'] === $size)>{{ $size === 'all' ? __('index.all') : $size }}</option>
            @endforeach
        </select>
        <label for="assetsSearch">{{ __('index.search') }}</label>
        <input type="search" class="form-control" id="assetsSearch" name="q" value="{{ $filterParameters['q'] }}" maxlength="200" placeholder="{{ __('index.assets_search_placeholder') }}">
        <button type="submit" class="btn btn-outline-primary btn-sm"><i data-feather="search"></i><span class="visually-hidden">{{ __('index.search') }}</span></button>
    </form>
</div>
<p class="assets-toolkit-status" role="status" aria-live="polite" data-assets-status></p>
