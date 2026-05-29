@php use App\Models\User; @endphp
@extends('layouts.master')

@section('title', 'Monthly Attendance')
@section('action', 'Monthly Attendance')

@section('button')
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#monthlyAttendanceFilters">
            <i class="link-icon" data-feather="filter"></i> Filter
        </button>
        @canany(['attendance_csv_export', 'monthly_attendance_csv_export'])
            <a class="btn btn-primary btn-sm" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">
                <i class="link-icon" data-feather="download"></i> Export
            </a>
        @endcanany
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
            min-height: 42px;
            padding: 6px 8px;
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

        .monthly-signal-toggle {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        .monthly-signal-column {
            min-width: 58px;
            transition: opacity .15s ease;
        }

        .monthly-signal-column.is-hidden {
            display: none;
        }

        .monthly-signal-value {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .monthly-signal-value.has-value {
            background: #fff7ed;
            color: #c2410c;
        }

        .monthly-signal-button {
            border: 0;
            padding: 0;
            background: transparent;
        }

        .monthly-signal-button:disabled {
            cursor: default;
            opacity: 0.55;
        }

        .monthly-signal-day-list {
            display: grid;
            gap: 8px;
        }

        .monthly-signal-day-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f8fafc;
        }

        .monthly-signal-day-item strong {
            display: block;
            color: #0f172a;
            font-size: 12px;
        }

        .monthly-signal-day-item span {
            color: #64748b;
            font-size: 11px;
        }

        .monthly-stat-card {
            min-height: 56px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
        }

        .monthly-stat-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 30px;
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
            font-size: 16px;
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
            min-width: 1120px;
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
            padding: 4px 3px;
            font-size: 10px;
        }

        .monthly-attendance-table thead th {
            position: sticky;
            top: 0;
            z-index: 4;
            color: #334155;
            font-size: 10px;
            font-weight: 800;
            background: #f8fafc;
            line-height: 1.15;
        }

        .monthly-attendance-table thead small {
            font-size: 8px;
        }

        .monthly-attendance-table .sticky-number {
            position: sticky;
            left: 0;
            z-index: 5;
            min-width: 30px;
            width: 30px;
        }

        .monthly-attendance-table .sticky-employee {
            position: sticky;
            left: 30px;
            z-index: 5;
            min-width: 190px;
            width: 190px;
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
            gap: 7px;
            min-width: 0;
        }

        .monthly-avatar-wrap {
            position: relative;
            flex: 0 0 28px;
            padding-top: 2px;
        }

        .monthly-avatar {
            width: 26px;
            height: 26px;
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
            font-size: 11px;
        }

        .monthly-employee-main {
            position: relative;
            min-width: 0;
            flex: 1;
        }

        .monthly-employee-line {
            display: flex;
            align-items: center;
            gap: 4px;
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
            font-size: 10px;
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
            width: 190px;
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
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 7px;
            font-weight: 800;
            line-height: 1;
        }

        .status-present { background: #39b54a; }
        .status-late { background: #f97316; }
        .status-absent { background: #ef232a; }
        .status-leave { background: #7c3aed; }
        .status-off_day { background: #cbd5e1; color: #475569; }
        .status-empty { background: #e5e7eb; color: #94a3b8; }

        .monthly-day-cell {
            position: relative;
            min-width: 24px;
            padding: 0 !important;
        }

        .monthly-day-link {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 26px;
            padding: 4px 3px;
            background: transparent;
            color: inherit;
            cursor: pointer;
            text-decoration: none;
        }

        .monthly-day-link:hover {
            background: #eff6ff;
            text-decoration: none;
        }

        .monthly-cell-indicators {
            position: absolute;
            right: 1px;
            bottom: 1px;
            display: flex;
            gap: 2px;
            pointer-events: none;
        }

        .monthly-cell-indicator {
            min-width: 10px;
            height: 10px;
            padding: 0 2px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 5px;
            font-weight: 900;
            line-height: 1;
            border: 1px solid #fff;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.16);
        }

        .indicator-leave-approved { background: #7c3aed; }
        .indicator-leave-request { background: #f59e0b; }
        .indicator-time-leave-approved { background: #0891b2; }
        .indicator-time-leave-request { background: #f59e0b; }
        .indicator-open-checkout { background: #dc2626; }

        .monthly-detail-modal-list {
            margin: 0;
            padding-left: 18px;
            color: #334155;
            font-size: 13px;
        }

        .monthly-detail-modal-list li + li {
            margin-top: 5px;
        }

        .monthly-detail-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 14px;
        }

        .monthly-detail-action-card {
            width: 100%;
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f8fafc;
        }

        .monthly-detail-action-card strong {
            display: block;
            color: #0f172a;
            font-size: 12px;
        }

        .monthly-detail-action-card p {
            margin: 3px 0 7px;
            color: #64748b;
            font-size: 11px;
        }

        .attendance-chat-thread {
            height: 360px;
            overflow-y: auto;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .attendance-chat-thread .chat-bubble-row {
            display: flex;
        }

        .attendance-chat-thread .chat-bubble-row.outgoing {
            justify-content: flex-end;
        }

        .attendance-chat-thread .chat-bubble {
            max-width: 76%;
            padding: 9px 11px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            color: #0f172a;
            font-size: 12px;
            overflow-wrap: anywhere;
        }

        .attendance-chat-thread .chat-bubble.outgoing {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .attendance-chat-thread .chat-bubble-meta,
        .attendance-chat-status-text {
            color: #64748b;
            font-size: 11px;
        }

        .attendance-chat-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .attendance-chat-attach {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            color: #2563eb;
            cursor: pointer;
            margin: 0;
        }

        .attendance-chat-attach input {
            display: none;
        }

        .attendance-chat-input {
            flex: 1;
            min-width: 0;
            min-height: 34px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0 10px;
        }

        .monthly-total {
            font-weight: 800;
            color: #0f172a;
            min-width: 42px;
        }

        .monthly-total-button {
            border: 0;
            padding: 0;
            background: transparent;
            color: inherit;
            font: inherit;
        }

        .monthly-total-button:disabled {
            cursor: default;
            opacity: 0.55;
        }

        .monthly-total-value {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #f8fafc;
            font-size: 10px;
            font-weight: 800;
        }

        .monthly-total-value.has-value {
            background: #eff6ff;
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
                    <button type="button" class="btn btn-outline-secondary btn-sm monthly-signal-toggle" id="monthlySignalToggle" aria-pressed="true">
                        <i data-feather="columns"></i>
                        <span>Hide Signals</span>
                    </button>
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
                        <th class="monthly-signal-column">Pending<br>Day Off</th>
                        <th class="monthly-signal-column">Pending<br>Leave</th>
                        <th class="monthly-signal-column">Time<br>Leave</th>
                        <th class="monthly-signal-column">Time Leave<br>Request</th>
                        <th class="monthly-signal-column">No<br>Checkout</th>
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
                                <td class="monthly-day-cell {{ $day['is_weekend'] ? 'monthly-weekend' : '' }}">
                                    <button type="button"
                                            class="monthly-day-link border-0 w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#monthlyAttendanceDetailModal"
                                            data-user-id="{{ $employee->id }}"
                                            data-employee="{{ $employee->name }}"
                                            data-employee-code="{{ $employee->username ?: ($employee->employee_code ?: 'Employee') }}"
                                            data-employee-avatar="{{ $avatar }}"
                                            data-employee-online="{{ (int) ($employee->online_status ?? 0) }}"
                                            data-employee-subtitle="{{ trim(($employee->department?->dept_name ?: 'No department') . ' • ' . ($employee->branch?->name ?: 'No branch')) }}"
                                            data-date="{{ $day['date'] }}"
                                            data-display-date="{{ \Carbon\Carbon::parse($day['date'])->format('M d, Y') }}"
                                            data-status-key="{{ $day['status'] }}"
                                            data-status="{{ $day['label'] }}"
                                            data-details='@json($day["details"] ?? array_merge([$day["label"]], array_column($day["indicators"] ?? [], "label")))'
                                            data-indicators='@json($day["indicators"] ?? [])'
                                            data-actions='@json($day["actions"] ?? [])'
                                            data-can-quick-leave="{{ !empty($day['can_quick_leave']) ? '1' : '0' }}"
                                            data-can-quick-time-leave="{{ !empty($day['can_quick_time_leave']) ? '1' : '0' }}"
                                            data-fetch-url="{{ route('admin.leaves.employee-data', $employee->id) }}"
                                            data-detail-url="{{ $day['detail_url'] }}"
                                            title="{{ $day['date'] }} - {{ $day['tooltip'] ?? $day['label'] }}">
                                        <span class="monthly-status-dot status-{{ $day['status'] }}">
                                            {{ ['present' => 'P', 'late' => 'L', 'absent' => 'A', 'leave' => 'LV', 'off_day' => 'O', 'empty' => '-'][$day['status']] ?? '-' }}
                                        </span>
                                        @if(!empty($day['indicators']))
                                            <span class="monthly-cell-indicators">
                                                @foreach(array_slice($day['indicators'], 0, 2) as $indicator)
                                                    <span class="monthly-cell-indicator indicator-{{ $indicator['type'] }}" title="{{ $indicator['label'] }}">
                                                        {{ $indicator['short'] }}
                                                    </span>
                                                @endforeach
                                            </span>
                                        @endif
                                    </button>
                                </td>
                                            @endforeach
                            <td class="monthly-total total-present">
                                <button type="button" class="monthly-total-button" data-total-status="present" data-total-title="Present" @disabled(($row['totals']['present'] ?? 0) <= 0)>
                                    <span class="monthly-total-value {{ ($row['totals']['present'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['totals']['present'] }}</span>
                                </button>
                            </td>
                            <td class="monthly-total total-late">
                                <button type="button" class="monthly-total-button" data-total-status="late" data-total-title="Late" @disabled(($row['totals']['late'] ?? 0) <= 0)>
                                    <span class="monthly-total-value {{ ($row['totals']['late'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['totals']['late'] }}</span>
                                </button>
                            </td>
                            <td class="monthly-total total-absent">
                                <button type="button" class="monthly-total-button" data-total-status="absent" data-total-title="Absent" @disabled(($row['totals']['absent'] ?? 0) <= 0)>
                                    <span class="monthly-total-value {{ ($row['totals']['absent'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['totals']['absent'] }}</span>
                                </button>
                            </td>
                            <td class="monthly-total total-leave">
                                <button type="button" class="monthly-total-button" data-total-status="leave" data-total-title="Leave" @disabled(($row['totals']['leave'] ?? 0) <= 0)>
                                    <span class="monthly-total-value {{ ($row['totals']['leave'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['totals']['leave'] }}</span>
                                </button>
                            </td>
                            <td class="monthly-total">
                                <button type="button" class="monthly-total-button" data-total-status="off_day" data-total-title="Off Day" @disabled(($row['totals']['off_day'] ?? 0) <= 0)>
                                    <span class="monthly-total-value {{ ($row['totals']['off_day'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['totals']['off_day'] }}</span>
                                </button>
                            </td>
                            <td class="monthly-total">
                                <button type="button" class="monthly-total-button" data-total-status="all" data-total-title="Total" @disabled(($row['total_days'] ?? 0) <= 0)>
                                    <span class="monthly-total-value {{ ($row['total_days'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['total_days'] }}</span>
                                </button>
                            </td>
                            <td class="monthly-signal-column">
                                <button type="button" class="monthly-signal-button" data-signal="PO" data-signal-title="Pending Day Off" @disabled(($row['signal_totals']['pending_day_off'] ?? 0) <= 0)>
                                    <span class="monthly-signal-value {{ ($row['signal_totals']['pending_day_off'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['signal_totals']['pending_day_off'] ?? 0 }}</span>
                                </button>
                            </td>
                            <td class="monthly-signal-column">
                                <button type="button" class="monthly-signal-button" data-signal="PL" data-signal-title="Pending Leave" @disabled(($row['signal_totals']['pending_leave'] ?? 0) <= 0)>
                                    <span class="monthly-signal-value {{ ($row['signal_totals']['pending_leave'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['signal_totals']['pending_leave'] ?? 0 }}</span>
                                </button>
                            </td>
                            <td class="monthly-signal-column">
                                <button type="button" class="monthly-signal-button" data-signal="TL" data-signal-title="Time Leave" @disabled(($row['signal_totals']['time_leave'] ?? 0) <= 0)>
                                    <span class="monthly-signal-value {{ ($row['signal_totals']['time_leave'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['signal_totals']['time_leave'] ?? 0 }}</span>
                                </button>
                            </td>
                            <td class="monthly-signal-column">
                                <button type="button" class="monthly-signal-button" data-signal="TR" data-signal-title="Time Leave Request" @disabled(($row['signal_totals']['time_leave_request'] ?? 0) <= 0)>
                                    <span class="monthly-signal-value {{ ($row['signal_totals']['time_leave_request'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['signal_totals']['time_leave_request'] ?? 0 }}</span>
                                </button>
                            </td>
                            <td class="monthly-signal-column">
                                <button type="button" class="monthly-signal-button" data-signal="NC" data-signal-title="No Checkout" @disabled(($row['signal_totals']['no_checkout'] ?? 0) <= 0)>
                                    <span class="monthly-signal-value {{ ($row['signal_totals']['no_checkout'] ?? 0) > 0 ? 'has-value' : '' }}">{{ $row['signal_totals']['no_checkout'] ?? 0 }}</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($calendarDays) + 13 }}">
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
                    <span><i class="monthly-status-dot status-off_day">O</i> Day Off</span>
                    <span><i class="monthly-status-dot status-leave">LV</i> ច្បាប់ផ្សេង</span>
                    <span><i class="monthly-cell-indicator indicator-leave-request">PO</i> Pending Day Off</span>
                    <span><i class="monthly-cell-indicator indicator-leave-request">PL</i> Pending Leave</span>
                    <span><i class="monthly-cell-indicator indicator-time-leave-approved">TL</i> Time Leave</span>
                    <span><i class="monthly-cell-indicator indicator-time-leave-request">TR</i> Time Leave Request</span>
                    <span><i class="monthly-cell-indicator indicator-open-checkout">NC</i> No Checkout</span>
                </div>
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                    {{ $monthlyRows->links() }}
                    <span class="text-muted small">
                        Showing {{ $monthlyRows->firstItem() ?? 0 }} to {{ $monthlyRows->lastItem() ?? 0 }} of {{ $monthlyRows->total() }} entries
                    </span>
                </div>
            </div>
        </div>

        <div class="modal fade" id="monthlyAttendanceDetailModal" tabindex="-1" aria-labelledby="monthlyAttendanceDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="monthlyAttendanceDetailModalLabel">Attendance Detail</h5>
                            <small class="text-muted" id="monthlyAttendanceDetailSubtitle"></small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="monthly-detail-modal-list" id="monthlyAttendanceDetailList"></ul>
                        <div class="monthly-detail-actions" id="monthlyAttendanceRequestActions"></div>
                    </div>
                    <div class="modal-footer">
                        @can('quick_leave')
                            <button type="button" class="btn btn-outline-primary btn-sm quickApproveLeaveTrigger d-none" id="monthlyQuickLeaveButton">
                                Quick Approve Leave
                            </button>
                        @endcan
                        @can('create_time_leave_request')
                            <button type="button" class="btn btn-outline-info btn-sm quickApproveTimeLeaveTrigger d-none" id="monthlyQuickTimeLeaveButton">
                                Quick Time Leave
                            </button>
                        @endcan
                        @can('view_employee_chat')
                            <button type="button" class="btn btn-outline-secondary btn-sm openAttendanceChat" id="monthlyQuickChatButton">
                                Quick Chat
                            </button>
                        @endcan
                        <a href="#" class="btn btn-primary btn-sm" id="monthlyAttendanceDetailLink">Open Day Detail</a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="monthlySignalDetailModal" tabindex="-1" aria-labelledby="monthlySignalDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="monthlySignalDetailModalLabel">Signal Detail</h5>
                            <small class="text-muted" id="monthlySignalDetailSubtitle"></small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="monthly-signal-day-list" id="monthlySignalDayList"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="attendanceLeaveStatusUpdate" tabindex="-1" aria-labelledby="attendanceLeaveStatusUpdateTitle" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attendanceLeaveStatusUpdateTitle">Leave Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="forms-sample" id="attendanceUpdateLeaveStatus" action="" method="post">
                            @csrf
                            @method('put')
                            <input type="hidden" name="redirect_back" value="1">

                            <div class="mb-3">
                                <label class="form-label">{{ __('index.leave_reason') }}</label>
                                <div class="form-control bg-light" style="height:auto; min-height:44px;" id="attendanceLeaveStatusReason">N/A</div>
                            </div>

                            <div class="mb-3">
                                <label for="attendanceLeaveStatus" class="form-label">{{ __('index.status') }}</label>
                                <select class="form-select" id="attendanceLeaveStatus" name="status">
                                    <option value="{{ \App\Enum\LeaveStatusEnum::approved->value }}">{{ __('index.approve') }}</option>
                                    <option value="{{ \App\Enum\LeaveStatusEnum::rejected->value }}">{{ __('index.reject') }}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="attendanceLeaveRemark" class="form-label">{{ __('index.admin_remark') }}</label>
                                <textarea class="form-control" id="attendanceLeaveRemark" minlength="10" name="admin_remark" rows="3"></textarea>
                            </div>

                            <div id="attendancePreviousApprovers" class="mb-3"></div>

                            <button type="submit" class="btn btn-primary btn-sm">{{ __('index.submit') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @can('quick_leave')
            <div class="modal fade" id="attendanceQuickLeaveModal" tabindex="-1" aria-labelledby="attendanceQuickLeaveModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="attendanceQuickLeaveModalLabel">Quick Leave</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('admin.attendances.quick-approved-leave') }}" method="post" id="attendanceQuickLeaveForm">
                                @csrf
                                <input type="hidden" name="user_id" id="attendanceQuickLeaveUserId">
                                <input type="hidden" name="attendance_date" id="attendanceQuickLeaveDate">

                                <div class="mb-3">
                                    <label for="attendanceQuickLeaveType" class="form-label">Leave Type</label>
                                    <select class="form-select" name="leave_type_id" id="attendanceQuickLeaveType" required>
                                        <option value="">Select leave type</option>
                                    </select>
                                    <small class="text-muted d-block mt-2" id="attendanceQuickLeaveHelpText">
                                        This will create an already approved leave for the selected attendance day.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="attendanceQuickLeaveReason" class="form-label">{{ __('index.leave_reason') }}</label>
                                    <textarea class="form-control" name="reasons" id="attendanceQuickLeaveReason" rows="3" placeholder="Optional note"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm" id="attendanceQuickLeaveSubmit">
                                    Save as Approved Leave
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('create_time_leave_request')
            <div class="modal fade" id="attendanceQuickTimeLeaveModal" tabindex="-1" aria-labelledby="attendanceQuickTimeLeaveModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="attendanceQuickTimeLeaveModalLabel">Quick Time Leave</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('admin.attendances.quick-approved-time-leave') }}" method="post" id="attendanceQuickTimeLeaveForm">
                                @csrf
                                <input type="hidden" name="user_id" id="attendanceQuickTimeLeaveUserId">
                                <input type="hidden" name="attendance_date" id="attendanceQuickTimeLeaveDate">

                                <div class="mb-3">
                                    <label for="attendanceQuickTimeLeaveFrom" class="form-label">{{ __('index.from') }}</label>
                                    <input type="time" class="form-control" name="leave_from" id="attendanceQuickTimeLeaveFrom" required>
                                </div>

                                <div class="mb-3">
                                    <label for="attendanceQuickTimeLeaveTo" class="form-label">{{ __('index.to') }}</label>
                                    <input type="time" class="form-control" name="leave_to" id="attendanceQuickTimeLeaveTo" required>
                                    <small class="text-muted d-block mt-2" id="attendanceQuickTimeLeaveHelpText">
                                        This will create an already approved time leave for the selected attendance day.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="attendanceQuickTimeLeaveReason" class="form-label">{{ __('index.leave_reason') }}</label>
                                    <textarea class="form-control" name="reasons" id="attendanceQuickTimeLeaveReason" rows="3" minlength="10" required placeholder="Required note"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm" id="attendanceQuickTimeLeaveSubmit">
                                    Save as Approved Time Leave
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('view_employee_chat')
            <div class="modal fade" id="attendanceChatModal" tabindex="-1" aria-labelledby="attendanceChatModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <img id="attendanceChatAvatar" src="{{ asset('assets/images/img.png') }}" alt="Employee avatar" class="monthly-avatar">
                                <div class="min-w-0">
                                    <h5 class="modal-title" id="attendanceChatModalLabel">Employee Chat</h5>
                                    <small class="text-muted" id="attendanceChatSubtitle">Open a conversation from monthly attendance.</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="attendanceChatThread" class="attendance-chat-thread" data-base-url="{{ route('admin.employee-chat.messages') }}">
                                <div class="chat-empty">Select an employee to start chatting.</div>
                            </div>
                        </div>
                        <div class="modal-footer d-block">
                            @can('send_employee_chat')
                                <form id="attendanceChatForm" class="attendance-chat-form" action="{{ route('admin.employee-chat.store') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="employee_id" id="attendanceChatEmployeeId">
                                    <label class="attendance-chat-attach" title="Attach file">
                                        <i data-feather="paperclip"></i>
                                        <input type="file" name="attachment" id="attendanceChatAttachment">
                                    </label>
                                    <input type="text" class="attendance-chat-input" name="message" id="attendanceChatMessage" placeholder="Type your message">
                                    <button type="submit" class="btn btn-primary btn-sm">Send</button>
                                </form>
                                <div class="attendance-chat-status-text mt-2" id="attendanceChatStatusText">You can send text or attach a file here.</div>
                            @else
                                <div class="attendance-chat-status-text" id="attendanceChatStatusText">You have view access only. Chat sending is disabled for your role.</div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('monthlyAttendanceDetailModal');
            const detailModal = modal ? new bootstrap.Modal(modal) : null;
            const quickLeaveModalElement = document.getElementById('attendanceQuickLeaveModal');
            const quickLeaveModal = quickLeaveModalElement ? new bootstrap.Modal(quickLeaveModalElement) : null;
            const quickTimeLeaveModalElement = document.getElementById('attendanceQuickTimeLeaveModal');
            const quickTimeLeaveModal = quickTimeLeaveModalElement ? new bootstrap.Modal(quickTimeLeaveModalElement) : null;
            const statusModalElement = document.getElementById('attendanceLeaveStatusUpdate');
            const statusModal = statusModalElement ? new bootstrap.Modal(statusModalElement) : null;
            const signalDetailModalElement = document.getElementById('monthlySignalDetailModal');
            const signalDetailModal = signalDetailModalElement ? new bootstrap.Modal(signalDetailModalElement) : null;
            const chatModalElement = document.getElementById('attendanceChatModal');
            const chatModal = chatModalElement ? new bootstrap.Modal(chatModalElement) : null;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const signalToggle = document.getElementById('monthlySignalToggle');
            const signalColumns = () => document.querySelectorAll('.monthly-signal-column');

            const setSignalColumnsVisible = (visible) => {
                signalColumns().forEach((column) => {
                    column.classList.toggle('is-hidden', !visible);
                });

                if (signalToggle) {
                    signalToggle.setAttribute('aria-pressed', visible ? 'true' : 'false');
                    const label = signalToggle.querySelector('span');
                    if (label) {
                        label.textContent = visible ? 'Hide Signals' : 'Show Signals';
                    }
                }

                localStorage.setItem('monthlyAttendanceSignalsVisible', visible ? '1' : '0');
            };

            if (signalToggle) {
                setSignalColumnsVisible(localStorage.getItem('monthlyAttendanceSignalsVisible') !== '0');
                signalToggle.addEventListener('click', function () {
                    const visible = signalToggle.getAttribute('aria-pressed') !== 'true';
                    setSignalColumnsVisible(visible);
                });
            }

            const setQuickActionData = (button, trigger) => {
                if (!button || !trigger) {
                    return;
                }

                ['user-id', 'user-name', 'attendance-date', 'display-date', 'fetch-url', 'employee-id', 'employee-name', 'employee-avatar', 'employee-subtitle', 'employee-online'].forEach((key) => {
                    const value = trigger.getAttribute(`data-${key}`);
                    if (value !== null) {
                        button.setAttribute(`data-${key}`, value);
                    }
                });
            };

            const readJsonData = (element, attribute, fallback = []) => {
                try {
                    return JSON.parse(element.getAttribute(attribute) || JSON.stringify(fallback));
                } catch (error) {
                    return fallback;
                }
            };

            const openMonthlyDayDetail = (trigger, showModal = true) => {
                if (!trigger || !detailModal) {
                    return;
                }

                const employee = trigger.getAttribute('data-employee') || 'Employee';
                const employeeCode = trigger.getAttribute('data-employee-code') || '';
                const date = trigger.getAttribute('data-date') || '';
                const details = readJsonData(trigger, 'data-details');
                const actions = readJsonData(trigger, 'data-actions');
                const detailUrl = trigger.getAttribute('data-detail-url') || '#';
                const canQuickLeave = trigger.getAttribute('data-can-quick-leave') === '1';
                const canQuickTimeLeave = trigger.getAttribute('data-can-quick-time-leave') === '1';

                document.getElementById('monthlyAttendanceDetailModalLabel').textContent = `${employee} ${employeeCode}`;
                document.getElementById('monthlyAttendanceDetailSubtitle').textContent = date;
                document.getElementById('monthlyAttendanceDetailLink').setAttribute('href', detailUrl);

                const list = document.getElementById('monthlyAttendanceDetailList');
                list.innerHTML = '';
                details.forEach(function (detail) {
                    const item = document.createElement('li');
                    item.textContent = detail;
                    list.appendChild(item);
                });

                renderRequestActions(actions);

                const quickLeaveButton = document.getElementById('monthlyQuickLeaveButton');
                const quickTimeLeaveButton = document.getElementById('monthlyQuickTimeLeaveButton');
                const quickChatButton = document.getElementById('monthlyQuickChatButton');

                if (quickLeaveButton) {
                    quickLeaveButton.classList.toggle('d-none', !canQuickLeave);
                    setQuickActionData(quickLeaveButton, trigger);
                    quickLeaveButton.setAttribute('data-user-name', employee);
                }

                if (quickTimeLeaveButton) {
                    quickTimeLeaveButton.classList.toggle('d-none', !canQuickTimeLeave);
                    setQuickActionData(quickTimeLeaveButton, trigger);
                    quickTimeLeaveButton.setAttribute('data-user-name', employee);
                }

                if (quickChatButton) {
                    quickChatButton.setAttribute('data-employee-id', trigger.getAttribute('data-user-id') || '');
                    quickChatButton.setAttribute('data-employee-name', employee);
                    quickChatButton.setAttribute('data-employee-avatar', trigger.getAttribute('data-employee-avatar') || '');
                    quickChatButton.setAttribute('data-employee-subtitle', trigger.getAttribute('data-employee-subtitle') || employeeCode);
                    quickChatButton.setAttribute('data-employee-online', trigger.getAttribute('data-employee-online') || '0');
                }

                if (showModal) {
                    detailModal.show();
                }
            };

            const buildRequestActionButton = (action, status, label, className) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `btn btn-xs ${className} ${action.type === 'time_leave' ? 'attendanceTimeLeaveRequestUpdate' : 'attendanceLeaveRequestUpdate'}`;
                button.textContent = label;
                button.dataset.href = action.update_url || '#';
                button.dataset.status = status;
                button.dataset.remark = action.remark || '';
                button.dataset.reason = action.reason || 'N/A';
                button.dataset.id = action.id || '';
                button.dataset.label = action.title || 'Leave Request';
                button.dataset.approversUrl = action.approvers_url || '';
                return button;
            };

            const renderRequestActions = (actions) => {
                const container = document.getElementById('monthlyAttendanceRequestActions');
                if (!container) {
                    return;
                }

                container.innerHTML = '';
                actions.forEach((action) => {
                    const card = document.createElement('div');
                    card.className = 'monthly-detail-action-card';

                    const title = document.createElement('strong');
                    title.textContent = action.title || 'Pending Request';
                    card.appendChild(title);

                    const reason = document.createElement('p');
                    reason.textContent = action.reason || 'N/A';
                    card.appendChild(reason);

                    const buttons = document.createElement('div');
                    buttons.className = 'd-flex flex-wrap gap-1';

                    if (action.type === 'time_leave') {
                        @can('update_time_leave')
                            buttons.appendChild(buildRequestActionButton(action, '{{ \App\Enum\LeaveStatusEnum::approved->value }}', '{{ __('index.approve') }}', 'btn-success'));
                            buttons.appendChild(buildRequestActionButton(action, '{{ \App\Enum\LeaveStatusEnum::rejected->value }}', '{{ __('index.reject') }}', 'btn-danger'));
                        @endcan
                    } else {
                        @canany(['update_leave_request', 'access_admin_leave'])
                            buttons.appendChild(buildRequestActionButton(action, '{{ \App\Enum\LeaveStatusEnum::approved->value }}', '{{ __('index.approve') }}', 'btn-success'));
                            buttons.appendChild(buildRequestActionButton(action, '{{ \App\Enum\LeaveStatusEnum::rejected->value }}', '{{ __('index.reject') }}', 'btn-danger'));
                        @endcanany
                    }

                    if (buttons.children.length) {
                        card.appendChild(buttons);
                        container.appendChild(card);
                    }
                });
            };

            if (modal) {
                modal.addEventListener('show.bs.modal', function (event) {
                    if (event.relatedTarget) {
                        openMonthlyDayDetail(event.relatedTarget, false);
                    }
                });
            }

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.monthly-signal-button');
                if (!element || element.disabled || !signalDetailModal) {
                    return;
                }

                const row = element.closest('tr');
                const signal = element.getAttribute('data-signal');
                const signalTitle = element.getAttribute('data-signal-title') || 'Signal Detail';
                const dayButtons = Array.from(row?.querySelectorAll('.monthly-day-link') || []);
                const matchedDays = dayButtons.filter((dayButton) => {
                    return readJsonData(dayButton, 'data-indicators').some((indicator) => indicator.short === signal);
                });

                document.getElementById('monthlySignalDetailModalLabel').textContent = signalTitle;
                document.getElementById('monthlySignalDetailSubtitle').textContent = matchedDays[0]?.getAttribute('data-employee') || '';

                const list = document.getElementById('monthlySignalDayList');
                list.innerHTML = '';

                matchedDays.forEach((dayButton) => {
                    const item = document.createElement('div');
                    item.className = 'monthly-signal-day-item';

                    const text = document.createElement('div');
                    const title = document.createElement('strong');
                    title.textContent = dayButton.getAttribute('data-display-date') || dayButton.getAttribute('data-date') || '';
                    const subtitle = document.createElement('span');
                    subtitle.textContent = dayButton.getAttribute('data-status') || signalTitle;
                    text.appendChild(title);
                    text.appendChild(subtitle);

                    const actionButton = document.createElement('button');
                    actionButton.type = 'button';
                    actionButton.className = 'btn btn-primary btn-xs';
                    actionButton.textContent = 'View / Quick';
                    actionButton.addEventListener('click', function () {
                        signalDetailModal.hide();
                        openMonthlyDayDetail(dayButton);
                    });

                    item.appendChild(text);
                    item.appendChild(actionButton);
                    list.appendChild(item);
                });

                if (!matchedDays.length) {
                    list.innerHTML = '<div class="text-muted small">No matching days found.</div>';
                }

                signalDetailModal.show();
            });

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.monthly-total-button');
                if (!element || element.disabled || !signalDetailModal) {
                    return;
                }

                const row = element.closest('tr');
                const status = element.getAttribute('data-total-status');
                const titleText = element.getAttribute('data-total-title') || 'Attendance Detail';
                const dayButtons = Array.from(row?.querySelectorAll('.monthly-day-link') || []);
                const matchedDays = status === 'all'
                    ? dayButtons
                    : dayButtons.filter((dayButton) => dayButton.getAttribute('data-status-key') === status);

                document.getElementById('monthlySignalDetailModalLabel').textContent = titleText;
                document.getElementById('monthlySignalDetailSubtitle').textContent = matchedDays[0]?.getAttribute('data-employee') || '';

                const list = document.getElementById('monthlySignalDayList');
                list.innerHTML = '';

                matchedDays.forEach((dayButton) => {
                    const item = document.createElement('div');
                    item.className = 'monthly-signal-day-item';

                    const text = document.createElement('div');
                    const title = document.createElement('strong');
                    title.textContent = dayButton.getAttribute('data-display-date') || dayButton.getAttribute('data-date') || '';
                    const subtitle = document.createElement('span');
                    subtitle.textContent = dayButton.getAttribute('data-status') || titleText;
                    text.appendChild(title);
                    text.appendChild(subtitle);

                    const actionButton = document.createElement('button');
                    actionButton.type = 'button';
                    actionButton.className = 'btn btn-primary btn-xs';
                    actionButton.textContent = 'View / Quick';
                    actionButton.addEventListener('click', function () {
                        signalDetailModal.hide();
                        openMonthlyDayDetail(dayButton);
                    });

                    item.appendChild(text);
                    item.appendChild(actionButton);
                    list.appendChild(item);
                });

                if (!matchedDays.length) {
                    list.innerHTML = '<div class="text-muted small">No matching days found.</div>';
                }

                signalDetailModal.show();
            });

            const resetQuickLeaveOptions = (message = 'Loading leave types...') => {
                const type = document.getElementById('attendanceQuickLeaveType');
                const submit = document.getElementById('attendanceQuickLeaveSubmit');
                if (!type) {
                    return;
                }
                type.innerHTML = `<option value="">${message}</option>`;
                type.disabled = true;
                if (submit) {
                    submit.disabled = true;
                }
            };

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.quickApproveLeaveTrigger');
                if (!element || !quickLeaveModal) {
                    return;
                }

                event.preventDefault();
                detailModal?.hide();

                const userId = element.getAttribute('data-user-id');
                const userName = element.getAttribute('data-user-name') || 'Employee';
                const attendanceDate = element.getAttribute('data-attendance-date');
                const displayDate = element.getAttribute('data-display-date') || attendanceDate;
                const fetchUrl = element.getAttribute('data-fetch-url');

                document.getElementById('attendanceQuickLeaveUserId').value = userId;
                document.getElementById('attendanceQuickLeaveDate').value = attendanceDate;
                document.getElementById('attendanceQuickLeaveReason').value = '';
                document.getElementById('attendanceQuickLeaveModalLabel').textContent = `Quick Leave: ${userName}`;
                document.getElementById('attendanceQuickLeaveHelpText').textContent = `Create an already approved leave for ${displayDate}.`;

                resetQuickLeaveOptions();
                quickLeaveModal.show();

                fetch(fetchUrl)
                    .then(response => response.json())
                    .then(data => {
                        const leaveTypes = data.leaveTypes || data.leveTypes || [];
                        const type = document.getElementById('attendanceQuickLeaveType');
                        const submit = document.getElementById('attendanceQuickLeaveSubmit');

                        if (!leaveTypes.length) {
                            resetQuickLeaveOptions('No leave types available');
                            return;
                        }

                        type.disabled = false;
                        type.innerHTML = '<option value="">Select leave type</option>';
                        leaveTypes.forEach((leaveType) => {
                            const option = document.createElement('option');
                            option.value = leaveType.id;
                            option.textContent = leaveType.name;
                            type.appendChild(option);
                        });
                        type.value = String(leaveTypes[0].id);
                        if (submit) {
                            submit.disabled = false;
                        }
                    })
                    .catch(() => resetQuickLeaveOptions('Unable to load leave types'));
            });

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.quickApproveTimeLeaveTrigger');
                if (!element || !quickTimeLeaveModal) {
                    return;
                }

                event.preventDefault();
                detailModal?.hide();

                const userName = element.getAttribute('data-user-name') || 'Employee';
                const attendanceDate = element.getAttribute('data-attendance-date');
                const displayDate = element.getAttribute('data-display-date') || attendanceDate;

                document.getElementById('attendanceQuickTimeLeaveUserId').value = element.getAttribute('data-user-id');
                document.getElementById('attendanceQuickTimeLeaveDate').value = attendanceDate;
                document.getElementById('attendanceQuickTimeLeaveFrom').value = '';
                document.getElementById('attendanceQuickTimeLeaveTo').value = '';
                document.getElementById('attendanceQuickTimeLeaveReason').value = '';
                document.getElementById('attendanceQuickTimeLeaveModalLabel').textContent = `Quick Time Leave: ${userName}`;
                document.getElementById('attendanceQuickTimeLeaveHelpText').textContent = `Create an already approved time leave for ${displayDate}.`;
                quickTimeLeaveModal.show();
            });

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.attendanceLeaveRequestUpdate, .attendanceTimeLeaveRequestUpdate');
                if (!element || !statusModal) {
                    return;
                }

                event.preventDefault();
                detailModal?.hide();

                document.getElementById('attendanceLeaveStatusUpdateTitle').textContent = element.dataset.label || 'Leave Request';
                document.getElementById('attendanceUpdateLeaveStatus').setAttribute('action', element.dataset.href || '#');
                document.getElementById('attendanceLeaveStatus').value = element.dataset.status || '{{ \App\Enum\LeaveStatusEnum::approved->value }}';
                document.getElementById('attendanceLeaveRemark').value = element.dataset.remark || '';
                document.getElementById('attendanceLeaveStatusReason').textContent = element.dataset.reason || 'N/A';
                document.getElementById('attendancePreviousApprovers').innerHTML = '';

                if (element.classList.contains('attendanceLeaveRequestUpdate') && element.dataset.approversUrl) {
                    fetch(element.dataset.approversUrl)
                        .then(response => response.json())
                        .then(response => {
                            if (!response.success) {
                                return;
                            }

                            const approvers = response.data.approval_data || [];
                            document.getElementById('attendancePreviousApprovers').innerHTML = approvers.map((approver) => `
                                <div class="border rounded p-2 mb-2 small">
                                    <b>Approver:</b> ${approver.approved_by_name || 'N/A'}<br>
                                    <b>Status:</b> ${approver.status || 'N/A'}<br>
                                    <b>Remark:</b> ${approver.reason || 'N/A'}
                                </div>
                            `).join('');
                        })
                        .catch(() => {});
                }

                statusModal.show();
            });

            const chatThread = document.getElementById('attendanceChatThread');
            const chatForm = document.getElementById('attendanceChatForm');
            const chatEmployeeId = document.getElementById('attendanceChatEmployeeId');
            const chatAttachment = document.getElementById('attendanceChatAttachment');
            const chatStatusText = document.getElementById('attendanceChatStatusText');
            let activeChatEmployeeId = null;
            let chatPoller = null;

            const chatMessagesUrl = (employeeId) => {
                const url = new URL(chatThread.dataset.baseUrl, window.location.origin);
                url.searchParams.set('employee_id', employeeId);
                return url.toString();
            };

            const scrollChatToBottom = () => {
                if (chatThread) {
                    chatThread.scrollTop = chatThread.scrollHeight;
                }
            };

            const renderChatMessages = async (employeeId) => {
                if (!chatThread || !employeeId) {
                    return;
                }

                try {
                    const response = await fetch(chatMessagesUrl(employeeId), {
                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to load chat messages.');
                    }
                    chatThread.innerHTML = data.html;
                    scrollChatToBottom();
                } catch (error) {
                    if (chatStatusText) {
                        chatStatusText.textContent = error.message || 'Unable to load chat messages.';
                    }
                }
            };

            const stopChatPolling = () => {
                if (chatPoller) {
                    clearInterval(chatPoller);
                    chatPoller = null;
                }
            };

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.openAttendanceChat');
                if (!element || !chatModal) {
                    return;
                }

                event.preventDefault();
                detailModal?.hide();

                activeChatEmployeeId = element.getAttribute('data-employee-id');
                if (!activeChatEmployeeId) {
                    return;
                }

                if (chatEmployeeId) {
                    chatEmployeeId.value = activeChatEmployeeId;
                }
                document.getElementById('attendanceChatAvatar')?.setAttribute('src', element.getAttribute('data-employee-avatar') || '{{ asset('assets/images/img.png') }}');
                document.getElementById('attendanceChatModalLabel').textContent = element.getAttribute('data-employee-name') || 'Employee Chat';
                document.getElementById('attendanceChatSubtitle').textContent = element.getAttribute('data-employee-subtitle') || 'Employee';
                if (chatThread) {
                    chatThread.innerHTML = '<div class="chat-empty">Loading conversation...</div>';
                }
                if (chatStatusText) {
                    chatStatusText.textContent = 'Loading messages...';
                }

                chatModal.show();
                renderChatMessages(activeChatEmployeeId);
                stopChatPolling();
                chatPoller = setInterval(() => renderChatMessages(activeChatEmployeeId), 5000);
            });

            if (chatForm) {
                chatForm.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    if (!activeChatEmployeeId) {
                        return;
                    }

                    if (chatStatusText) {
                        chatStatusText.textContent = 'Sending message...';
                    }

                    try {
                        const response = await fetch(chatForm.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: new FormData(chatForm)
                        });
                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Unable to send message.');
                        }

                        chatThread.innerHTML = data.html;
                        chatForm.reset();
                        scrollChatToBottom();
                        if (chatStatusText) {
                            chatStatusText.textContent = 'Message sent successfully.';
                        }
                    } catch (error) {
                        if (chatStatusText) {
                            chatStatusText.textContent = error.message || 'Unable to send message right now.';
                        }
                    }
                });
            }

            chatAttachment?.addEventListener('change', function () {
                if (chatStatusText && this.files?.[0]) {
                    chatStatusText.textContent = `Attachment ready: ${this.files[0].name}`;
                }
            });

            chatModalElement?.addEventListener('hidden.bs.modal', function () {
                stopChatPolling();
                activeChatEmployeeId = null;
                if (chatThread) {
                    chatThread.innerHTML = '<div class="chat-empty">Select an employee to start chatting.</div>';
                }
                chatForm?.reset();
            });
        });
    </script>
@endsection
