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
        <div class="search-box p-4 pb-0 bg-white rounded mb-3 box-shadow">
            <h5>{{ __('index.attendance_of') . ' ' .ucfirst($userDetail->name) }}</h5>
            <form class="forms-sample mb-4" action="{{ route('admin.attendances.show', $userDetail->id) }}"
                  method="get">
                <div class="row align-items-center mt-3">
                    <div class="col-lg-3 col-md-3 mb-4">
                        <input type="number" min="{{ $filterData['min_year'] }}"
                               max="{{ $filterData['max_year'] }}" step="1"
                               placeholder="{{ __('index.attendance_year_example', ['year' => $filterData['min_year']]) }}"
                               id="year"
                               name="year"
                               value="{{ $filterParameter['year'] }}"
                               class="form-control">
                    </div>

                    <div class="col-lg-3 col-md-3 mb-4">
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

                    <div class="col-lg-2 col-md-3 mb-4">
                        <button type="submit"
                                class="btn btn-block btn-success form-control">{{ __('index.filter') }}</button>
                    </div>

                    @can('attendance_csv_export')
                        <div class="col-lg-2 col-md-3 mb-4">
                            <button type="button" id="download-excel"
                                    data-href="{{ route('admin.attendances.show', $userDetail->id) }}"
                                    class="btn btn-block btn-secondary form-control">
                                {{ __('index.csv_export') }}
                            </button>
                        </div>
                    @endcan

                    <div class="col-lg-2 col-md-3 mb-4">
                        <a class="btn btn-block btn-primary form-control"
                           href="{{ route('admin.attendances.show', $userDetail->id) }}">{{ __('index.reset') }}</a>
                    </div>

                </div>
            </form>

        </div>

        <div class="row">
            <div class=" col-xl-3 col-md-3 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body text-md-start text-center">
                        <h6 class="card-title w-100">{{ __('index.total_days_in_month') }}</h6>
                        <h5 class="text-primary">{{ $attendanceSummary ? number_format($attendanceSummary['totalDays']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-3 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body text-md-start text-center">
                        <h6 class="card-title w-100">{{ __('index.present_days') }}</h6>
                        <h5 class="text-primary">{{ $attendanceSummary ? number_format($attendanceSummary['totalPresent']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-3 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body text-md-start text-center">
                        <h6 class="card-title w-100">{{ __('index.absent_days') }}</h6>
                        <h5 class="text-primary">{{ $attendanceSummary ? number_format($attendanceSummary['totalAbsent']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-3 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body text-md-start text-center">
                        <h6 class="card-title w-100">{{ __('index.weekend_days') }}</h6>
                        <h5 class="text-primary">{{ $attendanceSummary ? number_format($attendanceSummary['totalWeekend']) : 0 }}</h5>
                    </div>
                </div>
            </div>

            <div class=" col-xl-3 col-md-3 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body text-md-start text-center">
                        <h6 class="card-title w-100">{{ __('index.holiday_days') }}</h6>
                        <h5 class="text-primary">{{ $attendanceSummary ? number_format($attendanceSummary['totalHoliday']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-3 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body text-md-start text-center">
                        <h6 class="card-title w-100">{{ __('index.leave_days') }}</h6>
                        <h5 class="text-primary">{{ $attendanceSummary ? number_format($attendanceSummary['totalLeave']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-3 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body text-md-start text-center">
                        <h6 class="card-title w-100">{{ __('index.working_hours') }}</h6>
                        <h6 class="text-primary">{{ $attendanceSummary ? $attendanceSummary['totalWorkingHours'] : '-' }}</h6>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-3 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body text-md-start text-center">
                        <h6 class="card-title w-100">{{ __('index.worked_hours') }}</h6>
                        <h6 class="text-primary">{{ $attendanceSummary ? $attendanceSummary['totalWorkedHours'] : '-' }}</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <h4 class="fw-bold text-center mb-4">{{ __('index.attendance_details_of', ['month' => $monthName]) }}</h4>

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

                                @forelse($attendanceDetail as $dayIndex => $dayData)
                                    @php
                                        $totalMinutes = 0;
                                        $isFirstIteration = true;

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
                                                        <a class="changeAttendanceStatus btn btn-{{ $changeColor[$attendance['attendance_status']] }} btn-xs"
                                                           data-href="{{ route('admin.attendances.change-status', $attendance['id']) }}" title="{{ __('index.change_attendance_status') }}">
                                                            {{ ($attendance['attendance_status'] == \App\Models\Attendance::ATTENDANCE_APPROVED) ? __('index.approved') : __('index.rejected') }}
                                                        </a>
                                                    </td>
                                                @else
                                                    <td  class="text-center">
                                                        <span class="btn btn-light btn-xs disabled">
                                                            {{ __('index.pending') }}
                                                        </span>
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
                                            @if($reason)
                                                <td class="text-center">
                                                    <span class="btn btn-outline-secondary btn-xs">
                                                        {{ $reason }}
                                                    </span>
                                                </td>
                                            @endif
                                            <td  class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                            <td  class="text-center">
                                                @if(isset($reason) && $reason == 'Absent')
                                                    <a href=""
                                                       class="addEmployeeAttendance"
                                                       data-href="{{ route('admin.attendances.store') }}"
                                                       data-name="{{ ucfirst($userDetail->name) }}"
                                                       data-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
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

    </section>
@endsection

@section('scripts')
    @include('admin.attendance.common.scripts')
@endsection



{{-- <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                <th>{{ __('index.date') }}</th>
                                <th style="text-align: center;">{{ __('index.check_in_at') }}</th>
                                <th style="text-align: center;">{{ __('index.check_out_at') }}</th>
                                <th style="text-align: center;">{{ __('index.worked_hour') }}</th>
                                <th style="text-align: center;">{{ __('index.status') }}</th>
                                <th style="text-align: center;">{{ __('index.attendance_by') }}</th>
                                @can('attendance_update')
                                    <th style="text-align: center;">{{ __('index.action') }}</th>
                                @endcan
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                $changeColor = [
                                    0 => 'danger',
                                    1 => 'success',
                                ]
                                ?>
                            @forelse($attendanceDetail as $key => $value)
                                <tr>
                                    <td>{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $value['attendance_date']) }}</td>

                                    @if(isset($value['check_in_at']))
                                        @if($value['check_in_at'])
                                            <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $value['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($value['check_in_type']).' '.__('index.checkin') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{ $value['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? 'https://maps.google.com/maps?q='.$value['check_in_latitude'].','.$value['check_in_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed' : '' }}"
                                                      data-bs-target="{{ $value['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? '#addslider' : '' }}">
                                                    {{ $value['check_in_at'] ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $value['check_in_at']) : '' }}
                                                </span>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif

                                        @if($value['check_out_at'])
                                            <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="{{ $value['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($value['check_out_type']).' '.__('index.checkout') }}"
                                                      data-bs-toggle="modal"
                                                      data-href="{{ $value['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? 'https://maps.google.com/maps?q='.$value['check_out_latitude'].','.$value['check_out_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed' : '' }}"
                                                      data-bs-target="{{ $value['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? '#addslider' : '' }}">
                                                    {{ $value['check_out_at'] ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $value['check_out_at']) : '' }}
                                                </span>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif

                                        <td class="text-center">
                                            @if($value['check_out_at'])
                                                {{ \App\Helpers\AttendanceHelper::getWorkedHourInHourAndMinute($value['check_in_at'], $value['check_out_at']) }}
                                            @endif
                                        </td>

                                        @if(!is_null($value['attendance_status']))
                                            <td class="text-center">
                                                <a class="changeAttendanceStatus btn btn-{{ $changeColor[$value['attendance_status']] }} btn-xs"
                                                   data-href="{{ route('admin.attendances.change-status', $value['id']) }}" title="{{ __('index.change_attendance_status') }}">
                                                    {{ ($value['attendance_status'] == \App\Models\Attendance::ATTENDANCE_APPROVED) ? __('index.approved') : __('index.rejected') }}
                                                </a>
                                            </td>
                                        @else
                                            <td>
                                                <span class="btn btn-light btn-xs disabled">
                                                    {{ __('index.pending') }}
                                                </span>
                                            </td>
                                        @endif

                                        @if($value['created_by'])
                                            <td class="text-center">
                                                <span class="btn btn-warning btn-xs">
                                                    {{ ($value['user_id'] == $value['created_by']) ? __('index.self') : __('index.admin') }}
                                                </span>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                    @else
                                        <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                        <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                        <td class="text-center"><i class="link-icon" data-feather="x"></i></td>

                                            <?php
                                            $reason = (\App\Helpers\AttendanceHelper::getHolidayOrLeaveDetail($value['attendance_date'], $userDetail->id));
                                            ?>
                                        @if($reason)
                                            <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs">
                                                    {{ $reason }}
                                                </span>
                                            </td>
                                        @endif
                                        <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    @endif

                                    @can('attendance_update')
                                        <td class="text-center">
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                @if(isset($value['id']))
                                                    <li class="me-2">
                                                        <a href=""
                                                           class="editAttendance"
                                                           data-href="{{ route('admin.attendances.update', $value['id']) }}"
                                                           data-in="{{ date('H:i', strtotime($value['check_in_at'])) }}"
                                                           data-out="{{ $value['check_out_at'] ? date('H:i', strtotime($value['check_out_at'])) : null }}"
                                                           data-remark="{{ $value['edit_remark'] }}"
                                                           data-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $value['attendance_date']) }}"
                                                           data-name="{{ ucfirst($userDetail->name) }}"
                                                           title="{{ __('index.edit_attendance_time') }}">
                                                            <i class="link-icon" data-feather="edit"></i>
                                                        </a>
                                                    </li>
                                                @else
                                                    @if(isset($reason) && $reason == __('index.absent'))
                                                        <li class="me-2">
                                                            <a href=""
                                                               class="addEmployeeAttendance"
                                                               data-href="{{ route('admin.attendances.store') }}"
                                                               data-name="{{ ucfirst($userDetail->name) }}"
                                                               data-date="{{ date('Y-m-d', strtotime($value['attendance_date'])) }}"
                                                               data-user_id="{{ $userDetail->id }}"
                                                               title="{{ __('index.add_attendance_time') }}">
                                                                <i class="link-icon" data-feather="plus-circle"></i>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endif
                                            </ul>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%">
                                        <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table> --}}
