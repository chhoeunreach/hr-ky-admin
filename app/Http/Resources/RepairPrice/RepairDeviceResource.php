<?php

namespace App\Http\Resources\RepairPrice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepairDeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'model_number' => $this->model_number,
            'image' => $this->image,
            'brand' => $this->whenLoaded('brand', fn () => new RepairBrandResource($this->brand)),
            'device_type' => $this->whenLoaded('deviceType', fn () => [
                'id' => $this->deviceType->id,
                'name' => $this->deviceType->name,
                'slug' => $this->deviceType->slug,
                'icon' => $this->deviceType->icon,
                'status' => (bool) $this->deviceType->status,
            ]),
            'series' => $this->whenLoaded('series', fn () => [
                'id' => $this->series?->id,
                'name' => $this->series?->name,
                'slug' => $this->series?->slug,
                'repair_brand_id' => $this->series?->repair_brand_id,
                'repair_device_type_id' => $this->series?->repair_device_type_id,
                'status' => (bool) $this->series?->status,
            ]),
        ];
    }
}
