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
             TAB 1: DEVICE & SESSIONS
             ========================================== --}}
        <div class="tab-pane fade show active" id="tab-pane-devices" role="tabpanel" aria-labelledby="tab-btn-devices">
            <div class="security-panel-card">
                {{-- Sub-View Switch: Connected Devices vs Live Connected Sessions --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom px-4 pt-3 pb-2 bg-light bg-opacity-50">
                    <ul class="nav nav-pills gap-2" id="fleetSubTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('view', 'devices') === 'devices' ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold" id="subtab-devices-btn" data-bs-toggle="pill" data-bs-target="#subtab-devices-pane" type="button" role="tab" aria-selected="{{ request('view', 'devices') === 'devices' ? 'true' : 'false' }}">
                                <i class="fa-solid fa-mobile-screen-button me-1"></i> {{ __('app_security.subtab_devices') }}
                                <span class="badge bg-light text-dark border rounded-pill ms-1">{{ $devices->total() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('view') === 'sessions' ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold" id="subtab-sessions-btn" data-bs-toggle="pill" data-bs-target="#subtab-sessions-pane" type="button" role="tab" aria-selected="{{ request('view') === 'sessions' ? 'true' : 'false' }}">
                                <i class="fa-solid fa-network-wired me-1"></i> {{ __('app_security.subtab_sessions') }}
                                <span class="badge {{ $activeSessionsTotal > 0 ? 'bg-success' : 'bg-secondary' }} text-white rounded-pill ms-1">
                                    <span class="conn-pulse-dot online me-1" style="width: 6px; height: 6px;"></span>{{ $activeSessionsTotal }} Active
                                </span>
                            </button>
                        </li>
                    </ul>

                    <div class="text-muted small d-flex align-items-center gap-2">
                        <span class="badge bg-white text-dark border rounded-pill px-3 py-2 shadow-xs">
                            <i class="fa-solid fa-shield-halved text-primary me-1"></i> Total Inventory: <strong>{{ $devices->total() }}</strong> devices · <strong>{{ $totalSessionsCount }}</strong> sessions
                        </span>
                        <form method="post" action="{{ route('admin.app-security.devices.syncFleet') }}" id="fleetSyncFormTop" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1-5 shadow-xs fw-semibold" title="Synchronize all mobile hardware connected via mobile app API and login sessions">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Sync All Connected Devices
                            </button>
                        </form>
                    </div>
                </div>

                <div class="tab-content" id="fleetSubTabsContent">
                    {{-- ========================================================
                         SUB-TAB 1: CONNECTED DEVICES FLEET
                         ======================================================== --}}
                    <div class="tab-pane fade {{ request('view', 'devices') === 'devices' ? 'show active' : '' }}" id="subtab-devices-pane" role="tabpanel" aria-labelledby="subtab-devices-btn">
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

                {{-- Quick Filter Chips Bar --}}
                @php
                    $isAllActive = !request()->anyFilled(['status', 'user_type', 'attestation_level', 'risk_level', 'activity', 'version']);
                    $hasFleetFilters = request()->anyFilled(['q', 'status', 'user_type', 'attestation_level', 'risk_level', 'activity', 'sort_by', 'version']);
                @endphp
                <div class="fleet-quick-chips mb-3">
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

                {{-- Collapsible Directory Filter Panel --}}
                <div class="directory-filter-panel mb-4">
                    @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'deviceFleetDirectoryFilters', 'directoryExpanded' => $hasFleetFilters])
                    <div id="deviceFleetDirectoryFilters" class="collapse {{ $hasFleetFilters ? 'show' : '' }}">
                        <form id="deviceFleetFilterForm" class="directory-filter-form" action="{{ route('admin.app-security.index') }}" method="get">
                            <input type="hidden" name="view" value="devices">
                            <div class="row g-3">
                                <div class="col-xl-3 col-md-6">
                                    <label for="filterStatus" class="form-label">{{ __('app_security.status') }}</label>
                                    <select id="filterStatus" name="status" class="form-select">
                                        <option value="">{{ __('app_security.all_statuses') }}</option>
                                        @foreach(['pending' => __('app_security.status_pending'), 'approved' => __('app_security.status_approved'), 'blocked' => __('app_security.status_blocked'), 'rejected' => __('app_security.status_rejected'), 'revoked' => __('app_security.status_revoked'), 're_registration_required' => __('app_security.status_re_registration')] as $statusKey => $statusLabel)
                                            <option value="{{ $statusKey }}" @selected(request('status') === $statusKey)>{{ $statusLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <label for="filterUserType" class="form-label">{{ __('app_security.user_type') }}</label>
                                    <select id="filterUserType" name="user_type" class="form-select">
                                        <option value="">{{ __('app_security.all_user_types') }}</option>
                                        <option value="staff" @selected(request('user_type') === 'staff')>{{ __('app_security.type_staff') }}</option>
                                        <option value="customer" @selected(request('user_type') === 'customer')>{{ __('app_security.type_customer') }}</option>
                                        <option value="admin" @selected(request('user_type') === 'admin')>{{ __('app_security.type_admin') }}</option>
                                    </select>
                                </div>

                                @unless($supportOnly)
                                    <div class="col-xl-3 col-md-6">
                                        <label for="filterAttestation" class="form-label">{{ __('app_security.attestation_level') }}</label>
                                        <select id="filterAttestation" name="attestation_level" class="form-select">
                                            <option value="">{{ __('app_security.all_attestation') }}</option>
                                            <option value="strongbox" @selected(request('attestation_level') === 'strongbox')>{{ __('app_security.attestation_strongbox') }}</option>
                                            <option value="tee" @selected(request('attestation_level') === 'tee')>{{ __('app_security.attestation_tee') }}</option>
                                            <option value="basic" @selected(request('attestation_level') === 'basic')>{{ __('app_security.attestation_basic') }}</option>
                                            <option value="none" @selected(request('attestation_level') === 'none')>{{ __('app_security.attestation_none') }}</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label for="filterRisk" class="form-label">{{ __('app_security.risk_level') }}</label>
                                        <select id="filterRisk" name="risk_level" class="form-select">
                                            <option value="">{{ __('app_security.all_risk_levels') }}</option>
                                            <option value="low" @selected(request('risk_level') === 'low')>{{ __('app_security.risk_low') }}</option>
                                            <option value="medium" @selected(request('risk_level') === 'medium')>{{ __('app_security.risk_medium') }}</option>
                                            <option value="high" @selected(request('risk_level') === 'high')>{{ __('app_security.risk_high') }}</option>
                                        </select>
                                    </div>
                                @endunless

                                <div class="col-xl-3 col-md-6">
                                    <label for="filterActivity" class="form-label">{{ __('app_security.activity') }}</label>
                                    <select id="filterActivity" name="activity" class="form-select">
                                        <option value="">{{ __('app_security.all_activity') }}</option>
                                        <option value="online" @selected(request('activity') === 'online')>{{ __('app_security.activity_online') }}</option>
                                        <option value="today" @selected(request('activity') === 'today')>{{ __('app_security.activity_today') }}</option>
                                        <option value="week" @selected(request('activity') === 'week')>{{ __('app_security.activity_week') }}</option>
                                        <option value="inactive" @selected(request('activity') === 'inactive')>{{ __('app_security.activity_inactive') }}</option>
                                    </select>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <label for="filterSortBy" class="form-label">{{ __('app_security.sort') ?? 'Sort By' }}</label>
                                    <select id="filterSortBy" name="sort_by" class="form-select">
                                        <option value="last_seen" @selected(request('sort_by', 'last_seen') === 'last_seen')>{{ __('app_security.sort_last_seen_desc') }}</option>
                                        <option value="registered" @selected(request('sort_by') === 'registered')>{{ __('app_security.sort_registered_desc') }}</option>
                                        <option value="risk_score" @selected(request('sort_by') === 'risk_score')>{{ __('app_security.sort_risk_desc') }}</option>
                                        <option value="model" @selected(request('sort_by') === 'model')>{{ __('app_security.sort_model_asc') }}</option>
                                    </select>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <label for="filterAppVersion" class="form-label">{{ __('app_security.app_version') }}</label>
                                    <select id="filterAppVersion" name="version" class="form-select">
                                        <option value="">{{ __('app_security.all_releases') }}</option>
                                        @if(isset($availableVersions) && count($availableVersions))
                                            @foreach($availableVersions as $v)
                                                <option value="{{ $v }}" @selected(request('version') === (string)$v)>v{{ $v }}</option>
                                            @endforeach
                                        @elseif(isset($releases) && $releases->count())
                                            @foreach($releases as $rel)
                                                <option value="{{ $rel->version_name }}" @selected(request('version') === $rel->version_name)>v{{ $rel->version_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <label for="fleetSearchQuery" class="form-label">{{ __('app_security.search') }}</label>
                                    <input type="text" id="fleetSearchQuery" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('app_security.search_placeholder') }}">
                                </div>
                            </div>
                            @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.app-security.index', ['view' => 'devices'])])
                        </form>
                    </div>
                </div>

                {{-- Standard Directory Toolbar & Card Table --}}
                <div class="card border-0 shadow-sm mb-4">
                    @include('admin.section.directory-toolbar', [
                        'directoryTable' => 'deviceFleetDirectoryTable',
                        'directoryForm' => 'deviceFleetFilterForm',
                        'directorySearch' => 'deviceFleetSearch',
                        'directoryRecords' => $devices,
                        'directoryTitle' => __('app_security.registered_devices'),
                        'directoryIcon' => 'smartphone',
                        'directoryCanAdd' => true,
                        'directoryAddUrl' => route('admin.app-security.devices.syncFleet'),
                        'directoryAddLabel' => 'Sync Fleet',
                        'directoryDefaultSize' => '25',
                        'directorySearchKey' => 'q',
                        'directoryLive' => false
                    ])
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="deviceFleetDirectoryTable" class="table security-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th>{{ __('app_security.user') }}</th>
                                        <th>{{ __('app_security.device_info') }}</th>
                                        <th>{{ __('app_security.last_seen') }}</th>
                                        @unless($supportOnly)
                                            <th>{{ __('app_security.release') }}</th>
                                            <th>{{ __('app_security.trust_attestation') }}</th>
                                        @endunless
                                        <th>{{ __('app_security.status') }}</th>
                                        <th class="text-center" data-directory-actions style="width: 130px;">{{ __('app_security.actions') }}</th>
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

                                // User location & battery telemetry
                                $devUser = $device->user;
                                $devLoc = $devUser?->latestDeviceLocation;
                                $devLat = $devLoc?->latitude;
                                $devLng = $devLoc?->longitude;
                                $devAcc = $devLoc?->accuracy;
                                $devBattery = $devLoc?->battery_level;
                                $devHasFcm = !empty($devUser?->fcm_token);
                                $devMapUrl = ($devLat && $devLng) ? "https://www.google.com/maps?q={$devLat},{$devLng}" : null;

                                $sessionsList = $device->sessions->map(function ($s) use ($device, $devLat, $devLng, $devMapUrl) {
                                    $isRev = $s->revoked_at !== null;
                                    $isExp = !$isRev && $s->expires_at <= now();
                                    $isAct = !$isRev && !$isExp;
                                    return [
                                        'id' => $s->id,
                                        'uuid' => $s->uuid,
                                        'platform' => 'Android',
                                        'token_preview' => substr($s->session_token_sha256, 0, 12) . '…',
                                        'token_full' => $s->session_token_sha256,
                                        'created_at' => $s->created_at?->format('Y-m-d H:i:s'),
                                        'created_at_human' => $s->created_at?->diffForHumans() ?? '—',
                                        'last_used_at' => $s->last_used_at?->format('Y-m-d H:i:s'),
                                        'last_used_human' => $s->last_used_at?->diffForHumans() ?? 'Never',
                                        'expires_at' => $s->expires_at?->format('Y-m-d H:i:s'),
                                        'expires_at_human' => $s->expires_at?->diffForHumans() ?? '—',
                                        'is_active' => $isAct,
                                        'is_online' => $isAct && (int) ($device->user?->online_status ?? 0) === 1,
                                        'status_text' => $isRev ? 'Revoked' : ($isExp ? 'Expired' : 'Online Now'),
                                        'latitude' => $devLat,
                                        'longitude' => $devLng,
                                        'map_url' => $devMapUrl,
                                        'revoke_url' => route('admin.app-security.devices.sessions.revoke', [$device->uuid, $s->uuid]),
                                    ];
                                })->values()->all();

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
                                    'battery_level' => $devBattery,
                                    'has_fcm' => $devHasFcm,
                                    'employee' => [
                                        'id' => $device->user_id,
                                        'name' => $devUser?->name ?? 'Unknown User',
                                        'employee_code' => $devUser?->employee_code ?: 'N/A',
                                        'avatar' => $devUser?->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $devUser->avatar) : asset('assets/images/img.png'),
                                        'role' => $devUser?->role?->name ?? 'Staff',
                                        'department' => $devUser?->department?->dept_name ?? '—',
                                        'branch' => $devUser?->branch?->name ?? '—',
                                        'workspace_type' => $devUser?->workspace_type == 1 ? 'Field' : 'Office',
                                        'is_online' => (int) ($devUser?->online_status ?? 0) === 1,
                                        'username' => $devUser?->username ?? '—',
                                        'email' => $devUser?->email ?? '—',
                                        'phone' => $devUser?->phone ?? '—',
                                        'last_login_at' => $devUser?->updated_at?->format('Y-m-d H:i:s'),
                                        'last_login_human' => $devUser?->updated_at?->diffForHumans() ?? '—',
                                    ],
                                    'location' => [
                                        'has_location' => !empty($devLat) && !empty($devLng),
                                        'latitude' => $devLat,
                                        'longitude' => $devLng,
                                        'accuracy' => $devAcc,
                                        'map_url' => $devMapUrl,
                                        'status_text' => (int) ($devUser?->online_status ?? 0) === 1 ? 'Live Location' : 'Last Offline Location',
                                        'updated_at' => $devLoc?->updated_at?->format('Y-m-d H:i:s'),
                                        'updated_at_human' => $devLoc?->updated_at?->diffForHumans() ?? 'N/A',
                                    ],
                                    'sessions' => $sessionsList,
                                    'user_name' => $devUser?->name ?? 'Unknown User',
                                    'user_username' => $devUser?->username ?? '—',
                                    'user_email' => $devUser?->email ?? '—',
                                    'user_phone' => $devUser?->phone ?? '—',
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
                                    'revoke_all_url' => route('admin.app-security.devices.sessions.revokeAll', $device->uuid),
                                ];
                            @endphp
                            <tr>
                                <td class="text-center text-muted small">{{ $devices->firstItem() + $loop->index }}</td>
                                {{-- 1. User Profile & Role --}}
                                <td>
                                    <div class="device-user-box">
                                        <div class="device-user-avatar position-relative">
                                            @if($device->user?->avatar)
                                                <img src="{{ asset(\App\Models\User::AVATAR_UPLOAD_PATH . $device->user->avatar) }}" alt="{{ $device->user->name }}" class="w-100 h-100 rounded-3 object-fit-cover">
                                            @else
                                                {{ strtoupper(substr($device->user?->name ?? 'D', 0, 1)) }}
                                            @endif
                                            @if((int)($device->user?->online_status ?? 0) === 1)
                                                <span class="position-absolute bottom-0 end-0 rounded-circle bg-success border border-white" style="width: 9px; height: 9px;" title="Online Now"></span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                <span class="device-user-name mb-0">{{ $device->user?->name ?? 'Deleted account' }}</span>
                                                @if(!empty($device->user?->employee_code))
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0 fw-medium" style="font-size: 10px;">{{ $device->user->employee_code }}</span>
                                                @endif
                                            </div>
                                            <div class="device-user-meta">{{ $device->user?->username ?? '—' }}</div>
                                            <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                                <span class="badge {{ $typeBadge }} px-2 py-0 rounded-pill" style="font-size: 10px;">
                                                    {{ ucfirst($device->user_type) }}
                                                </span>
                                                @if($device->user?->role)
                                                    <span class="badge bg-light text-muted border px-2 py-0 rounded-pill" style="font-size: 10px;">
                                                        {{ $device->user->role->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($device->user?->department || $device->user?->branch)
                                                <div class="text-muted small mt-1 text-truncate" style="font-size: 10px; max-width: 175px;">
                                                    <i class="fa-solid fa-building me-1 opacity-75"></i>{{ $device->user?->department?->dept_name ?? '—' }} &bull; {{ $device->user?->branch?->name ?? '—' }}
                                                </div>
                                            @endif
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

                                            {{-- Battery and FCM badges --}}
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap my-1">
                                                @if($devBattery !== null)
                                                    @php
                                                        $bIcon = $devBattery >= 80 ? 'fa-battery-full' : ($devBattery >= 50 ? 'fa-battery-three-quarters' : ($devBattery >= 20 ? 'fa-battery-quarter' : 'fa-battery-empty'));
                                                        $bClass = $devBattery >= 40 ? 'battery-good' : ($devBattery >= 20 ? 'battery-warn' : 'battery-danger');
                                                    @endphp
                                                    <span class="battery-pill {{ $bClass }}" title="Battery level: {{ $devBattery }}%">
                                                        <i class="fa-solid {{ $bIcon }}"></i> {{ $devBattery }}%
                                                    </span>
                                                @endif
                                                @if($devHasFcm)
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 10px;" title="{{ __('app_security.fcm_active') }}">
                                                        <i class="fa-solid fa-bell me-1"></i>FCM
                                                    </span>
                                                @endif
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

                                    {{-- GPS Location Coordinates & Map Link --}}
                                    @if($devMapUrl)
                                        <div class="mt-1">
                                            <a href="{{ $devMapUrl }}" target="_blank" rel="noopener noreferrer" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 10px;" title="View GPS coordinates on Google Maps: {{ $devLat }}, {{ $devLng }}">
                                                <i class="fa-solid fa-location-dot"></i> {{ number_format($devLat, 4) }}, {{ number_format($devLng, 4) }}
                                                <i class="fa-solid fa-arrow-up-right-from-square ms-0.5" style="font-size: 8px;"></i>
                                            </a>
                                        </div>
                                    @endif

                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        @if(($device->active_sessions_count ?? 0) > 0)
                                            <a href="{{ route('admin.app-security.index', ['view' => 'sessions', 'session_device' => $device->uuid]) }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill text-decoration-none" style="font-size: 10px;" title="Click to view live sessions for this device">
                                                <i class="fa-solid fa-network-wired me-1"></i>{{ $device->active_sessions_count }} Active
                                            </a>
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
                                            <a class="btn btn-sec-action btn-outline-secondary" href="{{ route('admin.app-security.index', ['view' => 'sessions', 'session_device' => $device->uuid]) }}" title="{{ __('app_security.sessions') }}">
                                                <i class="fa-solid fa-network-wired"></i>
                                                <span class="d-none d-xxl-inline">{{ __('app_security.sessions') }}</span>
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
                                <td colspan="{{ $supportOnly ? 6 : 8 }}" class="text-center py-5">
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
            </div>

            @if($devices->hasPages())
                <div class="card-footer bg-white border-top py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="small text-muted">
                        Showing {{ $devices->firstItem() ?? 0 }} to {{ $devices->lastItem() ?? 0 }} of {{ number_format($devices->total()) }} devices
                    </div>
                    <div>
                        {{ $devices->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

                    {{-- ========================================================
                         SUB-TAB 2: LIVE CONNECTED SESSIONS
                         ======================================================== --}}
                    <div class="tab-pane fade {{ request('view') === 'sessions' ? 'show active' : '' }}" id="subtab-sessions-pane" role="tabpanel" aria-labelledby="subtab-sessions-btn">
                        {{-- Sessions Header with Telemetry Summary --}}
                        <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="kpi-icon-wrap" style="width: 44px; height: 44px; font-size: 20px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                    <i class="fa-solid fa-network-wired"></i>
                                </div>
                                <div>
                                    <h5 class="panel-title mb-0">
                                        <span>{{ __('app_security.subtab_sessions') }}</span>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill fs-6 px-3 ms-2">
                                            {{ number_format($activeSessionsTotal) }} {{ __('app_security.session_active') }}
                                        </span>
                                    </h5>
                                    <div class="text-muted small mt-1">
                                        Live cryptographic API sessions with challenge-response token validation and real-time heartbeat monitoring
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                    <span class="conn-pulse-dot online me-1"></span>
                                    <strong>{{ number_format($activeSessionsTotal) }}</strong> Active
                                </span>
                                <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">
                                    <i class="fa-solid fa-database me-1"></i>
                                    <strong>{{ number_format($totalSessionsCount) }}</strong> Total Recorded
                                </span>
                            </div>
                        </div>

                        {{-- Collapsible Directory Filter Panel for Sessions --}}
                        @php
                            $hasSessionFilters = request()->anyFilled(['session_q', 'session_status', 'session_device']);
                        @endphp
                        <div class="directory-filter-panel mb-4">
                            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'sessionsDirectoryFilters', 'directoryExpanded' => $hasSessionFilters])
                            <div id="sessionsDirectoryFilters" class="collapse {{ $hasSessionFilters ? 'show' : '' }}">
                                <form id="sessionsFilterForm" class="directory-filter-form" action="{{ route('admin.app-security.index') }}" method="get">
                                    <input type="hidden" name="view" value="sessions">
                                    <div class="row g-3">
                                        <div class="col-xl-4 col-md-6">
                                            <label for="filterSessionStatus" class="form-label">{{ __('app_security.status') }}</label>
                                            <select id="filterSessionStatus" name="session_status" class="form-select">
                                                <option value="">{{ __('app_security.all_sessions') }}</option>
                                                <option value="active" @selected(request('session_status') === 'active')>{{ __('app_security.active_sessions_only') }}</option>
                                                <option value="revoked" @selected(request('session_status') === 'revoked')>{{ __('app_security.revoked_sessions_only') }}</option>
                                                <option value="expired" @selected(request('session_status') === 'expired')>{{ __('app_security.expired_sessions_only') }}</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-4 col-md-6">
                                            <label for="filterSessionQ" class="form-label">{{ __('app_security.search') }}</label>
                                            <input type="text" id="filterSessionQ" name="session_q" value="{{ request('session_q') }}" class="form-control" placeholder="{{ __('app_security.search_sessions') }}">
                                        </div>

                                        @if(request()->filled('session_device'))
                                            <div class="col-xl-4 col-md-6">
                                                <label class="form-label">Filtered Device UUID</label>
                                                <div class="input-group">
                                                    <input type="text" name="session_device" value="{{ request('session_device') }}" class="form-control" readonly>
                                                    <a href="{{ route('admin.app-security.index', ['view' => 'sessions']) }}" class="btn btn-outline-secondary" title="Clear device filter"><i class="fa-solid fa-xmark"></i></a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.app-security.index', ['view' => 'sessions'])])
                                </form>
                            </div>
                        </div>

                        {{-- Standard Directory Toolbar & Card Table for Sessions --}}
                        <div class="card border-0 shadow-sm mb-4">
                            @include('admin.section.directory-toolbar', [
                                'directoryTable' => 'sessionsDirectoryTable',
                                'directoryForm' => 'sessionsFilterForm',
                                'directorySearch' => 'sessionsSearch',
                                'directoryRecords' => $allSessions,
                                'directoryTitle' => __('app_security.subtab_sessions'),
                                'directoryIcon' => 'cpu',
                                'directoryCanAdd' => true,
                                'directoryAddUrl' => route('admin.app-security.devices.syncFleet'),
                                'directoryAddLabel' => 'Sync Fleet',
                                'directoryDefaultSize' => '25',
                                'directorySearchKey' => 'session_q',
                                'directoryLive' => false
                            ])
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="sessionsDirectoryTable" class="table security-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <th>{{ __('app_security.device_info') }}</th>
                                                <th>{{ __('app_security.user') }}</th>
                                                <th>{{ __('app_security.last_location') }}</th>
                                                <th>{{ __('app_security.session_token') }}</th>
                                                <th>{{ __('app_security.session_created') }}</th>
                                                <th>{{ __('app_security.session_last_used') }}</th>
                                                <th>{{ __('app_security.session_expires') }}</th>
                                                <th>{{ __('app_security.session_status') }}</th>
                                                <th class="text-center" data-directory-actions style="width: 120px;">{{ __('app_security.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($allSessions as $session)
                                            @php
                                                $isRevoked = $session->revoked_at !== null;
                                                $isExpired = !$isRevoked && $session->expires_at <= now();
                                                $isActive = !$isRevoked && !$isExpired;
                                                $sessLoc = $session->user?->latestDeviceLocation;
                                                $sessLat = $sessLoc?->latitude;
                                                $sessLng = $sessLoc?->longitude;
                                                $sessBattery = $sessLoc?->battery_level;
                                                $sessMapUrl = ($sessLat && $sessLng) ? "https://www.google.com/maps?q={$sessLat},{$sessLng}" : null;
                                            @endphp
                                            <tr>
                                                <td class="text-center text-muted small">{{ $allSessions->firstItem() + $loop->index }}</td>
                                        {{-- 1. Device Info --}}
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-mobile-screen text-muted fs-5"></i>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $session->device?->device_model ?: 'Android Device' }}</div>
                                                    <div class="device-user-meta mb-1">
                                                        <span class="badge bg-light text-dark border py-0 px-2">Android {{ $session->device?->android_version ?: 'OS' }}</span>
                                                    </div>
                                                    @if($sessBattery !== null)
                                                        @php
                                                            $sbIcon = $sessBattery >= 80 ? 'fa-battery-full' : ($sessBattery >= 50 ? 'fa-battery-three-quarters' : ($sessBattery >= 20 ? 'fa-battery-quarter' : 'fa-battery-empty'));
                                                            $sbClass = $sessBattery >= 40 ? 'battery-good' : ($sessBattery >= 20 ? 'battery-warn' : 'battery-danger');
                                                        @endphp
                                                        <span class="battery-pill {{ $sbClass }} mb-1" title="Battery level: {{ $sessBattery }}%">
                                                            <i class="fa-solid {{ $sbIcon }}"></i> {{ $sessBattery }}%
                                                        </span>
                                                    @endif
                                                    @if($session->device?->uuid)
                                                        <div class="copy-badge mt-1" onclick="copyToClipboard('{{ $session->device->uuid }}', this)" title="Click to copy Device UUID: {{ $session->device->uuid }}">
                                                            <i class="fa-solid fa-fingerprint text-primary"></i>
                                                            <span>{{ substr($session->device->uuid, 0, 8) }}…</span>
                                                            <i class="fa-regular fa-copy"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 2. User Account --}}
                                        <td>
                                            <div class="device-user-box">
                                                <div class="device-user-avatar position-relative">
                                                    @if($session->user?->avatar)
                                                        <img src="{{ asset(\App\Models\User::AVATAR_UPLOAD_PATH . $session->user->avatar) }}" alt="{{ $session->user->name }}" class="w-100 h-100 rounded-3 object-fit-cover">
                                                    @else
                                                        {{ strtoupper(substr($session->user?->name ?? 'U', 0, 1)) }}
                                                    @endif
                                                    @if((int)($session->user?->online_status ?? 0) === 1)
                                                        <span class="position-absolute bottom-0 end-0 rounded-circle bg-success border border-white" style="width: 8px; height: 8px;" title="Online Now"></span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                        <span class="device-user-name mb-0">{{ $session->user?->name ?? 'User' }}</span>
                                                        @if(!empty($session->user?->employee_code))
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0 fw-medium" style="font-size: 10px;">{{ $session->user->employee_code }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="device-user-meta">{{ $session->user?->username ?? '—' }}</div>
                                                    @if($session->user?->department || $session->user?->branch)
                                                        <div class="text-muted small mt-0.5 text-truncate" style="font-size: 10px; max-width: 160px;">
                                                            {{ $session->user?->department?->dept_name ?? '—' }} &bull; {{ $session->user?->branch?->name ?? '—' }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 3. GPS Location --}}
                                        <td>
                                            @if($sessMapUrl)
                                                <a href="{{ $sessMapUrl }}" target="_blank" rel="noopener noreferrer" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 10.5px;" title="View GPS coordinates on Google Maps: {{ $sessLat }}, {{ $sessLng }}">
                                                    <i class="fa-solid fa-location-dot"></i> {{ number_format($sessLat, 4) }}, {{ number_format($sessLng, 4) }}
                                                    <i class="fa-solid fa-arrow-up-right-from-square ms-0.5" style="font-size: 8px;"></i>
                                                </a>
                                                <div class="text-muted small mt-1" style="font-size: 10px;">
                                                    {{ $sessLoc?->updated_at?->diffForHumans() ?? '—' }}
                                                </div>
                                            @else
                                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                                    <i class="fa-solid fa-location-slash me-1"></i> No GPS
                                                </span>
                                            @endif
                                        </td>

                                        {{-- 4. Token Identity --}}
                                        <td>
                                            <div class="copy-badge mb-1" onclick="copyToClipboard('{{ $session->session_token_sha256 }}', this)" title="Click to copy full Session Token SHA-256: {{ $session->session_token_sha256 }}">
                                                <i class="fa-solid fa-key text-primary"></i>
                                                <span class="fw-bold">Token: {{ substr($session->session_token_sha256, 0, 12) }}…</span>
                                                <i class="fa-regular fa-copy"></i>
                                            </div>
                                            @if($session->refresh_token_sha256)
                                                <div class="copy-badge" onclick="copyToClipboard('{{ $session->refresh_token_sha256 }}', this)" title="Click to copy Refresh Token SHA-256: {{ $session->refresh_token_sha256 }}">
                                                    <i class="fa-solid fa-arrows-rotate text-info"></i>
                                                    <span>Refresh: {{ substr($session->refresh_token_sha256, 0, 10) }}…</span>
                                                    <i class="fa-regular fa-copy"></i>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 5. Created At --}}
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $session->created_at?->format('M d, Y') }}</div>
                                            <div class="text-muted small">{{ $session->created_at?->format('H:i:s') }}</div>
                                        </td>

                                        {{-- 6. Last Heartbeat / Used --}}
                                        <td>
                                            @if($session->last_used_at)
                                                <div class="fw-semibold text-dark" title="{{ $session->last_used_at->format('Y-m-d H:i:s') }}">
                                                    {{ $session->last_used_at->diffForHumans() }}
                                                </div>
                                                <div class="text-muted small">{{ $session->last_used_at->format('M d, H:i') }}</div>
                                            @else
                                                <span class="text-muted fst-italic">Never</span>
                                            @endif
                                        </td>

                                        {{-- 7. Expires At --}}
                                        <td>
                                            <div class="fw-semibold {{ $isExpired ? 'text-danger' : 'text-dark' }}">
                                                {{ $session->expires_at->diffForHumans() }}
                                            </div>
                                            <div class="text-muted small">{{ $session->expires_at->format('M d, H:i') }}</div>
                                        </td>

                                        {{-- 8. Status --}}
                                        <td>
                                            @if($isRevoked)
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1">
                                                    <i class="fa-solid fa-ban me-1"></i> Revoked
                                                </span>
                                                <div class="text-muted small mt-1" style="font-size: 11px;" title="{{ $session->revocation_reason }}">
                                                    {{ \Illuminate\Support\Str::limit($session->revocation_reason ?: 'By admin', 24) }}
                                                </div>
                                            @elseif($isExpired)
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3 py-1">
                                                    <i class="fa-solid fa-hourglass-end me-1"></i> Expired
                                                </span>
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                                                    <span class="conn-pulse-dot online me-1"></span> Active Token
                                                </span>
                                            @endif
                                        </td>

                                        {{-- 9. Actions --}}
                                        <td class="text-end">
                                            @if($isActive && ($canManageDevices || ($canApproveStaffDevices && $session->device?->user_type === 'staff')))
                                                <button type="button" class="btn btn-sec-action btn-outline-danger"
                                                    onclick="openStepUpModal('revoke-session', '{{ route('admin.app-security.devices.sessions.revoke', [$session->device->uuid, $session->uuid]) }}', 'Revoke Connected API Session', 'Revoking will immediately invalidate this session token and refresh token on the cryptographic server. The mobile client will be forced to re-authenticate.', '{{ $session->device?->device_model ?: 'Android Device' }}', '{{ $session->user?->name ?? 'User' }}', '{{ $session->uuid }}')">
                                                    <i class="fa-solid fa-ban"></i> Revoke
                                                </button>
                                            @else
                                                <span class="text-muted small fst-italic">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <div class="py-4">
                                                <i class="fa-solid fa-network-wired fs-1 text-muted mb-3 d-block opacity-50"></i>
                                                <h5 class="text-muted fw-bold">{{ __('app_security.no_sessions_found') }}</h5>
                                                <p class="text-muted small mb-0">No API token sessions match your filter criteria.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($allSessions->hasPages())
                        <div class="card-footer bg-white border-top py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="small text-muted">
                                Showing {{ $allSessions->firstItem() ?? 0 }} to {{ $allSessions->lastItem() ?? 0 }} of {{ number_format($allSessions->total()) }} sessions
                            </div>
                            <div>
                                {{ $allSessions->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
                </div>
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
                                            <input class="form-check-input" type="checkbox" name="enabled" value="1" @checked($securityEnabled ?? false)>
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
                <input type="hidden" name="from_index" value="1">
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
     MODAL: INSPECT FULL DEVICE TELEMETRY, SESSIONS & ACTIVITY LOGS
     ============================================================== --}}
