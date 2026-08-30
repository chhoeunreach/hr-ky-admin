@php use App\Helpers\AppHelper; @endphp
@php use App\Helpers\AttendanceHelper; @endphp
@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', __('index.employee_attendance_detail'))

@section('button')
    <a href="{{ route('admin.attendances.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon"
                                                  data-feather="arrow-left"></i> {{ __('index.back') }}</button>
    </a>
@endsection

@section('styles')
    <style>
        .attendance-status-stack {
            position: relative;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            min-width: 128px;
            overflow: visible;
        }

        .attendance-status-stack .quickApproveLeaveTrigger,
        .attendance-status-stack .quickApproveTimeLeaveTrigger {
            position: absolute;
            left: 50%;
            z-index: 7;
            opacity: 0;
            visibility: hidden;
            white-space: nowrap;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.16);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease, box-shadow 0.18s ease;
        }

        .attendance-status-stack .quickApproveLeaveTrigger {
            bottom: 0;
            transform: translate(-50%, 118%) scale(0.98);
        }

        .attendance-status-stack .quickApproveTimeLeaveTrigger {
            top: 0;
            transform: translate(-50%, -118%) scale(0.98);
        }

        .attendance-detail-row:hover .attendance-status-stack .quickApproveLeaveTrigger,
        .attendance-detail-row:hover .attendance-status-stack .quickApproveTimeLeaveTrigger,
        .attendance-status-stack:hover .quickApproveLeaveTrigger,
        .attendance-status-stack:hover .quickApproveTimeLeaveTrigger,
        .attendance-status-stack .quickApproveLeaveTrigger:focus,
        .attendance-status-stack .quickApproveTimeLeaveTrigger:focus {
            opacity: 1;
            visibility: visible;
        }

        .attendance-detail-row:hover .attendance-status-stack .quickApproveLeaveTrigger,
        .attendance-status-stack:hover .quickApproveLeaveTrigger,
        .attendance-status-stack .quickApproveLeaveTrigger:focus {
            transform: translate(-50%, 118%) scale(1);
        }

        .attendance-detail-row:hover .attendance-status-stack .quickApproveTimeLeaveTrigger,
        .attendance-status-stack:hover .quickApproveTimeLeaveTrigger,
        .attendance-status-stack .quickApproveTimeLeaveTrigger:focus {
            transform: translate(-50%, -118%) scale(1);
        }

        .attendance-detail-legend {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 7px 12px;
            padding: 9px 12px;
            border: 1px solid #d8e1ee;
            border-radius: 8px;
            background: #ffffff;
            color: #334155;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
        }

        .attendance-detail-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 700;
        }

        .attendance-detail-legend-badge {
            min-width: 19px;
            height: 19px;
            padding: 0 4px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 8px;
            font-weight: 900;
            line-height: 1;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.16);
        }

        .attendance-legend-present { background: #39b54a; }
        .attendance-legend-late { background: #f97316; }
        .attendance-legend-absent { background: #ef232a; }
        .attendance-legend-off { background: #cbd5e1; color: #475569; }
        .attendance-legend-leave { background: #7c3aed; }
        .attendance-legend-pending { background: #f59e0b; }
        .attendance-legend-time { background: #0891b2; }
        .attendance-legend-danger { background: #dc2626; }

        .attendance-status-badges {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 4px;
        }

        .attendance-status-pill {
            min-width: 23px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 9px;
            font-weight: 900;
            line-height: 1;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.16);
        }

        .attendance-status-pill.is-present { background: #39b54a; }
        .attendance-status-pill.is-late { background: #f97316; }
        .attendance-status-pill.is-early { background: #0ea5e9; }
        .attendance-status-pill.is-absent { background: #ef232a; }
        .attendance-status-pill.is-off { background: #cbd5e1; color: #475569; }
        .attendance-status-pill.is-leave { background: #7c3aed; }
        .attendance-status-pill.is-pending { background: #f59e0b; }
        .attendance-status-pill.is-time { background: #0891b2; }
        .attendance-status-pill.is-danger { background: #dc2626; }

        .attendance-time-cell {
            position: relative;
            overflow: visible;
        }

        .attendance-overlay-badge {
            position: absolute;
            top: 5px;
            right: calc(50% - 66px);
            z-index: 8;
            pointer-events: auto;
        }

        .attendance-overlay-badge .attendance-status-pill {
            min-width: 17px;
            height: 16px;
            padding: 0 4px;
            border: 1px solid #ffffff;
            font-size: 7px;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.18);
        }

        .employee-attendance-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(168px, 1fr));
            gap: 8px;
            margin-bottom: 14px;
        }

        .employee-attendance-dashboard-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 2px 0 10px;
        }

        .employee-attendance-dashboard-actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .employee-attendance-dashboard-title h6 {
            margin: 0;
            color: #0f172a;
            font-size: 15px;
            font-weight: 900;
        }

        .employee-attendance-dashboard-title p {
            margin: 2px 0 0;
            color: #64748b;
            font-size: 12px;
        }

        .employee-attendance-card {
            min-height: 62px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 5px 14px rgba(15, 23, 42, 0.04);
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 10px;
        }

        .employee-attendance-card-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 30px;
            font-size: 10px;
            font-weight: 900;
        }

        .employee-attendance-card-title {
            margin: 0;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.15;
        }

        .employee-attendance-card-value {
            margin: 2px 0 0;
            color: #0f172a;
            font-size: 18px;
            font-weight: 900;
            line-height: 1.05;
        }

        .employee-attendance-card-note {
            margin: 1px 0 0;
            color: #64748b;
            font-size: 9px;
        }

        .employee-attendance-card.is-highlight {
            border-color: #bfdbfe;
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
        }

        .employee-attendance-card.is-present .employee-attendance-card-icon { background: #ecfdf3; color: #16a34a; }
        .employee-attendance-card.is-late .employee-attendance-card-icon { background: #fff7ed; color: #f97316; }
        .employee-attendance-card.is-absent .employee-attendance-card-icon { background: #fef2f2; color: #dc2626; }
        .employee-attendance-card.is-leave .employee-attendance-card-icon { background: #f5f3ff; color: #7c3aed; }
        .employee-attendance-card.is-off .employee-attendance-card-icon { background: #f1f5f9; color: #64748b; }
        .employee-attendance-card.is-total .employee-attendance-card-icon { background: #eff6ff; color: #2563eb; }
        .employee-attendance-card.is-pending .employee-attendance-card-icon { background: #fffbeb; color: #d97706; }
        .employee-attendance-card.is-time .employee-attendance-card-icon { background: #ecfeff; color: #0891b2; }
        .employee-attendance-card.is-danger .employee-attendance-card-icon { background: #fef2f2; color: #dc2626; }
        .employee-attendance-card.is-highlight .employee-attendance-card-icon { background: #dbeafe; color: #2563eb; }

        .employee-attendance-dashboard-print-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 32px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
        }

        .attendance-filter-card,
        .attendance-table-card {
            border: 1px solid #e6ebf2;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .attendance-filter-card .card-header,
        .attendance-table-card .card-header {
            padding: 12px 16px;
            background: #ffffff;
            border-bottom: 1px solid #eef2f7;
        }

        .attendance-filter-card .card-title,
        .attendance-table-card .card-title {
            color: #0f172a;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .attendance-filter-card .card-body {
            padding: 14px 16px 10px;
        }

        .attendance-filter-card .form-control,
        .attendance-filter-card .form-select {
            min-height: 40px;
            border-color: #dbe3ee;
            border-radius: 8px;
            box-shadow: none;
            font-size: 13px;
        }

        .attendance-filter-card .btn {
            min-height: 40px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
        }

        .attendance-table-card .card-body {
            padding: 12px 16px 16px;
        }

        .attendance-detail-table {
            margin-bottom: 0;
            border-color: #e6ebf2;
        }

        .attendance-detail-table thead th {
            padding: 7px 12px;
            background: #f8fafc;
            color: #334155;
            border-bottom: 1px solid #e6ebf2;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
            vertical-align: middle;
        }

        .attendance-detail-table tbody td {
            padding: 5px 12px;
            border-color: #e8edf4;
            color: #1f2937;
            vertical-align: middle;
        }

        .attendance-detail-table td:nth-child(2),
        .attendance-detail-table td:nth-child(4) {
            padding-left: 6px;
            padding-right: 6px;
        }

        .attendance-detail-table .btn-xs {
            min-height: 22px;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .attendance-detail-table .checkLocation {
            min-width: 112px;
            color: #64748b;
            border-color: #8fa1bf;
            background: #ffffff;
        }

        .attendance-detail-table td:nth-child(2) .checkLocation,
        .attendance-detail-table td:nth-child(4) .checkLocation {
            min-height: 22px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.2;
        }

        .attendance-print-only {
            display: none;
        }

        .attendance-print-signatures {
            display: none;
        }

        .attendance-remark-cell {
            max-width: 180px;
            color: #334155;
            font-size: 12px;
            line-height: 1.25;
            word-break: break-word;
        }

        .attendance-detail-table .checkLocation:hover {
            color: #334155;
            border-color: #64748b;
            background: #f8fafc;
        }

        .attendance-detail-table .attendance-detail-row:hover td {
            background: #fbfdff;
        }

        .attendance-detail-table .btn-success {
            background: #059b4a;
            border-color: #059b4a;
        }

        .attendance-detail-table .btn-info {
            background: #5ccccc;
            border-color: #5ccccc;
            color: #ffffff;
        }

        @media (max-width: 991.98px) {
            .employee-attendance-dashboard {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .attendance-filter-card .card-body,
            .attendance-table-card .card-body {
                padding-left: 12px;
                padding-right: 12px;
            }
        }

        @media (max-width: 575.98px) {
            .employee-attendance-dashboard {
                grid-template-columns: 1fr;
            }

            .employee-attendance-dashboard-title,
            .employee-attendance-dashboard-actions {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 4mm;
            }

            body * {
                visibility: hidden;
            }

            .employee-attendance-print-area,
            .employee-attendance-print-area * {
                visibility: visible;
            }

            .employee-attendance-print-area {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                padding: 0;
                background: #ffffff;
            }

            .employee-attendance-dashboard-print-button,
            .attendance-print-hide,
            .employee-attendance-print-area .quickApproveLeaveTrigger,
            .employee-attendance-print-area .quickApproveTimeLeaveTrigger {
                display: none !important;
            }

            .employee-attendance-dashboard-title {
                border-bottom: 1px solid #dbe3ee;
                padding-bottom: 3px;
                margin: 0 0 4px;
            }

            .employee-attendance-dashboard {
                display: grid !important;
                grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
                gap: 2px;
                margin-bottom: 4px;
                width: 100%;
            }

            .employee-attendance-card {
                width: auto;
                min-width: 0;
                min-height: 28px;
                padding: 2px 3px;
                break-inside: avoid;
                box-shadow: none;
                gap: 3px;
            }

            .employee-attendance-card-icon {
                width: 15px;
                height: 15px;
                flex-basis: 15px;
                font-size: 5px;
            }

            .employee-attendance-card-title {
                font-size: 6px;
                white-space: normal;
            }

            .employee-attendance-card-value {
                font-size: 9px;
                white-space: nowrap;
            }

            .employee-attendance-card-note {
                font-size: 5.5px;
                white-space: normal;
            }

            .attendance-detail-legend {
                gap: 2px 5px;
                padding: 2px 0;
                margin-bottom: 4px !important;
                box-shadow: none;
            }

            .attendance-detail-legend span {
                font-size: 6.5px;
            }

            .attendance-detail-legend-badge {
                min-width: 11px;
                height: 11px;
                font-size: 5px;
            }

            .attendance-table-card {
                border: 0;
                box-shadow: none;
                overflow: visible;
            }

            .attendance-table-card .card-header {
                padding: 3px 0;
                border-bottom: 1px solid #dbe3ee;
            }

            .attendance-table-card .card-title {
                font-size: 9px;
            }

            .attendance-table-card .card-body,
            .attendance-table-card .table-responsive {
                padding: 0;
                overflow: visible;
            }

            .attendance-detail-table {
                width: 100% !important;
                table-layout: fixed;
                border-collapse: collapse;
                font-size: 7.5px;
            }

            .attendance-detail-table thead th,
            .attendance-detail-table tbody td {
                padding: 0 1px;
                white-space: normal;
                word-break: break-word;
            }

            .attendance-detail-table td:nth-child(2),
            .attendance-detail-table td:nth-child(4) {
                padding: 0 1px;
                font-size: 7.5px;
            }

            .attendance-detail-table th:nth-child(9),
            .attendance-detail-table th:nth-child(10),
            .attendance-detail-table td:nth-child(9),
            .attendance-detail-table td:nth-child(10) {
                display: none !important;
            }

            .attendance-print-only {
                display: table-cell !important;
            }

            .attendance-print-status {
                color: #15803d;
                font-size: 7.5px;
                font-weight: 800;
                line-height: 1.15;
            }

            .attendance-print-status.is-alert {
                color: #dc2626;
            }

            .attendance-print-day-status {
                display: block;
                color: #7c3aed;
                font-size: 7.5px;
                font-weight: 800;
                line-height: 1.15;
            }

            .attendance-print-day-status.is-off {
                color: #64748b;
            }

            .attendance-print-day-status.is-pending {
                color: #d97706;
            }

            .attendance-print-day-status.is-time {
                color: #0891b2;
            }

            .attendance-print-signatures {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10mm;
                margin-top: 10mm;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .attendance-print-signature {
                min-height: 18mm;
                display: flex;
                align-items: flex-end;
                justify-content: center;
                color: #111827;
                font-size: 8px;
                font-weight: 800;
                text-align: center;
            }

            .attendance-print-signature-line {
                width: 100%;
                border-top: 1px solid #111827;
                padding-top: 3px;
            }

            .attendance-detail-table .btn-xs {
                min-height: 12px;
                padding: 0 2px;
                border-radius: 3px;
                font-size: 5.5px;
                line-height: 1.2;
            }

            .attendance-detail-table .checkLocation {
                min-width: 0;
                min-height: 0;
                padding: 0;
                border: 0;
                background: transparent;
                color: #15803d;
                box-shadow: none;
                font-size: 7.5px !important;
                font-weight: 800;
                line-height: 1.15;
            }

            .attendance-remark-cell {
                max-width: none;
                font-size: 6.5px;
                line-height: 1.15;
            }

            .attendance-status-pill {
                min-width: 13px;
                height: 11px;
                padding: 0 2px;
                font-size: 5px;
            }

            .attendance-overlay-badge {
                top: 0;
                right: 1px;
            }

            .attendance-overlay-badge .attendance-status-pill {
                min-width: 11px;
                height: 10px;
                padding: 0 2px;
                border-width: 1px;
                font-size: 4.5px;
                box-shadow: none;
            }
        }
    </style>
@endsection

@section('main-content')
    <?php
    if ($isBsEnabled) {
        $filterData['min_year'] = '2076';
        $filterData['max_year'] = '2089';
        $filterData['month'] = 'np';
        $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
        $filterData['current_year'] = $nepaliDate['year'];
        $filterData['current_month'] = $nepaliDate['month'];
    } else {
        $filterData['min_year'] = '2020';
        $filterData['max_year'] = '2033';
        $filterData['current_year'] = now()->format('Y');
        $filterData['current_month'] = now()->month;
        $filterData['month'] = 'en';
    }
    ?>

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.attendance.common.breadcrumb')
        <div class="card attendance-filter-card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_of') . ' ' .ucfirst($userDetail->name) }}</h6>
            </div>
            <div class="card-body">
                <form class="forms-sample" action="{{ route('admin.attendances.show', $userDetail->id) }}"
                    method="get">
                    <div class="row align-items-center g-2">
                        <div class="col-lg-4 col-md-3">
                            <input type="number" min="{{ $filterData['min_year'] }}"
                                max="{{ $filterData['max_year'] }}" step="1"
                                placeholder="{{ __('index.attendance_year_example', ['year' => $filterData['min_year']]) }}"
                                id="year"
                                name="year"
                                value="{{ $filterParameter['year'] }}"
                                class="form-control">
                        </div>

                        <div class="col-lg-4 col-md-3">
                            <select class="form-select" name="month" id="month">
                                <option
                                    value="" {{ !isset($filterParameter['month']) ? 'selected' : '' }}>{{ __('index.all_month') }}</option>
                                @foreach($months as $key => $value)
                                    <option
                                        value="{{ $key }}" {{ (isset($filterParameter['month']) && $key == $filterParameter['month']) ? 'selected' : '' }}>
                                        {{ $value[$filterData['month']] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg col-md-3">
                            <button type="submit"
                                    class="btn btn-block btn-success">{{ __('index.filter') }}</button>
                        </div>

                        @can('attendance_csv_export')
                            <div class="col-lg col-md-3">
                                <button type="button" id="download-excel"
                                        data-href="{{ route('admin.attendances.show', $userDetail->id) }}"
                                        class="btn btn-block btn-secondary">
                                    {{ __('index.csv_export') }}
                                </button>
                            </div>
                        @endcan

                        <div class="col-lg col-md-3">
                            <a class="btn btn-block btn-primary"
                            href="{{ route('admin.attendances.show', $userDetail->id) }}">{{ __('index.reset') }}</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        @php
            $isDayOffLeave = static function ($leaveRequest): bool {
                $leaveTypeName = strtolower((string) ($leaveRequest->leaveType?->name ?? ''));

                return str_contains($leaveTypeName, 'day off') || str_contains($leaveTypeName, 'ឈប់សម្រាក');
            };

            $detailCardStats = [
                'total' => (int) ($attendanceSummary['totalDays'] ?? 0),
                'present' => (int) ($attendanceSummary['totalPresent'] ?? 0),
                'sun' => (int) ($attendanceSummary['totalWeekend'] ?? 0),
                'late' => 0,
                'absent' => (int) ($attendanceSummary['totalAbsent'] ?? 0),
                'leave' => 0,
                'off_day' => (int) (($attendanceSummary['totalWeekend'] ?? 0) + ($attendanceSummary['totalHoliday'] ?? 0)),
                'pending_day_off' => 0,
                'pending_leave' => 0,
                'time_leave' => 0,
                'time_leave_request' => 0,
                'no_checkout' => 0,
            ];
            $lateDates = [];
            $noCheckoutDates = [];
            $presentCheckInDates = [];
            $manualLateGraceMinutes = 16;
            $officeOpeningTime = $userDetail->officeTime?->opening_time;
            $lateAllowedUntil = $officeOpeningTime
                ? \Carbon\Carbon::parse($officeOpeningTime)->addMinutes($manualLateGraceMinutes)->format('h:i A')
                : null;
            $officeOpeningLabel = $officeOpeningTime
                ? \Carbon\Carbon::parse($officeOpeningTime)->format('h:i A')
                : null;
            $officeClosingTime = $userDetail->officeTime?->closing_time;
            $officeClosingLabel = $officeClosingTime
                ? \Carbon\Carbon::parse($officeClosingTime)->format('h:i A')
                : null;

            foreach ($leaveRequestsByDate ?? [] as $leaveRequest) {
                $status = strtolower((string) $leaveRequest->status);
                $isDayOff = $isDayOffLeave($leaveRequest);

                if ($status === 'pending') {
                    $isDayOff ? $detailCardStats['pending_day_off']++ : $detailCardStats['pending_leave']++;
                }
            }

            foreach ($leaveRequestsByDate ?? [] as $leaveRequest) {
                $status = strtolower((string) $leaveRequest->status);
                $isDayOff = $isDayOffLeave($leaveRequest);

                if ($status === 'approved') {
                    $isDayOff ? $detailCardStats['off_day']++ : $detailCardStats['leave']++;
                }
            }

            if ($detailCardStats['leave'] === 0) {
                $detailCardStats['leave'] = (int) ($attendanceSummary['totalLeave'] ?? 0);
            }

            foreach ($timeLeavesByDate ?? [] as $timeLeave) {
                $status = strtolower((string) $timeLeave->status);

                if ($status === 'approved') {
                    $detailCardStats['time_leave']++;
                } elseif ($status === 'pending') {
                    $detailCardStats['time_leave_request']++;
                }
            }

            foreach ($attendanceDetail as $dayData) {
                foreach (($dayData['data'] ?? []) as $attendance) {
                    $checkIn = $attendance['check_in_at'] ?? $attendance['night_checkin'] ?? null;
                    $checkOut = $attendance['check_out_at'] ?? $attendance['night_checkout'] ?? null;
                    $attendanceApproved = $attendance['attendance_status'] === null
                        || $attendance['attendance_status'] == \App\Models\Attendance::ATTENDANCE_APPROVED;

                    if ($checkIn && $attendanceApproved) {
                        $presentCheckInDates[$dayData['attendance_date']] = true;
                    }

                    if ($checkIn && !$checkOut) {
                        $noCheckoutDates[$dayData['attendance_date']] = true;
                    }

                    $shiftOpening = $attendance['opening_time'] ?? $userDetail->officeTime?->opening_time ?? null;
                    if ($checkIn && $shiftOpening) {
                        $allowedCheckIn = \Carbon\Carbon::parse($shiftOpening)->addMinutes($manualLateGraceMinutes);

                        if (
                            \Carbon\Carbon::parse($checkIn)->gt($allowedCheckIn)
                            && $attendanceApproved
                        ) {
                            $lateDates[$dayData['attendance_date']] = true;
                        }
                    }
                }
            }

            $detailCardStats['late'] = count($lateDates);
            $detailCardStats['no_checkout'] = count($noCheckoutDates);
            $detailCardStats['present'] = count($presentCheckInDates);

            $detailCards = [
                ['key' => 'total', 'class' => 'is-highlight', 'icon' => 'SUM', 'title' => 'Total', 'note' => 'Calendar days'],
                ['key' => 'sun', 'class' => 'is-off', 'icon' => 'SUN', 'title' => 'Sun', 'note' => 'Weekend days'],
                ['key' => 'present', 'class' => 'is-present', 'icon' => 'P', 'title' => 'Present', 'note' => 'Check-in days'],
                ['key' => 'late', 'class' => 'is-late', 'icon' => 'L', 'title' => 'Late', 'note' => 'After office rule'],
                ['key' => 'absent', 'class' => 'is-absent', 'icon' => 'A', 'title' => 'Absent', 'note' => 'No attendance'],
                ['key' => 'leave', 'class' => 'is-leave', 'icon' => 'LV', 'title' => 'Leave', 'note' => 'Approved leave'],
                ['key' => 'off_day', 'class' => 'is-off', 'icon' => 'O', 'title' => 'Off Day', 'note' => 'Weekend/holiday'],
                ['key' => 'pending_day_off', 'class' => 'is-pending', 'icon' => 'PO', 'title' => 'Pending Day Off', 'note' => 'Waiting approval'],
                ['key' => 'pending_leave', 'class' => 'is-pending', 'icon' => 'PL', 'title' => 'Pending Leave', 'note' => 'Waiting approval'],
                ['key' => 'time_leave', 'class' => 'is-time', 'icon' => 'TL', 'title' => 'Time Leave', 'note' => 'Approved hours'],
                ['key' => 'time_leave_request', 'class' => 'is-pending', 'icon' => 'TR', 'title' => 'Time Leave Request', 'note' => 'Waiting approval'],
                ['key' => 'no_checkout', 'class' => 'is-danger', 'icon' => 'NC', 'title' => 'No Checkout', 'note' => 'Open attendance'],
            ];

            $statusBadge = static function (string $code, string $label, string $class, ?string $title = null): string {
                $title = $title ?: $label;

                return '<span class="attendance-status-pill '.$class.'" title="'.e($title).'">'.e($code).'</span>';
            };

            $formatRuleMinutes = static function (int $minutes): string {
                if ($minutes <= 0) {
                    return 'Ok';
                }

                $hours = intdiv($minutes, 60);
                $remainingMinutes = $minutes % 60;

                return trim(($hours ? $hours.'h ' : '').($remainingMinutes ? $remainingMinutes.'m' : ''));
            };
        @endphp

        <div class="employee-attendance-print-area">
            <div class="employee-attendance-dashboard-title">
                <div>
                    <h6>Employee Attendance Dashboard</h6>
                    <p>{{ $userDetail->name }} - {{ $monthName }} {{ $filterParameter['year'] }}</p>
                </div>
                <div class="employee-attendance-dashboard-actions">
                    <span class="text-muted small">
                        Working {{ $attendanceSummary ? $attendanceSummary['totalWorkingHours'] : '-' }}
                    </span>
                    <button type="button"
                            id="print-employee-attendance-dashboard"
                            class="btn btn-sm btn-outline-secondary employee-attendance-dashboard-print-button">
                        <i class="link-icon" data-feather="printer"></i> Print Dashboard
                    </button>
                </div>
            </div>

            <div class="employee-attendance-dashboard">
                @foreach($detailCards as $card)
                    <div class="employee-attendance-card {{ $card['class'] }}">
                        <span class="employee-attendance-card-icon">{{ $card['icon'] }}</span>
                        <div>
                            <p class="employee-attendance-card-title">{{ $card['title'] }}</p>
                            <p class="employee-attendance-card-value">{{ number_format($detailCardStats[$card['key']] ?? 0) }}</p>
                            <p class="employee-attendance-card-note">{{ $card['note'] }}</p>
                        </div>
                    </div>
                @endforeach
                <div class="employee-attendance-card is-total">
                    <span class="employee-attendance-card-icon"><i data-feather="clock"></i></span>
                    <div>
                        <p class="employee-attendance-card-title">Worked Hours</p>
                        <p class="employee-attendance-card-value" style="font-size: 16px;">{{ $attendanceSummary ? $attendanceSummary['totalWorkedHours'] : '-' }}</p>
                        <p class="employee-attendance-card-note">of {{ $attendanceSummary ? $attendanceSummary['totalWorkingHours'] : '-' }}</p>
                    </div>
                </div>
                <div class="employee-attendance-card is-late">
                    <span class="employee-attendance-card-icon">OK</span>
                    <div>
                        <p class="employee-attendance-card-title">Not Late Until</p>
                        <p class="employee-attendance-card-value">{{ $lateAllowedUntil ?: '-' }}</p>
                        <p class="employee-attendance-card-note">
                            {{ $officeOpeningLabel ? $officeOpeningLabel.' + '.$manualLateGraceMinutes.' min' : 'Office time not set' }}
                        </p>
                    </div>
                </div>
                <div class="employee-attendance-card is-time">
                    <span class="employee-attendance-card-icon">OT</span>
                    <div>
                        <p class="employee-attendance-card-title">Office Time</p>
                        <p class="employee-attendance-card-value">
                            {{ $officeOpeningLabel && $officeClosingLabel ? $officeOpeningLabel.' - '.$officeClosingLabel : '-' }}
                        </p>
                        <p class="employee-attendance-card-note">Employee shift rule</p>
                    </div>
                </div>
            </div>

            <div class="attendance-detail-legend mb-3">
                <span><i class="attendance-detail-legend-badge attendance-legend-present">P</i> Present</span>
                <span><i class="attendance-detail-legend-badge attendance-legend-late">L</i> Late</span>
                <span><i class="attendance-detail-legend-badge attendance-legend-absent">A</i> Absent</span>
                <span><i class="attendance-detail-legend-badge attendance-legend-off">O</i> Day Off</span>
                <span><i class="attendance-detail-legend-badge attendance-legend-leave">LV</i> ច្បាប់ផ្សេង</span>
                <span><i class="attendance-detail-legend-badge attendance-legend-pending">PO</i> Pending Day Off</span>
                <span><i class="attendance-detail-legend-badge attendance-legend-pending">PL</i> Pending Leave</span>
                <span><i class="attendance-detail-legend-badge attendance-legend-time">TL</i> Time Leave</span>
                <span><i class="attendance-detail-legend-badge attendance-legend-pending">TR</i> Time Leave Request</span>
                <span><i class="attendance-detail-legend-badge attendance-legend-danger">NC</i> No Checkout</span>
            </div>

        <div class="card attendance-table-card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_details_of', ['month' => $monthName]) }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table attendance-detail-table">
                        <thead>
                        <tr>
                            <th>{{ __('index.date') }}</th>
                            <th style="text-align: center;">{{ __('index.check_in_at') }}</th>
                            <th class="attendance-print-only" style="text-align: center;">In Status</th>
                            <th style="text-align: center;">{{ __('index.check_out_at') }}</th>
                            <th class="attendance-print-only" style="text-align: center;">Out Status</th>
                            <th style="text-align: center;">{{ __('index.worked_hour') }}</th>
                            <th class="attendance-print-only" style="text-align: center;">Warning</th>
                            <th class="attendance-print-only" style="text-align: center;">Remark</th>
                            <th style="text-align: center;">{{ __('index.status') }}</th>
                            <th style="text-align: center;">{{ __('index.shift') }}</th>
                            @can('attendance_update')
                                <th class="attendance-print-hide" style="text-align: center;">{{ __('index.action') }}</th>
                            @endcan
                        </tr>
                        </thead>

                            @php
                            $changeColor = [
                                0 => 'danger',
                                1 => 'success',
                            ]
                            @endphp
                            @php
                                $leaveRequestColor = [
                                    'approved' => 'success',
                                    'pending' => 'secondary',
                                    'rejected' => 'danger',
                                    'cancelled' => 'danger',
                                ];
                            @endphp

                        @forelse($attendanceDetail as $dayIndex => $dayData)
                            @php
                                $totalMinutes = 0;
                                $isFirstIteration = true;
                                $leaveRequest = $leaveRequestsByDate[$dayData['attendance_date']] ?? null;
                                $timeLeave = $timeLeavesByDate[$dayData['attendance_date']] ?? null;
                                $printDayStatus = null;
                                $printRemark = null;

                                if ($leaveRequest) {
                                    $leaveStatus = strtolower((string) $leaveRequest->status);
                                    $leaveIsDayOff = $isDayOffLeave($leaveRequest);
                                    $leaveName = $leaveRequest->leaveType ? ucfirst($leaveRequest->leaveType->name) : __('index.leave_request');
                                    $leaveReason = trim(strip_tags((string) $leaveRequest->reasons));
                                    $leaveAdminRemark = trim(strip_tags((string) $leaveRequest->admin_remark));
                                    $printDayStatus = [
                                        'label' => $leaveName.' ('.ucfirst($leaveStatus).')',
                                        'class' => $leaveStatus === 'pending' ? 'is-pending' : ($leaveIsDayOff ? 'is-off' : 'is-leave'),
                                    ];
                                    $printRemark = $leaveReason ?: $leaveAdminRemark;
                                } elseif ($timeLeave) {
                                    $timeLeaveStatus = strtolower((string) $timeLeave->status);
                                    $timeLeaveReason = trim(strip_tags((string) $timeLeave->reasons));
                                    $timeLeaveAdminRemark = trim(strip_tags((string) $timeLeave->admin_remark));
                                    $printDayStatus = [
                                        'label' => ($timeLeaveStatus === 'approved' ? 'Time Leave' : 'Time Leave Request').' ('.ucfirst($timeLeaveStatus).')',
                                        'class' => $timeLeaveStatus === 'approved' ? 'is-time' : 'is-pending',
                                    ];
                                    $printRemark = $timeLeaveReason ?: $timeLeaveAdminRemark;
                                }

                            @endphp
                        <tbody>
                            @if(isset($dayData['data']) && count($dayData['data']) > 0)
                                @foreach($dayData['data'] as $attendance)

                                    @php
                                        $totalMinutes += $attendance['worked_hour'];
                                        $attendanceCheckIn = $attendance['check_in_at'] ?? $attendance['night_checkin'] ?? null;
                                        $attendanceCheckOut = $attendance['check_out_at'] ?? $attendance['night_checkout'] ?? null;
                                        $attendanceNoCheckout = $attendanceCheckIn && !$attendanceCheckOut;
                                        $attendanceLate = false;
                                        $attendanceEarlyCheckout = false;
                                        $checkInLateMinutes = 0;
                                        $checkOutEarlyMinutes = 0;
                                        $attendanceRejected = $attendance['attendance_status'] !== null && $attendance['attendance_status'] != \App\Models\Attendance::ATTENDANCE_APPROVED;
                                        $allowedCheckInLabel = null;
                                        $allowedCheckOutLabel = null;
                                        $shiftOpening = $attendance['opening_time'] ?? $userDetail->officeTime?->opening_time ?? null;
                                        $shiftClosing = $attendance['closing_time'] ?? $userDetail->officeTime?->closing_time ?? null;
                                        $checkoutBefore = $attendance['checkout_before'] ?? $userDetail->officeTime?->checkout_before ?? null;
                                        $manualLateGraceMinutes = 16;
                                        $earlyCheckoutEnabled = (int) ($attendance['is_early_check_out'] ?? $userDetail->officeTime?->is_early_check_out ?? 0) === 1;

                                        if ($attendanceCheckIn && $shiftOpening) {
                                            $allowedCheckIn = \Carbon\Carbon::parse($shiftOpening)->addMinutes($manualLateGraceMinutes);
                                            $actualCheckIn = \Carbon\Carbon::parse($attendanceCheckIn);
                                            $allowedCheckInLabel = $allowedCheckIn->format('H:i');
                                            $attendanceLate = $actualCheckIn->gt($allowedCheckIn);
                                            $checkInLateMinutes = $attendanceLate ? $allowedCheckIn->diffInMinutes($actualCheckIn) : 0;
                                        }

                                        if ($attendanceCheckOut && $shiftClosing && $earlyCheckoutEnabled) {
                                            $allowedCheckOut = \Carbon\Carbon::parse($shiftClosing);
                                            if ($checkoutBefore !== null) {
                                                $allowedCheckOut = $allowedCheckOut->subMinutes((int) $checkoutBefore);
                                            }
                                            $actualCheckOut = \Carbon\Carbon::parse($attendanceCheckOut);
                                            $allowedCheckOutLabel = $allowedCheckOut->format('H:i');
                                            $attendanceEarlyCheckout = $actualCheckOut->lt($allowedCheckOut);
                                            $checkOutEarlyMinutes = $attendanceEarlyCheckout ? $actualCheckOut->diffInMinutes($allowedCheckOut) : 0;
                                        }

                                        $attendanceStatusTitle = trim(
                                            ($attendanceCheckIn ? 'In '.$attendanceCheckIn : '').
                                            ($attendanceCheckOut ? ' Out '.$attendanceCheckOut : '')
                                        );
                                        $attendancePrintRemark = trim(strip_tags((string) ($attendance['edit_remark'] ?? ''))) ?: $printRemark;
                                        $attendanceWarnings = [];
                                        if ($attendanceRejected) {
                                            $attendanceWarnings[] = 'Rejected';
                                        } else {
                                            if ($attendanceLate) {
                                                $attendanceWarnings[] = 'Late '.$formatRuleMinutes($checkInLateMinutes);
                                            }
                                            if ($attendanceEarlyCheckout) {
                                                $attendanceWarnings[] = 'Early '.$formatRuleMinutes($checkOutEarlyMinutes);
                                            }
                                            if ($attendanceNoCheckout) {
                                                $attendanceWarnings[] = 'No Checkout';
                                            }
                                        }
                                        $attendancePrintWarning = $attendanceWarnings ? implode(', ', $attendanceWarnings) : 'Ok';
                                        $lateBadgeTitle = trim(($attendanceStatusTitle ?: 'Late') . ($allowedCheckInLabel ? ' | Allowed ' . $allowedCheckInLabel : ''));
                                        $earlyBadgeTitle = trim(($attendanceStatusTitle ?: 'Early Checkout') . ($allowedCheckOutLabel ? ' | Allowed ' . $allowedCheckOutLabel : ''));
                                    @endphp
                                    <tr class="attendance-detail-row">

                                        @if($isFirstIteration)
                                            <td>{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}</td>
                                            @php
                                                $isFirstIteration = false; // Set to false after displaying the date for the first time
                                            @endphp
                                        @else
                                            <td></td>
                                        @endif
                                            @if(isset($attendance['shift'])  && ($attendance['shift'] == \App\Enum\ShiftTypeEnum::night->value))
                                                @if(isset($attendance['night_checkin']))
                                                    <td class="text-center attendance-time-cell">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($attendance['check_in_type']).' '.__('index.checkin') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_in_latitude'].','.$attendance['check_in_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'}}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{  \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkin']) }}
                                                        </span>
                                                        @if($attendanceLate && !$attendanceRejected)
                                                            <span class="attendance-overlay-badge">{!! $statusBadge('L', 'Late', 'is-late', $lateBadgeTitle) !!}</span>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td class="text-center attendance-time-cell"></td>
                                                @endif
                                                <td class="text-center attendance-print-only">
                                                    @if($attendanceCheckIn && !$attendanceRejected)
                                                        <span class="attendance-print-status {{ $attendanceLate ? 'is-alert' : '' }}">
                                                            {{ $attendanceLate ? 'Late '.$formatRuleMinutes($checkInLateMinutes) : 'Ok' }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                @if(isset($attendance['night_checkout']))
                                                    <td class="text-center attendance-time-cell">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($attendance['check_out_type']).' '.__('index.checkout') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_out_latitude'].','.$attendance['check_out_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{ \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkout'])}}
                                                        </span>
                                                        @if($attendanceEarlyCheckout && !$attendanceRejected)
                                                            <span class="attendance-overlay-badge">{!! $statusBadge('E', 'Early Checkout', 'is-early', $earlyBadgeTitle) !!}</span>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td class="text-center attendance-time-cell">
                                                        @if($attendanceNoCheckout)
                                                            <span class="attendance-overlay-badge">{!! $statusBadge('NC', 'No Checkout', 'is-danger', 'No Checkout') !!}</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="text-center attendance-print-only">
                                                    @if($attendanceCheckOut && !$attendanceRejected)
                                                        <span class="attendance-print-status {{ $attendanceEarlyCheckout ? 'is-alert' : '' }}">
                                                            {{ $attendanceEarlyCheckout ? 'Early '.$formatRuleMinutes($checkOutEarlyMinutes) : 'Ok' }}
                                                        </span>
                                                        @if($printDayStatus)
                                                            <span class="attendance-print-day-status {{ $printDayStatus['class'] }}">
                                                                {{ $printDayStatus['label'] }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        @if($printDayStatus)
                                                            <span class="attendance-print-day-status {{ $printDayStatus['class'] }}">
                                                                {{ $printDayStatus['label'] }}
                                                            </span>
                                                        @else
                                                            -
                                                        @endif
                                                    @endif
                                                </td>
                                            @else
                                                @if(isset($attendance['check_in_at']))
                                                    <td class="text-center attendance-time-cell">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($attendance['check_in_type']).' '.__('index.checkin') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_in_latitude'].','.$attendance['check_in_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'}}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{  \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_in_at']) }}
                                                        </span>
                                                        @if($attendanceLate && !$attendanceRejected)
                                                            <span class="attendance-overlay-badge">{!! $statusBadge('L', 'Late', 'is-late', $lateBadgeTitle) !!}</span>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td class="text-center attendance-time-cell"></td>
                                                @endif
                                                <td class="text-center attendance-print-only">
                                                    @if($attendanceCheckIn && !$attendanceRejected)
                                                        <span class="attendance-print-status {{ $attendanceLate ? 'is-alert' : '' }}">
                                                            {{ $attendanceLate ? 'Late '.$formatRuleMinutes($checkInLateMinutes) : 'Ok' }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                @if(isset($attendance['check_out_at']))
                                                    <td class="text-center attendance-time-cell">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($attendance['check_out_type']).' '.__('index.checkout') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_out_latitude'].','.$attendance['check_out_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{  \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting,  $attendance['check_out_at']) }}
                                                        </span>
                                                        @if($attendanceEarlyCheckout && !$attendanceRejected)
                                                            <span class="attendance-overlay-badge">{!! $statusBadge('E', 'Early Checkout', 'is-early', $earlyBadgeTitle) !!}</span>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td class="text-center attendance-time-cell">
                                                        @if($attendanceNoCheckout)
                                                            <span class="attendance-overlay-badge">{!! $statusBadge('NC', 'No Checkout', 'is-danger', 'No Checkout') !!}</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="text-center attendance-print-only">
                                                    @if($attendanceCheckOut && !$attendanceRejected)
                                                        <span class="attendance-print-status {{ $attendanceEarlyCheckout ? 'is-alert' : '' }}">
                                                            {{ $attendanceEarlyCheckout ? 'Early '.$formatRuleMinutes($checkOutEarlyMinutes) : 'Ok' }}
                                                        </span>
                                                        @if($printDayStatus)
                                                            <span class="attendance-print-day-status {{ $printDayStatus['class'] }}">
                                                                {{ $printDayStatus['label'] }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        @if($printDayStatus)
                                                            <span class="attendance-print-day-status {{ $printDayStatus['class'] }}">
                                                                {{ $printDayStatus['label'] }}
                                                            </span>
                                                        @else
                                                            -
                                                        @endif
                                                    @endif
                                                </td>
                                            @endif
                                        <td  class="text-center">
                                            {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['worked_hour']) }}
                                        </td>
                                        <td class="text-center attendance-print-only">
                                            <span class="attendance-print-status {{ $attendancePrintWarning === 'Ok' ? '' : 'is-alert' }}">
                                                {{ $attendancePrintWarning }}
                                            </span>
                                        </td>
                                        <td class="attendance-print-only attendance-remark-cell">
                                            {{ $attendancePrintRemark ?: '-' }}
                                        </td>
                                          @if(!is_null($attendance['attendance_status']))
                                              <td class="text-center">
                                                  <a class="btn btn-{{ $changeColor[$attendance['attendance_status']] }} btn-xs"
                                                       title="{{ __('index.change_attendance_status') }}">
                                                      {{ ($attendance['attendance_status'] == \App\Models\Attendance::ATTENDANCE_APPROVED) ? __('index.present') : __('index.rejected') }}
                                                  </a>
                                              </td>
                                         @else
                                            <td  class="text-center">
                                                 @if($leaveRequest)
                                                     @php
                                                         $leaveStatus = strtolower((string) $leaveRequest->status);
                                                         $leaveIsDayOff = $isDayOffLeave($leaveRequest);
                                                         $leaveCode = $leaveStatus === 'pending' ? ($leaveIsDayOff ? 'PO' : 'PL') : ($leaveIsDayOff ? 'O' : 'LV');
                                                         $leaveClass = $leaveStatus === 'pending' ? 'is-pending' : ($leaveIsDayOff ? 'is-off' : 'is-leave');
                                                         $leaveLabel = $leaveStatus === 'pending'
                                                             ? ($leaveIsDayOff ? 'Pending Day Off' : 'Pending Leave')
                                                             : ($leaveIsDayOff ? 'Day Off' : 'ច្បាប់ផ្សេង');
                                                     @endphp
                                                     <div class="d-flex justify-content-center align-items-center gap-2">
                                                         @canany(['update_leave_request','access_admin_leave'])
                                                             <a href="#"
                                                               class="attendanceLeaveRequestUpdate"
                                                               data-href="{{ route('admin.leave-request.update-status', $leaveRequest->id) }}"
                                                               data-status="{{ $leaveRequest->status }}"
                                                                data-remark="{{ $leaveRequest->admin_remark }}"
                                                                 data-reason="{{ strip_tags((string) $leaveRequest->reasons) }}"
                                                                 data-id="{{ $leaveRequest->id }}">
                                                                  <span class="btn btn-{{ $leaveRequestColor[$leaveRequest->status] ?? 'secondary' }} btn-xs"
                                                                        title="{{ \App\Helpers\AppHelper::convertLeaveDateFormat($leaveRequest->leave_from) }} - {{ \App\Helpers\AppHelper::convertLeaveDateFormat($leaveRequest->leave_to) }}">
                                                                      {{ $leaveRequest->leaveType ? ucfirst($leaveRequest->leaveType->name) : __('index.leave_request') }}
                                                                      ({{ ucfirst($leaveRequest->status) }})
                                                                  </span>
                                                              </a>
                                                          @else
                                                              <span class="btn btn-{{ $leaveRequestColor[$leaveRequest->status] ?? 'secondary' }} btn-xs"
                                                                    title="{{ \App\Helpers\AppHelper::convertLeaveDateFormat($leaveRequest->leave_from) }} - {{ \App\Helpers\AppHelper::convertLeaveDateFormat($leaveRequest->leave_to) }}">
                                                                  {{ $leaveRequest->leaveType ? ucfirst($leaveRequest->leaveType->name) : __('index.leave_request') }}
                                                                  ({{ ucfirst($leaveRequest->status) }})
                                                              </span>
                                                          @endcanany
                                                        @canany(['show_leave_request_detail','access_admin_leave'])
                                                            <a href="{{ route('admin.leave-request.show', $leaveRequest->id) }}"
                                                               class="showAttendanceLeaveReason"
                                                               title="{{ __('index.show_leave_reason') }}">
                                                                <i class="link-icon" data-feather="eye"></i>
                                                            </a>
                                                         @endcanany
                                                     </div>
                                                  @elseif($timeLeave)
                                                      @php
                                                          $timeLeaveStatus = strtolower((string) $timeLeave->status);
                                                          $timeLeaveCode = $timeLeaveStatus === 'approved' ? 'TL' : 'TR';
                                                          $timeLeaveClass = $timeLeaveStatus === 'approved' ? 'is-time' : 'is-pending';
                                                          $timeLeaveLabel = $timeLeaveStatus === 'approved' ? 'Time Leave' : 'Time Leave Request';
                                                          $timeLeaveTitle = $timeLeaveLabel.' '.\App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time).' - '.\App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time);
                                                      @endphp
                                                      <div class="d-flex justify-content-center align-items-center gap-2">
                                                          @if(auth('admin')->check() || \Illuminate\Support\Facades\Gate::allows('update_time_leave'))
                                                              <a href="#"
                                                                class="attendanceTimeLeaveRequestUpdate"
                                                                data-href="{{ route('admin.time-leave-request.update-status', $timeLeave->id) }}"
                                                                data-status="{{ $timeLeave->status }}"
                                                                data-remark="{{ $timeLeave->admin_remark }}"
                                                                  data-reason="{{ strip_tags((string) $timeLeave->reasons) }}"
                                                                  data-id="{{ $timeLeave->id }}"
                                                                  data-label="{{ __('index.time_leave_request') }} {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }}">
                                                                   <span class="btn btn-info btn-xs"
                                                                         title="{{ \App\Helpers\AppHelper::timeLeaverequestDate($timeLeave->issue_date) }} {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }}">
                                                                       {{ __('index.time_leave_request') }}
                                                                       ({{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }})
                                                                   </span>
                                                               </a>
                                                           @else
                                                               <span class="btn btn-info btn-xs"
                                                                     title="{{ \App\Helpers\AppHelper::timeLeaverequestDate($timeLeave->issue_date) }} {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }}">
                                                                   {{ __('index.time_leave_request') }}
                                                                   ({{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }})
                                                               </span>
                                                           @endif
                                                         @can('time_leave_list')
                                                             <a href="{{ route('admin.time-leave-request.show', $timeLeave->id) }}"
                                                                class="showAttendanceLeaveReason"
                                                                title="{{ __('index.show_leave_reason') }}">
                                                                 <i class="link-icon" data-feather="eye"></i>
                                                             </a>
                                                         @endcan
                                                     </div>
                                                   @else
                                                       <div class="attendance-status-stack">
                                                          <span class="btn btn-light btn-xs disabled">
                                                              {{ __('index.pending') }}
                                                          </span>
                                                         @can('quick_leave')
                                                            <a href="#"
                                                               class="btn btn-outline-primary btn-xs quickApproveLeaveTrigger"
                                                               data-user-id="{{ $userDetail->id }}"
                                                               data-user-name="{{ ucfirst($userDetail->name) }}"
                                                               data-attendance-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                               data-display-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}"
                                                               data-fetch-url="{{ route('admin.leaves.employee-data', $userDetail->id) }}">
                                                                 {{ __('index.quick_leave') }}
                                                             </a>
                                                         @endcan
                                                         @can('create_time_leave_request')
                                                             <a href="#"
                                                                 class="btn btn-outline-info btn-xs quickApproveTimeLeaveTrigger"
                                                                data-user-id="{{ $userDetail->id }}"
                                                                data-user-name="{{ ucfirst($userDetail->name) }}"
                                                                data-attendance-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                                data-display-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}">
                                                                 {{ __('index.quick_time_leave') }}
                                                             </a>
                                                         @endcan
                                                     </div>
                                                 @endif
                                            </td>
                                        @endif
                                            @if($attendance['shift'])
                                                <td class="text-center">
                                                    <span class="btn btn-info btn-xs">
                                                        {{ ucfirst($attendance['shift']) }}
                                                    </span>
                                                </td>
                                            @else
                                                <td></td>
                                            @endif
                                            @can('attendance_update')
                                                <td class="text-center attendance-print-hide">

                                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                        @if(isset($attendance['shift'])  && ($attendance['shift'] == \App\Enum\ShiftTypeEnum::night->value))
                                                            <li class="me-2">
                                                                <a href=""
                                                                    class="editNightAttendance"
                                                                    data-href="{{ route('admin.night_attendances.update', $attendance['id']) }}"
                                                                    data-in="{{ $attendance['night_checkin'] }}"
                                                                    data-out="{{ $attendance['night_checkout'] ?? null }}"
                                                                    data-remark="{{ $attendance['edit_remark'] }}"
                                                                    data-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $attendance['attendance_date']) }}"
                                                                    data-name="{{ ucfirst($userDetail->name) }}"
                                                                    title="{{ __('index.edit_attendance_time') }}"
                                                                >
                                                                    <i class="link-icon" data-feather="edit"></i>
                                                                </a>
                                                            </li>
                                                        @else
                                                            @if(count($dayData['data']) < $multipleAttendance && isset($attendance['check_out_at']))
                                                                <li class="me-2">
                                                                    <a href=""
                                                                    class="addEmployeeAttendance"
                                                                    data-href="{{ route('admin.attendances.store') }}"
                                                                    data-name="{{ ucfirst($userDetail->name) }}"
                                                                    data-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                                    data-cdate="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $attendance['attendance_date']) }}"
                                                                    data-user_id="{{ $userDetail->id }}"
                                                                    title="{{ __('index.add_attendance_time') }}">
                                                                    <i class="link-icon" data-feather="plus-circle"></i>
                                                                </a>
                                                                </li>
                                                            @endif
                                                            @if(isset($attendance['id']))
                                                                <li class="me-2">
                                                                    <a href=""
                                                                        class="editAttendance"
                                                                        data-href="{{ route('admin.attendances.update', $attendance['id']) }}"
                                                                        data-in="{{ date('H:i', strtotime($attendance['check_in_at'])) }}"
                                                                        data-out="{{ $attendance['check_out_at'] ? date('H:i', strtotime($attendance['check_out_at'])) : null }}"
                                                                        data-remark="{{ $attendance['edit_remark'] }}"
                                                                        data-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $attendance['attendance_date']) }}"
                                                                        data-name="{{ ucfirst($userDetail->name) }}"
                                                                        title="{{ __('index.edit_attendance_time') }}">
                                                                        <i class="link-icon" data-feather="edit"></i>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endif
                                                        @can('attendance_delete')
                                                            <li class="me-2">
                                                                <a class="deleteAttendance" href="{{ route('admin.attendance.delete', $attendance['id']) }}">
                                                                    <i class="link-icon"  data-feather="delete"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </td>
                                            @endcan
                                    </tr>
                                @endforeach

                                @if($multipleAttendance > 1 && count($dayData['data']) > 1)
                                    <tr class="bg-light">
                                        <th></th>
                                        <th></th>
                                        <th class="attendance-print-only"></th>
                                        <th></th>
                                        <th class="attendance-print-only"></th>
                                        @php
                                            $hours = floor($totalMinutes / 60);
                                            $minutes = $totalMinutes % 60;
                                            if ($hours == 0 && $minutes == 0) {
                                                $worked_hours = '';
                                            } elseif ($hours == 0) {
                                                $worked_hours = $minutes . ' min';
                                            } elseif ($minutes == 0) {
                                                $worked_hours = $hours . ' hr';
                                            } else {
                                                $worked_hours = $hours . ' hr ' . $minutes . ' min';
                                            }
                                        @endphp
                                        <th class="text-center">{{ $worked_hours }}</th>
                                        <th class="attendance-print-only"></th>
                                        <th class="attendance-print-only"></th>
                                        <th></th>
                                        <th></th>
                                        @can('attendance_update')
                                            <th class="attendance-print-hide"></th>
                                        @endcan

                                    </tr>
                                @endif
                            @else
                                @php
                                    $reason = (\App\Helpers\AttendanceHelper::getHolidayOrLeaveDetail($dayData['attendance_date'], $userDetail->id));
                                    $dayCheckBadge = null;

                                    if ($leaveRequest) {
                                        $leaveStatus = strtolower((string) $leaveRequest->status);
                                        $leaveIsDayOff = $isDayOffLeave($leaveRequest);
                                        $dayCheckBadge = [
                                            'code' => $leaveStatus === 'pending' ? ($leaveIsDayOff ? 'PO' : 'PL') : ($leaveIsDayOff ? 'O' : 'LV'),
                                            'label' => $leaveStatus === 'pending' ? ($leaveIsDayOff ? 'Pending Day Off' : 'Pending Leave') : ($leaveIsDayOff ? 'Day Off' : 'ច្បាប់ផ្សេង'),
                                            'class' => $leaveStatus === 'pending' ? 'is-pending' : ($leaveIsDayOff ? 'is-off' : 'is-leave'),
                                        ];
                                    } elseif ($timeLeave) {
                                        $timeLeaveStatus = strtolower((string) $timeLeave->status);
                                        $dayCheckBadge = [
                                            'code' => $timeLeaveStatus === 'approved' ? 'TL' : 'TR',
                                            'label' => $timeLeaveStatus === 'approved' ? 'Time Leave' : 'Time Leave Request',
                                            'class' => $timeLeaveStatus === 'approved' ? 'is-time' : 'is-pending',
                                        ];
                                    } elseif ($reason) {
                                        $reasonText = (string) $reason;
                                        $reasonLower = strtolower($reasonText);
                                        $reasonCode = $reasonText === 'Absent' ? 'A' : ((str_contains($reasonLower, 'leave') || str_contains($reasonLower, 'ច្បាប់')) ? 'LV' : 'O');
                                        $dayCheckBadge = [
                                            'code' => $reasonCode,
                                            'label' => $reasonCode === 'A' ? 'Absent' : ($reasonCode === 'LV' ? 'ច្បាប់ផ្សេង' : 'Day Off'),
                                            'class' => $reasonCode === 'A' ? 'is-absent' : ($reasonCode === 'LV' ? 'is-leave' : 'is-off'),
                                        ];
                                    }

                                    $nonAttendanceRemark = $printRemark ?: ((isset($reason) && $reason) ? (string) $reason : '-');
                                    $nonAttendanceWarning = $printDayStatus['label'] ?? ((isset($reason) && $reason) ? (string) $reason : 'No Attendance');
                                    $nonAttendanceWarningClass = $printDayStatus['class'] ?? 'is-alert';
                                @endphp
                                <tr class="attendance-detail-row">
                                    <td>{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}</td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td class="text-center attendance-print-only">
                                        @if($printDayStatus)
                                            <span class="attendance-print-day-status {{ $printDayStatus['class'] }}">
                                                {{ $printDayStatus['label'] }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td class="text-center attendance-print-only">-</td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td class="text-center attendance-print-only">
                                        <span class="{{ $printDayStatus ? 'attendance-print-day-status' : 'attendance-print-status' }} {{ $nonAttendanceWarningClass }}">
                                            {{ $nonAttendanceWarning }}
                                        </span>
                                    </td>
                                    <td class="attendance-print-only attendance-remark-cell">
                                        {{ $nonAttendanceRemark }}
                                    </td>
                                     <td class="text-center">
                                         @if($leaveRequest)
                                             @php
                                                 $leaveStatus = strtolower((string) $leaveRequest->status);
                                                 $leaveIsDayOff = $isDayOffLeave($leaveRequest);
                                                 $leaveCode = $leaveStatus === 'pending' ? ($leaveIsDayOff ? 'PO' : 'PL') : ($leaveIsDayOff ? 'O' : 'LV');
                                                 $leaveClass = $leaveStatus === 'pending' ? 'is-pending' : ($leaveIsDayOff ? 'is-off' : 'is-leave');
                                                 $leaveLabel = $leaveStatus === 'pending'
                                                     ? ($leaveIsDayOff ? 'Pending Day Off' : 'Pending Leave')
                                                     : ($leaveIsDayOff ? 'Day Off' : 'ច្បាប់ផ្សេង');
                                             @endphp
                                             <div class="d-flex justify-content-center align-items-center gap-2">
                                                 @canany(['update_leave_request','access_admin_leave'])
                                                     <a href="#"
                                                       class="attendanceLeaveRequestUpdate"
                                                       data-href="{{ route('admin.leave-request.update-status', $leaveRequest->id) }}"
                                                       data-status="{{ $leaveRequest->status }}"
                                                        data-remark="{{ $leaveRequest->admin_remark }}"
                                                        data-reason="{{ strip_tags((string) $leaveRequest->reasons) }}"
                                                        data-id="{{ $leaveRequest->id }}">
                                                          <span class="btn btn-{{ $leaveRequestColor[$leaveRequest->status] ?? 'secondary' }} btn-xs"
                                                                title="{{ \App\Helpers\AppHelper::convertLeaveDateFormat($leaveRequest->leave_from) }} - {{ \App\Helpers\AppHelper::convertLeaveDateFormat($leaveRequest->leave_to) }}">
                                                              {{ $leaveRequest->leaveType ? ucfirst($leaveRequest->leaveType->name) : __('index.leave_request') }}
                                                              ({{ ucfirst($leaveRequest->status) }})
                                                          </span>
                                                      </a>
                                                  @else
                                                      <span class="btn btn-{{ $leaveRequestColor[$leaveRequest->status] ?? 'secondary' }} btn-xs"
                                                            title="{{ \App\Helpers\AppHelper::convertLeaveDateFormat($leaveRequest->leave_from) }} - {{ \App\Helpers\AppHelper::convertLeaveDateFormat($leaveRequest->leave_to) }}">
                                                          {{ $leaveRequest->leaveType ? ucfirst($leaveRequest->leaveType->name) : __('index.leave_request') }}
                                                          ({{ ucfirst($leaveRequest->status) }})
                                                      </span>
                                                  @endcanany
                                                @canany(['show_leave_request_detail','access_admin_leave'])
                                                    <a href="{{ route('admin.leave-request.show', $leaveRequest->id) }}"
                                                       class="showAttendanceLeaveReason"
                                                       title="{{ __('index.show_leave_reason') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                 @endcanany
                                             </div>
                                          @elseif($timeLeave)
                                              @php
                                                  $timeLeaveStatus = strtolower((string) $timeLeave->status);
                                                  $timeLeaveCode = $timeLeaveStatus === 'approved' ? 'TL' : 'TR';
                                                  $timeLeaveClass = $timeLeaveStatus === 'approved' ? 'is-time' : 'is-pending';
                                                  $timeLeaveLabel = $timeLeaveStatus === 'approved' ? 'Time Leave' : 'Time Leave Request';
                                                  $timeLeaveTitle = $timeLeaveLabel.' '.\App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time).' - '.\App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time);
                                              @endphp
                                              <div class="d-flex justify-content-center align-items-center gap-2">
                                                  @if(auth('admin')->check() || \Illuminate\Support\Facades\Gate::allows('update_time_leave'))
                                                      <a href="#"
                                                        class="attendanceTimeLeaveRequestUpdate"
                                                        data-href="{{ route('admin.time-leave-request.update-status', $timeLeave->id) }}"
                                                        data-status="{{ $timeLeave->status }}"
                                                        data-remark="{{ $timeLeave->admin_remark }}"
                                                          data-reason="{{ strip_tags((string) $timeLeave->reasons) }}"
                                                          data-id="{{ $timeLeave->id }}"
                                                          data-label="{{ __('index.time_leave_request') }} {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }}">
                                                           <span class="btn btn-info btn-xs"
                                                                 title="{{ \App\Helpers\AppHelper::timeLeaverequestDate($timeLeave->issue_date) }} {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }}">
                                                               {{ __('index.time_leave_request') }}
                                                               ({{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }})
                                                           </span>
                                                       </a>
                                                   @else
                                                       <span class="btn btn-info btn-xs"
                                                             title="{{ \App\Helpers\AppHelper::timeLeaverequestDate($timeLeave->issue_date) }} {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }}">
                                                           {{ __('index.time_leave_request') }}
                                                           ({{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->start_time) }} - {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($timeLeave->end_time) }})
                                                       </span>
                                                   @endif
                                                 @can('time_leave_list')
                                                     <a href="{{ route('admin.time-leave-request.show', $timeLeave->id) }}"
                                                        class="showAttendanceLeaveReason"
                                                        title="{{ __('index.show_leave_reason') }}">
                                                         <i class="link-icon" data-feather="eye"></i>
                                                     </a>
                                                 @endcan
                                             </div>
                                          @elseif($reason)
                                              @php
                                                  $reasonText = (string) $reason;
                                                  $reasonLower = strtolower($reasonText);
                                                  $reasonCode = $reasonText === 'Absent' ? 'A' : ((str_contains($reasonLower, 'leave') || str_contains($reasonLower, 'ច្បាប់')) ? 'LV' : 'O');
                                                  $reasonClass = $reasonCode === 'A' ? 'is-absent' : ($reasonCode === 'LV' ? 'is-leave' : 'is-off');
                                                  $reasonLabel = $reasonCode === 'A' ? 'Absent' : ($reasonCode === 'LV' ? 'ច្បាប់ផ្សេង' : 'Day Off');
                                              @endphp
                                              <div class="attendance-status-stack">
                                                  <span class="btn btn-outline-secondary btn-xs">
                                                      {{ $reason }}
                                                  </span>
                                                  @if($reason === 'Absent')
                                                    @can('quick_leave')
                                                        <a href="#"
                                                           class="btn btn-outline-primary btn-xs quickApproveLeaveTrigger"
                                                           data-user-id="{{ $userDetail->id }}"
                                                           data-user-name="{{ ucfirst($userDetail->name) }}"
                                                           data-attendance-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                           data-display-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}"
                                                           data-fetch-url="{{ route('admin.leaves.employee-data', $userDetail->id) }}">
                                                              {{ __('index.quick_leave') }}
                                                         </a>
                                                     @endcan
                                                     @can('create_time_leave_request')
                                                         <a href="#"
                                                            class="btn btn-outline-info btn-xs quickApproveTimeLeaveTrigger"
                                                            data-user-id="{{ $userDetail->id }}"
                                                            data-user-name="{{ ucfirst($userDetail->name) }}"
                                                            data-attendance-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                            data-display-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}">
                                                              {{ __('index.quick_time_leave') }}
                                                         </a>
                                                     @endcan
                                                 @endif
                                             </div>
                                         @else
                                             <div class="attendance-status-stack">
                                                 <span class="btn btn-light btn-xs disabled">
                                                    {{ __('index.pending') }}
                                                </span>
                                                @can('quick_leave')
                                                    <a href="#"
                                                       class="btn btn-outline-primary btn-xs quickApproveLeaveTrigger"
                                                       data-user-id="{{ $userDetail->id }}"
                                                       data-user-name="{{ ucfirst($userDetail->name) }}"
                                                       data-attendance-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                       data-display-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}"
                                                       data-fetch-url="{{ route('admin.leaves.employee-data', $userDetail->id) }}">
                                                          {{ __('index.quick_leave') }}
                                                     </a>
                                                 @endcan
                                                 @can('create_time_leave_request')
                                                     <a href="#"
                                                        class="btn btn-outline-info btn-xs quickApproveTimeLeaveTrigger"
                                                        data-user-id="{{ $userDetail->id }}"
                                                        data-user-name="{{ ucfirst($userDetail->name) }}"
                                                        data-attendance-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                        data-display-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}">
                                                          {{ __('index.quick_time_leave') }}
                                                     </a>
                                                 @endcan
                                             </div>
                                         @endif
                                     </td>
                                    <td  class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td  class="text-center attendance-print-hide">
                                        @if(!$leaveRequest && !$timeLeave && isset($reason) && $reason == 'Absent')
                                            <a href=""
                                                class="addEmployeeAttendance"
                                                data-href="{{ route('admin.attendances.store') }}"
                                                data-name="{{ ucfirst($userDetail->name) }}"
                                                data-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                data-cdate="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}"
                                                data-user_id="{{ $userDetail->id }}"
                                                title="{{ __('index.add_attendance_time') }}">
                                                <i class="link-icon" data-feather="plus-circle"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="100%">
                                        <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                    </td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
        <div class="attendance-print-signatures">
            <div class="attendance-print-signature">
                <div class="attendance-print-signature-line">Staff</div>
            </div>
            <div class="attendance-print-signature">
                <div class="attendance-print-signature-line">Head Department</div>
            </div>
            <div class="attendance-print-signature">
                <div class="attendance-print-signature-line">Admin</div>
            </div>
        </div>
        </div>

        <div class="attendance-detail-legend mt-3 mb-4">
            <span><i class="attendance-detail-legend-badge attendance-legend-present">P</i> Present</span>
            <span><i class="attendance-detail-legend-badge attendance-legend-late">L</i> Late</span>
            <span><i class="attendance-detail-legend-badge attendance-legend-absent">A</i> Absent</span>
            <span><i class="attendance-detail-legend-badge attendance-legend-off">O</i> Day Off</span>
            <span><i class="attendance-detail-legend-badge attendance-legend-leave">LV</i> ច្បាប់ផ្សេង</span>
            <span><i class="attendance-detail-legend-badge attendance-legend-pending">PO</i> Pending Day Off</span>
            <span><i class="attendance-detail-legend-badge attendance-legend-pending">PL</i> Pending Leave</span>
            <span><i class="attendance-detail-legend-badge attendance-legend-time">TL</i> Time Leave</span>
            <span><i class="attendance-detail-legend-badge attendance-legend-pending">TR</i> Time Leave Request</span>
            <span><i class="attendance-detail-legend-badge attendance-legend-danger">NC</i> No Checkout</span>
        </div>

        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <iframe id="iframeModalWindow" class="attendancelocation" height="500px" width="100%" src=""
                                name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.attendance.common.edit-attendance-form')
        @include('admin.attendance.common.create-attendance-form')
        @include('admin.attendance.common.edit-night-attendance-form')

        <div class="modal fade" id="attendanceLeaveRequestModal" tabindex="-1" aria-labelledby="attendanceLeaveRequestModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-12 mb-2 pb-2 border-bottom">
                                    <label class="form-label fw-bold">{{ __('index.referred_by') }}</label>
                                    <p class="form-control border-0 p-0 fst-italic" style="height:inherit" id="attendanceLeaveReferredBy"></p>
                                </div>
                                <div class="col-lg-12 mb-2 pb-2 border-bottom">
                                    <label class="form-label fw-bold">{{ __('index.leave_reason') }}</label>
                                    <p class="form-control border-0 p-0 fst-italic" style="height:inherit" id="attendanceLeaveDescription"></p>
                                </div>
                                <div class="col-lg-12">
                                    <label class="form-label fw-bold">{{ __('index.admin_remark') }}</label>
                                    <p class="form-control border-0 p-0 fst-italic" style="height:inherit" id="attendanceLeaveAdminRemark"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="attendanceLeaveStatusUpdate" tabindex="-1" aria-labelledby="attendanceLeaveStatusUpdate" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="attendanceLeaveStatusUpdateTitle">{{ __('index.leave_request_section') }}</h5>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <form class="forms-sample" id="attendanceUpdateLeaveStatus" action="" method="post">
                                @csrf
                                @method('put')
                                <input type="hidden" name="redirect_back" value="1">
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        <label class="form-label">{{ __('index.leave_reason') }}</label>
                                        <div class="form-control bg-light" style="height: auto; min-height: 44px;" id="attendanceLeaveStatusReason">N/A</div>
                                    </div>

                                    <label for="attendanceLeaveStatus" class="form-label">{{ __('index.status') }} </label>
                                    <div class="col-lg-12 mb-3">
                                        <select class="form-select" id="attendanceLeaveStatus" name="status">
                                            <option value="{{ \App\Enum\LeaveStatusEnum::approved->value }}">{{ __('index.approve') }}</option>
                                            <option value="{{ \App\Enum\LeaveStatusEnum::rejected->value }}">{{ __('index.reject') }}</option>
                                        </select>
                                    </div>

                                    <label for="attendanceLeaveRemark" class="form-label">{{ __('index.admin_remark') }}</label>
                                    <div class="col-lg-12 mb-3">
                                        <textarea class="form-select" id="attendanceLeaveRemark" minlength="10" name="admin_remark" rows="3"></textarea>
                                    </div>
                                </div>

                                <div id="attendancePreviousApprovers" class="mb-3"></div>

                                <div class="text-start">
                                    <button type="submit" class="btn btn-primary btn-xs">{{ __('index.submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="attendanceQuickLeaveModal" tabindex="-1" aria-labelledby="attendanceQuickLeaveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attendanceQuickLeaveModalLabel">{{ __('index.quick_leave') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.attendances.quick-approved-leave') }}" method="post" id="attendanceQuickLeaveForm">
                            @csrf
                            <input type="hidden" name="user_id" id="attendanceQuickLeaveUserId">
                            <input type="hidden" name="attendance_date" id="attendanceQuickLeaveDate">

                            <div class="mb-3">
                                <label for="attendanceQuickLeaveType" class="form-label">{{ __('index.leave_type') }}</label>
                                <select class="form-select" name="leave_type_id" id="attendanceQuickLeaveType" required>
                                    <option value="">{{ __('index.select_leave_type') }}</option>
                                </select>
                                <small class="text-muted d-block mt-2" id="attendanceQuickLeaveHelpText">
                                    {{ __('index.create_approved_leave_selected_day') }}
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="attendanceQuickLeaveReason" class="form-label">{{ __('index.leave_reason') }}</label>
                                <textarea class="form-control" name="reasons" id="attendanceQuickLeaveReason" rows="3" placeholder="{{ __('index.optional_note') }}"></textarea>
                            </div>

                            <div class="text-start">
                                <button type="submit" class="btn btn-primary btn-sm" id="attendanceQuickLeaveSubmit">
                                    {{ __('index.save_as_approved_leave') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="attendanceQuickTimeLeaveModal" tabindex="-1" aria-labelledby="attendanceQuickTimeLeaveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attendanceQuickTimeLeaveModalLabel">{{ __('index.quick_time_leave') }}</h5>
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
                                    {{ __('index.create_approved_time_leave_today') }}
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="attendanceQuickTimeLeaveReason" class="form-label">{{ __('index.leave_reason') }}</label>
                                <textarea class="form-control" name="reasons" id="attendanceQuickTimeLeaveReason" rows="3" minlength="10" required placeholder="Required note"></textarea>
                            </div>

                            <div class="text-start">
                                <button type="submit" class="btn btn-primary btn-sm" id="attendanceQuickTimeLeaveSubmit">
                                    Save as Approved Time Leave
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.attendance.common.scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const printEmployeeAttendanceDashboard = document.getElementById('print-employee-attendance-dashboard');
            const attendanceQuickLeaveModalElement = document.getElementById('attendanceQuickLeaveModal');
            const attendanceQuickLeaveModal = attendanceQuickLeaveModalElement ? new bootstrap.Modal(attendanceQuickLeaveModalElement) : null;
            const attendanceQuickLeaveUserId = document.getElementById('attendanceQuickLeaveUserId');
            const attendanceQuickLeaveDate = document.getElementById('attendanceQuickLeaveDate');
            const attendanceQuickLeaveType = document.getElementById('attendanceQuickLeaveType');
            const attendanceQuickLeaveReason = document.getElementById('attendanceQuickLeaveReason');
            const attendanceQuickLeaveSubmit = document.getElementById('attendanceQuickLeaveSubmit');
            const attendanceQuickLeaveLabel = document.getElementById('attendanceQuickLeaveModalLabel');
            const attendanceQuickLeaveHelpText = document.getElementById('attendanceQuickLeaveHelpText');
            const attendanceQuickTimeLeaveModalElement = document.getElementById('attendanceQuickTimeLeaveModal');
            const attendanceQuickTimeLeaveModal = attendanceQuickTimeLeaveModalElement ? new bootstrap.Modal(attendanceQuickTimeLeaveModalElement) : null;
            const attendanceQuickTimeLeaveUserId = document.getElementById('attendanceQuickTimeLeaveUserId');
            const attendanceQuickTimeLeaveDate = document.getElementById('attendanceQuickTimeLeaveDate');
            const attendanceQuickTimeLeaveFrom = document.getElementById('attendanceQuickTimeLeaveFrom');
            const attendanceQuickTimeLeaveTo = document.getElementById('attendanceQuickTimeLeaveTo');
            const attendanceQuickTimeLeaveReason = document.getElementById('attendanceQuickTimeLeaveReason');
            const attendanceQuickTimeLeaveLabel = document.getElementById('attendanceQuickTimeLeaveModalLabel');
            const attendanceQuickTimeLeaveHelpText = document.getElementById('attendanceQuickTimeLeaveHelpText');
            const attendanceLeaveStatusUpdateTitle = document.getElementById('attendanceLeaveStatusUpdateTitle');
            const attendanceI18n = {
                quickLeave: @json(__('index.quick_leave')),
                quickTimeLeave: @json(__('index.quick_time_leave')),
                loadingLeaveTypes: @json(__('index.loading_leave_types')),
                noLeaveTypesAvailable: @json(__('index.no_leave_types_available')),
                noLeaveTypesAvailableEmployee: @json(__('index.no_leave_types_available_employee')),
                selectLeaveType: @json(__('index.select_leave_type')),
                unableLoadLeaveTypes: @json(__('index.unable_load_leave_types')),
                unableLoadLeaveTypesTryAgain: @json(__('index.unable_load_leave_types_try_again')),
                createApprovedLeaveForDate: @json(__('index.create_approved_leave_for_date')),
                createApprovedTimeLeaveForDate: @json(__('index.create_approved_time_leave_for_date')),
            };

            if (printEmployeeAttendanceDashboard) {
                printEmployeeAttendanceDashboard.addEventListener('click', function () {
                    window.print();
                });
            }

            const resetQuickLeaveOptions = (message = attendanceI18n.loadingLeaveTypes) => {
                if (!attendanceQuickLeaveType) {
                    return;
                }

                attendanceQuickLeaveType.innerHTML = `<option value="">${message}</option>`;
                attendanceQuickLeaveType.disabled = true;
                if (attendanceQuickLeaveSubmit) {
                    attendanceQuickLeaveSubmit.disabled = true;
                }
            };

            document.querySelectorAll('.quickApproveLeaveTrigger').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();

                    if (!attendanceQuickLeaveModal) {
                        return;
                    }

                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    const attendanceDate = this.getAttribute('data-attendance-date');
                    const displayDate = this.getAttribute('data-display-date');
                    const fetchUrl = this.getAttribute('data-fetch-url');

                    attendanceQuickLeaveUserId.value = userId;
                    attendanceQuickLeaveDate.value = attendanceDate;
                    attendanceQuickLeaveReason.value = '';
                    attendanceQuickLeaveLabel.textContent = `${attendanceI18n.quickLeave}: ${userName}`;
                    attendanceQuickLeaveHelpText.textContent = attendanceI18n.createApprovedLeaveForDate.replace(':date', displayDate);

                    resetQuickLeaveOptions();
                    attendanceQuickLeaveModal.show();

                    fetch(fetchUrl)
                        .then(response => response.json())
                        .then(data => {
                            const leaveTypes = data.leaveTypes || data.leveTypes || [];

                            if (!leaveTypes.length) {
                                resetQuickLeaveOptions(attendanceI18n.noLeaveTypesAvailable);
                                attendanceQuickLeaveHelpText.textContent = attendanceI18n.noLeaveTypesAvailableEmployee;
                                return;
                            }

                            attendanceQuickLeaveType.disabled = false;
                            attendanceQuickLeaveType.innerHTML = `<option value="">${attendanceI18n.selectLeaveType}</option>`;

                            leaveTypes.forEach((leaveType) => {
                                const option = document.createElement('option');
                                option.value = leaveType.id;
                                option.textContent = leaveType.name;
                                attendanceQuickLeaveType.appendChild(option);
                            });

                            const preferredType = leaveTypes.find((leaveType) => {
                                const typeName = String(leaveType.name || '').toLowerCase();
                                return typeName.includes('day off') || typeName.includes('ច្បាប់') || typeName.includes('leave');
                            });

                            attendanceQuickLeaveType.value = String(preferredType?.id || leaveTypes[0].id);
                            if (attendanceQuickLeaveSubmit) {
                                attendanceQuickLeaveSubmit.disabled = false;
                            }
                        })
                        .catch(() => {
                            resetQuickLeaveOptions(attendanceI18n.unableLoadLeaveTypes);
                            attendanceQuickLeaveHelpText.textContent = attendanceI18n.unableLoadLeaveTypesTryAgain;
                        });
                });
            });

            document.querySelectorAll('.quickApproveTimeLeaveTrigger').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();

                    if (!attendanceQuickTimeLeaveModal) {
                        return;
                    }

                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    const attendanceDate = this.getAttribute('data-attendance-date');
                    const displayDate = this.getAttribute('data-display-date');

                    attendanceQuickTimeLeaveUserId.value = userId;
                    attendanceQuickTimeLeaveDate.value = attendanceDate;
                    attendanceQuickTimeLeaveFrom.value = '';
                    attendanceQuickTimeLeaveTo.value = '';
                    attendanceQuickTimeLeaveReason.value = '';
                    attendanceQuickTimeLeaveLabel.textContent = `${attendanceI18n.quickTimeLeave}: ${userName}`;
                    attendanceQuickTimeLeaveHelpText.textContent = attendanceI18n.createApprovedTimeLeaveForDate.replace(':date', displayDate);

                    attendanceQuickTimeLeaveModal.show();
                });
            });

            document.querySelectorAll('.showAttendanceLeaveReason').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();
                    const url = this.getAttribute('href');

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.data) {
                                const leaveRequest = data.data;
                                document.getElementById('attendanceLeaveReferredBy').innerText = leaveRequest.name || 'Admin';
                                document.getElementById('attendanceLeaveDescription').innerText = leaveRequest.reasons || 'N/A';
                                document.getElementById('attendanceLeaveAdminRemark').innerText = leaveRequest.admin_remark || 'N/A';

                                const modal = new bootstrap.Modal(document.getElementById('attendanceLeaveRequestModal'));
                                modal.show();
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });

            document.querySelectorAll('.attendanceLeaveRequestUpdate').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();

                    const url = this.getAttribute('data-href');
                    const status = this.getAttribute('data-status');
                    const remark = this.getAttribute('data-remark');
                    const reason = this.getAttribute('data-reason');
                    const leaveRequestId = this.getAttribute('data-id');

                    document.getElementById('attendanceUpdateLeaveStatus').setAttribute('action', url);
                     document.getElementById('attendanceLeaveStatus').value = status;
                     document.getElementById('attendanceLeaveRemark').value = remark || '';
                     document.getElementById('attendanceLeaveStatusReason').textContent = reason || 'N/A';
                     document.getElementById('attendancePreviousApprovers').innerHTML = '';
                     attendanceLeaveStatusUpdateTitle.textContent = '{{ __('index.leave_request_section') }}';

                     fetch(`/admin/leave-request/get-approvers/${leaveRequestId}`)
                        .then(response => response.json())
                        .then(response => {
                            if (!response.success) {
                                return;
                            }

                            let approversData = '';
                            response.data.approval_data.forEach(function (approver) {
                                approversData += `
                                    <div class="approver-details">
                                        <p><b>Approver:</b> ${approver.approved_by_name}</p>
                                        <p><b>Status:</b> ${approver.status}</p>
                                        <p><b>Remark:</b> ${approver.reason}</p>
                                    </div>
                                    <hr>`;
                            });

                            if (response.data.admin_data.status !== 'pending' && response.data.admin_data.remark !== '') {
                                approversData += `
                                    <div class="approver-details">
                                        <p><b>Status:</b> ${response.data.admin_data.status}</p>
                                        <p><b>Admin Remark:</b> ${response.data.admin_data.remark}</p>
                                    </div>`;
                            }

                            document.getElementById('attendancePreviousApprovers').innerHTML = approversData;
                        })
                        .catch(error => console.error('Error:', error));

                     const modal = new bootstrap.Modal(document.getElementById('attendanceLeaveStatusUpdate'));
                     modal.show();
                 });
             });

            document.querySelectorAll('.attendanceTimeLeaveRequestUpdate').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();

                    const url = this.getAttribute('data-href');
                    const status = this.getAttribute('data-status');
                    const remark = this.getAttribute('data-remark');
                    const reason = this.getAttribute('data-reason');
                    const label = this.getAttribute('data-label') || '{{ __('index.time_leave_request') }}';

                    document.getElementById('attendanceUpdateLeaveStatus').setAttribute('action', url);
                    document.getElementById('attendanceLeaveStatus').value = status;
                    document.getElementById('attendanceLeaveRemark').value = remark || '';
                    document.getElementById('attendanceLeaveStatusReason').textContent = reason || 'N/A';
                    document.getElementById('attendancePreviousApprovers').innerHTML = '';
                    attendanceLeaveStatusUpdateTitle.textContent = label;

                    const modal = new bootstrap.Modal(document.getElementById('attendanceLeaveStatusUpdate'));
                    modal.show();
                });
            });
         });
     </script>
@endsection
