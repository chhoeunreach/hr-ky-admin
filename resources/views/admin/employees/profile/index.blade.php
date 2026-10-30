@extends('layouts.master')

@section('title', __('index.employee_profiles'))

@section('action', __('index.employee_profile'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.employees.common.breadcrumb')

        <style>
            .employee-profile-actions .dropdown-toggle {
                display: inline-flex;
                align-items: center;
                gap: .5rem;
                border-radius: .5rem;
                font-weight: 500;
                white-space: nowrap;
            }
            .employee-profile-actions .link-icon { width: 16px; height: 16px; flex-shrink: 0; }
            .employee-profile-action-menu {
                min-width: 230px;
                max-width: calc(100vw - 24px);
                padding: .5rem;
                border-radius: .75rem;
                box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
            }
            .employee-profile-action-menu .dropdown-header { max-width: 260px; padding: .5rem .75rem; }
            .employee-profile-action-menu .dropdown-item {
                display: flex;
                align-items: center;
                gap: .75rem;
                padding: .65rem .75rem;
                border-radius: .375rem;
            }
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 8mm;
                }
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                .sidebar,
                .navbar,
                .footer,
                #preloader,
                .breadcrumb,
                .no-print,
                .pagination {
                    display: none !important;
                }
                .main-wrapper,
                .page-wrapper,
                .page-content,
                .content,
                .card,
                .card-body,
                .table-responsive {
                    border: 0 !important;
                    box-shadow: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                }
                .card-header {
                    border: 0 !important;
                    padding: 0 0 6px !important;
                }
                .employee-profile-print-title {
                    display: block !important;
                    font-size: 16px;
                    font-weight: 800;
                    margin-bottom: 6px;
                }
                .table {
                    font-size: 8.5px;
                    width: 100% !important;
                }
                .table th,
                .table td {
                    border: 1px solid #cbd5e1 !important;
                    padding: 3px 4px !important;
                    vertical-align: top !important;
                }
                .table img {
                    height: 26px !important;
                    width: 26px !important;
                }
                .badge {
                    border: 1px solid #94a3b8;
                    color: #111827 !important;
                }
            }
            .employee-profile-search {
                margin-left: auto;
                position: relative;
                width: min(100%, 280px);
            }
            .employee-profile-search .search-icon {
                color: #94a3b8;
                height: 15px;
                left: 11px;
                pointer-events: none;
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 15px;
            }
            .employee-profile-search-input {
                background: #f8fafc;
                border: 1px solid #d7dfeb;
                border-radius: 8px;
                color: #1e293b;
                font-size: .8125rem;
                height: 34px;
                padding: 0 34px;
                width: 100%;
            }
            .employee-profile-search-input:focus {
                background: #ffffff;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
                outline: none;
            }
            .employee-profile-search-clear {
                align-items: center;
                background: #e2e8f0;
                border: 0;
                border-radius: 50%;
                color: #64748b;
                display: flex;
                height: 20px;
                justify-content: center;
                padding: 0;
                position: absolute;
                right: 9px;
                top: 50%;
                transform: translateY(-50%);
                width: 20px;
            }
            .employee-profile-search-clear:hover {
                background: #cbd5e1;
                color: #0f172a;
            }
            .employee-profile-search-clear .link-icon {
                height: 12px;
                width: 12px;
            }
            @media (max-width: 767.98px) {
                .employee-profile-search {
                    order: 4;
                    width: 100%;
                }
            }
        </style>

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3 no-print">
                    <button class="btn btn-outline-secondary btn-sm"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#employeeProfileFilters"
                            aria-expanded="{{ request()->hasAny(['branch_id', 'department_id', 'department_ids', 'post_id', 'employment_status', 'review_status', 'per_page']) ? 'true' : 'false' }}"
                            aria-controls="employeeProfileFilters">
                        {{ __('index.filter') }}
                    </button>
                    <h6 class="card-title mb-0">{{ __('index.employee_profile') }}</h6>
                    @can('employee.attendance_daily_report.view')
                    <button type="submit" form="employeeProfileFilters" name="attendance_report" value="1" data-employee-report class="btn btn-outline-primary btn-sm">
                        <i class="link-icon" data-feather="file-text"></i> {{ __('index.attendance_confirmation_report') }}
                    </button>
                    @endcan
                    <div class="employee-profile-search">
                        <i class="link-icon search-icon" data-feather="search"></i>
                        <input type="search"
                               id="employeeProfileSearch"
                               class="employee-profile-search-input"
                               name="search"
                               form="employeeProfileFilters"
                               autocomplete="off"
                               maxlength="255"
                               value="{{ request('search') }}"
                               placeholder="{{ __('index.search_employee') }}"
                               aria-label="{{ __('index.search_employee') }}">
                        <button type="button"
                                id="employeeProfileSearchClear"
                                class="employee-profile-search-clear"
                                style="display: {{ filled(request('search')) ? 'flex' : 'none' }};"
                                title="{{ __('index.clear_search') }}"
                                aria-label="{{ __('index.clear_search') }}">
                            <i class="link-icon" data-feather="x"></i>
                        </button>
                    </div>
                    @can('employee.profile.print')
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                            {{ __('index.print') }}
                        </button>
                    @endcan
                </div>
                <h6 class="employee-profile-print-title d-none">{{ __('index.employee_profile') }}</h6>
                <form method="get" id="employeeProfileFilters" class="collapse no-print {{ request()->hasAny(['branch_id', 'department_id', 'department_ids', 'post_id', 'employment_status', 'review_status', 'per_page']) ? 'show' : '' }}">
                    @if(request()->filled('name_direction'))
                        <input type="hidden" name="name_direction" value="{{ request('name_direction') }}">
                    @endif
                    @if(request()->filled('working_life_direction'))
                        <input type="hidden" name="working_life_direction" value="{{ request('working_life_direction') }}">
                    @endif
                    <div class="row g-2">
                        <div class="col-lg-2 col-md-6">
                            <select class="form-select" name="branch_id">
                                <option value="">{{ __('index.all_branches') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select class="form-select employee-department-multiselect" name="department_ids[]" multiple aria-label="{{ __('index.department') }}" data-placeholder="{{ __('index.all_departments') }}">
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected(in_array((string) $department->id, array_map('strval', $departmentIds), true))>{{ $department->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select class="form-select" name="post_id">
                                <option value="">{{ __('index.all_positions') }}</option>
                                @foreach($posts as $post)
                                    <option value="{{ $post->id }}" @selected((string) request('post_id') === (string) $post->id)>{{ $post->post_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <select class="form-select" name="employment_status">
                                <option value="">{{ __('index.all_status') }}</option>
                                @foreach(['active', 'probation', 'suspended', 'resigned', 'terminated', 'inactive'] as $status)
                                    <option value="{{ $status }}" @selected($employmentStatus === $status)>{{ __('index.' . $status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <select class="form-select" name="review_status">
                                <option value="">{{ __('index.review') }}</option>
                                @foreach(['Due', 'Overdue', 'Upcoming', 'Done', 'N/A'] as $status)
                                    <option value="{{ $status }}" @selected(request('review_status') === $status)>{{ $status === 'N/A' ? 'N/A' : __('index.' . strtolower($status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <select class="form-select" name="per_page">
                                @foreach([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                                @endforeach
                                <option value="all" @selected($perPage === 'all')>{{ __('index.all') }}</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6 d-flex gap-2">
                            <button class="btn btn-primary w-100">{{ __('index.apply') }}</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.employees.profile.index') }}">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </form>
                <div id="employeeProfileSearchLoading" class="text-muted small d-none" role="status">{{ __('index.loading') }}</div>
                <div id="employeeProfileSearchError" class="alert alert-danger mt-2 mb-0 d-none" role="alert">{{ __('index.error_loading_employees') }}</div>
            </div>
            @include('admin.employees.profile.partials.directory-results')
        </div>
        @can('employee.performance.create')
            @include('admin.employees.profile.partials.quick-complete-review')
        @endcan
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reviewModal = document.getElementById('quickCompleteReviewModal');
            if (reviewModal) {
                reviewModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (!button) return;
                    const reviewForm = reviewModal.querySelector('form');
                    reviewForm.reset();
                    reviewForm.action = button.dataset.action;
                    reviewModal.querySelector('[data-review-summary]').textContent =
                        button.dataset.employee + ' · ' + button.dataset.label + ' · ' +
                        button.dataset.periodStart + ' → ' + button.dataset.periodEnd;
                    ['review_type', 'period_start', 'period_end'].forEach(function (name) {
                        const key = name.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());
                        reviewForm.elements[name].value = button.dataset[key];
                    });
                    reviewModal.querySelector('[data-full-review]').href = button.dataset.reviewUrl;
                    reviewForm.querySelector('[type="submit"]').disabled = false;
                });
                reviewModal.querySelector('form').addEventListener('submit', function () {
                    this.querySelector('[type="submit"]').disabled = true;
                });
            }
        });
    </script>
    @include('admin.employees.profile.partials.department-filter-script', ['departmentFilterForm' => 'employeeProfileFilters'])
    <script src="{{ asset('assets/js/employee-profile-directory.js') }}?v={{ filemtime(public_path('assets/js/employee-profile-directory.js')) }}"></script>
@endsection
