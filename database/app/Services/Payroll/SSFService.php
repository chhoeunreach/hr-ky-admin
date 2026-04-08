<?php

namespace App\Services\Payroll;

use App\Repositories\SSFRepository;
use Illuminate\Support\Facades\DB;

class SSFService
{
    public function __construct(protected SSFRepository $ssfRepository){}


    /**
     * @throws \Exception
     */
    public function findSSFById($id)
    {
        /** @var TYPE_NAME $ssfDetail */
        $ssfDetail = $this->ssfRepository->find($id);

        return $ssfDetail;
    }

    /**
     * @throws \Exception
     */
    public function findSSFDetail($select=['*'])
    {
        return $this->ssfRepository->getDetail($select);
    }

    /**
     * @throws \Exception
     */
    public function storeSSF($validatedData)
    {

           return $this->ssfRepository->store($validatedData);
    }

    /**
     * @throws \Exception
     */
    public function updateSSF($id, $validatedData)
    {

            $ssfDetail = $this->findSSFById($id);
            $this->ssfRepository->update($ssfDetail,$validatedData);
            return $ssfDetail;
    }


}
