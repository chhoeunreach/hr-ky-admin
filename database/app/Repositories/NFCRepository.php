<?php

namespace App\Repositories;

use App\Models\NfcAttendance;

class NFCRepository
{

    /**
     * @param array $select
     * @return mixed
     */
    public function getAll($identifier = ''): mixed
    {
        $authUserId = auth()->user()->id;
        $branchId = auth()->user()->branch_id;

        $nfcData = NfcAttendance::query()
            ->when(isset($branchId) && ($authUserId != 1), function ($query) use ($branchId) {
                $query->whereHas('createdBy',function($subQuery) use ($branchId){
                    $subQuery->where('branch_id', $branchId);
                });
            });
        if (!empty($identifier)) {
            $nfcData = $nfcData->where('identifier', $identifier)->first();
        } else {
            $nfcData = $nfcData->get();
        }
        return $nfcData;

    }

    /**
     * @param $validatedData
     * @return mixed
     */
    public function store($validatedData):mixed
    {
        return NfcAttendance::create($validatedData)->fresh();
    }


    /**
     * @param $id
     * @return mixed
     */
    public function findNFCDetailById($id):mixed
    {
        return NfcAttendance::find($id);
    }


    public function delete($nfcDetail): ?bool
    {
        return $nfcDetail->delete();
    }

}
