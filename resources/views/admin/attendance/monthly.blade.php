@php use App\Models\User; @endphp
@extends('layouts.master')

@section('title', 'Monthly Attendance')
@section('action', 'Monthly Attendance')

@section('button')
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#monthlyAttendanceFilters">
            <i class="link-icon" data-feather="filter"></i> Filter
        </button>
        @can('attendance_csv_export')
            <a class="btn btn-primary btn-sm" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">
                <i class="link-icon" data-feather="download"></i> Export
            </a>
        @endcan
    </div>
@endsection

@section('styles')
    <style>
        .monthly-attendance-page {
            color: #111827;
        }

        .page-content {
            padding-top: 0.45rem !important;
        }

        .content {
            margin-top: 0 !important;
        }

        .monthly-attendance-page .page-breadcrumb {
            margin-bottom: 0.6rem;
        }

        .monthly-filter-shell,
        .monthly-table-shell {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .monthly-report-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #e5e7eb;
            margin-bottom: 8px;
        }

        .monthly-report-item {
            min-height: 48px;
            padding: 8px 10px;
            background: #fff;
        }

        .monthly-report-label {
            display: block;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .monthly-report-value {
            display: block;
            margin-top: 1px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.2;
        }

        .monthly-report-note {
            display: block;
            margin-top: 1px;
            color: #64748b;
            font-size: 10px;
        }

        .month-switcher {
            min-height: 34px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: grid;
            grid-template-columns: 28px 128px 28px;
            align-items: center;
            background: #fff;
            overflow: hidden;
            width: 184px;
        }

        .month-switcher a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            min-height: 34px;
        }

        .month-switcher .form-control {
            min-height: 34px;
            border: 0;
            border-left: 1px solid #eef2f7;
            border-right: 1px solid #eef2f7;
            border-radius: 0;
            box-shadow: none;
            text-align: center;
            font-weight: 700;
        }

        .monthly-filter-label {
            display: inline-flex;
            align-items: center;
            margin: 0;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .monthly-filter-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 10px;
            border-bottom: 1px solid #eef2f7;
        }

        .monthly-filter-toggle .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .monthly-filter-toggle svg {
            width: 15px;
            height: 15px;
        }

        .monthly-inline-field {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .monthly-inline-field .monthly-filter-label {
            flex: 0 0 auto;
        }

        .monthly-inline-field .form-control,
        .monthly-inline-field .form-select {
            flex: 1;
            min-width: 0;
        }

        .monthly-inline-field .month-switcher {
            flex: 0 0 184px;
        }

        .monthly-filter-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .monthly-filter-month {
            width: 240px;
        }

        .monthly-filter-select {
            width: 330px;
        }

        .monthly-filter-actions {
            width: 250px;
        }

        .monthly-filter-shell .form-control,
        .monthly-filter-shell .form-select,
        .monthly-filter-shell .btn,
        .monthly-table-toolbar .form-control,
        .monthly-table-toolbar .form-select,
        .monthly-table-toolbar .btn {
            min-height: 34px;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
            font-size: 12px;
        }

        .monthly-table-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 10px;
            border-bottom: 1px solid #eef2f7;
            background: #fbfdff;
        }

        .monthly-table-toolbar-title {
            margin: 0;
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
        }

        .monthly-table-toolbar-subtitle {
            margin: 2px 0 0;
            color: #64748b;
            font-size: 11px;
        }

        .monthly-table-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .monthly-table-search {
            width: min(240px, 100%);
        }

        .monthly-table-rows {
            width: 84px;
        }

        .monthly-stat-card {
            min-height: 68px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
        }

        .monthly-stat-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 34px;
        }

        .monthly-stat-icon i {
            width: 16px;
            height: 16px;
        }

        .stat-employees .monthly-stat-icon { color: #1d4ed8; background: #eff6ff; }
        .stat-present .monthly-stat-icon { color: #16a34a; background: #ecfdf3; }
        .stat-late .monthly-stat-icon { color: #f97316; background: #fff7ed; }
        .stat-absent .monthly-stat-icon { color: #dc2626; background: #fef2f2; }
        .stat-leave .monthly-stat-icon { color: #7c3aed; background: #f5f3ff; }
        .stat-off .monthly-stat-icon { color: #64748b; background: #f1f5f9; }

        .monthly-stat-title {
            margin: 0;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
        }

        .monthly-stat-value {
            margin: 1px 0 0;
            color: #0f172a;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
        }

        .monthly-stat-subtitle {
            margin: 0;
            color: #64748b;
            font-size: 10px;
        }

        .monthly-table-wrap {
            overflow: auto;
            border-radius: 8px;
        }

        .monthly-attendance-table {
            width: 100%;
            min-width: 1280px;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }

        .monthly-attendance-table th,
        .monthly-attendance-table td {
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #edf2f7;
            background: #fff;
            text-align: center;
            vertical-align: middle;
            padding: 6px 5px;
            font-size: 11px;
        }

        .monthly-attendance-table thead th {
            position: sticky;
            top: 0;
            z-index: 4;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
            background: #f8fafc;
            line-height: 1.15;
        }

        .monthly-attendance-table thead small {
            font-size: 9px;
        }

        .monthly-attendance-table .sticky-number {
            position: sticky;
            left: 0;
            z-index: 5;
            min-width: 38px;
            width: 38px;
        }

        .monthly-attendance-table .sticky-employee {
            position: sticky;
            left: 38px;
            z-index: 5;
            min-width: 285px;
            width: 285px;
            text-align: left;
            box-shadow: 5px 0 10px rgba(15, 23, 42, 0.04);
            overflow: visible;
        }

        .monthly-attendance-table tbody tr:hover .sticky-employee {
            z-index: 25;
        }

        .monthly-attendance-table thead .sticky-number,
        .monthly-attendance-table thead .sticky-employee {
            z-index: 7;
            background: #f8fafc;
        }

        .monthly-weekend,
        .monthly-weekend td,
        .monthly-attendance-table th.monthly-weekend {
            background: #fff7f7;
        }

        .monthly-employee {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            min-width: 0;
        }

        .monthly-avatar-wrap {
            position: relative;
            flex: 0 0 38px;
            padding-top: 2px;
        }

        .monthly-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            background: #e5e7eb;
            border: 2px solid #fff;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.12);
        }

        .monthly-employee strong {
            display: block;
            color: #0f172a;
            font-weight: 800;
            line-height: 1.15;
            font-size: 12px;
        }

        .monthly-employee-main {
            position: relative;
            min-width: 0;
            flex: 1;
        }

        .monthly-employee-line {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            white-space: nowrap;
        }

        .monthly-employee-line strong {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .monthly-employee-username {
            display: inline-flex;
            flex: 0 0 auto;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
        }

        .monthly-employee span {
            display: block;
            color: #64748b;
            font-size: 10px;
            line-height: 1.25;
        }

        .monthly-employee-meta {
            position: absolute;
            left: 0;
            top: calc(100% + 6px);
            z-index: 35;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            width: 255px;
            padding: 7px;
            border: 1px solid #dbe4ef;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.16);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-4px);
            transition: opacity .15s ease, transform .15s ease, visibility .15s ease;
        }

        .monthly-attendance-table tbody tr:hover .monthly-employee-meta,
        .monthly-employee:hover .monthly-employee-meta {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .monthly-employee-meta span {
            display: inline-flex;
            align-items: center;
            max-width: 126px;
            padding: 2px 5px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            color: #475569;
            font-size: 9px;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .monthly-employee-meta .meta-branch { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .monthly-employee-meta .meta-department { background: #ecfdf3; color: #15803d; border-color: #bbf7d0; }
        .monthly-employee-meta .meta-position { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
        .monthly-employee-meta .meta-shift { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }

        .monthly-status-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 8px;
            font-weight: 800;
            line-height: 1;
        }

        .status-present { background: #39b54a; }
        .status-late { background: #f97316; }
        .status-absent { background: #ef232a; }
        .status-leave { background: #7c3aed; }
        .status-off_day { background: #cbd5e1; color: #475569; }
        .status-empty { background: #e5e7eb; color: #94a3b8; }

        .monthly-total {
            font-weight: 800;
            color: #0f172a;
        }

        .total-present { color: #16a34a; }
        .total-late { color: #f97316; }
        .total-absent { color: #dc2626; }
        .total-leave { color: #7c3aed; }

        .monthly-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            color: #334155;
            font-size: 11px;
        }

        .monthly-legend span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .monthly-empty-state {
            padding: 28px 16px;
            text-align: center;
            color: #64748b;
        }

        @media (max-width: 991.98px) {
            .monthly-report-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .monthly-filter-month,
            .monthly-filter-select,
            .monthly-filter-actions {
                width: 100%;
            }

            .monthly-inline-field .month-switcher {
                flex: 1;
            }
        }

        @media (max-width: 575.98px) {
            .monthly-report-strip {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('main-content')
    <section class="content monthly-attendance-page">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.attendances.index') }}">Attendance</a></li>
                <li class="breadcrumb-item active" aria-current="page">Monthly Attendance</li>
            </ol>
        </nav>

        <div class="monthly-filter-shell mb-3">
            <div class="monthly-filter-toggle">
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#monthlyAttendanceFilters" aria-expanded="true" aria-controls="monthlyAttendanceFilters">
                    <i data-feather="filter"></i>
                    <span>Filters</span>
                    <i data-feather="chevron-down"></i>
                </button>
            </div>

            <form action="{{ route('admin.attendance-monthly.index') }}" method="get" class="collapse show p-2" id="monthlyAttendanceFilters">
            <div class="monthly-filter-row">
                <div class="monthly-filter-month">
                    <div class="monthly-inline-field">
                        <label class="monthly-filter-label">Month</label>
                        <div class="month-switcher">
                        <a href="{{ request()->fullUrlWithQuery(['month' => $month->copy()->subMonth()->format('Y-m'), 'page' => null]) }}" title="Previous month">
                            <i data-feather="chevron-left"></i>
                        </a>
                        <input class="form-control" type="month" name="month" value="{{ $filter['month'] }}" aria-label="Select month">
                        <a href="{{ request()->fullUrlWithQuery(['month' => $month->copy()->addMonth()->format('Y-m'), 'page' => null]) }}" title="Next month">
                            <i data-feather="chevron-right"></i>
                        </a>
                        </div>
                    </div>
                </div>

                <div class="monthly-filter-select">
                    <div class="monthly-inline-field">
                        <label class="monthly-filter-label" for="branch_id">Branch</label>
                        <select class="form-select" id="branch_id" name="branch_id">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) $filter['branch_id'] === (string) $branch->id)>{{ ucfirst($branch->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="monthly-filter-select">
                    <div class="monthly-inline-field">
                        <label class="monthly-filter-label" for="department_id">Department</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) $filter['department_id'] === (string) $department->id)>{{ ucfirst($department->dept_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="monthly-filter-actions d-flex gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">Apply</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.attendance-monthly.index') }}">Reset</a>
                </div>
            </div>
            </form>
        </div>

        <div class="monthly-report-strip">
            <div class="monthly-report-item">
                <span class="monthly-report-label">Report Month</span>
                <span class="monthly-report-value">{{ $month->format('F Y') }}</span>
                <span class="monthly-report-note">{{ $calendarDays[0]['date'] ?? $month->startOfMonth()->toDateString() }} to {{ $calendarDays[count($calendarDays) - 1]['date'] ?? $month->endOfMonth()->toDateString() }}</span>
            </div>
            <div class="monthly-report-item">
                <span class="monthly-report-label">Employees In View</span>
                <span class="monthly-report-value">{{ number_format($summary['employees']) }}</span>
                <span class="monthly-report-note">After branch and department filters</span>
            </div>
            <div class="monthly-report-item">
                <span class="monthly-report-label">Calendar Days</span>
                <span class="monthly-report-value">{{ count($calendarDays) }}</span>
                <span class="monthly-report-note">{{ collect($calendarDays)->where('is_weekend', true)->count() }} weekend columns highlighted</span>
            </div>
            <div class="monthly-report-item">
                <span class="monthly-report-label">Attendance Signals</span>
                <span class="monthly-report-value">{{ number_format($summary['present'] + $summary['late'] + $summary['leave'] + $summary['absent']) }}</span>
                <span class="monthly-report-note">Present, late, leave, and absent cells</span>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="monthly-stat-card stat-employees">
                    <span class="monthly-stat-icon"><i data-feather="users"></i></span>
                    <div>
                        <p class="monthly-stat-title">Total Employees</p>
                        <p class="monthly-stat-value">{{ number_format($summary['employees']) }}</p>
                        <p class="monthly-stat-subtitle">All Employees</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="monthly-stat-card stat-present">
                    <span class="monthly-stat-icon"><i data-feather="check"></i></span>
                    <div>
                        <p class="monthly-stat-title">Present</p>
                        <p class="monthly-stat-value">{{ number_format($summary['present']) }}</p>
                        <p class="monthly-stat-subtitle">{{ number_format($summary['present_rate'], 2) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="monthly-stat-card stat-late">
                    <span class="monthly-stat-icon"><i data-feather="clock"></i></span>
                    <div>
                        <p class="monthly-stat-title">Late</p>
                        <p class="monthly-stat-value">{{ number_format($summary['late']) }}</p>
                        <p class="monthly-stat-subtitle">{{ number_format($summary['late_rate'], 2) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="monthly-stat-card stat-absent">
                    <span class="monthly-stat-icon"><i data-feather="x"></i></span>
                    <div>
                        <p class="monthly-stat-title">Absent</p>
                        <p class="monthly-stat-value">{{ number_format($summary['absent']) }}</p>
                        <p class="monthly-stat-subtitle">{{ number_format($summary['absent_rate'], 2) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="monthly-stat-card stat-leave">
                    <span class="monthly-stat-icon"><i data-feather="umbrella"></i></span>
                    <div>
                        <p class="monthly-stat-title">On Leave</p>
                        <p class="monthly-stat-value">{{ number_format($summary['leave']) }}</p>
                        <p class="monthly-stat-subtitle">{{ number_format($summary['leave_rate'], 2) }}%</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="monthly-stat-card stat-off">
                    <span class="monthly-stat-icon"><i data-feather="calendar"></i></span>
                    <div>
                        <p class="monthly-stat-title">Off Day</p>
                        <p class="monthly-stat-value">{{ number_format($summary['off_day']) }}</p>
                        <p class="monthly-stat-subtitle">Monthly Off</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="monthly-table-shell">
            <div class="monthly-table-toolbar">
                <div>
                    <p class="monthly-table-toolbar-title">Employee Monthly Attendance</p>
                    <p class="monthly-table-toolbar-subtitle">Full employee identity, daily status, and month totals for {{ $month->format('F Y') }}</p>
                </div>
                <form action="{{ route('admin.attendance-monthly.index') }}" method="get" class="monthly-table-controls">
                    <input type="hidden" name="month" value="{{ $filter['month'] }}">
                    @if($filter['branch_id'])
                        <input type="hidden" name="branch_id" value="{{ $filter['branch_id'] }}">
                    @endif
                    @if($filter['department_id'])
                        <input type="hidden" name="department_id" value="{{ $filter['department_id'] }}">
                    @endif

                    <div class="monthly-inline-field">
                        <label class="monthly-filter-label" for="table_per_page">Rows</label>
                        <select class="form-select monthly-table-rows" id="table_per_page" name="per_page">
                            @foreach([10, 25, 50, 100] as $perPage)
                                <option value="{{ $perPage }}" @selected((int) $filter['per_page'] === $perPage)>{{ $perPage }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="monthly-inline-field">
                        <label class="monthly-filter-label" for="table_search">Search</label>
                        <input class="form-control monthly-table-search" id="table_search" name="search" value="{{ $filter['search'] }}" placeholder="Name, code, or username">
                    </div>

                    <button class="btn btn-primary" type="submit">Apply</button>
                    @if($filter['search'])
                        <a class="btn btn-outline-secondary" href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}">Clear</a>
                    @endif
                </form>
            </div>

            <div class="monthly-table-wrap">
                <table class="monthly-attendance-table">
                    <thead>
                    <tr>
                        <th class="sticky-number">#</th>
                        <th class="sticky-employee">Employee</th>
                        @foreach($calendarDays as $day)
                            <th class="{{ $day['is_weekend'] ? 'monthly-weekend' : '' }}">
                                <div>{{ $day['day'] }}</div>
                                <small>{{ $day['weekday'] }}</small>
                            </th>
                        @endforeach
                        <th>Present</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Leave</th>
                        <th>Off Day</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($monthlyRows as $row)
                        @php
                            $employee = $row['employee'];
                            $avatar = $employee->avatar ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar) : asset('assets/images/img.png');
                            $shiftLabel = $employee->officeTime?->shift ?: trim(($employee->officeTime?->opening_time ?: '') . ' - ' . ($employee->officeTime?->closing_time ?: ''));
                        @endphp
                        <tr>
                            <td class="sticky-number">{{ $monthlyRows->firstItem() + $loop->index }}</td>
                            <td class="sticky-employee">
                                <div class="monthly-employee">
                                    <div class="monthly-avatar-wrap">
                                        <img class="monthly-avatar" src="{{ $avatar }}" alt="{{ $employee->name }}">
                                    </div>
                                    <div class="monthly-employee-main">
                                        <div class="monthly-employee-line">
                                            <strong>{{ ucfirst($employee->name) }}</strong>
                                            <span class="monthly-employee-username">{{ $employee->username ?: 'Employee' }}</span>
                                        </div>
                                        <div class="monthly-employee-meta">
                                            <span class="meta-branch" title="Branch: {{ $employee->branch?->name ?: 'No branch' }}">
                                                {{ $employee->branch?->name ?: 'No branch' }}
                                            </span>
                                            <span class="meta-department" title="Department: {{ $employee->department?->dept_name ?: 'No department' }}">
                                                {{ $employee->department?->dept_name ?: 'No department' }}
                                            </span>
                                            <span class="meta-position" title="Position: {{ $employee->post?->post_name ?: 'No position' }}">
                                                {{ $employee->post?->post_name ?: 'No position' }}
                                            </span>
                                            <span class="meta-shift" title="Shift: {{ $shiftLabel ?: 'No shift' }}">
                                                {{ $shiftLabel ?: 'No shift' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @foreach($row['days'] as $day)
                                <td class="{{ $day['is_weekend'] ? 'monthly-weekend' : '' }}">
                                    <span class="monthly-status-dot status-{{ $day['status'] }}" title="{{ $day['date'] }} - {{ $day['label'] }}">
                                        {{ ['present' => 'P', 'late' => 'L', 'absent' => 'A', 'leave' => 'LV', 'off_day' => 'O', 'empty' => '-'][$day['status']] ?? '-' }}
                                    </span>
                                </td>
                            @endforeach
                            <td class="monthly-total total-present">{{ $row['totals']['present'] }}</td>
                            <td class="monthly-total total-late">{{ $row['totals']['late'] }}</td>
                            <td class="monthly-total total-absent">{{ $row['totals']['absent'] }}</td>
                            <td class="monthly-total total-leave">{{ $row['totals']['leave'] }}</td>
                            <td class="monthly-total">{{ $row['totals']['off_day'] }}</td>
                            <td class="monthly-total">{{ $row['total_days'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($calendarDays) + 8 }}">
                                <div class="monthly-empty-state">
                                    <strong>No monthly attendance records found.</strong>
                                    <div>Try changing the month or filters.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-2">
                <div class="monthly-legend">
                    <span><i class="monthly-status-dot status-present">P</i> Present</span>
                    <span><i class="monthly-status-dot status-late">L</i> Late</span>
                    <span><i class="monthly-status-dot status-absent">A</i> Absent</span>
                    <span><i class="monthly-status-dot status-leave">LV</i> On Leave</span>
                    <span><i class="monthly-status-dot status-off_day">O</i> Off Day</span>
                </div>
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                    {{ $monthlyRows->links() }}
                    <span class="text-muted small">
                        Showing {{ $monthlyRows->firstItem() ?? 0 }} to {{ $monthlyRows->lastItem() ?? 0 }} of {{ $monthlyRows->total() }} entries
                    </span>
                </div>
            </div>
        </div>
    </section>
@endsection
