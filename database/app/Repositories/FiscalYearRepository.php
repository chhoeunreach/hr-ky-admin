<?php

namespace App\Repositories;


use App\Models\FiscalYear;

class FiscalYearRepository
{

    public function getAllFiscalYears($select=['*'])
    {
        return FiscalYear::select($select)->get();
    }

    public function getActiveFiscalYear($select=['*'])
    {
        return FiscalYear::select($select)->where('is_running',1)->first();
    }

    public function find($id,$select=['*'])
    {
        return FiscalYear::select($select)->where('id',$id)->first();
    }

    public function create($validatedData)
    {
        return FiscalYear::create($validatedData)->fresh();
    }

    public function update($assetTypeDetail,$validatedData)
    {
        return $assetTypeDetail->update($validatedData);
    }

    public function delete($assetTypeDetail)
    {
        return $assetTypeDetail->delete();
    }

    public function fiscalYearOverlaps($startDate, $endDate, $id=0)
    {
        return FiscalYear::where('id', '!=', $id)
            ->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        })->exists();
    }
}
