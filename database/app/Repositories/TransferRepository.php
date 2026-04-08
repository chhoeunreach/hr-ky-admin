<?php

namespace App\Repositories;

use App\Models\Transfer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransferRepository
{

    public function getAllTransferPaginated($select=['*'],$with=[])
    {
        $branchId = auth()->user()?->branch_id;
        $authUserId = auth()->user()?->id;

        return Transfer::with($with)->select($select)
            ->when(isset($branchId) && $authUserId != 1, function($query) use ($branchId){
                $query->where('branch_id', $branchId);
            })
            ->latest()
            ->paginate(Transfer::RECORDS_PER_PAGE);
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Transfer::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }


    public function store($validatedData)
    {
        return Transfer::create($validatedData)->fresh();
    }

    public function update($transferDetail,$validatedData)
    {
        $validatedData['updated_by'] = auth()->user()->id;

        return $transferDetail->update($validatedData);
    }


    public function delete($transferDetail)
    {
        return $transferDetail->delete();
    }




}
