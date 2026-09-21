<?php

namespace App\Http\Resources\RepairPrice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepairBrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'status' => (bool) $this->status,
        ];
    }
}
