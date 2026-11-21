@extends('layouts.master')

@section('title', 'Task & Report Center')

@section('main-content')
<div class="d-grid gap-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h1 class="h4 mb-0">Task &amp; Report Center</h1>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary" href="{{ route('admin.daily-records.create') }}">+ Assign Task</a>
            <a class="btn btn-outline-primary" href="{{ route('admin.daily-records.submissions') }}">Daily submissions</a>
            @if ($categorySettings)<a class="btn btn-outline-secondary" href="{{ route('admin.daily-records.categories') }}">Category settings</a>@endif
            <a class="btn btn-outline-primary" href="{{ route('admin.daily-records.index', array_merge($filters, ['status' => 'review'])) }}">Review Reports</a>
            <a class="btn btn-outline-primary" href="{{ route('admin.daily-records.index', array_merge($filters, ['status' => 'overdue'])) }}">View Overdue</a>
            <a class="btn btn-outline-primary" href="{{ route('admin.daily-records.index', ['view' => 'timeline']) }}">View Employee Work</a>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success mb-0" role="status">{{ session('success') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-0" role="alert"><ul class="mb-0">
            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul></div>
    @endif

    <nav class="d-flex flex-wrap gap-2" aria-label="Report Center views">
        @foreach (['tasks' => 'Tasks', 'daily' => 'Daily Report', 'monthly' => 'Monthly Report', 'performance' => 'Employee Performance', 'calendar' => 'Task Calendar', 'department' => 'Departments', 'location' => 'Locations', 'timeline' => 'Timeline'] as $key => $label)
            <a class="btn btn-sm {{ $mode === $key ? 'btn-primary' : 'btn-outline-secondary' }}"
                @if ($mode === $key) aria-current="page" @endif
                href="{{ route('admin.daily-records.index', array_merge($filters, ['view' => $key])) }}">{{ $label }}</a>
        @endforeach
    </nav>

    <nav class="d-flex flex-wrap gap-2" aria-label="Operational reports">
        @foreach ([
            'Daily activity' => ['view' => 'daily', 'kind' => 'activity'],
            'Daily incidents' => ['view' => 'daily', 'kind' => 'incident'],
            'Monthly activity' => ['view' => 'monthly', 'kind' => 'activity'],
            'Monthly incidents' => ['view' => 'monthly', 'kind' => 'incident'],
            'Task completion' => ['view' => 'monthly', 'kind' => 'task'],
            'Outstanding incidents' => ['kind' => 'incident', 'status' => 'outstanding_incidents'],
        ] as $label => $selection)
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.daily-records.index', $selection) }}">{{ $label }}</a>
        @endforeach
    </nav>

    <div class="card"><div class="card-body">
        <form method="GET" action="{{ route('admin.daily-records.index') }}" class="row g-2 align-items-end">
            <input type="hidden" name="view" value="{{ $mode }}">
            <label class="col-sm-6 col-lg-3">Search
                <input class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" placeholder="Employee or title">
            </label>
            <div class="col-sm-6 col-lg-3">
                @include('admin.daily-records.employee-picker', ['pickerName' => 'employee_id', 'pickerSelected' => $employeeLabel ? [['id' => $filters['employee_id'], 'name' => $employeeLabel]] : []])
            </div>
            <label class="col-sm-6 col-lg-3">Department
                <select class="form-select" name="department_id"><option value="">All departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="col-sm-6 col-lg-3">Location
                <select class="form-select" name="branch_id"><option value="">All locations</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(($filters['branch_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="col-sm-6 col-lg-3">Record type
                <select class="form-select" name="kind"><option value="">All record types</option>
                    @foreach (['activity','incident','task','report','note','extension'] as $kind)
                        <option value="{{ $kind }}" @selected(($filters['kind'] ?? null) === $kind)>{{ ucfirst($kind) }}</option>
                    @endforeach
                </select>
            </label>
            @foreach (['category_id'=>'Category','subcategory_id'=>'Subcategory'] as $field=>$caption)
                <label class="col-sm-6 col-lg-3">{{ $caption }}
                    <select class="form-select" name="{{ $field }}"><option value="">All</option>
                        @foreach ($catalog->filter(fn ($node) => $field === 'category_id' ? !$node->parent_id : $node->parent_id) as $node)
                            <option value="{{ $node->id }}" @selected(($filters[$field] ?? null) == $node->id)>{{ $node->name_en }}</option>
                        @endforeach
                    </select>
                </label>
            @endforeach
            <label class="col-sm-6 col-lg-3">Priority
                <select name="priority" class="form-select"><option value="">All</option>
                    @foreach (['low','normal','high','urgent'] as $priority)
                        <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="col-sm-6 col-lg-3">Reviewer ID
                <input class="form-control" name="reviewer_id" type="number" min="1" value="{{ $filters['reviewer_id'] ?? '' }}">
            </label>
            <label class="col-sm-6 col-lg-3">Status
                <select class="form-select" name="status"><option value="">All statuses</option>
                    @foreach (array_unique([...\App\Modules\DailyRecords\Tasks::STATUSES, ...\App\Modules\DailyRecords\WorkRecords::INCIDENT_STATES, 'under_review', 'outstanding_incidents', 'overdue', 'archived', 'review', 'pending', 'open', 'resolved', 'declined'] ) as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </label>
            @if ($mode === 'daily')
                <label class="col-sm-6 col-lg-3">Report date (ICT)
                    <input class="form-control" type="date" name="date" value="{{ $filters['date'] ?? now('Asia/Phnom_Penh')->toDateString() }}">
                </label>
            @elseif (in_array($mode, ['monthly', 'performance', 'calendar']))
                <label class="col-sm-6 col-lg-3">Report month (ICT)
                    <input class="form-control" type="month" name="month" value="{{ $filters['month'] ?? now('Asia/Phnom_Penh')->format('Y-m') }}">
                </label>
            @else
                <label class="col-sm-6 col-lg-2">From (ICT)
                    <input class="form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                </label>
                <label class="col-sm-6 col-lg-2">Through (ICT)
                    <input class="form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                </label>
            @endif
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-primary" type="submit">{{ in_array($mode, ['daily', 'monthly']) ? 'Generate '.ucfirst($mode).' Report' : 'Apply filters' }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.daily-records.index', ['view' => $mode]) }}">Clear</a>
            </div>
        </form>
    </div></div>

    <div class="d-flex gap-2 align-items-center flex-wrap">
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.daily-records.export', $filters + ['format' => 'xlsx', 'view' => $mode]) }}">Export Excel</a>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.daily-records.export', $filters + ['format' => 'pdf', 'view' => $mode]) }}">Export PDF</a>
        <a class="btn btn-sm btn-outline-secondary" href="{{ request()->fullUrlWithoutQuery(['fragment']) }}">Refresh</a>
        @if (in_array($mode, ['daily', 'monthly', 'performance']))
            <span class="small text-muted">Asia/Phnom_Penh. Pending and overdue figures reflect current task status. On-time completion is measured across approved assignments.</span>
        @endif
    </div>

    <div id="daily-record-results" class="d-grid gap-3">
        @include('admin.daily-records.results')
    </div>
</div>
@if ($assignment || $record)
    @include('admin.daily-records.detail')
@endif
@include('admin.daily-records.picker-script')
@if ($mode === 'tasks' && !$assignment && !$record)
<script>
(() => {
    const panel = document.getElementById('daily-record-results');
    const url = new URL(window.location.href);
    url.searchParams.set('fragment', '1');
    let loading = false;
    window.setInterval(async () => {
        if (loading || document.hidden || panel.contains(document.activeElement) || panel.querySelector('input:checked')) return;
        try {
            loading = true;
            const response = await fetch(url, {headers: {Accept: 'text/html'}, cache: 'no-store'});
            if (!response.ok || response.redirected) return;
            const html = await response.text();
            if (!panel.contains(document.activeElement) && !panel.querySelector('input:checked')) panel.innerHTML = html;
        } catch (_) {
            // The Refresh link remains available.
        } finally {
            loading = false;
        }
    }, 15000);
})();
</script>
@endif
@endsection
