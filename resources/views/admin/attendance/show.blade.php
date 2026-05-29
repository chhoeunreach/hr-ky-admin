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
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_of') . ' ' .ucfirst($userDetail->name) }}</h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.attendances.show', $userDetail->id) }}"
                    method="get">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-3 mb-4">
                            <input type="number" min="{{ $filterData['min_year'] }}"
                                max="{{ $filterData['max_year'] }}" step="1"
                                placeholder="{{ __('index.attendance_year_example', ['year' => $filterData['min_year']]) }}"
                                id="year"
                                name="year"
                                value="{{ $filterParameter['year'] }}"
                                class="form-control">
                        </div>

                        <div class="col-lg-4 col-md-3 mb-4">
                            <select class="form-select form-select-lg" name="month" id="month">
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

                        <div class="col-lg col-md-3 mb-4">
                            <button type="submit"
                                    class="btn btn-block btn-success">{{ __('index.filter') }}</button>
                        </div>

                        @can('attendance_csv_export')
                            <div class="col-lg col-md-3 mb-4">
                                <button type="button" id="download-excel"
                                        data-href="{{ route('admin.attendances.show', $userDetail->id) }}"
                                        class="btn btn-block btn-secondary">
                                    {{ __('index.csv_export') }}
                                </button>
                            </div>
                        @endcan

                        <div class="col-lg col-md-3 mb-4">
                            <a class="btn btn-block btn-primary"
                            href="{{ route('admin.attendances.show', $userDetail->id) }}">{{ __('index.reset') }}</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.total_days_in_month') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalDays']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.present_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalPresent']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.absent_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalAbsent']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.weekend_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalWeekend']) : 0 }}</h5>
                    </div>
                </div>
            </div>

            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.holiday_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalHoliday']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.leave_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalLeave']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.working_hours') }}</h6>
                        <h6 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? $attendanceSummary['totalWorkingHours'] : '-' }}</h6>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.worked_hours') }}</h6>
                        <h6 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? $attendanceSummary['totalWorkedHours'] : '-' }}</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_details_of', ['month' => $monthName]) }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>{{ __('index.date') }}</th>
                            <th style="text-align: center;">{{ __('index.check_in_at') }}</th>
                            <th style="text-align: center;">{{ __('index.check_out_at') }}</th>
                            <th style="text-align: center;">{{ __('index.worked_hour') }}</th>
                            <th style="text-align: center;">{{ __('index.status') }}</th>
                            <th style="text-align: center;">{{ __('index.shift') }}</th>
                            @can('attendance_update')
                                <th style="text-align: center;">{{ __('index.action') }}</th>
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

                            @endphp
                        <tbody>
                            @if(isset($dayData['data']) && count($dayData['data']) > 0)
                                @foreach($dayData['data'] as $attendance)

                                    @php
                                        $totalMinutes += $attendance['worked_hour'];
                                    @endphp
                                    <tr>

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
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($attendance['check_in_type']).' '.__('index.checkin') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_in_latitude'].','.$attendance['check_in_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'}}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{  \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkin']) }}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                                @if(isset($attendance['night_checkout']))
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($attendance['check_out_type']).' '.__('index.checkout') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_out_latitude'].','.$attendance['check_out_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{ \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkout'])}}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                            @else
                                                @if(isset($attendance['check_in_at']))
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($attendance['check_in_type']).' '.__('index.checkin') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_in_latitude'].','.$attendance['check_in_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'}}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{  \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_in_at']) }}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                                @if(isset($attendance['check_out_at']))
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($attendance['check_out_type']).' '.__('index.checkout') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_out_latitude'].','.$attendance['check_out_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{  \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting,  $attendance['check_out_at']) }}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                            @endif
                                        <td  class="text-center">
                                            {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['worked_hour']) }}
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
                                                @else
                                                    <div class="d-inline-flex flex-column align-items-center gap-2">
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
                                                                Quick Leave
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
                                                <td class="text-center">

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
                                        <th></th>
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
                                        <th></th>
                                        <th></th>
                                        <th></th>

                                    </tr>
                                @endif
                            @else
                                <tr>
                                    <td>{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}</td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    @php
                                        $reason = (\App\Helpers\AttendanceHelper::getHolidayOrLeaveDetail($dayData['attendance_date'], $userDetail->id));
                                    @endphp
                                    <td class="text-center">
                                        @if($leaveRequest)
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
                                        @elseif($reason)
                                            <div class="d-inline-flex flex-column align-items-center gap-2">
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
                                                            Quick Leave
                                                        </a>
                                                    @endcan
                                                @endif
                                            </div>
                                        @else
                                            <div class="d-inline-flex flex-column align-items-center gap-2">
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
                                                        Quick Leave
                                                    </a>
                                                @endcan
                                            </div>
                                        @endif
                                    </td>
                                    <td  class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td  class="text-center">
                                        @if(!$leaveRequest && isset($reason) && $reason == 'Absent')
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
                        <h5 class="modal-title">{{ __('index.leave_request_section') }}</h5>
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

    </section>
@endsection

@section('scripts')
    @include('admin.attendance.common.scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const attendanceQuickLeaveModalElement = document.getElementById('attendanceQuickLeaveModal');
            const attendanceQuickLeaveModal = attendanceQuickLeaveModalElement ? new bootstrap.Modal(attendanceQuickLeaveModalElement) : null;
            const attendanceQuickLeaveUserId = document.getElementById('attendanceQuickLeaveUserId');
            const attendanceQuickLeaveDate = document.getElementById('attendanceQuickLeaveDate');
            const attendanceQuickLeaveType = document.getElementById('attendanceQuickLeaveType');
            const attendanceQuickLeaveReason = document.getElementById('attendanceQuickLeaveReason');
            const attendanceQuickLeaveSubmit = document.getElementById('attendanceQuickLeaveSubmit');
            const attendanceQuickLeaveLabel = document.getElementById('attendanceQuickLeaveModalLabel');
            const attendanceQuickLeaveHelpText = document.getElementById('attendanceQuickLeaveHelpText');

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
        });
    </script>
@endsection
