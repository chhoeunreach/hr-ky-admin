<table>
    <thead>
    <tr>
        <th colspan="5" style="text-align: center">
            <strong>{{ __('index.attendance_report') }} : {{ $userName }} </strong>
        </th>
    </tr>
    <tr>
        <th><b>{{ __('index.date') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_in_at') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_out_at') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.total_worked_hours') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.overtime') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.undertime') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.attendance_status') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.shift') }}</b></th>

    </tr>
    </thead>
    <tbody>
    @php
        $changeColor = [
            0 => 'danger',
            1 => 'success',
        ];

        $netTotalMinutes = 0;
        $netTotalOverTime = 0;
        $netTotalUnderTime = 0;
        $netTotalLeave = 0;
        $netTotalAbsent = 0;

    @endphp
    @forelse($attendanceData as $dayIndex => $dayData)
        @php
            $totalMinutes = 0;
            $isFirstIteration = true;
            $totalOverTime = 0;
            $totalUnderTime = 0;
        @endphp
        @if(isset($dayData['data']) && is_array($dayData['data']) && count($dayData['data']) > 0)
            @foreach($dayData['data'] as $attendance)
                @php
                    if(!is_null($attendance['worked_hour'])){

                        $totalMinutes += $attendance['worked_hour'];
                        $totalOverTime += $attendance['overtime'];
                        $netTotalOverTime += $attendance['overtime'];
                        $totalUnderTime += $attendance['undertime'];
                        $netTotalUnderTime += $attendance['undertime'];
                        $netTotalMinutes += $attendance['worked_hour'];
                    }
                @endphp
                <tr>
                    @if($isFirstIteration)
                        <td>{{ $dayIndex }}</td>
                        @php
                               $isFirstIteration = false;
                        @endphp
                    @else
                        <td></td>
                    @endif

                    @if(isset($attendance['check_in_at']))
                        <td class="text-center">


                            {{ \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_in_at']) }}
                        </td>
                    @elseif(isset($attendance['night_checkin']))

                        <td class="text-center">
                            {{ \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkin']) }}
                        </td>
                    @else
                        <td></td>
                    @endif

                    @if(isset($attendance['check_out_at']))

                        <td class="text-center">
                            {{ \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_out_at']) }}
                        </td>
                    @elseif(isset($attendance['night_checkout']))

                        <td class="text-center">
                            {{ \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkout']) }}
                        </td>
                    @else
                        <td></td>
                    @endif

                    <td class="text-center">

                        {{ !is_null($attendance['worked_hour']) ? \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['worked_hour']): ( isset($attendance['check_out_at']) ? \App\Helpers\AttendanceHelper::getWorkedHourInHourAndMinute($attendance['check_in_at'],$attendance['check_out_at']) : \App\Helpers\AttendanceHelper::getWorkedHourInHourAndMinute($attendance['night_checkin'],$attendance['night_checkout']) )  }}
                    </td>
                    <td  class="text-center">
                        {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['overtime']) }}
                    </td>
                    <td  class="text-center">
                        {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['undertime']) }}
                    </td>
                    <td></td>

                    <td>{{ isset($attendance['shift']) ? ucfirst($attendance['shift']) : 'N/A' }}</td>


                </tr>
            @endforeach

            @if($multipleAttendance > 1 && count($dayData['data']) > 1)
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
                    <th>{{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($totalOverTime) }} </th>
                    <th> {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($totalUnderTime) }}</th>
                    <th></th>
                    <th></th>
                </tr>
            @endif
        @else
            <tr>
                <td>{{ $dayIndex }}</td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                @php
                    $reason = (\App\Helpers\AttendanceHelper::getHolidayOrLeaveDetail($dayIndex, $userId));
                @endphp
                @if($reason)
                    @php
                        if($reason == 'Leave%'){
                            $netTotalLeave++;
                        }

                        if($reason == 'Absent'){
                            $netTotalAbsent++;
                        }
                    @endphp
                    <td class="text-center">
                        <span class="btn btn-outline-secondary btn-xs">
                            {{ $reason }}
                        </span>
                    </td>
                @endif
            </tr>
        @endif
    @empty
        <tr>
            <td colspan="6" class="text-center"><b>{{ __('index.no_records_found') }}</b></td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr>
        <th><b>{{ __('index.total') }}</b></th>
        <th></th>
        <th></th>
        <th  style="text-align: center;">{{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalMinutes) }}</th>
        <th style="text-align: center;"><b>{{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalOverTime) }}</b></th>
        <th style="text-align: center;"><b>{{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalUnderTime) }}</b></th>
        <th></th>
        <th></th>


    </tr>

    <tr></tr>
    <tr>
        <th> Remaks:</th>
    </tr>
    <tr>
        <th> Total Leave:</th>
        <td>{{ $netTotalLeave }}</td>
    </tr>
    <tr>
        <th> Total Absent:</th>
        <td>{{ $netTotalAbsent }}</td>
    </tr>

    </tfoot>
</table>
