@extends('layouts.master')

@section('title', __('daily_records.branch_route_management'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/daily-records.css') }}?v={{ filemtime(public_path('assets/css/daily-records.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .route-kpi { border:1px solid #e8edf3; border-radius:12px; background:#fff; padding:16px; height:100%; }
        .route-kpi-label { color:#64748b; font-size:.82rem; font-weight:600; }
        .route-kpi-value { color:#172033; font-size:1.65rem; font-weight:750; line-height:1.15; margin-top:7px; }
        
        /* Modal Timeline Stepper Styling */
        .route-modal-timeline { position:relative; padding-left:14px; }
        .route-timeline-node { position:relative; padding-bottom:18px; padding-left:24px; border-left:2px dashed #cbd5e1; }
        .route-timeline-node:last-child { border-left-color:transparent; padding-bottom:0; }
        .route-timeline-pin {
            position:absolute; left:-13px; top:0; width:26px; height:26px; border-radius:50%;
            display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:11px; color:#fff;
            box-shadow:0 2px 6px rgba(0,0,0,0.15); border:2px solid #fff;
        }
        .route-timeline-pin.departure { background:#f59e0b; }
        .route-timeline-pin.transit { background:#2563eb; }
        .route-timeline-pin.arrival { background:#10b981; }
        
        .route-leg-badge {
            display:inline-flex; align-items:center; gap:6px; background:#f1f5f9; border:1px solid #cbd5e1;
            border-radius:20px; padding:3px 10px; font-size:11px; font-weight:600; color:#334155; margin:6px 0 10px;
        }
        .route-stop-card {
            background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:10px 12px; transition:all .18s ease; cursor:pointer;
        }
        .route-stop-card:hover, .route-stop-card.active {
            border-color:#3b82f6; background:#f8faff; box-shadow:0 3px 10px rgba(59,130,246,0.08); transform:translateX(2px);
        }
        
        /* Leaflet Custom Marker Pins */
        .leaflet-route-pin {
            display:flex; align-items:center; justify-content:center; border-radius:50%; color:#fff; font-weight:800; font-size:12px;
            border:2px solid #ffffff; box-shadow:0 6px 16px rgba(0,0,0,0.3); width:32px; height:32px;
        }
        .leaflet-route-pin.departure { background:#f59e0b; }
        .leaflet-route-pin.transit { background:#2563eb; }
        .leaflet-route-pin.arrival { background:#10b981; }
        
        #routeDetailMap {
            min-height:480px; height:100%; width:100%; border-radius:0 0 12px 12px; background:#f8fafc;
        }

        /* Modal Attachment Viewer Lightbox */
        #modalAttachmentViewer {
            z-index: 1070 !important;
        }
        .modal-backdrop.viewer-backdrop {
            z-index: 1065 !important;
        }
        .attachment-popup-thumb {
            position: relative;
            width: 48px;
            height: 48px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            background: #f1f5f9;
        }
        .attachment-popup-thumb:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border-color: #3b82f6;
        }
        .attachment-popup-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .attachment-popup-thumb .attachment-popup-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.15s ease;
        }
        .attachment-popup-thumb:hover .attachment-popup-overlay {
            opacity: 1;
        }
        .attachment-popup-file {
            transition: background 0.15s ease, transform 0.15s ease;
        }
        .attachment-popup-file:hover {
            background: #e2e8f0 !important;
            transform: translateY(-1px);
        }
        .viewer-thumbnail-item {
            width: 48px;
            height: 48px;
            border-radius: 6px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            opacity: 0.6;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .viewer-thumbnail-item:hover, .viewer-thumbnail-item.active {
            opacity: 1;
            border-color: #38bdf8;
            transform: scale(1.06);
        }
        .route-attachment-item-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            padding: 8px 10px;
            transition: all 0.15s ease;
        }
        .route-attachment-item-card:hover {
            border-color: #93c5fd;
            background: #eff6ff;
        }
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
            ['label' => __('daily_records.branch_routes_ongoing'), 'value' => $summary['ongoing'] ?? 0, 'icon' => 'fa-person-walking-luggage', 'color' => 'text-warning'],
            ['label' => __('daily_records.branch_routes_completed'), 'value' => $summary['completed'], 'icon' => 'fa-circle-check', 'color' => 'text-success'],
            ['label' => __('daily_records.branch_routes_cancelled'), 'value' => $summary['cancelled'], 'icon' => 'fa-ban', 'color' => 'text-secondary'],
            ['label' => __('daily_records.branch_routes_employees'), 'value' => $summary['employees'], 'icon' => 'fa-users', 'color' => 'text-info'],
        ] as $metric)
            <div class="col-6 col-md-4 col-xl"><div class="route-kpi">
                <div class="d-flex align-items-center justify-content-between gap-2"><span class="route-kpi-label">{{ $metric['label'] }}</span><i class="fa-solid {{ $metric['icon'] }} {{ $metric['color'] }}"></i></div>
                <div class="route-kpi-value">{{ number_format($metric['value']) }}</div>
            </div></div>
        @endforeach
    </div>

    @php
        $hasRouteFilters = collect($filters ?? [])
            ->except(['search', 'per_page', 'page'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->isNotEmpty();
    @endphp
    <div class="directory-filter-panel">
        @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'branchRouteFilterCollapse', 'directoryExpanded' => $hasRouteFilters])
        <div id="branchRouteFilterCollapse" class="collapse{{ $hasRouteFilters ? ' show' : '' }}">
            <form class="forms-sample directory-filter-form" action="{{ route('admin.daily-records.branch-routes') }}" id="branchRouteFilterForm" method="get">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label" for="routeStatus">{{ __('daily_records.status') }}</label>
                        <select class="form-select" id="routeStatus" name="status">
                            <option value="">{{ __('daily_records.all_statuses') }}</option>
                            <option value="ongoing" @selected(($filters['status'] ?? '') === 'ongoing')>{{ __('daily_records.branch_routes_ongoing') }}</option>
                            <option value="in_progress" @selected(($filters['status'] ?? '') === 'in_progress')>{{ __('daily_records.branch_routes_in_progress') }}</option>
                            <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>{{ __('daily_records.branch_routes_completed') }}</option>
                            <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>{{ __('daily_records.branch_routes_cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="routeDepartment">{{ __('daily_records.department') }}</label>
                        <select class="form-select" id="routeDepartment" name="department_id">
                            <option value="">{{ __('daily_records.all_departments') }}</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="routeBranch">{{ __('daily_records.location_branch') }}</label>
                        <select class="form-select" id="routeBranch" name="branch_id">
                            <option value="">{{ __('daily_records.all_locations') }}</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected(($filters['branch_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label" for="routeDate">{{ __('daily_records.report_date_ict') }}</label>
                        <input class="form-control" type="date" id="routeDate" name="date" value="{{ $filters['date'] ?? '' }}">
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label" for="routeFrom">{{ __('daily_records.from_ict') }}</label>
                        <input class="form-control" type="date" id="routeFrom" name="from" value="{{ $filters['from'] ?? '' }}">
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label" for="routeTo">{{ __('daily_records.through_ict') }}</label>
                        <input class="form-control" type="date" id="routeTo" name="to" value="{{ $filters['to'] ?? '' }}">
                    </div>
                </div>
                @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.daily-records.branch-routes')])
            </form>
        </div>
    </div>

    <div class="dr-card">
        @include('admin.section.directory-toolbar', ['directoryTable' => 'branchRouteTable', 'directoryForm' => 'branchRouteFilterForm', 'directorySearch' => 'branchRouteSearch', 'directoryRecords' => $rows, 'directoryTitle' => __('daily_records.branch_route_management'), 'directoryIcon' => 'navigation', 'directoryCanAdd' => false, 'directoryDefaultSize' => '25', 'directoryCurrentSize' => (string) request('per_page', '25'), 'directorySearchKey' => 'search', 'directorySearchInForm' => true, 'directoryLive' => false])
        <div class="dr-card-body p-0"><div class="dr-table-responsive">
            <table id="branchRouteTable" class="dr-table">
                <thead><tr><th>{{ __('daily_records.employee') }}</th><th>{{ __('daily_records.branch_route') }}</th><th>{{ __('daily_records.reporting_date_ict') }}</th><th>{{ __('daily_records.status') }}</th><th>{{ __('daily_records.branch_route_duration') }}</th><th data-directory-actions>{{ __('daily_records.actions') }}</th></tr></thead>
                <tbody>
                @forelse ($rows as $row)
                    @php
                        $route = $row->route;
                        $checkpoints = $route['checkpoints_data'] ?? [];
                        $status = $route['status'] ?? 'completed';
                        $badge = match($status) {
                            'completed' => 'dr-badge-success',
                            'ongoing', 'in_progress' => 'dr-badge-warning',
                            'cancelled' => 'dr-badge-secondary',
                            default => 'dr-badge-info',
                        };
                    @endphp
                    <tr>
                        <td><strong>{{ $row->employee }}</strong><div class="small text-muted">{{ $row->title }}</div></td>
                        <td><div class="fw-semibold">{{ $route['origin_branch'] ?? '—' }} <i class="fa-solid fa-arrow-right-long mx-1 text-muted"></i> {{ implode(', ', $route['target_branches'] ?? []) ?: '—' }}</div><div class="small text-muted">{{ $route['purpose'] ?? '' }}</div></td>
                        <td><div>{{ \App\Modules\DailyRecords\RecordTime::display($row->occurred_at) }} ICT</div><div class="small text-muted">{{ __('daily_records.branch_route_stops_count', ['count' => count($checkpoints)]) }}</div></td>
                        <td><span class="dr-badge {{ $badge }}"><span class="dr-badge-dot"></span>{{ __('daily_records.branch_routes_'.$status) }}</span></td>
                        <td>{{ $route['formatted_duration'] ?? __('daily_records.minutes_count', ['count' => $route['total_minutes'] ?? 0]) }}</td>
                        <td>
                            @php
                                $routePayload = [
                                    'id' => $row->id,
                                    'employee' => $row->employee,
                                    'title' => $row->title,
                                    'date_formatted' => \App\Modules\DailyRecords\RecordTime::display($row->occurred_at),
                                    'status' => $status,
                                    'status_label' => __('daily_records.branch_routes_' . $status),
                                    'status_badge' => $badge,
                                    'origin_branch' => $route['origin_branch'] ?? '—',
                                    'target_branches' => $route['target_branches'] ?? [],
                                    'purpose' => $route['purpose'] ?? '',
                                    'mission_mode' => $route['mission_mode'] ?? '—',
                                    'departure_time' => $route['departure_time'] ?? '—',
                                    'arrival_time' => $route['arrival_time'] ?? '—',
                                    'total_minutes' => $route['total_minutes'] ?? 0,
                                    'formatted_duration' => $route['formatted_duration'] ?? ($route['formatted_duration_kh'] ?? '—'),
                                    'formatted_duration_kh' => $route['formatted_duration_kh'] ?? '—',
                                    'total_distance_km' => $route['total_distance_km'] ?? 0,
                                    'formatted_total_distance' => $route['formatted_total_distance'] ?? '0 km',
                                    'average_speed_kmh' => $route['average_speed_kmh'] ?? null,
                                    'formatted_avg_speed' => $route['formatted_avg_speed'] ?? '—',
                                    'final_summary' => $route['final_summary'] ?? '',
                                    'supporting_links' => $route['supporting_links'] ?? [],
                                    'stops' => $route['stops'] ?? [],
                                    'segments' => $route['segments'] ?? [],
                                    'attachments' => $row->attachments->map(function ($f) use ($row) {
                                        $name = $f->name;
                                        $isImage = (bool) preg_match('/\.(jpe?g|png|gif|webp|bmp|svg)$/i', $name);
                                        $isPdf = (bool) preg_match('/\.pdf$/i', $name);
                                        return [
                                            'id' => $f->id,
                                            'name' => $name,
                                            'bytes' => $f->bytes,
                                            'kb' => number_format($f->bytes / 1024, 1),
                                            'url' => route('admin.daily-records.record-file', [$row->id, $f->id]),
                                            'view_url' => route('admin.daily-records.record-file', [$row->id, $f->id]) . '?inline=1',
                                            'is_image' => $isImage,
                                            'is_pdf' => $isPdf,
                                        ];
                                    })->values()->all(),
                                ];
                            @endphp
                            <button type="button" 
                                    class="dr-btn-outline btn-sm btn-open-route-modal d-inline-flex align-items-center gap-1"
                                    data-route='@json($routePayload)'>
                                <i class="fa-solid fa-map-location-dot text-primary"></i>
                                <span>{{ __('daily_records.view_route_details') }}</span>
                            </button>
                        </td>
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

<!-- Modal Route Details with Leaflet Interactive Map -->
<div class="modal fade" id="modalRouteDetails" tabindex="-1" aria-labelledby="modalRouteDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom py-3 px-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary px-2 py-1"><i class="fa-solid fa-route me-1"></i> {{ __('daily_records.branch_route_details') }}</span>
                        <span class="badge" id="modalRouteStatusBadge"></span>
                    </div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalRouteDetailsLabel">
                        <span id="modalRouteEmployee"></span> · <small class="text-light opacity-75 fs-6" id="modalRouteDate"></small>
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Top KPI Summary Bar: Distance, Duration, Speed, Stops -->
                <div class="row g-3 mb-4" id="modalKpiBar">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center justify-content-between text-muted small fw-semibold">
                                <span>{{ __('daily_records.route_total_distance') }}</span>
                                <i class="fa-solid fa-road text-primary"></i>
                            </div>
                            <div class="fs-4 fw-bold text-dark mt-1" id="kpiTotalDistance">0 km</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center justify-content-between text-muted small fw-semibold">
                                <span>{{ __('daily_records.route_total_duration') }}</span>
                                <i class="fa-solid fa-clock text-warning"></i>
                            </div>
                            <div class="fs-4 fw-bold text-dark mt-1" id="kpiTotalDuration">0 នាទី</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center justify-content-between text-muted small fw-semibold">
                                <span>{{ __('daily_records.route_average_speed') }}</span>
                                <i class="fa-solid fa-gauge-high text-info"></i>
                            </div>
                            <div class="fs-4 fw-bold text-dark mt-1" id="kpiAvgSpeed">—</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="d-flex align-items-center justify-content-between text-muted small fw-semibold">
                                <span>{{ __('daily_records.route_stops_count') }}</span>
                                <i class="fa-solid fa-map-pin text-success"></i>
                            </div>
                            <div class="fs-4 fw-bold text-dark mt-1" id="kpiTotalStops">0</div>
                        </div>
                    </div>
                </div>

                <!-- Split View: Left Timeline Stepper & Right Leaflet Map -->
                <div class="row g-4 align-items-stretch">
                    <!-- Left Column: Timeline & Route Stops -->
                    <div class="col-lg-5 d-flex flex-column">
                        <div class="card border rounded-3 h-100 shadow-xs flex-fill">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark">
                                    <i class="fa-solid fa-timeline me-1 text-primary"></i> {{ __('daily_records.route_journey_timeline') }}
                                </h6>
                                <small class="text-muted" style="font-size: 11px;">ចុចលើចំណុចដើម្បីមើលលើផែនទី</small>
                            </div>
                            <div class="card-body p-3 overflow-auto" style="max-height: 480px;" id="modalRouteTimeline">
                                <!-- Rendered dynamically by JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Interactive Map -->
                    <div class="col-lg-7 d-flex flex-column">
                        <div class="card border rounded-3 h-100 shadow-xs flex-fill">
                            <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-map-location-dot text-primary"></i>
                                    <span class="fw-bold small text-dark">{{ __('daily_records.route_map_view') }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-xs btn-outline-primary" id="btnFitRouteMap" title="{{ __('daily_records.route_fit_bounds') }}">
                                        <i class="fa-solid fa-expand me-1"></i> {{ __('daily_records.route_fit_bounds') }}
                                    </button>
                                    <a href="#" target="_blank" rel="noopener" class="btn btn-xs btn-outline-secondary" id="btnOpenExternalMap" title="{{ __('daily_records.route_open_google_maps') }}">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Google Maps
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0 position-relative">
                                <div id="routeDetailMap" style="min-height: 480px; height: 100%;"></div>
                                <!-- Floating Quick Access Button for Attachments on Map -->
                                <div id="routeMapFloatingAttachments" class="position-absolute top-0 end-0 m-2 d-none" style="z-index: 1000;">
                                    <button type="button" class="btn btn-sm btn-light border shadow-sm d-flex align-items-center gap-1 py-1 px-2" id="btnMapQuickGallery" title="{{ __('daily_records.route_open_gallery') }}">
                                        <i class="fa-solid fa-camera text-primary"></i>
                                        <span class="fw-semibold small">{{ __('daily_records.route_attachments') }}</span>
                                        <span class="badge bg-primary rounded-pill ms-1" id="mapAttachmentsBadge">0</span>
                                    </button>
                                </div>
                                <div id="routeMapNoGpsNotice" class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-75 text-white p-3 rounded text-center d-none" style="z-index: 1000; width: 85%;">
                                    <i class="fa-solid fa-location-slash fs-3 mb-2 text-warning"></i>
                                    <p class="mb-0 small">{{ __('daily_records.route_no_gps_warning') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section: Mission Purpose & Attachments -->
                <div class="card border rounded-3 mt-3 shadow-xs">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 11px;">{{ __('daily_records.purpose') }}</div>
                                <p class="mb-2 text-dark small" id="modalRoutePurpose"></p>
                                <div id="modalRouteSummaryContainer" class="d-none">
                                    <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 11px;">{{ __('daily_records.final_summary') }}</div>
                                    <p class="mb-0 text-muted small text-break" id="modalRouteSummary"></p>
                                </div>
                            </div>
                            <div class="col-md-5 border-start-md">
                                <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 11px;">{{ __('daily_records.attachments') }}</div>
                                <div id="modalRouteAttachments" class="small">
                                    <!-- Rendered dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    {{ __('index.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lightbox for Full Attachment View -->
<div class="modal fade" id="modalAttachmentViewer" tabindex="-1" aria-labelledby="modalAttachmentViewerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-secondary py-2 px-3 align-items-center justify-content-between" style="background: rgba(15, 23, 42, 0.95);">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <i id="viewerTypeIcon" class="fa-regular fa-image text-info fs-5"></i>
                    <div class="text-truncate">
                        <div class="fw-bold text-white small text-truncate" id="viewerFileName">—</div>
                        <div class="text-light opacity-75" style="font-size: 11px;">
                            <span id="viewerFileCounter">1 / 1</span> · <span id="viewerFileSize">0 KB</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a id="viewerDownloadBtn" href="#" class="btn btn-sm btn-outline-light py-1 px-2" download title="{{ __('daily_records.route_download_file') }}">
                        <i class="fa-solid fa-download me-1"></i> <span class="d-none d-sm-inline" style="font-size: 11px;">{{ __('daily_records.route_download_file') }}</span>
                    </a>
                    <a id="viewerNewTabBtn" href="#" target="_blank" class="btn btn-sm btn-outline-light py-1 px-2" title="{{ __('daily_records.route_open_in_new_tab') }}">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center" style="min-height: 420px; max-height: 80vh; background: #020617;">
                <!-- Navigation Prev/Next -->
                <button type="button" class="btn btn-dark bg-opacity-75 text-white position-absolute start-0 top-50 translate-middle-y ms-2 rounded-circle viewer-nav-btn shadow-sm" id="viewerPrevBtn" style="width: 44px; height: 44px; z-index: 105;">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-dark bg-opacity-75 text-white position-absolute end-0 top-50 translate-middle-y me-2 rounded-circle viewer-nav-btn shadow-sm" id="viewerNextBtn" style="width: 44px; height: 44px; z-index: 105;">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <!-- Media Content Area -->
                <div id="viewerMediaContainer" class="w-100 h-100 d-flex align-items-center justify-content-center p-3" style="min-height: 420px;">
                    <!-- Injected dynamically -->
                </div>
            </div>
            <!-- Thumbnails navigation footer (if multiple attachments) -->
            <div class="modal-footer border-secondary py-2 px-3 justify-content-center" id="viewerFooterStrip" style="background: rgba(15, 23, 42, 0.95);">
                <div class="d-flex gap-2 overflow-auto py-1" id="viewerThumbnailsStrip" style="max-width: 100%;">
                    <!-- mini thumbnails -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let routeMap = null;
    let routeMarkersGroup = null;
    let routePolyline = null;
    let currentRouteData = null;

    const modalEl = document.getElementById('modalRouteDetails');
    const bsModal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.btn-open-route-modal').forEach(function (button) {
        button.addEventListener('click', function () {
            const dataStr = this.getAttribute('data-route');
            if (!dataStr) return;
            try {
                currentRouteData = JSON.parse(dataStr);
                populateModal(currentRouteData);
                bsModal.show();
            } catch (e) {
                console.error('Failed to parse route payload:', e);
            }
        });
    });

    function populateModal(data) {
        document.getElementById('modalRouteEmployee').textContent = data.employee || '—';
        document.getElementById('modalRouteDate').textContent = (data.date_formatted || '') + ' ICT';

        const statusBadge = document.getElementById('modalRouteStatusBadge');
        statusBadge.className = 'badge ' + (data.status_badge || 'bg-info');
        statusBadge.textContent = data.status_label || data.status;

        // KPI Bar
        document.getElementById('kpiTotalDistance').textContent = data.formatted_total_distance || (data.total_distance_km + ' km');
        document.getElementById('kpiTotalDuration').textContent = data.formatted_duration_kh || data.formatted_duration || '—';
        document.getElementById('kpiAvgSpeed').textContent = data.formatted_avg_speed || '—';
        document.getElementById('kpiTotalStops').textContent = (data.stops || []).length;

        // Mission Purpose & Summary
        document.getElementById('modalRoutePurpose').textContent = data.purpose || '—';
        
        const summaryCont = document.getElementById('modalRouteSummaryContainer');
        if (data.final_summary) {
            summaryCont.classList.remove('d-none');
            document.getElementById('modalRouteSummary').textContent = data.final_summary;
        } else {
            summaryCont.classList.add('d-none');
        }

        // Attachments
        const attachList = document.getElementById('modalRouteAttachments');
        attachList.innerHTML = '';
        if (data.attachments && data.attachments.length > 0) {
            const listGrid = document.createElement('div');
            listGrid.className = 'd-flex flex-column gap-2';

            data.attachments.forEach(function (file, attIdx) {
                const card = document.createElement('div');
                card.className = 'route-attachment-item-card d-flex align-items-center justify-content-between gap-2';
                
                let thumbPreview = '';
                if (file.is_image) {
                    thumbPreview = `
                        <div class="position-relative attachment-popup-thumb flex-shrink-0" data-att-idx="${attIdx}" title="{{ __('daily_records.route_view_full_attachment') }}">
                            <img src="${file.view_url}" alt="${escapeHtml(file.name)}" loading="lazy">
                            <div class="attachment-popup-overlay"><i class="fa-solid fa-magnifying-glass-plus text-white" style="font-size:11px;"></i></div>
                        </div>
                    `;
                } else {
                    thumbPreview = `
                        <div class="d-flex align-items-center justify-content-center bg-white border rounded flex-shrink-0" style="width:48px; height:48px; cursor:pointer;" data-att-idx="${attIdx}">
                            <i class="fa-solid ${file.is_pdf ? 'fa-file-pdf text-danger' : 'fa-file-lines text-primary'} fs-5"></i>
                        </div>
                    `;
                }

                card.innerHTML = `
                    <div class="d-flex align-items-center gap-2 overflow-hidden flex-grow-1">
                        ${thumbPreview}
                        <div class="overflow-hidden">
                            <a href="#" class="fw-semibold text-dark text-truncate d-block text-decoration-none btn-view-full-att" data-att-idx="${attIdx}" title="${escapeHtml(file.name)}">
                                ${escapeHtml(file.name)}
                            </a>
                            <span class="text-muted small" style="font-size: 11px;">${file.kb} KB</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                        <button type="button" class="btn btn-xs btn-outline-primary btn-view-full-att" data-att-idx="${attIdx}" title="{{ __('daily_records.route_view_full_attachment') }}">
                            <i class="fa-solid fa-eye me-1"></i> {{ __('daily_records.route_view_full_attachment') }}
                        </button>
                        <a href="${file.url}" class="btn btn-xs btn-outline-secondary" download title="{{ __('daily_records.route_download_file') }}">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>
                `;
                listGrid.appendChild(card);
            });
            attachList.appendChild(listGrid);
        } else {
            attachList.innerHTML = '<span class="text-muted small fst-italic">{{ __('daily_records.route_no_attachments') }}</span>';
        }

        // External Google Maps button
        const externalBtn = document.getElementById('btnOpenExternalMap');
        const coordsStops = (data.stops || []).filter(s => s.latitude && s.longitude);
        if (coordsStops.length > 1) {
            const origin = coordsStops[0];
            const dest = coordsStops[coordsStops.length - 1];
            externalBtn.href = `https://www.google.com/maps/dir/${origin.latitude},${origin.longitude}/${dest.latitude},${dest.longitude}`;
            externalBtn.classList.remove('d-none');
        } else if (coordsStops.length === 1) {
            externalBtn.href = `https://www.google.com/maps?q=${coordsStops[0].latitude},${coordsStops[0].longitude}`;
            externalBtn.classList.remove('d-none');
        } else {
            externalBtn.classList.add('d-none');
        }

        // Timeline Stepper
        const timelineCont = document.getElementById('modalRouteTimeline');
        timelineCont.innerHTML = '';

        if (!data.stops || data.stops.length === 0) {
            timelineCont.innerHTML = '<div class="text-muted small p-3 text-center">មិនមានចំណុចសាខាកត់ត្រាឡើយ</div>';
            return;
        }

        data.stops.forEach(function (stop, idx) {
            const node = document.createElement('div');
            node.className = 'route-timeline-node';

            let pinType = 'transit';
            let pinLabel = stop.stop_index;
            if (idx === 0) {
                pinType = 'departure';
                pinLabel = '🚩';
            } else if (idx === data.stops.length - 1) {
                pinType = 'arrival';
                pinLabel = '🏁';
            }

            let legHtml = '';
            if (stop.leg_distance_km !== undefined && stop.leg_distance_km !== null) {
                const durHtml = stop.formatted_leg_duration ? ` · <i class="fa-regular fa-clock ms-1"></i> ${stop.formatted_leg_duration}` : '';
                legHtml = `
                    <div class="route-leg-badge">
                        <i class="fa-solid fa-arrow-down text-primary" style="font-size:9px;"></i>
                        <span>${stop.formatted_leg_distance}${durHtml}</span>
                    </div>
                `;
            }

            const hasGps = (stop.latitude && stop.longitude);
            const gpsBadge = hasGps 
                ? `<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:9px;"><i class="fa-solid fa-location-dot me-1"></i> GPS OK</span>`
                : `<span class="badge bg-light text-muted border" style="font-size:9px;">No GPS</span>`;

            node.innerHTML = `
                ${legHtml}
                <div class="route-timeline-pin ${pinType}">${pinLabel}</div>
                <div class="route-stop-card" data-stop-idx="${idx}" ${hasGps ? `data-lat="${stop.latitude}" data-lng="${stop.longitude}"` : ''}>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <strong class="text-dark small">${escapeHtml(stop.title)}</strong>
                        ${gpsBadge}
                    </div>
                    <div class="d-flex align-items-center justify-content-between text-muted" style="font-size:11px;">
                        <span><i class="fa-regular fa-clock me-1"></i> ${escapeHtml(stop.time_label || '—')}</span>
                        <span class="text-capitalize badge bg-light text-dark">${escapeHtml(stop.action_type || '')}</span>
                    </div>
                    ${stop.notes ? `<div class="mt-1 small text-secondary fst-italic" style="font-size:11px;">"${escapeHtml(stop.notes)}"</div>` : ''}
                    ${hasGps ? `<div class="mt-1 text-end"><small class="text-primary fw-semibold" style="font-size:10px;"><i class="fa-solid fa-crosshairs me-1"></i> {{ __('daily_records.route_focus_stop') }}</small></div>` : ''}
                </div>
            `;

            timelineCont.appendChild(node);
        });

        // Click on timeline stop card to fly to marker on map
        timelineCont.querySelectorAll('.route-stop-card').forEach(function (card) {
            card.addEventListener('click', function () {
                timelineCont.querySelectorAll('.route-stop-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                const lat = parseFloat(this.getAttribute('data-lat'));
                const lng = parseFloat(this.getAttribute('data-lng'));
                if (!isNaN(lat) && !isNaN(lng) && routeMap) {
                    routeMap.flyTo([lat, lng], 16, { duration: 0.8 });
                    const idx = parseInt(this.getAttribute('data-stop-idx'), 10);
                    if (routeMarkersGroup) {
                        routeMarkersGroup.eachLayer(function (layer) {
                            if (layer._stopIdx === idx) {
                                layer.openPopup();
                            }
                        });
                    }
                }
            });
        });
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        initOrUpdateMap(currentRouteData);
    });

    function initOrUpdateMap(data) {
        if (!data) return;

        const mapContainer = document.getElementById('routeDetailMap');
        const noGpsNotice = document.getElementById('routeMapNoGpsNotice');

        if (!routeMap) {
            routeMap = L.map(mapContainer, {
                zoomControl: true,
                attributionControl: true
            }).setView([11.5564, 104.9282], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(routeMap);

            routeMarkersGroup = L.featureGroup().addTo(routeMap);
        } else {
            routeMarkersGroup.clearLayers();
            if (routePolyline) {
                routeMap.removeLayer(routePolyline);
                routePolyline = null;
            }
        }

        // Floating Attachments Button on Map
        const floatingGallery = document.getElementById('routeMapFloatingAttachments');
        const badgeCount = document.getElementById('mapAttachmentsBadge');
        if (data.attachments && data.attachments.length > 0) {
            floatingGallery.classList.remove('d-none');
            badgeCount.textContent = data.attachments.length;
        } else {
            floatingGallery.classList.add('d-none');
        }

        const validCoordsStops = (data.stops || []).filter(s => s.latitude && s.longitude);

        if (validCoordsStops.length === 0) {
            noGpsNotice.classList.remove('d-none');
            routeMap.setView([11.5564, 104.9282], 11);
            routeMap.invalidateSize();
            return;
        }

        noGpsNotice.classList.add('d-none');
        const latlngs = [];

        validCoordsStops.forEach(function (stop, i) {
            const latlng = [stop.latitude, stop.longitude];
            latlngs.push(latlng);

            let pinClass = 'transit';
            let iconText = stop.stop_index;
            if (i === 0) {
                pinClass = 'departure';
                iconText = '🚩';
            } else if (i === validCoordsStops.length - 1) {
                pinClass = 'arrival';
                iconText = '🏁';
            }

            const customIcon = L.divIcon({
                className: '',
                html: `<div class="leaflet-route-pin ${pinClass}">${iconText}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -18]
            });

            const marker = L.marker(latlng, { icon: customIcon });
            marker._stopIdx = (data.stops || []).indexOf(stop);

            let legText = '';
            if (stop.leg_distance_km !== undefined && stop.leg_distance_km !== null) {
                legText = `<div class="small text-muted mt-1">🚗 ចម្ងាយ: <strong>${stop.formatted_leg_distance}</strong></div>`;
            }

            let popupAttachmentsHtml = '';
            if (data.attachments && data.attachments.length > 0) {
                const thumbs = data.attachments.map(function (att, aIdx) {
                    if (att.is_image) {
                        return `
                            <div class="attachment-popup-thumb" data-att-idx="${aIdx}" title="${escapeHtml(att.name)} (${att.kb} KB)">
                                <img src="${att.view_url}" alt="${escapeHtml(att.name)}" loading="lazy">
                                <div class="attachment-popup-overlay"><i class="fa-solid fa-magnifying-glass-plus text-white" style="font-size:10px;"></i></div>
                            </div>
                        `;
                    } else {
                        return `
                            <div class="d-flex align-items-center gap-1 p-1 bg-light border rounded attachment-popup-file" data-att-idx="${aIdx}" style="cursor:pointer; font-size:11px;" title="${escapeHtml(att.name)}">
                                <i class="fa-solid ${att.is_pdf ? 'fa-file-pdf text-danger' : 'fa-file-lines text-secondary'}"></i>
                                <span class="text-truncate" style="max-width:110px;">${escapeHtml(att.name)}</span>
                            </div>
                        `;
                    }
                }).join('');

                popupAttachmentsHtml = `
                    <div class="mt-2 pt-2 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-bold text-dark"><i class="fa-solid fa-images text-primary me-1"></i> {{ __('daily_records.route_attachments') }} (${data.attachments.length})</span>
                            <a href="#" class="small text-primary text-decoration-none fw-semibold btn-open-gallery-direct" style="font-size:11px;">{{ __('daily_records.route_view_full_attachment') }}</a>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            ${thumbs}
                        </div>
                    </div>
                `;
            }

            marker.bindPopup(`
                <div class="p-1" style="min-width:210px; max-width:290px;">
                    <div class="fw-bold text-dark fs-6 mb-1">${escapeHtml(stop.title)}</div>
                    <div class="small text-muted mb-1"><i class="fa-regular fa-clock me-1"></i> ${escapeHtml(stop.time_label || '—')}</div>
                    ${legText}
                    ${stop.notes ? `<div class="small mt-1 text-secondary fst-italic">"${escapeHtml(stop.notes)}"</div>` : ''}
                    ${popupAttachmentsHtml}
                </div>
            `);

            routeMarkersGroup.addLayer(marker);
        });

        // Polyline connecting stops
        if (latlngs.length > 1) {
            routePolyline = L.polyline(latlngs, {
                color: '#2563eb',
                weight: 5,
                opacity: 0.85,
                smoothFactor: 1
            }).addTo(routeMap);

            if (data.formatted_total_distance) {
                routePolyline.bindTooltip(`🚗 ចម្ងាយសរុប: ${data.formatted_total_distance}`, {
                    sticky: true,
                    className: 'fw-bold'
                });
            }
        }

        routeMap.invalidateSize();

        if (routeMarkersGroup.getLayers().length > 0) {
            const bounds = routeMarkersGroup.getBounds();
            if (bounds.isValid()) {
                routeMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
            }
        }

        document.getElementById('btnFitRouteMap').onclick = function () {
            if (routeMarkersGroup && routeMarkersGroup.getLayers().length > 0) {
                const bounds = routeMarkersGroup.getBounds();
                if (bounds.isValid()) {
                    routeMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                }
            }
        };
    }

    // Lightbox modal viewer logic
    let currentViewerAttachments = [];
    let currentViewerIndex = 0;
    const viewerModalEl = document.getElementById('modalAttachmentViewer');
    const bsViewerModal = new bootstrap.Modal(viewerModalEl);

    function openAttachmentViewer(attachments, index) {
        if (!attachments || attachments.length === 0) return;
        currentViewerAttachments = attachments;
        currentViewerIndex = Math.max(0, Math.min(index || 0, attachments.length - 1));
        renderViewerCurrent();
        bsViewerModal.show();
    }

    function renderViewerCurrent() {
        const file = currentViewerAttachments[currentViewerIndex];
        if (!file) return;

        document.getElementById('viewerFileName').textContent = file.name;
        document.getElementById('viewerFileCounter').textContent = `${currentViewerIndex + 1} / ${currentViewerAttachments.length}`;
        document.getElementById('viewerFileSize').textContent = `${file.kb} KB`;
        document.getElementById('viewerDownloadBtn').href = file.url;
        document.getElementById('viewerNewTabBtn').href = file.view_url || file.url;

        // Show/hide prev/next buttons
        document.getElementById('viewerPrevBtn').style.display = currentViewerAttachments.length > 1 ? 'flex' : 'none';
        document.getElementById('viewerNextBtn').style.display = currentViewerAttachments.length > 1 ? 'flex' : 'none';

        // Type icon
        const typeIcon = document.getElementById('viewerTypeIcon');
        if (file.is_image) {
            typeIcon.className = 'fa-regular fa-image text-info fs-5';
        } else if (file.is_pdf) {
            typeIcon.className = 'fa-solid fa-file-pdf text-danger fs-5';
        } else {
            typeIcon.className = 'fa-solid fa-file-lines text-secondary fs-5';
        }

        const container = document.getElementById('viewerMediaContainer');
        container.innerHTML = '';

        if (file.is_image) {
            const img = document.createElement('img');
            img.src = file.view_url || file.url;
            img.alt = file.name;
            img.className = 'img-fluid rounded shadow-sm';
            img.style.maxHeight = '72vh';
            img.style.maxWidth = '100%';
            img.style.objectFit = 'contain';
            container.appendChild(img);
        } else if (file.is_pdf) {
            const iframe = document.createElement('iframe');
            iframe.src = file.view_url || file.url;
            iframe.style.width = '100%';
            iframe.style.height = '70vh';
            iframe.style.border = 'none';
            iframe.style.borderRadius = '8px';
            container.appendChild(iframe);
        } else {
            container.innerHTML = `
                <div class="text-center p-5">
                    <i class="fa-solid fa-file-circle-check fs-1 text-info mb-3"></i>
                    <h6 class="text-white mb-2">${escapeHtml(file.name)}</h6>
                    <p class="text-white-50 small mb-3">${file.kb} KB</p>
                    <a href="${file.url}" class="btn btn-primary btn-sm" download>
                        <i class="fa-solid fa-download me-1"></i> {{ __('daily_records.route_download_file') }}
                    </a>
                </div>
            `;
        }

        // Render footer strip if multiple
        const stripFooter = document.getElementById('viewerFooterStrip');
        const strip = document.getElementById('viewerThumbnailsStrip');
        if (currentViewerAttachments.length > 1) {
            stripFooter.classList.remove('d-none');
            strip.innerHTML = '';
            currentViewerAttachments.forEach((att, idx) => {
                const thumb = document.createElement('div');
                thumb.className = 'viewer-thumbnail-item ' + (idx === currentViewerIndex ? 'active' : '');
                if (att.is_image) {
                    thumb.innerHTML = `<img src="${att.view_url || att.url}" style="width:100%; height:100%; object-fit:cover;" alt="${escapeHtml(att.name)}">`;
                } else {
                    thumb.className += ' bg-secondary d-flex align-items-center justify-content-center text-white';
                    thumb.innerHTML = `<i class="fa-solid ${att.is_pdf ? 'fa-file-pdf text-danger' : 'fa-file'}" style="font-size:16px;"></i>`;
                }
                thumb.addEventListener('click', function () {
                    currentViewerIndex = idx;
                    renderViewerCurrent();
                });
                strip.appendChild(thumb);
            });
        } else {
            stripFooter.classList.add('d-none');
        }
    }

    document.getElementById('viewerPrevBtn').addEventListener('click', function () {
        if (currentViewerAttachments.length > 1) {
            currentViewerIndex = (currentViewerIndex - 1 + currentViewerAttachments.length) % currentViewerAttachments.length;
            renderViewerCurrent();
        }
    });

    document.getElementById('viewerNextBtn').addEventListener('click', function () {
        if (currentViewerAttachments.length > 1) {
            currentViewerIndex = (currentViewerIndex + 1) % currentViewerAttachments.length;
            renderViewerCurrent();
        }
    });

    // Keyboard arrow navigation
    document.addEventListener('keydown', function (e) {
        if (!viewerModalEl.classList.contains('show')) return;
        if (e.key === 'ArrowLeft') {
            document.getElementById('viewerPrevBtn').click();
        } else if (e.key === 'ArrowRight') {
            document.getElementById('viewerNextBtn').click();
        }
    });

    // Modal backdrop stacking UX
    viewerModalEl.addEventListener('show.bs.modal', function () {
        setTimeout(function () {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                backdrops[backdrops.length - 1].classList.add('viewer-backdrop');
            }
        }, 10);
    });

    viewerModalEl.addEventListener('hidden.bs.modal', function () {
        if (document.getElementById('modalRouteDetails').classList.contains('show')) {
            document.body.classList.add('modal-open');
        }
    });

    // Global click delegation for opening the attachment viewer
    document.addEventListener('click', function (e) {
        const thumb = e.target.closest('[data-att-idx]');
        if (thumb) {
            e.preventDefault();
            const idx = parseInt(thumb.getAttribute('data-att-idx'), 10);
            if (currentRouteData && currentRouteData.attachments) {
                openAttachmentViewer(currentRouteData.attachments, isNaN(idx) ? 0 : idx);
            }
            return;
        }

        const galleryBtn = e.target.closest('.btn-open-gallery-direct, #btnMapQuickGallery, [data-open-gallery]');
        if (galleryBtn) {
            e.preventDefault();
            if (currentRouteData && currentRouteData.attachments && currentRouteData.attachments.length > 0) {
                openAttachmentViewer(currentRouteData.attachments, 0);
            }
            return;
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
</script>
@endsection
