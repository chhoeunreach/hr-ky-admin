<?php

namespace App\Repositories;

use App\Enum\ResignationStatusEnum;
use App\Models\Resignation;
use App\Traits\ImageService;

class ResignationRepository
{
    use ImageService;

    public function getAllResignationPaginated($select=['*'],$with=[])
    {
        return Resignation::with($with)->select($select)
            ->latest()
            ->paginate(Resignation::RECORDS_PER_PAGE);
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Resignation::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        $validatedData['created_by']= auth()->user()->id;
        if(isset($validatedData['document'])){
            $validatedData['document'] = $this->storeImage($validatedData['document'], Resignation::UPLOAD_PATH, 500, 250);
        }
        return Resignation::create($validatedData)->fresh();
    }

    public function update($resignationDetail,$validatedData)
    {
        $validatedData['updated_by']= auth()->user()->id;
        if (isset($validatedData['document'])) {
            if ($resignationDetail['document']) {
                $this->removeImage(Resignation::UPLOAD_PATH, $resignationDetail['document']);
            }
            $validatedData['document'] = $this->storeImage($validatedData['document'], Resignation::UPLOAD_PATH, 500, 250);
        }
        return $resignationDetail->update($validatedData);
    }


    public function delete($resignationDetail)
    {
        if ($resignationDetail['document']) {
            $this->removeImage(Resignation::UPLOAD_PATH, $resignationDetail['document']);
        }
        return $resignationDetail->delete();
    }

    public function findByEmployeeId($employeeId,$select=['*'])
    {
        return Resignation::select($select)
            ->where('employee_id',$employeeId)
            ->orderBy('created_at','desc')
            ->first();
    }

}
