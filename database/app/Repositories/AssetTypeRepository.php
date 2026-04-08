<?php

namespace App\Repositories;

use App\Models\AssetType;

class AssetTypeRepository
{

    public function getAllAssetTypes($select=['*'],$with=[])
    {
        return AssetType::select($select)->withCount($with)->get();
    }

    public function getAllActiveAssetTypes($select=['*'],$with=[])
    {
        return AssetType::select($select)->with($with)->where('is_active',1)->get();
    }

    public function findAssetTypeById($id,$select=['*'],$with=[])
    {
        return AssetType::select($select)->with($with)->where('id',$id)->first();
    }

    public function create($validatedData)
    {
        return AssetType::create($validatedData)->fresh();
    }

    public function update($assetTypeDetail,$validatedData)
    {
        return $assetTypeDetail->update($validatedData);
    }

    public function delete($assetTypeDetail)
    {
        return $assetTypeDetail->delete();
    }

    public function toggleIsActiveStatus($assetTypeDetail)
    {
        return $assetTypeDetail->update([
            'is_active' => !$assetTypeDetail->is_active,
        ]);
    }
}
