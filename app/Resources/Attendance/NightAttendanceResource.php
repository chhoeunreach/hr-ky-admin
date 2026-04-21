<?php

namespace App\Resources\Attendance;


use App\Helpers\AttendanceHelper;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NightAttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        $time = $this->night_checkout ?? \Carbon\Carbon::now();

        $checkInTime = Carbon::parse($this->night_checkin);
        $checkOutTime = Carbon::parse($time);


        $productiveTimeInMin = $checkOutTime->diffInMinutes($checkInTime);

        return [
            'check_in_at' => isset($this->night_checkin) ? AttendanceHelper::changeNightTimeFormatForAttendanceView($this->night_checkin) : '-',
            'check_out_at' => isset($this->night_checkout) ? AttendanceHelper::changeNightTimeFormatForAttendanceView($this->night_checkout) : '-',
            'productive_time_in_min' => $productiveTimeInMin,
            'check_in_location' => [
                'latitude' => $this->check_in_latitude,
                'longitude' => $this->check_in_longitude,
            ],
            'check_out_location' => [
                'latitude' => $this->check_out_latitude,
                'longitude' => $this->check_out_longitude,
            ],
            'latest_location' => [
                'latitude' => $this->night_checkout ? $this->check_out_latitude : $this->check_in_latitude,
                'longitude' => $this->night_checkout ? $this->check_out_longitude : $this->check_in_longitude,
            ],
        ];
    }
}











