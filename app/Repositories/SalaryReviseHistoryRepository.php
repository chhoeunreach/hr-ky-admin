<?php

namespace App\Repositories;

use App\Models\SalaryReviseHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;

class SalaryReviseHistoryRepository
{

    public function getAllEmployeeSalaryHistoryList($employeeId,$select=['*'],$with=[]): Collection|array
    {
        $query = SalaryReviseHistory::query();
        // Older installations have salary history but no soft-delete column yet.
        // Keep history readable while retaining the deletion filter once migrated.
        if (!Schema::hasColumn((new SalaryReviseHistory())->getTable(), 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query
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
