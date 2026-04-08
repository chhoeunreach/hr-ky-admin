<?php

namespace App\Repositories;

use App\Models\SalaryComponent;

class SalaryComponentRepository
{
    public function getAllSalaryComponentLists($select=['*'],$with=[])
    {
        return SalaryComponent::with($with)->select($select)->get();
    }

    public function getGeneralSalaryComponentList($select=['*'])
    {
        return SalaryComponent::select($select)->where('apply_for_all',1)->get();
    }

    public function findDetailById($id,$select=['*'])
    {
        return SalaryComponent::select($select)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        return SalaryComponent::create($validatedData)->fresh();
    }

    public function toggleStatus($salaryComponentDetail)
    {
        return $salaryComponentDetail->update([
            'status' => !$salaryComponentDetail->status
        ]);
    }

    public function update($salaryComponentDetail, $validatedData)
    {
         $salaryComponentDetail->update($validatedData);
         return $salaryComponentDetail->fresh();
    }


    public function delete($salaryComponentDetail)
    {
        return $salaryComponentDetail->delete();
    }

    public function pluckAllSalaryComponentLists()
    {
        return SalaryComponent::active()
            ->where('apply_for_all','=',0)
            ->pluck('name','id')
            ->toArray();
    }


}
