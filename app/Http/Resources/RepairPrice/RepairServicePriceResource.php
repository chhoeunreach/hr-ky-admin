<?php

namespace App\Http\Resources\RepairPrice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class RepairServicePriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canViewCost = Gate::allows('repair_price.view_cost');
        $data = [
            'id' => $this->repair_service_id,
            'repair_price_id' => $this->id,
            'name' => $this->service?->name,
            'name_kh' => $this->service?->name_kh,
            'description' => $this->service?->description,
            'category' => $this->service?->category ? new RepairCategoryResource($this->service->category) : null,
            'part_name' => $this->part_name,
            'part_type' => $this->part_type,
            'selling_price' => (float) $this->selling_price,
            'warranty_days' => $this->warranty_days,
            'estimated_minutes' => $this->estimated_minutes,
            'note' => $this->note,
            'availability_status' => $this->availability_status,
            'can_view_cost' => $canViewCost,
        ];

        if ($canViewCost) {
            $data['part_cost'] = (float) $this->part_cost;
            $data['service_fee'] = (float) $this->service_fee;
            $data['profit'] = (float) $this->selling_price - (float) $this->part_cost - (float) $this->service_fee;
        }

        return $data;
    }
}
