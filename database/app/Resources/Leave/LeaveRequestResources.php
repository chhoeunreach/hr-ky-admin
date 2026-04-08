<?php

namespace App\Resources\Leave;

use App\Helpers\AppHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class LeaveRequestResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'no_of_days' => $this->no_of_days ?? 1,
            'leave_type_id' => $this->leave_type_id ?? 0,
            'leave_type_name' => ucfirst($this->leave_type_name ?? 'Time Leave'),
            'leave_from' => AppHelper::convertLeaveDateFormat($this->leave_from,true),
            'leave_to' => AppHelper::convertLeaveDateFormat($this->leave_to,true),
            'leave_requested_date' => AppHelper::convertLeaveDateFormat($this->leave_requested_date, true),
            'status' => ucfirst($this->status),
            'leave_reason' => $this->leave_reason,
            'admin_remark' => $this->admin_remark ?? '-',
            'early_exit' => $this->early_exit == 1,
            'status_updated_by' => isset($this->leaveRequestUpdatedBy) ? $this->leaveRequestUpdatedBy->name : '',

        ];
    }
}













