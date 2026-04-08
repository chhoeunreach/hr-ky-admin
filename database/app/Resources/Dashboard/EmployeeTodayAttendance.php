<?php

namespace App\Resources\Dashboard;


use App\Helpers\AttendanceHelper;
use App\Resources\Attendance\TodayAttendanceResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class EmployeeTodayAttendance extends JsonResource
{
    public function toArray($request)
    {
        $attendances = $this->employeeTodayAttendance;
        $productiveTimeInMin = 0;
        $checkInAt = '-';
        $checkOutAt = '-';
        foreach ($attendances as $key=>$attendance){
            if($key == 0){
                $checkInAt = isset($attendance->check_in_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance->check_in_at) : AttendanceHelper::changeNightTimeFormatForAttendanceView($attendance->night_checkin);
            }
            if(is_null($attendance->check_in_at)){
                $productiveTimeInMin += $this->calculateProductiveTime($attendance->night_checkin, $attendance->night_checkout);

            }else{
                $productiveTimeInMin += $this->calculateProductiveTime($attendance->check_in_at, $attendance->check_out_at);

            }

            $checkOutAt = isset($attendance->check_out_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance->check_out_at) : (isset($attendance->night_checkout) ? AttendanceHelper::changeNightTimeFormatForAttendanceView($attendance->night_checkout) : '-') ;
        }


        $data = [
            'check_in_at' => $checkInAt,
            'check_out_at' => $checkOutAt,
            'productive_time_in_min' => $productiveTimeInMin,
        ];

        return $data;
    }

    private function calculateProductiveTime($checkInAt, $checkOutAt)
    {
        if (!$checkInAt) {
            return 0;
        }

        $endTime = $checkOutAt ? Carbon::parse($checkOutAt) : Carbon::now();
        return Carbon::parse($checkInAt)->diffInMinutes($endTime);
    }

}











