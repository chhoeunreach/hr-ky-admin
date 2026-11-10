@once
    <link rel="stylesheet" href="{{ asset('assets/css/directory-table-tools.css') }}?v={{ filemtime(public_path('assets/css/directory-table-tools.css')) }}">
    <script defer src="{{ asset('assets/js/directory-workbook.js') }}?v=1"></script>
    <script defer src="{{ asset('assets/js/directory-table-tools.js') }}?v={{ filemtime(public_path('assets/js/directory-table-tools.js')) }}"></script>
@endonce
<div class="directory-list-heading">
    <div class="directory-title"><i data-feather="{{ $directoryIcon }}"></i><h6>{{ $directoryTitle }}</h6><span class="directory-count" data-directory-count="{{ $directoryTable }}">{{ number_format(method_exists($directoryRecords, 'total') ? $directoryRecords->total() : $directoryRecords->count()) }}</span></div>
    @if($directoryCanAdd ?? \Illuminate\Support\Facades\Gate::allows($directoryPermission))
        @if($directoryAddModal ?? false)
            <button type="button" class="directory-add border-0" data-bs-toggle="modal" data-bs-target="#{{ $directoryAddModal }}"><i data-feather="plus"></i>{{ __('index.add') }}</button>
        @else
            <a class="directory-add" href="{{ $directoryAddUrl }}" @if($directoryAddRequestModal ?? false) data-open-leave-request-form @endif><i data-feather="plus"></i>{{ __('index.add') }}</a>
        @endif
    @endif
</div>
<div class="directory-tools no-print" data-directory-table="{{ $directoryTable }}" data-directory-form="{{ $directoryForm }}" data-directory-live="{{ $directoryLive ? 'true' : 'false' }}" data-directory-search-key="{{ $directorySearchKey }}" data-directory-title="{{ $directoryTitle }}" data-directory-working="{{ __('index.loading') }}" data-directory-failed="{{ __('index.directory_export_failed') }}" data-directory-copied="{{ __('index.directory_copy_success') }}" data-directory-yes="{{ __('index.yes') }}" data-directory-no="{{ __('index.no') }}" data-directory-active="{{ __('index.active') }}" data-directory-inactive="{{ __('index.inactive') }}">
    <div class="directory-entry-control">
        <label for="{{ $directoryPageSizeId ?? 'per_page' }}">{{ __('index.show') }}</label>
        <select id="{{ $directoryPageSizeId ?? 'per_page' }}" name="per_page" form="{{ $directoryForm }}" class="form-select form-select-sm">
            @foreach(($directoryPageSizes ?? ($directoryDefaultSize === '10' ? [10,25,50,100,200,500,1000,'all'] : [25,50,100,200,500,1000,'all'])) as $size)
                <option value="{{ $size }}" {{ (string) ($directoryCurrentSize ?? request('per_page', $directoryDefaultSize)) === (string) $size ? 'selected' : '' }}>{{ $size === 'all' ? __('index.all') : number_format($size) }}</option>
            @endforeach
        </select><span>{{ __('index.entries') }}</span>
    </div>
    <div class="directory-export-actions">
        @foreach(['copy' => ['copy','copy'], 'csv' => ['file-text','export_csv'], 'excel' => ['file','export_excel'], 'print' => ['printer','print']] as $format => [$icon,$label])
            @if($format !== 'print' || ($directoryCanPrint ?? true))<button type="button" class="directory-tool-button" data-directory-export="{{ $format }}"><i data-feather="{{ $icon }}"></i>{{ __('index.'.$label) }}</button>@endif
        @endforeach
        <div class="dropdown">
            <button type="button" class="directory-tool-button dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="columns"></i>{{ __('index.column_visibility') }}</button>
            <div class="dropdown-menu directory-column-menu" data-bs-auto-close="outside">
                <div data-directory-columns></div><div class="dropdown-divider"></div>
                <button type="button" class="dropdown-item" data-directory-restore>{{ __('index.department_restore_columns') }}</button>
            </div>
        </div>
        @if($directoryCanPrint ?? true)
        <div class="dropdown">
            <button type="button" class="directory-tool-button dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="file-text"></i>{{ __('index.export_pdf') }}</button>
            <div class="dropdown-menu">
                @foreach(['portrait','landscape'] as $orientation)<button type="button" class="dropdown-item" data-directory-export="pdf" data-directory-orientation="{{ $orientation }}">{{ __('index.'.$orientation) }}</button>@endforeach
            </div>
        </div>
        @endif
    </div>
    <div class="directory-search">
        <label class="visually-hidden" for="{{ $directorySearch }}">{{ __('index.search') }}</label>
        <input type="search" id="{{ $directorySearch }}" @if($directorySearchInForm ?? false) name="{{ $directorySearchKey }}" form="{{ $directoryForm }}" @endif class="form-control" value="{{ request($directorySearchKey, '') }}" placeholder="{{ __('index.search') }} …" autocomplete="off">
    </div>
    <button type="button" class="directory-tool-button directory-refresh" data-directory-refresh aria-label="{{ __('index.refresh') }}" title="{{ __('index.refresh') }}"><i data-feather="refresh-cw"></i></button>
    <div class="directory-tools-status" data-directory-status role="status" aria-live="polite" hidden></div>
</div>
