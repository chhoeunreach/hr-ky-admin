<div class="directory-filter-heading">
    <button class="directory-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $directoryCollapse }}" aria-expanded="{{ $directoryExpanded ? 'true' : 'false' }}" aria-controls="{{ $directoryCollapse }}">
        <span><i data-feather="filter"></i><strong>{{ __('index.filters') }}</strong></span>
        <span class="directory-filter-state"><span data-directory-expand>{{ __('index.department_expand') }}</span><span data-directory-collapse>{{ __('index.department_collapse') }}</span><i data-feather="chevron-down"></i></span>
    </button>
</div>
