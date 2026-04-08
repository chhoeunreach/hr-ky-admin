<?php

namespace App\Resources\Holiday;
use App\Resources\Holiday\HolidayResource;
use Illuminate\Http\Resources\Json\ResourceCollection;


class HolidayCollection extends ResourceCollection
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return HolidayResource::collection($this->collection);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function with($request)
    {
        return [
            'status' => true,
            'code' => 200
        ];
    }

}




