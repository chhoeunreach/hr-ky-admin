<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\AdvanceSalary;
use Carbon\Carbon;

class AdvanceSalaryRepository
{
    public function getAllAdvanceSalaryRequestLists($filterParameters,$select=['*'],$with=[])
    {
        if( AppHelper::ifDateInBsEnabled())
        {
            $currentNepaliYearMonth = AppHelper::getCurrentYearMonth();
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($currentNepaliYearMonth['year'], $filterParameters['month']);

            $startDate = date('Y-m-d',strtotime($dateInAD['start_date'])) ?? null;
            $endDate = date('Y-m-d',strtotime($dateInAD['end_date'])) ?? null;
        }else{
            $firstDayOfMonth  = Carbon::create(date('Y'), $filterParameters['month'], 1)->startOfDay();
            $startDate = date('Y-m-d',strtotime($firstDayOfMonth));
            $endDate = date('Y-m-d',strtotime($firstDayOfMonth->endOfMonth()));
        }


        return  AdvanceSalary::query()->select($select)->with($with)
            ->when(isset($filterParameters['status']), function ($query) use ($filterParameters) {
                $query->where('status', $filterParameters['status']);
            })
            ->when(isset($filterParameters['employee']), function ($query) use ($filterParameters) {
                $query->whereHas('requestedBy',function($subQuery) use ($filterParameters){
                    $subQuery->where('name', 'like', '%' . $filterParameters['employee'] . '%');
                });
            })
            ->when(isset($filterParameters['month']), function ($query) use ($startDate, $endDate) {
                $query->whereDate('advance_requested_date', '>=', $startDate)
                    ->whereDate('advance_requested_date', '<=', $endDate);
            })

            ->latest()
            ->paginate(AdvanceSalary::RECORDS_PER_PAGE);
    }

    public function getAllEmployeeAdvanceSalaryRequestLists($employeeId,$select,$with)
    {
        return AdvanceSalary::select($select)->with($with)
            ->where('employee_id',$employeeId)
            ->get();
    }

    public function store($validated)
    {
        return AdvanceSalary::create($validated)->fresh();
    }


    public function findEmployeeAdvanceSalaryDetailByIdAndEmployeeId($id,$select,$with)
    {
        return AdvanceSalary::select($select)
            ->with($with)
            ->where('employee_id',getAuthUserCode())
            ->where('id',$id)
            ->first();
    }

    public function findAdvanceSalaryDetailById($id,$select,$with)
    {
        return AdvanceSalary::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function checkIfEmployeeUnsettledAdvanceSalaryRequestExists($employeeId)
    {
        return AdvanceSalary::where('employee_id', $employeeId)
            ->where('is_settled', false)
            ->exists();;
    }

    public function update($advanceSalaryDetail, $validatedData)
    {
        $advanceSalaryDetail->update($validatedData);
        return $advanceSalaryDetail->fresh();
    }

    public function delete($advanceSalaryDetail)
    {
        return $advanceSalaryDetail->delete();
    }

    public function createManyAttachment(AdvanceSalary $advanceSalaryDetail,$attachments)
    {
        return $advanceSalaryDetail->attachments()->createMany($attachments);
    }

    public function changeAdvanceSalaryStatus($salaryAdvanceDetail, $validatedData)
    {
        return $salaryAdvanceDetail->update([
            'status' => $validatedData['status'],
            'remark' => $validatedData['remark'],
            'verified_by' => getAuthUserCode()
        ]);
    }

    public function settlement($employeeId, $updateData)
    {
        return AdvanceSalary::where('employee_id', $employeeId)->where('is_settled',0)->where('status','=','approved')->update($updateData);
    }


}
