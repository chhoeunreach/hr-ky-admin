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
        </style>

        <div class="directory-filter-panel mb-4 no-print">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'employeeProfileFilterCollapse', 'directoryExpanded' => false])
                <div id="employeeProfileFilterCollapse" class="collapse">
                <form method="get" action="{{ route('admin.employees.profile.index') }}" id="employeeProfileFilters" class="directory-filter-form">
                    @if(request()->filled('name_direction'))
                        <input type="hidden" name="name_direction" value="{{ request('name_direction') }}">
                    @endif
                    @if(request()->filled('working_life_direction'))
                        <input type="hidden" name="working_life_direction" value="{{ request('working_life_direction') }}">
                    @endif
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-4 col-md-6">
                            <label for="profileBranchFilter" class="form-label">{{ __('index.branch') }}</label>
                            <select id="profileBranchFilter" class="form-select" name="branch_id">
                                <option value="">{{ __('index.all_branches') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <label for="profileDepartmentFilter" class="form-label">{{ __('index.department') }}</label>
                            <select id="profileDepartmentFilter" class="form-select employee-department-multiselect" name="department_ids[]" multiple aria-label="{{ __('index.department') }}" data-placeholder="{{ __('index.all_departments') }}">
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected(in_array((string) $department->id, array_map('strval', $departmentIds), true))>{{ $department->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <label for="profilePostFilter" class="form-label">{{ __('index.position') }}</label>
                            <select id="profilePostFilter" class="form-select" name="post_id">
                                <option value="">{{ __('index.all_positions') }}</option>
                                @foreach($posts as $post)
                                    <option value="{{ $post->id }}" @selected((string) request('post_id') === (string) $post->id)>{{ $post->post_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <label for="profileEmploymentStatusFilter" class="form-label">{{ __('index.employment_status') }}</label>
                            <select id="profileEmploymentStatusFilter" class="form-select" name="employment_status">
                                <option value="">{{ __('index.all_status') }}</option>
                                @foreach(['active', 'probation', 'suspended', 'resigned', 'terminated', 'inactive'] as $status)
                                    <option value="{{ $status }}" @selected($employmentStatus === $status)>{{ __('index.' . $status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <label for="profileReviewStatusFilter" class="form-label">{{ __('index.review') }}</label>
                            <select id="profileReviewStatusFilter" class="form-select" name="review_status">
                                <option value="">{{ __('index.review') }}</option>
                                @foreach(['Due', 'Overdue', 'Upcoming', 'Done', 'N/A'] as $status)
                                    <option value="{{ $status }}" @selected(request('review_status') === $status)>{{ $status === 'N/A' ? 'N/A' : __('index.' . strtolower($status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.employees.profile.index')])
                </form>
            </div>
        </div>
        <div class="card">
            @include('admin.section.directory-toolbar', ['directoryTable' => 'employeeProfileTable', 'directoryForm' => 'employeeProfileFilters', 'directorySearch' => 'employeeProfileSearch', 'directorySearchInForm' => true, 'directoryRecords' => $employees, 'directoryTitle' => __('index.employee_profiles'), 'directoryIcon' => 'users', 'directoryCanAdd' => false, 'directoryCanPrint' => \Illuminate\Support\Facades\Gate::allows('employee.profile.print'), 'directoryAddUrl' => '', 'directoryDefaultSize' => '25', 'directoryPageSizes' => [10,25,50,100,200,500,1000,'all'], 'directorySearchKey' => 'search', 'directoryLive' => true])
            <h6 class="employee-profile-print-title d-none">{{ __('index.employee_profile') }}</h6>
            @can('employee.attendance_daily_report.view')
                <div class="px-3 py-2 no-print border-bottom">
                    <button type="submit" form="employeeProfileFilters" name="attendance_report" value="1" data-employee-report class="btn btn-outline-primary btn-sm">
                        <i class="link-icon" data-feather="file-text"></i> {{ __('index.attendance_confirmation_report') }}
                    </button>
                </div>
            @endcan
            <div id="employeeProfileSearchLoading" class="text-muted small d-none px-3 py-2" role="status">{{ __('index.loading') }}</div>
            <div id="employeeProfileSearchError" class="alert alert-danger m-3 d-none" role="alert">{{ __('index.error_loading_employees') }}</div>
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
