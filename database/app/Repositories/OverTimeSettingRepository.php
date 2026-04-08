<?php

namespace App\Repositories;

use App\Models\OverTimeSetting;

class OverTimeSettingRepository
{

    public function getAll($select=['*']):mixed
    {
        return OverTimeSetting::select($select)
            ->withCount('otEmployees')
            ->latest()
            ->get();
    }


    public function find($id, $with=[]):mixed
    {
        return OverTimeSetting::with($with)
            ->where('id',$id)
            ->first();

    }

    public function save($validatedData)
    {

        return OverTimeSetting::create($validatedData);
    }

    public function update($otData,$validatedData)
    {
        return $otData->update($validatedData);
    }

    public function delete($overtimeData)
    {
        return $overtimeData->delete();
    }

    public function toggleIsActiveStatus($overTimeDetail)
    {
        return $overTimeDetail->update([
            'is_active' => !$overTimeDetail->is_active
        ]);
    }

}
