<?php

namespace App\Repositories;

use App\Models\Tada;

class TadaRepository
{
    public function getAllTadaPaginated($filterParameters,$select,$with)
    {
        $branchId = auth()->user()->branch_id;
        $authUserId = auth()->user()->id;

       return Tada::query()->select($select)->with($with)
           ->when(isset($filterParameters['status']) || isset($filterParameters['employee']), function ($query) use ($filterParameters, $branchId) {
               if(isset($filterParameters['status'])){
                   $query->where('status', $filterParameters['status']);
               }
               if(isset($filterParameters['employee'])){
                   $query->whereHas('employeeDetail',function($subQuery) use ($filterParameters){
                       $subQuery->where('name', 'like', '%' . $filterParameters['employee'] . '%');

                   });
               }
           })
           ->when(isset($branchId) && $authUserId != 1, function ($query) use ($filterParameters, $branchId) {
               $query->whereHas('employeeDetail',function($subQuery) use ($filterParameters,$branchId){
                   $subQuery->where('branch_id', $branchId);
               });
           })

           ->latest()
           ->paginate(Tada::RECORDS_PER_PAGE);
    }

    public function getAllActiveTadaDetail($select,$with)
    {
        return Tada::select($select)->with($with)
            ->where('is_active',1)
            ->get();
    }

    public function findTadaDetailById($id,$select,$with)
    {
        return Tada::select($select)->with($with)
            ->where('id',$id)
            ->first();
    }

    public function findEmployeeTadaDetailByTadaId($id,$select,$with)
    {
        return Tada::select($select)->with($with)
            ->where('employee_id',getAuthUserCode())
            ->where('id',$id)
            ->first();
    }

    public function getEmployeeTadaDetailLists($employeeId,$select,$with)
    {
        return Tada::select($select)->with($with)
            ->where('employee_id',$employeeId)
            ->where('is_active',1)
            ->orderBy('created_at','desc')
            ->get();
    }

    public function getEmployeeUnsettledTadaLists($employeeId)
    {
        return Tada::where('employee_id',$employeeId)
            ->where('is_active',1)
            ->where('status','=','pending')
            ->sum('total_expense');
    }

    public function store($validatedData)
    {
        return Tada::create($validatedData)->fresh();
    }

    public function update($tadaDetail, $validatedData)
    {
        return $tadaDetail->update($validatedData);
    }

    public function delete($tadaDetail)
    {
        return $tadaDetail->delete();
    }

    public function toggleStatus($detail)
    {
        return $detail->update([
           'is_settled' => !$detail->is_settled
        ]);
    }

    public function createManyAttachment(Tada $tadaDetail,$attachments)
    {
        return $tadaDetail->attachments()->createMany($attachments);
    }

    public function deleteTadaAttachments(Tada $tadaDetail)
    {
        return $tadaDetail->attachments()->delete();
    }

    public function changeTadaStatus($tadaDetail, $validatedData)
    {
        return $tadaDetail->update([
           'status' => $validatedData['status'],
           'remark' => $validatedData['remark'],
           'verified_by' => getAuthUserCode()
        ]);
    }

    public function updateIsSettledStatus($tadaDetail)
    {
        return $tadaDetail->update([
           'is_settled' => true
        ]);
    }

    public function settleTada($updateData, $employeeId)
    {
        return Tada::where('employee_id', $employeeId)->where('status', 'pending')->update($updateData);
    }

}
