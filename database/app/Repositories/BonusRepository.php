<?php

namespace App\Repositories;

use App\Models\Bonus;

class BonusRepository
{
    public function getAll($select=['*'],$with=[])
    {
        return Bonus::with($with)->select($select)->get();
    }

    public function find($id,$select=['*'])
    {
        return Bonus::select($select)
            ->where('id',$id)
            ->first();
    }

    public function findByMonth($month, $select=['*'])
    {
        return Bonus::select($select)
            ->where('applicable_month',$month)
            ->where('is_active',1)
            ->first();
    }

    public function store($validatedData)
    {
        return Bonus::create($validatedData)->fresh();
    }

    public function toggleStatus($bonusDetail)
    {
        return $bonusDetail->update([
            'is_active' => !$bonusDetail->is_active
        ]);
    }

    public function update($bonusDetail, $validatedData)
    {
         $bonusDetail->update($validatedData);
         return $bonusDetail->fresh();
    }


    public function delete($bonusDetail)
    {
        return $bonusDetail->delete();
    }

    public function pluckAllBonusLists()
    {
        return Bonus::where('is_active',1)
            ->get();
    }


}
