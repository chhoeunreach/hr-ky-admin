@php
    $selectedBranches = collect((array) ($filterParameters['branch'] ?? []))->filter()->map(fn ($id) => (string) $id)->all();
    $departmentFilterCount = ((!isset(auth()->user()->branch_id) && count($selectedBranches)) ? 1 : 0)
        + (filled($filterParameters['name'] ?? null) ? 1 : 0)
        + (($filterParameters['is_active'] ?? '') !== '' && $filterParameters['is_active'] !== null ? 1 : 0)
        + (($filterParameters['employee_status'] ?? 'all') !== 'all' ? 1 : 0);
@endphp
<div class="card department-filter-card mb-3">
    <div class="card-header department-filter-heading">
        <button class="department-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#departmentFilterCollapse" aria-expanded="false" aria-controls="departmentFilterCollapse">
            <span class="d-flex align-items-center gap-2"><i data-feather="filter"></i><strong>{{ __('index.filters') }}</strong><span id="departmentAppliedFilterCount" class="badge bg-light text-secondary">{{ $departmentFilterCount }}</span></span>
            <span class="d-flex align-items-center gap-2"><small id="departmentFilterToggleLabel">{{ __('index.department_expand') }}</small><i data-feather="chevron-down"></i></span>
        </button>
    </div>
    <div id="departmentFilterCollapse" class="collapse">
        <form action="{{ route('admin.departments.index') }}" id="departmentFilterForm" method="get" class="card-body">
            <input type="hidden" id="departmentSearch" name="search" value="{{ $filterParameters['search'] ?? '' }}">
            <input type="hidden" name="sort_by" value="{{ $filterParameters['sort_by'] ?? 'id' }}">
            <input type="hidden" name="sort_direction" value="{{ $filterParameters['sort_direction'] ?? 'desc' }}">
            <div class="row g-3">
                @if(!isset(auth()->user()->branch_id))
                    <div class="col-lg-3 col-md-6">
                        <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
                        <select class="form-select" id="branch_id" name="branch[]" multiple data-placeholder="{{ __('index.all_branches') }}">
                            @foreach($branch as $branchOption)
                                <option value="{{ $branchOption->id }}" {{ in_array((string) $branchOption->id, $selectedBranches, true) ? 'selected' : '' }}>{{ $branchOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-lg-3 col-md-6">
                    <label for="departmentNameFilter" class="form-label">{{ __('index.department_name') }}</label>
                    <input id="departmentNameFilter" type="text" class="form-control" name="name" value="{{ $filterParameters['name'] ?? '' }}" placeholder="{{ __('index.search_by_department_name') }}" autocomplete="off">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="is_active" class="form-label">{{ __('index.department_status_filter') }}</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="">{{ __('index.all') }}</option>
                        <option value="1" {{ (string) ($filterParameters['is_active'] ?? '') === '1' ? 'selected' : '' }}>{{ __('index.active') }}</option>
                        <option value="0" {{ (string) ($filterParameters['is_active'] ?? '') === '0' ? 'selected' : '' }}>{{ __('index.inactive') }}</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="employee_status" class="form-label">{{ __('index.department_employee_status_filter') }}</label>
                    <select class="form-select" id="employee_status" name="employee_status">
                        @foreach(['all' => __('index.all'), 'active' => __('index.active'), 'inactive' => __('index.inactive')] as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ ($filterParameters['employee_status'] ?? 'all') === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="department-filter-actions">
                <small class="text-muted">{{ __('index.department_filter_hint') }}</small>
                <div class="d-flex gap-2"><a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.departments.index') }}">{{ __('index.clear_filters') }}</a><button class="btn btn-primary btn-sm" type="submit">{{ __('index.department_apply_filters') }}</button></div>
            </div>
        </form>
    </div>
</div>
