<?php

namespace App\Repositories;

use App\Models\AppSetting;

class AppSettingRepository
{

    public function getAllAppSettings($select=['*'])
    {
        return AppSetting::select($select)->latest()->get();
    }

    public function findAppSettingDetailById($id,$select=['*'])
    {
        return AppSetting::select($select)->where('id',$id)->first();
    }

    public function findAppSettingDetailBySlug($slug)
    {
        return AppSetting::where('slug',$slug)->first();
    }

    public function toggleStatus($id)
    {
        $appSettings = $this->findAppSettingDetailById($id);
        return $appSettings->update([
            'status' => !$appSettings->status,
        ]);
    }

    public function toggleTheme($themeDetail)
    {
        return $themeDetail->update([
            'status' => !$themeDetail->status,
        ]);
    }
}
