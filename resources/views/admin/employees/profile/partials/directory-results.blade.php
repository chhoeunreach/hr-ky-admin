        @php
            $reviewBadgeClass = fn ($status) => match ($status) {
                'Done' => 'success',
                'Upcoming' => 'info',
                'Due' => 'warning',
                'Overdue' => 'danger',
                default => 'secondary',
            };
            $reviewStatus = function ($employee, string $label, array $reviewTypes, string $reviewType, ?int $months = null) {
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
                    ->contains(fn ($review) => $review->completesMilestone($dueDate));

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

            <div class="card-body" id="employeeProfileResults" aria-busy="false">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th class="no-print">{{ __('index.action') }}</th>
                            <th aria-sort="{{ request('name_direction') === 'desc' ? 'descending' : (request('name_direction') === 'asc' ? 'ascending' : 'none') }}">
                                <a data-profile-results-link class="d-flex align-items-center justify-content-between gap-2 text-reset text-decoration-none"
                                   href="{{ request()->fullUrlWithQuery(['name_direction' => request('name_direction') === 'asc' ? 'desc' : 'asc', 'working_life_direction' => null, 'page' => null]) }}">
                                    {{ __('index.employee') }}
                                    <span aria-hidden="true">{{ request('name_direction') === 'desc' ? '↓' : (request('name_direction') === 'asc' ? '↑' : '↕') }}</span>
                                </a>
                            </th>
                            <th>{{ __('index.branch') }}</th>
                            <th>{{ __('index.department') }}</th>
                            <th>{{ __('index.position') }}</th>
                            <th aria-sort="{{ request('working_life_direction') === 'desc' ? 'descending' : (request('working_life_direction') === 'asc' ? 'ascending' : 'none') }}">
                                <a data-profile-results-link class="d-flex align-items-center justify-content-between gap-2 text-reset text-decoration-none"
                                   href="{{ request()->fullUrlWithQuery(['working_life_direction' => request('working_life_direction') === 'desc' ? 'asc' : 'desc', 'name_direction' => null, 'page' => null]) }}">
                                    {{ __('index.working_life') }}
                                    <span aria-hidden="true">{{ request('working_life_direction') === 'desc' ? '↓' : (request('working_life_direction') === 'asc' ? '↑' : '↕') }}</span>
                                </a>
                            </th>
                            <th>{{ __('index.review') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($employees as $employee)
                            @php
                                $reviewMilestones = [
                                    $reviewStatus($employee, __('index.review_three_months'), ['quarterly', 'probation'], 'quarterly', 3),
                                    $reviewStatus($employee, __('index.review_six_months'), ['six_month'], 'six_month', 6),
                                    $reviewStatus($employee, __('index.review_twelve_months'), ['annual'], 'annual', 12),
                                    $reviewStatus($employee, __('index.review_yearly'), ['annual'], 'annual', null),
                                ];
                            @endphp
                            <tr>
                                <td class="no-print align-middle">
                                    <div class="dropdown employee-profile-actions">
                                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                                id="employeeProfileActions{{ $employee->id }}" data-bs-toggle="dropdown" aria-expanded="false"
                                                aria-label="{{ __('index.action') }} · {{ $employee->name ?: $employee->english_name }}">
                                            <i class="link-icon" data-feather="eye" aria-hidden="true"></i>{{ __('index.view') }}
                                        </button>
                                        <ul class="dropdown-menu employee-profile-action-menu" aria-labelledby="employeeProfileActions{{ $employee->id }}">
                                            <li><h6 class="dropdown-header text-truncate">{{ $employee->name ?: $employee->english_name }}</h6></li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            @can('edit_employee')
                                                <li>
                                                    <a class="dropdown-item" data-open-employee-form
                                                       data-employee-form-title="{{ __('index.edit') }} · {{ $employee->name ?: $employee->english_name }}"
                                                       href="{{ route('admin.employees.edit', $employee->id) }}">
                                                        <i class="link-icon text-primary" data-feather="edit" aria-hidden="true"></i>{{ __('index.edit') }}
                                                    </a>
                                                </li>
                                            @endcan
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.employees.profile.show', $employee->id) }}">
                                                    <i class="link-icon text-primary" data-feather="user" aria-hidden="true"></i>{{ __('index.employee_360') }}
                                                </a>
                                            </li>
                                            @canany(['view_salary_list', 'salary_increment', 'employee.salary.view', 'employee.salary.history.view', 'employee.salary.history.manage', 'employee.salary.history.edit', 'employee.salary.history.delete'])
                                                <li>
                                                    <a class="dropdown-item" data-open-employee-form data-employee-form-title="{{ $employee->name }} · {{ __('index.salary') }}" href="{{ route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'salary']) }}">
                                                        <i class="link-icon text-success" data-feather="dollar-sign" aria-hidden="true"></i>{{ __('index.salary') }}
                                                    </a>
                                                </li>
                                            @endcanany
                                            @canany(['employee.salary.view', 'employee.salary.history.view'])
                                                <li>
                                                    <a class="dropdown-item" data-open-employee-form href="{{ route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'salary-certificate']) }}">
                                                        <i class="link-icon text-success" data-feather="file-text" aria-hidden="true"></i>{{ __('index.salary_certificate') }}
                                                    </a>
                                                </li>
                                            @endcanany
                                        </ul>
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
                                                $canCreateFromBadge = in_array($milestone['status'], ['Upcoming', 'Due', 'Overdue'], true);
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
                                                    <button type="button" class="badge border-0 bg-{{ $reviewBadgeClass($milestone['status']) }}"
                                                            data-bs-toggle="modal" data-bs-target="#quickCompleteReviewModal"
                                                            data-action="{{ route('admin.employees.profile.reviews.store', $employee->id) }}"
                                                            data-employee="{{ $employee->name ?: $employee->english_name }}"
                                                            data-label="{{ $milestone['label'] }}"
                                                            data-review-type="{{ $milestone['review_type'] }}"
                                                            data-period-start="{{ $milestone['period_start'] }}"
                                                            data-period-end="{{ $milestone['period_end'] }}"
                                                            data-review-url="{{ $reviewUrl }}"
                                                            title="{{ __('index.quick_review_due', ['date' => $milestone['date']]) }}">
                                                        {{ $milestone['label'] }}: {{ $statusLabel }}
                                                    </button>
                                                @else
                                                    <span class="badge bg-{{ $reviewBadgeClass($milestone['status']) }}" title="{{ __('index.review_due', ['date' => $milestone['date'] ?: 'N/A']) }}">
                                                        {{ $milestone['label'] }}: {{ $statusLabel }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-{{ $reviewBadgeClass($milestone['status']) }}" title="{{ __('index.review_due', ['date' => $milestone['date'] ?: 'N/A']) }}">
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
