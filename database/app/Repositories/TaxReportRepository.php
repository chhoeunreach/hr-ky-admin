<?php

namespace App\Repositories;


use App\Models\TaxReport;

class TaxReportRepository
{
    const STATUS_VERIFIED = 'verified';

    public function getAll()
    {
        $branchId = auth()->user()->branch_id;
        $authUserId = auth()->user()->id;
        return TaxReport::select('tax_reports.id', 'fiscal_years.year', 'users.name', 'tax_reports.total_payable_tds')
            ->leftJoin('users', 'tax_reports.employee_id', 'users.id')
            ->leftJoin('fiscal_years', 'tax_reports.fiscal_year_id', 'fiscal_years.id')
            ->where('users.status', self::STATUS_VERIFIED)
            ->when(isset($branchId) && ($authUserId != 1), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })->get();
    }

    public function getTaxReportByEmployee($employeeId, $fiscalYearId, $select=['*'],$with=[])
    {
        return TaxReport::select($select)->with($with)->where('employee_id',$employeeId)->where('fiscal_year_id',$fiscalYearId)->get();
    }

    public function find($id, $select=['*'],$with=[])
    {
        return TaxReport::select($select)->with($with)->where('id',$id)->first();
    }

    public function findByEmployee($employeeId, $fiscalYearId)
    {
        $branchId = auth()->user()->branch_id;
        $authUserId = auth()->user()->id;

       return TaxReport::select('tax_reports.id','fiscal_years.year','users.name','tax_reports.total_payable_tds')
            ->leftJoin('users','tax_reports.employee_id','users.id')
            ->leftJoin('fiscal_years','tax_reports.fiscal_year_id','fiscal_years.id')
            ->where('tax_reports.employee_id',$employeeId)
            ->where('tax_reports.fiscal_year_id',$fiscalYearId)
            ->where('users.status', self::STATUS_VERIFIED)
            ->when(isset($branchId) && ($authUserId != 1), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })->first();
    }

    public function create($validatedData)
    {
        return TaxReport::create($validatedData)->fresh();
    }

    public function update($taxReportDetail,$validatedData)
    {
        return $taxReportDetail->update($validatedData);
    }

}
