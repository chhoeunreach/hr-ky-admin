{{-- KPI Metric Summary Cards --}}
@if (!empty($summary))
    <div class="dr-summary-grid">
        @foreach ($summary as $label => $count)
            @php
                $lowerLabel = strtolower(trim($label));
                $kpiKey = 'kpi_' . str_replace([' ', '-'], '_', $lowerLabel);
                $translatedLabel = __('daily_records.' . $kpiKey);
                if ($translatedLabel === 'daily_records.' . $kpiKey) {
                    $translatedLabel = ucfirst($label);
                }

                $statConfig = ['icon' => 'fa-solid fa-chart-simple', 'class' => 'dr-stat-primary'];
                if (str_contains($lowerLabel, 'assigned') || str_contains($lowerLabel, 'tasks')) {
                    $statConfig = ['icon' => 'fa-solid fa-list-check', 'class' => 'dr-stat-primary'];
                } elseif (str_contains($lowerLabel, 'approved') || str_contains($lowerLabel, 'completed') || str_contains($lowerLabel, 'on-time') || str_contains($lowerLabel, 'on time')) {
                    $statConfig = ['icon' => 'fa-solid fa-circle-check', 'class' => 'dr-stat-success'];
                } elseif (str_contains($lowerLabel, 'progress')) {
                    $statConfig = ['icon' => 'fa-solid fa-spinner', 'class' => 'dr-stat-info'];
                } elseif (str_contains($lowerLabel, 'submitted') || str_contains($lowerLabel, 'review') || str_contains($lowerLabel, 'pending')) {
                    $statConfig = ['icon' => 'fa-solid fa-inbox', 'class' => 'dr-stat-warning'];
                } elseif (str_contains($lowerLabel, 'overdue') || str_contains($lowerLabel, 'returned') || str_contains($lowerLabel, 'critical') || str_contains($lowerLabel, 'declined')) {
                    $statConfig = ['icon' => 'fa-solid fa-triangle-exclamation', 'class' => 'dr-stat-danger'];
                } elseif (str_contains($lowerLabel, 'incident')) {
                    $statConfig = ['icon' => 'fa-solid fa-shield-halved', 'class' => 'dr-stat-purple'];
                }
            @endphp
            <div class="dr-stat-card {{ $statConfig['class'] }}">
                <div class="dr-stat-header">
                    <span class="dr-stat-label">{{ $translatedLabel }}</span>
                    <div class="dr-stat-icon">
                        <i class="{{ $statConfig['icon'] }}"></i>
                    </div>
                </div>
                <div class="dr-stat-value">{{ number_format($count) }}</div>
            </div>
        @endforeach
    </div>
@endif

