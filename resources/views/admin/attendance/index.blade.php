@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', __('index.employee_attendance_lists'))


@section('main-content')

    <section class="content">
        <style>
            .page-content {
                padding-top: 0.4rem !important;
            }

            .content {
                margin-top: 0 !important;
            }

            .attendance-chat-modal .modal-dialog {
                max-width: 760px;
            }

            .attendance-chat-modal .modal-content {
                border: 0;
                border-radius: 26px;
                overflow: hidden;
                box-shadow: 0 28px 70px rgba(15, 23, 42, 0.24);
            }

            .attendance-chat-shell {
                background:
                    radial-gradient(circle at top left, rgba(96, 165, 250, 0.14), transparent 28%),
                    radial-gradient(circle at top right, rgba(168, 85, 247, 0.10), transparent 24%),
                    linear-gradient(180deg, #f8fbff 0%, #ffffff 32%);
            }

            .attendance-chat-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 22px 24px 20px;
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(16px);
                border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            }

            .attendance-chat-person {
                display: flex;
                align-items: center;
                gap: 14px;
                min-width: 0;
            }

            .attendance-chat-avatar-wrap {
                position: relative;
                flex-shrink: 0;
            }

            .attendance-chat-avatar {
                width: 56px;
                height: 56px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid #ffffff;
                box-shadow: 0 10px 26px rgba(59, 130, 246, 0.16);
            }

            .attendance-chat-status {
                position: absolute;
                right: 2px;
                bottom: 2px;
                width: 14px;
                height: 14px;
                border-radius: 50%;
                background: #cbd5e1;
                border: 2px solid #ffffff;
            }

            .attendance-chat-status.online {
                background: #22c55e;
            }

            .attendance-chat-person h5 {
                margin: 0;
                color: #111827;
                font-size: 1.35rem;
                font-weight: 700;
                letter-spacing: -0.02em;
            }

            .attendance-chat-person p {
                margin: 2px 0 0;
                color: #64748b;
                font-size: 0.94rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .attendance-chat-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #a855f7;
                font-size: 1.1rem;
            }

            .attendance-chat-actions button,
            .attendance-chat-actions span {
                width: 42px;
                height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: rgba(250, 245, 255, 0.92);
                border: 1px solid rgba(233, 213, 255, 0.75);
                color: #a855f7;
                box-shadow: 0 12px 22px rgba(168, 85, 247, 0.10);
                transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
            }

            .attendance-chat-actions button:hover,
            .attendance-chat-actions span:hover {
                transform: translateY(-1px);
                background: #ffffff;
                box-shadow: 0 16px 28px rgba(168, 85, 247, 0.16);
            }

            .attendance-chat-body {
                padding: 0;
                background:
                    linear-gradient(180deg, rgba(255, 255, 255, 0.92) 0%, rgba(248, 251, 255, 0.96) 100%),
                    linear-gradient(90deg, rgba(59, 130, 246, 0.03) 0, rgba(59, 130, 246, 0.03) 1px, transparent 1px, transparent 32px),
                    linear-gradient(rgba(148, 163, 184, 0.03) 0, rgba(148, 163, 184, 0.03) 1px, transparent 1px, transparent 32px);
                background-size: auto, 32px 32px, 32px 32px;
            }

            .attendance-chat-thread {
                height: 56vh;
                min-height: 420px;
                max-height: 680px;
                overflow-y: auto;
                padding: 26px 26px 18px;
                display: flex;
                flex-direction: column;
                gap: 14px;
            }

            .attendance-chat-thread .chat-bubble-row {
                display: flex;
                width: 100%;
                margin-bottom: 0;
            }

            .attendance-chat-thread .chat-bubble-row.outgoing {
                justify-content: flex-end;
            }

            .attendance-chat-thread .chat-bubble {
                display: inline-block;
                width: fit-content;
                max-width: min(72%, 460px);
                min-width: 0;
                padding: 14px 16px 12px;
                border-radius: 24px 24px 24px 10px;
                background: rgba(255, 255, 255, 0.96);
                border: 1px solid rgba(226, 232, 240, 0.95);
                box-shadow: 0 18px 34px rgba(15, 23, 42, 0.07);
                overflow-wrap: anywhere;
                word-break: break-word;
                color: #0f172a;
            }

            .attendance-chat-thread .chat-bubble.outgoing {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                border-color: rgba(37, 99, 235, 0.85);
                color: #ffffff;
                border-radius: 24px 24px 10px 24px;
                box-shadow: 0 20px 34px rgba(37, 99, 235, 0.22);
            }

            .attendance-chat-thread .chat-bubble-meta {
                margin-top: 8px;
                font-size: 0.74rem;
                font-weight: 500;
                color: #64748b;
            }

            .attendance-chat-thread .chat-bubble.outgoing .chat-bubble-meta {
                color: rgba(255, 255, 255, 0.84);
            }

            .attendance-chat-thread .chat-bubble-image {
                border-radius: 20px;
            }

            .attendance-chat-thread .chat-bubble > div,
            .attendance-chat-thread .chat-bubble > a,
            .attendance-chat-thread .chat-bubble > audio,
            .attendance-chat-thread .chat-bubble > img {
                max-width: 100%;
            }

            .attendance-chat-thread .chat-empty {
                margin: auto;
                max-width: 320px;
                padding: 22px 20px;
                text-align: center;
                border-radius: 22px;
                background: rgba(255, 255, 255, 0.88);
                border: 1px solid rgba(226, 232, 240, 0.95);
                color: #64748b;
                box-shadow: 0 18px 34px rgba(15, 23, 42, 0.06);
            }

            .attendance-chat-footer {
                border-top: 1px solid #edf1f7;
                background: rgba(255, 255, 255, 0.94);
                padding: 16px 18px 18px;
                backdrop-filter: blur(16px);
            }

            .attendance-chat-preview {
                display: none;
                position: relative;
                width: 142px;
                height: 142px;
                margin-bottom: 14px;
                border-radius: 28px;
                background: #f4f7fb;
                box-shadow: inset 0 0 0 1px #e5edf7;
                overflow: hidden;
            }

            .attendance-chat-preview.is-visible {
                display: block;
            }

            .attendance-chat-preview img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .attendance-chat-preview-remove {
                position: absolute;
                top: 8px;
                right: 8px;
                width: 38px;
                height: 38px;
                border-radius: 50%;
                border: 0;
                background: rgba(255, 255, 255, 0.96);
                color: #111827;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.14);
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .attendance-chat-form {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .attendance-chat-attach {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background: #eff6ff;
                color: #2563eb;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                flex-shrink: 0;
                margin-bottom: 0;
            }

            .attendance-chat-attach input {
                display: none;
            }

            .attendance-chat-input {
                flex: 1;
                border: 1px solid rgba(226, 232, 240, 0.95);
                background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
                border-radius: 999px;
                min-height: 48px;
                padding: 0 18px;
                color: #0f172a;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
            }

            .attendance-chat-input:focus {
                outline: none;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14);
                background: #ffffff;
            }

            .attendance-chat-send {
                border: 0;
                min-width: 104px;
                height: 46px;
                border-radius: 999px;
                background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
                color: #ffffff;
                font-weight: 600;
                box-shadow: 0 14px 24px rgba(37, 99, 235, 0.25);
            }

            .attendance-chat-status-text {
                margin-top: 12px;
                color: #64748b;
                font-size: 0.88rem;
                padding-left: 6px;
            }

            .attendance-filter-card .card-header,
            .attendance-day-card .card-header {
                padding: 0.72rem 1.25rem;
                border-bottom: 1px solid #edf2f7;
            }

            .attendance-filter-card .card-title,
            .attendance-day-card .card-title {
                font-size: 0.92rem;
                font-weight: 700;
                letter-spacing: 0.01em;
                line-height: 1.1;
            }

            .attendance-filter-form {
                padding: 0.9rem 1.25rem 1rem;
            }

            .attendance-filter-grid {
                display: grid;
                grid-template-columns: minmax(180px, 240px) minmax(180px, 220px) minmax(220px, 1fr) auto;
                gap: 16px;
                align-items: end;
            }

            .attendance-filter-field {
                margin-bottom: 0;
            }

            .attendance-filter-field .form-control,
            .attendance-filter-field .form-select,
            .attendance-filter-actions .btn {
                min-height: 40px;
                border-radius: 14px;
            }

            .attendance-filter-field .form-control,
            .attendance-filter-field .form-select {
                border-color: #d9e2ef;
                box-shadow: none;
            }

            .attendance-filter-field .form-control:focus,
            .attendance-filter-field .form-select:focus {
                border-color: #93c5fd;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
            }

            .attendance-filter-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                justify-content: flex-end;
            }

            .attendance-filter-actions .btn {
                min-width: 108px;
                padding-inline: 16px;
                margin: 0;
            }

            .attendance-table-toolbar {
                display: grid;
                grid-template-columns: auto 1fr auto;
                align-items: center;
                gap: 12px;
                margin-bottom: 8px;
            }

            .attendance-toolbar-left {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .attendance-entry-control {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #111827;
                font-weight: 500;
            }

            .attendance-entry-select {
                min-width: 104px;
                min-height: 38px;
                padding-top: 0.45rem;
                padding-bottom: 0.45rem;
                border-radius: 14px;
            }

            .attendance-toolbar-actions {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }

            .attendance-toolbar-actions .btn,
            .attendance-filter-card .card-header .btn {
                min-height: 38px;
                padding: 0.5rem 0.9rem;
                border-radius: 14px;
            }

            .attendance-table-search-wrap {
                display: flex;
                justify-content: flex-end;
            }

            .attendance-table-search {
                width: min(100%, 250px);
                border: 1px solid #d7dfeb;
                border-radius: 14px;
                min-height: 38px;
                padding: 0 12px;
                color: #111827;
                background: #f8fbff;
                box-shadow: none;
            }

            .attendance-table-search:focus {
                outline: none;
                border-color: #93c5fd;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
            }

            .attendance-username-cell {
                position: relative;
            }

            .attendance-username-wrap {
                position: relative;
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                gap: 4px;
            }

            .attendance-username-summary {
                display: none;
                position: absolute;
                top: calc(100% + 6px);
                left: 50%;
                transform: translateX(-50%);
                min-width: 170px;
                padding: 8px 10px;
                border-radius: 12px;
                background: #1f2937;
                color: #fff;
                font-size: 0.76rem;
                line-height: 1.35;
                text-align: left;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.2);
                z-index: 6;
                white-space: pre-line;
            }

            .attendance-day-row:hover .attendance-username-summary {
                display: block;
            }

            .attendance-status-actions {
                position: relative;
            }

            .attendance-status-actions .quickApproveLeaveTrigger {
                position: absolute;
                top: calc(100% + 4px);
                left: 50%;
                z-index: 5;
                opacity: 0;
                visibility: hidden;
                white-space: nowrap;
                transform: translate(-50%, 4px);
                transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
            }

            .attendance-day-row:hover .attendance-status-actions .quickApproveLeaveTrigger,
            .attendance-status-actions .quickApproveLeaveTrigger:focus {
                opacity: 1;
                visibility: visible;
                transform: translate(-50%, 0);
            }

            .attendance-row-chat-action {
                display: none;
            }

            .attendance-day-row:hover .attendance-row-chat-action,
            .attendance-row-chat-action:focus-within {
                display: list-item;
            }

            .attendance-summary-footer {
                display: grid;
                grid-template-columns: repeat(8, minmax(0, 1fr));
                gap: 10px;
                margin-top: 12px;
            }

            .attendance-summary-item {
                padding: 10px 12px;
                border: 1px solid #e5ecf6;
                border-radius: 14px;
                background: #f8fbff;
                text-align: center;
                cursor: pointer;
                transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
            }

            .attendance-summary-item:hover {
                border-color: #bfd0eb;
                background: #f2f7ff;
                box-shadow: 0 8px 18px rgba(148, 163, 184, 0.12);
                transform: translateY(-1px);
            }

            .attendance-summary-item.is-active {
                border-color: #93c5fd;
                background: #eaf4ff;
                box-shadow: 0 10px 22px rgba(59, 130, 246, 0.14);
            }

            .attendance-summary-item strong {
                display: block;
                color: #0f172a;
                font-size: 1rem;
                line-height: 1.1;
            }

            .attendance-summary-item span {
                display: block;
                margin-top: 4px;
                color: #64748b;
                font-size: 0.78rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }

            @media (max-width: 767.98px) {
                .attendance-chat-modal .modal-dialog {
                    max-width: 100%;
                    margin: 0;
                }

                .attendance-chat-modal .modal-content {
                    min-height: 100vh;
                    border-radius: 0;
                }

                .attendance-chat-thread .chat-bubble {
                    max-width: 88%;
                }

                .attendance-chat-actions {
                    display: none;
                }

                .attendance-filter-form {
                    padding: 1rem;
                }

                .attendance-filter-grid {
                    grid-template-columns: 1fr;
                }

                .attendance-filter-actions {
                    justify-content: stretch;
                }

                .attendance-filter-actions .btn {
                    width: 100%;
                }

                .attendance-table-toolbar {
                    grid-template-columns: 1fr;
                    align-items: stretch;
                }

                .attendance-entry-control {
                    width: 100%;
                    justify-content: space-between;
                }

                .attendance-entry-select {
                    min-width: 0;
                    flex: 1;
                }

                .attendance-toolbar-actions,
                .attendance-table-search-wrap {
                    justify-content: flex-start;
                }

                .attendance-table-search {
                    width: 100%;
                }

                .attendance-status-actions .quickApproveLeaveTrigger {
                    position: static;
                    opacity: 1;
                    visibility: visible;
                    transform: none;
                }

                .attendance-row-chat-action {
                    display: list-item;
                }

                .attendance-username-summary {
                    position: static;
                    display: block;
                    transform: none;
                    margin-top: 6px;
                }

                .attendance-summary-footer {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
        </style>
        <?php
        if($isBsEnabled){
            $currentDate = \App\Helpers\AppHelper::getCurrentDateInBS();

        }else{
            $currentDate = \App\Helpers\AppHelper::getCurrentDateInYmdFormat();
        }

        $hasAttendanceFilters = filled($filterParameter['branch_id'] ?? null)
            || filled($filterParameter['department_id'] ?? null)
            || (($filterParameter['attendance_date'] ?? null) !== $currentDate);
        ?>

        @include('admin.section.flash_message')

        @include('admin.attendance.common.breadcrumb')
        <div class="card mb-4 attendance-filter-card">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#attendanceFilterCollapse"
                            aria-expanded="{{ $hasAttendanceFilters ? 'true' : 'false' }}"
                            aria-controls="attendanceFilterCollapse">
                        <i class="link-icon" data-feather="filter"></i>
                        {{ __('index.filter') }}
                    </button>
                    <h6 class="card-title mb-0">{{ __('index.attendance_filter') }}</h6>
                </div>
            </div>
            <div id="attendanceFilterCollapse" class="collapse{{ $hasAttendanceFilters ? ' show' : '' }}">
            <form class="forms-sample attendance-filter-form" action="{{ route('admin.attendances.index') }}" method="get">

                <div class="attendance-filter-grid">

                    <div class="attendance-filter-field">
                        <input id="attendance_date"
                               name="attendance_date"
                               value="{{ $filterParameter['attendance_date'] }}"
                               @if($isBsEnabled)
                                   class="form-control dayAttendance"
                               type="text"
                               placeholder="{{ __('index.date_placeholder_bs') }}"
                               @else
                                   class="form-control"
                               type="date"
                            @endif
                        />
                    </div>
                    @if(!isset(auth()->user()->branch_id))
                    <div class="attendance-filter-field">
                        <select class="form-select form-select-lg" name="branch_id" id="branch_id">
                            <option value="" {{ !isset($filterParameter['branch_id']) ? 'selected' : '' }}>{{ __('index.select_branch') }}</option>
                            @foreach($branch as $key =>  $value)
                                <option value="{{ $value->id }}" {{ (isset($filterParameter['branch_id']) && $value->id == $filterParameter['branch_id']) ? 'selected' : '' }}>
                                    {{ ucfirst($value->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @endif
                    <div class="attendance-filter-field">
                        <select class="form-select " name="department_id" id="department_id">
                            <option selected disabled>{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="attendance-filter-actions">
                            <button type="submit" class="btn btn-success">{{ __('index.filter') }}</button>
                            <a class="btn btn-primary me-0" href="{{ route('admin.attendances.index') }}">{{ __('index.reset') }}</a>
                    </div>

                </div>
            </form>
            </div>
        </div>

        @php
            $groupedAttendance = $attendanceDetail->groupBy('user_id');
            $isDayOffType = static function ($type) {
                $typeName = strtolower(trim((string) $type));
                return str_contains($typeName, 'day off');
            };
            $hasCheckIn = static function ($attendanceRows) {
                return $attendanceRows->contains(fn ($row) => !empty($row->check_in_at) || !empty($row->night_checkin));
            };
            $hasCheckOut = static function ($attendanceRows) {
                return $attendanceRows->contains(fn ($row) => !empty($row->check_out_at) || !empty($row->night_checkout));
            };

            $attendanceSummary = [
                'total_employee' => $groupedAttendance->count(),
                'total_check_in' => $groupedAttendance->filter($hasCheckIn)->count(),
                'total_check_out' => $groupedAttendance->filter($hasCheckOut)->count(),
                'total_not_yet_check_in' => $groupedAttendance->filter(function ($rows) use ($hasCheckIn) {
                    return !$hasCheckIn($rows);
                })->count(),
                'total_not_yet_check_out' => $groupedAttendance->filter(function ($rows) use ($hasCheckIn, $hasCheckOut) {
                    return $hasCheckIn($rows) && !$hasCheckOut($rows);
                })->count(),
                'total_day_off' => $groupedAttendance->filter(function ($rows) use ($isDayOffType) {
                    $first = $rows->first();
                    return $first?->leave_request_id
                        && $first?->leave_request_status === 'approved'
                        && $isDayOffType($first?->leave_request_type);
                })->count(),
                'total_leave' => $groupedAttendance->filter(function ($rows) use ($isDayOffType) {
                    $first = $rows->first();
                    return $first?->leave_request_id
                        && $first?->leave_request_status === 'approved'
                        && !$isDayOffType($first?->leave_request_type);
                })->count(),
                'total_leave_request' => $groupedAttendance->filter(function ($rows) {
                    $first = $rows->first();
                    return $first?->leave_request_id && $first?->leave_request_status === 'pending';
                })->count(),
            ];
        @endphp

        <div class="card attendance-day-card">
            <div class="card-header">
                <div class="attendance-table-toolbar mb-0">
                    <div class="attendance-toolbar-left">
                        <div class="attendance-entry-control">
                            <span>Show</span>
                            <select id="attendanceEntries" class="form-control attendance-entry-select">
                                <option value="all" selected>All</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                                <option value="1000">1,000</option>
                            </select>
                            <span>entries</span>
                        </div>
                        <h6 class="card-title mb-0">{{ __('index.attendance_of_the_day') }}</h6>
                    </div>
                    <div class="attendance-toolbar-actions">
                        @can('attendance_csv_export')
                            <button type="button"
                                    id="download-daywise-attendance-excel"
                                    data-href="{{ route('admin.attendances.index') }}"
                                    class="btn btn-outline-secondary btn-sm">Export
                            </button>
                        @endcan
                    </div>
                    <div class="attendance-table-search-wrap">
                        <input type="text"
                               id="attendanceDaySearch"
                               class="attendance-table-search"
                               placeholder="Search ...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                        <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                @can('attendance_show')
                                    <th></th>
                                @endcan
                                <th class="text-center">{{ __('index.username') }}</th>
                                <th>{{ __('index.employee_name') }}</th>
                                    @if($multipleAttendance > 1)
                                        <th class="text-center">{{ __('index.total_worked_hours') }}</th>
                                    @else
                                        <th class="text-center">Time In</th>
                                        <th class="text-center">{{ __('index.check_in_at') }}</th>
                                        <th class="text-center">Time Out</th>
                                        <th class="text-center">{{ __('index.check_out_at') }}</th>
                                        <th class="text-center">{{ __('index.worked_hour') }}</th>
                                    @endif
                                <th class="text-center">{{ __('index.attendance_status') }}</th>
                                <th class="text-center">{{ __('index.shift') }}</th>
                                @canany(['attendance_create', 'attendance_update', 'attendance_delete', 'view_employee_chat'])
                                    <th class="text-center">{{ __('index.action') }}</th>
                                @endcanany
                            </tr>
                            </thead>
                            <tbody>
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
                                    $selectedAttendanceDate = $isBsEnabled
                                        ? \App\Helpers\AppHelper::dateInYmdFormatNepToEng($filterParameter['attendance_date'])
                                        : $filterParameter['attendance_date'];
                                @endphp

                                @forelse($groupedAttendance as $userId => $userAttendances)

                                    @php
                                        $firstAttendance = $userAttendances->first();
                                        $totalWorkedMinutes = $userAttendances->sum('worked_hour');
                                        $lastAttendance = $userAttendances->last();

                                        $hours = floor($totalWorkedMinutes / 60);
                                        $minutes = $totalWorkedMinutes % 60;

                                        $workedHours = '';
                                        if ($hours > 0) {
                                            $workedHours .= $hours . ' h ';
                                        }
                                        if ($minutes > 0) {
                                            $workedHours .= $minutes . ' m';
                                        }
                                        $workedHours = trim($workedHours);

                                        $multipleEntries = $userAttendances->count();

                                        $nightShift = \App\Helpers\AppHelper::isOnNightShift($userId);
                                        $canAddAttendanceForSelectedDate = $filterParameter['attendance_date'] != $currentDate
                                            && !$firstAttendance->attendance_id
                                            && !$firstAttendance->leave_request_id;
                                        $quickChatTitle = 'Quick chat with ' . ucfirst($firstAttendance->user_name);
                                        $userHoverSummary = 'Day Off: ' . (int) ($firstAttendance->approved_day_off_days ?? 0)
                                            . "\nច្បាប់: " . (int) ($firstAttendance->approved_leave_days ?? 0)
                                            . "\nRequest Pending: " . (int) ($firstAttendance->pending_leave_days ?? 0);

                                    @endphp

                                    @php
                                        $rowHasCheckIn = $hasCheckIn($userAttendances);
                                        $rowHasCheckOut = $hasCheckOut($userAttendances);
                                        $rowIsApprovedLeave = $firstAttendance?->leave_request_id && $firstAttendance?->leave_request_status === 'approved';
                                        $rowIsPendingLeaveRequest = $firstAttendance?->leave_request_id && $firstAttendance?->leave_request_status === 'pending';
                                        $rowIsDayOff = $rowIsApprovedLeave && $isDayOffType($firstAttendance?->leave_request_type);
                                        $rowIsLeave = $rowIsApprovedLeave && !$isDayOffType($firstAttendance?->leave_request_type);
                                    @endphp

                                    <tr class="attendance-day-row"
                                        data-summary-total_employee="1"
                                        data-summary-total_check_in="{{ $rowHasCheckIn ? '1' : '0' }}"
                                        data-summary-total_not_yet_check_in="{{ !$rowHasCheckIn ? '1' : '0' }}"
                                        data-summary-total_check_out="{{ $rowHasCheckOut ? '1' : '0' }}"
                                        data-summary-total_not_yet_check_out="{{ ($rowHasCheckIn && !$rowHasCheckOut) ? '1' : '0' }}"
                                        data-summary-total_day_off="{{ $rowIsDayOff ? '1' : '0' }}"
                                        data-summary-total_leave="{{ $rowIsLeave ? '1' : '0' }}"
                                        data-summary-total_leave_request="{{ $rowIsPendingLeaveRequest ? '1' : '0' }}">
                                    @can('attendance_show')
                                        <td>
                                            <ul class="text-center list-unstyled mb-0">
                                                <li class="me-2">
                                                    <a href="{{ route('admin.attendances.show', $userId) }}"
                                                       title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    @endcan

                                    <td class="text-center attendance-username-cell">
                                        <div class="attendance-username-wrap">
                                            <span>{{ $firstAttendance->username ?: 'N/A' }}</span>
                                            <div class="attendance-username-summary">{{ $userHoverSummary }}</div>
                                        </div>
                                    </td>

                                    <td>
                                        @php
                                            $profileImage = $firstAttendance->avatar
                                                ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $firstAttendance->avatar)
                                                : asset('assets/images/img.png');
                                            $profileTitle = ucfirst($firstAttendance->user_name)
                                                . ' | ' . __('index.branch_name') . ': ' . ($firstAttendance->branch_name ? ucfirst($firstAttendance->branch_name) : 'N/A')
                                                . ' | ' . __('index.department') . ': ' . ($firstAttendance->department_name ? ucfirst($firstAttendance->department_name) : 'N/A');
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="#"
                                               class="showProfilePhoto"
                                               data-src="{{ $profileImage }}"
                                               data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                               title="{{ $profileTitle }}">
                                                <img src="{{ $profileImage }}"
                                                 alt="{{ ucfirst($firstAttendance->user_name) }}"
                                                 class="rounded-circle"
                                                 style="width: 42px; height: 42px; object-fit: cover;">
                                            </a>
                                            <div>
                                                <div class="fw-semibold">{{ ucfirst($firstAttendance->user_name) }}</div>
                                                <small class="text-muted">{{ $firstAttendance->phone ?: 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    @if($nightShift)
                                        @if($multipleAttendance <= 1)
                                            <td class="text-center">
                                                {{ $firstAttendance->office_opening_time ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_opening_time) : 'N/A' }}
                                            </td>
                                            @if(isset($firstAttendance->night_checkin))
                                                <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $firstAttendance->check_in_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($firstAttendance->check_in_type).' '.__('index.checkin') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{ 'https://maps.google.com/maps?q='.$firstAttendance->check_in_latitude.','.$firstAttendance->check_in_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                      data-bs-target="{{ '#addslider' }}"
                                                >
                                                    {{  \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $firstAttendance->night_checkin) ?? '' }}
                                                </span>
                                                </td>
                                            @else
                                                <td class="text-center"></td>
                                            @endif
                                            <td class="text-center">
                                                {{ $firstAttendance->office_closing_time ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_closing_time) : 'N/A' }}
                                            </td>

                                            @if( isset($firstAttendance->night_checkout))
                                                <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $firstAttendance->check_out_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($firstAttendance->check_out_type).' '.__('index.checkout') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{  'https://maps.google.com/maps?q='.$firstAttendance->check_out_latitude.','.$firstAttendance->check_out_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                      data-bs-target="{{  '#addslider' }}"
                                                >
                                                   {{  \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $firstAttendance->night_checkout)  ??  '' }}
                                                </span>
                                                </td>
                                            @else
                                                <td class="text-center"></td>
                                            @endif
                                        @endif

                                            <td class="text-center">
                                                {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($firstAttendance->worked_hour) }}
                                            </td>
                                    @elseif($multipleAttendance > 1)
                                        <td class="text-center">
                                            {{ $workedHours }}
                                        </td>
                                    @else
                                        <td class="text-center">
                                            {{ $firstAttendance->office_opening_time ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_opening_time) : 'N/A' }}
                                        </td>
                                        @if(isset($firstAttendance->check_in_at))
                                            <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $firstAttendance->check_in_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($firstAttendance->check_in_type).' '.__('index.checkin') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{ 'https://maps.google.com/maps?q='.$firstAttendance->check_in_latitude.','.$firstAttendance->check_in_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                      data-bs-target="{{ '#addslider' }}"
                                                >
                                                    {{  \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->check_in_at) ?? '' }}
                                                </span>
                                            </td>
                                        @else
                                            <td class="text-center"></td>
                                        @endif
                                        <td class="text-center">
                                            {{ $firstAttendance->office_closing_time ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_closing_time) : 'N/A' }}
                                        </td>

                                        @if(isset($firstAttendance->check_out_at) )
                                            <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $firstAttendance->check_out_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($firstAttendance->check_out_type).' '.__('index.checkout') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{  'https://maps.google.com/maps?q='.$firstAttendance->check_out_latitude.','.$firstAttendance->check_out_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                      data-bs-target="{{  '#addslider' }}"
                                                >
                                                   {{ \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->check_out_at) ??  '' }}
                                                </span>
                                            </td>
                                        @else
                                            <td class="text-center"></td>
                                        @endif

                                        <td class="text-center">
                                            {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($firstAttendance->worked_hour) }}
                                        </td>
                                    @endif

                                    @if(!is_null($firstAttendance->attendance_status))
                                        <td class="text-center">
                                            <a class="btn btn-{{ $changeColor[$firstAttendance->attendance_status] }} btn-xs"
                                               title="{{ $firstAttendance->attendance_status == \App\Models\Attendance::ATTENDANCE_APPROVED ? __('index.approved') : __('index.rejected') }}">
                                                {{ $firstAttendance->attendance_status == \App\Models\Attendance::ATTENDANCE_APPROVED ? __('index.approved') : __('index.rejected') }}
                                            </a>
                                        </td>
                                    @else
                                        <td class="text-center">
                                            @if($firstAttendance->leave_request_id)
                                                <div class="d-flex justify-content-center align-items-center gap-2">
                                                    @canany(['update_leave_request','access_admin_leave'])
                                                        <a href="#"
                                                           class="attendanceLeaveRequestUpdate"
                                                           data-href="{{ route('admin.leave-request.update-status', $firstAttendance->leave_request_id) }}"
                                                           data-status="{{ $firstAttendance->leave_request_status }}"
                                                           data-remark="{{ $firstAttendance->leave_request_admin_remark }}"
                                                           data-id="{{ $firstAttendance->leave_request_id }}">
                                                            <span class="btn btn-{{ $leaveRequestColor[$firstAttendance->leave_request_status] ?? 'secondary' }} btn-xs"
                                                                  title="{{ \App\Helpers\AppHelper::convertLeaveDateFormat($firstAttendance->leave_request_from) }} - {{ \App\Helpers\AppHelper::convertLeaveDateFormat($firstAttendance->leave_request_to) }}">
                                                                {{ $firstAttendance->leave_request_type ? ucfirst($firstAttendance->leave_request_type) : __('index.leave_request') }}
                                                                ({{ ucfirst($firstAttendance->leave_request_status) }})
                                                            </span>
                                                        </a>
                                                    @else
                                                        <span class="btn btn-{{ $leaveRequestColor[$firstAttendance->leave_request_status] ?? 'secondary' }} btn-xs"
                                                              title="{{ \App\Helpers\AppHelper::convertLeaveDateFormat($firstAttendance->leave_request_from) }} - {{ \App\Helpers\AppHelper::convertLeaveDateFormat($firstAttendance->leave_request_to) }}">
                                                            {{ $firstAttendance->leave_request_type ? ucfirst($firstAttendance->leave_request_type) : __('index.leave_request') }}
                                                            ({{ ucfirst($firstAttendance->leave_request_status) }})
                                                        </span>
                                                    @endcanany
                                                    @canany(['show_leave_request_detail','access_admin_leave'])
                                                        <a href="{{ route('admin.leave-request.show', $firstAttendance->leave_request_id) }}"
                                                           class="showAttendanceLeaveReason"
                                                           title="{{ __('index.show_leave_reason') }}">
                                                            <i class="link-icon" data-feather="eye"></i>
                                                        </a>
                                                    @endcanany
                                                </div>
                                            @else
                                                <div class="d-inline-flex flex-column align-items-center gap-2 attendance-status-actions">
                                                    <span class="btn btn-light btn-xs disabled">
                                                        {{ __('index.pending') }}
                                                    </span>
                                                    @can('quick_leave')
                                                        <a href="#"
                                                           class="btn btn-outline-primary btn-xs quickApproveLeaveTrigger"
                                                           data-user-id="{{ $firstAttendance->user_id }}"
                                                           data-user-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                           data-attendance-date="{{ $selectedAttendanceDate }}"
                                                           data-display-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $selectedAttendanceDate) }}"
                                                           data-fetch-url="{{ route('admin.leaves.employee-data', $firstAttendance->user_id) }}">
                                                            Quick Leave
                                                        </a>
                                                    @endcan
                                                </div>
                                            @endif
                                        </td>
                                    @endif

                                    @if($firstAttendance->shift)
                                        <td class="text-center">
                                            <span class="btn btn-warning btn-xs">
                                                {{ ucfirst($firstAttendance->shift)  }}
                                            </span>
                                        </td>
                                    @else
                                        <td class="text-center">
                                        </td>
                                    @endif

                                    @canany(['attendance_create','attendance_update','attendance_delete','view_employee_chat'])
                                        @if($nightShift && $filterParameter['attendance_date'] ==  $currentDate)

                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">
                                                    @can('view_employee_chat')
                                                        <li class="me-2 attendance-row-chat-action">
                                                            <a href="#"
                                                               class="openAttendanceChat"
                                                               data-employee-id="{{ $firstAttendance->user_id }}"
                                                               data-employee-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                               data-employee-avatar="{{ $profileImage }}"
                                                               data-employee-subtitle="{{ $firstAttendance->department_name ? ucfirst($firstAttendance->department_name) : ($firstAttendance->phone ?: 'Employee') }}"
                                                               data-employee-online="{{ (int) ($firstAttendance->online_status ?? 0) === \App\Models\User::ONLINE ? '1' : '0' }}"
                                                               title="{{ $quickChatTitle }}">
                                                                <i class="link-icon" data-feather="message-circle"></i>
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @php
                                                        $nightAttendance = \App\Helpers\AttendanceHelper::checkNightShiftCheckOut($userId);

                                                    @endphp
                                                    @if($nightAttendance == 'checkout')
                                                        @can('attendance_update')
                                                            <li class="me-2">
                                                                <a href="{{ route('admin.employees.check-out', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                   id="checkOut"
                                                                   data-href=""
                                                                   data-id="">
                                                                    <button class="btn btn-danger btn-xs">{{ __('index.check_out') }}</button>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    @elseif($nightAttendance == 'checkin')
                                                        @can('attendance_create')
                                                            <li class="me-2">
                                                                <a href="{{ route('admin.employees.check-in', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                   id="checkIn"
                                                                   data-href=""
                                                                   data-id="">
                                                                    <button class="btn btn-success btn-xs">{{ __('index.check_in') }}</button>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    @else

                                                    @endif

                                                    @if($firstAttendance->attendance_id)
                                                        @can('attendance_update')
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="editNightAttendance"
                                                                   data-href="{{ route('admin.night_attendances.update', $firstAttendance->attendance_id) }}"
                                                                   data-in="{{ $firstAttendance->night_checkin }}"
                                                                   data-out="{{ $firstAttendance->night_checkout ?? null  }}"
                                                                   data-remark="{{ $firstAttendance->edit_remark }}"
                                                                   data-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $firstAttendance->attendance_date) }}"
                                                                   data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                                   title="{{ __('index.edit_attendance_time') }}"
                                                                >
                                                                    <i class="link-icon"
                                                                       data-feather="edit"></i>
                                                                </a>
                                                            </li>
                                                        @endcan

                                                        @can('attendance_delete')
                                                            <li class="me-2">
                                                                <a class="deleteAttendance" href="{{ route('admin.attendance.delete', $firstAttendance->attendance_id) }}">
                                                                    <i class="link-icon"  data-feather="delete"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                        @if($attendanceNote)
                                                            <li class="me-2">
                                                                <a href="#"
                                                                   class="noteLink"
                                                                   data-checkout_note="{{ $firstAttendance->check_out_note }}"
                                                                   data-checkin_note="{{ $firstAttendance->check_in_note }}">
                                                                    Note
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endif
                                                </ul>
                                            </td>
                                        @elseif($multipleAttendance > 1)
                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">
                                                    @can('view_employee_chat')
                                                        <li class="me-2 attendance-row-chat-action">
                                                            <a href="#"
                                                               class="openAttendanceChat"
                                                               data-employee-id="{{ $firstAttendance->user_id }}"
                                                               data-employee-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                               data-employee-avatar="{{ $profileImage }}"
                                                               data-employee-subtitle="{{ $firstAttendance->department_name ? ucfirst($firstAttendance->department_name) : ($firstAttendance->phone ?: 'Employee') }}"
                                                               data-employee-online="{{ (int) ($firstAttendance->online_status ?? 0) === \App\Models\User::ONLINE ? '1' : '0' }}"
                                                               title="{{ $quickChatTitle }}">
                                                                <i class="link-icon" data-feather="message-circle"></i>
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @if($filterParameter['attendance_date'] == $currentDate && ($multipleEntries < $multipleAttendance || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at)))

                                                        @if((!$firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendance->check_in_at && $lastAttendance->check_out_at))
                                                            @can('attendance_create')
                                                                <li class="me-2">
                                                                    <a href="{{ route('admin.employees.check-in', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                       id="checkIn"
                                                                       data-href=""
                                                                       data-id="">
                                                                        <button
                                                                            class="btn btn-success btn-xs">{{ __('index.check_in') }}</button>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                        @elseif(($firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at))
                                                            @can('attendance_update')
                                                                <li class="me-2">
                                                                    <a href="{{ route('admin.employees.check-out', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                       id="checkOut"
                                                                       data-href=""
                                                                       data-id="">
                                                                        <button
                                                                            class="btn btn-danger btn-xs">{{ __('index.check_out') }}</button>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                        @endif

                                                    @endif
                                                    @if($canAddAttendanceForSelectedDate)
                                                        @can('attendance_create')
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="addEmployeeAttendance"
                                                                   data-href="{{ route('admin.attendances.store') }}"
                                                                   data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                                   data-date="{{ $filterParameter['attendance_date'] }}"
                                                                   data-cdate="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $filterParameter['attendance_date']) }}"
                                                                   data-user_id="{{ $firstAttendance->user_id }}"
                                                                   title="{{ __('index.add_attendance_time') }}">
                                                                    <i class="link-icon" data-feather="plus-circle"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    @endif
                                                    @if($attendanceNote)
                                                        <li class="me-2">
                                                            <a href="#"
                                                               class="noteLink"
                                                               data-checkout_note="{{ $firstAttendance->check_out_note }}"
                                                               data-checkin_note="{{ $firstAttendance->check_in_note }}">
                                                                Note
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </td>
                                        @else
                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">
                                                    @can('view_employee_chat')
                                                        <li class="me-2 attendance-row-chat-action">
                                                            <a href="#"
                                                               class="openAttendanceChat"
                                                               data-employee-id="{{ $firstAttendance->user_id }}"
                                                               data-employee-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                               data-employee-avatar="{{ $profileImage }}"
                                                               data-employee-subtitle="{{ $firstAttendance->department_name ? ucfirst($firstAttendance->department_name) : ($firstAttendance->phone ?: 'Employee') }}"
                                                               data-employee-online="{{ (int) ($firstAttendance->online_status ?? 0) === \App\Models\User::ONLINE ? '1' : '0' }}"
                                                               title="{{ $quickChatTitle }}">
                                                                <i class="link-icon" data-feather="message-circle"></i>
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @if($filterParameter['attendance_date'] ==  $currentDate)
                                                            @if(!$firstAttendance->check_in_at)
                                                                @can('attendance_create')
                                                                    <li class="me-2">
                                                                        <a href="{{ route('admin.employees.check-in', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                           id="checkIn"
                                                                           data-href=""
                                                                           data-id="">
                                                                            <button class="btn btn-success btn-xs">{{ __('index.check_in') }}</button>
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            @endif


                                                            @if($firstAttendance->check_in_at && !$firstAttendance->check_out_at)
                                                                @can('attendance_update')
                                                                    <li class="me-2">
                                                                        <a href="{{ route('admin.employees.check-out', [$firstAttendance->company_id, $firstAttendance->user_id]) }}"
                                                                           id="checkOut"
                                                                           data-href=""
                                                                           data-id="">
                                                                            <button class="btn btn-danger btn-xs">{{ __('index.check_out') }}</button>
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            @endif
                                                    @endif

                                                    @if($firstAttendance->attendance_id)
                                                        @can('attendance_update')
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="editAttendance"
                                                                   data-href="{{ route('admin.attendances.update', $firstAttendance->attendance_id) }}"
                                                                   data-in="{{ date('H:i', strtotime($firstAttendance->check_in_at)) }}"
                                                                   data-out="{{ $firstAttendance->check_out_at ? date('H:i', strtotime($firstAttendance->check_out_at)) : null }}"
                                                                   data-remark="{{ $firstAttendance->edit_remark }}"
                                                                   data-date="{{ $filterParameter['attendance_date'] }}"
                                                                   data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                                   title="{{ __('index.edit_attendance_time') }}"
                                                                >
                                                                    <i class="link-icon"
                                                                       data-feather="edit"></i>
                                                                </a>
                                                            </li>
                                                        @endcan

                                                        @can('attendance_delete')
                                                            <li class="me-2">
                                                                <a class="deleteAttendance" href="{{ route('admin.attendance.delete', $firstAttendance->attendance_id) }}">
                                                                    <i class="link-icon"  data-feather="delete"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                            @if($attendanceNote)
                                                                <li class="me-2">
                                                                    <a href="#"
                                                                       class="noteLink"
                                                                       data-checkout_note="{{ $firstAttendance->check_out_note }}"
                                                                       data-checkin_note="{{ $firstAttendance->check_in_note }}">
                                                                        Note
                                                                    </a>
                                                                </li>
                                                            @endif
                                                    @endif

                                                    @if($canAddAttendanceForSelectedDate)
                                                        @can('attendance_create')
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="addEmployeeAttendance"
                                                                   data-href="{{ route('admin.attendances.store') }}"
                                                                   data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                                                   data-date="{{ $filterParameter['attendance_date'] }}"
                                                                   data-cdate="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $filterParameter['attendance_date']) }}"
                                                                   data-user_id="{{ $firstAttendance->user_id }}"
                                                                   title="{{ __('index.add_attendance_time') }}">
                                                                    <i class="link-icon" data-feather="plus-circle"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    @endif

                                                </ul>
                                            </td>
                                        @endif
                                    @endcanany

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
                        <div class="attendance-summary-footer">
                            <div class="attendance-summary-item" data-summary-filter="total_employee" role="button" tabindex="0">
                                <strong>{{ number_format($attendanceSummary['total_employee']) }}</strong>
                                <span>Total Employee</span>
                            </div>
                            <div class="attendance-summary-item" data-summary-filter="total_check_in" role="button" tabindex="0">
                                <strong>{{ number_format($attendanceSummary['total_check_in']) }}</strong>
                                <span>Total Check In</span>
                            </div>
                            <div class="attendance-summary-item" data-summary-filter="total_not_yet_check_in" role="button" tabindex="0">
                                <strong>{{ number_format($attendanceSummary['total_not_yet_check_in']) }}</strong>
                                <span>Not Yet Check In</span>
                            </div>
                            <div class="attendance-summary-item" data-summary-filter="total_check_out" role="button" tabindex="0">
                                <strong>{{ number_format($attendanceSummary['total_check_out']) }}</strong>
                                <span>Total Check Out</span>
                            </div>
                            <div class="attendance-summary-item" data-summary-filter="total_not_yet_check_out" role="button" tabindex="0">
                                <strong>{{ number_format($attendanceSummary['total_not_yet_check_out']) }}</strong>
                                <span>Not Yet Check Out</span>
                            </div>
                            <div class="attendance-summary-item" data-summary-filter="total_day_off" role="button" tabindex="0">
                                <strong>{{ number_format($attendanceSummary['total_day_off']) }}</strong>
                                <span>Total Day Off</span>
                            </div>
                            <div class="attendance-summary-item" data-summary-filter="total_leave" role="button" tabindex="0">
                                <strong>{{ number_format($attendanceSummary['total_leave']) }}</strong>
                                <span>ច្បាប់</span>
                            </div>
                            <div class="attendance-summary-item" data-summary-filter="total_leave_request" role="button" tabindex="0">
                                <strong>{{ number_format($attendanceSummary['total_leave_request']) }}</strong>
                                <span>Leave Request</span>
                            </div>
                        </div>

                </div>
            </div>
        </div>


        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <iframe id="iframeModalWindow" class="attendancelocation" height="500px" width="100%" src="" name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.attendance.common.edit-attendance-form')
        @include('admin.attendance.common.create-attendance-form')
        @include('admin.attendance.common.edit-night-attendance-form')

        <div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-labelledby="profilePhotoModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="profilePhotoModalTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="profilePhotoPreview" src="" alt="profile" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>

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
                        <h5 class="modal-title">{{ __('index.leave_request_section') }}</h5>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <form class="forms-sample" id="attendanceUpdateLeaveStatus" action="" method="post">
                                @csrf
                                @method('put')
                                <input type="hidden" name="redirect_back" value="1">
                                <div class="row">
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

                            <div class="text-start">
                                <button type="submit" class="btn btn-primary btn-sm" id="attendanceQuickLeaveSubmit">
                                    Save as Approved Leave
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- note for checkin and checkout -->
        <div id="noteModal" class="modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Attendance Notes</h5>
                    </div>
                    <div class="modal-body">
                        <p><strong>Check-in Note:</strong> <span id="checkinNote"></span></p>
                        <p><strong>Check-out Note:</strong> <span id="checkoutNote"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade attendance-chat-modal" id="attendanceChatModal" tabindex="-1" aria-labelledby="attendanceChatModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content attendance-chat-shell">
                    <div class="attendance-chat-header">
                        <div class="attendance-chat-person">
                            <div class="attendance-chat-avatar-wrap">
                                <img id="attendanceChatAvatar" src="{{ asset('assets/images/img.png') }}" alt="Employee avatar" class="attendance-chat-avatar">
                                <span id="attendanceChatStatus" class="attendance-chat-status"></span>
                            </div>
                            <div class="min-w-0">
                                <h5 id="attendanceChatModalLabel">Employee Chat</h5>
                                <p id="attendanceChatSubtitle">Open a conversation from attendance.</p>
                            </div>
                        </div>
                        <div class="attendance-chat-actions">
                            <span><i data-feather="phone"></i></span>
                            <span><i data-feather="video"></i></span>
                            <button type="button" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="attendance-chat-body">
                        <div id="attendanceChatThread"
                             class="attendance-chat-thread"
                             data-base-url="{{ route('admin.employee-chat.messages') }}">
                            <div class="chat-empty">Select an employee to start chatting.</div>
                        </div>
                    </div>
                    <div class="attendance-chat-footer">
                        @can('send_employee_chat')
                            <div class="attendance-chat-preview" id="attendanceChatPreview">
                                <img id="attendanceChatPreviewImage" src="" alt="Attachment preview">
                                <button type="button" class="attendance-chat-preview-remove" id="attendanceChatPreviewRemove" aria-label="Remove attachment">
                                    <i data-feather="x"></i>
                                </button>
                            </div>
                            <form id="attendanceChatForm" class="attendance-chat-form" action="{{ route('admin.employee-chat.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="employee_id" id="attendanceChatEmployeeId">
                                <label class="attendance-chat-attach">
                                    <i data-feather="paperclip"></i>
                                    <input type="file" name="attachment" id="attendanceChatAttachment">
                                </label>
                                <input type="text" class="attendance-chat-input" name="message" id="attendanceChatMessage" placeholder="Type your message">
                                <button type="submit" class="attendance-chat-send">Send</button>
                            </form>
                            <div class="attendance-chat-status-text" id="attendanceChatStatusText">You can send text, image, or voice files here. You can also paste a screenshot.</div>
                        @else
                            <div class="attendance-chat-status-text" id="attendanceChatStatusText">You have view access only. Chat sending is disabled for your role.</div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    @include('admin.attendance.common.scripts')
    <script>
        $(document).ready(function () {
            const loadDepartments = async () => {

                const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
                const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
                const selectedBranchId = isAdmin ? $('#branch_id option:selected').val() : defaultBranchId;


                let departmentId = "{{  $filterParameter['department_id'] ?? '' }}";
                console.log(departmentId);
                $('#department_id').empty();
                if (selectedBranchId) {
                    $.ajax({
                        type: 'GET',
                        url: "{{ url('admin/departments/get-All-Departments') }}" + '/' + selectedBranchId,
                    }).done(function (response) {
                        if (!departmentId) {
                            $('#department_id').append('<option disabled  selected >{{ __('index.select_department') }}</option>');
                        }
                        response.data.forEach(function (data) {
                            $('#department_id').append('<option ' + ((data.id == departmentId) ? "selected" : '') + ' value="' + data.id + '" >' + data.dept_name + '</option>');
                        });
                    });
                }
            };

            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            if (isAdmin) {
                $('#branch_id').on('change', loadDepartments);
                $('#branch_id').trigger('change');
            } else {
                loadDepartments(); // Load directly for regular users
            }

        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
                new bootstrap.Tooltip(element);
            });

            const noteModal = new bootstrap.Modal(document.getElementById('noteModal'));
            const attendanceDaySearch = document.getElementById('attendanceDaySearch');
            const attendanceEntries = document.getElementById('attendanceEntries');
            const attendanceDayTable = document.getElementById('dataTableExample');
            const attendanceDayRows = attendanceDayTable
                ? Array.from(attendanceDayTable.querySelectorAll('tbody .attendance-day-row'))
                : [];
            const attendanceEmptyRow = attendanceDayTable
                ? attendanceDayTable.querySelector('tbody tr td[colspan]')
                : null;
            const attendanceSummaryItems = Array.from(document.querySelectorAll('.attendance-summary-item[data-summary-filter]'));
            let activeAttendanceSummaryFilter = null;

            document.querySelectorAll('.noteLink').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const checkinNote = this.getAttribute('data-checkin_note');
                    const checkoutNote = this.getAttribute('data-checkout_note');

                    document.getElementById('checkinNote').textContent = checkinNote || '';
                    document.getElementById('checkoutNote').textContent = checkoutNote || '';

                    noteModal.show();
                });
            });

            const attachGeoRedirect = (anchor) => {
                anchor.addEventListener('click', function (e) {
                    const href = anchor.getAttribute('href');
                    if (!href || href === '#') return;

                    if (!navigator.geolocation) {
                        return; // fallback: normal navigation without coords
                    }

                    e.preventDefault();

                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            const lat = pos.coords.latitude;
                            const long = pos.coords.longitude;
                            const url = new URL(href, window.location.origin);
                            url.searchParams.set('lat', String(lat));
                            url.searchParams.set('long', String(long));
                            window.location.href = url.toString();
                        },
                        function () {
                            window.location.href = href; // fallback if denied/error
                        },
                        { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                    );
                });
            };

            document.querySelectorAll('a#checkIn, a#checkOut').forEach(attachGeoRedirect);

            document.querySelectorAll('.showProfilePhoto').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();

                    document.getElementById('profilePhotoPreview').setAttribute('src', this.getAttribute('data-src'));
                    document.getElementById('profilePhotoModalTitle').innerText = this.getAttribute('data-name') || '';

                    const modal = new bootstrap.Modal(document.getElementById('profilePhotoModal'));
                    modal.show();
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

                                const modalElement = document.getElementById('attendanceLeaveRequestModal');
                                if (modalElement) {
                                    const modal = new bootstrap.Modal(modalElement);
                                    modal.show();
                                }
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
                    const leaveRequestId = this.getAttribute('data-id');

                    document.getElementById('attendanceUpdateLeaveStatus').setAttribute('action', url);
                    document.getElementById('attendanceLeaveStatus').value = status;
                    document.getElementById('attendanceLeaveRemark').value = remark || '';
                    document.getElementById('attendancePreviousApprovers').innerHTML = '';

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

                    const modalElement = document.getElementById('attendanceLeaveStatusUpdate');
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                });
            });

            const applyAttendanceTableFilters = () => {
                if (!attendanceDayTable) {
                    return;
                }

                const query = attendanceDaySearch ? attendanceDaySearch.value.trim().toLowerCase() : '';
                const limitValue = attendanceEntries ? attendanceEntries.value : '25';
                const limit = limitValue === 'all' ? Number.POSITIVE_INFINITY : parseInt(limitValue, 10);
                let shownCount = 0;
                let matchedCount = 0;

                attendanceDayRows.forEach((row) => {
                    const text = row.textContent.toLowerCase();
                    const matchesSearch = text.includes(query);
                    const matchesSummary = !activeAttendanceSummaryFilter
                        || row.dataset[`summary${activeAttendanceSummaryFilter.replace(/(^|_)(\w)/g, (_, __, char) => char.toUpperCase())}`] === '1';
                    const matches = matchesSearch && matchesSummary;

                    if (matches) {
                        matchedCount++;
                    }

                    const shouldShow = matches && shownCount < limit;
                    row.style.display = shouldShow ? '' : 'none';

                    if (shouldShow) {
                        shownCount++;
                    }
                });

                if (attendanceEmptyRow) {
                    attendanceEmptyRow.parentElement.style.display = matchedCount === 0 ? '' : 'none';
                }
            };

            if (attendanceDaySearch && attendanceDayTable) {
                attendanceDaySearch.addEventListener('input', applyAttendanceTableFilters);
            }

            if (attendanceEntries && attendanceDayTable) {
                attendanceEntries.addEventListener('change', applyAttendanceTableFilters);
            }

            attendanceSummaryItems.forEach((item) => {
                const toggleSummaryFilter = () => {
                    const nextFilter = item.dataset.summaryFilter || null;
                    activeAttendanceSummaryFilter = activeAttendanceSummaryFilter === nextFilter ? null : nextFilter;

                    attendanceSummaryItems.forEach((summaryItem) => {
                        summaryItem.classList.toggle('is-active', summaryItem.dataset.summaryFilter === activeAttendanceSummaryFilter);
                    });

                    applyAttendanceTableFilters();
                };

                item.addEventListener('click', toggleSummaryFilter);
                item.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        toggleSummaryFilter();
                    }
                });
            });

            applyAttendanceTableFilters();

            const attendanceChatModalElement = document.getElementById('attendanceChatModal');
            const attendanceChatModal = attendanceChatModalElement ? new bootstrap.Modal(attendanceChatModalElement) : null;
            const attendanceChatThread = document.getElementById('attendanceChatThread');
            const attendanceChatForm = document.getElementById('attendanceChatForm');
            const attendanceChatEmployeeId = document.getElementById('attendanceChatEmployeeId');
            const attendanceChatAttachment = document.getElementById('attendanceChatAttachment');
            const attendanceChatPreview = document.getElementById('attendanceChatPreview');
            const attendanceChatPreviewImage = document.getElementById('attendanceChatPreviewImage');
            const attendanceChatPreviewRemove = document.getElementById('attendanceChatPreviewRemove');
            const attendanceChatAvatar = document.getElementById('attendanceChatAvatar');
            const attendanceChatTitle = document.getElementById('attendanceChatModalLabel');
            const attendanceChatSubtitle = document.getElementById('attendanceChatSubtitle');
            const attendanceChatStatus = document.getElementById('attendanceChatStatus');
            const attendanceChatStatusText = document.getElementById('attendanceChatStatusText');
            const attendanceQuickLeaveModalElement = document.getElementById('attendanceQuickLeaveModal');
            const attendanceQuickLeaveModal = attendanceQuickLeaveModalElement ? new bootstrap.Modal(attendanceQuickLeaveModalElement) : null;
            const attendanceQuickLeaveUserId = document.getElementById('attendanceQuickLeaveUserId');
            const attendanceQuickLeaveDate = document.getElementById('attendanceQuickLeaveDate');
            const attendanceQuickLeaveType = document.getElementById('attendanceQuickLeaveType');
            const attendanceQuickLeaveReason = document.getElementById('attendanceQuickLeaveReason');
            const attendanceQuickLeaveSubmit = document.getElementById('attendanceQuickLeaveSubmit');
            const attendanceQuickLeaveLabel = document.getElementById('attendanceQuickLeaveModalLabel');
            const attendanceQuickLeaveHelpText = document.getElementById('attendanceQuickLeaveHelpText');
            let attendanceChatPoller = null;
            let activeAttendanceChatEmployeeId = null;

            const resetQuickLeaveOptions = (message = 'Loading leave types...') => {
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
                    attendanceQuickLeaveLabel.textContent = `Quick Leave: ${userName}`;
                    attendanceQuickLeaveHelpText.textContent = `Create an already approved leave for ${displayDate}.`;

                    resetQuickLeaveOptions();
                    attendanceQuickLeaveModal.show();

                    fetch(fetchUrl)
                        .then(response => response.json())
                        .then(data => {
                            const leaveTypes = data.leaveTypes || data.leveTypes || [];

                            if (!leaveTypes.length) {
                                resetQuickLeaveOptions('No leave types available');
                                attendanceQuickLeaveHelpText.textContent = 'No leave types are available for this employee.';
                                return;
                            }

                            attendanceQuickLeaveType.disabled = false;
                            attendanceQuickLeaveType.innerHTML = '<option value="">Select leave type</option>';

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
                            resetQuickLeaveOptions('Unable to load leave types');
                            attendanceQuickLeaveHelpText.textContent = 'Unable to load leave types right now. Please try again.';
                        });
                });
            });

            const attendanceChatScrollToBottom = () => {
                if (attendanceChatThread) {
                    attendanceChatThread.scrollTop = attendanceChatThread.scrollHeight;
                }
            };

            const attendanceChatMessagesUrl = (employeeId) => {
                const url = new URL(attendanceChatThread.dataset.baseUrl, window.location.origin);
                url.searchParams.set('employee_id', employeeId);
                return url.toString();
            };

            const renderAttendanceChatMessages = async (employeeId, keepStatus = true) => {
                if (!attendanceChatThread || !employeeId) {
                    return;
                }

                try {
                    const response = await fetch(attendanceChatMessagesUrl(employeeId), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to load chat messages.');
                    }

                    attendanceChatThread.innerHTML = data.html;
                    attendanceChatScrollToBottom();
                    if (!keepStatus && attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = 'Conversation loaded.';
                    }
                    if (window.feather) {
                        feather.replace();
                    }
                } catch (error) {
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = error.message || 'Unable to load chat messages.';
                    }
                }
            };

            const stopAttendanceChatPolling = () => {
                if (attendanceChatPoller) {
                    clearInterval(attendanceChatPoller);
                    attendanceChatPoller = null;
                }
            };

            const startAttendanceChatPolling = (employeeId) => {
                stopAttendanceChatPolling();
                attendanceChatPoller = setInterval(() => {
                    if (activeAttendanceChatEmployeeId === employeeId) {
                        renderAttendanceChatMessages(employeeId);
                    }
                }, 5000);
            };

            const bindClipboardImagePaste = (target, fileInput, setStatus) => {
                if (!target || !fileInput) {
                    return;
                }

                target.addEventListener('paste', function (event) {
                    const items = event.clipboardData?.items || [];

                    for (const item of items) {
                        if (!item.type || !item.type.startsWith('image/')) {
                            continue;
                        }

                        const blob = item.getAsFile();
                        if (!blob) {
                            continue;
                        }

                        const extension = (blob.type.split('/')[1] || 'png').replace('jpeg', 'jpg');
                        const file = new File([blob], `pasted-screenshot-${Date.now()}.${extension}`, { type: blob.type });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        fileInput.files = dataTransfer.files;

                        if (typeof setStatus === 'function') {
                            setStatus(`Screenshot pasted: ${file.name}`);
                        }
                        event.preventDefault();
                        break;
                    }
                });
            };

            const showAttendanceChatPreview = (file) => {
                if (!attendanceChatPreview || !attendanceChatPreviewImage || !file || !file.type.startsWith('image/')) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    attendanceChatPreviewImage.src = event.target?.result || '';
                    attendanceChatPreview.classList.add('is-visible');
                    if (window.feather) {
                        feather.replace();
                    }
                };
                reader.readAsDataURL(file);
            };

            const clearAttendanceChatPreview = () => {
                if (attendanceChatAttachment) {
                    attendanceChatAttachment.value = '';
                }
                if (attendanceChatPreviewImage) {
                    attendanceChatPreviewImage.src = '';
                }
                if (attendanceChatPreview) {
                    attendanceChatPreview.classList.remove('is-visible');
                }
            };

            document.querySelectorAll('.openAttendanceChat').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();

                    const employeeId = this.getAttribute('data-employee-id');
                    if (!employeeId || !attendanceChatModal) {
                        return;
                    }

                    activeAttendanceChatEmployeeId = employeeId;
                    if (attendanceChatEmployeeId) {
                        attendanceChatEmployeeId.value = employeeId;
                    }
                    if (attendanceChatAvatar) {
                        attendanceChatAvatar.setAttribute('src', this.getAttribute('data-employee-avatar') || '{{ asset('assets/images/img.png') }}');
                    }
                    if (attendanceChatTitle) {
                        attendanceChatTitle.textContent = this.getAttribute('data-employee-name') || 'Employee Chat';
                    }
                    if (attendanceChatSubtitle) {
                        attendanceChatSubtitle.textContent = this.getAttribute('data-employee-subtitle') || 'Employee';
                    }
                    if (attendanceChatStatus) {
                        attendanceChatStatus.classList.toggle('online', this.getAttribute('data-employee-online') === '1');
                    }
                    if (attendanceChatThread) {
                        attendanceChatThread.innerHTML = '<div class="chat-empty">Loading conversation...</div>';
                    }
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = 'Loading messages...';
                    }

                    attendanceChatModal.show();
                    renderAttendanceChatMessages(employeeId, false);
                    startAttendanceChatPolling(employeeId);
                });
            });

            if (attendanceChatForm) {
                bindClipboardImagePaste(attendanceChatForm, attendanceChatAttachment, (message) => {
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = message;
                    }
                    const file = attendanceChatAttachment.files?.[0];
                    if (file) {
                        showAttendanceChatPreview(file);
                    }
                });

                attendanceChatAttachment?.addEventListener('change', function () {
                    const file = this.files?.[0];
                    if (file && file.type.startsWith('image/')) {
                        showAttendanceChatPreview(file);
                        if (attendanceChatStatusText) {
                            attendanceChatStatusText.textContent = `Image ready: ${file.name}`;
                        }
                    } else {
                        clearAttendanceChatPreview();
                    }
                });

                attendanceChatPreviewRemove?.addEventListener('click', function () {
                    clearAttendanceChatPreview();
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = 'Attachment removed.';
                    }
                });

                attendanceChatForm.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    if (!activeAttendanceChatEmployeeId) {
                        return;
                    }

                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = 'Sending message...';
                    }

                    try {
                        const response = await fetch(attendanceChatForm.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: new FormData(attendanceChatForm)
                        });
                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Unable to send message.');
                        }

                        attendanceChatThread.innerHTML = data.html;
                        attendanceChatForm.reset();
                        clearAttendanceChatPreview();
                        attendanceChatScrollToBottom();
                        if (attendanceChatStatusText) {
                            attendanceChatStatusText.textContent = 'Message sent successfully.';
                        }
                        if (window.feather) {
                            feather.replace();
                        }
                    } catch (error) {
                        if (attendanceChatStatusText) {
                            attendanceChatStatusText.textContent = error.message || 'Unable to send message right now.';
                        }
                    }
                });
            }

            if (attendanceChatModalElement) {
                attendanceChatModalElement.addEventListener('hidden.bs.modal', function () {
                    stopAttendanceChatPolling();
                    activeAttendanceChatEmployeeId = null;
                    if (attendanceChatThread) {
                        attendanceChatThread.innerHTML = '<div class="chat-empty">Select an employee to start chatting.</div>';
                    }
                    if (attendanceChatForm) {
                        attendanceChatForm.reset();
                    }
                    clearAttendanceChatPreview();
                    if (attendanceChatStatusText) {
                        attendanceChatStatusText.textContent = @can('send_employee_chat')
                            'You can send text, image, or voice files here. You can also paste a screenshot.'
                        @else
                            'You have view access only. Chat sending is disabled for your role.'
                        @endcan;
                    }
                });
            }
        });
    </script>
@endsection
