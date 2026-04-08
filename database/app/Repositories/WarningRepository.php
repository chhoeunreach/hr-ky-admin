<?php

namespace App\Repositories;

use App\Models\Warning;
use App\Traits\ImageService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WarningRepository
{
    use ImageService;

    public function getAllWarningPaginated($select=['*'],$with=[])
    {
        $branchId = auth()->user()?->branch_id;
        $authUserId = auth()->user()?->id;

        return Warning::with($with)->select($select)
            ->when(isset($branchId) && $authUserId != 1, function($query) use ($branchId){
                $query->where('branch_id', $branchId);
            })
            ->latest()
            ->paginate(Warning::RECORDS_PER_PAGE);
    }

    public function getEmployeeWarningPaginated($perPage, $select = ['*'], $with = [])
    {
        $authUserCode = getAuthUserCode();


        return Warning::select($select)
            ->with($with)
            ->where(function ($query) use ($authUserCode) {
                $query->whereHas('warningEmployee', function ($employeeQuery) use ($authUserCode) {
                    $employeeQuery->where('employee_id', $authUserCode);
                });
            })->paginate($perPage);
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Warning::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }


    public function store($validatedData)
    {
        return Warning::create($validatedData)->fresh();
    }

    public function update($warningDetail,$validatedData)
    {
        $validatedData['updated_by'] = auth()->user()->id;

        return $warningDetail->update($validatedData);
    }


    public function delete($warningDetail)
    {
        $warningDetail->warningEmployee()->delete();
        $warningDetail->warningDepartment()->delete();
        $warningDetail->warningReply()->delete();
        return $warningDetail->delete();
    }


    public function saveEmployee(Warning $warningDetail,$userArray)
    {
        return $warningDetail->warningEmployee()->createMany($userArray);
    }

    public function updateEmployee(Warning $warningDetail,$userArray)
    {
        $warningDetail->warningEmployee()->delete();
        return $warningDetail->warningEmployee()->createMany($userArray);
    }
    public function saveDepartment(Warning $warningDetail,$departmentArray)
    {
        return $warningDetail->warningDepartment()->createMany($departmentArray);
    }

    public function updateDepartment(Warning $warningDetail,$departmentArray)
    {
        $warningDetail->warningDepartment()->delete();
        return $warningDetail->warningDepartment()->createMany($departmentArray);
    }

    public function saveResponse(Warning $warningDetail,$responseArray)
    {

        return $warningDetail->warningReply()->create($responseArray);
    }

    public function updateResponse(Warning $warningDetail,$responseArray)
    {
        $warningDetail->warningReply()->delete();
        return $warningDetail->warningReply()->create($responseArray);
    }

}
