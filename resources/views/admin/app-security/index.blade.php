@extends('layouts.master')
@section('title', __('app_security.title'))

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/app-security.css') }}?v={{ filemtime(public_path('assets/css/app-security.css')) }}">
@endsection

@section('main-content')
<div class="container-fluid security-wrap py-3">

    {{-- System Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('danger'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation fs-5 text-danger"></i>
                <div class="fw-semibold">{{ session('danger') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-shield-xmark fs-5 text-danger"></i>
                <div class="fw-semibold">{{ $errors->first() }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Hero Command Banner --}}
    <div class="security-hero-banner mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="hero-brand-pill">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>{{ __('app_security.kneayerng_security') }}</span>
                </div>
                <h1 class="hero-title text-white">{{ __('app_security.title') }}</h1>
                <p class="hero-subtitle mb-0">{{ __('app_security.subtitle') }}</p>
            </div>
            <div class="col-lg-5 text-lg-end">
                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 mb-2">
                    <span class="status-pill {{ $securityEnabled ? 'status-active' : 'status-inactive' }}">
                        <span class="pulse-dot"></span>
                        {{ $securityEnabled ? __('app_security.enforcement_enabled') : __('app_security.enforcement_disabled') }}
                    </span>
                </div>
                <div class="small text-white-50 mb-3">
                    @unless($supportOnly)
                        <i class="fa-brands fa-android me-1 text-info"></i>
                        {{ $release ? __('app_security.active_release', ['name' => $release->version_name, 'code' => $release->version_code]) : __('app_security.no_active_release') }}
                    @endunless
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                    <a class="btn btn-sm btn-outline-light rounded-pill px-3" href="{{ route('admin.showQR') }}" target="_blank" rel="noopener">
                        <i class="fa-solid fa-qrcode me-1"></i> {{ __('app_security.pair_mobile_app') }}
                    </a>
                    @if($canExportAuditLogs)
                        <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#exportAuditModal">
                            <i class="fa-solid fa-file-arrow-down me-1"></i> {{ __('app_security.export_audit_csv') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Executive KPI Metric Cards --}}
    <div class="row g-3 mb-4">
        @php
            $kpiCards = [
                ['key' => 'pending', 'label' => __('app_security.pending_devices'), 'val' => $counts['pending'], 'cls' => 'kpi-pending', 'icon' => 'fa-solid fa-clock-rotate-left', 'badge' => 'Attention', 'badgeCls' => 'bg-warning text-dark', 'status' => 'pending', 'paramKey' => 'status', 'paramVal' => 'pending'],
                ['key' => 'approved', 'label' => __('app_security.approved_devices'), 'val' => $counts['approved'], 'cls' => 'kpi-approved', 'icon' => 'fa-solid fa-shield-check', 'badge' => 'Active', 'badgeCls' => 'bg-success text-white', 'status' => 'approved', 'paramKey' => 'status', 'paramVal' => 'approved'],
                ['key' => 'blocked', 'label' => __('app_security.blocked_devices'), 'val' => $counts['blocked'], 'cls' => 'kpi-blocked', 'icon' => 'fa-solid fa-ban', 'badge' => 'Restricted', 'badgeCls' => 'bg-danger text-white', 'status' => 'blocked', 'paramKey' => 'status', 'paramVal' => 'blocked'],
                ['key' => 'revoked', 'label' => __('app_security.revoked_devices'), 'val' => $counts['revoked'], 'cls' => 'kpi-revoked', 'icon' => 'fa-solid fa-trash-can', 'badge' => 'Retired', 'badgeCls' => 'bg-secondary text-white', 'status' => 'revoked', 'paramKey' => 'status', 'paramVal' => 'revoked'],
            ];
            if (!$supportOnly) {
                $kpiCards[] = ['key' => 'high_risk', 'label' => __('app_security.high_risk_devices'), 'val' => $counts['high_risk'], 'cls' => 'kpi-highrisk', 'icon' => 'fa-solid fa-fire-flame-curved', 'badge' => 'Critical', 'badgeCls' => 'bg-danger text-white', 'status' => '', 'paramKey' => 'risk_level', 'paramVal' => 'high'];
            }
        @endphp

        @foreach($kpiCards as $kpi)
            <div class="col-6 col-md-4 col-xl">
                <div class="security-kpi-card {{ $kpi['cls'] }}" onclick="filterByStatus('{{ $kpi['status'] }}', '{{ $kpi['paramKey'] ?? '' }}', '{{ $kpi['paramVal'] ?? '' }}')">
                    <span class="badge {{ $kpi['badgeCls'] }} kpi-badge">{{ $kpi['badge'] }}</span>
                    <div class="kpi-icon-wrap">
                        <i class="{{ $kpi['icon'] }}"></i>
                    </div>
                    <div class="kpi-count">{{ number_format($kpi['val']) }}</div>
                    <div class="kpi-label">{{ $kpi['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Step-by-Step Navigation Tabs --}}
    <div class="security-step-nav mb-4" id="securityTabs" role="tablist">
        <button class="nav-link active" id="tab-btn-devices" data-bs-toggle="tab" data-bs-target="#tab-pane-devices" type="button" role="tab" aria-controls="tab-pane-devices" aria-selected="true">
            <i class="fa-solid fa-mobile-screen-button step-icon text-primary"></i>
            <span>{{ __('app_security.step_devices') }}</span>
            <span class="badge bg-primary rounded-pill ms-1">{{ $devices->total() }}</span>
        </button>

        @if(!$supportOnly && $canManageSettings)
            <button class="nav-link" id="tab-btn-policy" data-bs-toggle="tab" data-bs-target="#tab-pane-policy" type="button" role="tab" aria-controls="tab-pane-policy" aria-selected="false">
                <i class="fa-solid fa-sliders step-icon text-info"></i>
                <span>{{ __('app_security.step_policy') }}</span>
                <span class="badge {{ $securityEnabled ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill ms-1">{{ $securityEnabled ? 'ON' : 'OFF' }}</span>
            </button>
        @endif

        @unless($supportOnly)
            <button class="nav-link" id="tab-btn-releases" data-bs-toggle="tab" data-bs-target="#tab-pane-releases" type="button" role="tab" aria-controls="tab-pane-releases" aria-selected="false">
                <i class="fa-solid fa-box-archive step-icon text-success"></i>
                <span>{{ __('app_security.step_releases') }}</span>
                <span class="badge bg-secondary rounded-pill ms-1">{{ $releases->count() }}</span>
            </button>
        @endunless

        @if($canViewSecurityAlerts)
            @php $openAlertsCount = $securityAlerts->whereNull('acknowledged_at')->count(); @endphp
            <button class="nav-link" id="tab-btn-alerts" data-bs-toggle="tab" data-bs-target="#tab-pane-alerts" type="button" role="tab" aria-controls="tab-pane-alerts" aria-selected="false">
                <i class="fa-solid fa-triangle-exclamation step-icon text-warning"></i>
                <span>{{ __('app_security.step_alerts') }}</span>
                @if($openAlertsCount > 0)
                    <span class="badge bg-danger rounded-pill ms-1">{{ $openAlertsCount }}</span>
                @else
                    <span class="badge bg-light text-muted border rounded-pill ms-1">0</span>
                @endif
            </button>
        @endif

        @if(!$supportOnly && $canAssignAdminRoles)
            <button class="nav-link" id="tab-btn-roles" data-bs-toggle="tab" data-bs-target="#tab-pane-roles" type="button" role="tab" aria-controls="tab-pane-roles" aria-selected="false">
                <i class="fa-solid fa-user-shield step-icon text-purple" style="color: #8b5cf6;"></i>
                <span>{{ __('app_security.step_admin_roles') }}</span>
                <span class="badge bg-light text-dark border rounded-pill ms-1">{{ $adminUsers->count() }}</span>
            </button>
        @endif

        @if($canViewAnalytics && $analytics)
            <button class="nav-link" id="tab-btn-analytics" data-bs-toggle="tab" data-bs-target="#tab-pane-analytics" type="button" role="tab" aria-controls="tab-pane-analytics" aria-selected="false">
                <i class="fa-solid fa-chart-line step-icon text-danger"></i>
                <span>{{ __('app_security.step_analytics') }}</span>
            </button>
        @endif
    </div>

    {{-- Step Tab Contents --}}
    <div class="tab-content" id="securityTabsContent">

        {{-- ==========================================
             TAB 1: REGISTERED DEVICES FLEET
             ========================================== --}}
        <div class="tab-pane fade show active" id="tab-pane-devices" role="tabpanel" aria-labelledby="tab-btn-devices">
            <div class="security-panel-card">
                {{-- Panel Header with Fleet Summary Telemetry --}}
                <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon-wrap" style="width: 44px; height: 44px; font-size: 20px; border-radius: 12px; background: rgba(29, 78, 216, 0.1); color: var(--sec-primary);">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <div>
                            <h5 class="panel-title mb-0">
                                <span>{{ __('app_security.registered_devices') }}</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill fs-6 px-3 ms-2">
                                    {{ number_format($devices->total()) }} {{ __('app_security.installations') }}
                                </span>
                            </h5>
                            <div class="text-muted small mt-1">
                                Zero-trust inventory of all mobile devices connected via cryptographic API verification
                            </div>
                        </div>
                    </div>

                    {{-- Fleet Telemetry Status Summary --}}
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill" title="Devices with active heartbeat in the last 24 hours">
                            <span class="conn-pulse-dot online me-1"></span>
                            <strong>{{ number_format($counts['online']) }}</strong> {{ __('app_security.chip_online') }}
                        </span>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill" title="Hardware backed keys verified with StrongBox or TEE Keystore">
                            <i class="fa-solid fa-shield-halved me-1"></i>
                            <strong>{{ number_format($counts['attested']) }}</strong> {{ __('app_security.chip_attested') }}
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill" title="Devices awaiting administrator approval">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>
                            <strong>{{ number_format($counts['pending']) }}</strong> {{ __('app_security.status_pending') }}
                        </span>
                        @if(($counts['blocked'] + $counts['high_risk']) > 0)
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill" title="Blocked access or high risk security score flagged">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                <strong>{{ number_format($counts['blocked'] + $counts['high_risk']) }}</strong> {{ __('app_security.chip_high_risk') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Toolbar & Filter Section --}}
                <div class="fleet-toolbar-wrapper">
                    {{-- 1. Quick Filter Chips Bar --}}
                    @php
                        $isAllActive = !request()->anyFilled(['status', 'user_type', 'attestation_level', 'risk_level', 'activity']);
                    @endphp
                    <div class="fleet-quick-chips">
                        <span class="text-muted small fw-bold text-uppercase me-2" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-bolt me-1 text-primary"></i> Quick Views:
                        </span>

                        <a href="{{ route('admin.app-security.index') }}" class="fleet-chip {{ $isAllActive ? 'active' : '' }}">
                            <i class="fa-solid fa-layer-group"></i> {{ __('app_security.chip_all') }}
                            <span class="badge bg-light text-dark rounded-pill ms-1">{{ $counts['total'] }}</span>
                        </a>

                        <a href="{{ route('admin.app-security.index', ['status' => 'pending']) }}" class="fleet-chip {{ request('status') === 'pending' ? 'active' : '' }}">
                            <span class="chip-dot bg-warning"></span> {{ __('app_security.chip_pending') }}
                            <span class="badge bg-light text-dark rounded-pill ms-1">{{ $counts['pending'] }}</span>
                        </a>

                        <a href="{{ route('admin.app-security.index', ['status' => 'approved']) }}" class="fleet-chip {{ request('status') === 'approved' ? 'active' : '' }}">
                            <span class="chip-dot bg-success"></span> {{ __('app_security.chip_approved') }}
                            <span class="badge bg-light text-dark rounded-pill ms-1">{{ $counts['approved'] }}</span>
                        </a>

                        <a href="{{ route('admin.app-security.index', ['activity' => 'today']) }}" class="fleet-chip {{ request('activity') === 'today' ? 'active' : '' }}">
                            <span class="conn-pulse-dot online me-1"></span> {{ __('app_security.chip_online') }}
                            <span class="badge bg-light text-dark rounded-pill ms-1">{{ $counts['online'] }}</span>
                        </a>

                        <a href="{{ route('admin.app-security.index', ['risk_level' => 'high']) }}" class="fleet-chip {{ request('risk_level') === 'high' ? 'active' : '' }}">
                            <i class="fa-solid fa-fire text-danger"></i> {{ __('app_security.chip_high_risk') }}
                            <span class="badge bg-light text-dark rounded-pill ms-1">{{ $counts['high_risk'] }}</span>
                        </a>

                        <a href="{{ route('admin.app-security.index', ['attestation_level' => 'strongbox']) }}" class="fleet-chip {{ request('attestation_level') === 'strongbox' ? 'active' : '' }}">
                            <i class="fa-solid fa-shield-halved text-info"></i> {{ __('app_security.chip_attested') }}
                            <span class="badge bg-light text-dark rounded-pill ms-1">{{ $counts['attested'] }}</span>
                        </a>

                        <a href="{{ route('admin.app-security.index', ['status' => 'blocked']) }}" class="fleet-chip {{ request('status') === 'blocked' ? 'active' : '' }}">
                            <i class="fa-solid fa-ban text-danger"></i> {{ __('app_security.chip_blocked') }}
                            <span class="badge bg-light text-dark rounded-pill ms-1">{{ $counts['blocked'] }}</span>
                        </a>

                        <a href="{{ route('admin.app-security.index', ['user_type' => 'staff']) }}" class="fleet-chip {{ request('user_type') === 'staff' ? 'active' : '' }}">
                            <i class="fa-solid fa-user-tie text-primary"></i> Staff
                        </a>

                        <a href="{{ route('admin.app-security.index', ['user_type' => 'customer']) }}" class="fleet-chip {{ request('user_type') === 'customer' ? 'active' : '' }}">
                            <i class="fa-solid fa-users text-purple"></i> Customer
                        </a>
                    </div>

                    {{-- 2. Advanced Multi-Field Filter Form --}}
                    <form method="get" action="{{ route('admin.app-security.index') }}" id="deviceFilterForm">
                        <div class="fleet-filter-grid">
                            {{-- Multi-Attribute Search Input --}}
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="q" id="fleetSearchInput" value="{{ request('q') }}" class="form-control border-start-0 border-end-0 ps-0" placeholder="{{ __('app_security.search_placeholder') }}">
                                @if(request()->filled('q'))
                                    <button type="button" class="btn btn-sm btn-outline-secondary border-start-0" onclick="document.getElementById('fleetSearchInput').value=''; document.getElementById('deviceFilterForm').submit();" title="Clear search text">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                @endif
                            </div>

                            {{-- Status Filter --}}
                            <select name="status" class="form-select form-select-sm" onchange="document.getElementById('deviceFilterForm').submit()">
                                <option value="">{{ __('app_security.all_statuses') }}</option>
                                @foreach(['pending' => __('app_security.status_pending'), 'approved' => __('app_security.status_approved'), 'blocked' => __('app_security.status_blocked'), 'rejected' => __('app_security.status_rejected'), 'revoked' => __('app_security.status_revoked'), 're_registration_required' => __('app_security.status_re_registration')] as $statusKey => $statusLabel)
                                    <option value="{{ $statusKey }}" @selected(request('status') === $statusKey)>{{ $statusLabel }}</option>
                                @endforeach
                            </select>

                            {{-- User Type Filter --}}
                            <select name="user_type" class="form-select form-select-sm" onchange="document.getElementById('deviceFilterForm').submit()">
                                <option value="">{{ __('app_security.all_user_types') }}</option>
                                <option value="staff" @selected(request('user_type') === 'staff')>{{ __('app_security.type_staff') }}</option>
                                <option value="customer" @selected(request('user_type') === 'customer')>{{ __('app_security.type_customer') }}</option>
                                <option value="admin" @selected(request('user_type') === 'admin')>{{ __('app_security.type_admin') }}</option>
                            </select>

                            {{-- Hardware Attestation Level Filter --}}
                            @unless($supportOnly)
                                <select name="attestation_level" class="form-select form-select-sm" onchange="document.getElementById('deviceFilterForm').submit()">
                                    <option value="">{{ __('app_security.all_attestation') }}</option>
                                    <option value="strongbox" @selected(request('attestation_level') === 'strongbox')>{{ __('app_security.attestation_strongbox') }}</option>
                                    <option value="tee" @selected(request('attestation_level') === 'tee')>{{ __('app_security.attestation_tee') }}</option>
                                    <option value="basic" @selected(request('attestation_level') === 'basic')>{{ __('app_security.attestation_basic') }}</option>
                                    <option value="none" @selected(request('attestation_level') === 'none')>{{ __('app_security.attestation_none') }}</option>
                                </select>
                            @endunless

                            {{-- Risk Severity Level Filter --}}
                            @unless($supportOnly)
                                <select name="risk_level" class="form-select form-select-sm" onchange="document.getElementById('deviceFilterForm').submit()">
                                    <option value="">{{ __('app_security.all_risk_levels') }}</option>
                                    <option value="low" @selected(request('risk_level') === 'low')>{{ __('app_security.risk_low') }}</option>
                                    <option value="medium" @selected(request('risk_level') === 'medium')>{{ __('app_security.risk_medium') }}</option>
                                    <option value="high" @selected(request('risk_level') === 'high')>{{ __('app_security.risk_high') }}</option>
                                </select>
                            @endunless

                            {{-- Connection Activity Filter --}}
                            <select name="activity" class="form-select form-select-sm" onchange="document.getElementById('deviceFilterForm').submit()">
                                <option value="">{{ __('app_security.all_activity') }}</option>
                                <option value="online" @selected(request('activity') === 'online')>{{ __('app_security.activity_online') }}</option>
                                <option value="today" @selected(request('activity') === 'today')>{{ __('app_security.activity_today') }}</option>
                                <option value="week" @selected(request('activity') === 'week')>{{ __('app_security.activity_week') }}</option>
                                <option value="inactive" @selected(request('activity') === 'inactive')>{{ __('app_security.activity_inactive') }}</option>
                            </select>

                            {{-- Sorting Dropdown --}}
                            <select name="sort_by" class="form-select form-select-sm" onchange="document.getElementById('deviceFilterForm').submit()">
                                <option value="last_seen" @selected(request('sort_by', 'last_seen') === 'last_seen')>{{ __('app_security.sort_last_seen_desc') }}</option>
                                <option value="registered" @selected(request('sort_by') === 'registered')>{{ __('app_security.sort_registered_desc') }}</option>
                                <option value="risk_score" @selected(request('sort_by') === 'risk_score')>{{ __('app_security.sort_risk_desc') }}</option>
                                <option value="model" @selected(request('sort_by') === 'model')>{{ __('app_security.sort_model_asc') }}</option>
                            </select>

                            {{-- Items Per Page --}}
                            <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="document.getElementById('deviceFilterForm').submit()">
                                @foreach([15, 25, 50, 100] as $limit)
                                    <option value="{{ $limit }}" @selected((int)request('per_page', 25) === $limit)>{{ $limit }} / page</option>
                                @endforeach
                            </select>

                            {{-- Action Buttons --}}
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-primary btn-sm px-3 rounded-pill" type="submit">
                                    <i class="fa-solid fa-filter me-1"></i> {{ __('app_security.filter_button') }}
                                </button>
                                @if(request()->anyFilled(['q', 'status', 'user_type', 'attestation_level', 'risk_level', 'activity', 'sort_by', 'per_page']))
                                    <a href="{{ route('admin.app-security.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Clear all active filters">
                                        <i class="fa-solid fa-rotate-left me-1"></i> {{ __('app_security.reset_filter') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Full-Information Device Fleet Table --}}
                <div class="table-responsive">
                    <table class="table security-table align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('app_security.user') }}</th>
                                <th>{{ __('app_security.device_info') }}</th>
                                <th>{{ __('app_security.last_seen') }}</th>
                                @unless($supportOnly)
                                    <th>{{ __('app_security.release') }}</th>
                                    <th>{{ __('app_security.trust_attestation') }}</th>
                                @endunless
                                <th>{{ __('app_security.status') }}</th>
                                <th class="text-end">{{ __('app_security.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($devices as $device)
                            @php
                                // Connection status calculation
                                $lastSeenMinutes = $device->last_seen_at ? $device->last_seen_at->diffInMinutes(now()) : null;
                                if ($lastSeenMinutes === null) {
                                    $connStatus = 'offline';
                                    $connLabel = 'Never Connected';
                                } elseif ($lastSeenMinutes <= 60) {
                                    $connStatus = 'online';
                                    $connLabel = 'Online Now';
                                } elseif ($lastSeenMinutes <= 1440) {
                                    $connStatus = 'today';
                                    $connLabel = 'Active Today';
                                } elseif ($lastSeenMinutes <= 10080) {
                                    $connStatus = 'week';
                                    $connLabel = 'Active This Week';
                                } else {
                                    $connStatus = 'offline';
                                    $connLabel = 'Offline';
                                }

                                // User type badge
                                $typeBadge = match($device->user_type) {
                                    'staff' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
                                    'admin' => 'bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25',
                                    default => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25'
                                };

                                // Attestation badge
                                $attChip = match(strtolower($device->attestation_level ?? 'none')) {
                                    'strongbox' => ['class' => 'chip-strongbox', 'icon' => 'fa-solid fa-shield-halved', 'label' => 'StrongBox'],
                                    'tee' => ['class' => 'chip-tee', 'icon' => 'fa-solid fa-microchip', 'label' => 'TEE Hardware'],
                                    'basic' => ['class' => 'chip-basic', 'icon' => 'fa-solid fa-certificate', 'label' => 'Basic Keystore'],
                                    default => ['class' => 'chip-failed', 'icon' => 'fa-solid fa-triangle-exclamation', 'label' => ucfirst($device->attestation_level ?: 'Unverified')]
                                };

                                // Risk meter
                                $score = (int) $device->risk_score;
                                $riskLevel = $score >= 60 ? 'risk-high' : ($score >= 25 ? 'risk-medium' : 'risk-low');
                                $riskColor = $score >= 60 ? 'text-danger' : ($score >= 25 ? 'text-warning' : 'text-success');

                                // Status badge
                                $statusClass = 'status-pending';
                                if ($device->device_status === 'approved' && !$device->blocked_at && !$device->revoked_at) {
                                    $statusClass = 'status-approved';
                                } elseif ($device->device_status === 'blocked' || $device->blocked_at) {
                                    $statusClass = 'status-blocked';
                                } elseif ($device->device_status === 'revoked' || $device->revoked_at) {
                                    $statusClass = 'status-revoked';
                                } elseif ($device->device_status === 're_registration_required') {
                                    $statusClass = 'status-re_registration_required';
                                } elseif ($device->device_status === 'rejected') {
                                    $statusClass = 'status-rejected';
                                }
                                $displayStatus = ($device->device_status === 'blocked' || $device->blocked_at) ? 'blocked' : $device->device_status;

                                // Telemetry payload for inspect modal
                                $inspectPayload = [
                                    'uuid' => $device->uuid,
                                    'installation_uuid' => $device->installation_uuid,
                                    'device_model' => $device->device_model ?: 'Android Device',
                                    'android_version' => $device->android_version ?: 'Unknown',
                                    'android_sdk' => $device->android_sdk ?: 0,
                                    'package_name' => $device->package_name,
                                    'version_name' => $device->version_name,
                                    'version_code' => $device->version_code,
                                    'signing_sha256' => $device->signing_sha256,
                                    'public_key_fingerprint' => $device->public_key_fingerprint,
                                    'attestation_level' => $device->attestation_level ?: 'none',
                                    'device_status' => $displayStatus,
                                    'risk_score' => $score,
                                    'user_name' => $device->user?->name ?? 'Unknown User',
                                    'user_username' => $device->user?->username ?? '—',
                                    'user_email' => $device->user?->email ?? '—',
                                    'user_phone' => $device->user?->phone ?? '—',
                                    'user_type' => $device->user_type,
                                    'approved_by' => $device->approver?->name ?? '—',
                                    'approved_at' => $device->approved_at?->format('Y-m-d H:i:s') ?? '—',
                                    'registered_at' => $device->registered_at?->format('Y-m-d H:i:s') ?? '—',
                                    'last_seen_at' => $device->last_seen_at?->format('Y-m-d H:i:s') ?? 'Never',
                                    'last_seen_diff' => $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never',
                                    'conn_status' => $connStatus,
                                    'conn_label' => $connLabel,
                                    'active_sessions_count' => $device->active_sessions_count ?? 0,
                                    'sessions_count' => $device->sessions_count ?? 0,
                                    'risk_signals' => data_get($device->latestRiskScore?->reasons, 'signals', []),
                                    'sessions_url' => route('admin.app-security.devices.sessions', $device->uuid),
                                    'review_url' => route('admin.app-security.devices.review', $device->uuid),
                                ];
                            @endphp
                            <tr>
                                {{-- 1. User Profile & Role --}}
                                <td>
                                    <div class="device-user-box">
                                        <div class="device-user-avatar">
                                            @if($device->user?->avatar)
                                                <img src="{{ asset(\App\Models\User::AVATAR_UPLOAD_PATH . $device->user->avatar) }}" alt="{{ $device->user->name }}" class="w-100 h-100 rounded-3 object-fit-cover">
                                            @else
                                                {{ strtoupper(substr($device->user?->name ?? 'D', 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="device-user-name">{{ $device->user?->name ?? 'Deleted account' }}</div>
                                            <div class="device-user-meta">{{ $device->user?->username ?? '—' }}</div>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span class="badge {{ $typeBadge }} px-2 py-0 rounded-pill" style="font-size: 10px;">
                                                    {{ ucfirst($device->user_type) }}
                                                </span>
                                                @if($device->user?->role)
                                                    <span class="badge bg-light text-muted border px-2 py-0 rounded-pill" style="font-size: 10px;">
                                                        {{ $device->user->role->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Device Hardware & OS Specs --}}
                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="position-relative mt-1">
                                            <i class="fa-solid fa-mobile-screen text-muted fs-5"></i>
                                            <span class="conn-pulse-dot {{ $connStatus }} position-absolute bottom-0 end-0" style="width: 7px; height: 7px;"></span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $device->device_model ?: 'Android Device' }}</div>
                                            <div class="device-user-meta mb-1">
                                                <span class="badge bg-light text-dark border py-0 px-2">Android {{ $device->android_version ?: 'OS' }}</span>
                                                <span class="badge bg-light text-muted border py-0 px-2">SDK {{ $device->android_sdk ?: '?' }}</span>
                                            </div>

                                            {{-- Click-to-copy UUIDs with tooltip --}}
                                            <div class="d-flex flex-wrap align-items-center gap-1">
                                                <div class="copy-badge" onclick="copyToClipboard('{{ $device->uuid }}', this)" title="Click to copy full hardware UUID: {{ $device->uuid }}">
                                                    <i class="fa-solid fa-fingerprint text-primary"></i>
                                                    <span>{{ substr($device->uuid, 0, 8) }}…</span>
                                                    <i class="fa-regular fa-copy"></i>
                                                </div>
                                                @unless($supportOnly)
                                                    <div class="copy-badge" onclick="copyToClipboard('{{ $device->installation_uuid }}', this)" title="Click to copy installation UUID: {{ $device->installation_uuid }}">
                                                        <i class="fa-solid fa-cube text-info"></i>
                                                        <span>{{ substr($device->installation_uuid, 0, 8) }}…</span>
                                                        <i class="fa-regular fa-copy"></i>
                                                    </div>
                                                @endunless
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 3. Live Connection & Session Activity --}}
                                <td>
                                    <div class="conn-indicator mb-1">
                                        <span class="conn-pulse-dot {{ $connStatus }}"></span>
                                        <span class="{{ $connStatus === 'online' ? 'text-success fw-bold' : ($connStatus === 'today' ? 'text-cyan fw-bold' : 'text-muted') }}">
                                            {{ $connLabel }}
                                        </span>
                                    </div>
                                    <div class="small fw-semibold text-dark">
                                        @if($device->last_seen_at)
                                            <span title="{{ $device->last_seen_at->format('Y-m-d H:i:s') }}">{{ $device->last_seen_at->diffForHumans() }}</span>
                                        @else
                                            <span class="text-muted fst-italic">Never</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        @if(($device->active_sessions_count ?? 0) > 0)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill" style="font-size: 10px;">
                                                <i class="fa-solid fa-network-wired me-1"></i>{{ $device->active_sessions_count }} Active
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border rounded-pill" style="font-size: 10px;">
                                                0 Active Sessions
                                            </span>
                                        @endif
                                        <span class="text-muted small" style="font-size: 10px;" title="Total lifetime sessions">
                                            ({{ $device->sessions_count ?? 0 }} total)
                                        </span>
                                    </div>
                                </td>

                                @unless($supportOnly)
                                    {{-- 4. App Release & Package Cryptography --}}
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $device->version_name }}
                                            <span class="badge bg-light text-dark border ms-1">v{{ $device->version_code }}</span>
                                        </div>
                                        <div class="device-user-meta font-monospace small text-truncate" style="max-width: 170px;" title="{{ $device->package_name }}">
                                            {{ $device->package_name }}
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                            <div class="copy-badge" onclick="copyToClipboard('{{ $device->signing_sha256 }}', this)" title="Click to copy Signing SHA-256: {{ $device->signing_sha256 }}">
                                                <i class="fa-solid fa-key text-secondary"></i>
                                                <span>Sign: {{ substr($device->signing_sha256, 0, 8) }}…</span>
                                                <i class="fa-regular fa-copy"></i>
                                            </div>
                                            <div class="copy-badge" onclick="copyToClipboard('{{ $device->public_key_fingerprint }}', this)" title="Click to copy Public Key Fingerprint: {{ $device->public_key_fingerprint }}">
                                                <i class="fa-solid fa-shield text-purple"></i>
                                                <span>Key: {{ substr($device->public_key_fingerprint, 0, 8) }}…</span>
                                                <i class="fa-regular fa-copy"></i>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 5. Hardware Attestation & Threat Telemetry --}}
                                    <td>
                                        <div class="attestation-chip {{ $attChip['class'] }}">
                                            <i class="{{ $attChip['icon'] }}"></i>
                                            <span>{{ $attChip['label'] }}</span>
                                        </div>

                                        {{-- Risk progress meter --}}
                                        <div class="risk-meter" title="Risk Score: {{ $score }}/100">
                                            <div class="risk-meter-bar">
                                                <div class="risk-meter-fill {{ $riskLevel }}" style="width: {{ max(6, $score) }}%"></div>
                                            </div>
                                            <span class="risk-score-text {{ $riskColor }}">{{ $score }}/100</span>
                                        </div>

                                        {{-- Top 2 Risk Signal Pills --}}
                                        @foreach(array_slice(data_get($device->latestRiskScore?->reasons, 'signals', []), 0, 2) as $riskReason)
                                            <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 mt-1" style="font-size: 10px;">
                                                <i class="fa-solid fa-radiation me-1"></i>
                                                {{ \Illuminate\Support\Str::headline($riskReason['signal'] ?? 'signal') }} +{{ $riskReason['points'] ?? 0 }}
                                            </div>
                                        @endforeach
                                    </td>
                                @endunless

                                {{-- 6. Device Status & Lifecycle --}}
                                <td>
                                    <span class="device-status-badge {{ $statusClass }}">
                                        {{ str_replace('_', ' ', $displayStatus) }}
                                    </span>
                                    @if($device->approved_at)
                                        <div class="device-user-meta mt-1" title="Approved at {{ $device->approved_at->format('Y-m-d H:i') }}">
                                            <i class="fa-solid fa-circle-check text-success me-1"></i>
                                            Approved: {{ $device->approved_at->format('M d') }}
                                        </div>
                                    @elseif($device->registered_at)
                                        <div class="device-user-meta mt-1" title="Registered at {{ $device->registered_at->format('Y-m-d H:i') }}">
                                            <i class="fa-solid fa-clock me-1"></i>
                                            Reg: {{ $device->registered_at->format('M d') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- 7. Control Actions Toolbar --}}
                                <td class="text-end">
                                    <div class="security-action-group justify-content-end">
                                        {{-- Primary Inspect Telemetry Modal Button --}}
                                        <button type="button" class="btn btn-sec-action btn-outline-primary"
                                            onclick="openDeviceInspectModal(this)"
                                            data-device="{{ htmlspecialchars(json_encode($inspectPayload), ENT_QUOTES, 'UTF-8') }}"
                                            title="{{ __('app_security.inspect_device') }}">
                                            <i class="fa-solid fa-circle-info"></i>
                                            <span class="d-none d-xl-inline">{{ __('app_security.inspect_device') }}</span>
                                        </button>

                                        {{-- Secondary Links: Review & Sessions --}}
                                        @if(!$supportOnly && ($canViewSecurityAlerts || $canReviewRiskSignals))
                                            <a class="btn btn-sec-action btn-outline-secondary" href="{{ route('admin.app-security.devices.review', $device->uuid) }}" title="{{ __('app_security.risk_review') }}">
                                                <i class="fa-solid fa-magnifying-glass-chart"></i>
                                            </a>
                                        @endif

                                        @if(!$supportOnly && ($canManageDevices || ($canApproveStaffDevices && $device->user_type === 'staff')))
                                            <a class="btn btn-sec-action btn-outline-secondary" href="{{ route('admin.app-security.devices.sessions', $device->uuid) }}" title="{{ __('app_security.sessions') }}">
                                                <i class="fa-solid fa-network-wired"></i>
                                            </a>
                                        @endif

                                        {{-- Pending State: Approve & Reject --}}
                                        @if(($canManageDevices || ($canApproveStaffDevices && $device->user_type === 'staff')) && $device->device_status === 'pending')
                                            <button type="button" class="btn btn-sec-action btn-success shadow-sm"
                                                onclick="openStepUpModal('approve', '{{ route('admin.app-security.devices.approve', $device->uuid) }}', 'Approve Device Authorization', 'Approving this device verifies its hardware identity and allows full encrypted API challenge-response sessions.', '{{ $device->device_model ?: 'Android Device' }}', '{{ $device->user?->name ?? 'User' }}', '{{ $device->uuid }}')">
                                                <i class="fa-solid fa-check"></i> {{ __('app_security.approve') }}
                                            </button>
                                            <button type="button" class="btn btn-sec-action btn-outline-danger"
                                                onclick="openStepUpModal('reject', '{{ route('admin.app-security.devices.reject', $device->uuid) }}', 'Reject Device Registration', 'Rejecting will immediately dismiss this registration challenge and terminate preliminary key pairs.', '{{ $device->device_model ?: 'Android Device' }}', '{{ $device->user?->name ?? 'User' }}', '{{ $device->uuid }}')">
                                                <i class="fa-solid fa-xmark"></i> {{ __('app_security.reject') }}
                                            </button>
                                        @endif

                                        {{-- Blocked State: Unblock --}}
                                        @if($canManageDevices && $device->blocked_at)
                                            <button type="button" class="btn btn-sec-action btn-warning"
                                                onclick="openStepUpModal('unblock', '{{ route('admin.app-security.devices.unblock', $device->uuid) }}', 'Unblock Device Access', 'Unblocking will restore API access and allow the device to resume secure heartbeat sessions.', '{{ $device->device_model ?: 'Android Device' }}', '{{ $device->user?->name ?? 'User' }}', '{{ $device->uuid }}')">
                                                <i class="fa-solid fa-lock-open"></i> {{ __('app_security.unblock') }}
                                            </button>
                                        @endif

                                        {{-- Approved & Active State: Block --}}
                                        @if($canManageDevices && $device->device_status === 'approved' && !$device->revoked_at && !$device->blocked_at)
                                            <button type="button" class="btn btn-sec-action btn-outline-danger"
                                                onclick="openStepUpModal('block', '{{ route('admin.app-security.devices.block', $device->uuid) }}', 'Block Device Access', 'Temporarily restricts all API calls from this installation and revokes active token sessions.', '{{ $device->device_model ?: 'Android Device' }}', '{{ $device->user?->name ?? 'User' }}', '{{ $device->uuid }}')">
                                                <i class="fa-solid fa-ban"></i> {{ __('app_security.block') }}
                                            </button>
                                        @endif

                                        {{-- More Actions Dropdown (Require Re-registration & Revoke) --}}
                                        @if($canManageDevices && !$device->revoked_at)
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sec-action btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                                    @if($device->device_status !== 're_registration_required')
                                                        <li>
                                                            <a class="dropdown-item text-warning" href="javascript:void(0)"
                                                                onclick="openStepUpModal('reregister', '{{ route('admin.app-security.devices.requireReregistration', $device->uuid) }}', 'Require Device Re-registration', 'Retires the active cryptographic key fingerprint on the server and forces the mobile client to re-register with a fresh key pair.', '{{ $device->device_model ?: 'Android Device' }}', '{{ $device->user?->name ?? 'User' }}', '{{ $device->uuid }}')">
                                                                <i class="fa-solid fa-key me-2"></i> {{ __('app_security.require_reregistration') }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                            onclick="openStepUpModal('revoke', '{{ route('admin.app-security.devices.revoke', $device->uuid) }}', 'Permanently Revoke Device Installation', 'PERMANENT REVOCATION: Immediately terminates all sessions, blacklists the public key, and prevents future API challenges from this device.', '{{ $device->device_model ?: 'Android Device' }}', '{{ $device->user?->name ?? 'User' }}', '{{ $device->uuid }}')">
                                                            <i class="fa-solid fa-trash me-2"></i> {{ __('app_security.revoke') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $supportOnly ? 5 : 7 }}" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fa-solid fa-mobile-screen fs-1 text-muted mb-3 d-block opacity-50"></i>
                                        <h5 class="text-muted fw-bold">{{ __('app_security.no_devices_found') }}</h5>
                                        <p class="text-muted small mb-3">No mobile hardware matches your current keyword, status, or connection filter.</p>
                                        <a href="{{ route('admin.app-security.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fa-solid fa-rotate-left me-1"></i> Clear All Filters
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($devices->hasPages())
                    <div class="panel-header border-top border-bottom-0 bg-white">
                        <div class="small text-muted">
                            Showing {{ $devices->firstItem() ?? 0 }} to {{ $devices->lastItem() ?? 0 }} of {{ $devices->total() }} devices
                        </div>
                        <div>
                            {{ $devices->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ==========================================
             TAB 2: SECURITY POLICY & RULES
             ========================================== --}}
        @if(!$supportOnly && $canManageSettings)
        <div class="tab-pane fade" id="tab-pane-policy" role="tabpanel" aria-labelledby="tab-btn-policy">
            <div class="security-panel-card" id="security-policy-settings">
                <div class="panel-header">
                    <div>
                        <h5 class="panel-title">
                            <i class="fa-solid fa-sliders text-info"></i>
                            <span>{{ __('app_security.policy_settings') }}</span>
                        </h5>
                        <p class="small text-muted mb-0 mt-1">{{ __('app_security.policy_description') }}</p>
                    </div>
                    <span class="badge {{ $securityEnabled ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 rounded-pill">
                        <i class="fa-solid fa-circle-dot me-1"></i> {{ $securityEnabled ? 'Master Switch: ACTIVE' : 'Master Switch: DISABLED' }}
                    </span>
                </div>

                <div class="panel-body">
                    <form method="post" action="{{ route('admin.app-security.settings.update') }}">
                        @csrf

                        {{-- Section 1: Master Switch & Core Access --}}
                        <div class="policy-section-card">
                            <div class="policy-section-title">
                                <i class="fa-solid fa-power-off text-primary"></i>
                                <span>Master Control & Access Flags</span>
                            </div>
                            <div class="row g-3">
                                {{-- Master Switch --}}
                                <div class="col-md-6">
                                    <div class="policy-switch-card highlight">
                                        <div>
                                            <strong class="text-dark d-block">{{ __('app_security.master_switch') }}</strong>
                                            <span class="small text-muted">When disabled, zero-trust cryptographic signature verification is bypassed for mobile traffic.</span>
                                        </div>
                                        <div class="form-check form-switch fs-5 mb-0">
                                            <input type="hidden" name="enabled" value="0">
                                            <input class="form-check-input" type="checkbox" name="enabled" value="1" @checked($settings['enabled'])>
                                        </div>
                                    </div>
                                </div>

                                {{-- Require Staff Approval --}}
                                <div class="col-md-6">
                                    <div class="policy-switch-card">
                                        <div>
                                            <strong class="text-dark d-block">{{ __('app_security.require_staff_approval') }}</strong>
                                            <span class="small text-muted">Staff devices remain in 'pending' status until authorized by a security admin.</span>
                                        </div>
                                        <div class="form-check form-switch fs-5 mb-0">
                                            <input type="hidden" name="device_approval_required" value="0">
                                            <input class="form-check-input" type="checkbox" name="device_approval_required" value="1" @checked($settings['device_approval_required'])>
                                        </div>
                                    </div>
                                </div>

                                {{-- Auto-Approve Low-Risk Customers --}}
                                <div class="col-md-6">
                                    <div class="policy-switch-card">
                                        <div>
                                            <strong class="text-dark d-block">{{ __('app_security.auto_approve_customers') }}</strong>
                                            <span class="small text-muted">Customer devices meeting hardware integrity criteria are approved immediately.</span>
                                        </div>
                                        <div class="form-check form-switch fs-5 mb-0">
                                            <input type="hidden" name="customer_auto_approval" value="0">
                                            <input class="form-check-input" type="checkbox" name="customer_auto_approval" value="1" @checked($settings['customer_auto_approval'])>
                                        </div>
                                    </div>
                                </div>

                                {{-- Enable Risk Scoring --}}
                                <div class="col-md-6">
                                    <div class="policy-switch-card">
                                        <div>
                                            <strong class="text-dark d-block">{{ __('app_security.enable_risk_scoring') }}</strong>
                                            <span class="small text-muted">Continuously assess heuristics (root, emulator, VPN, nonce replays) to calculate risk.</span>
                                        </div>
                                        <div class="form-check form-switch fs-5 mb-0">
                                            <input type="hidden" name="risk_enabled" value="0">
                                            <input class="form-check-input" type="checkbox" name="risk_enabled" value="1" @checked($settings['risk_enabled'])>
                                        </div>
                                    </div>
                                </div>

                                {{-- Hardware Attestation Policy --}}
                                <div class="col-12">
                                    <div class="bg-white p-3 rounded-3 border">
                                        <label class="form-label fw-bold mb-1">{{ __('app_security.attestation_policy') }}</label>
                                        <select class="form-select" name="hardware_attestation">
                                            <option value="preferred" @selected($settings['hardware_attestation'] === 'preferred')>
                                                {{ __('app_security.attestation_preferred') }}
                                            </option>
                                            <option value="required" @selected($settings['hardware_attestation'] === 'required')>
                                                {{ __('app_security.attestation_required') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Quotas & Lifetimes --}}
                        <div class="policy-section-card">
                            <div class="policy-section-title">
                                <i class="fa-solid fa-stopwatch text-primary"></i>
                                <span>Device Limits, Quotas & Session Timers</span>
                            </div>
                            <div class="row g-3">
                                @php
                                    $numberFields = [
                                        ['key' => 'max_devices_staff', 'label' => __('app_security.max_staff_devices'), 'min' => 0, 'max' => 50],
                                        ['key' => 'max_devices_customer', 'label' => __('app_security.max_customer_devices'), 'min' => 0, 'max' => 50],
                                        ['key' => 'max_devices_admin', 'label' => __('app_security.max_admin_devices'), 'min' => 0, 'max' => 50],
                                        ['key' => 'max_sessions_per_device', 'label' => __('app_security.max_sessions'), 'min' => 1, 'max' => 20],
                                        ['key' => 'heartbeat_seconds', 'label' => __('app_security.heartbeat_seconds'), 'min' => 30, 'max' => 900],
                                        ['key' => 'session_ttl_seconds', 'label' => __('app_security.session_ttl'), 'min' => 60, 'max' => 3600],
                                        ['key' => 'refresh_ttl_seconds', 'label' => __('app_security.refresh_ttl'), 'min' => 300, 'max' => 7776000],
                                        ['key' => 'challenge_ttl_seconds', 'label' => __('app_security.challenge_ttl'), 'min' => 30, 'max' => 600],
                                        ['key' => 'nonce_ttl_seconds', 'label' => __('app_security.nonce_ttl'), 'min' => 60, 'max' => 600],
                                        ['key' => 'offline_grace_seconds', 'label' => __('app_security.offline_grace'), 'min' => 0, 'max' => 86400],
                                    ];
                                @endphp
                                @foreach($numberFields as $nf)
                                    <div class="col-6 col-md-4 col-xl-3">
                                        <div class="bg-white p-3 rounded-3 border h-100">
                                            <label class="form-label small fw-semibold text-secondary" for="{{ $nf['key'] }}">{{ $nf['label'] }}</label>
                                            <input id="{{ $nf['key'] }}" class="form-control" type="number" min="{{ $nf['min'] }}" max="{{ $nf['max'] }}" name="{{ $nf['key'] }}" value="{{ $settings[$nf['key']] }}" required>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Section 3: Risk Scoring & Threat Matrix --}}
                        <div class="policy-section-card">
                            <div class="policy-section-title">
                                <i class="fa-solid fa-shield-virus text-danger"></i>
                                <span>Risk Scoring Engine & Threat Matrix Penalty Weights</span>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="bg-white p-3 rounded-3 border">
                                        <label class="form-label small fw-semibold text-secondary">{{ __('app_security.risk_window') }}</label>
                                        <input class="form-control" type="number" min="1" max="720" name="risk_window_hours" value="{{ $settings['risk_window_hours'] }}" required>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-white p-3 rounded-3 border">
                                        <label class="form-label small fw-semibold text-secondary">{{ __('app_security.risk_review_threshold') }}</label>
                                        <input class="form-control" type="number" min="0" max="99" name="risk_review_threshold" value="{{ $settings['risk_review_threshold'] }}" required>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-white p-3 rounded-3 border">
                                        <label class="form-label small fw-semibold text-secondary">{{ __('app_security.risk_block_threshold') }}</label>
                                        <input class="form-control" type="number" min="1" max="100" name="risk_block_threshold" value="{{ $settings['risk_block_threshold'] }}" required>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-white p-3 rounded-3 border">
                                        <label class="form-label small fw-semibold text-secondary">{{ __('app_security.risk_policy_version') }}</label>
                                        <input class="form-control" name="risk_policy_version" maxlength="40" value="{{ $settings['risk_policy_version'] }}" required>
                                    </div>
                                </div>
                            </div>

                            <label class="form-label small fw-bold text-uppercase text-muted mb-2">Threat Signal Penalty Allocations (0 - 100 points)</label>
                            <div class="row g-2">
                                @php
                                    $threatPoints = [
                                        'risk_points_root_detected' => 'Root Detected',
                                        'risk_points_emulator_detected' => 'Emulator Detected',
                                        'risk_points_instrumentation_detected' => 'Instrumentation Hook',
                                        'risk_points_debug_build' => 'Debug Build APK',
                                        'risk_points_debugger_attached' => 'Debugger Attached',
                                        'risk_points_hooking_detected' => 'Runtime Hooking (Frida/Xposed)',
                                        'risk_points_vpn_detected' => 'VPN / Proxy Network',
                                        'risk_points_attestation_missing' => 'Missing Keystore Attestation',
                                        'risk_points_attestation_verification_failed' => 'Failed Keystore Attestation',
                                        'risk_points_attestation_os_policy_failed' => 'Outdated OS / Patch Level',
                                        'risk_points_request_nonce_replayed' => 'Replayed Request Nonce',
                                        'risk_points_security_session_invalid' => 'Invalid Security Session',
                                        'risk_points_request_timestamp_expired' => 'Expired Timestamp Clock Drift',
                                        'risk_points_device_not_authorized' => 'Unauthorized Device Access',
                                        'risk_points_registration_velocity' => 'High Registration Velocity',
                                        'risk_points_cross_account_key_reuse' => 'Cross-Account Key Reuse',
                                    ];
                                @endphp
                                @foreach($threatPoints as $tpKey => $tpLabel)
                                    <div class="col-6 col-md-4 col-xl-3">
                                        <div class="bg-white p-2 rounded-2 border d-flex align-items-center justify-content-between gap-2">
                                            <span class="small fw-semibold text-truncate" title="{{ $tpLabel }}">{{ $tpLabel }}</span>
                                            <input class="form-control form-control-sm text-end" style="width: 65px;" type="number" min="0" max="100" name="{{ $tpKey }}" value="{{ $settings[$tpKey] }}" required>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Step-up Authentication Box --}}
                        <div class="bg-light p-4 rounded-4 border mt-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark mb-1" for="settings-step-up-password">
                                        <i class="fa-solid fa-lock text-primary me-1"></i> {{ __('app_security.admin_password') }}
                                    </label>
                                    <div class="input-group">
                                        <input id="settings-step-up-password" type="password" name="step_up_password" class="form-control" autocomplete="current-password" placeholder="{{ __('app_security.admin_password_placeholder') }}" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('settings-step-up-password', this)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text small">High-privilege security policy updates require fresh step-up password verification.</div>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <button class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm" type="submit">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('app_security.save_policy') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- ==========================================
             TAB 3: APK RELEASES & INTEGRITY
             ========================================== --}}
        @unless($supportOnly)
        <div class="tab-pane fade" id="tab-pane-releases" role="tabpanel" aria-labelledby="tab-btn-releases">
            <div class="row g-4">
                {{-- Left: Upload Draft APK --}}
                @if($canManageReleases)
                <div class="col-xl-5">
                    <div class="security-panel-card h-100">
                        <div class="panel-header">
                            <h5 class="panel-title">
                                <i class="fa-solid fa-cloud-arrow-up text-primary"></i>
                                <span>{{ __('app_security.register_release') }}</span>
                            </h5>
                        </div>
                        <div class="panel-body">
                            <p class="small text-muted mb-3">
                                APK package name, version codes, signing certificate and SHA-256 digest are extracted via aapt / apksigner.
                            </p>
                            <form method="post" enctype="multipart/form-data" action="{{ route('admin.app-security.releases.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('app_security.upload_apk') }}</label>
                                    <input type="file" name="apk" class="form-control" accept=".apk" required>
                                    <div class="form-text small">Maximum file size: 500MB (.apk only).</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('app_security.min_version_code') }}</label>
                                    <input name="minimum_supported_version" type="number" min="0" value="0" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="is_mandatory" value="1" id="is_mandatory_check">
                                        <label class="form-check-label fw-semibold" for="is_mandatory_check">{{ __('app_security.mandatory_update') }}</label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">{{ __('app_security.release_notes') }}</label>
                                    <textarea name="release_notes" class="form-control" rows="3" maxlength="10000" placeholder="Changelog, security enhancements, and bug fixes..."></textarea>
                                </div>

                                <button class="btn btn-primary w-100 rounded-pill py-2 shadow-sm" type="submit">
                                    <i class="fa-solid fa-check-double me-1"></i> {{ __('app_security.verify_and_save') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Right: Release History --}}
                <div class="{{ $canManageReleases ? 'col-xl-7' : 'col-12' }}">
                    <div class="security-panel-card h-100">
                        <div class="panel-header">
                            <h5 class="panel-title">
                                <i class="fa-solid fa-list-check text-success"></i>
                                <span>{{ __('app_security.release_history') }}</span>
                            </h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table security-table align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('app_security.version') }}</th>
                                        <th>{{ __('app_security.sha256') }}</th>
                                        <th>{{ __('app_security.status') }}</th>
                                        <th class="text-end">{{ __('app_security.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($releases as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->version_name }}</div>
                                            <div class="small text-muted">Version Code: <strong>{{ $item->version_code }}</strong> · {{ $item->created_at?->diffForHumans() }}</div>
                                        </td>
                                        <td>
                                            <span class="font-monospace small text-muted" title="{{ $item->apk_sha256 }}">{{ substr($item->apk_sha256, 0, 16) }}…</span>
                                        </td>
                                        <td>
                                            @php
                                                $relStatusBadge = match($item->release_status) {
                                                    'active' => 'bg-success',
                                                    'draft' => 'bg-info text-dark',
                                                    'testing' => 'bg-warning text-dark',
                                                    'revoked' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $relStatusBadge }} px-2 py-1 rounded-pill">
                                                {{ ucfirst($item->release_status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-1">
                                                @if($canManageReleases && in_array($item->release_status, ['draft', 'testing', 'suspended']))
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3"
                                                        onclick="openStepUpModal('activate', '{{ route('admin.app-security.releases.activate', $item->uuid) }}', 'Activate APK Release', 'Activating version {{ $item->version_name }} will make it the active production build for all mobile devices.', 'KNEAYERNG Android APK', 'Version {{ $item->version_name }} ({{ $item->version_code }})', '{{ $item->uuid }}')">
                                                        <i class="fa-solid fa-play me-1"></i> {{ __('app_security.activate') }}
                                                    </button>
                                                @endif

                                                @if($canManageReleases && $item->release_status !== 'revoked')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                        onclick="openStepUpModal('revoke', '{{ route('admin.app-security.releases.revoke', $item->uuid) }}', 'Revoke APK Release', 'Revoking will immediately prevent mobile clients with this build from accessing API endpoints.', 'KNEAYERNG Android APK', 'Version {{ $item->version_name }} ({{ $item->version_code }})', '{{ $item->uuid }}')">
                                                        <i class="fa-solid fa-ban me-1"></i> {{ __('app_security.revoke') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            {{ __('app_security.no_releases') }}
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endunless

        {{-- ==========================================
             TAB 4: SECURITY THREAT ALERTS
             ========================================== --}}
        @if($canViewSecurityAlerts)
        <div class="tab-pane fade" id="tab-pane-alerts" role="tabpanel" aria-labelledby="tab-btn-alerts">
            <div class="security-panel-card">
                <div class="panel-header">
                    <h5 class="panel-title">
                        <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                        <span>{{ __('app_security.security_alerts') }}</span>
                    </h5>
                    <span class="badge bg-danger rounded-pill px-3 py-2">
                        {{ $securityAlerts->whereNull('acknowledged_at')->count() }} {{ __('app_security.open_alerts') }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table security-table align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('app_security.alert') }}</th>
                                <th>{{ __('app_security.severity') }}</th>
                                <th>{{ __('app_security.created') }}</th>
                                <th>{{ __('app_security.status') }} / {{ __('app_security.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($securityAlerts as $alert)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $alert->title }}</div>
                                    <div class="small text-muted">{{ $alert->message }}</div>
                                </td>
                                <td>
                                    @php
                                        $sevBadge = match(strtolower($alert->severity)) {
                                            'critical' => 'bg-danger text-white',
                                            'high' => 'bg-warning text-dark',
                                            'medium' => 'bg-info text-dark',
                                            default => 'bg-light text-dark border'
                                        };
                                    @endphp
                                    <span class="badge {{ $sevBadge }} px-2 py-1 rounded-pill">
                                        {{ ucfirst($alert->severity) }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{ \Illuminate\Support\Carbon::parse($alert->created_at)->diffForHumans() }}
                                </td>
                                <td>
                                    @if($alert->acknowledged_at)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill">
                                            <i class="fa-solid fa-check me-1"></i> {{ __('app_security.acknowledged') }}
                                        </span>
                                        <div class="small text-muted mt-1">{{ $alert->acknowledgement_reason }}</div>
                                    @elseif($canAcknowledgeSecurityAlerts)
                                        <form method="post" action="{{ route('admin.app-security.alerts.acknowledge', $alert->id) }}" class="d-flex gap-2" style="max-width: 380px;">
                                            @csrf
                                            <input name="reason" class="form-control form-control-sm" maxlength="512" placeholder="{{ __('app_security.reason_placeholder') }}" required>
                                            <button class="btn btn-sm btn-primary rounded-pill px-3 text-nowrap" type="submit">
                                                <i class="fa-solid fa-check"></i> {{ __('app_security.acknowledge') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-danger rounded-pill">Open</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    {{ __('app_security.no_alerts') }}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ==========================================
             TAB 5: ADMIN SECURITY ROLES (RBAC)
             ========================================== --}}
        @if(!$supportOnly && $canAssignAdminRoles)
        <div class="tab-pane fade" id="tab-pane-roles" role="tabpanel" aria-labelledby="tab-btn-roles">
            <div class="security-panel-card">
                <div class="panel-header">
                    <div>
                        <h5 class="panel-title">
                            <i class="fa-solid fa-user-shield text-purple" style="color: #8b5cf6;"></i>
                            <span>{{ __('app_security.admin_roles') }}</span>
                        </h5>
                        <p class="small text-muted mb-0 mt-1">{{ __('app_security.admin_roles_desc') }}</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table security-table align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('app_security.administrator') }}</th>
                                <th>{{ __('app_security.current_role') }}</th>
                                <th>{{ __('app_security.assign_role') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($adminUsers as $admin)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $admin->name }}</div>
                                    <div class="small text-muted">{{ $admin->email ?: $admin->username }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                        {{ $adminRoleOptions[$admin->security_role] ?? 'Unassigned' }}
                                    </span>
                                    @if($admin->assignment_reason)
                                        <div class="small text-muted mt-1 fst-italic">"{{ $admin->assignment_reason }}"</div>
                                    @endif
                                </td>
                                <td>
                                    <form method="post" action="{{ route('admin.app-security.admins.role.update', $admin->id) }}" class="d-flex flex-wrap gap-2 align-items-center">
                                        @csrf
                                        <select name="security_role" class="form-select form-select-sm" style="max-width: 220px;" required>
                                            @foreach($adminRoleOptions as $roleKey => $roleLabel)
                                                <option value="{{ $roleKey }}" @selected(($admin->security_role ?? '') === $roleKey)>{{ $roleLabel }}</option>
                                            @endforeach
                                        </select>
                                        <input name="reason" class="form-control form-control-sm" style="max-width: 240px;" maxlength="500" placeholder="{{ __('app_security.reason_placeholder') }}" required>
                                        <input name="step_up_password" type="password" class="form-control form-control-sm" style="max-width: 170px;" autocomplete="current-password" placeholder="{{ __('app_security.admin_password') }}" required>
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" type="submit">
                                            {{ __('app_security.save_role') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-muted text-center py-4">No administrators found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ==========================================
             TAB 6: SECURITY ANALYTICS & TELEMETRY
             ========================================== --}}
        @if($canViewAnalytics && $analytics)
        <div class="tab-pane fade" id="tab-pane-analytics" role="tabpanel" aria-labelledby="tab-btn-analytics">
            @include('admin.app-security.partials.analytics')
        </div>
        @endif

    </div>

</div>

{{-- ==========================================
     STEP-UP SECURITY CONFIRMATION MODAL
     ========================================== --}}
<div class="modal fade modal-security-stepup" id="securityStepUpModal" tabindex="-1" aria-labelledby="stepUpModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="stepUpModalForm" method="post" action="">
                @csrf
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="action-icon-pill" id="stepUpActionIcon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="stepUpModalTitle">{{ __('app_security.action_confirm') }}</h5>
                            <div class="small text-muted" id="stepUpActionSubtitle">High-Privilege Security Operation</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- Target Information Box --}}
                    <div class="target-details-box">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-semibold text-uppercase">Target Object</span>
                            <span class="badge bg-light text-dark border font-monospace small" id="stepUpTargetUuid">UUID</span>
                        </div>
                        <div class="fw-bold text-dark fs-6" id="stepUpTargetModel">Samsung Galaxy S23</div>
                        <div class="text-muted small" id="stepUpTargetUser">User: Chhoeun Reach</div>
                    </div>

                    {{-- Security Warning Description --}}
                    <div class="alert alert-warning border-0 d-flex align-items-start gap-2 py-2 mb-3 rounded-3" role="alert">
                        <i class="fa-solid fa-triangle-exclamation mt-1 text-warning"></i>
                        <div class="small" id="stepUpActionDescription">Description of this action impact.</div>
                    </div>

                    {{-- Reason / Audit Note --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">
                            {{ __('app_security.reason') }} <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" id="stepUpReasonInput" class="form-control" rows="2" maxlength="512" placeholder="{{ __('app_security.reason_placeholder') }}" required></textarea>

                        {{-- Quick Preset Chips --}}
                        <div class="preset-chips-wrap">
                            <span class="preset-chip" onclick="applyPreset('{{ __('app_security.preset_authorized') }}')">{{ __('app_security.preset_authorized') }}</span>
                            <span class="preset-chip" onclick="applyPreset('{{ __('app_security.preset_routine') }}')">{{ __('app_security.preset_routine') }}</span>
                            <span class="preset-chip" onclick="applyPreset('{{ __('app_security.preset_offboarding') }}')">{{ __('app_security.preset_offboarding') }}</span>
                            <span class="preset-chip" onclick="applyPreset('{{ __('app_security.preset_suspicious') }}')">{{ __('app_security.preset_suspicious') }}</span>
                            <span class="preset-chip" onclick="applyPreset('{{ __('app_security.preset_lost') }}')">{{ __('app_security.preset_lost') }}</span>
                        </div>
                    </div>

                    {{-- Admin Step-Up Password --}}
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark mb-1" for="stepUpPasswordInput">
                            <i class="fa-solid fa-lock text-primary me-1"></i> {{ __('app_security.admin_password') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="step_up_password" id="stepUpPasswordInput" class="form-control" autocomplete="current-password" placeholder="{{ __('app_security.admin_password_placeholder') }}" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('stepUpPasswordInput', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text small">Your administrator password verifies identity for permanent audit logging.</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">{{ __('app_security.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="stepUpSubmitBtn">
                        <i class="fa-solid fa-shield-check me-1"></i> {{ __('app_security.confirm_and_audit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==========================================
     AUDIT LOGS CSV EXPORT MODAL
     ========================================== --}}
@if($canExportAuditLogs)
<div class="modal fade" id="exportAuditModal" tabindex="-1" aria-labelledby="exportAuditModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="get" action="{{ route('admin.app-security.logs.export') }}">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="exportAuditModalTitle">
                        <i class="fa-solid fa-file-csv text-primary me-2"></i> {{ __('app_security.export_audit_csv') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="small text-muted mb-3">Download a cryptographic and operational audit record in CSV format for security compliance reviews.</p>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold" for="modal-audit-from">{{ __('app_security.export_from') }}</label>
                            <input id="modal-audit-from" class="form-control" type="date" name="from" value="{{ now()->subDays(90)->toDateString() }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold" for="modal-audit-to">{{ __('app_security.export_to') }}</label>
                            <input id="modal-audit-to" class="form-control" type="date" name="to" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light border flex-fill rounded-pill" onclick="setAuditDatePreset(7)">Last 7 Days</button>
                        <button type="button" class="btn btn-sm btn-light border flex-fill rounded-pill" onclick="setAuditDatePreset(30)">Last 30 Days</button>
                        <button type="button" class="btn btn-sm btn-light border flex-fill rounded-pill" onclick="setAuditDatePreset(90)">Last 90 Days</button>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">{{ __('app_security.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fa-solid fa-download me-1"></i> {{ __('app_security.download_csv') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ==============================================================
     MODAL: INSPECT FULL DEVICE TELEMETRY & CRYPTOGRAPHY
     ============================================================== --}}
<div class="modal fade modal-device-inspect" id="deviceInspectModal" tabindex="-1" aria-labelledby="deviceInspectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-wrap" style="width: 42px; height: 42px; border-radius: 12px; background: rgba(255, 255, 255, 0.15); color: #93c5fd; font-size: 18px;">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white mb-0" id="deviceInspectModalLabel">
                            <span id="inspectModalDeviceModel">Android Device</span>
                        </h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span id="inspectModalStatusBadge" class="device-status-badge status-approved py-0 px-2" style="font-size: 11px;">Approved</span>
                            <span id="inspectModalUserBadge" class="badge bg-white bg-opacity-20 text-white rounded-pill" style="font-size: 11px;">User</span>
                            <span id="inspectModalConnBadge" class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 rounded-pill" style="font-size: 11px;">
                                <span class="conn-pulse-dot online me-1"></span> <span id="inspectModalConnText">Online</span>
                            </span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                {{-- Inspector Tabs --}}
                <ul class="nav nav-tabs" id="deviceInspectTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="inspect-tab-specs-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-specs" type="button" role="tab">
                            <i class="fa-solid fa-mobile-screen me-1"></i> {{ __('app_security.tab_specs') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inspect-tab-crypto-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-crypto" type="button" role="tab">
                            <i class="fa-solid fa-key me-1"></i> {{ __('app_security.tab_crypto') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inspect-tab-sessions-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-sessions" type="button" role="tab">
                            <i class="fa-solid fa-network-wired me-1"></i> {{ __('app_security.tab_sessions') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inspect-tab-risk-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-risk" type="button" role="tab">
                            <i class="fa-solid fa-shield-halved me-1"></i> {{ __('app_security.tab_risk') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inspect-tab-raw-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-raw" type="button" role="tab">
                            <i class="fa-solid fa-code me-1"></i> {{ __('app_security.tab_raw_json') }}
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="deviceInspectTabsContent">
                    {{-- TAB 1: HARDWARE & OS SPECS --}}
                    <div class="tab-pane fade show active inspect-tab-pane" id="inspect-tab-specs" role="tabpanel">
                        <div class="inspect-info-grid">
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-mobile-screen"></i> {{ __('app_security.device_info') }}</div>
                                <div class="inspect-info-val" id="inspectSpecModel">—</div>
                            </div>
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-brands fa-android"></i> {{ __('app_security.android_os_level') }}</div>
                                <div class="inspect-info-val" id="inspectSpecAndroid">—</div>
                            </div>
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-box"></i> {{ __('app_security.package_name') }}</div>
                                <div class="inspect-info-val font-monospace small" id="inspectSpecPackage">—</div>
                            </div>
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-code-branch"></i> {{ __('app_security.version_info') }}</div>
                                <div class="inspect-info-val" id="inspectSpecVersion">—</div>
                            </div>
                            <div class="inspect-info-card" style="grid-column: 1 / -1;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="inspect-info-label mb-0"><i class="fa-solid fa-fingerprint"></i> {{ __('app_security.device_uuid') }}</div>
                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="copyModalField('inspectSpecUuid', this)">
                                        <i class="fa-regular fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                                <div class="inspect-info-val font-monospace text-primary small" id="inspectSpecUuid">—</div>
                            </div>
                            <div class="inspect-info-card" style="grid-column: 1 / -1;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="inspect-info-label mb-0"><i class="fa-solid fa-cube"></i> {{ __('app_security.installation_uuid') }}</div>
                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="copyModalField('inspectSpecInstallUuid', this)">
                                        <i class="fa-regular fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                                <div class="inspect-info-val font-monospace text-info small" id="inspectSpecInstallUuid">—</div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: CRYPTOGRAPHIC KEYS & ATTESTATION --}}
                    <div class="tab-pane fade inspect-tab-pane" id="inspect-tab-crypto" role="tabpanel">
                        <div class="inspect-info-grid">
                            <div class="inspect-info-card" style="grid-column: 1 / -1;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="inspect-info-label mb-0"><i class="fa-solid fa-shield-halved"></i> {{ __('app_security.trust_attestation') }}</div>
                                    <span id="inspectCryptoAttChip" class="attestation-chip chip-strongbox">StrongBox</span>
                                </div>
                                <div class="small text-muted mt-1" id="inspectCryptoAttDesc">
                                    Cryptographic hardware isolated security module verified via Google Play Attestation API.
                                </div>
                            </div>

                            <div class="inspect-info-card" style="grid-column: 1 / -1;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="inspect-info-label mb-0"><i class="fa-solid fa-key"></i> {{ __('app_security.public_key_fingerprint') }}</div>
                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="copyModalField('inspectCryptoKeyFingerprint', this)">
                                        <i class="fa-regular fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                                <div class="inspect-info-val font-monospace text-purple small" id="inspectCryptoKeyFingerprint">—</div>
                            </div>

                            <div class="inspect-info-card" style="grid-column: 1 / -1;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="inspect-info-label mb-0"><i class="fa-solid fa-certificate"></i> {{ __('app_security.signing_sha256') }}</div>
                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="copyModalField('inspectCryptoSigningSha', this)">
                                        <i class="fa-regular fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                                <div class="inspect-info-val font-monospace text-secondary small" id="inspectCryptoSigningSha">—</div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: API SESSIONS & ACTIVITY --}}
                    <div class="tab-pane fade inspect-tab-pane" id="inspect-tab-sessions" role="tabpanel">
                        <div class="inspect-info-grid">
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-wifi"></i> {{ __('app_security.connection_status') }}</div>
                                <div class="inspect-info-val d-flex align-items-center gap-2" id="inspectSessionConnVal">
                                    <span class="conn-pulse-dot online"></span>
                                    <span>Active</span>
                                </div>
                            </div>
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-clock-rotate-left"></i> {{ __('app_security.last_seen') }}</div>
                                <div class="inspect-info-val" id="inspectSessionLastSeen">—</div>
                            </div>
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-calendar-plus"></i> {{ __('app_security.registered_at') }}</div>
                                <div class="inspect-info-val" id="inspectSessionRegistered">—</div>
                            </div>
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-user-check"></i> {{ __('app_security.approved_at') }}</div>
                                <div class="inspect-info-val" id="inspectSessionApproved">—</div>
                            </div>
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-network-wired"></i> {{ __('app_security.active_sessions') }}</div>
                                <div class="inspect-info-val" id="inspectSessionActiveCount">0 Active</div>
                            </div>
                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-database"></i> {{ __('app_security.total_sessions') }}</div>
                                <div class="inspect-info-val" id="inspectSessionTotalCount">0 Total</div>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <a href="#" id="inspectSessionViewLink" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ __('app_security.view_sessions') }}
                            </a>
                        </div>
                    </div>

                    {{-- TAB 4: RISK & THREAT ANALYSIS --}}
                    <div class="tab-pane fade inspect-tab-pane" id="inspect-tab-risk" role="tabpanel">
                        <div class="inspect-info-card mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="inspect-info-label mb-1"><i class="fa-solid fa-gauge-high"></i> Risk Telemetry Score</div>
                                    <h4 class="mb-0 fw-bold" id="inspectRiskScoreText">0/100</h4>
                                </div>
                                <div id="inspectRiskBadge" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fs-6">
                                    Low Risk
                                </div>
                            </div>
                            <div class="risk-meter-bar w-100 my-2" style="height: 10px;">
                                <div id="inspectRiskMeterFill" class="risk-meter-fill risk-low" style="width: 10%"></div>
                            </div>
                            <div class="small text-muted" id="inspectRiskThresholdText">
                                Automated real-time zero-trust device posture evaluation.
                            </div>
                        </div>

                        <div class="inspect-info-card">
                            <div class="inspect-info-label mb-2"><i class="fa-solid fa-radiation"></i> Detected Integrity Signals & Penalties</div>
                            <div id="inspectRiskSignalsList">
                                <div class="text-muted small fst-italic">No active threat signals or penalties recorded for this hardware profile.</div>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <a href="#" id="inspectRiskReviewLink" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                <i class="fa-solid fa-magnifying-glass-chart me-1"></i> {{ __('app_security.view_risk_review') }}
                            </a>
                        </div>
                    </div>

                    {{-- TAB 5: RAW TELEMETRY JSON --}}
                    <div class="tab-pane fade inspect-tab-pane" id="inspect-tab-raw" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small text-muted fw-bold text-uppercase">
                                <i class="fa-solid fa-terminal me-1 text-primary"></i> Complete Hardware & API Payload
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btnCopyInspectJson" onclick="copyJsonTelemetry(this)">
                                <i class="fa-regular fa-copy me-1"></i> {{ __('app_security.copy_json') }}
                            </button>
                        </div>
                        <pre class="inspect-code-block mb-0" id="inspectRawJsonBlock"><code>{}</code></pre>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top bg-white">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close Inspector</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Tab switching based on URL hash
    document.addEventListener("DOMContentLoaded", function() {
        const hash = window.location.hash;
        if (hash) {
            if (hash === '#security-policy-settings') {
                const policyTab = document.getElementById('tab-btn-policy');
                if (policyTab) bootstrap.Tab.getOrCreateInstance(policyTab).show();
            } else {
                const targetTab = document.querySelector(`[data-bs-target="${hash}"]`);
                if (targetTab) bootstrap.Tab.getOrCreateInstance(targetTab).show();
            }
        }
    });

    // Copy to clipboard helper
    function copyToClipboard(text, element) {
        if (!text) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(showFeedback).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }

        function fallbackCopy() {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                showFeedback();
            } catch (err) {
                console.error('Fallback copy failed', err);
            }
            document.body.removeChild(textarea);
        }

        function showFeedback() {
            if (!element) return;
            const originalHtml = element.innerHTML;
            element.innerHTML = '<i class="fa-solid fa-check text-success"></i> <span class="text-success fw-bold">Copied!</span>';
            setTimeout(() => {
                element.innerHTML = originalHtml;
            }, 1500);
        }
    }

    // Copy field from inside inspect modal
    function copyModalField(elemId, btn) {
        const elem = document.getElementById(elemId);
        if (elem) {
            copyToClipboard(elem.textContent.trim(), btn);
        }
    }

    // Copy raw JSON telemetry
    let activeInspectDeviceJson = '';
    function copyJsonTelemetry(btn) {
        if (!activeInspectDeviceJson) return;
        copyToClipboard(activeInspectDeviceJson, btn);
    }

    // Open Device Inspect Modal
    function openDeviceInspectModal(button) {
        const rawData = button.getAttribute('data-device');
        if (!rawData) return;
        let d = {};
        try {
            d = JSON.parse(rawData);
        } catch (e) {
            console.error('Error parsing device JSON', e);
            return;
        }

        activeInspectDeviceJson = JSON.stringify(d, null, 2);

        // Header
        document.getElementById('inspectModalDeviceModel').textContent = d.device_model || 'Android Device';
        document.getElementById('inspectModalUserBadge').textContent = d.user_name || 'User';

        const statusBadge = document.getElementById('inspectModalStatusBadge');
        statusBadge.className = 'device-status-badge status-' + (d.device_status || 'pending') + ' py-0 px-2';
        statusBadge.textContent = (d.device_status || 'pending').replace('_', ' ');

        const connBadge = document.getElementById('inspectModalConnBadge');
        const connText = document.getElementById('inspectModalConnText');
        connBadge.innerHTML = `<span class="conn-pulse-dot ${d.conn_status || 'offline'} me-1"></span> <span id="inspectModalConnText">${d.conn_label || 'Offline'}</span>`;

        // Tab 1: Specs
        document.getElementById('inspectSpecModel').textContent = d.device_model || 'Android Device';
        document.getElementById('inspectSpecAndroid').textContent = `Android ${d.android_version || 'OS'} (API SDK ${d.android_sdk || '?'})`;
        document.getElementById('inspectSpecPackage').textContent = d.package_name || '—';
        document.getElementById('inspectSpecVersion').textContent = `${d.version_name || '1.0.0'} (Build ${d.version_code || 1})`;
        document.getElementById('inspectSpecUuid').textContent = d.uuid || '—';
        document.getElementById('inspectSpecInstallUuid').textContent = d.installation_uuid || '—';

        // Tab 2: Crypto
        const attLevel = (d.attestation_level || 'none').toLowerCase();
        const attChip = document.getElementById('inspectCryptoAttChip');
        const attDesc = document.getElementById('inspectCryptoAttDesc');
        if (attLevel === 'strongbox') {
            attChip.className = 'attestation-chip chip-strongbox';
            attChip.innerHTML = '<i class="fa-solid fa-shield-halved me-1"></i> StrongBox KeyStore';
            attDesc.textContent = 'Hardware isolated tamper-resistant security chip (dedicated secure processor).';
        } else if (attLevel === 'tee') {
            attChip.className = 'attestation-chip chip-tee';
            attChip.innerHTML = '<i class="fa-solid fa-microchip me-1"></i> TEE Hardware';
            attDesc.textContent = 'Trusted Execution Environment (ARM TrustZone) isolated keystore.';
        } else if (attLevel === 'basic') {
            attChip.className = 'attestation-chip chip-basic';
            attChip.innerHTML = '<i class="fa-solid fa-certificate me-1"></i> Basic Keystore';
            attDesc.textContent = 'Standard Android software keystore.';
        } else {
            attChip.className = 'attestation-chip chip-failed';
            attChip.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Unverified';
            attDesc.textContent = 'Hardware key attestation not completed or failed verification challenge.';
        }
        document.getElementById('inspectCryptoKeyFingerprint').textContent = d.public_key_fingerprint || '—';
        document.getElementById('inspectCryptoSigningSha').textContent = d.signing_sha256 || '—';

        // Tab 3: Sessions
        const connVal = document.getElementById('inspectSessionConnVal');
        connVal.innerHTML = `<span class="conn-pulse-dot ${d.conn_status || 'offline'}"></span> <span>${d.conn_label || 'Offline'}</span>`;
        document.getElementById('inspectSessionLastSeen').textContent = d.last_seen_at ? `${d.last_seen_at} (${d.last_seen_diff})` : 'Never';
        document.getElementById('inspectSessionRegistered').textContent = d.registered_at || '—';
        document.getElementById('inspectSessionApproved').textContent = d.approved_at ? `${d.approved_at} (by ${d.approved_by || 'Admin'})` : 'Not Approved';
        document.getElementById('inspectSessionActiveCount').textContent = `${d.active_sessions_count || 0} Active Session(s)`;
        document.getElementById('inspectSessionTotalCount').textContent = `${d.sessions_count || 0} Total Lifetime Session(s)`;
        document.getElementById('inspectSessionViewLink').href = d.sessions_url || '#';

        // Tab 4: Risk
        const score = parseInt(d.risk_score || 0);
        document.getElementById('inspectRiskScoreText').textContent = score + '/100';
        const riskFill = document.getElementById('inspectRiskMeterFill');
        const riskBadge = document.getElementById('inspectRiskBadge');
        if (score >= 60) {
            riskFill.className = 'risk-meter-fill risk-high';
            riskBadge.className = 'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fs-6';
            riskBadge.textContent = 'High Risk';
        } else if (score >= 25) {
            riskFill.className = 'risk-meter-fill risk-medium';
            riskBadge.className = 'badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill fs-6';
            riskBadge.textContent = 'Medium Risk';
        } else {
            riskFill.className = 'risk-meter-fill risk-low';
            riskBadge.className = 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fs-6';
            riskBadge.textContent = 'Low Risk';
        }
        riskFill.style.width = Math.max(6, score) + '%';

        const signalsList = document.getElementById('inspectRiskSignalsList');
        if (d.risk_signals && d.risk_signals.length > 0) {
            signalsList.innerHTML = d.risk_signals.map(s => `
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="fw-semibold text-danger">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> ${s.signal || 'Signal'}
                    </div>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill">+${s.points || 0} pts</span>
                </div>
            `).join('');
        } else {
            signalsList.innerHTML = '<div class="text-muted small fst-italic py-2"><i class="fa-solid fa-circle-check text-success me-1"></i> No active threat signals or penalties recorded for this hardware profile.</div>';
        }
        document.getElementById('inspectRiskReviewLink').href = d.review_url || '#';

        // Tab 5: Raw JSON
        document.getElementById('inspectRawJsonBlock').textContent = activeInspectDeviceJson;

        // Reset to first tab
        const firstTab = document.getElementById('inspect-tab-specs-btn');
        if (firstTab) bootstrap.Tab.getOrCreateInstance(firstTab).show();

        // Show modal
        const modalEl = document.getElementById('deviceInspectModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    // Quick filter from KPI cards or pills
    function filterByStatus(status, paramKey, paramVal) {
        const url = new URL(window.location.origin + window.location.pathname);
        if (paramKey && paramVal) {
            url.searchParams.set(paramKey, paramVal);
        } else if (status) {
            url.searchParams.set('status', status);
        }
        window.location.href = url.toString();
    }

    // Toggle password visibility
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    // Quick preset selection for reason
    function applyPreset(text) {
        const reasonInput = document.getElementById('stepUpReasonInput');
        if (reasonInput) {
            reasonInput.value = text;
            reasonInput.focus();
        }
    }

    // Open Step-Up Security Modal
    function openStepUpModal(type, actionUrl, title, description, targetModel, targetUser, targetUuid) {
        const modalEl = document.getElementById('securityStepUpModal');
        const form = document.getElementById('stepUpModalForm');
        const titleEl = document.getElementById('stepUpModalTitle');
        const descEl = document.getElementById('stepUpActionDescription');
        const iconBox = document.getElementById('stepUpActionIcon');
        const targetModelEl = document.getElementById('stepUpTargetModel');
        const targetUserEl = document.getElementById('stepUpTargetUser');
        const targetUuidEl = document.getElementById('stepUpTargetUuid');
        const submitBtn = document.getElementById('stepUpSubmitBtn');
        const passwordInput = document.getElementById('stepUpPasswordInput');
        const reasonInput = document.getElementById('stepUpReasonInput');

        if (!modalEl || !form) return;

        // Reset classes
        modalEl.className = 'modal fade modal-security-stepup type-' + type;
        form.action = actionUrl;
        titleEl.textContent = title;
        descEl.textContent = description;
        targetModelEl.textContent = targetModel || 'Target Object';
        targetUserEl.textContent = targetUser || '';
        targetUuidEl.textContent = targetUuid ? targetUuid.substring(0, 16) + '…' : '';

        // Reset inputs
        passwordInput.value = '';
        reasonInput.value = '';

        // Configure styles based on action type
        if (type === 'approve') {
            iconBox.innerHTML = '<i class="fa-solid fa-shield-check"></i>';
            submitBtn.className = 'btn btn-success rounded-pill px-4 shadow-sm';
            submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Approve Device';
        } else if (type === 'reject') {
            iconBox.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            submitBtn.className = 'btn btn-danger rounded-pill px-4 shadow-sm';
            submitBtn.innerHTML = '<i class="fa-solid fa-xmark me-1"></i> Reject Device';
        } else if (type === 'block') {
            iconBox.innerHTML = '<i class="fa-solid fa-ban"></i>';
            submitBtn.className = 'btn btn-danger rounded-pill px-4 shadow-sm';
            submitBtn.innerHTML = '<i class="fa-solid fa-ban me-1"></i> Block Device';
        } else if (type === 'unblock') {
            iconBox.innerHTML = '<i class="fa-solid fa-lock-open"></i>';
            submitBtn.className = 'btn btn-warning rounded-pill px-4 shadow-sm';
            submitBtn.innerHTML = '<i class="fa-solid fa-lock-open me-1"></i> Unblock Device';
        } else if (type === 'revoke') {
            iconBox.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
            submitBtn.className = 'btn btn-danger rounded-pill px-4 shadow-sm';
            submitBtn.innerHTML = '<i class="fa-solid fa-trash-can me-1"></i> Revoke Installation';
        } else if (type === 'reregister') {
            iconBox.innerHTML = '<i class="fa-solid fa-key"></i>';
            submitBtn.className = 'btn btn-warning rounded-pill px-4 shadow-sm';
            submitBtn.innerHTML = '<i class="fa-solid fa-key me-1"></i> Require Re-registration';
        } else if (type === 'activate') {
            iconBox.innerHTML = '<i class="fa-solid fa-play"></i>';
            submitBtn.className = 'btn btn-success rounded-pill px-4 shadow-sm';
            submitBtn.innerHTML = '<i class="fa-solid fa-play me-1"></i> Activate Release';
        }

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
    }

    // Set Date Presets for Audit Logs Export
    function setAuditDatePreset(days) {
        const fromInput = document.getElementById('modal-audit-from');
        const toInput = document.getElementById('modal-audit-to');
        if (!fromInput || !toInput) return;

        const toDate = new Date();
        const fromDate = new Date();
        fromDate.setDate(toDate.getDate() - days);

        toInput.value = toDate.toISOString().split('T')[0];
        fromInput.value = fromDate.toISOString().split('T')[0];
    }
</script>
@endsection
