@extends('layouts.master')

@section('title', __('daily_records.task_and_report_center'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/daily-records.css') }}?v={{ filemtime(public_path('assets/css/daily-records.css')) }}">
@endsection

@section('main-content')
<div class="dr-container">
    {{-- Hero Header Card --}}
    <div class="dr-hero-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 position-relative" style="z-index: 1;">
            <div class="d-flex align-items-center gap-3">
                <div class="dr-hero-icon-box">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h1 class="dr-hero-title">
                        {{ __('daily_records.task_and_report_center') }}
                        <span class="dr-meta-badge">
                            <span class="dr-live-dot"></span> {{ __('daily_records.live_console') }}
                        </span>
                    </h1>
                    <p class="dr-hero-subtitle">
                        {{ __('daily_records.task_and_report_center_subtitle') }}
                    </p>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a class="dr-btn-primary" href="{{ route('admin.daily-records.create') }}">
                    <i class="fa-solid fa-plus"></i>
                    <span>{{ __('daily_records.assign_task') }}</span>
                </a>
                <a class="dr-btn-outline" href="{{ route('admin.daily-records.branch-routes') }}">
                    <i class="fa-solid fa-route"></i>
                    <span>{{ __('daily_records.branch_route_management') }}</span>
                </a>
                <a class="dr-btn-outline" href="{{ route('admin.daily-records.submissions') }}">
                    <i class="fa-regular fa-calendar-check"></i>
                    <span>{{ __('daily_records.daily_submissions') }}</span>
                </a>
                @if ($categorySettings)
                    <a class="dr-btn-outline" href="{{ route('admin.daily-records.categories') }}">
                        <i class="fa-solid fa-sliders"></i>
                        <span>{{ __('daily_records.categories') }}</span>
                    </a>
                @endif
                <div class="dropdown">
                    <button class="dr-btn-outline dropdown-toggle" type="button" id="quickViewsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-bolt"></i> {{ __('daily_records.quick_views') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="quickViewsDropdown">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('admin.daily-records.index', array_merge($filters, ['status' => 'review'])) }}">
                                <i class="fa-regular fa-clock me-2 text-warning"></i> {{ __('daily_records.review_reports') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('admin.daily-records.index', array_merge($filters, ['status' => 'overdue'])) }}">
                                <i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i> {{ __('daily_records.view_overdue') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('admin.daily-records.index', ['view' => 'timeline']) }}">
                                <i class="fa-solid fa-timeline me-2 text-primary"></i> {{ __('daily_records.view_employee_timeline') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- System Alerts --}}
    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-0 shadow-sm border-0" role="status">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2 mb-0 shadow-sm border-0" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-5 mt-1"></i>
            <ul class="mb-0 ps-2">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- View Navigation Tabs --}}
    <div class="dr-tabs-container">
        <nav class="dr-tabs-nav" aria-label="Report Center views">
            @php
                $tabConfig = [
                    'tasks' => ['label' => __('daily_records.tasks_overview'), 'icon' => 'fa-solid fa-list-check'],
                    'daily' => ['label' => __('daily_records.daily_report'), 'icon' => 'fa-solid fa-calendar-day'],
                    'monthly' => ['label' => __('daily_records.monthly_report'), 'icon' => 'fa-solid fa-calendar-days'],
                    'performance' => ['label' => __('daily_records.employee_performance'), 'icon' => 'fa-solid fa-chart-line'],
                    'calendar' => ['label' => __('daily_records.task_calendar'), 'icon' => 'fa-solid fa-calendar-check'],
                    'department' => ['label' => __('daily_records.department_summary'), 'icon' => 'fa-solid fa-building-user'],
                    'location' => ['label' => __('daily_records.location_summary'), 'icon' => 'fa-solid fa-location-dot'],
                    'timeline' => ['label' => __('daily_records.work_timeline'), 'icon' => 'fa-solid fa-clock-rotate-left'],
                ];
            @endphp
            @foreach ($tabConfig as $key => $tab)
                <a class="dr-tab-link {{ $mode === $key ? 'active' : '' }}"
                    @if ($mode === $key) aria-current="page" @endif
                    href="{{ route('admin.daily-records.index', array_merge($filters, ['view' => $key])) }}">
                    <i class="{{ $tab['icon'] }}"></i>
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Operational Presets (Chips) --}}
    <div class="dr-chips-container">
        <span class="dr-chip-label"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> {{ __('daily_records.quick_presets') }}:</span>
        @php
            $presetList = [
                __('daily_records.daily_activity') => ['view' => 'daily', 'kind' => 'activity', 'icon' => 'fa-regular fa-file-lines'],
                __('daily_records.daily_incidents') => ['view' => 'daily', 'kind' => 'incident', 'icon' => 'fa-solid fa-shield-halved'],
                __('daily_records.monthly_activity') => ['view' => 'monthly', 'kind' => 'activity', 'icon' => 'fa-regular fa-calendar-check'],
                __('daily_records.monthly_incidents') => ['view' => 'monthly', 'kind' => 'incident', 'icon' => 'fa-solid fa-triangle-exclamation'],
                __('daily_records.task_completion') => ['view' => 'monthly', 'kind' => 'task', 'icon' => 'fa-solid fa-circle-check'],
                __('daily_records.outstanding_incidents') => ['kind' => 'incident', 'status' => 'outstanding_incidents', 'icon' => 'fa-solid fa-bell'],
            ];
        @endphp
        @foreach ($presetList as $label => $selection)
            @php
                $isPresetActive = true;
                foreach ($selection as $k => $v) {
                    if ($k === 'view' && $mode !== $v) { $isPresetActive = false; break; }
                    if ($k !== 'view' && ($filters[$k] ?? null) !== $v) { $isPresetActive = false; break; }
                }
            @endphp
            <a class="dr-chip {{ $isPresetActive ? 'active' : '' }}" href="{{ route('admin.daily-records.index', $selection) }}">
                <i class="{{ $selection['icon'] }}"></i>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </div>

    {{-- Modern Filter Card --}}
    <div class="dr-card">
        <div class="dr-card-header">
            <h2 class="dr-card-title">
                <i class="fa-solid fa-sliders text-primary"></i> {{ __('daily_records.filter_and_search') }}
            </h2>
            <div class="d-flex align-items-center gap-2">
                @php
                    $activeFilterCount = collect($filters)->filter(fn($v) => !is_null($v) && $v !== '')->count();
                @endphp
                @if ($activeFilterCount > 0)
                    <span class="dr-badge dr-badge-primary">
                        {{ $activeFilterCount == 1 ? __('daily_records.active_filter_single') : __('daily_records.active_filter_plural', ['count' => $activeFilterCount]) }}
                    </span>
                @endif
            </div>
        </div>
        <div class="dr-card-body">
            <form method="GET" action="{{ route('admin.daily-records.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="view" value="{{ $mode }}">

                {{-- Search --}}
                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-magnifying-glass me-1"></i> {{ __('daily_records.search_query') }}</label>
                    <input class="dr-form-control" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" placeholder="{{ __('daily_records.search_placeholder') }}">
                </div>

                {{-- Employee Picker --}}
                <div class="col-sm-6 col-lg-3 dr-form-group">
                    @include('admin.daily-records.employee-picker', [
                        'pickerName' => 'employee_id',
                        'pickerSelected' => $employeeLabel ? [['id' => $filters['employee_id'], 'name' => $employeeLabel]] : []
                    ])
                </div>

                {{-- Department --}}
                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-building me-1"></i> {{ __('daily_records.department') }}</label>
                    <select class="dr-form-select" name="department_id">
                        <option value="">{{ __('daily_records.all_departments') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Location --}}
                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-location-dot me-1"></i> {{ __('daily_records.location_branch') }}</label>
                    <select class="dr-form-select" name="branch_id">
                        <option value="">{{ __('daily_records.all_locations') }}</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(($filters['branch_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Record Type --}}
                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-tag me-1"></i> {{ __('daily_records.record_type') }}</label>
                    <select class="dr-form-select" name="kind">
                        <option value="">{{ __('daily_records.all_types') }}</option>
                        @foreach ([
                            'activity' => __('daily_records.type_activity'),
                            'incident' => __('daily_records.type_incident'),
                            'task' => __('daily_records.type_task'),
                            'report' => __('daily_records.type_report'),
                            'note' => __('daily_records.type_note'),
                            'extension' => __('daily_records.type_extension')
                        ] as $kindKey => $kindTitle)
                            <option value="{{ $kindKey }}" @selected(($filters['kind'] ?? null) === $kindKey)>{{ $kindTitle }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Category & Subcategory --}}
                @foreach (['category_id' => __('daily_records.category'), 'subcategory_id' => __('daily_records.subcategory')] as $field => $caption)
                    <div class="col-sm-6 col-lg-3 dr-form-group">
                        <label class="dr-form-label"><i class="fa-solid fa-folder me-1"></i> {{ $caption }}</label>
                        <select class="dr-form-select" name="{{ $field }}">
                            <option value="">{{ $field === 'category_id' ? __('daily_records.all_categories') : __('daily_records.all_subcategories') }}</option>
                            @foreach ($catalog->filter(fn ($node) => $field === 'category_id' ? !$node->parent_id : $node->parent_id) as $node)
                                <option value="{{ $node->id }}" @selected(($filters[$field] ?? null) == $node->id)>
                                    {{ \Illuminate\Support\Facades\App::getLocale() === 'km' && !empty($node->name_km) ? $node->name_km : $node->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

                {{-- Priority --}}
                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-flag me-1"></i> {{ __('daily_records.priority') }}</label>
                    <select name="priority" class="dr-form-select">
                        <option value="">{{ __('daily_records.all_priorities') }}</option>
                        @foreach ([
                            'low' => __('daily_records.priority_low'),
                            'normal' => __('daily_records.priority_normal'),
                            'high' => __('daily_records.priority_high'),
                            'urgent' => __('daily_records.priority_urgent')
                        ] as $priorityKey => $priorityLabel)
                            <option value="{{ $priorityKey }}" @selected(($filters['priority'] ?? '') === $priorityKey)>{{ $priorityLabel }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-circle-dot me-1"></i> {{ __('daily_records.status') }}</label>
                    <select class="dr-form-select" name="status">
                        <option value="">{{ __('daily_records.all_statuses') }}</option>
                        @foreach (array_unique([...\App\Modules\DailyRecords\Tasks::STATUSES, ...\App\Modules\DailyRecords\WorkRecords::INCIDENT_STATES, 'under_review', 'outstanding_incidents', 'overdue', 'archived', 'review', 'pending', 'open', 'resolved', 'declined'] ) as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>
                                {{ __('daily_records.kpi_' . $status) !== 'daily_records.kpi_' . $status ? __('daily_records.kpi_' . $status) : ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date / Month Parameters --}}
                @if ($mode === 'daily')
                    <div class="col-sm-6 col-lg-3 dr-form-group">
                        <label class="dr-form-label"><i class="fa-regular fa-calendar me-1"></i> {{ __('daily_records.report_date_ict') }}</label>
                        <input class="dr-form-control" type="date" name="date" value="{{ $filters['date'] ?? now('Asia/Phnom_Penh')->toDateString() }}">
                    </div>
                @elseif (in_array($mode, ['monthly', 'performance', 'calendar']))
                    <div class="col-sm-6 col-lg-3 dr-form-group">
                        <label class="dr-form-label"><i class="fa-regular fa-calendar-days me-1"></i> {{ __('daily_records.report_month_ict') }}</label>
                        <input class="dr-form-control" type="month" name="month" value="{{ $filters['month'] ?? now('Asia/Phnom_Penh')->format('Y-m') }}">
                    </div>
                @else
                    <div class="col-sm-6 col-lg-3 dr-form-group">
                        <label class="dr-form-label"><i class="fa-regular fa-calendar me-1"></i> {{ __('daily_records.from_ict') }}</label>
                        <input class="dr-form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                    </div>
                    <div class="col-sm-6 col-lg-3 dr-form-group">
                        <label class="dr-form-label"><i class="fa-regular fa-calendar-check me-1"></i> {{ __('daily_records.through_ict') }}</label>
                        <input class="dr-form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="col-12 d-flex flex-wrap align-items-center gap-2 pt-2">
                    <button class="dr-btn-primary" type="submit">
                        <i class="fa-solid fa-filter"></i>
                        <span>{{ in_array($mode, ['daily', 'monthly']) ? __('daily_records.generate_report', ['mode' => ucfirst($mode)]) : __('daily_records.apply_filters') }}</span>
                    </button>
                    <a class="dr-btn-outline" href="{{ route('admin.daily-records.index', ['view' => $mode]) }}">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>{{ __('daily_records.reset_filters') }}</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Utility Bar (Export & Realtime Info) --}}
    <div class="dr-toolbar">
        <div class="dr-toolbar-group">
            <a class="dr-btn-excel" href="{{ route('admin.daily-records.export', $filters + ['format' => 'xlsx', 'view' => $mode]) }}">
                <i class="fa-solid fa-file-excel"></i>
                <span>{{ __('daily_records.export_excel') }}</span>
            </a>
            <a class="dr-btn-pdf" href="{{ route('admin.daily-records.export', $filters + ['format' => 'pdf', 'view' => $mode]) }}">
                <i class="fa-solid fa-file-pdf"></i>
                <span>{{ __('daily_records.export_pdf') }}</span>
            </a>
            <a class="dr-btn-refresh" href="{{ request()->fullUrlWithoutQuery(['fragment']) }}" title="{{ __('daily_records.refresh') }}">
                <i class="fa-solid fa-rotate"></i>
                <span>{{ __('daily_records.refresh') }}</span>
            </a>
        </div>
        <div class="dr-toolbar-group">
            <span class="dr-meta-badge">
                <i class="fa-solid fa-globe text-primary"></i> {{ __('daily_records.timezone_ict') }}
            </span>
            @if (in_array($mode, ['daily', 'monthly', 'performance']))
                <span class="dr-meta-badge d-none d-md-inline-flex">
                    <i class="fa-solid fa-circle-info text-info"></i> {{ __('daily_records.ontime_notice') }}
                </span>
            @endif
        </div>
    </div>

    {{-- Results Container --}}
    <div id="daily-record-results" class="dr-container">
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
            if (!panel.contains(document.activeElement) && !panel.querySelector('input:checked')) {
                panel.innerHTML = html;
                if (window.feather) feather.replace();
            }
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
