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
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(180deg, var(--summary-card-top) 0%, var(--summary-card-bottom) 100%);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        .summary-panel .card-header {
            border-bottom: 1px solid rgba(217, 226, 239, 0.95);
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 32%),
                linear-gradient(135deg, var(--summary-head-top) 0%, var(--summary-head-bottom) 100%);
            padding: 1.35rem 1.7rem;
        }

        .summary-panel .card-body {
            padding: 1.5rem;
        }

        .summary-panel-title {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0;
        }

        .summary-panel-subtitle {
            margin-top: 0.35rem;
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.92rem;
        }

        .summary-table-shell {
            border: 1px solid rgba(217, 226, 239, 0.95);
            border-radius: 20px;
            overflow: auto;
            background: rgba(255, 255, 255, 0.96);
        }

        .branch-summary-table {
            margin-bottom: 0;
            min-width: 1180px;
        }

        .branch-summary-table th,
        .branch-summary-table td {
            white-space: nowrap;
            vertical-align: middle;
            border-color: rgba(217, 226, 239, 0.95);
            padding: 1rem 1.05rem;
        }

        .branch-summary-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #eef5ff;
            color: var(--summary-ink);
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .branch-summary-table tbody tr:nth-child(even) td {
            background: rgba(245, 249, 255, 0.78);
        }

        .branch-summary-table tbody tr:hover td {
            background: #fff3d9;
            transition: background-color 0.2s ease;
        }

        .branch-summary-table th:first-child,
        .branch-summary-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 2;
            background-clip: padding-box;
        }

        .branch-summary-table thead th:first-child {
            z-index: 4;
        }

        .branch-summary-table tbody td:first-child {
            background: #ffffff;
        }

        .branch-summary-table tbody tr:nth-child(even) td:first-child {
            background: #f8fbff;
        }

        .branch-summary-table tbody tr:hover td:first-child {
            background: #fff3d9;
        }

        .summary-name-trigger {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: var(--summary-ink);
        }

        .summary-name-trigger::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
        }

        .summary-value-trigger {
            min-width: 44px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            background: rgba(22, 58, 95, 0.08);
            color: var(--summary-ink);
            font-weight: 800;
            line-height: 1.1;
            box-shadow: inset 0 0 0 1px rgba(22, 58, 95, 0.08);
        }

        .summary-value-trigger:hover {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
        }

        .branch-summary-table tfoot th,
        .branch-summary-table tfoot td {
            font-weight: 700;
            background: linear-gradient(180deg, var(--summary-total-top) 0%, var(--summary-total-bottom) 100%);
            color: var(--summary-ink);
            position: sticky;
            bottom: 0;
            z-index: 2;
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
                                    <div class="row align-items-center d-md-flex">
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
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_employees') }}</h6>
                                    </div>

                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_employee)}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="users"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.pending_leave_requests') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{ number_format($dashboardDetail?->total_pending_leave_requests) ?? 0}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="twitch"> </i>
                                        </div>

                                    </div>
                                </div>
                            </div>
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
                                    <div class="row align-items-center d-md-fle">
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
                    'active_employee_pending_request' => 'Pending',
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
                                @foreach($summaryMetrics as $metricLabel)
                                    <th class="text-center">{{ $metricLabel }}</th>
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
                                                    class="summary-trigger summary-value-trigger"
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
                                    <td colspan="11" class="text-center"><b>{{ __('index.no_records_found') }}</b></td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Total</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <td class="text-center">
                                        <button type="button"
                                                class="summary-trigger summary-value-trigger"
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
                                @foreach($summaryMetrics as $metricLabel)
                                    <th class="text-center">{{ $metricLabel }}</th>
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
                                                    class="summary-trigger summary-value-trigger"
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
                                    <td colspan="11" class="text-center"><b>{{ __('index.no_records_found') }}</b></td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Total</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <td class="text-center">
                                        <button type="button"
                                                class="summary-trigger summary-value-trigger"
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
                                    </tr>
                                    </thead>
                                    <tbody id="summaryDetailTableBody"></tbody>
                                </table>
                            </div>
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

