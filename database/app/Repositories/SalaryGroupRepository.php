<?php

namespace App\Repositories;

use App\Models\SalaryGroup;

class SalaryGroupRepository
{
    const IS_ACTIVE = 1;

    public function getAllSalaryGroupLists($select=['*'],$with=[])
    {
        return SalaryGroup::select($select)
            ->withCount('groupEmployees')
            ->with($with)
            ->latest()
            ->get();
    }

    public function findSalaryGroupDetailById($groupId,$select=['*'],$with=[])
    {
        return SalaryGroup::select($select)
            ->with($with)
            ->where('id',$groupId)
            ->first();
    }

    public function pluckActiveSalaryGroup($select)
    {
        return SalaryGroup::where('is_active',self::IS_ACTIVE)
            ->pluck($select)
            ->toArray();
    }

    public function store($validatedData)
    {
        return SalaryGroup::create($validatedData)->fresh();
    }

    public function update($salaryGroupDetail,$validatedData)
    {
         $salaryGroupDetail->update($validatedData);
         return $salaryGroupDetail->fresh();
    }

    public function toggleIsActiveStatus($salaryGroupDetail)
    {
        return $salaryGroupDetail->update([
            'is_active' => !$salaryGroupDetail->is_active
        ]);
    }

    public function delete($salaryGroupDetail)
    {
        return $salaryGroupDetail->delete();
    }

    public function attachComponentToGroup($salaryGroup, array $componentIds)
    {
        return $salaryGroup->salaryComponents()->attach($componentIds);
    }

    public function syncSalaryComponentToSalaryGroup($salaryGroup, $componentIds)
    {
        return $salaryGroup->salaryComponents()->sync($componentIds);
    }

    public function detachSalaryComponentFromSalaryGroup($salaryGroup,$componentIds)
    {
        return $salaryGroup->salaryComponents()->detach($componentIds);
    }


}
