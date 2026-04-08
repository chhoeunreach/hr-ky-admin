<?php

namespace App\Resources\Leave;


use App\Helpers\AppHelper;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeTimeLeaveDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'leave_id' => 0,
            'user_id' => $this->user_id,
            'user_name' => ucfirst($this->name),
            'user_avatar' => asset(User::AVATAR_UPLOAD_PATH.$this->avatar),
            'department' => ucfirst($this->department),
            'post' => ucfirst($this->post),
            'leave_days' => 1,
            'leave_from' =>  date('H:i A', strtotime($this->leave_from)),
            'leave_to' => date('H:i A', strtotime($this->leave_to)),
            'leave_status' => ucfirst($this->leave_status),
        ];
    }
}














