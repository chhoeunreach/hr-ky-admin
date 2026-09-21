<?php

namespace App\Http\Resources\RepairPrice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepairCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_kh' => $this->name_kh,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'status' => (bool) $this->status,
        ];
    }
}
