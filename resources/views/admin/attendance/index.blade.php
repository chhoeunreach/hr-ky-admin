@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', __('index.employee_attendance_lists'))


@section('main-content')

    <section class="content">
        <?php
        if($isBsEnabled){
            $currentDate = \App\Helpers\AppHelper::getCurrentDateInBS();

        }else{
            $currentDate = \App\Helpers\AppHelper::getCurrentDateInYmdFormat();
        }
        ?>

        @include('admin.section.flash_message')

        @include('admin.attendance.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_filter')  }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.attendances.index') }}" method="get">

                <div class="row align-items-center">

                    <div class="col-lg col-md-6 mb-4">
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
                    <div class="col-lg col-md-6 mb-4">
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
                    <div class="col-lg col-md-6 mb-4">
                        <select class="form-select " name="department_id" id="department_id">
                            <option selected disabled>{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="d-md-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-block btn-success mb-4">{{ __('index.filter') }}</button>
                            @can('attendance_csv_export')
                            <button type="button" id="download-daywise-attendance-excel"
                                    data-href="{{ route('admin.attendances.index') }}"
                                    class="btn btn-block btn-secondary mb-4">{{ __('index.csv_export') }}
                            </button>
                            @endcan
                            <a class="btn btn-block btn-primary me-0 mb-4" href="{{ route('admin.attendances.index') }}">{{ __('index.reset') }}</a>
                        </div>
                    </div>

                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_of_the_day') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                        <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                @can('attendance_show')
                                    <th></th>
                                @endcan
                                <th class="text-center">{{ __('index.employee_code') }}</th>
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
                                @canany(['attendance_create', 'attendance_update', 'attendance_delete'])
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
                               @endphp

                                @forelse($attendanceDetail->groupBy('user_id') as $userId => $userAttendances)

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

                                    @endphp

                                    <tr>
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

                                    <td class="text-center">
                                        {{ $firstAttendance->employee_code ?: 'N/A' }}
                                    </td>

                                    <td>
                                        @php
                                            $profileImage = $firstAttendance->avatar
                                                ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $firstAttendance->avatar)
                                                : asset('assets/images/img.png');
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="#"
                                               class="showProfilePhoto"
                                               data-src="{{ $profileImage }}"
                                               data-name="{{ ucfirst($firstAttendance->user_name) }}"
                                               title="{{ ucfirst($firstAttendance->user_name) }}">
                                                <img src="{{ $profileImage }}"
                                                 alt="{{ ucfirst($firstAttendance->user_name) }}"
                                                 class="rounded-circle"
                                                 style="width: 42px; height: 42px; object-fit: cover;">
                                            </a>
                                            <div>
                                                <div class="fw-semibold">{{ ucfirst($firstAttendance->user_name) }}</div>
                                                <small class="text-muted">{{ $firstAttendance->employee_code ?: 'N/A' }}</small>
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
                                                <span class="btn btn-light btn-xs disabled">
                                                    {{ __('index.pending') }}
                                                </span>
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

                                    @canany(['attendance_create','attendance_update'])
                                        @if($nightShift && $filterParameter['attendance_date'] ==  $currentDate)

                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">
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
            const noteModal = new bootstrap.Modal(document.getElementById('noteModal'));

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
        });
    </script>
@endsection
