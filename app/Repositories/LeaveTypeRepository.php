<?php

namespace App\Repositories;

use App\Enum\LeaveGenderEnum;
use App\Models\LeaveRequestMaster;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeaveTypeRepository
{
    public function getAllLeaveTypesWithLeaveTakenbyEmployee($filterParameters)
    {

        $authUserGender = auth()->user()->gender;
        return LeaveType::query()
            ->select(
                'leave_types.id as leave_type_id',
                'leave_types.name as leave_type_name',
                'leave_types.slug as leave_type_slug',
                'leave_types.is_active as leave_type_status',
                'leave_types.early_exit as early_exit',
                'leave_types.company_id as company_id',
                'leave_requests_master.status',
                'leave_requests_master.requested_by',
                DB::raw('COALESCE(employee_leave_types.days, leave_types.leave_allocated, 0) as total_leave_allocated'),
                DB::raw('IFNULL(sum(leave_requests_master.no_of_days),0) as leave_taken')
            )
            ->leftJoin('employee_leave_types', function ($join) {
                $join->on('leave_types.id', '=', 'employee_leave_types.leave_type_id')
                    ->where('employee_leave_types.employee_id', '=', getAuthUserCode())
                    ->where('employee_leave_types.is_active', '=', 1);
            })
            ->leftJoin('leave_requests_master', function ($join) use ($filterParameters) {
                $join->on("leave_types.id", "=", "leave_requests_master.leave_type_id")
                    ->where("leave_requests_master.requested_by", getAuthUserCode())
                    ->where("leave_requests_master.status", 'approved');
                if (isset($filterParameters['start_date'])) {
                    $join
                        ->whereBetween('leave_requests_master.leave_from', [$filterParameters['start_date'], $filterParameters['end_date']])
                        ->whereBetween('leave_requests_master.leave_to', [$filterParameters['start_date'], $filterParameters['end_date']]);
                } else {
                    $join
                        ->whereYear('leave_requests_master.leave_from', $filterParameters['year'])
                        ->whereYear('leave_requests_master.leave_to', $filterParameters['year']);
                }
            })
            ->when(isset($authUserGender), function ($query) use ($authUserGender) {
                $query->where('leave_types.gender', $authUserGender)
                ->orWhere('leave_types.gender', '=',LeaveGenderEnum::all->value);
            })
            ->groupBy(
                'leave_types.id',
                'leave_types.name',
                'leave_types.leave_allocated',
                'leave_types.slug',
                'leave_types.company_id',
                'leave_requests_master.status',
                'leave_requests_master.requested_by',
                'leave_types.is_active',
                'leave_types.early_exit',
            )
            ->orderBy('leave_types.id', 'ASC')
            ->get();
    }


    public function getAllLeaveTypes($filterParameters,$select = ['*'], $with = [])
    {
        $selectedColumns = $select === ['*'] ? ['leave_types.*'] : $select;

        return LeaveType::with($with)
            ->leftJoin('branches', 'branches.id', '=', 'leave_types.branch_id')
            ->select($selectedColumns)
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('leave_types.branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['type']), function($query) use ($filterParameters){
                $query->where('leave_types.name', 'like', '%' . $filterParameters['type'] . '%');
            })
            ->orderBy('branches.name')
            ->orderBy('leave_types.name')
            ->get();
    }

    public function exportLeaveTypes($select = ['*'], $with = [])
    {
        return LeaveType::with($with)
            ->select($select)
            ->get();
    }

    public function getAllActiveLeaveTypes($select=['*'])
    {
        return LeaveType::select($select)
            ->where('is_active',1)
            ->pluck('name','id')
            ->toArray();
    }
    public function getAllActiveLeaveTypeByBranch($branchId, $select=['*'])
    {
        return LeaveType::select($select)
            ->where('is_active',1)
            ->where('branch_id',$branchId)
            ->get();
    }

    public function getPaidLeaveTypes()
    {
        return LeaveType::whereNotNUll('leave_allocated')
            ->select('name','id')
            ->orderBy('id')
            ->get();
    }



    public function store($validatedData)
    {
        $validatedData['slug'] = Str::slug($validatedData['name']);
        return LeaveType::create($validatedData)->fresh();
    }

    public function update($leaveTypeDetail, $validatedData)
    {
        return $leaveTypeDetail->update($validatedData);
    }

    public function delete($leaveTypeDetail)
    {
        return $leaveTypeDetail->delete();
    }

    public function toggleStatus($id)
    {
        $leaveTypeDetail = $this->findLeaveTypeDetailById($id);
        return $leaveTypeDetail->update([
            'is_active' => !$leaveTypeDetail->is_active,
        ]);
    }

    public function findLeaveTypeDetailById($id, $select = ['*'])
    {
        return LeaveType::select($select)->where('id', $id)->firstorFail();
    }

    public function findLeaveTypeDetail($id, $employeeId)
    {
        return LeaveType::select(
            'leave_types.name',
            DB::raw('COALESCE(employee_leave_types.days, leave_types.leave_allocated, 0) as leave_allocated'),
            'leave_types.leave_allocated as is_paid'
        )
            ->leftJoin('employee_leave_types', function ($join) use ($employeeId) {
                $join->on('leave_types.id', '=', 'employee_leave_types.leave_type_id')
                    ->where('employee_leave_types.employee_id', '=', $employeeId)
                    ->where('employee_leave_types.is_active', '=', 1);
            })->where('leave_types.id', $id)->firstorFail();
    }

    public function toggleEarlyExitStatus($id)
    {
        $leaveTypeDetail = $this->findLeaveTypeDetailById($id);
        return $leaveTypeDetail->update([
            'early_exit' => !$leaveTypeDetail->early_exit,
        ]);
    }


    public function getAllLeaveTypesBasedOnEarlyExitStatus($earlyExitStatus)
    {
        return LeaveType::where('is_active', LeaveType::IS_ACTIVE)
            ->when($earlyExitStatus, function ($query) use ($earlyExitStatus) {
                return $query->where('early_exit', $earlyExitStatus);
            })
            ->pluck('name', 'id')
            ->toArray();
    }


    public function getGenderSpecificPaidLeaveTypes($branchId,$gender)
    {
        return LeaveType::whereNotNUll('leave_allocated')
            ->where('branch_id',$branchId)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                    ->orWhere('gender', LeaveGenderEnum::all->value);
            })
            ->select('name','id')
            ->orderBy('id')
            ->get();
    }

    public function getGenderLeaveTypeByBranch($branchId, $gender, $select=['*'])
    {
        return LeaveType::select($select)
            ->where('is_active',1)
            ->where('branch_id',$branchId)
            ->where('gender',$gender)
            ->orWhere('gender',LeaveGenderEnum::all->value)
            ->get();
    }

    public function getEmployeePaidLeaveTypes($employeeId)
    {
        $employee = User::select('id','gender','branch_id')->where('id',$employeeId)->first();

        $gender = $employee->gender;
        return LeaveType::where('branch_id',$employee->branch_id)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                    ->orWhere('gender', LeaveGenderEnum::all->value);
            })
            ->select('name','id')
            ->orderBy('id')
            ->get();
    }
}
