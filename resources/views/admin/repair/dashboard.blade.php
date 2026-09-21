@extends('layouts.master')

@section('title', __('index.repair_management') ?? 'Repair Management')

@section('styles')
<style>
    :root {
        --rp-bg-card: #ffffff;
        --rp-border: #e2e8f0;
        --rp-border-hover: #cbd5e1;
        --rp-text-main: #0f172a;
        --rp-text-muted: #64748b;
        --rp-text-light: #94a3b8;
        --rp-badge-bg: #f1f5f9;
    }

    body.theme-dark {
        --rp-bg-card: #131d36;
        --rp-border: #1e293b;
        --rp-border-hover: #334155;
        --rp-text-main: #f1f5f9;
        --rp-text-muted: #94a3b8;
        --rp-text-light: #64748b;
        --rp-badge-bg: #1e293b;
    }

    .repair-dashboard {
        font-family: inherit;
    }

    .rp-header-bar {
        background: var(--rp-bg-card);
        border: 1px solid var(--rp-border);
        border-radius: 12px;
        padding: 18px 24px;
        margin-bottom: 24px;
    }

    .rp-header-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--rp-text-main);
        margin: 0;
    }

    .rp-header-subtitle {
        font-size: 0.875rem;
        color: var(--rp-text-muted);
        margin-top: 4px;
        margin-bottom: 0;
    }

    .rp-date-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--rp-badge-bg);
        border: 1px solid var(--rp-border);
        color: var(--rp-text-muted);
        font-size: 0.85rem;
        font-weight: 500;
        padding: 8px 14px;
        border-radius: 8px;
    }

    /* Sub-navigation pills */
    .rp-nav-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 24px;
    }

    .rp-nav-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        color: var(--rp-text-muted);
        background: var(--rp-bg-card);
        border: 1px solid var(--rp-border);
        transition: all 0.2s ease;
    }

    .rp-nav-pill:hover {
        color: #2563eb;
        border-color: #93c5fd;
        background: #eff6ff;
    }

    .rp-nav-pill.active {
        color: #ffffff;
        background: #2563eb;
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    body.theme-dark .rp-nav-pill:hover {
        background: #1e293b;
        color: #60a5fa;
        border-color: #3b82f6;
    }

    body.theme-dark .rp-nav-pill.active {
        background: #2563eb;
        color: #ffffff;
    }

    /* Stat Cards */
    .rp-stat-card {
        background: var(--rp-bg-card);
        border: 1px solid var(--rp-border);
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .rp-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        border-color: var(--rp-border-hover);
    }

    .rp-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .rp-icon-box svg {
        width: 22px;
        height: 22px;
    }

    /* Icon box color themes */
    .rp-icon-blue { background: #eff6ff; color: #2563eb; }
    .rp-icon-green { background: #f0fdf4; color: #16a34a; }
    .rp-icon-orange { background: #fff7ed; color: #ea580c; }
    .rp-icon-purple { background: #f5f3ff; color: #7c3aed; }
    .rp-icon-sky { background: #f0f9ff; color: #0284c7; }
    .rp-icon-rose { background: #fef2f2; color: #dc2626; }
    .rp-icon-teal { background: #f0fdfa; color: #0d9488; }
    .rp-icon-indigo { background: #eef2ff; color: #4338ca; }

    body.theme-dark .rp-icon-blue { background: rgba(37, 99, 235, 0.15); color: #60a5fa; }
    body.theme-dark .rp-icon-green { background: rgba(22, 163, 74, 0.15); color: #4ade80; }
    body.theme-dark .rp-icon-orange { background: rgba(234, 88, 12, 0.15); color: #fb923c; }
    body.theme-dark .rp-icon-purple { background: rgba(124, 58, 237, 0.15); color: #c084fc; }
    body.theme-dark .rp-icon-sky { background: rgba(2, 132, 199, 0.15); color: #38bdf8; }
    body.theme-dark .rp-icon-rose { background: rgba(220, 38, 38, 0.15); color: #f87171; }
    body.theme-dark .rp-icon-teal { background: rgba(13, 148, 136, 0.15); color: #2dd4bf; }
    body.theme-dark .rp-icon-indigo { background: rgba(67, 56, 202, 0.15); color: #818cf8; }

    .rp-stat-content {
        flex: 1;
        min-width: 0;
    }

    .rp-stat-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--rp-text-muted);
        text-transform: capitalize;
        margin-bottom: 2px;
    }

    .rp-stat-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: var(--rp-text-main);
        line-height: 1.2;
    }

    /* Content Cards */
    .rp-card {
        background: var(--rp-bg-card);
        border: 1px solid var(--rp-border);
        border-radius: 12px;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .rp-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--rp-border);
    }

    .rp-card-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--rp-text-main);
        margin: 0;
    }

    .rp-card-link {
        font-size: 0.825rem;
        font-weight: 500;
        color: #2563eb;
        text-decoration: none;
        transition: color 0.15s;
    }

    .rp-card-link:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .rp-card-body {
        padding: 16px 20px;
    }

    /* Price Table */
    .rp-table {
        width: 100%;
        margin-bottom: 0;
    }

    .rp-table th {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--rp-text-muted);
        padding: 10px 14px;
        border-bottom: 1px solid var(--rp-border);
        background: transparent;
    }

    .rp-table td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid var(--rp-border);
        color: var(--rp-text-main);
    }

    .rp-table tr:last-child td {
        border-bottom: none;
    }

    .rp-device-avatar {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: var(--rp-badge-bg);
        border: 1px solid var(--rp-border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--rp-text-muted);
        flex-shrink: 0;
        overflow: hidden;
    }

    .rp-device-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .rp-price-tag {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2563eb;
    }

    body.theme-dark .rp-price-tag {
        color: #60a5fa;
    }

    /* Popular Devices Progress Bars */
    .rp-device-row {
        margin-bottom: 16px;
    }

    .rp-device-row:last-child {
        margin-bottom: 0;
    }

    .rp-device-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .rp-device-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--rp-text-main);
    }

    .rp-device-percent {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--rp-text-muted);
    }

    .rp-progress {
        height: 8px;
        border-radius: 6px;
        background: var(--rp-badge-bg);
        overflow: hidden;
    }

    .rp-progress-bar {
        height: 100%;
        border-radius: 6px;
        transition: width 0.6s ease;
    }

    /* Low Stock List */
    .rp-stock-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--rp-border);
    }

    .rp-stock-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .rp-stock-item:first-child {
        padding-top: 0;
    }

    .rp-stock-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--rp-text-main);
    }

    .rp-stock-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 24px;
        padding: 0 8px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #dc2626;
        background: #fef2f2;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    body.theme-dark .rp-stock-badge {
        color: #f87171;
        background: rgba(220, 38, 38, 0.15);
        border-color: rgba(220, 38, 38, 0.3);
    }

    /* Timeline Activity */
    .rp-activity-item {
        display: flex;
        gap: 14px;
        padding: 10px 0;
        position: relative;
    }

    .rp-activity-dot-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 5px;
    }

    .rp-activity-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .rp-dot-success { background: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2); }
    .rp-dot-primary { background: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2); }
    .rp-dot-warning { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2); }
    .rp-dot-info { background: #0284c7; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2); }

    .rp-activity-content {
        flex: 1;
    }

    .rp-activity-text {
        font-size: 0.875rem;
        color: var(--rp-text-main);
        margin-bottom: 2px;
        line-height: 1.4;
    }

    .rp-activity-time {
        font-size: 0.775rem;
        color: var(--rp-text-muted);
    }
</style>
@endsection

@section('main-content')
<div class="repair-dashboard">
    @include('admin.section.flash_message')

    <!-- Header Section -->
    <div class="rp-header-bar d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">KNEAYERNG</span>
                <span class="text-muted small">Device Repair Management System</span>
            </div>
            <h1 class="rp-header-title">{{ __('index.dashboard') ?? 'Dashboard' }}</h1>
            <p class="rp-header-subtitle">{{ __('index.repair_dashboard_subtitle') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="rp-date-badge">
                <i data-feather="calendar" style="width: 15px; height: 15px;"></i>
                <span>{{ $todayFormatted }}</span>
            </div>
            <a href="{{ route('admin.repair-price.import') }}" class="btn btn-outline-primary btn-sm">
                <i data-feather="upload-cloud" class="me-1"></i> Import Excel
            </a>
            <a href="{{ route('admin.repair-price.index', ['active_tab' => 'prices']) }}" class="btn btn-primary btn-sm">
                <i data-feather="plus" class="me-1"></i> Add Repair Price
            </a>
        </div>
    </div>

    <!-- Sub-navigation Pills -->
    <div class="rp-nav-pills">
        <a href="{{ route('admin.repair.dashboard') }}" class="rp-nav-pill active">
            <i data-feather="grid" style="width: 16px; height: 16px;"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.repair-price.index', ['active_tab' => 'prices']) }}" class="rp-nav-pill">
            <i data-feather="dollar-sign" style="width: 16px; height: 16px;"></i>
            <span>{{ __('index.repair_price_matrix') }}</span>
        </a>
        <a href="{{ route('admin.repair-price.index', ['active_tab' => 'devices']) }}" class="rp-nav-pill">
            <i data-feather="smartphone" style="width: 16px; height: 16px;"></i>
            <span>{{ __('index.repair_devices') }}</span>
        </a>
        <a href="{{ route('admin.repair-price.index', ['active_tab' => 'services']) }}" class="rp-nav-pill">
            <i data-feather="tool" style="width: 16px; height: 16px;"></i>
            <span>{{ __('index.repair_services') }}</span>
        </a>
        <a href="{{ route('admin.repair-price.import') }}" class="rp-nav-pill">
            <i data-feather="upload-cloud" style="width: 16px; height: 16px;"></i>
            <span>Import Excel / CSV</span>
        </a>
    </div>

    <!-- 8 KPI Metrics Cards -->
    <div class="row">
        <!-- Card 1: Total Brands -->
        <div class="col-xl-3 col-md-6">
            <div class="rp-stat-card">
                <div class="rp-icon-box rp-icon-blue">
                    <i data-feather="tag"></i>
                </div>
                <div class="rp-stat-content">
                    <div class="rp-stat-label">{{ __('index.total_brands') }}</div>
                    <div class="rp-stat-value">{{ number_format($totalBrands) }}</div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Models -->
        <div class="col-xl-3 col-md-6">
            <div class="rp-stat-card">
                <div class="rp-icon-box rp-icon-green">
                    <i data-feather="smartphone"></i>
                </div>
                <div class="rp-stat-content">
                    <div class="rp-stat-label">{{ __('index.total_models') }}</div>
                    <div class="rp-stat-value">{{ number_format($totalModels) }}</div>
                </div>
            </div>
        </div>

        <!-- Card 3: Repair Services -->
        <div class="col-xl-3 col-md-6">
            <div class="rp-stat-card">
                <div class="rp-icon-box rp-icon-orange">
                    <i data-feather="tool"></i>
                </div>
                <div class="rp-stat-content">
                    <div class="rp-stat-label">{{ __('index.repair_services_count') }}</div>
                    <div class="rp-stat-value">{{ number_format($repairServices) }}</div>
                </div>
            </div>
        </div>

        <!-- Card 4: Parts -->
        <div class="col-xl-3 col-md-6">
            <div class="rp-stat-card">
                <div class="rp-icon-box rp-icon-purple">
                    <i data-feather="cpu"></i>
                </div>
                <div class="rp-stat-content">
                    <div class="rp-stat-label">Parts</div>
                    <div class="rp-stat-value">{{ number_format($totalParts) }}</div>
                </div>
            </div>
        </div>

        <!-- Card 5: Prices Updated Today -->
        <div class="col-xl-3 col-md-6">
            <div class="rp-stat-card">
                <div class="rp-icon-box rp-icon-sky">
                    <i data-feather="refresh-cw"></i>
                </div>
                <div class="rp-stat-content">
                    <div class="rp-stat-label">{{ __('index.prices_updated_today') }}</div>
                    <div class="rp-stat-value">{{ number_format($pricesUpdatedToday) }}</div>
                </div>
            </div>
        </div>

        <!-- Card 6: Out of Stock -->
        <div class="col-xl-3 col-md-6">
            <div class="rp-stat-card">
                <div class="rp-icon-box rp-icon-rose">
                    <i data-feather="alert-triangle"></i>
                </div>
                <div class="rp-stat-content">
                    <div class="rp-stat-label">{{ __('index.out_of_stock_count') }}</div>
                    <div class="rp-stat-value">{{ number_format($outOfStock) }}</div>
                </div>
            </div>
        </div>

        <!-- Card 7: Active Devices -->
        <div class="col-xl-3 col-md-6">
            <div class="rp-stat-card">
                <div class="rp-icon-box rp-icon-teal">
                    <i data-feather="check-circle"></i>
                </div>
                <div class="rp-stat-content">
                    <div class="rp-stat-label">{{ __('index.active_devices') }}</div>
                    <div class="rp-stat-value">{{ number_format($activeDevices) }}</div>
                </div>
            </div>
        </div>

        <!-- Card 8: Total Categories -->
        <div class="col-xl-3 col-md-6">
            <div class="rp-stat-card">
                <div class="rp-icon-box rp-icon-indigo">
                    <i data-feather="layers"></i>
                </div>
                <div class="rp-stat-content">
                    <div class="rp-stat-label">{{ __('index.total_categories') }}</div>
                    <div class="rp-stat-value">{{ number_format($totalCategories) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Panels -->
    <div class="row">
        <!-- Left Column: Recently Updated Prices & Low Stock Parts -->
        <div class="col-lg-7">
            <!-- Recently Updated Prices -->
            <div class="rp-card">
                <div class="rp-card-header">
                    <h3 class="rp-card-title">{{ __('index.recently_updated_prices') }}</h3>
                    <a href="javascript:void(0)" class="rp-card-link">{{ __('index.view_all') }}</a>
                </div>
                <div class="rp-card-body p-0">
                    <div class="table-responsive">
                        <table class="table rp-table align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('index.device_model') }} / {{ __('index.repair_service') }}</th>
                                    <th>{{ __('index.selling_price') }}</th>
                                    <th>{{ __('index.time') ?? 'Time' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentlyUpdatedPrices as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rp-device-avatar">
                                                    @if(!empty($item['image']))
                                                        <img src="{{ $item['image'] }}" alt="{{ $item['device'] }}">
                                                    @else
                                                        <i data-feather="smartphone" style="width: 18px; height: 18px;"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-truncate" style="max-width: 260px;">{{ $item['device'] }}</div>
                                                    <div class="text-muted small">{{ $item['service'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="rp-price-tag">{{ $item['price'] }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $item['time'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No prices updated recently.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Low Stock Parts -->
            <div class="rp-card">
                <div class="rp-card-header">
                    <h3 class="rp-card-title">{{ __('index.low_stock_parts') }}</h3>
                    <a href="javascript:void(0)" class="rp-card-link">{{ __('index.view_all') }}</a>
                </div>
                <div class="rp-card-body">
                    @foreach($lowStockParts as $part)
                        <div class="rp-stock-item">
                            <span class="rp-stock-name">{{ $part['name'] }}</span>
                            <span class="rp-stock-badge">{{ $part['stock'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Popular Devices & Recent Activity -->
        <div class="col-lg-5">
            <!-- Popular Devices -->
            <div class="rp-card">
                <div class="rp-card-header">
                    <h3 class="rp-card-title">{{ __('index.popular_devices') }}</h3>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            This Month
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="javascript:void(0)">{{ __('index.this_month') }}</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)">{{ __('index.last_month') }}</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)">{{ __('index.this_year') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="rp-card-body">
                    @foreach($popularDevices as $device)
                        <div class="rp-device-row">
                            <div class="rp-device-info">
                                <span class="rp-device-name">{{ $device['name'] }}</span>
                                <span class="rp-device-percent">{{ $device['percentage'] }}%</span>
                            </div>
                            <div class="rp-progress">
                                <div class="rp-progress-bar" style="width: {{ $device['percentage'] }}%; background-color: {{ $device['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="rp-card">
                <div class="rp-card-header">
                    <h3 class="rp-card-title">{{ __('index.recent_activity') }}</h3>
                    <a href="javascript:void(0)" class="rp-card-link">{{ __('index.view_all') }}</a>
                </div>
                <div class="rp-card-body">
                    @foreach($recentActivity as $act)
                        <div class="rp-activity-item">
                            <div class="rp-activity-dot-col">
                                <span class="rp-activity-dot rp-dot-{{ $act['type'] }}"></span>
                            </div>
                            <div class="rp-activity-content">
                                <div class="rp-activity-text">{{ $act['text'] }}</div>
                                <div class="rp-activity-time">{{ $act['time'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>
@endsection
