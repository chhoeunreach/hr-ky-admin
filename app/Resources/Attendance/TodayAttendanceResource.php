<?php

namespace App\Resources\Attendance;


use App\Helpers\AttendanceHelper;

use Illuminate\Http\Resources\Json\JsonResource;

class TodayAttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        $time =  $this->check_out_at ?  $this->check_out_at :\Carbon\Carbon::now() ;
        $latestLatitude = $this->check_out_at ? $this->check_out_latitude : $this->check_in_latitude;
        $latestLongitude = $this->check_out_at ? $this->check_out_longitude : $this->check_in_longitude;

        return [
            'check_in_at' => isset($this->check_in_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($this->check_in_at) : '-',
            'check_out_at' => isset($this->check_out_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($this->check_out_at) : '-',
            'productive_time_in_min' => \Carbon\Carbon::createFromFormat('H:i:s', $this->check_in_at)->diffInMinutes($time),
            'check_in_location' => [
                'latitude' => $this->check_in_latitude,
                'longitude' => $this->check_in_longitude,
            ],
            'check_out_location' => [
                'latitude' => $this->check_out_latitude,
                'longitude' => $this->check_out_longitude,
            ],
            'latest_location' => [
                'latitude' => $latestLatitude,
                'longitude' => $latestLongitude,
            ],
        ];
    }
}