<div class="modal fade modal-device-inspect text-start" id="deviceInspectModal" tabindex="-1" aria-labelledby="deviceInspectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            {{-- Modal Header --}}
            <div class="modal-header-hero text-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 w-100">
                    <div class="d-flex align-items-center gap-3">
                        <img id="inspectModalAvatar" src="{{ asset('assets/images/img.png') }}" alt="Avatar" class="rounded-circle shadow-sm" style="width: 48px; height: 48px; object-fit: cover; border: 2px solid rgba(255,255,255,0.4);">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="modal-title fw-bold text-white mb-0" id="inspectModalEmployeeName">
                                    {{ __('index.loading') }}
                                </h5>
                                <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 py-0.5 fw-medium" id="inspectModalEmployeeCode" style="font-size: 11px;">
                                    ---
                                </span>
                                <span class="badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 eda-status-offline" id="inspectModalOnlineBadge" style="font-size: 11px;">
                                    <span class="eda-pulse-dot"></span>
                                    <span id="inspectModalOnlineText">{{ __('index.loading') }}</span>
                                </span>
                                <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5 fw-semibold" id="inspectModalViewMapBtn" title="{{ __('app_security.view_on_map') }}">
                                    <i class="fa-solid fa-location-dot text-info"></i>
                                    <span>{{ __('app_security.view_live_location') }}</span>
                                </a>
                            </div>
                            <div class="text-white-50 small mt-1 text-truncate" id="inspectModalRoleDeptBranch" style="font-size: 0.8rem;">
                                ---
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- Modal Subnav / Tabs --}}
            <div class="modal-tabs-bar">
                <ul class="nav nav-tabs border-0 gap-2" id="deviceInspectTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active d-inline-flex align-items-center gap-1.5" id="inspect-tab-devsess-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-devsess" type="button" role="tab">
                            <i class="fa-solid fa-mobile-screen"></i>
                            <span>{{ __('app_security.device_sessions') }}</span>
                            <span class="badge bg-primary rounded-pill ms-1" id="inspectTabSessionCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-inline-flex align-items-center gap-1.5" id="inspect-tab-crypto-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-crypto" type="button" role="tab">
                            <i class="fa-solid fa-key"></i>
                            <span>{{ __('app_security.tab_crypto') }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-inline-flex align-items-center gap-1.5" id="inspect-tab-risk-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-risk" type="button" role="tab">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>{{ __('app_security.tab_risk') }}</span>
                            <span class="badge bg-danger rounded-pill ms-1" id="inspectTabRiskBadge">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-inline-flex align-items-center gap-1.5" id="inspect-tab-activities-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-activities" type="button" role="tab">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>{{ __('app_security.activity_logs') }}</span>
                            <span class="badge bg-secondary rounded-pill ms-1" id="inspectTabActivityCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-inline-flex align-items-center gap-1.5" id="inspect-tab-raw-btn" data-bs-toggle="tab" data-bs-target="#inspect-tab-raw" type="button" role="tab">
                            <i class="fa-solid fa-code"></i>
                            <span>{{ __('app_security.tab_raw_json') }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4 position-relative" style="min-height: 400px; max-height: calc(85vh - 180px); overflow-y: auto;">
                <div class="tab-content" id="deviceInspectTabsContent">
                    {{-- TAB 1: DEVICE & ACTIVE SESSIONS --}}
                    <div class="tab-pane fade show active" id="inspect-tab-devsess" role="tabpanel">
                        {{-- 4-Column Primary Device Card (Mirrors Employees > Device & Sessions) --}}
                        <div class="card border border-slate-200 shadow-none mb-4" style="border-radius: 12px; background: #ffffff;">
                            <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <a href="#" target="_blank" rel="noopener noreferrer" class="d-flex align-items-center gap-2 text-decoration-none" id="inspectDevicePlatformLink" title="{{ __('app_security.view_on_map') }}">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 28px; height: 28px;">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                    <span class="fw-bold text-dark small">{{ __('app_security.device_info') }} &amp; {{ __('app_security.connection_status') }}</span>
                                    <i class="fa-solid fa-location-dot text-primary"></i>
                                </a>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span id="inspectDeviceStatusBadge" class="device-status-badge status-approved py-0 px-2" style="font-size: 11px;">Approved</span>
                                    <span id="inspectDevicePlatformBadge" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-0.5" style="font-size: 11px;">Android</span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    {{-- Col 1: Device & Hardware --}}
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-2.5 rounded-3 bg-light border border-1 border-opacity-50 h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <span class="text-muted d-block small" style="font-size: 11px;">{{ __('app_security.device_info') }}</span>
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap mt-0.5">
                                                    <span class="fw-bold text-dark text-truncate" id="inspectCardDeviceName" style="font-size: 0.9rem;">---</span>
                                                    <span id="inspectCardBatteryBadge"></span>
                                                </div>
                                                <small class="text-muted d-block mt-1 text-truncate" id="inspectCardDeviceModel" style="font-size: 10.5px;">---</small>
                                            </div>
                                            <div class="mt-2 pt-2 border-top border-1 border-opacity-50 d-flex align-items-center justify-content-between">
                                                <small class="text-muted text-truncate font-monospace" id="inspectCardDeviceUuid" style="font-size: 10px; max-width: 150px;">---</small>
                                                <button type="button" class="btn btn-link btn-xs p-0 text-muted" id="inspectCardCopyUuidBtn" title="Copy UUID">
                                                    <i class="fa-regular fa-copy" style="font-size: 11px;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Col 2: Connection & Activity --}}
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-2.5 rounded-3 bg-light border border-1 border-opacity-50 h-100">
                                            <span class="text-muted d-block small" style="font-size: 11px;">{{ __('app_security.connection_status') }}</span>
                                            <div class="mt-1 mb-2" id="inspectCardConnectionBadge">
                                                <span class="badge bg-secondary bg-opacity-10 text-muted rounded-pill px-2 py-0.5" style="font-size: 10.5px;">---</span>
                                            </div>
                                            <div class="mb-1.5">
                                                <span class="text-muted d-block" style="font-size: 10px;">{{ __('app_security.login_time') }}</span>
                                                <span class="fw-bold text-dark d-block" id="inspectCardLoginTime" style="font-size: 0.82rem;">---</span>
                                                <small class="text-muted d-block" id="inspectCardLoginTimeHuman" style="font-size: 10px;">---</small>
                                            </div>
                                            <div>
                                                <span class="text-muted d-block" style="font-size: 10px;">{{ __('app_security.last_seen') }}</span>
                                                <span class="fw-semibold text-dark d-block" id="inspectCardLastSeenTime" style="font-size: 0.82rem;">---</span>
                                                <small class="text-muted d-block" id="inspectCardLastSeenHuman" style="font-size: 10px;">---</small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Col 3: App Information --}}
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-2.5 rounded-3 bg-light border border-1 border-opacity-50 h-100">
                                            <span class="text-muted d-block small" style="font-size: 11px;">App &amp; Notifications</span>
                                            <span class="fw-bold text-dark d-block text-truncate mt-0.5" id="inspectCardAppName" style="font-size: 0.88rem;">---</span>
                                            <small class="text-muted d-block mt-0.5 text-truncate" id="inspectCardAppVersion" style="font-size: 10.5px;">---</small>
                                            <div class="mt-2 pt-2 border-top border-1 border-opacity-50">
                                                <span class="badge rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1" id="inspectCardFcmBadge" style="font-size: 10px;">
                                                    <i class="fa-solid fa-bell"></i>
                                                    <span id="inspectCardFcmText">---</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Col 4: Device Location --}}
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-2.5 rounded-3 bg-light border border-1 border-opacity-50 h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="text-muted small" style="font-size: 11px;">{{ __('app_security.last_location') }}</span>
                                                    <span class="badge rounded-pill px-1.5 py-0.5" id="inspectCardLocationStatusBadge" style="font-size: 9.5px;">---</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="fw-semibold text-dark text-truncate" id="inspectCardLocationCoords" style="font-size: 0.85rem;">---</span>
                                                    <button type="button" class="btn btn-link btn-xs p-0 text-muted" id="inspectCardCopyCoordsBtn" title="{{ __('app_security.copy_coordinates') }}">
                                                        <i class="fa-regular fa-copy" style="font-size: 11px;"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted d-block mt-0.5" id="inspectCardLocationTime" style="font-size: 10.5px;">---</small>
                                                <small class="text-muted d-block" id="inspectCardLocationAccuracy" style="font-size: 10px;">---</small>
                                            </div>
                                            <div class="mt-2 pt-1" id="inspectCardMapLinkWrapper">
                                                <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-primary py-1 px-2.5 rounded-2 d-inline-flex align-items-center gap-1 shadow-sm w-100 justify-content-center" id="inspectCardMapBtn" style="font-size: 11px; font-weight: 600;">
                                                    <i class="fa-solid fa-location-dot"></i>
                                                    <span>{{ __('app_security.view_on_map') }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Active Sessions List Table Card --}}
                        <div class="card border border-slate-200 shadow-none" style="border-radius: 12px; background: #ffffff;">
                            <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 28px; height: 28px;">
                                        <i class="fa-solid fa-key"></i>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-bold text-dark small">{{ __('app_security.device_sessions') }}</span>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-2 py-0.5" id="inspectSessionsTotalBadge" style="font-size: 10.5px;">0</span>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1" id="inspectSessionsOnlineBadge" style="font-size: 10.5px;">
                                            <span class="eda-pulse-dot"></span> <span id="inspectSessionsOnlineCount">0</span> {{ __('app_security.online_now') }}
                                        </span>
                                        <span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-20 rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1" id="inspectSessionsOfflineBadge" style="font-size: 10.5px;">
                                            <span class="eda-pulse-dot"></span> <span id="inspectSessionsOfflineCount">0</span> {{ __('app_security.offline') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2.5 rounded-2 d-inline-flex align-items-center gap-1 shadow-none" id="inspectForceLogoutAllBtn" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                        <span>{{ __('app_security.revoke_all_sessions') }}</span>
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                    <thead class="bg-light text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.03em;">
                                        <tr>
                                            <th class="ps-3" style="width: 40px;">#</th>
                                            <th>{{ __('app_security.device_info') }} / Platform</th>
                                            <th class="text-center">{{ __('app_security.connection_status') }}</th>
                                            <th>{{ __('app_security.session_token') }}</th>
                                            <th>{{ __('app_security.last_location') }}</th>
                                            <th>{{ __('app_security.login_time') }} &amp; {{ __('app_security.last_seen') }}</th>
                                            <th class="text-center">{{ __('app_security.session_status') }}</th>
                                            <th class="text-end pe-3">{{ __('app_security.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inspectSessionsTableBody">
                                        {{-- Rendered via JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: HARDWARE ATTESTATION & CRYPTOGRAPHIC KEYSTORE --}}
                    <div class="tab-pane fade" id="inspect-tab-crypto" role="tabpanel">
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

                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-box"></i> {{ __('app_security.package_name') }}</div>
                                <div class="inspect-info-val font-monospace small" id="inspectCryptoPackage">—</div>
                            </div>

                            <div class="inspect-info-card">
                                <div class="inspect-info-label"><i class="fa-solid fa-code-branch"></i> {{ __('app_security.version_info') }}</div>
                                <div class="inspect-info-val" id="inspectCryptoVersion">—</div>
                            </div>

                            <div class="inspect-info-card" style="grid-column: 1 / -1;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="inspect-info-label mb-0"><i class="fa-solid fa-cube"></i> {{ __('app_security.installation_uuid') }}</div>
                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="copyModalField('inspectCryptoInstallUuid', this)">
                                        <i class="fa-regular fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                                <div class="inspect-info-val font-monospace text-info small" id="inspectCryptoInstallUuid">—</div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: RISK & THREAT TELEMETRY --}}
                    <div class="tab-pane fade" id="inspect-tab-risk" role="tabpanel">
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
                                <div class="text-muted small fst-italic py-2"><i class="fa-solid fa-circle-check text-success me-1"></i> No active threat signals or penalties recorded for this hardware profile.</div>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <a href="#" id="inspectRiskReviewLink" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                <i class="fa-solid fa-magnifying-glass-chart me-1"></i> {{ __('app_security.view_risk_review') }}
                            </a>
                        </div>
                    </div>

                    {{-- TAB 4: ACTIVITY LOGS (ATTENDANCE & GPS) --}}
                    <div class="tab-pane fade" id="inspect-tab-activities" role="tabpanel">
                        <div class="card border border-slate-200 shadow-none" style="border-radius: 12px; background: #ffffff;">
                            <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width: 28px; height: 28px;">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>
                                    <span class="fw-bold text-dark small">{{ __('app_security.activity_logs') }} (Attendance &amp; GPS Telemetry)</span>
                                </div>
                                <div class="btn-group btn-group-sm" role="group" id="inspectActivityFilterGroup">
                                    <button type="button" class="btn btn-outline-secondary active py-1 px-2.5" data-filter="all" style="font-size: 11px;">All</button>
                                    <button type="button" class="btn btn-outline-secondary py-1 px-2.5" data-filter="attendance" style="font-size: 11px;">Attendance</button>
                                    <button type="button" class="btn btn-outline-secondary py-1 px-2.5" data-filter="location" style="font-size: 11px;">GPS Location</button>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                    <thead class="bg-light text-muted text-uppercase sticky-top" style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.03em;">
                                        <tr>
                                            <th class="ps-3" style="width: 40px;">#</th>
                                            <th>Activity Type</th>
                                            <th>Source / Method</th>
                                            <th>Location / Coordinates</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inspectActivitiesTableBody">
                                        {{-- Rendered via JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 5: RAW TELEMETRY JSON --}}
                    <div class="tab-pane fade" id="inspect-tab-raw" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small text-muted fw-bold text-uppercase">
                                <i class="fa-solid fa-terminal me-1 text-primary"></i> Complete Hardware &amp; API Payload
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btnCopyInspectJson" onclick="copyJsonTelemetry(this)">
                                <i class="fa-regular fa-copy me-1"></i> {{ __('app_security.copy_json') }}
                            </button>
                        </div>
                        <pre class="inspect-code-block mb-0" id="inspectRawJsonBlock"><code>{}</code></pre>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer bg-light border-top py-2.5 px-4 d-flex align-items-center justify-content-between">
                <small class="text-muted" style="font-size: 11px;">
                    <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                    Sessions, hardware attestation, and device telemetry synchronize directly with KNEAYERNG security core and attendance services.
                </small>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    {{ __('app_security.close') }}
                </button>
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

    // Activity Logs state for inspect modal
    let activeInspectActivities = [];

    // Filter inspect activity logs
    function filterInspectActivities(filterType, btn) {
        if (btn) {
            document.querySelectorAll('#inspectActivityFilterGroup button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        const tbody = document.getElementById('inspectActivitiesTableBody');
        if (!tbody) return;

        let filtered = activeInspectActivities;
        if (filterType === 'attendance') {
            filtered = activeInspectActivities.filter(a => a.type === 'attendance');
        } else if (filterType === 'location') {
            filtered = activeInspectActivities.filter(a => a.type === 'location' || a.latitude);
        }

        if (!filtered || filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted small fst-italic">No activities found matching this filter.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map((act, index) => {
            const locText = (act.latitude && act.longitude)
                ? `<a href="https://www.google.com/maps?q=${act.latitude},${act.longitude}" target="_blank" rel="noopener noreferrer" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill text-decoration-none"><i class="fa-solid fa-location-dot me-1"></i>${Number(act.latitude).toFixed(4)}, ${Number(act.longitude).toFixed(4)}</a>`
                : `<span class="text-muted small fst-italic">—</span>`;

            return `
                <tr>
                    <td class="ps-3 text-muted">${index + 1}</td>
                    <td>
                        <span class="fw-semibold text-dark">${escapeHtml(act.title || act.type || 'Activity')}</span>
                    </td>
                    <td><span class="badge bg-light text-dark border">${escapeHtml(act.source || 'App')}</span></td>
                    <td>${locText}</td>
                    <td>
                        <div class="fw-semibold text-dark">${escapeHtml(act.created_at || '—')}</div>
                        <small class="text-muted">${escapeHtml(act.created_at_human || '')}</small>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
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

        // Header: Employee Avatar, Name, Code, Online status, Role/Dept/Branch, View Live Location Map Btn
        const emp = d.employee || {};
        const avatarEl = document.getElementById('inspectModalAvatar');
        if (avatarEl) avatarEl.src = emp.avatar || '/assets/images/img.png';

        const empNameEl = document.getElementById('inspectModalEmployeeName');
        if (empNameEl) empNameEl.textContent = emp.name || d.user_name || 'User';

        const empCodeEl = document.getElementById('inspectModalEmployeeCode');
        if (empCodeEl) empCodeEl.textContent = emp.employee_code || 'N/A';

        const isOnline = emp.is_online || d.conn_status === 'online';
        const onlineBadge = document.getElementById('inspectModalOnlineBadge');
        const onlineText = document.getElementById('inspectModalOnlineText');
        if (onlineBadge) {
            if (isOnline) {
                onlineBadge.className = 'badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 eda-status-online';
                if (onlineText) onlineText.textContent = 'Online Now';
            } else {
                onlineBadge.className = 'badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 eda-status-offline';
                if (onlineText) onlineText.textContent = 'Offline';
            }
        }

        const viewMapBtn = document.getElementById('inspectModalViewMapBtn');
        const loc = d.location || {};
        if (viewMapBtn) {
            if (loc.has_location && loc.map_url) {
                viewMapBtn.href = loc.map_url;
                viewMapBtn.classList.remove('disabled', 'opacity-50');
            } else {
                viewMapBtn.href = 'javascript:void(0)';
                viewMapBtn.classList.add('disabled', 'opacity-50');
            }
        }

        const roleDeptBranchEl = document.getElementById('inspectModalRoleDeptBranch');
        if (roleDeptBranchEl) {
            const parts = [emp.role || 'Staff', emp.department || '—', emp.branch || '—', emp.workspace_type || 'Office'].filter(Boolean);
            roleDeptBranchEl.textContent = parts.join(' • ');
        }

        // Subnav badges
        const sessionsList = d.sessions || [];
        const sessCountBadge = document.getElementById('inspectTabSessionCount');
        if (sessCountBadge) sessCountBadge.textContent = sessionsList.length;

        const score = parseInt(d.risk_score || 0);
        const riskTabBadge = document.getElementById('inspectTabRiskBadge');
        if (riskTabBadge) {
            riskTabBadge.textContent = score + '/100';
            riskTabBadge.className = 'badge rounded-pill ms-1 ' + (score >= 60 ? 'bg-danger' : (score >= 25 ? 'bg-warning text-dark' : 'bg-success'));
        }

        // Primary Device Card Header
        const devStatusBadge = document.getElementById('inspectDeviceStatusBadge');
        if (devStatusBadge) {
            devStatusBadge.className = 'device-status-badge status-' + (d.device_status || 'pending') + ' py-0 px-2';
            devStatusBadge.textContent = (d.device_status || 'pending').replace('_', ' ');
        }

        const devPlatformLink = document.getElementById('inspectDevicePlatformLink');
        if (devPlatformLink) {
            devPlatformLink.href = loc.map_url || '#';
        }

        // Primary Device Card - Col 1: Hardware & Battery
        const cardDevName = document.getElementById('inspectCardDeviceName');
        if (cardDevName) cardDevName.textContent = d.device_model || 'Android Device';

        const cardBatteryBadge = document.getElementById('inspectCardBatteryBadge');
        if (cardBatteryBadge) {
            if (d.battery_level !== null && d.battery_level !== undefined && d.battery_level !== '') {
                const bat = parseInt(d.battery_level);
                const bIcon = bat >= 80 ? 'fa-battery-full' : (bat >= 50 ? 'fa-battery-three-quarters' : (bat >= 20 ? 'fa-battery-quarter' : 'fa-battery-empty'));
                const bClass = bat >= 40 ? 'battery-good' : (bat >= 20 ? 'battery-warn' : 'battery-danger');
                cardBatteryBadge.innerHTML = `<span class="battery-pill ${bClass}" title="Battery level: ${bat}%"><i class="fa-solid ${bIcon}"></i> ${bat}%</span>`;
            } else {
                cardBatteryBadge.innerHTML = '';
            }
        }

        const cardDevModel = document.getElementById('inspectCardDeviceModel');
        if (cardDevModel) cardDevModel.textContent = `Android ${d.android_version || 'OS'} (SDK ${d.android_sdk || '?'})`;

        const cardDevUuid = document.getElementById('inspectCardDeviceUuid');
        if (cardDevUuid) cardDevUuid.textContent = d.uuid || '—';

        const copyUuidBtn = document.getElementById('inspectCardCopyUuidBtn');
        if (copyUuidBtn) {
            copyUuidBtn.onclick = function() { copyToClipboard(d.uuid, copyUuidBtn); };
        }

        // Primary Device Card - Col 2: Connection & Activity
        const cardConnBadge = document.getElementById('inspectCardConnectionBadge');
        if (cardConnBadge) {
            cardConnBadge.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 ${isOnline ? 'eda-status-online' : 'eda-status-offline'}" style="font-size: 11px;"><span class="eda-pulse-dot me-1"></span>${d.conn_label || (isOnline ? 'Online Now' : 'Offline')}</span>`;
        }

        const cardLoginTime = document.getElementById('inspectCardLoginTime');
        const cardLoginTimeHuman = document.getElementById('inspectCardLoginTimeHuman');
        if (cardLoginTime) cardLoginTime.textContent = emp.last_login_at || '—';
        if (cardLoginTimeHuman) cardLoginTimeHuman.textContent = emp.last_login_human ? '(' + emp.last_login_human + ')' : '';

        const cardLastSeenTime = document.getElementById('inspectCardLastSeenTime');
        const cardLastSeenHuman = document.getElementById('inspectCardLastSeenHuman');
        if (cardLastSeenTime) cardLastSeenTime.textContent = d.last_seen_at || 'Never';
        if (cardLastSeenHuman) cardLastSeenHuman.textContent = d.last_seen_diff ? '(' + d.last_seen_diff + ')' : '';

        // Primary Device Card - Col 3: App Information
        const cardAppName = document.getElementById('inspectCardAppName');
        if (cardAppName) cardAppName.textContent = d.package_name || 'KneayErng Mobile';

        const cardAppVersion = document.getElementById('inspectCardAppVersion');
        if (cardAppVersion) cardAppVersion.textContent = `v${d.version_name || '1.0.0'} (Build ${d.version_code || 1})`;

        const cardFcmBadge = document.getElementById('inspectCardFcmBadge');
        const cardFcmText = document.getElementById('inspectCardFcmText');
        if (cardFcmBadge && cardFcmText) {
            if (d.has_fcm) {
                cardFcmBadge.className = 'badge rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1 bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                cardFcmText.textContent = 'Push Notification (FCM Active)';
            } else {
                cardFcmBadge.className = 'badge rounded-pill px-2 py-0.5 d-inline-flex align-items-center gap-1 bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25';
                cardFcmText.textContent = 'No Push Token';
            }
        }

        // Primary Device Card - Col 4: Device Location
        const locStatusBadge = document.getElementById('inspectCardLocationStatusBadge');
        if (locStatusBadge) {
            if (loc.has_location) {
                locStatusBadge.className = 'badge rounded-pill px-2 py-0.5 ' + (isOnline ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-secondary bg-opacity-10 text-muted border');
                locStatusBadge.textContent = loc.status_text || (isOnline ? 'Live Location' : 'Last Offline Location');
            } else {
                locStatusBadge.className = 'badge bg-light text-muted border rounded-pill px-2 py-0.5';
                locStatusBadge.textContent = 'No GPS';
            }
        }

        const locCoords = document.getElementById('inspectCardLocationCoords');
        if (locCoords) {
            if (loc.has_location) {
                locCoords.textContent = `${Number(loc.latitude).toFixed(4)}, ${Number(loc.longitude).toFixed(4)}`;
            } else {
                locCoords.textContent = 'No GPS Coordinates';
            }
        }

        const copyCoordsBtn = document.getElementById('inspectCardCopyCoordsBtn');
        if (copyCoordsBtn) {
            if (loc.has_location) {
                copyCoordsBtn.classList.remove('d-none');
                copyCoordsBtn.onclick = function() { copyToClipboard(`${loc.latitude}, ${loc.longitude}`, copyCoordsBtn); };
            } else {
                copyCoordsBtn.classList.add('d-none');
            }
        }

        const locTime = document.getElementById('inspectCardLocationTime');
        if (locTime) locTime.textContent = loc.updated_at ? `${loc.updated_at} (${loc.updated_at_human})` : '—';

        const locAccuracy = document.getElementById('inspectCardLocationAccuracy');
        if (locAccuracy) locAccuracy.textContent = loc.accuracy ? `Accuracy: ±${loc.accuracy}m` : '';

        const mapBtn = document.getElementById('inspectCardMapBtn');
        if (mapBtn) {
            if (loc.has_location && loc.map_url) {
                mapBtn.href = loc.map_url;
                mapBtn.classList.remove('disabled', 'opacity-50');
            } else {
                mapBtn.href = 'javascript:void(0)';
                mapBtn.classList.add('disabled', 'opacity-50');
            }
        }

        // Active Sessions List Card
        const sessTotalBadge = document.getElementById('inspectSessionsTotalBadge');
        const sessOnlineCount = document.getElementById('inspectSessionsOnlineCount');
        const sessOfflineCount = document.getElementById('inspectSessionsOfflineCount');
        const sessTableBody = document.getElementById('inspectSessionsTableBody');

        let onlineSessCount = 0;
        let offlineSessCount = 0;

        sessionsList.forEach(s => {
            if (s.is_online) onlineSessCount++;
            else offlineSessCount++;
        });

        if (sessTotalBadge) sessTotalBadge.textContent = sessionsList.length;
        if (sessOnlineCount) sessOnlineCount.textContent = onlineSessCount;
        if (sessOfflineCount) sessOfflineCount.textContent = offlineSessCount;

        // Force Logout All Button
        const forceLogoutAllBtn = document.getElementById('inspectForceLogoutAllBtn');
        if (forceLogoutAllBtn) {
            if (sessionsList.length > 0 && d.revoke_all_url) {
                forceLogoutAllBtn.disabled = false;
                forceLogoutAllBtn.onclick = function() {
                    openStepUpModal('revoke-all-sessions', d.revoke_all_url, 'Revoke All Device Sessions', 'Immediately terminates ALL active connected sessions for ' + (d.device_model || 'this device') + '. The user will be required to re-authenticate.', d.device_model, emp.name || d.user_name, d.uuid);
                };
            } else {
                forceLogoutAllBtn.disabled = true;
                forceLogoutAllBtn.onclick = null;
            }
        }

        // Render Sessions Table Rows
        if (sessTableBody) {
            if (sessionsList.length === 0) {
                sessTableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted small fst-italic">No active or recorded sessions for this device.</td></tr>`;
            } else {
                sessTableBody.innerHTML = sessionsList.map((s, idx) => {
                    const sOnlineHtml = s.is_online
                        ? `<span class="badge rounded-pill px-2 py-0.5 eda-status-online" style="font-size: 10px;"><span class="eda-pulse-dot me-1"></span>Online</span>`
                        : `<span class="badge rounded-pill px-2 py-0.5 eda-status-offline" style="font-size: 10px;"><span class="eda-pulse-dot me-1"></span>Offline</span>`;

                    const sLocHtml = (s.latitude && s.longitude)
                        ? `<a href="${s.map_url || ('https://www.google.com/maps?q=' + s.latitude + ',' + s.longitude)}" target="_blank" rel="noopener noreferrer" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill text-decoration-none"><i class="fa-solid fa-location-dot me-1"></i>${Number(s.latitude).toFixed(3)}, ${Number(s.longitude).toFixed(3)}</a>`
                        : `<span class="text-muted small fst-italic">No GPS</span>`;

                    const sStatusBadge = s.is_active
                        ? `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Active</span>`
                        : (s.status_text === 'Revoked'
                            ? `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Revoked</span>`
                            : `<span class="badge bg-secondary bg-opacity-10 text-muted border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Expired</span>`);

                    const revokeActionBtn = (s.is_active && s.revoke_url)
                        ? `<button type="button" class="btn btn-outline-danger btn-xs py-0.5 px-2 rounded-2" onclick="openStepUpModal('revoke-session', '${s.revoke_url}', 'Revoke API Session Token', 'Revoke this specific active session token and force client token refresh.', '${d.device_model || 'Android Device'}', '${emp.name || d.user_name}', '${s.uuid || s.token_preview}')" title="Revoke Session"><i class="fa-solid fa-ban me-1"></i>Revoke</button>`
                        : `<span class="text-muted small fst-italic">—</span>`;

                    return `
                        <tr>
                            <td class="ps-3 text-muted">${idx + 1}</td>
                            <td>
                                <div class="fw-semibold text-dark">${escapeHtml(d.device_model || 'Android Device')}</div>
                                <small class="text-muted">Android ${escapeHtml(d.android_version || 'OS')}</small>
                            </td>
                            <td class="text-center">${sOnlineHtml}</td>
                            <td>
                                <span class="session-token-code" title="${escapeHtml(s.token_full || '')}">${escapeHtml(s.token_preview || 'Token')}</span>
                            </td>
                            <td>${sLocHtml}</td>
                            <td>
                                <div class="fw-semibold text-dark">${escapeHtml(s.created_at || '—')}</div>
                                <small class="text-muted">${escapeHtml(s.last_used_human ? 'Used: ' + s.last_used_human : '')}</small>
                            </td>
                            <td class="text-center">${sStatusBadge}</td>
                            <td class="text-end pe-3">${revokeActionBtn}</td>
                        </tr>
                    `;
                }).join('');
            }
        }

        // Tab 2: Crypto & Attestation
        const attLevel = (d.attestation_level || 'none').toLowerCase();
        const attChip = document.getElementById('inspectCryptoAttChip');
        const attDesc = document.getElementById('inspectCryptoAttDesc');
        if (attChip && attDesc) {
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
        }

        const cryptoFingerprint = document.getElementById('inspectCryptoKeyFingerprint');
        if (cryptoFingerprint) cryptoFingerprint.textContent = d.public_key_fingerprint || '—';

        const cryptoSigningSha = document.getElementById('inspectCryptoSigningSha');
        if (cryptoSigningSha) cryptoSigningSha.textContent = d.signing_sha256 || '—';

        const cryptoPackage = document.getElementById('inspectCryptoPackage');
        if (cryptoPackage) cryptoPackage.textContent = d.package_name || '—';

        const cryptoVersion = document.getElementById('inspectCryptoVersion');
        if (cryptoVersion) cryptoVersion.textContent = `${d.version_name || '1.0.0'} (Build ${d.version_code || 1})`;

        const cryptoInstallUuid = document.getElementById('inspectCryptoInstallUuid');
        if (cryptoInstallUuid) cryptoInstallUuid.textContent = d.installation_uuid || '—';

        // Tab 3: Risk & Threat
        const riskScoreText = document.getElementById('inspectRiskScoreText');
        if (riskScoreText) riskScoreText.textContent = score + '/100';

        const riskFill = document.getElementById('inspectRiskMeterFill');
        const riskBadge = document.getElementById('inspectRiskBadge');
        if (riskFill && riskBadge) {
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
        }

        const signalsList = document.getElementById('inspectRiskSignalsList');
        if (signalsList) {
            if (d.risk_signals && d.risk_signals.length > 0) {
                signalsList.innerHTML = d.risk_signals.map(s => `
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div class="fw-semibold text-danger">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> ${escapeHtml(s.signal || 'Signal')}
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill">+${s.points || 0} pts</span>
                    </div>
                `).join('');
            } else {
                signalsList.innerHTML = '<div class="text-muted small fst-italic py-2"><i class="fa-solid fa-circle-check text-success me-1"></i> No active threat signals or penalties recorded for this hardware profile.</div>';
            }
        }

        const riskReviewLink = document.getElementById('inspectRiskReviewLink');
        if (riskReviewLink) riskReviewLink.href = d.review_url || '#';

        // Tab 4: Activity Logs (Attendance & GPS Location Updates)
        activeInspectActivities = [];
        const activityCountBadge = document.getElementById('inspectTabActivityCount');
        const activitiesTbody = document.getElementById('inspectActivitiesTableBody');
        if (activitiesTbody) {
            activitiesTbody.innerHTML = `<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading employee activity telemetry...</td></tr>`;
        }

        if (emp.id) {
            fetch(`/admin/employees/device-activities/${emp.id}`)
                .then(r => r.json())
                .then(res => {
                    const acts = [];
                    if (res.attendanceActivities && Array.isArray(res.attendanceActivities)) {
                        res.attendanceActivities.forEach(a => acts.push({ ...a, type: 'attendance' }));
                    }
                    if (res.locationActivities && Array.isArray(res.locationActivities)) {
                        res.locationActivities.forEach(l => acts.push({ ...l, type: 'location' }));
                    }
                    if (res.attendances && Array.isArray(res.attendances)) {
                        res.attendances.forEach(at => acts.push({ ...at, type: 'attendance' }));
                    }
                    activeInspectActivities = acts;
                    if (activityCountBadge) activityCountBadge.textContent = acts.length;
                    filterInspectActivities('all');
                })
                .catch(err => {
                    console.warn('Failed loading activity telemetry', err);
                    if (activitiesTbody) {
                        activitiesTbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted small fst-italic">Activity logs synchronized via attendance service. (0 recorded)</td></tr>`;
                    }
                });
        } else {
            if (activityCountBadge) activityCountBadge.textContent = 0;
            if (activitiesTbody) {
                activitiesTbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted small fst-italic">No employee activity records available.</td></tr>`;
            }
        }

        // Setup activity filter buttons
        const filterGroup = document.getElementById('inspectActivityFilterGroup');
        if (filterGroup) {
            filterGroup.querySelectorAll('button').forEach(b => {
                b.onclick = function() {
                    filterInspectActivities(b.getAttribute('data-filter'), b);
                };
            });
        }

        // Tab 5: Raw JSON
        const rawJsonBlock = document.getElementById('inspectRawJsonBlock');
        if (rawJsonBlock) rawJsonBlock.textContent = activeInspectDeviceJson;

        // Reset to first tab
        const firstTab = document.getElementById('inspect-tab-devsess-btn');
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
        } else if (type === 'revoke-session') {
            iconBox.innerHTML = '<i class="fa-solid fa-ban"></i>';
            submitBtn.className = 'btn btn-danger rounded-pill px-4 shadow-sm';
            submitBtn.innerHTML = '<i class="fa-solid fa-ban me-1"></i> Revoke Token Session';
        } else if (type === 'revoke-all-sessions') {
            iconBox.innerHTML = '<i class="fa-solid fa-right-from-bracket"></i>';
            submitBtn.className = 'btn btn-danger rounded-pill px-4 shadow-sm';
            submitBtn.innerHTML = '<i class="fa-solid fa-right-from-bracket me-1"></i> Revoke All Sessions';
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
