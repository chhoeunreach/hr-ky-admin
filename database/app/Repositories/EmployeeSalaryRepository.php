<?php

namespace App\Repositories;


use App\Models\EmployeeSalary;
use Carbon\Carbon;

class EmployeeSalaryRepository
{
    const STATUS = 1;

    public function getAll($select = ['*'])
    {
        return EmployeeSalary::select($select)->get();

    }

    public function getAllEmployeeForPayroll($targetDate)
    {
        $branchId = auth()->user()->branch_id;
        $authUserId = auth()->user()->id;
        $targetDate = Carbon::parse($targetDate);
        $endOfMonth = $targetDate->copy()->endOfMonth();

        return EmployeeSalary::select('employee_salaries.employee_id', 'users.marital_status')
            ->join('users', 'employee_salaries.employee_id', 'users.id')
            ->where('users.is_active', 1)
            ->where('users.status', '=', 'verified')
            ->where('users.joining_date', '<=', $endOfMonth)
            ->when(isset($branchId) && ($authUserId != 1), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })
            ->get();

    }

    public function getEmployeeSalaryByEmployeeId($employeeId, $select=['*'])
    {
        return EmployeeSalary::select($select)->where('employee_id', $employeeId)->first();
    }

    public function find($id)
    {
        return EmployeeSalary::where('id',$id)->first();
    }


    public function store($validatedData)
    {
        return EmployeeSalary::create($validatedData)->fresh();
    }

    public function update($employeeSalaryDetail,$validatedData)
    {
         return $employeeSalaryDetail->update($validatedData);
    }

    public function delete($employeeSalaryDetail)
    {
        return $employeeSalaryDetail->delete();
    }





}
