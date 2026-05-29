@php use App\Helpers\AttendanceHelper; @endphp
@php use App\Models\Client; @endphp
@php use App\Models\User; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title',__('index.digital_hr_dashboard'))

<?php
$attendanceDetail = (AppHelper::employeeTodayAttendanceDetail());

$multipleEntries = count($attendanceDetail);
$firstAttendance = $attendanceDetail->first();
$lastAttendance = $attendanceDetail->last();

$checkInAt = $firstAttendance['check_in_at'] ?? '';
$checkOutAt = $lastAttendance['check_out_at'] ?? '';
$attendanceDate = $lastAttendance['attendance_date'] ?? '';
$viewCheckIn = $checkInAt ? AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $checkInAt) : '-:-:-';
$viewCheckOut = $checkOutAt ? AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $checkOutAt) : '-:-:-';
$currentMonthLabel = now()->format('M Y');
?>

@section('nav-head',__('index.welcome').' : ' .ucfirst($dashboardDetail?->company_name) )

@section('styles')
    <style>
        :root {
            --summary-ink: #172033;
            --summary-muted: #6b7280;
            --summary-line: #d9e2ef;
            --summary-card-top: #fdfefe;
            --summary-card-bottom: #f3f8ff;
            --summary-head-top: #163a5f;
            --summary-head-bottom: #285f93;
            --summary-accent: #ef4444;
            --summary-total-top: #fff4d8;
            --summary-total-bottom: #ffe7ad;
        }

        #clockContainer {
            background: url({{asset('assets/images/clock.png') }}) no-repeat;
            background-size: 100%;
        }

        .alert {
            display: flex;
            align-items: center;
        }

        .scrolling-message {
            display: inline-block;
            white-space: nowrap;
            position: absolute;
            animation: scroll-left 10s linear infinite;
        }

        @keyframes scroll-left {
            0% {
                transform: translateX(100%);
            }
            100% {
                transform: translateX(-100%);
            }
        }

        .summary-panel {
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 18px;
            overflow: hidden;
            background: linear-gradient(180deg, var(--summary-card-top) 0%, var(--summary-card-bottom) 100%);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.10);
        }

        .summary-panel .card-header {
            border-bottom: 1px solid rgba(219, 234, 254, 0.28);
            background:
                linear-gradient(90deg, rgba(255, 255, 255, 0.16), transparent 42%),
                linear-gradient(135deg, var(--summary-head-top) 0%, var(--summary-head-bottom) 100%);
            padding: 1.25rem 1.6rem;
        }

        .summary-panel .card-body {
            padding: 1.35rem 1.5rem 1.5rem;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        }

        .summary-panel-title {
            color: #fff;
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: 0;
            margin: 0;
        }

        .summary-panel-subtitle {
            margin-top: 0.35rem;
            color: rgba(255, 255, 255, 0.86);
            font-size: 0.92rem;
        }

        .summary-table-shell {
            border: 1px solid #dbe7f4;
            border-radius: 16px;
            overflow: auto;
            background: #ffffff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
            scrollbar-color: #8aa0b8 #eef4fb;
            scrollbar-width: thin;
        }

        .summary-table-shell::-webkit-scrollbar {
            height: 10px;
            width: 10px;
        }

        .summary-table-shell::-webkit-scrollbar-track {
            background: #eef4fb;
            border-radius: 999px;
        }

        .summary-table-shell::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #94a3b8, #64748b);
            border: 2px solid #eef4fb;
            border-radius: 999px;
        }

        .branch-summary-table {
            margin-bottom: 0;
            min-width: 1500px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .branch-summary-table th,
        .branch-summary-table td {
            white-space: nowrap;
            vertical-align: middle;
            border-color: #dbe7f4;
            padding: 0.82rem 0.95rem;
        }

        .branch-summary-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: linear-gradient(180deg, #f8fbff 0%, #edf5ff 100%);
            color: var(--summary-ink);
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0;
            box-shadow: inset 0 -1px 0 #d9e6f5;
        }

        .branch-summary-table tbody tr:nth-child(even) td {
            background: #f6f9fd;
        }

        .branch-summary-table tbody tr:hover td {
            background: #fff8ea;
            transition: background-color 0.2s ease;
        }

        .branch-summary-table th:first-child,
        .branch-summary-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 2;
            background-clip: padding-box;
            box-shadow: 1px 0 0 #dbe7f4, 10px 0 18px rgba(15, 23, 42, 0.04);
        }

        .branch-summary-table thead th:first-child {
            z-index: 4;
            border-top-left-radius: 14px;
        }

        .branch-summary-table tbody td:first-child {
            background: #ffffff;
            font-weight: 700;
        }

        .branch-summary-table tbody tr:nth-child(even) td:first-child {
            background: #f6f9fd;
        }

        .branch-summary-table tbody tr:hover td:first-child {
            background: #fff8ea;
        }

        .summary-name-trigger {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            font-weight: 700;
            color: var(--summary-ink);
        }

        .summary-name-trigger::before {
            content: "";
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%);
            box-shadow: 0 0 0 5px rgba(249, 115, 22, 0.14);
            flex: 0 0 auto;
        }

        .summary-value-trigger {
            --metric-accent: #2563eb;
            --metric-bg: #eff6ff;
            --metric-border: #dbeafe;
            min-width: 48px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 0.25rem 0.74rem;
            border-radius: 999px;
            background: var(--metric-bg);
            color: var(--summary-ink);
            font-weight: 800;
            line-height: 1.1;
            box-shadow: inset 0 0 0 1px var(--metric-border);
            transition: transform 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease, color 0.16s ease;
        }

        .summary-value-trigger:hover,
        .summary-value-trigger:focus-visible {
            background: #ffffff;
            color: var(--metric-accent);
            box-shadow: inset 0 0 0 1px var(--metric-border), 0 8px 18px rgba(15, 23, 42, 0.12);
            transform: translateY(-1px);
            outline: 0;
        }

        .summary-metric-heading {
            position: relative;
        }

        .summary-metric-heading::after {
            content: "";
            position: absolute;
            left: 18%;
            right: 18%;
            bottom: 0;
            height: 3px;
            border-radius: 999px 999px 0 0;
            background: var(--metric-accent, #2563eb);
            opacity: 0.75;
        }

        .summary-metric-total_all_employee,
        .summary-metric-active_employee {
            --metric-accent: #0ea5e9;
            --metric-bg: #ecfeff;
            --metric-border: #bae6fd;
        }

        .summary-metric-inactive_employee {
            --metric-accent: #64748b;
            --metric-bg: #f1f5f9;
            --metric-border: #d9e2ec;
        }

        .summary-metric-active_employee_checkin,
        .summary-metric-active_employee_checkout {
            --metric-accent: #16a34a;
            --metric-bg: #f0fdf4;
            --metric-border: #bbf7d0;
        }

        .summary-metric-active_employee_not_yet_checkin,
        .summary-metric-active_employee_not_yet_checkout {
            --metric-accent: #f97316;
            --metric-bg: #fff7ed;
            --metric-border: #fed7aa;
        }

        .summary-metric-active_employee_dayoff,
        .summary-metric-active_employee_leave {
            --metric-accent: #8b5cf6;
            --metric-bg: #f5f3ff;
            --metric-border: #ddd6fe;
        }

        .summary-metric-active_employee_pending_request,
        .summary-metric-active_employee_time_leave_request {
            --metric-accent: #dc2626;
            --metric-bg: #fef2f2;
            --metric-border: #fecaca;
        }

        .summary-metric-active_employee_time_leave {
            --metric-accent: #0891b2;
            --metric-bg: #ecfeff;
            --metric-border: #a5f3fc;
        }

        .branch-summary-table tfoot th,
        .branch-summary-table tfoot td {
            font-weight: 800;
            background: linear-gradient(180deg, #fff9e8 0%, #ffeab8 100%);
            color: var(--summary-ink);
            position: sticky;
            bottom: 0;
            z-index: 2;
            border-top: 1px solid #f3c86b;
        }

        .branch-summary-table tfoot th:first-child {
            z-index: 3;
            box-shadow: 1px 0 0 #f3c86b, 10px 0 18px rgba(15, 23, 42, 0.05);
        }

        .branch-summary-table tfoot .summary-value-trigger {
            background: rgba(255, 255, 255, 0.48);
            box-shadow: inset 0 0 0 1px rgba(202, 138, 4, 0.20);
        }

        .summary-trigger {
            background: none;
            border: 0;
            padding: 0;
            color: inherit;
            font: inherit;
            text-decoration: none;
        }

        .summary-trigger:hover {
            color: inherit;
        }

        .summary-modal-table thead th {
            background: #eef5ff;
            color: var(--summary-ink);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 800;
        }

        .summary-modal-table tbody tr:hover td {
            background: #fff7e6;
        }

        .summary-quick-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .summary-quick-actions .btn {
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.32rem 0.82rem;
        }

        .dashboard-kpi-card {
            width: 100%;
            min-height: 138px;
            padding: 0;
            border: 1px solid #dbe7f4;
            border-radius: 8px;
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
            color: #172033;
            cursor: pointer;
            text-align: left;
            appearance: none;
            transition: transform 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease;
        }

        .dashboard-kpi-card:hover,
        .dashboard-kpi-card:focus-visible {
            border-color: var(--kpi-accent, #2563eb);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.14);
            transform: translateY(-2px);
            outline: 0;
        }

        .dashboard-kpi-card .card-body {
            padding: 1.15rem 1.2rem;
        }

        .dashboard-kpi-eyebrow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 18px;
        }

        .dashboard-kpi-title {
            margin: 0;
            color: #334155;
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.25;
        }

        .dashboard-kpi-period {
            flex: 0 0 auto;
            padding: 4px 8px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--kpi-accent, #2563eb) 10%, #ffffff);
            color: var(--kpi-accent, #2563eb);
            font-size: 0.72rem;
            font-weight: 800;
        }

        .dashboard-kpi-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .dashboard-kpi-value {
            margin: 0;
            color: #0f172a;
            font-size: 2rem;
            line-height: 1;
            font-weight: 850;
            font-variant-numeric: tabular-nums;
        }

        .dashboard-kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: color-mix(in srgb, var(--kpi-accent, #2563eb) 12%, #ffffff);
            color: var(--kpi-accent, #2563eb);
        }

        .dashboard-kpi-icon svg {
            width: 23px;
            height: 23px;
            stroke-width: 2.4;
        }

        .dashboard-kpi-hint {
            margin-top: 12px;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .dashboard-kpi-total {
            --kpi-accent: #2563eb;
        }

        .dashboard-kpi-inactive {
            --kpi-accent: #64748b;
        }

        .dashboard-kpi-active {
            --kpi-accent: #16a34a;
        }

        .dashboard-kpi-leave {
            --kpi-accent: #7c3aed;
        }

        .dashboard-kpi-time-leave {
            --kpi-accent: #0891b2;
        }

        @media (max-width: 991.98px) {
            .summary-panel .card-body {
                padding: 1rem;
            }

            .summary-panel-title {
                font-size: 1.3rem;
            }
        }
    </style>
@endsection

@section('main-content')

    <section class="content">
        <?php
        $projectPriority = [
            'low' => 'info',
            'medium' => 'warning',
            'high' => 'primary',
            'urgent' => 'primary'
        ];
        ?>

        <div id="flashAttendanceMessage" class="d-none">
            <div class="alert alert-danger errorStartWorking">
                <p class="errorStartWorkingMessage"></p>
            </div>

            <div class="alert alert-danger errorStopWorking">
                <p class="errorStopWorkingMessage"></p>
            </div>

            <div class="alert alert-success successStartWorking">
                <p class="successStartWorkingMessage"></p>
            </div>

            <div class="alert alert-success successStopWorking">
                <p class="successStopWorkingMessage"></p>
            </div>
        </div>

        <div id="loader" style="display:none;">
            <div class="loading">
                <div class="loading-content"></div>
            </div>
        </div>

        @php
            $dashboardCardBranchIds = $branchDashboardSummaries->pluck('id')->filter()->implode(',');
        @endphp

        <div class="row">
            @can('attendance_summary')
                <div class=" {{ isset(auth()->user()->id) ? 'col-xxl-9 col-xl-8': 'col-xxl-12 col-xl-12'  }} d-flex">
                    <div class="row">
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_departments') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-fle">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_departments)}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="layers"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4 d-flex">
                            <button type="button"
                                    class="card dashboard-kpi-card dashboard-kpi-total summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="total_all_employee"
                                    data-entity-name="Total Employee"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div class="card-body text-md-start text-center">
                                    <div class="dashboard-kpi-eyebrow">
                                        <h6 class="dashboard-kpi-title">Total Employee</h6>
                                        <span class="dashboard-kpi-period">{{ $currentMonthLabel }}</span>
                                    </div>

                                    <div class="dashboard-kpi-main">
                                        <h3 class="dashboard-kpi-value">{{ number_format($dashboardDetail?->total_employee ?? 0) }}</h3>
                                        <div class="dashboard-kpi-icon">
                                            <i class="link-icon" data-feather="users"></i>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-hint">Click to view employee detail</div>
                                </div>
                            </button>
                        </div>

                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4 d-flex">
                            <button type="button"
                                    class="card dashboard-kpi-card dashboard-kpi-inactive summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="inactive_employee"
                                    data-entity-name="Inactive Employee"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div class="card-body text-md-start text-center">
                                    <div class="dashboard-kpi-eyebrow">
                                        <h6 class="dashboard-kpi-title">Inactive Employee</h6>
                                        <span class="dashboard-kpi-period">{{ $currentMonthLabel }}</span>
                                    </div>
                                    <div class="dashboard-kpi-main">
                                        <h3 class="dashboard-kpi-value">{{ number_format($dashboardDetail?->inactive_employee ?? 0) }}</h3>
                                        <div class="dashboard-kpi-icon">
                                            <i class="link-icon" data-feather="user-x"></i>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-hint">Click to view inactive staff</div>
                                </div>
                            </button>
                        </div>

                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4 d-flex">
                            <button type="button"
                                    class="card dashboard-kpi-card dashboard-kpi-active summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="active_employee"
                                    data-entity-name="Active Employee"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div class="card-body text-md-start text-center">
                                    <div class="dashboard-kpi-eyebrow">
                                        <h6 class="dashboard-kpi-title">Active Employee</h6>
                                        <span class="dashboard-kpi-period">{{ $currentMonthLabel }}</span>
                                    </div>
                                    <div class="dashboard-kpi-main">
                                        <h3 class="dashboard-kpi-value">{{ number_format($dashboardDetail?->active_employee ?? 0) }}</h3>
                                        <div class="dashboard-kpi-icon">
                                            <i class="link-icon" data-feather="user-check"></i>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-hint">Click to view active staff</div>
                                </div>
                            </button>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_holidays') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_holidays) ?? 0}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="umbrella"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.paid_leaves') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_paid_leaves) ?? 0}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="file-text"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.on_leave_today') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_on_leave) ?? 0}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="file-minus"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <button type="button"
                                    class="card dashboard-kpi-card dashboard-kpi-leave summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="current_month_leave_request"
                                    data-entity-name="Leave Request"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div class="card-body text-md-start text-center">
                                    <div class="dashboard-kpi-eyebrow">
                                        <h6 class="dashboard-kpi-title">Leave Request</h6>
                                        <span class="dashboard-kpi-period">{{ $currentMonthLabel }}</span>
                                    </div>
                                    <div class="dashboard-kpi-main">
                                        <h3 class="dashboard-kpi-value">{{ number_format($dashboardDetail?->current_month_leave_requests ?? 0) }}</h3>
                                        <div class="dashboard-kpi-icon">
                                            <i class="link-icon" data-feather="file-text"></i>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-hint">Click to view monthly requests</div>
                                </div>
                            </button>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <button type="button"
                                    class="card dashboard-kpi-card dashboard-kpi-time-leave summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="current_month_time_leave_request"
                                    data-entity-name="Time Leave Request"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div class="card-body text-md-start text-center">
                                    <div class="dashboard-kpi-eyebrow">
                                        <h6 class="dashboard-kpi-title">Time Leave Request</h6>
                                        <span class="dashboard-kpi-period">{{ $currentMonthLabel }}</span>
                                    </div>
                                    <div class="dashboard-kpi-main">
                                        <h3 class="dashboard-kpi-value">{{ number_format($dashboardDetail?->current_month_time_leave_requests ?? 0) }}</h3>
                                        <div class="dashboard-kpi-icon">
                                            <i class="link-icon" data-feather="clock"></i>
                                        </div>
                                    </div>
                                    <div class="dashboard-kpi-hint">Click to view monthly time leave</div>
                                </div>
                            </button>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_check_in_today') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_checked_in_employee) ?? 0 }}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="log-in"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_check_out_today') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_checked_out_employee) ?? 0 }}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="log-out"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endcan
            @if(auth()->user())
                @can('allow_attendance')
                    <div class="col-xxl-3 col-xl-4 mb-4 d-flex">
                        <div class="card w-100">
                            <div class="card-body text-center clock-display">
                                <div id="clockContainer" class="mb-2">
                                    <div id="hour"></div>
                                    <div id="minute"></div>
                                    <div id="second"></div>
                                </div>

                                <p id="date"
                                   class="text-primary fw-bolder mb-2"> {{ AppHelper::getCurrentDate() }}</p>

                                <div class="punch-btn mb-2 d-flex align-items-center justify-content-around">
                                    @if($multipleAttendance > 1)
                                        @if($multipleEntries < $multipleAttendance || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at))

                                            @if((!isset($firstAttendance->check_in_at) && !isset($firstAttendance->check_out_at)) || ($lastAttendance->check_in_at && $lastAttendance->check_out_at))
                                                <button href="{{route('admin.dashboard.takeAttendance','checkIn')}}"
                                                        class="btn btn-lg btn-danger "
                                                        id="startWorkingBtn"
                                                        data-audio="{{asset('assets/audio/beep.mp3')}}"
                                                >
                                                    {{ __('index.punch_in') }}
                                                </button>

                                            @elseif(($firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendancess->check_in_at && !$lastAttendance->check_out_at))
                                                <button href="{{route('admin.dashboard.takeAttendance','checkOut')}}"
                                                        class="btn btn-lg btn-danger"
                                                        id="stopWorkingBtn"
                                                        data-audio="{{asset('assets/audio/beep.mp3')}}"
                                                >
                                                    {{ __('index.punch_out') }}
                                                </button>
                                            @endif
                                        @endif
                                    @else
                                        <button href="{{route('admin.dashboard.takeAttendance','checkIn')}}"
                                                class="btn btn-lg btn-danger  {{ $checkInAt ? 'd-none' : ''}}"
                                                id="startWorkingBtn" data-audio="{{asset('assets/audio/beep.mp3')}}"
                                        >
                                            {{ __('index.punch_in') }}
                                        </button>
                                        <button href="{{route('admin.dashboard.takeAttendance','checkOut')}}"
                                                class="btn btn-lg btn-danger {{ $checkOutAt ? 'd-none' : ''}}"
                                                id="stopWorkingBtn" data-audio="{{asset('assets/audio/beep.mp3')}}"
                                        >
                                            {{ __('index.punch_out') }}
                                        </button>
                                    @endif
                                </div>

                                <div class="check-text d-flex align-items-center justify-content-around">
                                    <span>{{ __('index.check_in_at') }}<p class="text-success fw-bold h5"
                                                                          id="checkInTime">{{$viewCheckIn}} </p></span>
                                    <span>{{ __('index.check_out_at') }}<p class="text-danger fw-bold h5"
                                                                           id="checkOutTime">{{$viewCheckOut}}  </p></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
            @endif
        </div>
        @can('attendance_summary')
            @php
                $summaryMetrics = [
                    'total_all_employee' => 'All Staff',
                    'inactive_employee' => 'Inactive Employee',
                    'active_employee' => 'Active',
                    'active_employee_checkin' => 'Checked In',
                    'active_employee_not_yet_checkin' => 'No Check-In',
                    'active_employee_checkout' => 'Checked Out',
                    'active_employee_not_yet_checkout' => 'No Check-Out',
                    'active_employee_dayoff' => 'Day Off',
                    'active_employee_leave' => 'Leave',
                    'active_employee_pending_request' => 'Pending Leave Requests',
                    'active_employee_time_leave' => __('index.time_leave'),
                    'active_employee_time_leave_request' => __('index.time_leave_request'),
                ];
                $branchSummaryTotals = [
                    'total_all_employee' => $branchDashboardSummaries->sum('total_all_employee'),
                    'inactive_employee' => $branchDashboardSummaries->sum('inactive_employee'),
                    'active_employee' => $branchDashboardSummaries->sum('active_employee'),
                    'active_employee_checkin' => $branchDashboardSummaries->sum('active_employee_checkin'),
                    'active_employee_not_yet_checkin' => $branchDashboardSummaries->sum('active_employee_not_yet_checkin'),
                    'active_employee_checkout' => $branchDashboardSummaries->sum('active_employee_checkout'),
                    'active_employee_not_yet_checkout' => $branchDashboardSummaries->sum('active_employee_not_yet_checkout'),
                    'active_employee_dayoff' => $branchDashboardSummaries->sum('active_employee_dayoff'),
                    'active_employee_leave' => $branchDashboardSummaries->sum('active_employee_leave'),
                    'active_employee_pending_request' => $branchDashboardSummaries->sum('active_employee_pending_request'),
                    'active_employee_time_leave' => $branchDashboardSummaries->sum('active_employee_time_leave'),
                    'active_employee_time_leave_request' => $branchDashboardSummaries->sum('active_employee_time_leave_request'),
                ];
                $branchSummaryAllIds = $branchDashboardSummaries->pluck('id')->filter()->implode(',');
            @endphp
            <div class="card mb-4 summary-panel">
                <div class="card-header">
                    <h4 class="summary-panel-title">Branch Summary</h4>
                    <p class="summary-panel-subtitle">Quick branch-by-branch staffing and attendance snapshot.</p>
                </div>
                <div class="card-body">
                    <div class="summary-table-shell">
                        <table class="table table-striped table-bordered branch-summary-table mb-0">
                            <thead>
                            <tr>
                                <th>Branch</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <th class="text-center summary-metric-heading summary-metric-{{ $metricKey }}">{{ $metricLabel }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($branchDashboardSummaries as $branchSummary)
                                <tr>
                                    <td>
                                        <button type="button"
                                                class="summary-trigger summary-name-trigger"
                                                data-summary-scope="branch"
                                                data-summary-metric="total_all_employee"
                                                data-entity-name="{{ ucfirst($branchSummary->name) }}"
                                                data-entity-ids="{{ $branchSummary->id }}">
                                            {{ ucfirst($branchSummary->name) }}
                                        </button>
                                    </td>
                                    @foreach($summaryMetrics as $metricKey => $metricLabel)
                                        <td class="text-center">
                                            <button type="button"
                                                    class="summary-trigger summary-value-trigger summary-metric-{{ $metricKey }}"
                                                    data-summary-scope="branch"
                                                    data-summary-metric="{{ $metricKey }}"
                                                    data-entity-name="{{ ucfirst($branchSummary->name) }}"
                                                    data-entity-ids="{{ $branchSummary->id }}">
                                                {{ number_format($branchSummary->{$metricKey}) }}
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center"><b>{{ __('index.no_records_found') }}</b></td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Total</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <td class="text-center">
                                        <button type="button"
                                                class="summary-trigger summary-value-trigger summary-metric-{{ $metricKey }}"
                                                data-summary-scope="branch"
                                                data-summary-metric="{{ $metricKey }}"
                                                data-entity-name="All Branches"
                                                data-entity-ids="{{ $branchSummaryAllIds }}">
                                            {{ number_format($branchSummaryTotals[$metricKey]) }}
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @php
                $departmentSummaryTotals = [
                    'total_all_employee' => $departmentDashboardSummaries->sum('total_all_employee'),
                    'inactive_employee' => $departmentDashboardSummaries->sum('inactive_employee'),
                    'active_employee' => $departmentDashboardSummaries->sum('active_employee'),
                    'active_employee_checkin' => $departmentDashboardSummaries->sum('active_employee_checkin'),
                    'active_employee_not_yet_checkin' => $departmentDashboardSummaries->sum('active_employee_not_yet_checkin'),
                    'active_employee_checkout' => $departmentDashboardSummaries->sum('active_employee_checkout'),
                    'active_employee_not_yet_checkout' => $departmentDashboardSummaries->sum('active_employee_not_yet_checkout'),
                    'active_employee_dayoff' => $departmentDashboardSummaries->sum('active_employee_dayoff'),
                    'active_employee_leave' => $departmentDashboardSummaries->sum('active_employee_leave'),
                    'active_employee_pending_request' => $departmentDashboardSummaries->sum('active_employee_pending_request'),
                    'active_employee_time_leave' => $departmentDashboardSummaries->sum('active_employee_time_leave'),
                    'active_employee_time_leave_request' => $departmentDashboardSummaries->sum('active_employee_time_leave_request'),
                ];
                $departmentSummaryAllIds = $departmentDashboardSummaries->pluck('department_ids')->flatten()->filter()->unique()->implode(',');
            @endphp
            <div class="card mb-4 summary-panel">
                <div class="card-header">
                    <h4 class="summary-panel-title">Department Summary</h4>
                    <p class="summary-panel-subtitle">Merged department groups with live detail drill-down.</p>
                </div>
                <div class="card-body">
                    <div class="summary-table-shell">
                        <table class="table table-striped table-bordered branch-summary-table mb-0">
                            <thead>
                            <tr>
                                <th>Department</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <th class="text-center summary-metric-heading summary-metric-{{ $metricKey }}">{{ $metricLabel }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($departmentDashboardSummaries as $departmentSummary)
                                <tr>
                                    <td>
                                        <button type="button"
                                                class="summary-trigger summary-name-trigger"
                                                data-summary-scope="department"
                                                data-summary-metric="total_all_employee"
                                                data-entity-name="{{ ucfirst($departmentSummary->dept_name) }}"
                                                data-entity-ids="{{ implode(',', $departmentSummary->department_ids ?? []) }}">
                                            {{ ucfirst($departmentSummary->dept_name) }}
                                        </button>
                                    </td>
                                    @foreach($summaryMetrics as $metricKey => $metricLabel)
                                        <td class="text-center">
                                            <button type="button"
                                                    class="summary-trigger summary-value-trigger summary-metric-{{ $metricKey }}"
                                                    data-summary-scope="department"
                                                    data-summary-metric="{{ $metricKey }}"
                                                    data-entity-name="{{ ucfirst($departmentSummary->dept_name) }}"
                                                    data-entity-ids="{{ implode(',', $departmentSummary->department_ids ?? []) }}">
                                                {{ number_format($departmentSummary->{$metricKey}) }}
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center"><b>{{ __('index.no_records_found') }}</b></td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Total</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <td class="text-center">
                                        <button type="button"
                                                class="summary-trigger summary-value-trigger summary-metric-{{ $metricKey }}"
                                                data-summary-scope="department"
                                                data-summary-metric="{{ $metricKey }}"
                                                data-entity-name="All Departments"
                                                data-entity-ids="{{ $departmentSummaryAllIds }}">
                                            {{ number_format($departmentSummaryTotals[$metricKey]) }}
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="summaryDetailModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="summaryDetailModalLabel">Summary Detail</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="summaryDetailLoading" class="text-center py-4 d-none">Loading...</div>
                            <div id="summaryDetailEmpty" class="text-center py-4 d-none">No records found.</div>
                            <div class="table-responsive">
                                <table class="table table-striped summary-modal-table mb-0">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Employee Code</th>
                                        <th>Email</th>
                                        <th>Branch</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Quick Action</th>
                                    </tr>
                                    </thead>
                                    <tbody id="summaryDetailTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="dashboardQuickLeaveModal" tabindex="-1" aria-labelledby="dashboardQuickLeaveModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="dashboardQuickLeaveModalLabel">Quick Leave</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('admin.attendances.quick-approved-leave') }}" method="post" id="dashboardQuickLeaveForm">
                                @csrf
                                <input type="hidden" name="user_id" id="dashboardQuickLeaveUserId">
                                <input type="hidden" name="attendance_date" id="dashboardQuickLeaveDate">
                                <div class="mb-3">
                                    <label for="dashboardQuickLeaveType" class="form-label">Leave Type</label>
                                    <select class="form-select" name="leave_type_id" id="dashboardQuickLeaveType" required>
                                        <option value="">Loading leave types...</option>
                                    </select>
                                    <small class="text-muted d-block mt-2" id="dashboardQuickLeaveHelpText">
                                        Create an already approved leave for today.
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label for="dashboardQuickLeaveReason" class="form-label">{{ __('index.leave_reason') }}</label>
                                    <textarea class="form-control" name="reasons" id="dashboardQuickLeaveReason" rows="3" placeholder="Optional note"></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary btn-sm" id="dashboardQuickLeaveSubmit">Save Quick Leave</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="dashboardLeaveStatusUpdate" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h5 class="modal-title">Leave Status Update</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="forms-sample" id="dashboardUpdateLeaveStatus" action="" method="post">
                                @csrf
                                @method('put')
                                <input type="hidden" name="redirect_back" value="1">
                                <div class="mb-3">
                                    <label for="dashboardLeaveStatus" class="form-label">{{ __('index.status') }}</label>
                                    <select class="form-select" id="dashboardLeaveStatus" name="status">
                                        <option value="{{ \App\Enum\LeaveStatusEnum::approved->value }}">{{ __('index.approve') }}</option>
                                        <option value="{{ \App\Enum\LeaveStatusEnum::rejected->value }}">{{ __('index.reject') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="dashboardLeaveRemark" class="form-label">{{ __('index.admin_remark') }}</label>
                                    <textarea class="form-select" id="dashboardLeaveRemark" minlength="10" name="admin_remark" rows="3"></textarea>
                                </div>
                                <div id="dashboardPreviousApprovers" class="mb-3"></div>
                                <div class="text-start">
                                    <button type="submit" class="btn btn-primary btn-xs">{{ __('index.submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        @canany(['project_detail','client_detail'])
            @can('project_detail')
                <div class="projectManagement">
                    <h4 class="mb-4">{{ __('index.project_management') }} </h4>
                    <div class="row">
                        <div class="col-xxl-6 col-xl-6 d-flex mb-4">
                            <div class="card card-table flex-fill">
                                <div class="card-header">
                                    <h3 class="card-title mb-0">{{ __('index.projects_detail') }}</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="projectChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-6 col-xl-6 d-flex">
                            <div class="row">
                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.total_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['total_projects'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.pending_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['not_started'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.on_hold_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['on_hold'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.in_progress_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['in_progress'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.finished_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['completed'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.cancelled_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['cancelled'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <div class="row">
                @can('client_detail')
                    <div class="col-xxl-8 col-xl-8 mb-4 d-flex">
                        <div class="card card-table flex-fill">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">{{ __('index.top_clients') }}</h3>
                                <a href="{{route('admin.clients.index')}}">{{ __('index.view_all_clients') }}</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table custom-table mb-0">
                                        <thead>
                                        <tr>
                                            <th>{{ __('index.name') }}</th>
                                            <th class="text-center">{{ __('index.email') }}</th>
                                            <th class="text-center">{{ __('index.contact') }}</th>
                                            <th class="text-center">{{ __('index.project') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($topClients as $key => $client)
                                            <tr>
                                                <td class="table-avatar w-35">

                                                    <a href="{{route('admin.clients.show',$client->id)}}"
                                                       class="avatar">
                                                        <img alt=""
                                                             src="{{asset(Client::UPLOAD_PATH.$client->avatar)}}">
                                                        <span class="ms-1">{{ucfirst($client->name)}}</span>
                                                    </a>

                                                </td>
                                                <td class="text-center">{{$client->email}}</td>
                                                <td class="text-center">
                                                    {{$client->contact_no}}
                                                </td>

                                                <td class="text-center">
                                                    {{$client->project_count}}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="100%">
                                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('project_detail')
                    <div class="col-xxl-4 col-xl-4 mb-4 d-flex">
                        <div class="card card-table flex-fill">
                            <div class="card-header text-center">
                                <h3 class="card-title mb-0">{{ __('index.task_details') }}</h3>
                            </div>
                            <div class="card-body text-center">
                                <canvas id="tasksChart"></canvas>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>

            @can('project_detail')
                <div class="card card-table flex-fill">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">{{ __('index.recent_projects') }}</h3>
                        <a href="{{route('admin.projects.index')}}">{{ __('index.view_all_projects') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table custom-table mb-0">
                                <thead>
                                <tr>
                                    <th class="w-25">{{ __('index.title') }}</th>
                                    <th class="text-center">{{ __('index.date_start') }}</th>
                                    <th class="text-center">{{ __('index.deadline') }}</th>
                                    <th class="text-center">{{ __('index.leader') }}</th>
                                    <th class="text-center">{{ __('index.completion') }}</th>
                                    <th class="text-center">{{ __('index.priority') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($recentProjects as $key => $project)
                                    <tr>
                                        <td class="w-25">
                                            <a href="{{route('admin.projects.show',$project->id)}}">{{ucfirst($project->name)}} </a>
                                        </td>
                                        <td class="text-center">{{AppHelper::formatDateForView($project->start_date)}}</td>
                                        <td class="text-center">
                                            {{AppHelper::formatDateForView($project->deadline)}}
                                        </td>

                                        <td class="member-listed text-center">
                                            @forelse($project->projectLeaders as $key => $leader)

                                                <button type="button" class="p-0 border-0 bg-transparent ms-n3 "
                                                        disabled data-toggle="tooltip" data-placement="top"
                                                        title="{{ $leader->user ? ucfirst($leader->user->name) : 'Project Leader' }}">
                                                    <img class="rounded-circle" style="object-fit: cover"
                                                         src="{{ $leader->user ? asset(User::AVATAR_UPLOAD_PATH.$leader->user->avatar):
                                                                    asset('assets/images/img.png')
                                                        }}"
                                                         alt="profile">
                                                </button>

                                            @empty

                                            @endforelse
                                        </td>
                                        <td class="text-center">
                                            <div class="progress">
                                                <div class="progress-bar color2 rounded"
                                                     role="progressbar"
                                                     style="{{AppHelper::getProgressBarStyle($project->getProjectProgressInPercentage())}}"
                                                     aria-valuenow="25"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                    <span>{{($project->getProjectProgressInPercentage())}} %</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                                    <span
                                                        class="btn btn-{{$projectPriority[$project->priority]}} btn-xs cursor-default">
                                                            {{ucfirst($project->priority)}}
                                                    </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endcan
        @endcanany
    </section>
@endsection

<script src="{{asset('assets/vendors/chartjs/Chart.min.js')}}"></script>

@section('scripts')
    <script>
        let translatedStrings = @json(__('index'));
    </script>
    @include('admin.dashboard_scripts')
@endsection
