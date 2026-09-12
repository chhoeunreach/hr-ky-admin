@php use App\Helpers\AttendanceHelper; @endphp
@php use App\Models\Client; @endphp
@php use App\Models\User; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.digital_hr_dashboard'))

<?php
$attendanceDetail = (AppHelper::employeeTodayAttendanceDetail());

$multipleEntries = count($attendanceDetail);
$firstAttendance = $attendanceDetail->first();
$lastAttendance = $attendanceDetail->last();

$checkInAt = $firstAttendance['check_in_at'] ?? '';
$checkOutAt = $lastAttendance['check_out_at'] ?? '';
$attendanceDate = $lastAttendance['attendance_date'] ?? '';
$viewCheckIn = $checkInAt ? AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $checkInAt) : '--:--';
$viewCheckOut = $checkOutAt ? AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $checkOutAt) : '--:--';
$currentMonthLabel = now()->format('M Y');
?>

@section('nav-head', __('index.welcome') . ' : ' . ucfirst($dashboardDetail?->company_name))

@section('styles')
    <style>
        :root {
            --dash-bg: #f8fafc;
            --dash-surface: #ffffff;
            --dash-border: #e2e8f0;
            --dash-border-hover: #cbd5e1;
            --dash-ink: #0f172a;
            --dash-muted: #64748b;
            --dash-subtle: #94a3b8;
            --summary-card-top: #ffffff;
            --summary-card-bottom: #ffffff;
            --dash-brand: var(--primary-color);
            --dash-brand-hover: var(--hover-color);
            --dash-brand-soft: color-mix(in srgb, var(--primary-color) 12%, #ffffff);
            --dash-brand-border: color-mix(in srgb, var(--primary-color) 26%, #ffffff);
        }

        body.theme-dark {
            --dash-bg: #0b1329;
            --dash-surface: #131d36;
            --dash-border: #1e293b;
            --dash-border-hover: #334155;
            --dash-ink: #f1f5f9;
            --dash-muted: #94a3b8;
            --dash-subtle: #64748b;
            --summary-card-top: #131d36;
            --summary-card-bottom: #131d36;
            --dash-brand: var(--dark-primary-color, var(--primary-color));
            --dash-brand-hover: var(--dark-hover-color, var(--hover-color));
            --dash-brand-soft: color-mix(in srgb, var(--dash-brand) 18%, #131d36);
            --dash-brand-border: color-mix(in srgb, var(--dash-brand) 35%, #131d36);
        }

        /* Enforce theme color for primary components */
        .btn-outline-primary {
            color: var(--dash-brand) !important;
            border-color: var(--dash-brand) !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:active,
        .btn-outline-primary:focus {
            background-color: var(--dash-brand) !important;
            border-color: var(--dash-brand) !important;
            color: #ffffff !important;
        }

        .btn-primary {
            background-color: var(--dash-brand) !important;
            border-color: var(--dash-brand) !important;
        }

        .btn-primary:hover,
        .btn-primary:active,
        .btn-primary:focus {
            background-color: var(--dash-brand-hover) !important;
            border-color: var(--dash-brand-hover) !important;
        }

        .text-primary {
            color: var(--dash-brand) !important;
        }

        .bg-primary {
            background-color: var(--dash-brand) !important;
        }

        .bg-primary-subtle {
            background-color: var(--dash-brand-soft) !important;
            color: var(--dash-brand) !important;
            border-color: var(--dash-brand-border) !important;
        }

        .alert {
            display: flex;
            align-items: center;
        }

        /* Unified KPI Card System */
        .kpi-card {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
            min-height: 94px;
            padding: 0.65rem 0.85rem;
            background: var(--dash-surface);
            border: 1px solid var(--dash-border);
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: left;
            text-decoration: none;
            color: inherit;
            overflow: hidden;
        }

        button.kpi-card {
            appearance: none;
            cursor: pointer;
        }

        .kpi-card:hover {
            border-color: var(--kpi-border, var(--dash-brand));
            box-shadow: 0 6px 18px -3px rgba(15, 23, 42, 0.07), 0 0 0 1px var(--kpi-border, var(--dash-brand));
            transform: translateY(-2px);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2.5px;
            background: var(--kpi-accent, var(--dash-brand));
            opacity: 0.9;
            border-radius: 10px 10px 0 0;
        }

        .kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 0.35rem;
        }

        .kpi-title {
            margin: 0;
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--dash-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            line-height: 1.25;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kpi-badge {
            font-size: 0.62rem;
            font-weight: 700;
            padding: 0.08rem 0.35rem;
            border-radius: 16px;
            background: var(--kpi-bg, var(--dash-brand-soft));
            color: var(--kpi-accent, var(--dash-brand));
            flex-shrink: 0;
            line-height: 1.2;
        }

        .kpi-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .kpi-value {
            margin: 0;
            font-size: 1.38rem;
            font-weight: 800;
            color: var(--dash-ink);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .kpi-icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 7px;
            background: var(--kpi-bg, var(--dash-brand-soft));
            color: var(--kpi-accent, var(--dash-brand));
            flex-shrink: 0;
            transition: transform 0.18s ease;
        }

        .kpi-card:hover .kpi-icon-wrap {
            transform: scale(1.05);
        }

        .kpi-icon-wrap svg {
            width: 16px;
            height: 16px;
            stroke-width: 2.2;
        }

        .kpi-footer {
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.66rem;
            color: var(--dash-subtle);
            font-weight: 600;
            line-height: 1.2;
        }

        .kpi-interactive-hint {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            color: var(--kpi-accent, var(--dash-brand));
            font-weight: 600;
            opacity: 0.95;
        }

        /* Color Themes for KPI Cards - Anchored firmly on Theme Color */
        .kpi-theme-employees,
        .kpi-theme-departments,
        .kpi-theme-holidays,
        .kpi-theme-paid-leave,
        .kpi-theme-leave-req,
        .kpi-theme-time-req,
        .kpi-theme-onleave {
            --kpi-accent: var(--dash-brand);
            --kpi-border: var(--dash-brand-border);
            --kpi-bg: var(--dash-brand-soft);
        }

        .kpi-theme-checkin {
            --kpi-accent: var(--dash-brand);
            --kpi-border: var(--dash-brand-border);
            --kpi-bg: var(--dash-brand-soft);
        }

        .kpi-theme-checkout {
            --kpi-accent: var(--dash-brand-hover);
            --kpi-border: color-mix(in srgb, var(--dash-brand-hover) 28%, var(--dash-surface));
            --kpi-bg: color-mix(in srgb, var(--dash-brand-hover) 10%, var(--dash-surface));
        }

        .kpi-theme-active {
            --kpi-accent: #16a34a;
            --kpi-border: #86efac;
            --kpi-bg: #f0fdf4;
        }

        .kpi-theme-inactive {
            --kpi-accent: #64748b;
            --kpi-border: #cbd5e1;
            --kpi-bg: #f8fafc;
        }

        .kpi-theme-pending {
            --kpi-accent: #dc2626;
            --kpi-border: #fca5a5;
            --kpi-bg: #fef2f2;
        }

        /* Attendance Hub / Punch Terminal Card */
        .attendance-terminal-card {
            background: var(--dash-surface);
            border: 1px solid var(--dash-border);
            border-radius: 10px;
            box-shadow: 0 2px 12px -2px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .terminal-header {
            padding: 0.55rem 0.85rem;
            border-bottom: 1px solid var(--dash-border);
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.8) 0%, rgba(255, 255, 255, 0.4) 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .terminal-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--dash-muted);
        }

        .terminal-live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .terminal-body {
            padding: 0.65rem 0.85rem 0.5rem;
            text-align: center;
        }

        /* Minimalist Clock Dial */
        #clockContainer {
            position: relative;
            margin: 0 auto 0.45rem;
            height: 68px;
            width: 68px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,1) 50%, rgba(241,245,249,1) 100%);
            border: 1.5px solid var(--dash-border);
            box-shadow: inset 0 1px 4px rgba(15, 23, 42, 0.04), 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        #clockContainer::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #0f172a;
            transform: translate(-50%, -50%);
            z-index: 5;
            box-shadow: 0 0 0 1.5px #fff;
        }

        #hour {
            position: absolute;
            background: #1e293b;
            border-radius: 4px;
            transform-origin: bottom;
            width: 3.5%;
            height: 25%;
            top: 25%;
            left: 48.25%;
            z-index: 2;
        }

        #minute {
            width: 2.2%;
            height: 33%;
            top: 17%;
            left: 48.9%;
            position: absolute;
            background: var(--dash-brand);
            border-radius: 4px;
            transform-origin: bottom;
            z-index: 3;
        }

        #second {
            width: 1.4%;
            height: 40%;
            top: 10%;
            left: 49.3%;
            position: absolute;
            background: #ef4444;
            border-radius: 4px;
            transform-origin: bottom;
            z-index: 4;
        }

        .digital-clock-display {
            font-family: 'SF Mono', 'Roboto Mono', 'Cascadia Code', monospace;
            font-size: 1.32rem;
            font-weight: 800;
            color: var(--dash-ink);
            letter-spacing: 0.03em;
            line-height: 1.15;
            margin-bottom: 0.15rem;
            font-variant-numeric: tabular-nums;
        }

        .digital-clock-ampm {
            font-size: 0.62rem;
            font-weight: 700;
            padding: 0.08rem 0.35rem;
            border-radius: 4px;
            background: var(--dash-brand-soft);
            color: var(--dash-brand);
            margin-left: 3px;
            vertical-align: middle;
        }

        .terminal-date {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--dash-brand);
            margin-bottom: 0.65rem;
            line-height: 1.2;
        }

        .punch-action-area {
            margin-bottom: 0.65rem;
        }

        .btn-punch {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 0.46rem 0.85rem;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            border: none;
            letter-spacing: 0.02em;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-punch-in {
            background: linear-gradient(135deg, var(--dash-brand) 0%, var(--dash-brand-hover) 100%);
            color: #ffffff;
            box-shadow: 0 2px 10px color-mix(in srgb, var(--dash-brand) 28%, transparent);
        }

        .btn-punch-in:hover {
            background: linear-gradient(135deg, var(--dash-brand-hover) 0%, var(--dash-brand) 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px color-mix(in srgb, var(--dash-brand) 38%, transparent);
        }

        .btn-punch-out {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(244, 63, 94, 0.25);
        }

        .btn-punch-out:hover {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(244, 63, 94, 0.35);
        }

        .punch-status-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            padding: 0.45rem 0.65rem;
            background: rgba(248, 250, 252, 0.7);
            border-top: 1px solid var(--dash-border);
        }

        .punch-chip {
            padding: 0.3rem 0.45rem;
            border-radius: 6px;
            text-align: center;
            border: 1px solid transparent;
        }

        .punch-chip-in {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .punch-chip-out {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .punch-chip-label {
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            margin-bottom: 1px;
            line-height: 1.1;
        }

        .punch-chip-in .punch-chip-label { color: #166534; }
        .punch-chip-out .punch-chip-label { color: #991b1b; }

        .punch-chip-time {
            font-size: 0.8rem;
            font-weight: 800;
            margin: 0;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .punch-chip-in .punch-chip-time { color: #15803d; }
        .punch-chip-out .punch-chip-time { color: #b91c1c; }

        /* Modern Summary Accordions */
        .summary-panel {
            border: 1px solid var(--dash-border);
            border-top: 3px solid var(--dash-brand);
            border-radius: 10px;
            overflow: hidden;
            background: var(--dash-surface);
            box-shadow: 0 1px 6px rgba(15, 23, 42, 0.03);
            margin-bottom: 1rem;
        }

        .summary-panel .card-header {
            border-bottom: 1px solid var(--dash-border);
            background: var(--dash-surface);
            padding: 0.65rem 1rem;
        }

        .summary-panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .summary-panel-title-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-panel-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: var(--dash-brand-soft);
            color: var(--dash-brand);
        }

        .summary-panel-icon svg {
            width: 15px;
            height: 15px;
        }

        .summary-panel-title {
            color: var(--dash-ink);
            font-size: 0.92rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .summary-panel-subtitle {
            margin: 0.1rem 0 0;
            color: var(--dash-muted);
            font-size: 0.72rem;
            line-height: 1.2;
        }

        .summary-panel-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border: 1px solid var(--dash-border);
            border-radius: 6px;
            background: var(--dash-bg);
            color: var(--dash-muted);
            transition: all 0.18s ease;
        }

        .summary-panel-toggle:hover {
            background: #e2e8f0;
            color: var(--dash-ink);
        }

        .summary-panel-toggle svg {
            width: 14px;
            height: 14px;
            transition: transform 0.18s ease;
        }

        .summary-panel-toggle[aria-expanded="true"] svg {
            transform: rotate(180deg);
        }

        .summary-panel .card-body {
            padding: 0.65rem 0.85rem 0.85rem;
            background: var(--dash-surface);
        }

        .summary-table-shell {
            border: 1px solid var(--dash-border);
            border-radius: 8px;
            overflow: auto;
            background: #ffffff;
            scrollbar-color: #cbd5e1 #f8fafc;
            scrollbar-width: thin;
        }

        .summary-table-shell::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .summary-table-shell::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .summary-table-shell::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .branch-summary-table {
            margin-bottom: 0;
            min-width: 1500px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .branch-summary-table th,
        .branch-summary-table td {
            vertical-align: middle;
            border-color: #edf2f7;
            padding: 0.48rem 0.65rem;
            font-size: 0.76rem;
        }

        .branch-summary-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #f8fafc;
            color: var(--dash-muted);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            box-shadow: inset 0 -1px 0 #e2e8f0;
            white-space: nowrap;
        }

        .branch-summary-table th:first-child,
        .branch-summary-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #ffffff;
            box-shadow: 2px 0 6px rgba(15, 23, 42, 0.03);
            min-width: 160px;
        }

        .branch-summary-table thead th:first-child {
            z-index: 4;
            background: #f8fafc;
        }

        .branch-summary-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        .branch-summary-table tbody tr:hover td:first-child {
            background-color: #f8fafc;
        }

        .summary-name-trigger {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            color: var(--dash-ink);
            text-align: left;
            border: 0;
            background: transparent;
            padding: 0;
            font-size: 0.76rem;
        }

        .summary-name-trigger::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--dash-brand);
            flex-shrink: 0;
        }

        .summary-value-trigger {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-width: 32px;
            padding: 0.12rem 0.45rem;
            border-radius: 16px;
            background: var(--metric-bg, #f1f5f9);
            color: var(--metric-accent, #334155);
            font-size: 0.74rem;
            font-weight: 800;
            border: 1px solid var(--metric-border, #e2e8f0);
            transition: all 0.15s ease;
            line-height: 1.2;
        }

        .summary-value-trigger:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            filter: brightness(0.96);
        }

        .summary-metric-total_all_employee,
        .summary-metric-active_employee {
            --metric-accent: var(--dash-brand);
            --metric-bg: var(--dash-brand-soft);
            --metric-border: var(--dash-brand-border);
        }

        .summary-metric-inactive_employee {
            --metric-accent: #64748b;
            --metric-bg: #f8fafc;
            --metric-border: #e2e8f0;
        }

        .summary-metric-active_employee_checkin {
            --metric-accent: var(--dash-brand);
            --metric-bg: var(--dash-brand-soft);
            --metric-border: var(--dash-brand-border);
        }

        .summary-metric-active_employee_checkout {
            --metric-accent: var(--dash-brand-hover);
            --metric-bg: color-mix(in srgb, var(--dash-brand-hover) 10%, var(--dash-surface));
            --metric-border: color-mix(in srgb, var(--dash-brand-hover) 28%, var(--dash-surface));
        }

        .summary-metric-active_employee_not_yet_checkin,
        .summary-metric-active_employee_not_yet_checkout {
            --metric-accent: #d97706;
            --metric-bg: #fffbeb;
            --metric-border: #fde68a;
        }

        .summary-metric-active_employee_dayoff,
        .summary-metric-active_employee_leave {
            --metric-accent: var(--dash-brand);
            --metric-bg: var(--dash-brand-soft);
            --metric-border: var(--dash-brand-border);
        }

        .summary-metric-active_employee_pending_request,
        .summary-metric-active_employee_time_leave_request {
            --metric-accent: #dc2626;
            --metric-bg: #fef2f2;
            --metric-border: #fecaca;
        }

        .summary-metric-active_employee_time_leave {
            --metric-accent: var(--dash-brand-hover);
            --metric-bg: color-mix(in srgb, var(--dash-brand-hover) 10%, var(--dash-surface));
            --metric-border: color-mix(in srgb, var(--dash-brand-hover) 28%, var(--dash-surface));
        }

        .branch-summary-table tfoot th,
        .branch-summary-table tfoot td {
            font-weight: 800;
            background: color-mix(in srgb, var(--dash-brand) 7%, var(--dash-surface));
            color: var(--dash-ink);
            position: sticky;
            bottom: 0;
            z-index: 2;
            border-top: 1.5px solid var(--dash-brand-border);
            padding: 0.48rem 0.65rem;
        }

        .branch-summary-table tfoot th:first-child {
            z-index: 3;
            background: color-mix(in srgb, var(--dash-brand) 7%, var(--dash-surface));
        }

        /* Project Management Modern UI */
        .project-section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.85rem;
        }

        .project-section-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--dash-ink);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .project-mini-card {
            background: var(--dash-surface);
            border: 1px solid var(--dash-border);
            border-radius: 8px;
            padding: 0.55rem 0.75rem;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 3px solid var(--pm-color, var(--dash-brand));
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .project-mini-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        .project-mini-title {
            font-size: 0.66rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            color: var(--dash-muted);
            margin-bottom: 2px;
            line-height: 1.1;
        }

        .project-mini-val {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--dash-ink);
            margin: 0;
            line-height: 1;
        }

        .project-mini-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--pm-bg, var(--dash-brand-soft));
            color: var(--pm-color, var(--dash-brand));
        }

        .project-mini-icon svg {
            width: 14px;
            height: 14px;
        }

        .custom-dash-table {
            margin-bottom: 0;
        }

        .custom-dash-table thead th {
            background: #f8fafc;
            color: var(--dash-muted);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 0.55rem 0.85rem;
            border-bottom: 1px solid var(--dash-border);
        }

        .custom-dash-table tbody td {
            padding: 0.55rem 0.85rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--dash-border);
            font-size: 0.78rem;
        }

        .client-avatar-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--dash-ink);
            font-weight: 700;
        }

        .client-avatar-img {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--dash-border);
        }

        .progress-slim {
            height: 6px;
            border-radius: 20px;
            background: #e2e8f0;
            overflow: hidden;
            margin-bottom: 3px;
        }

        .priority-badge {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            text-transform: capitalize;
        }

        .priority-badge-urgent { background: #fee2e2; color: #b91c1c; }
        .priority-badge-high { background: #ffedd5; color: #c2410c; }
        .priority-badge-medium { background: #fef3c7; color: #b45309; }
        .priority-badge-low { background: #e0f2fe; color: #0369a1; }

        @media (max-width: 991.98px) {
            .branch-summary-table { min-width: 1400px; }
            .kpi-value { font-size: 1.55rem; }
        }
    </style>
@endsection

@section('main-content')
    <section class="content">
        @php
            $projectPriorityClasses = [
                'low' => 'priority-badge-low',
                'medium' => 'priority-badge-medium',
                'high' => 'priority-badge-high',
                'urgent' => 'priority-badge-urgent'
            ];
            $dashboardCardBranchIds = $branchDashboardSummaries->pluck('id')->filter()->implode(',');
        @endphp

        <div id="flashAttendanceMessage" class="d-none">
            <div class="alert alert-danger errorStartWorking mb-3">
                <p class="errorStartWorkingMessage mb-0"></p>
            </div>
            <div class="alert alert-danger errorStopWorking mb-3">
                <p class="errorStopWorkingMessage mb-0"></p>
            </div>
            <div class="alert alert-success successStartWorking mb-3">
                <p class="successStartWorkingMessage mb-0"></p>
            </div>
            <div class="alert alert-success successStopWorking mb-3">
                <p class="successStopWorkingMessage mb-0"></p>
            </div>
        </div>

        <div id="loader" style="display:none;">
            <div class="loading">
                <div class="loading-content"></div>
            </div>
        </div>

        <!-- Main Top KPI Grid + Attendance Hub -->
        <div class="row g-2 mb-3">
            @can('attendance_summary')
                <div class="{{ (auth()->check() && auth()->user()->can('allow_attendance')) ? 'col-xxl-9 col-xl-8' : 'col-12' }}">
                    <div class="row g-2">
                        <!-- 1. Total Employees (Interactive) -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <button type="button"
                                    class="kpi-card kpi-theme-employees summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="total_all_employee"
                                    data-entity-name="{{ __('index.total_employees') }}"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.total_employees') }}</span>
                                        <span class="kpi-badge">{{ $currentMonthLabel }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->total_employee ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="users"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span class="kpi-interactive-hint"><i data-feather="arrow-up-right" style="width:12px;height:12px;"></i> {{ __('index.click_view_employee_detail') }}</span>
                                </div>
                            </button>
                        </div>

                        <!-- 2. Active Employees (Interactive) -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <button type="button"
                                    class="kpi-card kpi-theme-active summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="active_employee"
                                    data-entity-name="{{ __('index.active_employee') }}"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.active_employee') }}</span>
                                        <span class="kpi-badge">{{ __('index.active') }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->active_employee ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="user-check"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span class="kpi-interactive-hint"><i data-feather="arrow-up-right" style="width:12px;height:12px;"></i> {{ __('index.click_view_active_staff') }}</span>
                                </div>
                            </button>
                        </div>

                        <!-- 3. Inactive Employees (Interactive) -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <button type="button"
                                    class="kpi-card kpi-theme-inactive summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="inactive_employee"
                                    data-entity-name="{{ __('index.inactive_employee') }}"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.inactive_employee') }}</span>
                                        <span class="kpi-badge">{{ $currentMonthLabel }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->inactive_employee ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="user-x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span class="kpi-interactive-hint"><i data-feather="arrow-up-right" style="width:12px;height:12px;"></i> {{ __('index.click_view_inactive_staff') }}</span>
                                </div>
                            </button>
                        </div>

                        <!-- 4. Total Departments -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <div class="kpi-card kpi-theme-departments">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.total_departments') }}</span>
                                        <span class="kpi-badge">{{ count($departmentDashboardSummaries) }} Units</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->total_departments ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="layers"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span>{{ __('index.department') }} snapshot</span>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Checked In Today -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <div class="kpi-card kpi-theme-checkin">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.total_check_in_today') }}</span>
                                        <span class="kpi-badge">{{ __('index.today') }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->total_checked_in_employee ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="log-in"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span>{{ __('index.checked_in') }} log</span>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Checked Out Today -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <div class="kpi-card kpi-theme-checkout">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.total_check_out_today') }}</span>
                                        <span class="kpi-badge">{{ __('index.today') }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->total_checked_out_employee ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="log-out"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span>{{ __('index.checked_out') }} log</span>
                                </div>
                            </div>
                        </div>

                        <!-- 7. On Leave Today -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <div class="kpi-card kpi-theme-onleave">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.on_leave_today') }}</span>
                                        <span class="kpi-badge">{{ __('index.today') }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->total_on_leave ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="user-minus"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span>{{ __('index.leave') }} today</span>
                                </div>
                            </div>
                        </div>

                        <!-- 8. Pending Leave Requests (Interactive) -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <button type="button"
                                    class="kpi-card kpi-theme-pending summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="active_employee_pending_request"
                                    data-entity-name="{{ __('index.pending_leave_requests') }}"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.pending_leave_requests') }}</span>
                                        <span class="kpi-badge">{{ __('index.action_required') ?? 'Action' }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->total_pending_leave_requests ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="alert-circle"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span class="kpi-interactive-hint"><i data-feather="arrow-up-right" style="width:12px;height:12px;"></i> {{ __('index.review') ?? 'Review' }}</span>
                                </div>
                            </button>
                        </div>

                        <!-- 9. Monthly Leave Requests (Interactive) -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <button type="button"
                                    class="kpi-card kpi-theme-leave-req summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="current_month_leave_request"
                                    data-entity-name="{{ __('index.leave_request') }}"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.leave_request') }}</span>
                                        <span class="kpi-badge">{{ $currentMonthLabel }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->current_month_leave_requests ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="file-text"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span class="kpi-interactive-hint"><i data-feather="arrow-up-right" style="width:12px;height:12px;"></i> {{ __('index.click_view_monthly_requests') }}</span>
                                </div>
                            </button>
                        </div>

                        <!-- 10. Monthly Time Leave Requests (Interactive) -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <button type="button"
                                    class="kpi-card kpi-theme-time-req summary-trigger"
                                    data-summary-scope="branch"
                                    data-summary-metric="current_month_time_leave_request"
                                    data-entity-name="{{ __('index.time_leave_request') }}"
                                    data-entity-ids="{{ $dashboardCardBranchIds }}">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.time_leave_request') }}</span>
                                        <span class="kpi-badge">{{ $currentMonthLabel }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->current_month_time_leave_requests ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="clock"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span class="kpi-interactive-hint"><i data-feather="arrow-up-right" style="width:12px;height:12px;"></i> {{ __('index.click_view_monthly_time_leave') }}</span>
                                </div>
                            </button>
                        </div>

                        <!-- 11. Paid Leaves -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <div class="kpi-card kpi-theme-paid-leave">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.paid_leaves') }}</span>
                                        <span class="kpi-badge">Policy</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->total_paid_leaves ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="check-circle"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span>Allocated days</span>
                                </div>
                            </div>
                        </div>

                        <!-- 12. Total Holidays -->
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 d-flex">
                            <div class="kpi-card kpi-theme-holidays">
                                <div>
                                    <div class="kpi-header">
                                        <span class="kpi-title">{{ __('index.total_holidays') }}</span>
                                        <span class="kpi-badge">{{ date('Y') }}</span>
                                    </div>
                                    <div class="kpi-body">
                                        <h3 class="kpi-value">{{ number_format($dashboardDetail?->total_holidays ?? 0) }}</h3>
                                        <div class="kpi-icon-wrap">
                                            <i data-feather="calendar"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer">
                                    <span>Annual holidays</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <!-- Attendance Hub Widget -->
            @if(auth()->user())
                @can('allow_attendance')
                    <div class="col-xxl-3 col-xl-4 d-flex">
                        <div class="attendance-terminal-card w-100">
                            <div class="terminal-header">
                                <span class="terminal-tag">
                                    <span class="terminal-live-dot"></span> Attendance Hub
                                </span>
                                <span class="badge bg-light text-secondary border font-monospace">{{ $appTimeSetting ? '24h' : '12h' }}</span>
                            </div>

                            <div class="terminal-body">
                                <div id="clockContainer">
                                    <div id="hour"></div>
                                    <div id="minute"></div>
                                    <div id="second"></div>
                                </div>

                                <div class="digital-clock-display">
                                    <span id="digitalClockTime">--:--:--</span>
                                    <span id="digitalClockAmPm" class="digital-clock-ampm">AM</span>
                                </div>

                                <p id="date" class="terminal-date mb-3">{{ AppHelper::getCurrentDate() }}</p>

                                <div class="punch-action-area">
                                    @if($multipleAttendance > 1)
                                        @if($multipleEntries < $multipleAttendance || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at))
                                            @if((!isset($firstAttendance->check_in_at) && !isset($firstAttendance->check_out_at)) || ($lastAttendance->check_in_at && $lastAttendance->check_out_at))
                                                <button href="{{ route('admin.dashboard.takeAttendance', 'checkIn') }}"
                                                        class="btn-punch btn-punch-in"
                                                        id="startWorkingBtn"
                                                        data-audio="{{ asset('assets/audio/beep.mp3') }}">
                                                    <i data-feather="log-in" style="width:18px;height:18px;"></i>
                                                    <span>{{ __('index.punch_in') }}</span>
                                                </button>
                                            @elseif(($firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at))
                                                <button href="{{ route('admin.dashboard.takeAttendance', 'checkOut') }}"
                                                        class="btn-punch btn-punch-out"
                                                        id="stopWorkingBtn"
                                                        data-audio="{{ asset('assets/audio/beep.mp3') }}">
                                                    <i data-feather="log-out" style="width:18px;height:18px;"></i>
                                                    <span>{{ __('index.punch_out') }}</span>
                                                </button>
                                            @endif
                                        @endif
                                    @else
                                        <button href="{{ route('admin.dashboard.takeAttendance', 'checkIn') }}"
                                                class="btn-punch btn-punch-in {{ $checkInAt ? 'd-none' : '' }}"
                                                id="startWorkingBtn"
                                                data-audio="{{ asset('assets/audio/beep.mp3') }}">
                                            <i data-feather="log-in" style="width:18px;height:18px;"></i>
                                            <span>{{ __('index.punch_in') }}</span>
                                        </button>
                                        <button href="{{ route('admin.dashboard.takeAttendance', 'checkOut') }}"
                                                class="btn-punch btn-punch-out {{ $checkOutAt ? 'd-none' : '' }}"
                                                id="stopWorkingBtn"
                                                data-audio="{{ asset('assets/audio/beep.mp3') }}">
                                            <i data-feather="log-out" style="width:18px;height:18px;"></i>
                                            <span>{{ __('index.punch_out') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="punch-status-grid">
                                <div class="punch-chip punch-chip-in">
                                    <div class="punch-chip-label"><i data-feather="check" style="width:10px;height:10px;"></i> {{ __('index.check_in_at') }}</div>
                                    <p class="punch-chip-time" id="checkInTime">{{ $viewCheckIn }}</p>
                                </div>
                                <div class="punch-chip punch-chip-out">
                                    <div class="punch-chip-label"><i data-feather="arrow-right" style="width:10px;height:10px;"></i> {{ __('index.check_out_at') }}</div>
                                    <p class="punch-chip-time" id="checkOutTime">{{ $viewCheckOut }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
            @endif
        </div>

        <!-- Branch & Department Summary Tables -->
        @can('attendance_summary')
            @php
                $summaryMetrics = [
                    'total_all_employee' => __('index.all_staff'),
                    'inactive_employee' => __('index.inactive_employee'),
                    'active_employee' => __('index.active'),
                    'active_employee_checkin' => __('index.checked_in'),
                    'active_employee_not_yet_checkin' => __('index.no_check_in'),
                    'active_employee_checkout' => __('index.checked_out'),
                    'active_employee_not_yet_checkout' => __('index.no_check_out'),
                    'active_employee_dayoff' => __('index.day_off'),
                    'active_employee_leave' => __('index.leave'),
                    'active_employee_pending_request' => __('index.pending_leave_requests'),
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

            <!-- Branch Summary Panel -->
            <div class="summary-panel">
                <div class="card-header">
                    <div class="summary-panel-heading">
                        <div class="summary-panel-title-group">
                            <div class="summary-panel-icon">
                                <i data-feather="map-pin"></i>
                            </div>
                            <div>
                                <h4 class="summary-panel-title">
                                    {{ __('index.branch_summary') }}
                                    <span class="badge bg-primary-subtle text-primary border rounded-pill">{{ count($branchDashboardSummaries) }} {{ __('index.branch') }}</span>
                                </h4>
                                <p class="summary-panel-subtitle">{{ __('index.branch_summary_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button"
                                class="summary-panel-toggle"
                                data-bs-toggle="collapse"
                                data-bs-target="#branchSummaryCollapse"
                                aria-expanded="false"
                                aria-controls="branchSummaryCollapse"
                                title="Toggle table">
                            <i data-feather="chevron-down"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body collapse" id="branchSummaryCollapse">
                    <div class="summary-table-shell">
                        <table class="table table-striped branch-summary-table mb-0">
                            <thead>
                            <tr>
                                <th>{{ __('index.branch') }}</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <th class="text-center">{{ $metricLabel }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($branchDashboardSummaries as $branchSummary)
                                <tr>
                                    <td>
                                        <button type="button"
                                                class="summary-name-trigger"
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
                                                    class="summary-value-trigger summary-metric-{{ $metricKey }}"
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
                                    <td colspan="13" class="text-center py-3"><b>{{ __('index.no_records_found') }}</b></td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>{{ __('index.total') }}</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <td class="text-center">
                                        <button type="button"
                                                class="summary-value-trigger summary-metric-{{ $metricKey }}"
                                                data-summary-scope="branch"
                                                data-summary-metric="{{ $metricKey }}"
                                                data-entity-name="{{ __('index.all_branches') }}"
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

            <!-- Department Summary Panel -->
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
            <div class="summary-panel">
                <div class="card-header">
                    <div class="summary-panel-heading">
                        <div class="summary-panel-title-group">
                            <div class="summary-panel-icon">
                                <i data-feather="grid"></i>
                            </div>
                            <div>
                                <h4 class="summary-panel-title">
                                    {{ __('index.department_summary') }}
                                    <span class="badge bg-primary-subtle text-primary border rounded-pill">{{ count($departmentDashboardSummaries) }} {{ __('index.department') }}</span>
                                </h4>
                                <p class="summary-panel-subtitle">{{ __('index.department_summary_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button"
                                class="summary-panel-toggle"
                                data-bs-toggle="collapse"
                                data-bs-target="#departmentSummaryCollapse"
                                aria-expanded="false"
                                aria-controls="departmentSummaryCollapse"
                                title="Toggle table">
                            <i data-feather="chevron-down"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body collapse" id="departmentSummaryCollapse">
                    <div class="summary-table-shell">
                        <table class="table table-striped branch-summary-table mb-0">
                            <thead>
                            <tr>
                                <th>{{ __('index.department') }}</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <th class="text-center">{{ $metricLabel }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($departmentDashboardSummaries as $departmentSummary)
                                <tr>
                                    <td>
                                        <button type="button"
                                                class="summary-name-trigger"
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
                                                    class="summary-value-trigger summary-metric-{{ $metricKey }}"
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
                                    <td colspan="13" class="text-center py-3"><b>{{ __('index.no_records_found') }}</b></td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>{{ __('index.total') }}</th>
                                @foreach($summaryMetrics as $metricKey => $metricLabel)
                                    <td class="text-center">
                                        <button type="button"
                                                class="summary-value-trigger summary-metric-{{ $metricKey }}"
                                                data-summary-scope="department"
                                                data-summary-metric="{{ $metricKey }}"
                                                data-entity-name="{{ __('index.all_departments') }}"
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

            <!-- Summary Detail Modal -->
            <div class="modal fade" id="summaryDetailModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title fw-bold" id="summaryDetailModalLabel">{{ __('index.summary_detail') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div id="summaryDetailLoading" class="text-center py-5 d-none">
                                <div class="spinner-border text-primary" role="status"></div>
                                <div class="mt-2 text-muted fw-semibold">{{ __('index.loading') }}</div>
                            </div>
                            <div id="summaryDetailEmpty" class="text-center py-5 d-none text-muted fw-bold">{{ __('index.no_records_found') }}</div>
                            <div class="table-responsive">
                                <table class="table table-hover custom-dash-table mb-0">
                                    <thead>
                                    <tr>
                                        <th>{{ __('index.name') }}</th>
                                        <th>{{ __('index.employee_code') }}</th>
                                        <th>{{ __('index.email') }}</th>
                                        <th>{{ __('index.branch') }}</th>
                                        <th>{{ __('index.department') }}</th>
                                        <th>{{ __('index.status') }}</th>
                                        <th class="text-end">{{ __('index.quick_action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody id="summaryDetailTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Leave Modal -->
            <div class="modal fade" id="dashboardQuickLeaveModal" tabindex="-1" aria-labelledby="dashboardQuickLeaveModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title fw-bold" id="dashboardQuickLeaveModalLabel">{{ __('index.quick_leave') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('admin.attendances.quick-approved-leave') }}" method="post" id="dashboardQuickLeaveForm">
                                @csrf
                                <input type="hidden" name="user_id" id="dashboardQuickLeaveUserId">
                                <input type="hidden" name="attendance_date" id="dashboardQuickLeaveDate">
                                <div class="mb-3">
                                    <label for="dashboardQuickLeaveType" class="form-label fw-semibold">{{ __('index.leave_type') }}</label>
                                    <select class="form-select" name="leave_type_id" id="dashboardQuickLeaveType" required>
                                        <option value="">{{ __('index.loading_leave_types') }}</option>
                                    </select>
                                    <small class="text-muted d-block mt-2" id="dashboardQuickLeaveHelpText">
                                        {{ __('index.create_approved_leave_today') }}
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label for="dashboardQuickLeaveReason" class="form-label fw-semibold">{{ __('index.leave_reason') }}</label>
                                    <textarea class="form-control" name="reasons" id="dashboardQuickLeaveReason" rows="3" placeholder="{{ __('index.optional_note') }}"></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('index.cancel') ?? 'Cancel' }}</button>
                                    <button type="submit" class="btn btn-primary" id="dashboardQuickLeaveSubmit">{{ __('index.save_quick_leave') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Status Update Modal -->
            <div class="modal fade" id="dashboardLeaveStatusUpdate" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title fw-bold">{{ __('index.leave_status_update') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <form class="forms-sample" id="dashboardUpdateLeaveStatus" action="" method="post">
                                @csrf
                                @method('put')
                                <input type="hidden" name="redirect_back" value="1">
                                <div class="mb-3">
                                    <label for="dashboardLeaveStatus" class="form-label fw-semibold">{{ __('index.status') }}</label>
                                    <select class="form-select" id="dashboardLeaveStatus" name="status">
                                        <option value="{{ \App\Enum\LeaveStatusEnum::approved->value }}">{{ __('index.approve') }}</option>
                                        <option value="{{ \App\Enum\LeaveStatusEnum::rejected->value }}">{{ __('index.reject') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="dashboardLeaveRemark" class="form-label fw-semibold">{{ __('index.admin_remark') }}</label>
                                    <textarea class="form-control" id="dashboardLeaveRemark" minlength="10" name="admin_remark" rows="3" placeholder="Remark..."></textarea>
                                </div>
                                <div id="dashboardPreviousApprovers" class="mb-3"></div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('index.cancel') ?? 'Cancel' }}</button>
                                    <button type="submit" class="btn btn-primary">{{ __('index.submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        <!-- Project Management & Activity Hub -->
        @canany(['project_detail','client_detail'])
            <div class="projectManagement mt-4">
                <div class="project-section-heading">
                    <h4 class="project-section-title">
                        <i data-feather="briefcase" class="text-primary"></i>
                        {{ __('index.project_management') }}
                    </h4>
                </div>

                @can('project_detail')
                    <div class="row g-2 mb-3">
                        <!-- Project Details Chart -->
                        <div class="col-xxl-6 col-xl-6 d-flex">
                            <div class="card w-100 border shadow-sm">
                                <div class="card-header bg-transparent py-2 border-bottom d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0 fw-bold fs-6">{{ __('index.projects_detail') }}</h5>
                                    <span class="badge bg-light text-secondary border">Status Breakdown</span>
                                </div>
                                <div class="card-body py-2">
                                    <canvas id="projectChart" height="210"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Project Mini Metric Cards Grid -->
                        <div class="col-xxl-6 col-xl-6">
                            <div class="row g-2">
                                <div class="col-sm-6 d-flex">
                                    <div class="project-mini-card w-100" style="--pm-color: var(--dash-brand); --pm-bg: var(--dash-brand-soft);">
                                        <div>
                                            <div class="project-mini-title">{{ __('index.total_projects') }}</div>
                                            <div class="project-mini-val">{{ number_format($projectCardDetail['total_projects']) }}</div>
                                        </div>
                                        <div class="project-mini-icon"><i data-feather="layers"></i></div>
                                    </div>
                                </div>

                                <div class="col-sm-6 d-flex">
                                    <div class="project-mini-card w-100" style="--pm-color:#f59e0b; --pm-bg:#fffbeb;">
                                        <div>
                                            <div class="project-mini-title">{{ __('index.pending_projects') }}</div>
                                            <div class="project-mini-val">{{ number_format($projectCardDetail['not_started']) }}</div>
                                        </div>
                                        <div class="project-mini-icon"><i data-feather="clock"></i></div>
                                    </div>
                                </div>

                                <div class="col-sm-6 d-flex">
                                    <div class="project-mini-card w-100" style="--pm-color:#8b5cf6; --pm-bg:#f5f3ff;">
                                        <div>
                                            <div class="project-mini-title">{{ __('index.on_hold_projects') }}</div>
                                            <div class="project-mini-val">{{ number_format($projectCardDetail['on_hold']) }}</div>
                                        </div>
                                        <div class="project-mini-icon"><i data-feather="pause-circle"></i></div>
                                    </div>
                                </div>

                                <div class="col-sm-6 d-flex">
                                    <div class="project-mini-card w-100" style="--pm-color: var(--dash-brand); --pm-bg: var(--dash-brand-soft);">
                                        <div>
                                            <div class="project-mini-title">{{ __('index.in_progress_projects') }}</div>
                                            <div class="project-mini-val">{{ number_format($projectCardDetail['in_progress']) }}</div>
                                        </div>
                                        <div class="project-mini-icon"><i data-feather="play-circle"></i></div>
                                    </div>
                                </div>

                                <div class="col-sm-6 d-flex">
                                    <div class="project-mini-card w-100" style="--pm-color:#10b981; --pm-bg:#f0fdf4;">
                                        <div>
                                            <div class="project-mini-title">{{ __('index.finished_projects') }}</div>
                                            <div class="project-mini-val">{{ number_format($projectCardDetail['completed']) }}</div>
                                        </div>
                                        <div class="project-mini-icon"><i data-feather="check-circle"></i></div>
                                    </div>
                                </div>

                                <div class="col-sm-6 d-flex">
                                    <div class="project-mini-card w-100" style="--pm-color:#ef4444; --pm-bg:#fef2f2;">
                                        <div>
                                            <div class="project-mini-title">{{ __('index.cancelled_projects') }}</div>
                                            <div class="project-mini-val">{{ number_format($projectCardDetail['cancelled']) }}</div>
                                        </div>
                                        <div class="project-mini-icon"><i data-feather="x-circle"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                <!-- Clients & Tasks -->
                <div class="row g-2 mb-3">
                    @can('client_detail')
                        <div class="col-xxl-8 col-xl-8 d-flex">
                            <div class="card w-100 border shadow-sm">
                                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0 fw-bold fs-6">{{ __('index.top_clients') }}</h5>
                                    <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-outline-primary py-1 px-3 rounded-pill">{{ __('index.view_all_clients') }}</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table custom-dash-table mb-0">
                                            <thead>
                                            <tr>
                                                <th>{{ __('index.name') }}</th>
                                                <th>{{ __('index.email') }}</th>
                                                <th class="text-center">{{ __('index.contact') }}</th>
                                                <th class="text-center">{{ __('index.project') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($topClients as $key => $client)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.clients.show', $client->id) }}" class="client-avatar-link">
                                                            <img class="client-avatar-img"
                                                                 alt="{{ $client->name }}"
                                                                 src="{{ $client->avatar ? asset(Client::UPLOAD_PATH . $client->avatar) : asset('assets/images/img.png') }}">
                                                            <span>{{ ucfirst($client->name) }}</span>
                                                        </a>
                                                    </td>
                                                    <td>{{ $client->email }}</td>
                                                    <td class="text-center">{{ $client->contact_no }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-2.5">{{ $client->project_count }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted"><b>{{ __('index.no_records_found') }}</b></td>
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
                        <div class="col-xxl-4 col-xl-4 d-flex">
                            <div class="card w-100 border shadow-sm">
                                <div class="card-header bg-transparent py-3 border-bottom text-center">
                                    <h5 class="card-title mb-0 fw-bold fs-6">{{ __('index.task_details') }}</h5>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center p-3">
                                    <div style="max-width: 280px; width: 100%;">
                                        <canvas id="tasksChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>

                <!-- Recent Projects Table -->
                @can('project_detail')
                    <div class="card border shadow-sm">
                        <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold fs-6">{{ __('index.recent_projects') }}</h5>
                            <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-outline-primary py-1 px-3 rounded-pill">{{ __('index.view_all_projects') }}</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table custom-dash-table mb-0">
                                    <thead>
                                    <tr>
                                        <th style="width: 28%;">{{ __('index.title') }}</th>
                                        <th class="text-center">{{ __('index.date_start') }}</th>
                                        <th class="text-center">{{ __('index.deadline') }}</th>
                                        <th class="text-center">{{ __('index.leader') }}</th>
                                        <th style="width: 18%;" class="text-center">{{ __('index.completion') }}</th>
                                        <th class="text-center">{{ __('index.priority') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($recentProjects as $key => $project)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.projects.show', $project->id) }}" class="fw-bold text-decoration-none text-dark">
                                                    {{ ucfirst($project->name) }}
                                                </a>
                                            </td>
                                            <td class="text-center text-muted">{{ AppHelper::formatDateForView($project->start_date) }}</td>
                                            <td class="text-center text-muted">{{ AppHelper::formatDateForView($project->deadline) }}</td>
                                            <td class="text-center">
                                                <div class="d-inline-flex align-items-center">
                                                    @forelse($project->projectLeaders as $key => $leader)
                                                        <span class="d-inline-block {{ $key > 0 ? 'ms-n2' : '' }}"
                                                              data-bs-toggle="tooltip"
                                                              title="{{ $leader->user ? ucfirst($leader->user->name) : 'Project Leader' }}">
                                                            <img class="rounded-circle border border-2 border-white"
                                                                 style="width: 28px; height: 28px; object-fit: cover;"
                                                                 src="{{ ($leader->user && $leader->user->avatar) ? asset(User::AVATAR_UPLOAD_PATH . $leader->user->avatar) : asset('assets/images/img.png') }}"
                                                                 alt="Leader">
                                                        </span>
                                                    @empty
                                                        <span class="text-muted small">--</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td>
                                                @php $progressPct = $project->getProjectProgressInPercentage(); @endphp
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress progress-slim flex-grow-1">
                                                        <div class="progress-bar bg-primary rounded-pill"
                                                             role="progressbar"
                                                             style="width: {{ $progressPct }}%;"
                                                             aria-valuenow="{{ $progressPct }}"
                                                             aria-valuemin="0"
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="small fw-bold text-muted" style="min-width:35px; text-align:right;">{{ $progressPct }}%</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="priority-badge {{ $projectPriorityClasses[$project->priority] ?? 'priority-badge-low' }}">
                                                    {{ ucfirst($project->priority) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted"><b>{{ __('index.no_records_found') }}</b></td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
        @endcanany
    </section>
@endsection

<script src="{{ asset('assets/vendors/chartjs/Chart.min.js') }}"></script>

@section('scripts')
    <script>
        let translatedStrings = @json(__('index'));
    </script>
    @include('admin.dashboard_scripts')
@endsection
