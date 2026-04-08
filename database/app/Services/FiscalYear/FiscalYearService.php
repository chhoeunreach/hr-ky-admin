<?php

namespace App\Services\FiscalYear;

use App\Repositories\FiscalYearRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class FiscalYearService
{
    public function __construct(
        protected FiscalYearRepository $fiscalYearRepository
    ){}

    public function getAllFiscalYears($select= ['*'])
    {
        return $this->fiscalYearRepository->getAllFiscalYears($select);
    }

    public function getActiveFiscalYear($select= ['*'])
    {
        return $this->fiscalYearRepository->getActiveFiscalYear($select);
    }

    /**
     * @throws Exception
     */
    public function findFiscalYearById($id, $select=['*'])
    {

        return $this->fiscalYearRepository->find($id,$select);

    }

    /**
     * @throws Exception
     */
    public function storeFiscalYear($validatedData)
    {

        $fiscalYearDetail = $this->fiscalYearRepository->create($validatedData);
        return $fiscalYearDetail;

    }

    /**
     * @throws Exception
     */
    public function updateFiscalYear($id, $validatedData)
    {

        $fiscalYearDetail = $this->findFiscalYearById($id);
        return $this->fiscalYearRepository->update($fiscalYearDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteFiscalYear($id): bool
    {

        $fiscalYearDetail = $this->findFiscalYearById($id);
        $this->fiscalYearRepository->delete($fiscalYearDetail);
        return true;

    }

    public function checkFiscalYear($startDate, $endDate, $id=0)
    {
        return $this->fiscalYearRepository->fiscalYearOverlaps($startDate, $endDate, $id);

    }


}
