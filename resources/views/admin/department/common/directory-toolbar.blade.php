<div class="card-header department-list-heading">
    <div class="department-toolbar-title">
        <i class="department-list-icon" data-feather="layers"></i>
        <h6 class="mb-0">{{ __('index.department_all_title') }} <span class="badge bg-light text-secondary ms-1" id="departmentResultCount">{{ number_format($departments->total()) }}</span></h6>
    </div>
    @can('create_department')
        <a href="{{ route('admin.departments.create') }}" class="department-add-button"><i data-feather="plus"></i>{{ __('index.add') }}</a>
    @endcan
</div>
<div class="department-toolbar" id="departmentTableTools" data-export-url="{{ route('admin.departments.export') }}">
    <div class="department-toolbar-left">
        <div class="department-entry-control">
            <label for="per_page" class="text-muted">{{ __('index.show') }}</label>
            <select class="form-select" id="per_page" name="per_page" form="departmentFilterForm">
                @foreach([10, 25, 50, 100, 200, 500, 1000, 'all'] as $pageSize)
                    <option value="{{ $pageSize }}" {{ (string) ($filterParameters['per_page'] ?? 10) === (string) $pageSize ? 'selected' : '' }}>{{ $pageSize === 'all' ? __('index.all') : number_format($pageSize) }}</option>
                @endforeach
            </select>
            <span class="text-muted">{{ __('index.entries') }}</span>
        </div>
        <div class="department-export-group">
            <button type="button" class="department-tool-button" id="departmentCopy"><i data-feather="copy"></i>{{ __('index.copy') }}</button>
            <a class="department-tool-button" data-department-export="csv" href="{{ route('admin.departments.export', array_merge(request()->query(), ['format' => 'csv'])) }}"><i data-feather="file-text"></i>{{ __('index.export_csv') }}</a>
            <a class="department-tool-button" data-department-export="excel" href="{{ route('admin.departments.export', array_merge(request()->query(), ['format' => 'excel'])) }}"><i data-feather="file-text" class="text-success"></i>{{ __('index.export_excel') }}</a>
            <a class="department-tool-button" data-department-export="print" target="_blank" rel="noopener" href="{{ route('admin.departments.export', array_merge(request()->query(), ['format' => 'print'])) }}"><i data-feather="printer"></i>{{ __('index.print') }}</a>
            <div class="dropdown">
                <button type="button" class="department-tool-button dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><i data-feather="columns"></i>{{ __('index.column_visibility') }}</button>
                <div class="dropdown-menu department-column-menu">
                    @foreach(['id' => '#', 'name' => __('index.name'), 'head' => __('index.department_head'), 'employees' => __('index.total_employees'), 'address' => __('index.address'), 'branch' => __('index.branch_name'), 'status' => __('index.status')] as $columnKey => $columnLabel)
                        <label class="dropdown-item-text d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input m-0" data-department-column-toggle="{{ $columnKey }}" checked>{{ $columnLabel }}</label>
                    @endforeach
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item" id="departmentRestoreColumns">{{ __('index.department_restore_columns') }}</button>
                </div>
            </div>
            <div class="dropdown">
                <button type="button" class="department-tool-button dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="file-text" class="text-danger"></i>{{ __('index.export_pdf') }}</button>
                <div class="dropdown-menu">
                    @foreach(['portrait', 'landscape'] as $orientation)
                        <a class="dropdown-item" data-department-export="pdf" data-orientation="{{ $orientation }}" target="_blank" rel="noopener" href="{{ route('admin.departments.export', array_merge(request()->query(), ['format' => 'pdf', 'orientation' => $orientation])) }}">{{ __('index.'.$orientation) }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="department-search-control">
        <label class="visually-hidden" for="departmentListSearch">{{ __('index.search') }}</label>
        <input type="search" id="departmentListSearch" class="form-control" value="{{ $filterParameters['search'] ?? '' }}" placeholder="{{ __('index.department_search_placeholder') }}" autocomplete="off">
        <button type="button" id="departmentClearSearch" aria-label="{{ __('index.department_clear_search') }}" {{ filled($filterParameters['search'] ?? null) ? '' : 'hidden' }}>&times;</button>
    </div>
    <button type="button" class="department-tool-button department-reload" id="departmentReload" title="{{ __('index.refresh') }}" aria-label="{{ __('index.refresh') }}"><i data-feather="refresh-cw"></i></button>
</div>
<div class="department-tool-status small text-muted" id="departmentToolsStatus" role="status" aria-live="polite" hidden></div>
@php
    $departmentToolsMessages = ['copied' => __('index.department_copy_success'), 'failed' => __('index.copy_failed'), 'expand' => __('index.department_expand'), 'collapse' => __('index.department_collapse')];
@endphp
<script type="application/json" id="departmentToolsMessages">@json($departmentToolsMessages)</script>
<div class="department-result-meta" id="departmentResultMeta">
    <div class="d-flex flex-wrap gap-2">
        <span class="department-filter-chip">{{ __('index.department_employee_status_filter') }}: <strong>{{ ($filterParameters['employee_status'] ?? 'all') === 'all' ? __('index.all') : (($filterParameters['employee_status'] ?? '') === 'active' ? __('index.active') : __('index.inactive')) }}</strong></span>
        @if(($filterParameters['is_active'] ?? '') !== '' && $filterParameters['is_active'] !== null)
            <span class="department-filter-chip">{{ __('index.department_status_filter') }}: <strong>{{ (string) $filterParameters['is_active'] === '1' ? __('index.active') : __('index.inactive') }}</strong></span>
        @endif
        @if(filled($filterParameters['name'] ?? null))
            <span class="department-filter-chip">{{ __('index.name') }}: <strong>{{ $filterParameters['name'] }}</strong></span>
        @endif
    </div>
    <span class="text-muted small">{{ __('index.department_results_range', ['from' => number_format($departments->firstItem() ?? 0), 'to' => number_format($departments->lastItem() ?? 0), 'total' => number_format($departments->total())]) }}</span>
</div>
<div id="departmentListLoading" class="department-loading text-muted small" role="status" hidden>{{ __('index.loading') }}</div>
