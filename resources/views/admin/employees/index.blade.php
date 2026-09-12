@php
    use App\Models\User;
    use App\Support\TelegramBotSettings;
@endphp
@extends('layouts.master')

@section('title', __('index.employees_title'))

@section('action', __('index.employees_action'))

@section('button')
    <div class="float-md-end d-flex align-items-center gap-1.5 justify-content-center">
        <a href="{{ route('admin.employees.calendar') }}">
            <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1.5 rounded-2 px-2.5 py-1.5" style="font-size: 0.8125rem;">
                <i class="link-icon" data-feather="calendar" style="width: 13px; height: 13px;"></i>Calendar
            </button>
        </a>

        @can('create_employee')
            <a href="{{ route('admin.employees.create')}}">
                <button class="btn btn-primary btn-sm d-flex align-items-center gap-1.5 rounded-2 px-2.5 py-1.5" style="font-size: 0.8125rem;">
                    <i class="link-icon" data-feather="plus" style="width: 14px; height: 14px;"></i>{{ __('index.add_employee') }}
                </button>
            </a>
        @endcan
    </div>
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.employees.common.breadcrumb')

        @php
            $telegramBotUsername = TelegramBotSettings::get(TelegramBotSettings::BOT_USERNAME, '');
            $hasEmployeeFilters = filled($filterParameters['branch_id'] ?? null)
                || filled($filterParameters['department_id'] ?? null)
                || filled($filterParameters['post_id'] ?? null)
                || filled($filterParameters['role_id'] ?? null)
                || filled($filterParameters['employee_name'] ?? null)
                || filled($filterParameters['search'] ?? null)
                || filled($filterParameters['email'] ?? null)
                || filled($filterParameters['phone'] ?? null)
                || (($filterParameters['is_active'] ?? '') !== '' && $filterParameters['is_active'] !== null)
                || (($filterParameters['per_page'] ?? '25') !== '25');
        @endphp

        <!-- Top KPI Metric Cards -->
        <div class="row g-2 mb-2.5 employee-kpi-container">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100 kpi-card-interactive"
                     onclick="filterByStatus('')"
                     title="Click to show all employees"
                     style="border-radius: 10px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-left: 3.5px solid #3b82f6 !important; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px; font-weight: 500; line-height: 1.2;">Total Employees</span>
                            <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.25rem; line-height: 1.2;">{{ $stats['total'] ?? $users->total() }}</h4>
                        </div>
                        <div class="rounded-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="link-icon" data-feather="users" style="width: 16px; height: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100 kpi-card-interactive"
                     onclick="filterByStatus('1')"
                     title="Click to filter active employees"
                     style="border-radius: 10px; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-left: 3.5px solid #10b981 !important; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px; font-weight: 500; line-height: 1.2;">Active Employees</span>
                            <h4 class="fw-bold mb-0 text-success" style="font-size: 1.25rem; line-height: 1.2;">{{ $stats['active'] ?? 0 }}</h4>
                        </div>
                        <div class="rounded-2 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="link-icon" data-feather="user-check" style="width: 16px; height: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100 kpi-card-interactive"
                     onclick="filterByStatus('0')"
                     title="Click to filter inactive employees"
                     style="border-radius: 10px; background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%); border-left: 3.5px solid #ef4444 !important; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px; font-weight: 500; line-height: 1.2;">Inactive Employees</span>
                            <h4 class="fw-bold mb-0 text-danger" style="font-size: 1.25rem; line-height: 1.2;">{{ $stats['inactive'] ?? 0 }}</h4>
                        </div>
                        <div class="rounded-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="link-icon" data-feather="user-x" style="width: 16px; height: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%); border-left: 3.5px solid #f97316 !important;">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px; font-weight: 500; line-height: 1.2;">Total Branches</span>
                            <h4 class="fw-bold mb-0" style="color: #ea580c; font-size: 1.25rem; line-height: 1.2;">{{ $stats['branches'] ?? $branches->count() }}</h4>
                        </div>
                        <div class="rounded-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(249, 115, 22, 0.1); color: #f97316;">
                            <i class="link-icon" data-feather="map-pin" style="width: 16px; height: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between gap-2 py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 rounded-2 px-2.5 py-1"
                            style="font-size: 0.8rem;"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#employeeFilterCollapse"
                            aria-expanded="{{ $hasEmployeeFilters ? 'true' : 'false' }}"
                            aria-controls="employeeFilterCollapse">
                        <i class="link-icon" data-feather="filter" style="width: 13px; height: 13px;"></i>
                        <span>{{ __('index.filter') }}</span>
                        @if($hasEmployeeFilters)
                            <span class="badge bg-primary rounded-pill" style="font-size: 9.5px;">Active</span>
                        @endif
                    </button>
                    <h6 class="card-title mb-0 fw-bold" style="font-size: 0.9rem;">{{ __('index.employee_lists') }}</h6>
                </div>
            </div>
            <div id="employeeFilterCollapse" class="collapse{{ $hasEmployeeFilters ? ' show' : '' }}">
            <form class="forms-sample card-body py-2 px-3 pb-1" action="{{ route('admin.employees.index') }}" id="employeeFilterForm" method="get">
                <input type="hidden" id="search" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                <div class="row g-2 align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-2">
                            <label class="form-label small text-muted mb-0.5" style="font-size: 11px; font-weight: 500;">{{ __('index.branch') }}</label>
                            @php
                                $selectedBranchIds = collect((array)($filterParameters['branch_id'] ?? []))
                                    ->filter()
                                    ->map(fn ($id) => (string) $id)
                                    ->all();
                            @endphp
                            <select class="form-control" id="branch" name="branch_id[]" multiple data-placeholder="{{ __('index.select_branch') }}">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ in_array((string) $branch->id, $selectedBranchIds, true) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-2">
                        <label class="form-label small text-muted mb-0.5" style="font-size: 11px; font-weight: 500;">{{ __('index.department') }}</label>
                        <select class="form-control" id="department" name="department_id[]" multiple data-placeholder="{{ __('index.select_department') }}">
                        </select>
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-2">
                        <label class="form-label small text-muted mb-0.5" style="font-size: 11px; font-weight: 500;">{{ __('index.post') }}</label>
                        <select class="form-control" id="post" name="post_id[]" multiple data-placeholder="{{ __('index.select_post') }}">
                        </select>
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-2">
                        <label class="form-label small text-muted mb-0.5" style="font-size: 11px; font-weight: 500;">{{ __('index.role') }}</label>
                        <select class="form-control" id="role_id" name="role_id" data-placeholder="{{ __('index.select_role') }}">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ (string)($filterParameters['role_id'] ?? '') === (string)$role->id ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-2">
                        <label class="form-label small text-muted mb-0.5" style="font-size: 11px; font-weight: 500;">{{ __('index.employee_name') }}</label>
                        <input type="text" placeholder="{{ __('index.employee_name') }}" id="employeeName"
                               name="employee_name" value="{{ $filterParameters['employee_name'] }}"
                               class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-2">
                        <label class="form-label small text-muted mb-0.5" style="font-size: 11px; font-weight: 500;">{{ __('index.employee_email') }}</label>
                        <input type="text" placeholder="{{ __('index.employee_email') }}" id="email" name="email"
                               value="{{ $filterParameters['email'] }}" class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-2">
                        <label class="form-label small text-muted mb-0.5" style="font-size: 11px; font-weight: 500;">{{ __('index.employee_phone') }}</label>
                        <input type="number" placeholder="{{ __('index.employee_phone') }}" id="phone" name="phone"
                               value="{{ $filterParameters['phone'] }}" class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-2">
                        <label class="form-label small text-muted mb-0.5" style="font-size: 11px; font-weight: 500;">{{ __('index.is_active') }}</label>
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="">All Status</option>
                            <option value="1" {{ (string)($filterParameters['is_active'] ?? '') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ (string)($filterParameters['is_active'] ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-12 mt-1 mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <button type="submit" value="filter" class="btn btn-primary btn-sm px-3 py-1.5 rounded-2 d-inline-flex align-items-center gap-1.5" style="font-size: 0.8rem;">
                                <i class="link-icon" data-feather="filter" style="width: 12px; height: 12px;"></i>
                                <span>{{ __('index.filter') }}</span>
                            </button>
                            <a class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 d-inline-flex align-items-center gap-1.5" style="font-size: 0.8rem;" href="{{ route('admin.employees.index') }}">
                                <i class="link-icon" data-feather="rotate-ccw" style="width: 12px; height: 12px;"></i>
                                <span>{{ __('index.reset') }}</span>
                            </a>
                        </div>
                    </div>

                </div>


            </form>
            </div>
        </div>

        <div id="employeeListSection">
        <div class="card border-0 shadow-sm employee-datatable-card" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white border-bottom py-2 px-3">
                <div class="employee-toolbar">
                    <div class="employee-toolbar-left">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="card-title mb-0 fw-bold" style="font-size: 0.95rem; color: #0f172a;">{{ __('index.employee_lists') }}</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-2 py-0.5 rounded-pill" style="font-size: 10.5px; font-weight: 600;">
                                {{ number_format($users->total()) }} Staff
                            </span>
                        </div>
                        <div class="employee-entry-control">
                            <span class="text-muted small fw-medium">Show</span>
                            <select class="form-select form-select-sm employee-entry-select" id="per_page" name="per_page" form="employeeFilterForm">
                                <option value="25" {{ (string)($filterParameters['per_page'] ?? '') === '25' ? 'selected' : '' }}>25</option>
                                <option value="50" {{ (string)($filterParameters['per_page'] ?? '') === '50' ? 'selected' : '' }}>50</option>
                                <option value="100" {{ (string)($filterParameters['per_page'] ?? '') === '100' ? 'selected' : '' }}>100</option>
                                <option value="200" {{ (string)($filterParameters['per_page'] ?? '') === '200' ? 'selected' : '' }}>200</option>
                                <option value="500" {{ (string)($filterParameters['per_page'] ?? '') === '500' ? 'selected' : '' }}>500</option>
                                <option value="1000" {{ (string)($filterParameters['per_page'] ?? '') === '1000' ? 'selected' : '' }}>1,000</option>
                                <option value="all" {{ (string)($filterParameters['per_page'] ?? '') === 'all' ? 'selected' : '' }}>All</option>
                            </select>
                            <span class="text-muted small fw-medium">entries</span>
                        </div>
                    </div>
                    <div class="employee-toolbar-actions">
                        @can('create_employee')
                            <button type="button"
                                    id="export_employee"
                                    data-href="{{ route('admin.employees.index') }}"
                                    value="export"
                                    class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 rounded-2 px-2.5 py-1 fw-medium shadow-none"
                                    style="border-color: #cbd5e1; color: #475569; font-size: 0.8rem;">
                                <i class="link-icon" data-feather="download" style="width: 13px; height: 13px;"></i>
                                <span>Export</span>
                            </button>
                        @endcan
                    </div>
                    <div class="employee-toolbar-search">
                        <div class="employee-search-box">
                            <i class="link-icon search-icon" data-feather="search"></i>
                            <input type="text"
                                   id="employeeListSearch"
                                   class="employee-list-search"
                                   value="{{ $filterParameters['search'] ?? '' }}"
                                   placeholder="Search employee...">
                            <button type="button"
                                    id="employeeSearchClear"
                                    class="employee-search-clear"
                                    style="display: {{ filled($filterParameters['search'] ?? null) ? 'flex' : 'none' }};"
                                    title="Clear search">
                                <i class="link-icon" data-feather="x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <style>
                    /* Filter Form Compact Fields */
                    #employeeFilterForm .form-label {
                        font-size: 11px;
                        font-weight: 600;
                        color: #64748b;
                        margin-bottom: 3px;
                    }

                    #employeeFilterForm .form-control {
                        min-height: 33px;
                        height: 33px;
                        padding: 3px 9px;
                        font-size: 0.8125rem;
                        border-radius: 7px;
                        border-color: #d7dfeb;
                        background-color: #ffffff;
                    }

                    #employeeFilterForm .form-control:focus {
                        border-color: #3b82f6;
                        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.12);
                    }

                    #employeeFilterForm .select2-container--default .select2-selection--single {
                        min-height: 33px;
                        height: 33px;
                        padding: 1px 6px;
                        border-radius: 7px;
                        border: 1px solid #d7dfeb;
                        background-color: #ffffff;
                        font-size: 0.8125rem;
                        display: flex;
                        align-items: center;
                    }

                    #employeeFilterForm .select2-container--default .select2-selection--single .select2-selection__rendered {
                        line-height: 31px;
                        padding-left: 2px;
                        font-size: 0.8125rem;
                        color: #334155;
                    }

                    #employeeFilterForm .select2-container--default .select2-selection--single .select2-selection__arrow {
                        height: 31px;
                        right: 8px;
                    }

                    #employeeFilterForm .select2-container--default .select2-selection--multiple {
                        min-height: 33px;
                        padding: 1px 6px;
                        border-radius: 7px;
                        border: 1px solid #d7dfeb;
                        background-color: #ffffff;
                        font-size: 0.8125rem;
                    }

                    #employeeFilterForm .select2-container--default .select2-selection--multiple .select2-selection__choice {
                        margin-top: 2px;
                        margin-bottom: 2px;
                        padding: 1px 6px;
                        font-size: 11px;
                        border-radius: 4px;
                        background-color: #eff6ff;
                        border: 1px solid #bfdbfe;
                        color: #1d4ed8;
                    }

                    #employeeFilterForm .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
                        margin-top: 2px;
                        font-size: 0.8125rem;
                        height: 24px;
                    }

                    .employee-toolbar {
                        display: grid;
                        grid-template-columns: auto 1fr auto;
                        align-items: center;
                        gap: 12px;
                    }

                    .employee-toolbar-left {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        flex-wrap: wrap;
                    }

                    .employee-toolbar-actions {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                    }

                    .employee-toolbar-search {
                        display: flex;
                        justify-content: flex-end;
                    }

                    .employee-entry-control {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        color: #64748b;
                        font-weight: 500;
                    }

                    .employee-entry-select {
                        min-width: 72px;
                        border-radius: 8px;
                        border: 1px solid #d7dfeb;
                        padding: 3px 22px 3px 8px;
                        font-size: 0.8rem;
                        font-weight: 600;
                        color: #334155;
                        background-color: #f8fafc;
                        cursor: pointer;
                    }

                    .employee-search-box {
                        position: relative;
                        display: flex;
                        align-items: center;
                        width: min(100%, 250px);
                    }

                    .employee-search-box .search-icon {
                        position: absolute;
                        left: 10px;
                        width: 14px;
                        height: 14px;
                        color: #94a3b8;
                        pointer-events: none;
                    }

                    .employee-list-search {
                        width: 100%;
                        border: 1px solid #d7dfeb;
                        border-radius: 8px;
                        min-height: 33px;
                        padding: 0 28px 0 32px;
                        font-size: 0.8rem;
                        color: #1e293b;
                        background: #f8fafc;
                        box-shadow: none;
                        transition: all 0.2s ease;
                    }

                    .employee-list-search:focus {
                        background: #ffffff;
                        outline: none;
                        border-color: #3b82f6;
                        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
                    }

                    .employee-search-clear {
                        position: absolute;
                        right: 8px;
                        width: 18px;
                        height: 18px;
                        border: none;
                        background: #e2e8f0;
                        color: #64748b;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        padding: 0;
                        transition: all 0.15s ease;
                    }

                    .employee-search-clear:hover {
                        background: #cbd5e1;
                        color: #0f172a;
                    }

                    .employee-search-clear .link-icon {
                        width: 11px;
                        height: 11px;
                    }

                    .employee-table-wrap {
                        overflow-x: auto;
                        overflow-y: visible;
                    }

                    .employee-table {
                        width: 100%;
                        min-width: 900px;
                        font-size: 0.785rem;
                        border-collapse: separate;
                        border-spacing: 0;
                        margin-bottom: 0;
                    }

                    .employee-table thead th {
                        background: #f8fafc;
                        color: #475569;
                        font-size: 0.68rem;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.03em;
                        padding: 7px 8px;
                        border-top: none;
                        border-bottom: 2px solid #e2e8f0;
                        vertical-align: middle;
                        white-space: nowrap;
                    }

                    .employee-table tbody td {
                        padding: 5px 8px;
                        vertical-align: middle;
                        border-bottom: 1px solid #f1f5f9;
                        background: #ffffff;
                        color: #1e293b;
                        transition: background-color 0.15s ease;
                    }

                    .employee-table tbody tr:hover td {
                        background-color: #f8fbff;
                    }

                    .employee-table tbody tr:last-child td {
                        border-bottom: none;
                    }

                    .employee-name-cell {
                        display: flex;
                        align-items: center;
                        gap: 7px;
                        min-width: 175px;
                    }

                    .employee-name-main {
                        display: flex;
                        align-items: center;
                        gap: 7px;
                        min-width: 0;
                        flex: 1;
                        text-decoration: none !important;
                        color: inherit;
                    }

                    .employee-name-main:hover .employee-name-text {
                        color: #2563eb !important;
                    }

                    .employee-name-info {
                        min-width: 0;
                    }

                    .employee-avatar-img {
                        width: 36px;
                        height: 36px;
                        border-radius: 50%;
                        object-fit: cover;
                        flex-shrink: 0;
                        border: 1.5px solid rgba(0, 0, 0, 0.08);
                        background-color: #f1f5f9;
                        display: block;
                        transition: transform 0.2s ease, box-shadow 0.2s ease;
                    }

                    .employee-name-main:hover .employee-avatar-img {
                        transform: scale(1.05);
                        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
                    }

                    .employee-edit-btn {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        color: #f97316 !important;
                        padding: 2px;
                        border-radius: 4px;
                        text-decoration: none !important;
                        transition: transform 0.15s ease, background-color 0.15s ease;
                        flex-shrink: 0;
                    }

                    .employee-edit-btn:hover {
                        background-color: rgba(249, 115, 22, 0.1);
                        transform: scale(1.1);
                    }

                    .employee-edit-btn i,
                    .employee-edit-btn svg {
                        width: 19px !important;
                        height: 19px !important;
                        stroke: #f97316 !important;
                        stroke-width: 2.2 !important;
                    }

                    .employee-edit-btn:hover i,
                    .employee-edit-btn:hover svg {
                        stroke: #ea580c !important;
                    }

                    .workplace-badge-btn {
                        display: inline-flex;
                        align-items: center;
                        gap: 3px;
                        padding: 1.5px 5px;
                        border-radius: 999px;
                        font-size: 9.5px;
                        font-weight: 600;
                        text-decoration: none !important;
                        transition: all 0.15s ease;
                        cursor: pointer;
                    }

                    .workplace-badge-btn.workplace-field {
                        background: #fffbeb;
                        color: #b45309;
                        border: 1px solid #fde68a;
                    }

                    .workplace-badge-btn.workplace-field:hover {
                        background: #fef3c7;
                        color: #92400e;
                        transform: translateY(-1px);
                    }

                    .workplace-badge-btn.workplace-office {
                        background: #f1f5f9;
                        color: #475569;
                        border: 1px solid #e2e8f0;
                    }

                    .workplace-badge-btn.workplace-office:hover {
                        background: #e2e8f0;
                        color: #1e293b;
                        transform: translateY(-1px);
                    }

                    .employee-action-btn {
                        width: 26px;
                        height: 26px;
                        border-radius: 50%;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        padding: 0;
                        transition: all 0.15s ease;
                        border: 1px solid #e2e8f0;
                        background: #ffffff;
                        color: #475569;
                    }

                    .employee-action-btn .link-icon {
                        width: 11px;
                        height: 11px;
                    }

                    .employee-action-btn:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 2px 5px rgba(0,0,0,0.06);
                    }

                    .employee-action-btn.btn-view:hover {
                        background: #eff6ff;
                        color: #2563eb;
                        border-color: #bfdbfe;
                    }

                    .employee-action-btn.btn-edit:hover {
                        background: #f0fdf4;
                        color: #16a34a;
                        border-color: #bbf7d0;
                    }

                    .employee-action-btn.btn-more:hover {
                        background: #f8fafc;
                        color: #1e293b;
                        border-color: #cbd5e1;
                    }

                    .kpi-card-interactive:hover {
                        transform: translateY(-3px) !important;
                        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08) !important;
                    }

                    .employee-table .switch {
                        margin-bottom: 0;
                    }

                    .employee-pagination-wrap nav {
                        margin-bottom: 0;
                    }

                    .employee-pagination-wrap .pagination {
                        margin-bottom: 0;
                        gap: 4px;
                    }

                    .employee-pagination-wrap .page-item .page-link {
                        border-radius: 8px;
                        border: 1px solid #e2e8f0;
                        color: #475569;
                        padding: 5px 11px;
                        font-size: 0.8125rem;
                        font-weight: 600;
                        background: #ffffff;
                        transition: all 0.15s ease;
                    }

                    .employee-pagination-wrap .page-item.active .page-link {
                        background: #2563eb;
                        border-color: #2563eb;
                        color: #ffffff;
                        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
                    }

                    .employee-pagination-wrap .page-item .page-link:hover:not(.active) {
                        background: #f1f5f9;
                        color: #1e293b;
                        border-color: #cbd5e1;
                    }

                    .telegram-connect-card {
                        text-align: center;
                    }

                    .telegram-connect-qr {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 220px;
                        height: 220px;
                        max-width: 100%;
                        margin: 0 auto 18px;
                        padding: 14px;
                        border: 1px solid #d8e0e7;
                        border-radius: 12px;
                        background: #ffffff;
                    }

                    .telegram-connect-qr svg {
                        width: 100%;
                        height: 100%;
                    }

                    .telegram-connect-link {
                        font-size: 12px;
                    }

                    .telegram-connect-status {
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        margin-bottom: 14px;
                        padding: 8px 14px;
                        border-radius: 999px;
                        color: #0f7a3b;
                        background: #dcfce7;
                        font-weight: 700;
                    }

                    @media (max-width: 767.98px) {
                        .employee-toolbar {
                            grid-template-columns: 1fr;
                            align-items: stretch;
                        }

                        .employee-toolbar-actions,
                        .employee-toolbar-search {
                            justify-content: flex-start;
                        }

                        .employee-entry-control {
                            width: 100%;
                            justify-content: space-between;
                        }

                        .employee-entry-select {
                            min-width: 0;
                            flex: 1;
                        }

                        .employee-search-box {
                            width: 100%;
                        }
                    }
                </style>
                <div class="table-responsive employee-table-wrap">
                    <table id="employeeTable" class="table employee-table">
                        <thead>
                        <tr>
                            <th class="text-center" style="width: 38px;">#</th>
                            <th style="min-width: 190px;">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="link-icon text-muted" data-feather="user" style="width: 12px; height: 12px;"></i>
                                    {{ __('index.employee') }}
                                </span>
                            </th>
                            <th style="min-width: 140px;">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="link-icon text-muted" data-feather="mail" style="width: 12px; height: 12px;"></i>
                                    {{ __('index.contact') }}
                                </span>
                            </th>
                            <th style="min-width: 140px;">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="link-icon text-muted" data-feather="briefcase" style="width: 12px; height: 12px;"></i>
                                    {{ __('index.branch') }} & {{ __('index.department') }}
                                </span>
                            </th>
                            <th style="min-width: 130px;">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="link-icon text-muted" data-feather="award" style="width: 12px; height: 12px;"></i>
                                    {{ __('index.designation') }} & {{ __('index.role') }}
                                </span>
                            </th>
                            <th class="text-center" style="min-width: 110px;">
                                <span class="d-inline-flex align-items-center justify-content-center gap-1">
                                    <i class="link-icon text-muted" data-feather="clock" style="width: 12px; height: 12px;"></i>
                                    {{ __('index.shift') }} & {{ __('index.workplace') }}
                                </span>
                            </th>
                            <th class="text-center" style="width: 85px;">
                                <span class="d-inline-flex align-items-center justify-content-center gap-1">
                                    <i class="link-icon text-muted" data-feather="calendar" style="width: 12px; height: 12px;"></i>
                                    {{ __('index.holiday_check_in') }}
                                </span>
                            </th>
                            <th class="text-center" style="width: 85px;">
                                <span class="d-inline-flex align-items-center justify-content-center gap-1">
                                    <i class="link-icon text-muted" data-feather="activity" style="width: 12px; height: 12px;"></i>
                                    {{ __('index.is_active') }}
                                </span>
                            </th>
                            @canany(['employee.profile.view','edit_employee','delete_employee','change_password','force_logout','show_detail_employee'])
                                <th class="text-end pe-2" style="width: 85px;">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $key => $value)
                            <tr>
                                <td class="text-center text-muted fw-semibold" style="font-size: 11px;">
                                    {{ method_exists($users, 'firstItem') && $users->firstItem() ? $users->firstItem() + $key : $key + 1 }}
                                </td>
                                <td class="col-employee">
                                    @php
                                        $profileImage = $value->avatar_url;
                                    @endphp
                                    <div class="employee-name-cell">
                                        @canany(['show_detail_employee', 'employee.profile.view'])
                                            <a href="{{ route('admin.employees.show', $value->id) }}"
                                               class="employee-name-main">
                                                <img src="{{ $profileImage }}"
                                                     alt="{{ ucfirst($value->name) }}"
                                                     class="employee-avatar-img"
                                                     loading="lazy"
                                                     onerror="this.onerror=null; this.src='{{ asset('assets/images/img.png') }}';">
                                                <div class="employee-name-info">
                                                    <p class="mb-0 fw-semibold text-dark text-truncate employee-name-text"
                                                       style="max-width: 145px; font-size: 0.825rem; line-height: 1.25;"
                                                       title="{{ ucfirst($value->name) }}">
                                                        {{ ucfirst($value->name) }}
                                                    </p>
                                                    <small class="text-muted d-block text-truncate"
                                                           style="max-width: 145px; font-size: 0.725rem; line-height: 1.2;"
                                                           title="{{ ucfirst($value->role ? $value->role->name : 'N/A') }}">
                                                        ({{ ucfirst($value->role ? $value->role->name : 'N/A') }})
                                                    </small>
                                                </div>
                                            </a>
                                        @else
                                            <div class="employee-name-main">
                                                <img src="{{ $profileImage }}"
                                                     alt="{{ ucfirst($value->name) }}"
                                                     class="employee-avatar-img"
                                                     loading="lazy"
                                                     onerror="this.onerror=null; this.src='{{ asset('assets/images/img.png') }}';">
                                                <div class="employee-name-info">
                                                    <p class="mb-0 fw-semibold text-dark text-truncate employee-name-text"
                                                       style="max-width: 145px; font-size: 0.825rem; line-height: 1.25;"
                                                       title="{{ ucfirst($value->name) }}">
                                                        {{ ucfirst($value->name) }}
                                                    </p>
                                                    <small class="text-muted d-block text-truncate"
                                                           style="max-width: 145px; font-size: 0.725rem; line-height: 1.2;"
                                                           title="{{ ucfirst($value->role ? $value->role->name : 'N/A') }}">
                                                        ({{ ucfirst($value->role ? $value->role->name : 'N/A') }})
                                                    </small>
                                                </div>
                                            </div>
                                        @endcanany

                                        @can('edit_employee')
                                            <a href="{{ route('admin.employees.edit', $value->id) }}"
                                               class="employee-edit-btn ms-auto"
                                               title="{{ __('index.edit_detail') }}"
                                               target="_blank"
                                               rel="noopener noreferrer">
                                                <i class="link-icon" data-feather="edit"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-0.5">
                                        @if($value->email)
                                            <a href="mailto:{{ $value->email }}"
                                               class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 text-truncate"
                                               style="max-width: 160px; font-size: 0.775rem;"
                                               title="{{ $value->email }}">
                                                <i class="link-icon text-muted" data-feather="mail" style="width: 11px; height: 11px; flex-shrink: 0;"></i>
                                                <span>{{ $value->email }}</span>
                                            </a>
                                        @else
                                            <span class="text-muted" style="font-size: 0.75rem;">N/A</span>
                                        @endif

                                        @if($value->phone)
                                            <a href="tel:{{ $value->phone }}"
                                               class="text-decoration-none text-muted d-inline-flex align-items-center gap-1"
                                               style="font-size: 0.75rem;">
                                                <i class="link-icon text-muted" data-feather="phone" style="width: 11px; height: 11px; flex-shrink: 0;"></i>
                                                <span>{{ $value->phone }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-0.5">
                                        <div>
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-medium px-1.5 py-0.5 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 10px;">
                                                <i class="link-icon" data-feather="map-pin" style="width: 9px; height: 9px;"></i>
                                                {{ $value->branch ? ucfirst($value->branch->name) : 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.725rem;">
                                            <i class="link-icon text-muted" data-feather="briefcase" style="width: 11px; height: 11px; flex-shrink: 0;"></i>
                                            <span class="text-truncate" style="max-width: 140px;">{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-0.5">
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 150px; font-size: 0.8rem;" title="{{ $value->post ? ucfirst($value->post->post_name) : 'N/A' }}">
                                            {{ $value->post ? ucfirst($value->post->post_name) : 'N/A' }}
                                        </div>
                                        <div>
                                            @php
                                                $roleName = strtolower((string)($value->role?->name ?? ''));
                                                $roleClass = 'bg-info bg-opacity-10 text-info border border-info border-opacity-25';
                                                if (str_contains($roleName, 'admin')) {
                                                    $roleClass = 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25';
                                                } elseif (str_contains($roleName, 'manager')) {
                                                    $roleClass = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                                                } elseif (str_contains($roleName, 'supervisor') || str_contains($roleName, 'lead')) {
                                                    $roleClass = 'bg-warning bg-opacity-15 text-dark border border-warning border-opacity-40';
                                                }
                                            @endphp
                                            <span class="badge {{ $roleClass }} px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px; font-weight: 600;">
                                                {{ $value->role ? ucfirst($value->role->name) : 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-0.5">
                                        <div class="text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.725rem;">
                                            <i class="link-icon text-muted" data-feather="clock" style="width: 10px; height: 10px;"></i>
                                            <span>{{ $value->officeTime ? ucfirst($value->officeTime->shift) : 'N/A' }}</span>
                                        </div>
                                        <a class="changeWorkPlace workplace-badge-btn {{ $value->workspace_type == User::FIELD ? 'workplace-field' : 'workplace-office' }}"
                                           data-href="{{ route('admin.employees.change-workspace', $value->id) }}"
                                           title="Click to toggle workspace (Office / Field)"
                                           role="button">
                                            <i class="link-icon" data-feather="{{ $value->workspace_type == User::FIELD ? 'navigation' : 'home' }}" style="width: 10px; height: 10px;"></i>
                                            <span>{{ $value->workspace_type == User::FIELD ? 'Field' : 'Office' }}</span>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <label class="switch mb-0">
                                        <input class="toggleHolidayCheckIn"
                                               href="{{ route('admin.employees.toggle-holiday-checkin', $value->id) }}"
                                               type="checkbox" {{ $value->allow_holiday_check_in == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                                        <label class="switch mb-0">
                                            <input class="toggleStatus"
                                                   href="{{ route('admin.employees.toggle-status', $value->id) }}"
                                                   type="checkbox" {{ $value->is_active == 1 ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                        <span class="badge {{ $value->is_active == 1 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} rounded-pill" style="font-size: 9px; font-weight: 600; padding: 2px 6px;">
                                            {{ $value->is_active == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </td>
                                @canany(['employee.profile.view','edit_employee','delete_employee','change_password','force_logout','show_detail_employee'])
                                    <td class="text-end pe-2">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            @can('employee.profile.view')
                                                <a href="{{ route('admin.employees.profile.show', $value->id) }}"
                                                   class="employee-action-btn btn-view"
                                                   title="Employee 360">
                                                    <i class="link-icon" data-feather="user" style="width: 13px; height: 13px;"></i>
                                                </a>
                                            @elsecan('show_detail_employee')
                                                <a href="{{ route('admin.employees.show', $value->id) }}"
                                                   class="employee-action-btn btn-view"
                                                   title="View Detail">
                                                    <i class="link-icon" data-feather="eye" style="width: 13px; height: 13px;"></i>
                                                </a>
                                            @endcan

                                            @can('edit_employee')
                                                <a href="{{ route('admin.employees.edit', $value->id) }}"
                                                   class="employee-action-btn btn-edit"
                                                   title="{{ __('index.edit_detail') }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer">
                                                    <i class="link-icon" data-feather="edit-2" style="width: 13px; height: 13px;"></i>
                                                </a>
                                            @endcan

                                            <div class="dropdown d-inline-block">
                                                <button class="employee-action-btn btn-more"
                                                        type="button"
                                                        id="dropdownMenuButton{{ $value->id }}"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false"
                                                        title="More actions">
                                                    <i class="link-icon" data-feather="more-vertical" style="width: 14px; height: 14px;"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="min-width: 190px; border-radius: 12px;" aria-labelledby="dropdownMenuButton{{ $value->id }}">
                                                    @can('show_detail_employee')
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.employees.show', $value->id) }}">
                                                                <i class="link-icon text-primary" data-feather="eye" style="width: 14px; height: 14px;"></i>
                                                                <span>View Details</span>
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @can('employee.profile.view')
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.employees.profile.show', $value->id) }}">
                                                                <i class="link-icon text-primary" data-feather="user" style="width: 14px; height: 14px;"></i>
                                                                <span>Employee 360</span>
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @can('edit_employee')
                                                        @php $telegramConnectUrl = $telegramBotUsername ? TelegramBotSettings::connectUrl($value) : null; @endphp
                                                        @if($telegramConnectUrl)
                                                            <li>
                                                                <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                                                   href="#"
                                                                   data-bs-toggle="modal"
                                                                   data-bs-target="#telegramConnectModal{{ $value->id }}">
                                                                    <i class="link-icon {{ $value->telegram_chat_id ? 'text-success' : 'text-info' }}" data-feather="{{ $value->telegram_chat_id ? 'check-circle' : 'send' }}" style="width: 14px; height: 14px;"></i>
                                                                    <span>{{ $value->telegram_chat_id ? 'Telegram Connected' : 'Connect Telegram' }}</span>
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endcan

                                                    @can('change_password')
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 changePassword"
                                                               href="#"
                                                               data-href="{{ route('admin.employees.change-password', $value->id) }}">
                                                                <i class="link-icon text-warning" data-feather="key" style="width: 14px; height: 14px;"></i>
                                                                <span>{{ __('index.change_password') }}</span>
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @can('force_logout')
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 forceLogOut"
                                                               href="#"
                                                               data-href="{{ route('admin.employees.force-logout', $value->id) }}">
                                                                <i class="link-icon text-secondary" data-feather="log-out" style="width: 14px; height: 14px;"></i>
                                                                <span>{{ __('index.force_logout') }}</span>
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @can('delete_employee')
                                                        @if( (isset(auth()->user()->id) && $value->id != auth()->user()->id) || $value->id != 1)
                                                            <li><hr class="dropdown-divider my-1"></li>
                                                            <li>
                                                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger deleteEmployee"
                                                                   href="#"
                                                                   data-href="{{ route('admin.employees.delete', $value->id) }}">
                                                                    <i class="link-icon text-danger" data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                                    <span>{{ __('index.delete_user') }}</span>
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>

                                        @can('edit_employee')
                                            @if($telegramConnectUrl)
                                                <!-- Telegram Connect Modal -->
                                                <div class="modal fade text-start" id="telegramConnectModal{{ $value->id }}" tabindex="-1"
                                                     aria-labelledby="telegramConnectModalLabel{{ $value->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="telegramConnectModalLabel{{ $value->id }}">
                                                                    {{ $value->telegram_chat_id ? ucfirst($value->name) . ' has been connected' : 'Connect ' . ucfirst($value->name) . ' to Telegram' }}
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="telegram-connect-card">
                                                                    @if($value->telegram_chat_id)
                                                                        <div class="telegram-connect-status">
                                                                            <i class="link-icon" data-feather="check-circle"></i>
                                                                            Has been connected
                                                                        </div>

                                                                        <p class="mb-2">
                                                                            This employee is already connected to Telegram.
                                                                        </p>

                                                                        <div class="text-muted mb-3">
                                                                            Chat ID: {{ $value->telegram_chat_id }}
                                                                            @if($value->telegram_username)
                                                                                <br>Username: {{ '@' . $value->telegram_username }}
                                                                            @endif
                                                                            @if($value->telegram_linked_at)
                                                                                <br>Linked at: {{ optional($value->telegram_linked_at)->format('Y-m-d H:i') }}
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        <div class="telegram-connect-qr">
                                                                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(190)->margin(1)->generate($telegramConnectUrl) !!}
                                                                        </div>

                                                                        <p class="mb-2">
                                                                            Scan this QR code or open the link to connect this employee with the Telegram bot.
                                                                        </p>
                                                                    @endif

                                                                    <div class="input-group input-group-sm">
                                                                        <input type="text"
                                                                               class="form-control telegram-connect-link"
                                                                               id="telegramConnectLink{{ $value->id }}"
                                                                               value="{{ $telegramConnectUrl }}"
                                                                               readonly>
                                                                        <button type="button"
                                                                                class="btn btn-outline-secondary copyTelegramConnectLink"
                                                                                data-target="telegramConnectLink{{ $value->id }}">
                                                                            Copy
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                @if($value->telegram_chat_id)
                                                                    <form method="POST" action="{{ route('admin.telegram-employees.unlink', $value->id) }}" class="m-0 telegram-unlink-form">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-outline-danger">
                                                                            <i class="link-icon" data-feather="unlink"></i>
                                                                            Unlink Telegram
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                                <form method="POST" action="{{ route('admin.telegram-employees.sync-starts') }}" class="m-0">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-outline-success">
                                                                        <i class="link-icon" data-feather="download-cloud"></i>
                                                                        Sync Telegram Starts
                                                                    </button>
                                                                </form>
                                                                <a href="{{ $telegramConnectUrl }}"
                                                                   target="_blank"
                                                                   rel="noopener noreferrer"
                                                                   class="btn {{ $value->telegram_chat_id ? 'btn-outline-primary' : 'btn-primary' }}">
                                                                    <i class="link-icon" data-feather="send"></i>
                                                                    {{ $value->telegram_chat_id ? 'Open Connect Link Again' : 'Open Telegram' }}
                                                                </a>
                                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <div class="text-center py-5 my-3">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 68px; height: 68px; background: #f1f5f9;">
                                            <i class="link-icon text-muted" data-feather="users" style="width: 32px; height: 32px;"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">{{ __('index.no_records_found') }}</h6>
                                        <p class="text-muted small mb-3">No employees found matching your search or filter criteria.</p>
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3" onclick="window.location.href='{{ route('admin.employees.index') }}'">
                                                <i class="link-icon" data-feather="refresh-cw" style="width: 12px; height: 12px;"></i>
                                                <span>Reset Filters</span>
                                            </button>
                                            @can('create_employee')
                                                <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-primary rounded-3 px-3">
                                                    <i class="link-icon" data-feather="plus" style="width: 13px; height: 13px;"></i>
                                                    <span>{{ __('index.add_employee') }}</span>
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2" id="employeeTableFooter">
                <div class="employee-pagination-info text-muted small d-flex align-items-center gap-1.5">
                    <span>Showing</span>
                    <span class="fw-bold text-dark">{{ $users->firstItem() ?? 0 }}</span>
                    <span>to</span>
                    <span class="fw-bold text-dark">{{ $users->lastItem() ?? 0 }}</span>
                    <span>of</span>
                    <span class="fw-bold text-dark">{{ number_format($users->total()) }}</span>
                    <span>entries</span>
                </div>
                <div class="employee-pagination-wrap">
                    {{ $users->appends($_GET)->links() }}
                </div>
            </div>
        </div>
        </div>

    </section>
    @include('admin.employees.common.password')
@endsection

@section('scripts')
    @include('admin.employees.common.scripts')
    <script>
        $(document).on('click', '.copyTelegramConnectLink', function () {
            var button = $(this);
            var input = document.getElementById(button.data('target'));

            if (!input) {
                return;
            }

            input.select();
            input.setSelectionRange(0, input.value.length);

            if (navigator.clipboard) {
                navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy');
            }

            var originalText = button.text();
            button.text('Copied');

            setTimeout(function () {
                button.text(originalText);
            }, 1500);
        });

        var telegramConnectSyncTimer = null;
        var telegramConnectSyncAttempts = 0;

        function syncTelegramStartsFromEmployeeModal() {
            fetch('{{ route('admin.telegram-employees.sync-starts') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data.linked && Number(data.linked) > 0) {
                    window.location.reload();
                }
            }).catch(function () {});
        }

        $(document).on('shown.bs.modal', '[id^="telegramConnectModal"]', function () {
            telegramConnectSyncAttempts = 0;
            clearInterval(telegramConnectSyncTimer);
            telegramConnectSyncTimer = setInterval(function () {
                telegramConnectSyncAttempts++;
                syncTelegramStartsFromEmployeeModal();

                if (telegramConnectSyncAttempts >= 12) {
                    clearInterval(telegramConnectSyncTimer);
                }
            }, 5000);
        });

        $(document).on('hidden.bs.modal', '[id^="telegramConnectModal"]', function () {
            clearInterval(telegramConnectSyncTimer);
        });

        $(document).on('submit', '.telegram-unlink-form', function (event) {
            if (!confirm('Unlink this employee from Telegram?')) {
                event.preventDefault();
            }
        });
    </script>
@endsection
