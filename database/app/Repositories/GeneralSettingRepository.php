<?php

namespace App\Repositories;

use App\Models\GeneralSetting;

class GeneralSettingRepository
{
    public function getAllGeneralSettingDetails($select = ['*'])
    {
        return GeneralSetting::select($select)->where('key','!=', 'advance_salary_limit')->where('key','!=', 'firebase_key')->get();
    }

    public function findOrFailGeneralSettingDetailById($id,$select=['*'])
    {
        return GeneralSetting::select($select)->where('id',$id)->firstOrFail();
    }

    public function getGeneralSettingByType($type,$select=['*'])
    {
        return GeneralSetting::select($select)->where('type',$type)->get();
    }

    public function getGeneralSettingByKey($key,$select=['*'])
    {
        return GeneralSetting::select($select)->where('key',$key)->firstOrFail();
    }

    public function store($validatedData)
    {
        return GeneralSetting::create($validatedData)->fresh();
    }

    public function update($generalSettingDetail,$validatedData)
    {
        return $generalSettingDetail->update($validatedData);
    }

    public function delete($id)
    {
        $generalSettingDetail = $this->findOrFailGeneralSettingDetailById($id);
        return $generalSettingDetail->delete();
    }

}