{{-- TIMELINE VIEW --}}
@if ($timeline)
    <div class="dr-card">
        <div class="dr-card-header">
            <h2 class="dr-card-title">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i> {{ __('daily_records.employee_work_timeline') }}
            </h2>
            <span class="dr-meta-badge">{{ __('daily_records.activity_log') }}</span>
        </div>
        <div class="dr-card-body">
            @if ($timeline->count())
                <div class="dr-timeline">
                    @foreach ($timeline as $activity)
                        @php
                            $actionLower = strtolower($activity->action ?? '');
                            $nodeIcon = 'fa-solid fa-circle-info';
                            if (str_contains($actionLower, 'approve')) $nodeIcon = 'fa-solid fa-check';
                            elseif (str_contains($actionLower, 'submit')) $nodeIcon = 'fa-solid fa-paper-plane';
                            elseif (str_contains($actionLower, 'assign') || str_contains($actionLower, 'create')) $nodeIcon = 'fa-solid fa-plus';
                            elseif (str_contains($actionLower, 'return') || str_contains($actionLower, 'reject')) $nodeIcon = 'fa-solid fa-rotate-left';
                            elseif (str_contains($actionLower, 'incident')) $nodeIcon = 'fa-solid fa-triangle-exclamation';

                            $initials = collect(explode(' ', $activity->employee ?? 'Staff'))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                        @endphp
                        <div class="dr-timeline-item">
                            <div class="dr-timeline-node">
                                <i class="{{ $nodeIcon }}"></i>
                            </div>
                            <div class="dr-timeline-content">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="dr-avatar-circle" style="width: 26px; height: 26px; font-size: 0.7rem;">
                                            {{ $initials }}
                                        </div>
                                        <span class="fw-bold text-dark">{{ $activity->employee }}</span>
                                        <span class="dr-badge dr-badge-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                        </span>
                                    </div>
                                    <span class="dr-meta-badge">
                                        <i class="fa-regular fa-clock me-1"></i>
                                        {{ \App\Modules\DailyRecords\RecordTime::display($activity->created_at) }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <a class="fw-semibold text-primary text-decoration-none" href="{{ request()->fullUrlWithQuery(['assignment' => $activity->entry_id ? null : $activity->assignment_id, 'entry' => $activity->entry_id, 'history' => null]) }}">
                                        {{ $activity->title }}
                                    </a>
                                    @if ($activity->status)
                                        <span class="dr-badge dr-badge-info">
                                            <span class="dr-badge-dot"></span>
                                            {{ ucfirst(str_replace('_', ' ', $activity->status)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="pt-3">
                    {{ $timeline->links() }}
                </div>
            @else
                <div class="dr-empty-state">
                    <div class="dr-empty-icon"><i class="fa-solid fa-timeline"></i></div>
                    <div class="dr-empty-title">{{ __('daily_records.no_timeline_activity') }}</div>
                    <p class="dr-empty-desc">{{ __('daily_records.no_timeline_activity_desc') }}</p>
                </div>
            @endif
        </div>
    </div>

{{-- GROUP INDICATORS VIEW (PERFORMANCE, DEPARTMENT, LOCATION) --}}
@elseif ($groups)
    <div class="dr-card">
        <div class="dr-card-header">
            <div>
                <h2 class="dr-card-title">
                    <i class="fa-solid fa-chart-pie text-primary"></i>
                    {{ [
                        'performance' => __('daily_records.employee_indicators'),
                        'department' => __('daily_records.department_indicators'),
                        'location' => __('daily_records.location_indicators')
                    ][$mode] }}
                </h2>
                @if ($mode === 'performance')
                    <p class="text-muted small mb-0 mt-1">{{ __('daily_records.indicator_subtitle') }}</p>
                @endif
            </div>
            <span class="dr-meta-badge">{{ __('daily_records.entities_count', ['count' => $groups->total()]) }}</span>
        </div>
        <div class="dr-card-body p-0">
            <div class="dr-table-responsive">
                <table class="dr-table">
                    <thead>
                        <tr>
                            <th>{{ $mode === 'performance' ? __('daily_records.employee') : ($mode === 'department' ? __('daily_records.department') : __('daily_records.location_branch')) }}</th>
                            <th class="text-center">{{ __('daily_records.assigned_col') }}</th>
                            <th class="text-center">{{ __('daily_records.approved_col') }}</th>
                            <th style="min-width: 170px;">{{ __('daily_records.ontime_completion_col') }}</th>
                            <th class="text-center">{{ __('daily_records.overdue_col') }}</th>
                            <th class="text-center">{{ __('daily_records.returned_col') }}</th>
                            <th class="text-center">{{ __('daily_records.awaiting_review_col') }}</th>
                            <th class="text-center">{{ __('daily_records.incidents_resolved_col') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groups as $group)
                            @php
                                $field = ['performance' => 'employee_id', 'department' => 'department_id', 'location' => 'branch_id'][$mode];
                                $rate = $group->approved ? round(100 * $group->on_time / $group->approved, 1) : null;
                                $rateClass = 'bg-primary';
                                if ($rate !== null) {
                                    if ($rate >= 90) $rateClass = 'bg-success';
                                    elseif ($rate >= 75) $rateClass = 'bg-info';
                                    elseif ($rate >= 60) $rateClass = 'bg-warning';
                                    else $rateClass = 'bg-danger';
                                }
                                $initials = collect(explode(' ', $group->label ?? 'G'))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                            @endphp
                            <tr>
                                <th scope="row">
                                    <div class="dr-avatar-pill">
                                        <div class="dr-avatar-circle">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            @if ($group->group_id)
                                                <a class="dr-emp-name" href="{{ route('admin.daily-records.index', array_merge($filters, ['view' => $mode === 'performance' ? 'monthly' : 'tasks', $field => $group->group_id])) }}">
                                                    {{ $group->label }}
                                                </a>
                                            @else
                                                <span class="text-muted fst-italic">{{ __('daily_records.unspecified') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </th>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $group->new_assignments }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $group->approved }}</span>
                                </td>
                                <td>
                                    @if ($rate !== null)
                                        <div class="dr-progress-wrapper">
                                            <div class="dr-progress-track">
                                                <div class="dr-progress-fill {{ $rate >= 90 ? 'fill-complete' : '' }}" style="width: {{ $rate }}%;"></div>
                                            </div>
                                            <span class="dr-progress-text fw-bold">{{ $rate }}%</span>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($group->overdue > 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $group->overdue }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($group->returned > 0)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ $group->returned }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($group->submitted > 0)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $group->submitted }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($group->resolved > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $group->resolved }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="dr-empty-state">
                                        <div class="dr-empty-icon"><i class="fa-solid fa-chart-column"></i></div>
                                        <div class="dr-empty-title">{{ __('daily_records.no_summary_indicators') }}</div>
                                        <p class="dr-empty-desc">{{ __('daily_records.no_summary_indicators_desc') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($groups->hasPages())
                <div class="p-3 border-top">
                    {{ $groups->links() }}
                </div>
            @endif
        </div>
    </div>

{{-- CALENDAR VIEW --}}
@elseif ($mode === 'calendar')
    <div class="dr-card">
        <div class="dr-card-header">
            <h2 class="dr-card-title">
                <i class="fa-solid fa-calendar-check text-primary"></i> {{ __('daily_records.task_deadlines_schedule') }}
            </h2>
            <span class="dr-meta-badge">{{ __('daily_records.scheduled_deadlines', ['count' => $tasks->total()]) }}</span>
        </div>
        <div class="dr-card-body">
            @php
                $groupedTasks = $tasks->getCollection()->groupBy(fn ($task) => \App\Modules\DailyRecords\RecordTime::day($task->due_at));
            @endphp
            @forelse ($groupedTasks as $day => $agenda)
                <div class="dr-calendar-day-card">
                    <div class="dr-calendar-day-header">
                        <span class="dr-calendar-date-badge">
                            <i class="fa-regular fa-calendar-days me-1"></i> {{ $day }}
                        </span>
                        <span class="dr-meta-badge">{{ __('daily_records.tasks_count', ['count' => $agenda->count(), 'label' => \Illuminate\Support\Str::plural('task', $agenda->count())]) }}</span>
                    </div>
                    <div class="d-grid gap-2">
                        @foreach ($agenda as $item)
                            @php
                                $itemInitials = collect(explode(' ', $item->employee ?? 'Staff'))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                            @endphp
                            <div class="dr-calendar-task-item">
                                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-regular fa-clock me-1 text-primary"></i>
                                        {{ substr(\App\Modules\DailyRecords\RecordTime::display($item->due_at), 11) }}
                                    </span>
                                    <a class="fw-semibold text-dark text-truncate text-decoration-none" href="{{ request()->fullUrlWithQuery(['assignment' => $item->id]) }}">
                                        {{ $item->title }}
                                    </a>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="dr-avatar-circle" style="width: 24px; height: 24px; font-size: 0.65rem;">
                                            {{ $itemInitials }}
                                        </div>
                                        <span class="small text-muted d-none d-sm-inline">{{ $item->employee }}</span>
                                    </div>
                                    <span class="dr-badge {{ match($item->priority) { 'urgent' => 'dr-priority-urgent', 'high' => 'dr-priority-high', 'normal' => 'dr-priority-normal', default => 'dr-priority-low' } }}">
                                        {{ __('daily_records.priority_' . $item->priority) !== 'daily_records.priority_' . $item->priority ? __('daily_records.priority_' . $item->priority) : ucfirst($item->priority) }}
                                    </span>
                                    <span class="dr-badge dr-badge-info">
                                        <span class="dr-badge-dot"></span>
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="dr-empty-state">
                    <div class="dr-empty-icon"><i class="fa-regular fa-calendar-xmark"></i></div>
                    <div class="dr-empty-title">{{ __('daily_records.no_deadlines_month') }}</div>
                    <p class="dr-empty-desc">{{ __('daily_records.no_deadlines_month_desc') }}</p>
                </div>
            @endforelse
            @if ($tasks->hasPages())
                <div class="pt-3 border-top">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>
    </div>

{{-- DEFAULT VIEW: TASKS & DAILY RECORDS TABLES --}}
@else
    {{-- TASKS TABLE --}}
    <div class="dr-card">
        <form method="POST" action="{{ route('admin.daily-records.bulk-review') }}">
            @csrf
            <div class="dr-card-header">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="dr-card-title">
                        <i class="fa-solid fa-list-check text-primary"></i> {{ __('daily_records.employee_tasks') }}
                    </h2>
                    <span class="dr-meta-badge">{{ __('daily_records.total_count', ['count' => $tasks->total()]) }}</span>
                </div>
                @if ($tasks->getCollection()->contains('status', 'submitted'))
                    <button class="dr-btn-primary btn-sm py-1 px-3" type="submit">
                        <i class="fa-solid fa-check-double"></i>
                        <span>{{ __('daily_records.approve_selected_reports') }}</span>
                    </button>
                @endif
            </div>

            <div class="dr-card-body p-0">
                <div class="dr-table-responsive">
                    <table class="dr-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="text-center">
                                    <span class="visually-hidden">Select</span>
                                    <i class="fa-regular fa-square-check text-muted"></i>
                                </th>
                                <th>{{ __('daily_records.employee') }}</th>
                                <th>{{ __('daily_records.dept_location') }}</th>
                                <th>{{ __('daily_records.task_title') }}</th>
                                <th>{{ __('daily_records.priority') }}</th>
                                <th>{{ __('daily_records.assigned_ict') }}</th>
                                <th>{{ __('daily_records.deadline_ict') }}</th>
                                <th>{{ __('daily_records.status') }}</th>
                                <th style="min-width: 120px;">{{ __('daily_records.progress') }}</th>
                                <th>{{ __('daily_records.last_update') }}</th>
                                <th class="text-end">{{ __('daily_records.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tasks as $task)
                                @php
                                    $overdue = !$task->completed_at && !$task->archived_at && !$task->superseded_at
                                        && in_array($task->status, \App\Modules\DailyRecords\Tasks::INCOMPLETE)
                                        && $task->due_at < $overdueAt;
                                    $initials = collect(explode(' ', $task->employee ?? 'Staff'))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                                    $progress = (int) ($task->progress ?? 0);
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        @if ($task->pending_report_id && !$task->archived_at)
                                            <input type="checkbox" class="form-check-input" name="approvals[{{ $task->id }}]" value="{{ $task->pending_report_id }}" aria-label="Select {{ $task->title }} for {{ $task->employee }}">
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dr-avatar-pill">
                                            <div class="dr-avatar-circle">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <a class="dr-emp-name" href="{{ route('admin.daily-records.index', ['view' => 'timeline', 'employee_id' => $task->employee_id]) }}">
                                                    {{ $task->employee }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">{{ $task->department ?: '—' }}</div>
                                        <div class="small text-muted">{{ $task->location ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $task->title }}</span>
                                    </td>
                                    <td>
                                        <span class="dr-badge {{ match($task->priority) { 'urgent' => 'dr-priority-urgent', 'high' => 'dr-priority-high', 'normal' => 'dr-priority-normal', default => 'dr-priority-low' } }}">
                                            @if ($task->priority === 'urgent')
                                                <i class="fa-solid fa-fire me-1"></i>
                                            @endif
                                            {{ __('daily_records.priority_' . $task->priority) !== 'daily_records.priority_' . $task->priority ? __('daily_records.priority_' . $task->priority) : ucfirst($task->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            <i class="fa-regular fa-calendar me-1"></i>
                                            {{ \App\Modules\DailyRecords\RecordTime::display($task->created_at) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold {{ $overdue ? 'text-danger' : 'text-dark' }}">
                                            <i class="fa-regular fa-clock me-1"></i>
                                            {{ \App\Modules\DailyRecords\RecordTime::display($task->due_at) }}
                                        </span>
                                        @if ($overdue)
                                            <span class="dr-overdue-tag">
                                                <i class="fa-solid fa-triangle-exclamation"></i> {{ __('daily_records.overdue') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusStyle = match($task->status) {
                                                'approved' => 'dr-badge-success',
                                                'submitted' => 'dr-badge-warning',
                                                'in_progress' => 'dr-badge-primary',
                                                'on_hold' => 'dr-badge-secondary',
                                                'cancelled', 'closed' => 'dr-badge-secondary',
                                                default => 'dr-badge-info',
                                            };
                                        @endphp
                                        <span class="dr-badge {{ $statusStyle }}">
                                            <span class="dr-badge-dot"></span>
                                            {{ __('daily_records.kpi_' . $task->status) !== 'daily_records.kpi_' . $task->status ? __('daily_records.kpi_' . $task->status) : ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dr-progress-wrapper">
                                            <div class="dr-progress-track">
                                                <div class="dr-progress-fill {{ $progress >= 100 ? 'fill-complete' : '' }}" style="width: {{ $progress }}%;"></div>
                                            </div>
                                            <span class="dr-progress-text">{{ $progress }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            {{ \App\Modules\DailyRecords\RecordTime::display($task->updated_at) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a class="dr-btn-outline py-1 px-2 text-decoration-none" href="{{ request()->fullUrlWithQuery(['assignment' => $task->id, 'entry' => null, 'fragment' => null]) }}">
                                            @if ($task->status === 'submitted')
                                                <i class="fa-solid fa-file-signature text-warning"></i>
                                                <span class="small">{{ __('daily_records.review_report') }}</span>
                                            @else
                                                <i class="fa-regular fa-folder-open text-primary"></i>
                                                <span class="small">{{ __('daily_records.details') }}</span>
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11">
                                        <div class="dr-empty-state">
                                            <div class="dr-empty-icon"><i class="fa-solid fa-list-check"></i></div>
                                            <div class="dr-empty-title">{{ __('daily_records.no_matching_tasks') }}</div>
                                            <p class="dr-empty-desc">{{ __('daily_records.no_matching_tasks_desc') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($tasks->hasPages())
                    <div class="p-3 border-top">
                        {{ $tasks->links() }}
                    </div>
                @endif
            </div>
        </form>
    </div>

    {{-- DAILY RECORDS TABLE --}}
    @if ($entries)
        <div class="dr-card">
            <div class="dr-card-header">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="dr-card-title">
                        <i class="fa-solid fa-newspaper text-primary"></i> {{ __('daily_records.operational_daily_records') }}
                    </h2>
                    <span class="dr-meta-badge">{{ __('daily_records.total_count', ['count' => $entries->total()]) }}</span>
                </div>
            </div>
            <div class="dr-card-body p-0">
                <div class="dr-table-responsive">
                    <table class="dr-table">
                        <thead>
                            <tr>
                                <th>{{ __('daily_records.employee') }}</th>
                                <th>{{ __('daily_records.task_title') }}</th>
                                <th>{{ __('daily_records.record_type') }}</th>
                                <th>{{ __('daily_records.status') }}</th>
                                <th>{{ __('daily_records.occurrence_ict') }}</th>
                                <th>{{ __('daily_records.last_update') }}</th>
                                <th class="text-end">{{ __('daily_records.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $entry)
                                @php
                                    $initials = collect(explode(' ', $entry->employee ?? 'Staff'))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                                    $kindBadgeClass = match($entry->kind) {
                                        'incident' => 'dr-badge-danger',
                                        'activity' => 'dr-badge-primary',
                                        'task' => 'dr-badge-info',
                                        'report' => 'dr-badge-warning',
                                        default => 'dr-badge-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="dr-avatar-pill">
                                            <div class="dr-avatar-circle">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <a class="dr-emp-name" href="{{ route('admin.daily-records.index', ['view' => 'timeline', 'employee_id' => $entry->employee_id]) }}">
                                                    {{ $entry->employee }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $entry->title }}</span>
                                        @if ($entry->critical)
                                            <span class="dr-badge dr-badge-danger ms-1">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ __('daily_records.kpi_critical') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="dr-badge {{ $kindBadgeClass }}">
                                            {{ __('daily_records.type_' . $entry->kind) !== 'daily_records.type_' . $entry->kind ? __('daily_records.type_' . $entry->kind) : ucfirst($entry->kind) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="dr-badge dr-badge-secondary">
                                            <span class="dr-badge-dot"></span>
                                            {{ ucfirst(str_replace('_', ' ', $entry->status ?? 'Recorded')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            <i class="fa-regular fa-clock me-1"></i>
                                            {{ \App\Modules\DailyRecords\RecordTime::display($entry->occurred_at) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            {{ \App\Modules\DailyRecords\RecordTime::display($entry->updated_at) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a class="dr-btn-outline py-1 px-2 text-decoration-none" href="{{ $entry->kind === 'incident' ? route('admin.daily-records.incident', $entry->id) : request()->fullUrlWithQuery(['entry' => $entry->id, 'assignment' => null, 'fragment' => null]) }}">
                                            @if ($entry->kind === 'incident')
                                                <i class="fa-solid fa-shield-halved text-danger"></i>
                                                <span class="small">{{ __('daily_records.manage_incident') }}</span>
                                            @else
                                                <i class="fa-regular fa-folder-open text-primary"></i>
                                                <span class="small">{{ __('daily_records.view_record') }}</span>
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="dr-empty-state">
                                            <div class="dr-empty-icon"><i class="fa-regular fa-file-lines"></i></div>
                                            <div class="dr-empty-title">{{ __('daily_records.no_daily_records') }}</div>
                                            <p class="dr-empty-desc">{{ __('daily_records.no_daily_records_desc') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($entries->hasPages())
                    <div class="p-3 border-top">
                        {{ $entries->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
