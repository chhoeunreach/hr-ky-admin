@extends('layouts.master')

@section('title', 'Employee Profiles')

@section('action', 'Employee Profile')

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

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <button class="btn btn-outline-secondary btn-sm"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#employeeProfileFilters"
                            aria-expanded="{{ request()->hasAny(['search', 'branch_id', 'department_id', 'post_id', 'employment_status', 'review_status']) ? 'true' : 'false' }}"
                            aria-controls="employeeProfileFilters">
                        Filter
                    </button>
                    <h6 class="card-title mb-0">Employee Profile</h6>
                </div>
                <form method="get" id="employeeProfileFilters" class="collapse {{ request()->hasAny(['search', 'branch_id', 'department_id', 'post_id', 'employment_status', 'review_status']) ? 'show' : '' }}">
                    <div class="row g-2">
                        <div class="col-lg-3 col-md-6">
                            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search employee">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select class="form-select" name="branch_id">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select class="form-select" name="department_id">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select class="form-select" name="post_id">
                                <option value="">All Positions</option>
                                @foreach($posts as $post)
                                    <option value="{{ $post->id }}" @selected((string) request('post_id') === (string) $post->id)>{{ $post->post_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <select class="form-select" name="employment_status">
                                <option value="">All Status</option>
                                @foreach(['active', 'probation', 'suspended', 'resigned', 'terminated', 'inactive'] as $status)
                                    <option value="{{ $status }}" @selected($employmentStatus === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <select class="form-select" name="review_status">
                                <option value="">Review</option>
                                @foreach(['Due', 'Overdue', 'Upcoming', 'Done', 'N/A'] as $status)
                                    <option value="{{ $status }}" @selected(request('review_status') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6 d-flex gap-2">
                            <button class="btn btn-primary w-100">Apply</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.employees.profile.index') }}">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Action</th>
                            <th>Employee</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Working Life</th>
                            <th>Review</th>
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
                                <td>
                                    <a class="btn btn-primary btn-xs" href="{{ route('admin.employees.profile.show', $employee->id) }}">Employee 360</a>
                                </td>
                                <td>
                                    @php
                                        $employeeAvatar = $employee->avatar
                                            ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $employee->avatar)
                                            : asset('assets/images/img.png');
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $employeeAvatar }}"
                                             alt="{{ $employee->english_name ?: $employee->name }}"
                                             class="rounded-circle"
                                             style="width: 38px; height: 38px; object-fit: cover;">
                                        <div>
                                            <div class="fw-semibold">{{ $employee->english_name ?: $employee->name }}</div>
                                            <div class="text-muted small">{{ $employee->employee_code ?: $employee->username }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $employee->branch?->name ?: 'N/A' }}</td>
                                <td>{{ $employee->department?->dept_name ?: 'N/A' }}</td>
                                <td>{{ $employee->post?->post_name ?: 'N/A' }}</td>
                                <td>
                                    <strong>{{ $workingLife($employee) }}</strong>
                                            <div class="text-muted small">
                                                {{ $employee->joining_date ? 'From ' . $employee->joining_date : 'No join date' }}
                                                @if($employee->employee360Profile?->last_working_date)
                                                    to {{ $employee->employee360Profile->last_working_date->format('Y-m-d') }}
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
                                            @endphp
                                            @can('employee.performance.create')
                                                @if($canCreateFromBadge)
                                                    <a class="badge bg-{{ $reviewBadgeClass($milestone['status']) }} text-decoration-none"
                                                       href="{{ $reviewUrl }}"
                                                       title="Create review due: {{ $milestone['date'] ?: 'N/A' }}">
                                                        {{ $milestone['label'] }}: {{ $milestone['status'] }}
                                                    </a>
                                                @else
                                                    <span class="badge bg-{{ $reviewBadgeClass($milestone['status']) }}" title="Due: {{ $milestone['date'] ?: 'N/A' }}">
                                                        {{ $milestone['label'] }}: {{ $milestone['status'] }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-{{ $reviewBadgeClass($milestone['status']) }}" title="Due: {{ $milestone['date'] ?: 'N/A' }}">
                                                    {{ $milestone['label'] }}: {{ $milestone['status'] }}
                                                </span>
                                            @endcan
                                        @endforeach
                                    </div>
                                    <div class="text-muted small mt-1">
                                        Next: {{ collect($reviewMilestones)->firstWhere('status', 'Upcoming')['date'] ?? collect($reviewMilestones)->firstWhere('status', 'Due')['date'] ?? collect($reviewMilestones)->firstWhere('status', 'Overdue')['date'] ?? 'Completed' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No records found</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $employees->appends(request()->query())->links() }}
            </div>
        </div>
    </section>
@endsection
