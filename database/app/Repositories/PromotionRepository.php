<?php

namespace App\Repositories;

use App\Models\Promotion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PromotionRepository
{

    public function getAllPromotionPaginated($select=['*'],$with=[])
    {
        $branchId = auth()->user()?->branch_id;
        $authUserId = auth()->user()?->id;

        return Promotion::with($with)->select($select)
            ->when(isset($branchId) && $authUserId != 1, function($query) use ($branchId){
                $query->where('branch_id', $branchId);
            })
            ->latest()
            ->paginate(Promotion::RECORDS_PER_PAGE);
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Promotion::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }


    public function store($validatedData)
    {
        return Promotion::create($validatedData)->fresh();
    }

    public function update($promotionDetail,$validatedData)
    {
        $validatedData['updated_by'] = auth()->user()->id;

        return $promotionDetail->update($validatedData);
    }


    public function delete($promotionDetail)
    {
        return $promotionDetail->delete();
    }




}
