@extends('layouts.master')

@section('title', __('daily_records.branch_route_management'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/daily-records.css') }}?v={{ filemtime(public_path('assets/css/daily-records.css')) }}">
    <style>
        .route-kpi { border:1px solid #e8edf3; border-radius:12px; background:#fff; padding:16px; height:100%; }
        .route-kpi-label { color:#64748b; font-size:.82rem; font-weight:600; }
        .route-kpi-value { color:#172033; font-size:1.65rem; font-weight:750; line-height:1.15; margin-top:7px; }
        .route-detail { min-width:280px; max-width:520px; }
        .route-stop { position:relative; padding:0 0 16px 22px; border-left:2px solid #dbe4ef; }
        .route-stop:last-child { padding-bottom:0; border-left-color:transparent; }
        .route-stop:before { content:''; position:absolute; left:-6px; top:3px; width:10px; height:10px; border-radius:50%; background:#2563eb; }
        .route-stop.departure:before { background:#f59e0b; }
    </style>
@endsection

@section('main-content')
<div class="dr-container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.daily-records.index') }}">{{ __('daily_records.work_and_operations') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('daily_records.branch_route_management') }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a class="dr-btn-outline" href="{{ route('admin.daily-records.index') }}">
                <i class="fa-solid fa-list-check"></i>
                <span>{{ __('daily_records.task_and_report_center') }}</span>
            </a>
            <a class="dr-btn-outline" href="{{ route('admin.daily-records.submissions') }}">
                <i class="fa-regular fa-calendar-check"></i>
                <span>{{ __('daily_records.daily_submissions') }}</span>
            </a>
            <a class="dr-btn-outline" href="{{ route('admin.daily-records.categories') }}">
                <i class="fa-solid fa-sliders"></i>
                <span>{{ __('daily_records.categories') }}</span>
            </a>
        </div>
    </div>

    <div class="dr-hero-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 position-relative" style="z-index:1">
            <div class="d-flex align-items-center gap-3">
                <div class="dr-hero-icon-box"><i class="fa-solid fa-route"></i></div>
                <div>
                    <h1 class="dr-hero-title">{{ __('daily_records.branch_route_management') }}</h1>
                    <p class="dr-hero-subtitle mb-0">{{ __('daily_records.branch_routes_subtitle') }}</p>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a class="dr-btn-primary" href="{{ route('admin.daily-records.create') }}">
                    <i class="fa-solid fa-plus"></i>
                    <span>{{ __('daily_records.assign_task') }}</span>
                </a>
                <a class="dr-btn-outline" href="{{ route('admin.daily-records.branch-routes', request()->query()) }}">
                    <i class="fa-solid fa-rotate"></i>
                    <span>{{ __('index.refresh') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3" aria-label="{{ __('daily_records.branch_routes_overview') }}">
        @foreach ([
            ['label' => __('daily_records.branch_routes_total'), 'value' => $summary['total'], 'icon' => 'fa-route', 'color' => 'text-primary'],
            ['label' => __('daily_records.branch_routes_completed'), 'value' => $summary['completed'], 'icon' => 'fa-circle-check', 'color' => 'text-success'],
            ['label' => __('daily_records.branch_routes_cancelled'), 'value' => $summary['cancelled'], 'icon' => 'fa-ban', 'color' => 'text-secondary'],
            ['label' => __('daily_records.branch_routes_employees'), 'value' => $summary['employees'], 'icon' => 'fa-users', 'color' => 'text-info'],
        ] as $metric)
            <div class="col-6 col-xl-3"><div class="route-kpi">
                <div class="d-flex align-items-center justify-content-between gap-2"><span class="route-kpi-label">{{ $metric['label'] }}</span><i class="fa-solid {{ $metric['icon'] }} {{ $metric['color'] }}"></i></div>
                <div class="route-kpi-value">{{ number_format($metric['value']) }}</div>
            </div></div>
        @endforeach
    </div>

    <div class="dr-card">
        <div class="dr-card-header"><h2 class="dr-card-title"><i class="fa-solid fa-sliders text-primary"></i> {{ __('daily_records.branch_routes_filter') }}</h2></div>
        <div class="dr-card-body">
            <form method="get" action="{{ route('admin.daily-records.branch-routes') }}" class="row g-3 align-items-end">
                <div class="col-sm-6 col-lg-2"><label class="dr-form-label">{{ __('daily_records.status') }}</label>
                    <select class="dr-form-select" name="status">
                        <option value="">{{ __('daily_records.all_statuses') }}</option>
                        <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>{{ __('daily_records.branch_routes_completed') }}</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>{{ __('daily_records.branch_routes_cancelled') }}</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3"><label class="dr-form-label">{{ __('daily_records.department') }}</label>
                    <select class="dr-form-select" name="department_id">
                        <option value="">{{ __('daily_records.all_departments') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3"><label class="dr-form-label">{{ __('daily_records.location_branch') }}</label>
                    <select class="dr-form-select" name="branch_id">
                        <option value="">{{ __('daily_records.all_locations') }}</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(($filters['branch_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2"><label class="dr-form-label">{{ __('daily_records.report_date_ict') }}</label><input class="dr-form-control" type="date" name="date" value="{{ $filters['date'] ?? '' }}"></div>
                <div class="col-sm-6 col-lg-2"><label class="dr-form-label">{{ __('daily_records.from_ict') }}</label><input class="dr-form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
                <div class="col-sm-6 col-lg-2"><label class="dr-form-label">{{ __('daily_records.through_ict') }}</label><input class="dr-form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
                <div class="col-sm-6 col-lg-3"><label class="dr-form-label">{{ __('daily_records.employee_search') }}</label><input class="dr-form-control" name="search" maxlength="100" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('daily_records.type_employee_name') }}"></div>
                <div class="col-sm-6 col-lg-1 d-flex gap-2"><button class="dr-btn-primary" type="submit" aria-label="{{ __('daily_records.apply_filters') }}"><i class="fa-solid fa-filter"></i></button><a class="dr-btn-outline" href="{{ route('admin.daily-records.branch-routes') }}" aria-label="{{ __('daily_records.reset_filters') }}"><i class="fa-solid fa-rotate-left"></i></a></div>
            </form>
        </div>
    </div>

    <div class="dr-card">
        <div class="dr-card-header">
            <div class="d-flex align-items-center gap-2"><h2 class="dr-card-title"><i class="fa-solid fa-list-check text-primary"></i> {{ __('daily_records.branch_routes_list') }}</h2><span class="dr-meta-badge">{{ __('daily_records.total_count', ['count' => $rows->total()]) }}</span></div>
        </div>
        <div class="dr-card-body p-0"><div class="dr-table-responsive">
            <table class="dr-table">
                <thead><tr><th>{{ __('daily_records.employee') }}</th><th>{{ __('daily_records.branch_route') }}</th><th>{{ __('daily_records.reporting_date_ict') }}</th><th>{{ __('daily_records.status') }}</th><th>{{ __('daily_records.branch_route_duration') }}</th><th>{{ __('daily_records.actions') }}</th></tr></thead>
                <tbody>
                @forelse ($rows as $row)
                    @php
                        $route = $row->route;
                        $checkpoints = $route['checkpoints_data'] ?? [];
                        $status = $route['status'] ?? 'completed';
                        $badge = $status === 'completed' ? 'dr-badge-success' : 'dr-badge-secondary';
                    @endphp
                    <tr>
                        <td><strong>{{ $row->employee }}</strong><div class="small text-muted">{{ $row->title }}</div></td>
                        <td><div class="fw-semibold">{{ $route['origin_branch'] ?? '—' }} <i class="fa-solid fa-arrow-right-long mx-1 text-muted"></i> {{ implode(', ', $route['target_branches'] ?? []) ?: '—' }}</div><div class="small text-muted">{{ $route['purpose'] ?? '' }}</div></td>
                        <td><div>{{ \App\Modules\DailyRecords\RecordTime::display($row->occurred_at) }} ICT</div><div class="small text-muted">{{ __('daily_records.branch_route_stops_count', ['count' => count($checkpoints)]) }}</div></td>
                        <td><span class="dr-badge {{ $badge }}"><span class="dr-badge-dot"></span>{{ __('daily_records.branch_routes_'.$status) }}</span></td>
                        <td>{{ $route['formatted_duration'] ?? __('daily_records.minutes_count', ['count' => $route['total_minutes'] ?? 0]) }}</td>
                        <td><details class="route-detail"><summary class="dr-btn-outline btn-sm">{{ __('daily_records.branch_route_details') }}</summary>
                            <div class="border rounded p-3 mt-2 bg-light">
                                <div class="small mb-2"><strong>{{ __('daily_records.branch_route_departure_time') }}:</strong> {{ $route['departure_time'] ?? '—' }} · {{ $route['mission_mode'] ?? '—' }}</div>
                                <div class="small mb-2"><strong>{{ __('daily_records.branch_route_arrival_time') }}:</strong> {{ $route['arrival_time'] ?? '—' }}</div>
                                @if (!empty($route['start_latitude']) && !empty($route['start_longitude']))
                                    <a class="small d-block mb-2" target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $route['start_latitude'] }},{{ $route['start_longitude'] }}"><i class="fa-solid fa-map-pin"></i> {{ __('daily_records.branch_route_start_location') }}</a>
                                @endif
                                <div class="mb-3">
                                    @foreach ($checkpoints as $checkpoint)
                                        <div class="route-stop {{ ($checkpoint['action_type'] ?? '') === 'departure' ? 'departure' : '' }}">
                                            <strong>{{ $checkpoint['branch_name'] ?? '—' }}</strong>
                                            <span class="badge bg-light text-dark">{{ __('daily_records.branch_route_'.$checkpoint['action_type']) }}</span>
                                            <div class="small text-muted">{{ $checkpoint['arrived_at'] ?? '' }}</div>
                                            @if (!empty($checkpoint['notes']))<div class="small">{{ $checkpoint['notes'] }}</div>@endif
                                            @if (!empty($checkpoint['latitude']) && !empty($checkpoint['longitude']))<a class="small" target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $checkpoint['latitude'] }},{{ $checkpoint['longitude'] }}">{{ __('daily_records.branch_route_open_map') }}</a>@endif
                                        </div>
                                    @endforeach
                                </div>
                                @if (!empty($route['final_summary']))<p class="small text-break">{{ $route['final_summary'] }}</p>@endif
                                @if (!empty($route['supporting_links']))<div class="mb-2">@foreach ($route['supporting_links'] as $link)<a class="d-block small" target="_blank" rel="noopener" href="{{ $link }}">{{ $link }}</a>@endforeach</div>@endif
                                @if ($row->attachments->isNotEmpty())<div class="border-top pt-2">@foreach ($row->attachments as $file)<a class="d-block small" href="{{ route('admin.daily-records.record-file', [$row->id, $file->id]) }}"><i class="fa-solid fa-paperclip"></i> {{ $file->name }} ({{ number_format($file->bytes / 1024, 1) }} KB)</a>@endforeach</div>@endif
                            </div>
                        </details></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="dr-empty-state"><div class="dr-empty-icon"><i class="fa-solid fa-route"></i></div><div class="dr-empty-title">{{ __('daily_records.branch_routes_empty') }}</div><p class="dr-empty-desc">{{ __('daily_records.branch_routes_empty_help') }}</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($rows->hasPages())<div class="p-3 border-top">{{ $rows->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
