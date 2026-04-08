<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Models\Project;
use App\Traits\ImageService;

class AssetRepository
{
    use ImageService;

    public function getAllAssetsPaginated($filterParameters,$select=['*'],$with=[])
    {
        return Asset::select($select)->with($with)
            ->when(isset($filterParameters['type']), function($query) use ($filterParameters){
                $query->whereHas('type',function($query) use ($filterParameters){
                    $query->where('id', $filterParameters['type']);
                });
            })
            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('name', 'like', '%' . $filterParameters['name'] . '%');
            })
            ->when(isset($filterParameters['is_working']), function ($query) use ($filterParameters) {
                $query->where('is_working', 'like', '%' . $filterParameters['is_working'] . '%');
            })
            ->when(isset($filterParameters['is_available']), function ($query) use ($filterParameters) {
                $query->where('is_available', $filterParameters['is_available']);
            })
            ->when(isset($filterParameters['purchased_from']), function($query) use ($filterParameters){
                $query->whereDate('purchased_date','>=',date('Y-m-d',strtotime($filterParameters['purchased_from'])));
            })
            ->when(isset($filterParameters['purchased_to']), function($query) use ($filterParameters){
                $query->whereDate('purchased_date','<=',date('Y-m-d',strtotime($filterParameters['purchased_to'])));
            })
            ->latest()
            ->paginate(Asset::RECORDS_PER_PAGE);
    }

    public function findAssetById($id,$select=['*'],$with=[])
    {
        return Asset::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        $validatedData['image'] = $this->storeImage($validatedData['image'], Asset::UPLOAD_PATH,500,500);
        return Asset::create($validatedData)->fresh();
    }

    public function update($assetDetail,$validatedData)
    {
        if (isset($validatedData['avatar'])) {
            if($assetDetail['image']){
                $this->removeImage(Asset::UPLOAD_PATH, $assetDetail['image']);
            }
            $validatedData['image'] = $this->storeImage($validatedData['image'], Asset::UPLOAD_PATH,500,500);
        }
        return $assetDetail->update($validatedData);
    }

    public function delete($assetDetail)
    {
        if($assetDetail['image']){
            $this->removeImage(Asset::UPLOAD_PATH, $assetDetail['image']);
        }
        return $assetDetail->delete();
    }

    public function changeIsAvailableStatus($assetDetail)
    {
        return $assetDetail->update([
            'is_available' => !$assetDetail->is_available
        ]);
    }

}
