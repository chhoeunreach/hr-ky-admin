@extends('layouts.master')

@section('title', __('index.employee_profiles'))

@section('action', __('index.employee_profile'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.employees.common.breadcrumb')

        @php
            $reviewBadgeClass = fn ($status) => match ($status) {
                'Done' => 'success',
                'Upcoming' => 'info',
                'Due' => 'warning',
                'Overdue' => 'danger',
                default => 'secondary',
            };
            $reviewStatus = function ($employee, string $label, array $reviewTypes, string $reviewType, int $months = null) {
                if (!$employee->joining_date) {
                    return ['label' => $label, 'status' => 'N/A', 'date' => null, 'review_type' => $reviewType, 'period_start' => null, 'period_end' => null];
                }

                $joinDate = \Illuminate\Support\Carbon::parse($employee->joining_date)->startOfDay();
                $today = now()->startOfDay();
                $dueDate = $months ? $joinDate->copy()->addMonthsNoOverflow($months) : $joinDate->copy()->addYearNoOverflow();

                if (!$months && $today->greaterThanOrEqualTo($dueDate)) {
                    $years = max(1, $joinDate->diffInYears($today));
                    $dueDate = $joinDate->copy()->addYearsNoOverflow($years);
                    if ($dueDate->isFuture()) {
                        $dueDate->subYearNoOverflow();
                    }
                }

                $done = $employee->employeePerformanceReviews
                    ->filter(fn ($review) => in_array($review->review_type, $reviewTypes, true))
                    ->contains(fn ($review) => $review->review_date && \Illuminate\Support\Carbon::parse($review->review_date)->greaterThanOrEqualTo($dueDate->copy()->subDays(30)));

                if ($done) {
                    return ['label' => $label, 'status' => 'Done', 'date' => $dueDate->format('Y-m-d'), 'review_type' => $reviewType, 'period_start' => $joinDate->format('Y-m-d'), 'period_end' => $dueDate->format('Y-m-d')];
                }

                if ($today->lt($dueDate)) {
                    return ['label' => $label, 'status' => 'Upcoming', 'date' => $dueDate->format('Y-m-d'), 'review_type' => $reviewType, 'period_start' => $joinDate->format('Y-m-d'), 'period_end' => $dueDate->format('Y-m-d')];
                }

                return [
                    'label' => $label,
                    'status' => $today->diffInDays($dueDate) <= 14 ? 'Due' : 'Overdue',
                    'date' => $dueDate->format('Y-m-d'),
                    'review_type' => $reviewType,
                    'period_start' => $joinDate->format('Y-m-d'),
                    'period_end' => $dueDate->format('Y-m-d'),
                ];
            };
            $workingLife = function ($employee) {
                if (!$employee->joining_date) {
                    return 'N/A';
                }

                $joinDate = \Illuminate\Support\Carbon::parse($employee->joining_date)->startOfDay();
                $endDate = $employee->employee360Profile?->last_working_date
                    ? \Illuminate\Support\Carbon::parse($employee->employee360Profile->last_working_date)->startOfDay()
                    : now()->startOfDay();
                $diff = $joinDate->diff($endDate);
                $parts = [];
                if ($diff->y) {
                    $parts[] = $diff->y . 'y';
                }
                if ($diff->m) {
                    $parts[] = $diff->m . 'm';
                }
                if (!$parts) {
                    $parts[] = $diff->d . 'd';
                }

                return implode(' ', $parts);
            };
        @endphp

        <style>
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

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3 no-print">
                    <button class="btn btn-outline-secondary btn-sm"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#employeeProfileFilters"
                            aria-expanded="{{ request()->hasAny(['search', 'branch_id', 'department_id', 'post_id', 'employment_status', 'review_status', 'per_page']) ? 'true' : 'false' }}"
                            aria-controls="employeeProfileFilters">
                        {{ __('index.filter') }}
                    </button>
                    <h6 class="card-title mb-0">{{ __('index.employee_profile') }}</h6>
                    @can('employee.profile.print')
                        <button type="button" class="btn btn-outline-primary btn-sm ms-auto" onclick="window.print()">
                            {{ __('index.print') }}
                        </button>
                    @endcan
                </div>
                <h6 class="employee-profile-print-title d-none">{{ __('index.employee_profile') }}</h6>
                <form method="get" id="employeeProfileFilters" class="collapse no-print {{ request()->hasAny(['search', 'branch_id', 'department_id', 'post_id', 'employment_status', 'review_status', 'per_page']) ? 'show' : '' }}">
                    <div class="row g-2">
                        <div class="col-lg-2 col-md-6">
                            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ __('index.search_employee') }}">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select class="form-select" name="branch_id">
                                <option value="">{{ __('index.all_branches') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select class="form-select" name="department_id">
                                <option value="">{{ __('index.all_departments') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->dept_name }}</option>
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
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th class="no-print">{{ __('index.action') }}</th>
                            <th>{{ __('index.employee') }}</th>
                            <th>{{ __('index.branch') }}</th>
                            <th>{{ __('index.department') }}</th>
                            <th>{{ __('index.position') }}</th>
                            <th>{{ __('index.working_life') }}</th>
                            <th>{{ __('index.review') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($employees as $employee)
                            @php
                                $reviewMilestones = [
                                    $reviewStatus($employee, '3M', ['quarterly', 'probation'], 'quarterly', 3),
                                    $reviewStatus($employee, '6M', ['six_month'], 'six_month', 6),
                                    $reviewStatus($employee, '12M', ['annual'], 'annual', 12),
                                    $reviewStatus($employee, 'Yearly', ['annual'], 'annual', null),
                                ];
                            @endphp
                            <tr>
                                <td class="no-print">
                                    <div class="d-flex flex-wrap gap-1">
                                        <a class="btn btn-primary btn-xs" href="{{ route('admin.employees.profile.show', $employee->id) }}">{{ __('index.employee_360') }}</a>
                                        @canany(['employee.salary.view', 'employee.salary.history.view'])
                                            <a class="btn btn-outline-success btn-xs" href="{{ route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'salary-certificate']) }}">{{ __('index.salary_certificate') }}</a>
                                        @endcanany
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $employeeAvatar = $employee->avatar_url;
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $employeeAvatar }}"
                                             alt="{{ $employee->name ?: $employee->english_name }}"
                                             class="rounded-circle"
                                             style="width: 38px; height: 38px; object-fit: cover; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"
                                             loading="lazy"
                                             onerror="this.onerror=null; this.src='{{ asset('assets/images/img.png') }}';">
                                        <div>
                                            <div class="fw-semibold">{{ $employee->name ?: $employee->english_name }}</div>
                                            <div class="text-muted small">{{ $employee->employee_code ?: $employee->username }}{{ $employee->english_name ? ' · ' . $employee->english_name : '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $employee->branch?->name ?: 'N/A' }}</td>
                                <td>{{ $employee->department?->dept_name ?: 'N/A' }}</td>
                                <td>{{ $employee->post?->post_name ?: 'N/A' }}</td>
                                <td>
                                    <strong>{{ $workingLife($employee) }}</strong>
                                            <div class="text-muted small">
                                                {{ $employee->joining_date ? __('index.from') . ' ' . $employee->joining_date : __('index.no_join_date') }}
                                                @if($employee->employee360Profile?->last_working_date)
                                                    {{ __('index.to') }} {{ $employee->employee360Profile->last_working_date->format('Y-m-d') }}
                                                @endif
                                            </div>
                                </td>
                                <td style="min-width: 260px;">
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($reviewMilestones as $milestone)
                                            @php
                                                $canCreateFromBadge = in_array($milestone['status'], ['Due', 'Overdue'], true);
                                                $reviewUrl = route('admin.employees.profile.show', [
                                                     'employee' => $employee->id,
                                                     'tab' => 'evaluation',
                                                     'review_create' => 1,
                                                     'review_type' => $milestone['review_type'],
                                                     'period_start' => $milestone['period_start'],
                                                     'period_end' => $milestone['period_end'],
                                                     'review_date' => $milestone['date'],
                                                 ]);
                                                $statusLabel = $milestone['status'] === 'N/A' ? 'N/A' : __('index.' . strtolower($milestone['status']));
                                            @endphp
                                            @can('employee.performance.create')
                                                @if($canCreateFromBadge)
                                                    <a class="badge bg-{{ $reviewBadgeClass($milestone['status']) }} text-decoration-none"
                                                       href="{{ $reviewUrl }}"
                                                       title="Create review due: {{ $milestone['date'] ?: 'N/A' }}">
                                                        {{ $milestone['label'] }}: {{ $statusLabel }}
                                                    </a>
                                                @else
                                                    <span class="badge bg-{{ $reviewBadgeClass($milestone['status']) }}" title="Due: {{ $milestone['date'] ?: 'N/A' }}">
                                                        {{ $milestone['label'] }}: {{ $statusLabel }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-{{ $reviewBadgeClass($milestone['status']) }}" title="Due: {{ $milestone['date'] ?: 'N/A' }}">
                                                    {{ $milestone['label'] }}: {{ $statusLabel }}
                                                </span>
                                            @endcan
                                        @endforeach
                                    </div>
                                    <div class="text-muted small mt-1">
                                        {{ __('index.next') }}: {{ collect($reviewMilestones)->firstWhere('status', 'Upcoming')['date'] ?? collect($reviewMilestones)->firstWhere('status', 'Due')['date'] ?? collect($reviewMilestones)->firstWhere('status', 'Overdue')['date'] ?? __('index.completed') }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">{{ __('index.no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($employees, 'appends'))
                    {{ $employees->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </section>
@endsection
