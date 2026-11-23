<?php

namespace App\Resources\Asset;

use App\Enum\LeaveStatusEnum;
use App\Helpers\AppHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class AssetResources extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'asset' => $this->asset?->name,
            'assigned_date' => AppHelper::formatDateForView($this->assigned_date),
            'returned_date' => isset($this->returned_date) ? AppHelper::formatDateForView($this->returned_date) : null,
            'status' => ucfirst($this->status),
            'return_condition' => isset($this->return_condition) ? ucfirst($this->return_condition) : null,
            'notes' => $this->notes ?? null,
            'sim_cards' => $this->asset?->simCards?->map(function ($sim) {
                return [
                    'id' => $sim->id,
                    'phone_number' => $sim->phone_number,
                    'operator' => $sim->operator,
                    'sim_slot' => $sim->sim_slot,
                    'purpose' => $sim->purpose,
                    'assigned_to' => $sim->employee?->name,
                ];
            }) ?? [],
        ];
    }
}













