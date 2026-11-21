<?php

namespace App\Repositories;

use App\Models\Feature;

class FeatureRepository
{

    public function getAllFeatures($select=['*'])
    {
        return Feature::select($select)->orderBy('group','desc')->orderBy('name')->get();
    }
    public function findFeatureByKey($key)
    {
        return Feature::where('key',$key)->first();
    }
}
