@php
    use App\Models\User;
    use Illuminate\Support\Facades\Gate;

    $canViewEmployeeChat = auth('admin')->check() || Gate::allows('view_employee_chat');
    $canSendEmployeeChat = auth('admin')->check() || Gate::allows('send_employee_chat');
    $canViewAttendanceDetail = auth('admin')->check() || Gate::allows('attendance_show');
@endphp
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

        .monthly-results-block {
            position: relative;
        }

        .monthly-results-reload {
            position: absolute;
            inset: 0;
            z-index: 80;
            display: none;
            padding: 24px;
            background: rgba(248, 250, 252, 0.72);
            backdrop-filter: blur(3px);
        }

        .monthly-results-block.is-refreshing .monthly-results-reload {
            display: block;
        }

        .monthly-reload-panel {
            position: sticky;
            top: calc(50vh - 90px);
            width: min(360px, 92%);
            margin: 80px auto;
            padding: 18px 20px;
            border: 1px solid #dbe5f3;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.16);
        }

        .monthly-reload-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 12px;
            color: #172033;
            font-weight: 800;
        }

        .monthly-reload-percent {
            color: #2563eb;
            font-variant-numeric: tabular-nums;
        }

        .monthly-reload-track {
            height: 9px;
            overflow: hidden;
            border-radius: 999px;
            background: #dbeafe;
        }

        .monthly-reload-bar {
            width: 0%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #2563eb, #60a5fa);
            transition: width 0.18s ease;
        }

        .monthly-reload-text {
            margin-top: 10px;
            color: #64748b;
            font-size: 12px;
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

        .monthly-filter-toggle-actions {
            display: flex;
            align-items: center;
            gap: 10px;
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

        .monthly-signal-report-dialog {
            width: 100vw;
            max-width: 100vw;
            height: 100vh;
            margin: 0;
        }

        .monthly-signal-report-dialog .modal-content {
            height: 100%;
            border: 0;
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.24);
        }

        .monthly-signal-report-dialog .modal-header {
            padding: 16px 20px 12px;
            border-bottom: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .monthly-signal-report-dialog .modal-title {
            color: #0f172a;
            font-size: 18px;
            font-weight: 900;
        }

        .monthly-signal-report-dialog #monthlySignalDetailSubtitle {
            display: block;
            margin-top: 4px;
            color: #64748b !important;
            font-size: 12px;
            font-weight: 700;
        }

        .monthly-signal-report-dialog .btn-close {
            width: 20px;
            height: 20px;
            opacity: 0.62;
        }

        .monthly-signal-report-dialog .modal-body {
            min-height: 0;
            padding: 16px 18px 18px;
            overflow: auto;
            background: #ffffff;
        }

        .monthly-signal-report-dialog .monthly-signal-day-list {
            gap: 8px;
        }

        .monthly-signal-report-dialog .monthly-signal-day-item {
            padding: 8px 10px;
            border-color: #e2e8f0;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.035);
        }

        .monthly-signal-report-dialog .monthly-signal-day-item strong {
            font-size: 12px;
            font-weight: 900;
        }

        .monthly-signal-report-dialog .monthly-signal-day-item span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 11px;
        }

        .monthly-signal-report-dialog .monthly-signal-day-item .btn-primary {
            min-width: 82px;
            border: 0;
            border-radius: 8px;
            background: #ef232a;
            box-shadow: 0 6px 14px rgba(239, 35, 42, 0.18);
            font-size: 11px;
            font-weight: 800;
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
            overflow: visible;
        }

        .monthly-attendance-table tbody tr:hover .sticky-number {
            z-index: 55;
        }

        .monthly-row-number {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
        }

        .monthly-row-number-value {
            transition: opacity .15s ease, transform .15s ease;
        }

        .monthly-attendance-table tbody tr:hover .monthly-row-number-value,
        .monthly-row-number:focus-within .monthly-row-number-value {
            opacity: 0;
            transform: scale(.82);
        }

        .monthly-row-chat-badge {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 60;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 0;
            border: 1px solid #bfdbfe;
            border-radius: 50%;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 9px;
            font-weight: 800;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
            opacity: 0;
            visibility: hidden;
            transform: translate(-50%, -50%) scale(.88);
            transition: opacity .15s ease, transform .15s ease, visibility .15s ease;
            cursor: pointer;
        }

        .monthly-row-chat-badge svg {
            width: 13px;
            height: 13px;
        }

        .monthly-attendance-table tbody tr:hover .monthly-row-chat-badge,
        .monthly-row-chat-badge:focus {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, -50%) scale(1);
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
            display: grid;
            gap: 5px;
            width: 210px;
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
        .monthly-employee:hover .monthly-employee-meta,
        .monthly-employee:focus-within .monthly-employee-meta {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }

        .monthly-employee-meta span {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            width: 100%;
            padding: 3px 6px;
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

        .monthly-employee-meta span b {
            color: #64748b;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .monthly-employee-meta span em {
            min-width: 0;
            color: inherit;
            font-style: normal;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .monthly-employee-meta .meta-branch { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .monthly-employee-meta .meta-department { background: #ecfdf3; color: #15803d; border-color: #bbf7d0; }
        .monthly-employee-meta .meta-position { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
        .monthly-employee-meta .meta-shift { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
        .monthly-employee-meta .meta-phone { background: #f0fdfa; color: #0f766e; border-color: #99f6e4; }

        .monthly-employee-detail-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            width: 100%;
            min-height: 28px;
            padding: 5px 8px;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 9px;
            font-weight: 800;
            line-height: 1.1;
            text-decoration: none;
            text-transform: uppercase;
        }

        .monthly-employee-detail-link:hover,
        .monthly-employee-detail-link:focus {
            background: #e0e7ff;
            color: #312e81;
            text-decoration: none;
        }

        .monthly-employee-detail-link svg {
            width: 12px;
            height: 12px;
        }

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

        .monthly-late-breakdown {
            display: block;
            margin: 0 0 10px;
            padding: 0;
            color: #0f172a;
            min-width: 0;
        }

        .monthly-late-report-head {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 10px;
        }

        .monthly-late-report-card {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 5px 14px rgba(15, 23, 42, 0.045);
        }

        .monthly-late-report-card-icon {
            flex: 0 0 auto;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff7ed;
            color: #f97316;
            font-size: 14px;
            font-weight: 900;
        }

        .monthly-late-report-card small {
            display: block;
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
        }

        .monthly-late-report-card strong {
            display: block;
            margin-top: 3px;
            color: #0f172a;
            font-size: 19px;
            line-height: 1.05;
        }

        .monthly-late-report-note {
            margin: 0 0 12px;
            color: #334155;
            font-size: 12px;
        }

        .monthly-late-report-note b {
            color: #dc2626;
        }

        .monthly-late-report-table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .monthly-late-report-table {
            width: 100%;
            min-width: 780px;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border: 0;
            font-size: 13px;
        }

        .monthly-late-report-table th,
        .monthly-late-report-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .monthly-late-report-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 12px;
            font-weight: 900;
        }

        .monthly-late-report-table tr:last-child td {
            border-bottom: 0;
        }

        .monthly-late-minutes-cell {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
        }

        .monthly-late-dot {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fee2e2;
            color: #dc2626;
            font-size: 11px;
            font-weight: 900;
        }

        .monthly-late-dot.is-20 { background: #ffedd5; color: #ea580c; }
        .monthly-late-dot.is-30 { background: #fef3c7; color: #d97706; }
        .monthly-late-dot.is-40 { background: #dcfce7; color: #16a34a; }
        .monthly-late-dot.is-50 { background: #cffafe; color: #0891b2; }
        .monthly-late-dot.is-60 { background: #f3e8ff; color: #7c3aed; }
        .monthly-late-dot.is-120 { background: #fee2e2; color: #b91c1c; }

        .monthly-late-count-pill {
            display: inline-flex;
            justify-content: center;
            min-width: 64px;
            padding: 7px 10px;
            border-radius: 6px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 14px;
            font-weight: 900;
        }

        .monthly-late-payment-pill {
            display: inline-flex;
            justify-content: center;
            min-width: 74px;
            padding: 7px 10px;
            border-radius: 6px;
            background: #dcfce7;
            color: #15803d;
            font-size: 14px;
            font-weight: 900;
        }

        .monthly-late-report-total td {
            background: #eff6ff;
            font-size: 13px;
            font-weight: 900;
        }

        #monthlySignalDetailModal.is-late-report .modal-header {
            display: none;
        }

        #monthlySignalDetailModal.is-late-report .monthly-signal-report-dialog {
            height: auto;
            min-height: 0;
        }

        #monthlySignalDetailModal.is-late-report .modal-content {
            height: auto;
            max-height: 100vh;
        }

        #monthlySignalDetailModal.is-late-report .modal-body {
            padding: 18px 22px 22px;
        }

        .late-dashboard-section {
            margin: 0;
            padding: 0;
            background: #ffffff;
            min-height: 0;
        }

        .late-dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .late-dashboard-header > div:first-child {
            min-width: 0;
        }

        .late-dashboard-title {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 0;
        }

        .late-dashboard-subtitle {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }

        .late-dashboard-actions {
            display: flex;
            flex-wrap: nowrap;
            justify-content: flex-end;
            flex: 0 0 auto;
        }

        .late-dashboard-action {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 32px;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .late-dashboard-action:hover {
            color: #0f172a;
            background: #f8fafc;
            text-decoration: none;
        }

        .late-dashboard-action.is-close {
            width: 32px;
            padding: 0;
            justify-content: center;
        }

        .late-dashboard-cards {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .late-dashboard-card {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055);
        }

        .late-dashboard-card > div {
            flex: 1 1 auto;
            min-width: 0;
        }

        .late-dashboard-icon {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .late-dashboard-icon svg {
            width: 18px;
            height: 18px;
        }

        .late-dashboard-icon.is-green { background: #dcfce7; color: #16a34a; }
        .late-dashboard-icon.is-amber { background: #fef3c7; color: #d97706; }
        .late-dashboard-icon.is-orange { background: #ffedd5; color: #f97316; }
        .late-dashboard-icon.is-purple { background: #f3e8ff; color: #7c3aed; }
        .late-dashboard-icon.is-blue { background: #dbeafe; color: #2563eb; }

        .late-dashboard-card-label {
            margin: 0;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: normal;
            word-break: normal;
        }

        .late-dashboard-card-value {
            margin: 2px 0 0;
            color: #0f172a;
            font-size: 14px;
            font-weight: 900;
            line-height: 1.18;
            overflow-wrap: normal;
            word-break: normal;
        }

        .late-dashboard-card-note {
            margin: 2px 0 0;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 700;
            line-height: 1.25;
            overflow-wrap: normal;
            word-break: normal;
        }

        .late-dashboard-info {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            padding: 8px 10px;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            background: #f8fbff;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }

        .late-dashboard-info svg {
            width: 16px;
            height: 16px;
            color: #2563eb;
            flex: 0 0 auto;
        }

        .late-dashboard-info strong {
            color: #dc2626;
        }

        .late-dashboard-table-wrap {
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .late-dashboard-table {
            width: 100%;
            min-width: 0;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            background: #ffffff;
        }

        .late-dashboard-table th,
        .late-dashboard-table td {
            padding: 9px 11px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: middle;
            overflow-wrap: anywhere;
            font-size: 12px;
        }

        .late-dashboard-table th:nth-child(1),
        .late-dashboard-table td:nth-child(1) {
            width: 22%;
        }

        .late-dashboard-table th:nth-child(2),
        .late-dashboard-table td:nth-child(2) {
            width: 24%;
        }

        .late-dashboard-table th:nth-child(3),
        .late-dashboard-table td:nth-child(3) {
            width: 31%;
        }

        .late-dashboard-table th:nth-child(4),
        .late-dashboard-table td:nth-child(4) {
            width: 10%;
            text-align: center;
        }

        .late-dashboard-table th:nth-child(5),
        .late-dashboard-table td:nth-child(5) {
            width: 13%;
            text-align: center;
        }

        .late-dashboard-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: linear-gradient(135deg, #16233b 0%, #233555 100%);
            color: #ffffff;
            font-size: 11px;
            font-weight: 900;
            line-height: 1.25;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .late-dashboard-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .late-dashboard-table tbody tr:hover td {
            background: #fff7ed;
        }

        .late-dashboard-table tr:last-child td {
            border-bottom: 0;
        }

        .late-dashboard-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            padding: 5px 7px;
            border-radius: 7px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
        }

        .late-dashboard-count.is-20 { background: #ffedd5; color: #ea580c; }
        .late-dashboard-count.is-30 { background: #fef3c7; color: #d97706; }
        .late-dashboard-count.is-40 { background: #dcfce7; color: #16a34a; }
        .late-dashboard-count.is-50 { background: #e0f2fe; color: #0284c7; }
        .late-dashboard-count.is-60 { background: #f3e8ff; color: #7c3aed; }
        .late-dashboard-count.is-120 { background: #fee2e2; color: #b91c1c; }
        .late-dashboard-grand .late-dashboard-count { background: #dbeafe; color: #1d4ed8; }

        .late-dashboard-payment {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 64px;
            padding: 5px 7px;
            border-radius: 7px;
            background: #dcfce7;
            color: #15803d;
            font-size: 12px;
            font-weight: 900;
        }

        .late-dashboard-grand td {
            background: #eff6ff !important;
            color: #0f172a;
            font-size: 13px;
            font-weight: 900;
        }

        .late-dashboard-note {
            margin: 0;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
        }

        .late-dashboard-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 10px;
        }

        @media (max-width: 767.98px) {
            .monthly-late-report-head {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1199.98px) {
            .late-dashboard-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .late-dashboard-card-value {
                font-size: 13px;
            }

            .late-dashboard-table th,
            .late-dashboard-table td {
                padding: 8px 7px;
            }

            .late-dashboard-table th {
                font-size: 10px;
            }
        }

        @media (max-width: 767.98px) {
            .late-dashboard-table-wrap {
                overflow-x: auto;
            }

            .late-dashboard-table {
                min-width: 720px;
            }

            .monthly-signal-report-dialog .modal-body {
                padding: 12px;
            }

            #monthlySignalDetailModal.is-late-report .modal-body {
                padding: 12px;
            }

            .late-dashboard-section {
                padding: 0;
            }

            .late-dashboard-header {
                flex-direction: row;
                align-items: flex-start;
            }

            .late-dashboard-actions {
                width: auto;
                justify-content: flex-end;
            }

            .late-dashboard-action {
                flex: 0 0 auto;
                justify-content: center;
            }

            .late-dashboard-action.is-close {
                flex: 0 0 42px;
            }

            .late-dashboard-cards {
                grid-template-columns: 1fr;
            }

            .late-dashboard-table th,
            .late-dashboard-table td {
                padding: 8px 6px;
            }

            .monthly-late-minutes-cell {
                gap: 6px;
            }

            .monthly-late-dot {
                width: 24px;
                height: 24px;
                flex: 0 0 24px;
            }

            .late-dashboard-count,
            .late-dashboard-payment {
                min-width: 0;
                width: 100%;
                padding: 5px 4px;
                font-size: 11px;
            }
        }

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

        .monthly-scroll-shortcuts {
            position: fixed;
            right: 22px;
            bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1040;
        }

        .monthly-scroll-shortcut {
            width: 44px;
            height: 44px;
            border: 1px solid #d6e1f0;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.94);
            color: #3b4d73;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .monthly-scroll-shortcut:hover {
            transform: translateY(-1px);
            border-color: #9db4da;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.16);
        }

        .monthly-scroll-shortcuts.is-hidden {
            display: none;
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

            .monthly-scroll-shortcuts {
                right: 14px;
                bottom: 14px;
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

        <div id="monthlyResultsBlock" class="monthly-results-block">
        <div class="monthly-results-reload" aria-hidden="true">
            <div class="monthly-reload-panel" role="status" aria-live="polite">
                <div class="monthly-reload-header">
                    <span>Reloading monthly attendance</span>
                    <span class="monthly-reload-percent">0%</span>
                </div>
                <div class="monthly-reload-track">
                    <div class="monthly-reload-bar"></div>
                </div>
                <div class="monthly-reload-text">Refreshing monthly table data...</div>
            </div>
        </div>

        <div class="monthly-filter-shell mb-3">
            <div class="monthly-filter-toggle">
                <div class="monthly-filter-toggle-actions">
                    <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#monthlyAttendanceFilters" aria-expanded="true" aria-controls="monthlyAttendanceFilters">
                        <i data-feather="filter"></i>
                        <span>Filters</span>
                        <i data-feather="chevron-down"></i>
                    </button>
                    <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#monthlyAttendanceSummary" aria-expanded="false" aria-controls="monthlyAttendanceSummary">
                        <i data-feather="bar-chart-2"></i>
                        <span>Summary</span>
                        <i data-feather="chevron-down"></i>
                    </button>
                </div>
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

                <div class="monthly-filter-select">
                    <div class="monthly-inline-field">
                        <label class="monthly-filter-label" for="shift_id">Shift</label>
                        <select class="form-select" id="shift_id" name="shift_id">
                            <option value="">All Shifts</option>
                            @foreach($shifts as $shift)
                                @php
                                    $shiftText = $shift->shift ?: trim(($shift->opening_time ?: '') . ' - ' . ($shift->closing_time ?: ''));
                                @endphp
                                <option value="{{ $shift->id }}" @selected((string) $filter['shift_id'] === (string) $shift->id)>{{ $shiftText ?: 'Shift #' . $shift->id }}</option>
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

            <div class="collapse p-2 pt-0" id="monthlyAttendanceSummary">
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

                <div class="row g-2">
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
                    @if($filter['shift_id'])
                        <input type="hidden" name="shift_id" value="{{ $filter['shift_id'] }}">
                    @endif

                    @canany(['attendance_csv_export', 'monthly_attendance_csv_export'])
                        <a class="btn btn-success" href="{{ request()->fullUrlWithQuery(['export' => 'reduc_xlsx']) }}">
                            <i class="link-icon" data-feather="file-text"></i> Export Reduc XLSX
                        </a>
                    @endcanany

                    <div class="monthly-inline-field">
                        <label class="monthly-filter-label" for="table_per_page">Rows</label>
                        <select class="form-select monthly-table-rows" id="table_per_page" name="per_page">
                            @foreach([10, 25, 50, 100] as $perPage)
                                <option value="{{ $perPage }}" @selected($filter['per_page'] === $perPage)>{{ $perPage }}</option>
                            @endforeach
                            <option value="all" @selected($filter['per_page'] === 'all')>All</option>
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
                            <td class="sticky-number">
                                <span class="monthly-row-number">
                                    <span class="monthly-row-number-value">{{ $monthlyRows->firstItem() + $loop->index }}</span>
                                    @if($canViewEmployeeChat)
                                        <button type="button"
                                                class="monthly-row-chat-badge openAttendanceChat"
                                                title="Quick chat with {{ $employee->name }}"
                                                aria-label="Quick chat with {{ $employee->name }}"
                                                data-employee-id="{{ $employee->id }}"
                                                data-employee-name="{{ $employee->name }}"
                                                data-employee-avatar="{{ $avatar }}"
                                                data-employee-subtitle="{{ trim(($employee->department?->dept_name ?: 'No department') . ' - ' . ($employee->branch?->name ?: 'No branch')) }}"
                                                data-employee-online="{{ (int) ($employee->online_status ?? 0) }}">
                                            <i data-feather="message-circle"></i>
                                        </button>
                                    @endif
                                </span>
                            </td>
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
                                                <b>Branch</b>
                                                <em>{{ $employee->branch?->name ?: 'No branch' }}</em>
                                            </span>
                                            <span class="meta-department" title="Department: {{ $employee->department?->dept_name ?: 'No department' }}">
                                                <b>Dept</b>
                                                <em>{{ $employee->department?->dept_name ?: 'No department' }}</em>
                                            </span>
                                            <span class="meta-position" title="Position: {{ $employee->post?->post_name ?: 'No position' }}">
                                                <b>Position</b>
                                                <em>{{ $employee->post?->post_name ?: 'No position' }}</em>
                                            </span>
                                            <span class="meta-shift" title="Shift: {{ $shiftLabel ?: 'No shift' }}">
                                                <b>Shift</b>
                                                <em>{{ $shiftLabel ?: 'No shift' }}</em>
                                            </span>
                                            <span class="meta-phone" title="Phone: {{ $employee->phone ?: 'No phone' }}">
                                                <b>Phone</b>
                                                <em>{{ $employee->phone ?: 'No phone' }}</em>
                                            </span>
                                            @if($canViewAttendanceDetail)
                                                <a class="monthly-employee-detail-link"
                                                   href="{{ route('admin.attendances.show', ['attendance' => $employee->id, 'year' => $month->format('Y'), 'month' => (int) $month->format('n')]) }}"
                                                   title="View {{ $employee->name }} detail for {{ $month->format('F Y') }}">
                                                    <i data-feather="eye"></i>
                                                    View Detail
                                                </a>
                                            @endif
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
                                            data-attendance-date="{{ $day['date'] }}"
                                            data-display-date="{{ \Carbon\Carbon::parse($day['date'])->format('M d, Y') }}"
                                            data-status-key="{{ $day['status'] }}"
                                            data-status="{{ $day['label'] }}"
                                            data-details='@json($day["details"] ?? array_merge([$day["label"]], array_column($day["indicators"] ?? [], "label")))'
                                            data-indicators='@json($day["indicators"] ?? [])'
                                            data-actions='@json($day["actions"] ?? [])'
                                            data-can-quick-attendance="{{ $day['status'] === 'absent' && !empty($day['can_quick_leave']) ? '1' : '0' }}"
                                            data-can-quick-leave="{{ !empty($day['can_quick_leave']) ? '1' : '0' }}"
                                            data-can-quick-time-leave="{{ !empty($day['can_quick_time_leave']) ? '1' : '0' }}"
                                            data-fetch-url="{{ route('admin.leaves.employee-data', $employee->id) }}"
                                            data-attendance-store-url="{{ route('admin.attendances.store') }}"
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
                                <button type="button"
                                        class="monthly-total-button"
                                        data-total-status="late"
                                        data-total-title="Late"
                                        data-late-breakdown='@json($row['late_breakdown'] ?? [])'
                                        data-late-total="{{ array_sum($row['late_breakdown'] ?? []) }}"
                                        data-late-minutes-total="{{ $row['late_minutes_total'] ?? 0 }}"
                                        data-opening-time="{{ $employee->officeTime?->opening_time ? \Carbon\Carbon::parse($employee->officeTime->opening_time)->format('h:i A') : 'N/A' }}"
                                        data-late-after="{{ $employee->officeTime?->opening_time ? \Carbon\Carbon::parse($employee->officeTime->opening_time)->addMinutes(\App\Http\Controllers\Web\AttendanceMonthlyController::LATE_CHECK_IN_GRACE_MINUTES)->format('h:i A') : 'opening + 16m' }}"
                                         data-total-employees="{{ $summary['employees'] }}"
                                         data-report-subtitle="{{ $employee->department?->dept_name ?: ($employee->branch?->name ?: $employee->name) }}"
                                          data-export-url="{{ request()->fullUrlWithQuery(['export' => 'reduc_xlsx']) }}"
                                        @disabled(($row['totals']['late'] ?? 0) <= 0)>
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
                        @canany(['attendance_create', 'attendance_update'])
                            <button type="button" class="btn btn-outline-success btn-sm quickAttendanceTrigger d-none" id="monthlyQuickAttendanceButton">
                                Quick Attendance
                            </button>
                        @endcanany
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
                        @if($canViewEmployeeChat)
                            <button type="button" class="btn btn-outline-secondary btn-sm openAttendanceChat" id="monthlyQuickChatButton">
                                Quick Chat
                            </button>
                        @endif
                        <a href="#" class="btn btn-primary btn-sm" id="monthlyAttendanceDetailLink">Open Day Detail</a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="monthlySignalDetailModal" tabindex="-1" aria-labelledby="monthlySignalDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered monthly-signal-report-dialog">
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

        @include('admin.attendance.common.create-attendance-form')

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

        @if($canViewEmployeeChat)
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
                            @if($canSendEmployeeChat)
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
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="monthly-scroll-shortcuts" id="monthlyScrollShortcuts">
            <button type="button"
                    class="monthly-scroll-shortcut"
                    id="monthlyScrollTop"
                    title="Go to top"
                    aria-label="Go to top">
                <i data-feather="arrow-up"></i>
            </button>
            <button type="button"
                    class="monthly-scroll-shortcut"
                    id="monthlyScrollBottom"
                    title="Go to bottom"
                    aria-label="Go to bottom">
                <i data-feather="arrow-down"></i>
            </button>
        </div>
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
            const quickAttendanceModalElement = document.getElementById('attendanceCreateForm');
            const quickAttendanceModal = quickAttendanceModalElement ? new bootstrap.Modal(quickAttendanceModalElement) : null;
            const statusModalElement = document.getElementById('attendanceLeaveStatusUpdate');
            const statusModal = statusModalElement ? new bootstrap.Modal(statusModalElement) : null;
            const signalDetailModalElement = document.getElementById('monthlySignalDetailModal');
            const signalDetailModal = signalDetailModalElement ? new bootstrap.Modal(signalDetailModalElement) : null;
            const chatModalElement = document.getElementById('attendanceChatModal');
            const chatModal = chatModalElement ? new bootstrap.Modal(chatModalElement) : null;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const monthlyScrollShortcuts = document.getElementById('monthlyScrollShortcuts');
            const monthlyScrollTopButton = document.getElementById('monthlyScrollTop');
            const monthlyScrollBottomButton = document.getElementById('monthlyScrollBottom');
            const signalColumns = () => document.querySelectorAll('.monthly-signal-column');
            let monthlySearchTimer = null;
            let monthlyRefreshProgressTimer = null;
            let monthlyResultsLoading = false;

            const updateMonthlyScrollShortcuts = () => {
                if (!monthlyScrollShortcuts) {
                    return;
                }

                const scrollable = document.documentElement.scrollHeight > (window.innerHeight + 120);
                monthlyScrollShortcuts.classList.toggle('is-hidden', !scrollable);
            };

            monthlyScrollTopButton?.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            monthlyScrollBottomButton?.addEventListener('click', () => {
                window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' });
            });

            window.addEventListener('resize', updateMonthlyScrollShortcuts);
            updateMonthlyScrollShortcuts();

            const setSignalColumnsVisible = (visible) => {
                signalColumns().forEach((column) => {
                    column.classList.toggle('is-hidden', !visible);
                });

                const signalToggle = document.getElementById('monthlySignalToggle');
                if (signalToggle) {
                    signalToggle.setAttribute('aria-pressed', visible ? 'true' : 'false');
                    const label = signalToggle.querySelector('span');
                    if (label) {
                        label.textContent = visible ? 'Hide Signals' : 'Show Signals';
                    }
                }

                localStorage.setItem('monthlyAttendanceSignalsVisible', visible ? '1' : '0');
            };

            const bindMonthlySignalToggle = () => {
                const signalToggle = document.getElementById('monthlySignalToggle');
                if (!signalToggle || signalToggle.dataset.bound === '1') {
                    return;
                }

                signalToggle.dataset.bound = '1';
                setSignalColumnsVisible(localStorage.getItem('monthlyAttendanceSignalsVisible') !== '0');
                signalToggle.addEventListener('click', function () {
                    const visible = signalToggle.getAttribute('aria-pressed') !== 'true';
                    setSignalColumnsVisible(visible);
                });
            };

            const setMonthlyReloadProgress = (block, percent) => {
                const safePercent = Math.max(0, Math.min(100, Math.round(percent)));
                const bar = block.querySelector('.monthly-reload-bar');
                const label = block.querySelector('.monthly-reload-percent');

                if (bar) {
                    bar.style.width = `${safePercent}%`;
                }

                if (label) {
                    label.textContent = `${safePercent}%`;
                }
            };

            const startMonthlyReloadProgress = (block) => {
                let progress = 0;
                block.classList.add('is-refreshing');
                block.querySelector('.monthly-results-reload')?.setAttribute('aria-hidden', 'false');
                setMonthlyReloadProgress(block, progress);
                clearInterval(monthlyRefreshProgressTimer);
                monthlyRefreshProgressTimer = setInterval(() => {
                    const remaining = 95 - progress;
                    progress += Math.max(1, Math.ceil(remaining * 0.16));
                    setMonthlyReloadProgress(block, Math.min(progress, 95));
                }, 220);
            };

            const stopMonthlyReloadProgress = (block) => {
                clearInterval(monthlyRefreshProgressTimer);
                monthlyRefreshProgressTimer = null;
                setMonthlyReloadProgress(block, 100);
            };

            const refreshMonthlyResultsBlock = async (url, pushState = true) => {
                const currentBlock = document.getElementById('monthlyResultsBlock');

                if (!currentBlock || monthlyResultsLoading) {
                    return;
                }

                monthlyResultsLoading = true;
                startMonthlyReloadProgress(currentBlock);

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to refresh monthly attendance.');
                    }

                    const html = await response.text();
                    const parsed = new DOMParser().parseFromString(html, 'text/html');
                    const nextBlock = parsed.getElementById('monthlyResultsBlock');

                    if (!nextBlock) {
                        window.location.href = url.toString();
                        return;
                    }

                    stopMonthlyReloadProgress(currentBlock);
                    await new Promise((resolve) => setTimeout(resolve, 160));
                    currentBlock.replaceWith(nextBlock);

                    if (pushState) {
                        window.history.pushState({}, '', url.toString());
                    }

                    bindMonthlySignalToggle();
                    updateMonthlyScrollShortcuts();

                    if (window.feather) {
                        feather.replace();
                    }

                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
                        bootstrap.Tooltip.getOrCreateInstance(element);
                    });
                } catch (error) {
                    console.error(error);
                    window.location.href = url.toString();
                } finally {
                    monthlyResultsLoading = false;
                    const refreshedBlock = document.getElementById('monthlyResultsBlock');
                    if (refreshedBlock) {
                        refreshedBlock.classList.remove('is-refreshing');
                        refreshedBlock.querySelector('.monthly-results-reload')?.setAttribute('aria-hidden', 'true');
                    }
                }
            };

            const urlFromForm = (form) => {
                const url = new URL(form.action || window.location.href, window.location.origin);
                const formData = new FormData(form);
                url.search = '';

                formData.forEach((value, key) => {
                    if (value !== null && String(value).trim() !== '') {
                        url.searchParams.set(key, value);
                    }
                });

                url.searchParams.delete('page');
                return url;
            };

            bindMonthlySignalToggle();

            document.addEventListener('submit', (event) => {
                const form = event.target.closest('#monthlyAttendanceFilters, .monthly-table-controls');
                if (!form) {
                    return;
                }

                event.preventDefault();
                clearTimeout(monthlySearchTimer);
                refreshMonthlyResultsBlock(urlFromForm(form));
            });

            document.addEventListener('change', (event) => {
                if (!event.target || event.target.id !== 'table_per_page') {
                    return;
                }

                const url = new URL(window.location.href);
                url.searchParams.set('per_page', event.target.value);
                url.searchParams.delete('page');
                refreshMonthlyResultsBlock(url);
            });

            document.addEventListener('input', (event) => {
                if (!event.target || event.target.id !== 'table_search') {
                    return;
                }

                clearTimeout(monthlySearchTimer);
                monthlySearchTimer = setTimeout(() => {
                    const url = new URL(window.location.href);
                    const searchValue = event.target.value.trim();

                    if (searchValue) {
                        url.searchParams.set('search', searchValue);
                    } else {
                        url.searchParams.delete('search');
                    }

                    url.searchParams.delete('page');
                    refreshMonthlyResultsBlock(url);
                }, 350);
            });

            document.addEventListener('click', (event) => {
                const paginationLink = event.target.closest('#monthlyResultsBlock .pagination a');

                if (!paginationLink) {
                    return;
                }

                event.preventDefault();
                refreshMonthlyResultsBlock(new URL(paginationLink.href, window.location.origin));
            });

            document.addEventListener('click', (event) => {
                const link = event.target.closest('#monthlyResultsBlock a[href]');

                if (!link || link.closest('.pagination') || link.closest('[data-bs-toggle]') || link.getAttribute('href') === '#') {
                    return;
                }

                const url = new URL(link.href, window.location.origin);
                if (url.origin !== window.location.origin || !url.pathname.includes('/attendance-monthly') || url.searchParams.has('export')) {
                    return;
                }

                event.preventDefault();
                clearTimeout(monthlySearchTimer);
                refreshMonthlyResultsBlock(url);
            });

            window.addEventListener('popstate', () => {
                refreshMonthlyResultsBlock(new URL(window.location.href), false);
            });

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

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[character]));

            const parseDisplayTime = (value) => {
                const match = String(value || '').trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
                if (!match) {
                    return null;
                }

                let hours = Number(match[1]);
                const minutes = Number(match[2]);
                const meridiem = match[3].toUpperCase();
                if (meridiem === 'PM' && hours < 12) {
                    hours += 12;
                }
                if (meridiem === 'AM' && hours === 12) {
                    hours = 0;
                }

                return (hours * 60) + minutes;
            };

            const formatMinutesAsTime = (totalMinutes) => {
                const normalized = ((totalMinutes % 1440) + 1440) % 1440;
                const hours24 = Math.floor(normalized / 60);
                const minutes = normalized % 60;
                const meridiem = hours24 >= 12 ? 'PM' : 'AM';
                const hours12 = hours24 % 12 || 12;

                return `${String(hours12).padStart(2, '0')}:${String(minutes).padStart(2, '0')} ${meridiem}`;
            };

            const formatLateDuration = (totalMinutes) => {
                const minutes = Math.max(0, Number(totalMinutes) || 0);
                const hours = Math.floor(minutes / 60);
                const remainder = minutes % 60;

                if (hours === 0) {
                    return `${remainder}m`;
                }

                return remainder > 0 ? `${hours}h ${remainder}m` : `${hours}h`;
            };

            const buildLateTimeRange = (openingTime, minutes) => {
                const ranges = {
                    16: [17, 19],
                    20: [20, 29],
                    30: [30, 39],
                    40: [40, 49],
                    50: [50, 59],
                    60: [60, 120],
                    120: [121, null],
                };
                const [startOffset, endOffset] = ranges[minutes] || [minutes, null];
                const openingMinutes = parseDisplayTime(openingTime);
                if (openingMinutes === null) {
                    return endOffset === null
                        ? `Opening + ${startOffset}m and after`
                        : `Opening + ${startOffset}m to ${endOffset}m`;
                }

                const start = openingMinutes + startOffset;
                return endOffset === null
                    ? `${formatMinutesAsTime(start)} and after`
                    : `${formatMinutesAsTime(start)} - ${formatMinutesAsTime(openingMinutes + endOffset)}`;
            };

            const renderLateDashboardReport = (element, matchedDays, list) => {
                const breakdown = readJsonData(element, 'data-late-breakdown', {});
                const openingTime = element.getAttribute('data-opening-time') || 'N/A';
                const grandTotal = Number(element.getAttribute('data-late-total') || 0);
                const lateMinutesTotal = Number(element.getAttribute('data-late-minutes-total') || 0);
                const lateAfter = element.getAttribute('data-late-after') || 'opening + 16m';
                const totalEmployees = element.getAttribute('data-total-employees') || '0';
                const exportUrl = element.getAttribute('data-export-url') || '#';
                const reportSubtitle = element.getAttribute('data-report-subtitle') || matchedDays[0]?.getAttribute('data-employee-subtitle') || matchedDays[0]?.getAttribute('data-employee') || '';
                const rows = [
                    [16, '16 Minutes', 'Late more than 16 minutes', 0.10],
                    [20, '20 Minutes', 'Late more than 20 minutes', 0.20],
                    [30, '30 Minutes', 'Late more than 30 minutes', 0.30],
                    [40, '40 Minutes', 'Late more than 40 minutes', 0.40],
                    [50, '50 Minutes', 'Late more than 50 minutes', 0.50],
                    [60, '60 Minutes', 'Late more than 60 minutes', 1.00],
                    [120, '2 Hours', 'Late more than 2 hours', 5.00],
                ];
                const formatPayment = (value) => `$${Number(value).toFixed(2)}`;
                const paymentTotal = rows.reduce((sum, [minutes, , , rate]) => {
                    const count = Number(breakdown[String(minutes)] ?? breakdown[minutes] ?? 0);
                    return sum + (count * rate);
                }, 0);

                document.getElementById('monthlySignalDetailModalLabel').textContent = 'Office Time / Late Check-in Report';
                document.getElementById('monthlySignalDetailSubtitle').textContent = reportSubtitle;

                const report = document.createElement('div');
                report.className = 'late-dashboard-section';
                report.innerHTML = `
                    <div class="late-dashboard-header">
                        <div>
                            <h2 class="late-dashboard-title">Office Time / Late Check-in Report</h2>
                            <p class="late-dashboard-subtitle">${escapeHtml(reportSubtitle)}</p>
                        </div>
                        <div class="late-dashboard-actions">
                            <button class="late-dashboard-action is-close" type="button" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="late-dashboard-cards">
                        <div class="late-dashboard-card">
                            <span class="late-dashboard-icon is-green"><i data-feather="clock"></i></span>
                            <div>
                                <p class="late-dashboard-card-label">Opening Time</p>
                                <p class="late-dashboard-card-value">${escapeHtml(openingTime)}</p>
                            </div>
                        </div>
                        <div class="late-dashboard-card">
                            <span class="late-dashboard-icon is-amber"><i data-feather="watch"></i></span>
                            <div>
                                <p class="late-dashboard-card-label">Grace Period</p>
                                <p class="late-dashboard-card-value">16 Minutes</p>
                                <p class="late-dashboard-card-note">Until ${escapeHtml(lateAfter)}</p>
                            </div>
                        </div>
                        <div class="late-dashboard-card">
                            <span class="late-dashboard-icon is-orange"><i data-feather="alert-triangle"></i></span>
                            <div>
                                <p class="late-dashboard-card-label">Late Check-in</p>
                                <p class="late-dashboard-card-value">After ${escapeHtml(lateAfter)}</p>
                            </div>
                        </div>
                        <div class="late-dashboard-card">
                            <span class="late-dashboard-icon is-purple"><i data-feather="users"></i></span>
                            <div>
                                <p class="late-dashboard-card-label">Employees</p>
                                <p class="late-dashboard-card-value">${escapeHtml(totalEmployees)}</p>
                                <p class="late-dashboard-card-note">Total Late: ${grandTotal}</p>
                            </div>
                        </div>
                        <div class="late-dashboard-card">
                            <span class="late-dashboard-icon is-blue"><i data-feather="bar-chart-2"></i></span>
                            <div>
                                <p class="late-dashboard-card-label">Late Hours</p>
                                <p class="late-dashboard-card-value">${escapeHtml(formatLateDuration(lateMinutesTotal))}</p>
                                <p class="late-dashboard-card-note">${lateMinutesTotal} minutes total</p>
                            </div>
                        </div>
                    </div>
                    <div class="late-dashboard-info">
                        <i data-feather="info"></i>
                        Check-in from <strong>opening time + 16 minutes</strong> will be considered as <strong>Late</strong>.
                    </div>
                    <div class="late-dashboard-table-wrap">
                        <table class="late-dashboard-table">
                            <thead>
                                <tr>
                                    <th>Late More Than</th>
                                    <th>Late Time Range</th>
                                    <th>Description</th>
                                    <th>Count</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.map(([minutes, label, description, rate], index) => {
                                    const count = Number(breakdown[String(minutes)] ?? breakdown[minutes] ?? 0);
                                    const payment = count * rate;

                                    return `
                                        <tr>
                                            <td>
                                                <span class="monthly-late-minutes-cell">
                                                    <span class="monthly-late-dot is-${minutes}">L</span>
                                                    <strong>${label}</strong>
                                                </span>
                                            </td>
                                            <td>${buildLateTimeRange(openingTime, minutes)}</td>
                                            <td>${description}</td>
                                            <td><span class="late-dashboard-count is-${minutes}">${count}</span></td>
                                            <td><span class="late-dashboard-payment">${formatPayment(payment)}</span></td>
                                        </tr>
                                    `;
                                }).join('')}
                                <tr class="late-dashboard-grand">
                                    <td colspan="3">Grand Total</td>
                                    <td><span class="late-dashboard-count">${grandTotal}</span></td>
                                    <td><span class="late-dashboard-payment">${formatPayment(paymentTotal)}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="late-dashboard-footer">
                        <p class="late-dashboard-note">* Payment is calculated by late time range and shown in USD.</p>
                        <a class="late-dashboard-action" href="${escapeHtml(exportUrl)}">
                            <i data-feather="download"></i>
                            Export Excel
                        </a>
                    </div>
                `;

                list.appendChild(report);
                if (window.feather) {
                    feather.replace();
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
                const canQuickAttendance = trigger.getAttribute('data-can-quick-attendance') === '1';
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

                const quickAttendanceButton = document.getElementById('monthlyQuickAttendanceButton');
                const quickLeaveButton = document.getElementById('monthlyQuickLeaveButton');
                const quickTimeLeaveButton = document.getElementById('monthlyQuickTimeLeaveButton');
                const quickChatButton = document.getElementById('monthlyQuickChatButton');

                if (quickAttendanceButton) {
                    quickAttendanceButton.classList.toggle('d-none', !canQuickAttendance);
                    setQuickActionData(quickAttendanceButton, trigger);
                    quickAttendanceButton.setAttribute('data-user-name', employee);
                    quickAttendanceButton.setAttribute('data-href', trigger.getAttribute('data-attendance-store-url') || '');
                    quickAttendanceButton.setAttribute('data-cdate', trigger.getAttribute('data-display-date') || date);
                }

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
                signalDetailModalElement.classList.remove('is-late-report');

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
                    : dayButtons.filter((dayButton) => {
                        const dayStatus = dayButton.getAttribute('data-status-key');
                        return status === 'present'
                            ? ['present', 'late'].includes(dayStatus)
                            : dayStatus === status;
                    });

                document.getElementById('monthlySignalDetailModalLabel').textContent = titleText;
                document.getElementById('monthlySignalDetailSubtitle').textContent = matchedDays[0]?.getAttribute('data-employee') || '';

                const list = document.getElementById('monthlySignalDayList');
                list.innerHTML = '';
                signalDetailModalElement.classList.toggle('is-late-report', status === 'late');

                if (status === 'late') {
                    renderLateDashboardReport(element, matchedDays, list);
                    signalDetailModal.show();
                    return;
                }

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

            document.addEventListener('click', function (event) {
                const element = event.target.closest('.quickAttendanceTrigger');
                if (!element || !quickAttendanceModal) {
                    return;
                }

                event.preventDefault();

                document.getElementById('createAttendance')?.setAttribute('action', element.getAttribute('data-href') || '');
                document.getElementById('empId').value = element.getAttribute('data-user-id') || '';
                document.getElementById('addDate').value = element.getAttribute('data-attendance-date') || element.getAttribute('data-date') || '';
                document.getElementById('checkAddIn').value = '';
                document.getElementById('checkAddOut').value = '';
                const remark = document.getElementById('createRemark');
                if (remark) {
                    remark.value = '';
                    remark.removeAttribute('required');
                }

                const form = document.getElementById('createAttendance');
                if (form && !form.querySelector('input[name="monthly_quick_attendance"]')) {
                    const quickFlag = document.createElement('input');
                    quickFlag.type = 'hidden';
                    quickFlag.name = 'monthly_quick_attendance';
                    quickFlag.value = '1';
                    form.appendChild(quickFlag);
                }

                const title = document.querySelector('#attendanceCreateForm .add-modal-title');
                if (title) {
                    const userName = element.getAttribute('data-user-name') || 'Employee';
                    const displayDate = element.getAttribute('data-cdate') || element.getAttribute('data-display-date') || '';
                    title.textContent = `Quick Attendance: ${userName}${displayDate ? ' - ' + displayDate : ''}`;
                }

                detailModal?.hide();
                quickAttendanceModal.show();
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
