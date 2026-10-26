<?php

namespace App\Repositories;

use App\Models\SalaryReviseHistory;
use Illuminate\Database\Eloquent\Collection;

class SalaryReviseHistoryRepository
{

    public function getAllEmployeeSalaryHistoryList($employeeId,$select=['*'],$with=[]): Collection|array
    {
        return SalaryReviseHistory::query()
            ->with($with)
            ->select($select)
            ->where('employee_id',$employeeId)
            ->orderByDesc('id')
            ->get();
    }

    public function getSalaryHistoryByEmployee($employeeId, $periodEnd = null)
    {
        return SalaryReviseHistory::withTrashed()->where('employee_id',$employeeId)
            ->when($periodEnd, fn ($query) => $query->where('date_from', '>', $periodEnd))
            ->orderBy('date_from')->orderBy('id')
            ->first();
    }

    public function store($validatedData)
    {
        return SalaryReviseHistory::create($validatedData)->fresh();
    }

}
