<table>
    <thead>
    <tr>
        <th colspan="5" style="text-align: center">
            <strong>
                @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                    {{ \App\Helpers\AppHelper::getFormattedNepaliDate($dayDetail['attendance_date']) }}
                @else
                    {{ date('M d Y', strtotime($dayDetail['attendance_date'])) }}
                @endif
                {{ __('index.attendance_report') }}
            </strong>
        </th>
    </tr>
    <tr>
        <th><b>{{ __('index.employee_name') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_in_at') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_out_at') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.total_worked_hours') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.attendance_status') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.shift') }}</b></th>
    </tr>
    </thead>
    @forelse($attendanceDayWiseRecord->groupBy('user_id')  as $userId => $userAttendances)
        @php
            $totalMinutes = 0;
            $isFirstIteration = true;
            $firstAttendance = $userAttendances->first()
        @endphp
        <tbody>
            @if(count($userAttendances) > 0)
                @foreach($userAttendances as $attendance)

                    @php
                        $totalMinutes += $attendance['worked_hour'];
                    @endphp
                    <tr>

                        @if($isFirstIteration)
                            <td>{{ $attendance['user_name'] }}</td>
                            @php
                                $isFirstIteration = false;
                            @endphp
                        @else
                            <td></td>
                        @endif
                        @if(isset($attendance['check_in_at']))
                            <td class="text-center">
                                {{ $attendance['check_in_at'] ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_in_at']) : '' }}
                            </td>
                        @elseif(isset($attendance['night_checkin']))
                                <td class="text-center">
                                    {{ $attendance['night_checkin'] ? \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkin']) : '' }}
                                </td>
                        @else

                            <td></td>
                        @endif
                        @if($attendance['check_out_at'])
                            <td class="text-center">
                                {{ $attendance['check_out_at'] ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting,  $attendance['check_out_at']) : '' }}
                            </td>
                        @elseif(isset($attendance['night_checkout']))
                            <td class="text-center">
                                {{ $attendance['night_checkout'] ? \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkout']) : '' }}
                            </td>
                        @else
                            <td></td>
                        @endif
                        <td  class="text-center">
                            {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['worked_hour']) }}
                        </td>

                            @if(!is_null($attendance['attendance_status']))
                                <td class="text-center">
                                    {{ $attendance['attendance_status'] == \App\Models\Attendance::ATTENDANCE_APPROVED ? __('index.approved') : __('index.rejected') }}
                                </td>
                            @else
                                <td class="text-center">
                                    {{ __('index.pending') }}
                                </td>
                            @endif

                        <td>
                            {{ ucfirst($attendance['shift']) ?? 'N/A' }}
                        </td>
                    </tr>
                @endforeach

                @if($multipleAttendance > 1 && count($userAttendances) > 1)
                    <tr class="bg-gray-100">
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

                    </tr>
                @endif
            @else
                <tr>
                    <td>{{ $firstAttendance->user_name  }}</td>
                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                    @php
                        $reason = (\App\Helpers\AttendanceHelper::getHolidayOrLeaveDetail($firstAttendance->attendance_date, $firstAttendance->user_id));
                    @endphp
                    @if($reason)
                        <td class="text-center">
                                    <span class="btn btn-outline-secondary btn-xs">
                                        {{ $reason }}
                                    </span>
                        </td>
                    @endif
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
        <tbody>
        @endforelse

</table>
