@extends('layouts.master')

@section('title', __('deliveries.delivery_management'))

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/daily-records-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/directory-table-tools.css') }}">
    <style>
        .delivery-pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            margin-right: 4px;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: deliveryPulse 1.8s infinite;
        }
        @keyframes deliveryPulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .leaflet-driver-pin {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            font-size: 16px;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: transform 0.2s ease;
        }
        .leaflet-driver-pin.arrived { background: #10b981; }
        .leaflet-driver-pin.pending { background: #f59e0b; }

        .leaflet-destination-pin {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ef4444;
            color: #ffffff;
            font-size: 14px;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
        }

        .delivery-photo-thumb {
            width: 54px;
            height: 54px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            position: relative;
            background: #f1f5f9;
        }
        .delivery-photo-thumb:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border-color: #3b82f6;
        }
        .delivery-photo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #modalDeliveryPhotoViewer { z-index: 1070 !important; }
        .modal-backdrop.viewer-backdrop { z-index: 1065 !important; }
    </style>
@endsection

@section('main-content')
<div class="dr-container">
    {{-- Breadcrumbs & Top Navigation --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.daily-records.index') }}">{{ __('daily_records.work_and_operations') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('deliveries.delivery_management') }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a class="dr-btn-outline" href="{{ route('admin.daily-records.branch-routes') }}">
                <i class="fa-solid fa-route"></i>
                <span>{{ __('daily_records.branch_route_management') }}</span>
            </a>
            <a class="dr-btn-outline" href="{{ route('admin.deliveries.index', request()->query()) }}">
                <i class="fa-solid fa-rotate"></i>
                <span>{{ __('index.refresh') }}</span>
            </a>
            <button type="button" class="dr-btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateDelivery">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('deliveries.quick_dispatch') }}</span>
            </button>
        </div>
    </div>

    {{-- Hero Card --}}
    <div class="dr-hero-card mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 position-relative" style="z-index: 1;">
            <div class="d-flex align-items-center gap-3">
                <div class="dr-hero-icon-box">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div>
                    <h1 class="dr-hero-title">{{ __('deliveries.delivery_management') }}</h1>
                    <p class="dr-hero-subtitle mb-0">{{ __('deliveries.delivery_subtitle') }}</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-light btn-sm shadow-sm border fw-semibold d-inline-flex align-items-center gap-1" id="btnSwitchToFleetMap">
                    <i class="fa-solid fa-map-location-dot text-primary"></i>
                    <span>{{ __('deliveries.live_fleet_tracking') }}</span>
                    <span class="badge bg-success rounded-pill ms-1" id="badgeActiveFleetCount">{{ $summary['in_transit'] }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- KPI Ribbon --}}
    <div class="row g-3 mb-4" aria-label="Delivery KPIs">
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3 border bg-white shadow-xs h-100">
                <div class="d-flex align-items-center justify-content-between text-muted small fw-semibold">
                    <span>{{ __('deliveries.kpi_total_today') }}</span>
                    <i class="fa-solid fa-boxes-packing text-primary"></i>
                </div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($summary['total_today']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3 border bg-white shadow-xs h-100">
                <div class="d-flex align-items-center justify-content-between text-muted small fw-semibold">
                    <span>{{ __('deliveries.kpi_in_transit') }}</span>
                    <i class="fa-solid fa-truck-arrow-right text-success"></i>
                </div>
                <div class="fs-4 fw-bold text-success mt-1 d-flex align-items-center">
                    @if($summary['in_transit'] > 0)<span class="delivery-pulse-dot"></span>@endif
                    <span>{{ number_format($summary['in_transit']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3 border bg-white shadow-xs h-100">
                <div class="d-flex align-items-center justify-content-between text-muted small fw-semibold">
                    <span>{{ __('deliveries.kpi_delivered_today') }}</span>
                    <i class="fa-solid fa-circle-check text-info"></i>
                </div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($summary['delivered_today']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3 border bg-white shadow-xs h-100">
                <div class="d-flex align-items-center justify-content-between text-muted small fw-semibold">
                    <span>{{ __('deliveries.kpi_active_drivers') }}</span>
                    <i class="fa-solid fa-motorcycle text-warning"></i>
                </div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($summary['active_drivers']) }}</div>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    @php
        $hasFilters = collect($filters ?? [])
            ->except(['per_page', 'page'])
            ->filter(fn ($v) => $v !== null && $v !== '' && $v !== 'all')
            ->isNotEmpty();
    @endphp
    <div class="directory-filter-panel mb-3">
        @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'deliveryFilterCollapse', 'directoryExpanded' => $hasFilters])
        <div id="deliveryFilterCollapse" class="collapse {{ $hasFilters ? 'show' : '' }}">
            <form class="forms-sample directory-filter-form" action="{{ route('admin.deliveries.index') }}" method="get" id="deliveryFilterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="filterStatus"><i class="fa-solid fa-circle-dot me-1"></i> {{ __('deliveries.status') }}</label>
                        <select class="form-select" id="filterStatus" name="status">
                            <option value="all">{{ __('deliveries.all_statuses') }}</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>🟢 {{ __('deliveries.kpi_in_transit') }}</option>
                            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>{{ __('deliveries.status_pending') }}</option>
                            <option value="in_transit" @selected(($filters['status'] ?? '') === 'in_transit')>{{ __('deliveries.status_in_transit') }}</option>
                            <option value="arrived" @selected(($filters['status'] ?? '') === 'arrived')>{{ __('deliveries.status_arrived') }}</option>
                            <option value="delivered" @selected(($filters['status'] ?? '') === 'delivered')>{{ __('deliveries.status_delivered') }}</option>
                            <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>{{ __('deliveries.status_cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="filterDriver"><i class="fa-solid fa-user me-1"></i> {{ __('deliveries.driver') }}</label>
                        <select class="form-select" id="filterDriver" name="driver_id">
                            <option value="">{{ __('deliveries.all_drivers') }}</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" @selected(($filters['driver_id'] ?? '') == $driver->id)>{{ $driver->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="filterBranch"><i class="fa-solid fa-building me-1"></i> {{ __('deliveries.branch') }}</label>
                        <select class="form-select" id="filterBranch" name="branch_id">
                            <option value="">{{ __('deliveries.all_branches') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="filterDate"><i class="fa-regular fa-calendar me-1"></i> {{ __('daily_records.date') }}</label>
                        <input type="date" class="form-control" id="filterDate" name="date" value="{{ $filters['date'] ?? '' }}">
                    </div>
                    <div class="col-sm-6 col-lg-6">
                        <label class="form-label" for="filterSearch"><i class="fa-solid fa-magnifying-glass me-1"></i> {{ __('index.search') }}</label>
                        <input type="search" class="form-control" id="filterSearch" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="ស្វែងរកតាមលេខកូដ, វិក្កយបត្រ, ឈ្មោះភ្ញៀវ, លេខទូរស័ព្ទ...">
                    </div>
                </div>
                @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.deliveries.index')])
            </form>
        </div>
    </div>

    {{-- Main Dual-View Navigation --}}
    <ul class="nav nav-tabs nav-tabs-line mb-3" id="deliveryTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="tab-list-btn" data-bs-toggle="tab" data-bs-target="#tab-list-pane" type="button" role="tab" aria-controls="tab-list-pane" aria-selected="true">
                <i class="fa-solid fa-list-check me-1"></i> {{ __('deliveries.delivery_list') }} ({{ number_format($rows->total()) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="tab-map-btn" data-bs-toggle="tab" data-bs-target="#tab-map-pane" type="button" role="tab" aria-controls="tab-map-pane" aria-selected="false">
                <i class="fa-solid fa-map-location-dot me-1 text-primary"></i> {{ __('deliveries.live_fleet_tracking') }}
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1">{{ count($activeFleet) }}</span>
            </button>
        </li>
    </ul>

    {{-- Tab Contents --}}
    <div class="tab-content" id="deliveryTabContent">
        {{-- Tab 1: Directory Table --}}
        <div class="tab-pane fade show active" id="tab-list-pane" role="tabpanel" aria-labelledby="tab-list-btn">
            <div class="dr-card">
                <div class="dr-table-responsive">
                    <table class="dr-table align-middle" id="deliveryTable">
                        <thead>
                            <tr>
                                <th>{{ __('deliveries.tracking_code') }} / {{ __('deliveries.invoice_number') }}</th>
                                <th>{{ __('deliveries.customer') }}</th>
                                <th>{{ __('deliveries.driver') }}</th>
                                <th>{{ __('deliveries.status') }}</th>
                                <th>{{ __('deliveries.duration') }} / {{ __('daily_records.time') }}</th>
                                <th class="text-end">{{ __('daily_records.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $delivery)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $delivery->tracking_code ?? 'DEL-#' . $delivery->id }}</div>
                                        @if($delivery->invoice_number)
                                            <div class="small text-muted"><i class="fa-solid fa-file-invoice me-1 text-secondary"></i> {{ $delivery->invoice_number }}</div>
                                        @endif
                                        <div class="mt-1">
                                            <a href="{{ $delivery->tracking_url }}" target="_blank" class="small text-primary text-decoration-none" title="{{ __('deliveries.open_in_google_maps') }}">
                                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Public Track
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $delivery->customer_name }}</div>
                                        <div class="small">
                                            <a href="tel:{{ $delivery->customer_phone }}" class="text-decoration-none text-muted">
                                                <i class="fa-solid fa-phone me-1 text-success"></i> {{ $delivery->customer_phone }}
                                            </a>
                                        </div>
                                        @if($delivery->destination_address)
                                            <div class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $delivery->destination_address }}">
                                                <i class="fa-solid fa-location-dot me-1 text-danger"></i> {{ $delivery->destination_address }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle-sm bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                                {{ mb_substr($delivery->user->name ?? 'D', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $delivery->user->name ?? '—' }}</div>
                                                <div class="small text-muted">{{ $delivery->branch->name ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusBadge = match($delivery->status) {
                                                'in_transit' => 'bg-success',
                                                'delivered' => 'bg-info',
                                                'arrived' => 'bg-primary',
                                                'cancelled' => 'bg-danger',
                                                default => 'bg-warning text-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge }} d-inline-flex align-items-center">
                                            @if($delivery->status === 'in_transit')<span class="delivery-pulse-dot"></span>@endif
                                            {{ $delivery->status_label }}
                                        </span>
                                        @if($delivery->last_ping_at && $delivery->is_active)
                                            <div class="text-muted" style="font-size: 10px;">
                                                <i class="fa-regular fa-clock me-1"></i> {{ $delivery->last_ping_at->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $delivery->formatted_duration }}</div>
                                        <div class="small text-muted" style="font-size: 11px;">
                                            {{ $delivery->created_at->format('Y-m-d H:i') }} ICT
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary btn-open-delivery-details" data-delivery-id="{{ $delivery->id }}" title="{{ __('deliveries.view_details') }}">
                                                <i class="fa-solid fa-map-location-dot me-1"></i> {{ __('deliveries.view_details') }}
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-copy-track-link" data-url="{{ $delivery->tracking_url }}" title="{{ __('deliveries.copy_tracking_link') }}">
                                                <i class="fa-solid fa-share-nodes"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="visually-hidden">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <form action="{{ route('admin.deliveries.status', $delivery->id) }}" method="post">
                                                        @csrf
                                                        <input type="hidden" name="status" value="delivered">
                                                        <button type="submit" class="dropdown-item text-success"><i class="fa-solid fa-check me-2"></i> {{ __('deliveries.mark_delivered') }}</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.deliveries.status', $delivery->id) }}" method="post">
                                                        @csrf
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-xmark me-2"></i> {{ __('deliveries.mark_cancelled') }}</button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('admin.deliveries.destroy', $delivery->id) }}" method="post" onsubmit="return confirm('{{ __('deliveries.confirm_delete') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-muted"><i class="fa-regular fa-trash-can me-2"></i> {{ __('index.delete') }}</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="dr-empty-state py-5 text-center">
                                            <div class="dr-empty-icon mb-2"><i class="fa-solid fa-truck fs-1 text-muted opacity-50"></i></div>
                                            <div class="dr-empty-title fw-bold text-dark">{{ __('deliveries.no_deliveries_found') }}</div>
                                            <p class="dr-empty-desc text-muted small">{{ __('deliveries.no_deliveries_help') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($rows->hasPages())
                    <div class="p-3 border-top">{{ $rows->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Tab 2: Live Fleet Map --}}
        <div class="tab-pane fade" id="tab-map-pane" role="tabpanel" aria-labelledby="tab-map-btn">
            <div class="dr-card p-0 overflow-hidden position-relative border rounded-3 shadow-xs">
                {{-- Floating Top Map Action Bar --}}
                <div class="position-absolute top-0 start-0 end-0 m-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="z-index: 1000;">
                    <div class="bg-white p-2 rounded-3 shadow-sm border d-flex align-items-center gap-2">
                        <i class="fa-solid fa-satellite-dish text-success fs-5"></i>
                        <div>
                            <div class="fw-bold text-dark small">{{ __('deliveries.live_fleet_tracking') }}</div>
                            <div class="text-muted" style="font-size: 11px;">
                                <span class="delivery-pulse-dot"></span> <span id="fleetStatusText">{{ __('deliveries.auto_refresh') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-light border shadow-sm fw-semibold" id="btnFitAllFleet">
                            <i class="fa-solid fa-expand me-1 text-primary"></i> {{ __('deliveries.fit_all_drivers') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-light border shadow-sm fw-semibold" id="btnRefreshFleet">
                            <i class="fa-solid fa-arrows-rotate text-success"></i>
                        </button>
                    </div>
                </div>

                {{-- Leaflet Map Container --}}
                <div id="fleetMap" style="min-height: 600px; height: 68vh; width: 100%; background: #f8fafc;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 1: Delivery Details & Journey Modal --}}
<div class="modal fade" id="modalDeliveryDetails" tabindex="-1" aria-labelledby="modalDeliveryDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom py-3 px-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary px-2 py-1"><i class="fa-solid fa-truck-fast me-1"></i> {{ __('deliveries.delivery_details') }}</span>
                        <span class="badge" id="modalDeliveryStatusBadge"></span>
                    </div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalDeliveryDetailsLabel">
                        <span id="modalDeliveryCode">—</span> · <small class="text-light opacity-75 fs-6" id="modalDeliveryInvoice"></small>
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 align-items-stretch">
                    {{-- Left Column: Details & Stepper --}}
                    <div class="col-lg-5 d-flex flex-column gap-3">
                        {{-- Customer Card --}}
                        <div class="card border rounded-3 shadow-xs">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-user-tag text-primary me-1"></i> {{ __('deliveries.customer') }}</h6>
                                    <a href="#" class="btn btn-xs btn-outline-success" id="modalCallCustomerBtn">
                                        <i class="fa-solid fa-phone me-1"></i> {{ __('deliveries.call_customer') }}
                                    </a>
                                </div>
                                <div class="fw-bold text-dark" id="modalCustomerName">—</div>
                                <div class="small text-muted" id="modalCustomerPhone">—</div>
                                <div class="small text-muted mt-1">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                    <span id="modalCustomerAddress">—</span>
                                </div>
                            </div>
                        </div>

                        {{-- Driver Card --}}
                        <div class="card border rounded-3 shadow-xs">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-motorcycle text-primary me-1"></i> {{ __('deliveries.driver') }}</h6>
                                    <span class="badge bg-light text-dark border" id="modalDriverBranch">—</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle-sm bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" id="modalDriverAvatar">
                                        D
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" id="modalDriverName">—</div>
                                        <div class="small text-muted" id="modalDriverPhone">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Public Tracking Link --}}
                        <div class="card border rounded-3 shadow-xs bg-light">
                            <div class="card-body p-3">
                                <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-share-nodes text-primary me-1"></i> {{ __('deliveries.public_tracking_link') }}
                                </div>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control bg-white" id="modalPublicTrackUrl" readonly>
                                    <button class="btn btn-primary" type="button" id="btnModalCopyTrackUrl">
                                        <i class="fa-regular fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Photos & Proofs Section --}}
                        <div class="card border rounded-3 shadow-xs">
                            <div class="card-header bg-white py-2 px-3 border-bottom">
                                <h6 class="fw-bold text-dark mb-0 small">
                                    <i class="fa-solid fa-camera text-primary me-1"></i> {{ __('deliveries.photos_and_proofs') }}
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex flex-wrap gap-2" id="modalPhotosContainer">
                                    <!-- Rendered dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Interactive Route Map --}}
                    <div class="col-lg-7 d-flex flex-column">
                        <div class="card border rounded-3 h-100 shadow-xs flex-fill">
                            <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <span class="fw-bold small text-dark"><i class="fa-solid fa-map-location-dot text-primary me-1"></i> {{ __('deliveries.delivery_journey_map') }}</span>
                                <a href="#" target="_blank" class="btn btn-xs btn-outline-secondary" id="modalGoogleMapsLink">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Google Maps
                                </a>
                            </div>
                            <div class="card-body p-0 position-relative">
                                <div id="detailMap" style="min-height: 480px; height: 100%; border-radius: 0 0 12px 12px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4 justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">{{ __('deliveries.duration') }}: <strong id="modalDeliveryDuration" class="text-dark">—</strong></span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    {{ __('index.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: Create / Quick Dispatch Modal --}}
<div class="modal fade" id="modalCreateDelivery" tabindex="-1" aria-labelledby="modalCreateDeliveryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.deliveries.store') }}" method="post" class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            @csrf
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark mb-0" id="modalCreateDeliveryLabel">
                    <i class="fa-solid fa-truck-fast text-primary me-1"></i> {{ __('deliveries.dispatch_new_delivery') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="inputUserId">{{ __('deliveries.driver') }}</label>
                        <select class="form-select" id="inputUserId" name="user_id" required>
                            <option value="">{{ __('deliveries.all_drivers') }}...</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->name }} ({{ $driver->phone ?? 'No phone' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="inputBranchId">{{ __('deliveries.branch') }}</label>
                        <select class="form-select" id="inputBranchId" name="branch_id">
                            <option value="">{{ __('deliveries.all_branches') }}...</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="inputDeliveryType">{{ __('deliveries.delivery_type') }}</label>
                        <select class="form-select" id="inputDeliveryType" name="delivery_type" required>
                            <option value="customer">{{ __('deliveries.type_customer') }}</option>
                            <option value="branch">{{ __('deliveries.type_branch') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="inputInvoice">{{ __('deliveries.invoice_number') }}</label>
                        <input type="text" class="form-control" id="inputInvoice" name="invoice_number" placeholder="ឧ. INV-2026-0012">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required" for="inputCustomerName">{{ __('deliveries.customer_name') }}</label>
                        <input type="text" class="form-control" id="inputCustomerName" name="customer_name" required placeholder="ឈ្មោះអ្នកទទួល...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required" for="inputCustomerPhone">{{ __('deliveries.customer_phone') }}</label>
                        <input type="text" class="form-control" id="inputCustomerPhone" name="customer_phone" required placeholder="012 345 678">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="inputAddress">{{ __('deliveries.destination_address') }}</label>
                        <textarea class="form-control" id="inputAddress" name="destination_address" rows="2" placeholder="ផ្ទះលេខ... ផ្លូវ... ខណ្ឌ... រាជធានីភ្នំពេញ"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="inputDestLat">Destination Latitude (ជម្រើស)</label>
                        <input type="number" step="any" class="form-control" id="inputDestLat" name="destination_latitude" placeholder="11.5564">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="inputDestLng">Destination Longitude (ជម្រើស)</label>
                        <input type="number" step="any" class="form-control" id="inputDestLng" name="destination_longitude" placeholder="104.9282">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="inputNotes">{{ __('deliveries.notes') }}</label>
                        <input type="text" class="form-control" id="inputNotes" name="notes" placeholder="កំណត់ចំណាំទំនិញងាយបែក ឬម៉ោងត្រូវប្រគល់...">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-paper-plane me-1"></i> {{ __('deliveries.save_dispatch') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 3: Photo Lightbox Modal --}}
<div class="modal fade" id="modalDeliveryPhotoViewer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-secondary py-2 px-3 align-items-center justify-content-between" style="background: rgba(15, 23, 42, 0.95);">
                <span class="small fw-bold text-white" id="photoViewerTitle">រូបភាពភស្តុតាង</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 d-flex align-items-center justify-content-center" style="min-height: 400px; max-height: 80vh; background: #020617;">
                <img id="photoViewerImage" src="" alt="Proof Photo" class="img-fluid rounded" style="max-height: 75vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // Tab switching and URL sync
    // -------------------------------------------------------------
    const btnSwitchToFleetMap = document.getElementById('btnSwitchToFleetMap');
    const tabMapBtn = document.getElementById('tab-map-btn');
    if (btnSwitchToFleetMap && tabMapBtn) {
        btnSwitchToFleetMap.addEventListener('click', function () {
            const bsTab = new bootstrap.Tab(tabMapBtn);
            bsTab.show();
        });
    }

    // -------------------------------------------------------------
    // Fleet Live Map Logic
    // -------------------------------------------------------------
    let fleetMap = null;
    let fleetMarkersGroup = null;
    let fleetPollingTimer = null;

    tabMapBtn.addEventListener('shown.bs.tab', function () {
        initOrUpdateFleetMap();
        startFleetPolling();
    });

    function initOrUpdateFleetMap() {
        const mapContainer = document.getElementById('fleetMap');
        if (!mapContainer) return;

        if (!fleetMap) {
            fleetMap = L.map(mapContainer, {
                zoomControl: true,
                attributionControl: true
            }).setView([11.5564, 104.9282], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(fleetMap);

            fleetMarkersGroup = L.featureGroup().addTo(fleetMap);
        }

        fleetMap.invalidateSize();
        fetchFleetLocations();
    }

    function fetchFleetLocations() {
        fetch("{{ route('admin.deliveries.live-locations') }}")
            .then(res => res.json())
            .then(data => {
                if (!data.status || !fleetMap || !fleetMarkersGroup) return;

                fleetMarkersGroup.clearLayers();
                const deliveries = data.deliveries || [];

                document.getElementById('badgeActiveFleetCount').textContent = deliveries.length;

                deliveries.forEach(function (d) {
                    if (!d.current_latitude || !d.current_longitude) return;

                    const latlng = [d.current_latitude, d.current_longitude];
                    const pinClass = d.status === 'arrived' ? 'arrived' : 'in_transit';

                    const customIcon = L.divIcon({
                        className: '',
                        html: `<div class="leaflet-driver-pin ${pinClass}"><i class="fa-solid fa-motorcycle"></i></div>`,
                        iconSize: [36, 36],
                        iconAnchor: [18, 18],
                        popupAnchor: [0, -18]
                    });

                    const marker = L.marker(latlng, { icon: customIcon });

                    marker.bindPopup(`
                        <div class="p-1" style="min-width: 200px;">
                            <div class="fw-bold text-dark fs-6 mb-1">${escapeHtml(d.driver_name)}</div>
                            <div class="small text-muted mb-1"><i class="fa-solid fa-phone me-1 text-success"></i> ${escapeHtml(d.driver_phone)}</div>
                            <div class="small mb-1"><span class="badge bg-primary">${escapeHtml(d.status_label)}</span> · ${d.speed || 0} km/h</div>
                            <div class="small text-muted mb-2">📦 ភ្ញៀវ: <strong>${escapeHtml(d.customer_name)}</strong></div>
                            <button type="button" class="btn btn-xs btn-primary w-100 btn-open-delivery-details" data-delivery-id="${d.id}">
                                មើលដំណើរការលម្អិត
                            </button>
                        </div>
                    `);

                    fleetMarkersGroup.addLayer(marker);

                    // Destination Marker & line if coords exist
                    if (d.destination_latitude && d.destination_longitude) {
                        const destMarker = L.marker([d.destination_latitude, d.destination_longitude], {
                            icon: L.divIcon({
                                className: '',
                                html: `<div class="leaflet-destination-pin"><i class="fa-solid fa-flag-checkered"></i></div>`,
                                iconSize: [30, 30],
                                iconAnchor: [15, 15]
                            })
                        }).bindPopup(`<b>គោលដៅ: ${escapeHtml(d.customer_name)}</b><br><small>${escapeHtml(d.destination_address || '')}</small>`);

                        fleetMarkersGroup.addLayer(destMarker);

                        L.polyline([latlng, [d.destination_latitude, d.destination_longitude]], {
                            color: '#3b82f6',
                            dashArray: '6, 6',
                            weight: 3,
                            opacity: 0.7
                        }).addTo(fleetMarkersGroup);
                    }
                });

                if (fleetMarkersGroup.getLayers().length > 0) {
                    const bounds = fleetMarkersGroup.getBounds();
                    if (bounds.isValid()) {
                        fleetMap.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
                    }
                }
            })
            .catch(err => console.error('Failed to load fleet pings:', err));
    }

    function startFleetPolling() {
        if (fleetPollingTimer) clearInterval(fleetPollingTimer);
        fleetPollingTimer = setInterval(fetchFleetLocations, 10000); // 10s interval
    }

    document.getElementById('btnRefreshFleet').addEventListener('click', fetchFleetLocations);
    document.getElementById('btnFitAllFleet').addEventListener('click', function () {
        if (fleetMarkersGroup && fleetMarkersGroup.getLayers().length > 0) {
            fleetMap.fitBounds(fleetMarkersGroup.getBounds(), { padding: [40, 40], maxZoom: 15 });
        }
    });

    // -------------------------------------------------------------
    // Delivery Details Modal & Single Route Map
    // -------------------------------------------------------------
    const modalDetailsEl = document.getElementById('modalDeliveryDetails');
    const bsDetailsModal = new bootstrap.Modal(modalDetailsEl);
    let detailMap = null;
    let detailMarkersGroup = null;

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-open-delivery-details');
        if (btn) {
            e.preventDefault();
            const id = btn.getAttribute('data-delivery-id');
            openDeliveryDetails(id);
        }

        const copyBtn = e.target.closest('.btn-copy-track-link');
        if (copyBtn) {
            e.preventDefault();
            const url = copyBtn.getAttribute('data-url');
            navigator.clipboard.writeText(url).then(() => {
                alert("{{ __('deliveries.tracking_link_copied') }}");
            });
        }
    });

    document.getElementById('btnModalCopyTrackUrl').addEventListener('click', function () {
        const input = document.getElementById('modalPublicTrackUrl');
        input.select();
        navigator.clipboard.writeText(input.value).then(() => {
            alert("{{ __('deliveries.tracking_link_copied') }}");
        });
    });

    function openDeliveryDetails(id) {
        fetch(`{{ url('admin/deliveries') }}/${id}`)
            .then(res => res.json())
            .then(data => {
                if (!data.status || !data.delivery) return;
                populateDetailsModal(data.delivery);
                bsDetailsModal.show();
            })
            .catch(err => console.error('Failed to fetch delivery:', err));
    }

    function populateDetailsModal(d) {
        document.getElementById('modalDeliveryCode').textContent = d.tracking_code || ('DEL-#' + d.id);
        document.getElementById('modalDeliveryInvoice').textContent = d.invoice_number ? ('Invoice: ' + d.invoice_number) : '';

        const badge = document.getElementById('modalDeliveryStatusBadge');
        badge.className = 'badge ' + (d.status === 'in_transit' ? 'bg-success' : (d.status === 'delivered' ? 'bg-info' : 'bg-warning text-dark'));
        badge.textContent = d.status_label;

        document.getElementById('modalCustomerName').textContent = d.customer_name;
        document.getElementById('modalCustomerPhone').textContent = d.customer_phone;
        document.getElementById('modalCustomerAddress').textContent = d.destination_address || '—';
        document.getElementById('modalCallCustomerBtn').href = 'tel:' + d.customer_phone;

        document.getElementById('modalDriverName').textContent = d.user ? d.user.name : '—';
        document.getElementById('modalDriverPhone').textContent = d.user ? (d.user.phone || '—') : '—';
        document.getElementById('modalDriverBranch').textContent = d.branch ? d.branch.name : '—';
        document.getElementById('modalPublicTrackUrl').value = d.tracking_url;
        document.getElementById('modalDeliveryDuration').textContent = d.formatted_duration;

        // External Google Maps directions
        const gLink = document.getElementById('modalGoogleMapsLink');
        if (d.destination_latitude && d.destination_longitude) {
            gLink.href = `https://www.google.com/maps/dir/?api=1&destination=${d.destination_latitude},${d.destination_longitude}`;
            gLink.classList.remove('d-none');
        } else {
            gLink.classList.add('d-none');
        }

        // Photos container
        const photosContainer = document.getElementById('modalPhotosContainer');
        photosContainer.innerHTML = '';
        let hasPhoto = false;

        if (d.driver_selfie_url) {
            hasPhoto = true;
            photosContainer.appendChild(createPhotoThumb(d.driver_selfie_url, 'Selfie អ្នកដឹក'));
        }

        (d.product_photo_urls || []).forEach((url, i) => {
            hasPhoto = true;
            photosContainer.appendChild(createPhotoThumb(url, `ទំនិញ #${i+1}`));
        });

        if (d.proof_photo_url) {
            hasPhoto = true;
            photosContainer.appendChild(createPhotoThumb(d.proof_photo_url, 'ភស្តុតាងប្រគល់ (POD)'));
        }

        if (!hasPhoto) {
            photosContainer.innerHTML = '<span class="text-muted small fst-italic">{{ __('deliveries.no_proof_photo') }}</span>';
        }

        // Trigger map render after modal shows
        modalDetailsEl.addEventListener('shown.bs.modal', function onModalShown() {
            renderDetailMap(d);
            modalDetailsEl.removeEventListener('shown.bs.modal', onModalShown);
        });
    }

    function createPhotoThumb(url, label) {
        const div = document.createElement('div');
        div.className = 'd-flex flex-column align-items-center gap-1';
        div.innerHTML = `
            <div class="delivery-photo-thumb" data-photo-url="${url}" data-photo-title="${escapeHtml(label)}">
                <img src="${url}" alt="${escapeHtml(label)}" loading="lazy">
            </div>
            <span class="text-muted" style="font-size: 10px;">${escapeHtml(label)}</span>
        `;
        return div;
    }

    function renderDetailMap(d) {
        const container = document.getElementById('detailMap');
        if (!container) return;

        if (!detailMap) {
            detailMap = L.map(container).setView([11.5564, 104.9282], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(detailMap);

            detailMarkersGroup = L.featureGroup().addTo(detailMap);
        }

        detailMarkersGroup.clearLayers();
        detailMap.invalidateSize();

        const latlngs = [];

        // 1. Driver Current Location
        if (d.current_latitude && d.current_longitude) {
            const driverLoc = [d.current_latitude, d.current_longitude];
            latlngs.push(driverLoc);

            const m = L.marker(driverLoc, {
                icon: L.divIcon({
                    className: '',
                    html: `<div class="leaflet-driver-pin in_transit"><i class="fa-solid fa-motorcycle"></i></div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                })
            }).bindPopup(`<b>អ្នកដឹក: ${escapeHtml(d.user ? d.user.name : '')}</b><br>ល្បឿន: ${d.speed || 0} km/h`);

            detailMarkersGroup.addLayer(m);
        }

        // 2. Destination Location
        if (d.destination_latitude && d.destination_longitude) {
            const destLoc = [d.destination_latitude, d.destination_longitude];
            latlngs.push(destLoc);

            const dm = L.marker(destLoc, {
                icon: L.divIcon({
                    className: '',
                    html: `<div class="leaflet-destination-pin"><i class="fa-solid fa-flag-checkered"></i></div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                })
            }).bindPopup(`<b>គោលដៅទទួល: ${escapeHtml(d.customer_name)}</b><br><small>${escapeHtml(d.destination_address || '')}</small>`);

            detailMarkersGroup.addLayer(dm);
        }

        // Polyline connecting driver to destination
        if (latlngs.length > 1) {
            L.polyline(latlngs, {
                color: '#2563eb',
                weight: 4,
                opacity: 0.85
            }).addTo(detailMarkersGroup);
        }

        if (detailMarkersGroup.getLayers().length > 0) {
            detailMap.fitBounds(detailMarkersGroup.getBounds(), { padding: [50, 50], maxZoom: 16 });
        } else {
            detailMap.setView([11.5564, 104.9282], 12);
        }
    }

    // -------------------------------------------------------------
    // Photo Lightbox Viewer
    // -------------------------------------------------------------
    const photoViewerEl = document.getElementById('modalDeliveryPhotoViewer');
    const bsPhotoViewer = new bootstrap.Modal(photoViewerEl);

    document.addEventListener('click', function (e) {
        const thumb = e.target.closest('.delivery-photo-thumb');
        if (thumb) {
            const url = thumb.getAttribute('data-photo-url');
            const title = thumb.getAttribute('data-photo-title');
            document.getElementById('photoViewerImage').src = url;
            document.getElementById('photoViewerTitle').textContent = title || 'រូបថតភស្តុតាង';
            bsPhotoViewer.show();
        }
    });

    photoViewerEl.addEventListener('show.bs.modal', function () {
        setTimeout(() => {
            const bds = document.querySelectorAll('.modal-backdrop');
            if (bds.length > 1) bds[bds.length - 1].classList.add('viewer-backdrop');
        }, 10);
    });

    photoViewerEl.addEventListener('hidden.bs.modal', function () {
        if (modalDetailsEl.classList.contains('show')) {
            document.body.classList.add('modal-open');
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
