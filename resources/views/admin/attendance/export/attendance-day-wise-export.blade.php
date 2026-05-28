<table>
    <thead>
    <tr>
        

        <th style="text-align: center;"><b>ID</b></th>
        <th style="text-align: center;"><b>{{ __('index.date') }}</b></th>
        <th style="text-align: center;"><b>User</b></th>
        <th style="text-align: center;"><b>{{ __('index.employee_name') }}</b></th>
        <th style="text-align: center;"><b>Time In</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_in_at') }}</b></th>
        <th style="text-align: center;"><b>Time Out</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_out_at') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.total_worked_hours') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.attendance_status') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.leave') }}</b></th>
        <th style="text-align: center;"><b>Month-Year</b></th>
        <th style="text-align: center;"><b>Create By</b></th>
        <th style="text-align: center;"><b>Create At</b></th>
        <th style="text-align: center;"><b>Update By</b></th>
        <th style="text-align: center;"><b>Update At</b></th>
    </tr>
    </thead>
    <tbody>
    @forelse($attendanceDayWiseRecord->groupBy('user_id') as $userId => $userAttendances)
        @php
            $firstAttendance = $userAttendances->first();
            $lastAttendance = $userAttendances->last();
            $selectedAttendanceDate = $firstAttendance->attendance_date ?? $dayDetail['attendance_date'];
            $displayDate = $selectedAttendanceDate ? date('Y-m-d', strtotime($selectedAttendanceDate)) : '';
            $nightShift = ($firstAttendance->user_shift_type ?? $firstAttendance->shift) === \App\Enum\ShiftTypeEnum::night->value;

            $timeIn = isset($firstAttendance->office_opening_time)
                ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_opening_time)
                : '';
            $timeOut = isset($firstAttendance->office_closing_time)
                ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->office_closing_time)
                : '';

            if ($nightShift) {
                $checkInAt = isset($firstAttendance->night_checkin)
                    ? \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $firstAttendance->night_checkin)
                    : '';
                $checkOutAt = isset($lastAttendance->night_checkout)
                    ? \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $lastAttendance->night_checkout)
                    : '';
            } else {
                $checkInAt = isset($firstAttendance->check_in_at)
                    ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->check_in_at)
                    : '';
                $checkOutAt = isset($lastAttendance->check_out_at)
                    ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $lastAttendance->check_out_at)
                    : '';
            }

            $totalWorkedMinutes = $userAttendances->sum('worked_hour');
            $workedHourDisplay = ($multipleAttendance > 1 && !$nightShift)
                ? \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($totalWorkedMinutes)
                : \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($firstAttendance->worked_hour ?? 0);

            $attendanceStatus = !is_null($firstAttendance->attendance_status)
                ? ($firstAttendance->attendance_status == \App\Models\Attendance::ATTENDANCE_APPROVED
                    ? __('index.approved')
                    : __('index.rejected'))
                : __('index.pending');

            $leaveLabels = [];
            if ($firstAttendance->leave_request_id) {
                $leaveRequestType = $firstAttendance->leave_request_type
                    ? ucfirst($firstAttendance->leave_request_type)
                    : __('index.leave_request');
                $leaveLabels[] = $leaveRequestType . ' (' . ucfirst($firstAttendance->leave_request_status) . ')';
            }

            if ($firstAttendance->time_leave_id) {
                $leaveLabels[] = __('index.time_leave_request') . ' (' .
                    \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_start_time) .
                    ' - ' .
                    \App\Helpers\AppHelper::convertLeaveTimeFormat($firstAttendance->time_leave_end_time) .
                    ', ' . ucfirst($firstAttendance->time_leave_status) . ')';
            }

            $leaveStatus = implode(' | ', $leaveLabels);

            $employeeCode = $firstAttendance->employee_code ?? '';
            $recordId = $firstAttendance->uuid ?? '';
            $monthYear = date('m-Y', strtotime($selectedAttendanceDate));
            $createdBy = $firstAttendance->created_by ? \App\Helpers\AppHelper::findUserName($firstAttendance->created_by) : '';
            $updatedBy = $firstAttendance->updated_by ? \App\Helpers\AppHelper::findUserName($firstAttendance->updated_by) : '';
            $createdAt = $firstAttendance->created_at ? date('Y-m-d H:i:s', strtotime($firstAttendance->created_at)) : '';
            $updatedAt = $firstAttendance->updated_at ? date('Y-m-d H:i:s', strtotime($firstAttendance->updated_at)) : '';
        @endphp
        <tr>
            <td style="text-align: center;">{{ $displayDate . $firstAttendance->username }}</td>
            <td style="text-align: center;">{{ $displayDate }}</td>
            <td style="text-align: center;">{{ $firstAttendance->username }}</td>
            <td style="text-align: center;">{{ $firstAttendance->user_name }}</td>
            <td style="text-align: center;">{{ $timeIn }}</td>
            <td style="text-align: center;">{{ $checkInAt }}</td>
            <td style="text-align: center;">{{ $timeOut }}</td>
            <td style="text-align: center;">{{ $checkOutAt }}</td>
            <td style="text-align: center;">{{ $workedHourDisplay }}</td>
            <td style="text-align: center;">{{ $attendanceStatus }}</td>
            <td style="text-align: center;">{{ $leaveStatus }}</td>
            <td style="text-align: center;">{{ $monthYear }}</td>
            <td style="text-align: center;">{{ $createdBy }}</td>
            <td style="text-align: center;">{{ $createdAt }}</td>
            <td style="text-align: center;">{{ $updatedBy }}</td>
            <td style="text-align: center;">{{ $updatedAt }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="16" style="text-align: center;">
                <p><b>{{ __('index.no_records_found') }}</b></p>
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
