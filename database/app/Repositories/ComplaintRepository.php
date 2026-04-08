<?php

namespace App\Repositories;


use App\Models\Complaint;
use App\Traits\ImageService;

class ComplaintRepository
{
    use ImageService;

    public function getAllComplaintPaginated($select=['*'],$with=[])
    {
        $branchId = auth()->user()?->branch_id;
        $authUserId = auth()->user()?->id;

        return Complaint::with($with)->select($select)
            ->when(isset($branchId) && $authUserId != 1, function($query) use ($branchId){
                $query->where('branch_id', $branchId);
            })
            ->latest()
            ->paginate(Complaint::RECORDS_PER_PAGE);
    }

    public function getEmployeeComplaintPaginated($perPage, $select = ['*'], $with = [])
    {
        $authUserCode = getAuthUserCode();


        return Complaint::select($select)
            ->with($with)
            ->where(function ($query) use ($authUserCode) {
                $query->whereHas('complaintEmployee', function ($employeeQuery) use ($authUserCode) {
                    $employeeQuery->where('employee_id', $authUserCode);
                });
            })->paginate($perPage);
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Complaint::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }


    public function store($validatedData)
    {
        if(isset($validatedData['image'])){
            $validatedData['image'] = $this->storeImage($validatedData['image'], Complaint::UPLOAD_PATH, 500, 250);
        }
        return Complaint::create($validatedData)->fresh();
    }

    public function update($complaintDetail,$validatedData)
    {
        $validatedData['updated_by'] = auth()->user()->id;

        if (isset($validatedData['image'])) {
            if ($complaintDetail['image']) {
                $this->removeImage(Complaint::UPLOAD_PATH, $complaintDetail['image']);
            }
            $validatedData['image'] = $this->storeImage($validatedData['image'], Complaint::UPLOAD_PATH, 500, 250);
        }
        return $complaintDetail->update($validatedData);
    }


    public function delete($complaintDetail)
    {
        if ($complaintDetail['image']) {
            $this->removeImage(Complaint::UPLOAD_PATH, $complaintDetail['image']);
        }
        $complaintDetail->complaintEmployee()->delete();
        $complaintDetail->complaintDepartment()->delete();
        $complaintDetail->complaintReply()->delete();
        return $complaintDetail->delete();
    }


    public function saveEmployee(Complaint $complaintDetail,$userArray)
    {
        return $complaintDetail->complaintEmployee()->createMany($userArray);
    }

    public function updateEmployee(Complaint $complaintDetail,$userArray)
    {
        $complaintDetail->complaintEmployee()->delete();
        return $complaintDetail->complaintEmployee()->createMany($userArray);
    }
    public function saveDepartment(Complaint $complaintDetail,$departmentArray)
    {
        return $complaintDetail->complaintDepartment()->createMany($departmentArray);
    }

    public function updateDepartment(Complaint $complaintDetail,$departmentArray)
    {
        $complaintDetail->complaintDepartment()->delete();
        return $complaintDetail->complaintDepartment()->createMany($departmentArray);
    }

    public function saveResponse(Complaint $complaintDetail,$responseArray)
    {

        return $complaintDetail->complaintReply()->create($responseArray);
    }

    public function updateResponse(Complaint $complaintDetail,$responseArray)
    {
        $complaintDetail->complaintReply()->delete();
        return $complaintDetail->complaintReply()->create($responseArray);
    }

}
