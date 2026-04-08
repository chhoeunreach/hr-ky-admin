<?php

namespace App\Services\Payroll;

use App\Repositories\BonusRepository;
use Illuminate\Support\Facades\DB;

class BonusService
{
    public function __construct(protected BonusRepository $bonusRepository){}

    /**
     * @throws \Exception
     */
    public function getAllBonusList($select=['*'], $with=[])
    {

        return $this->bonusRepository->getAll($select,$with);

    }

    /**
     * @throws \Exception
     */
    public function store($validatedData)
    {


        return $this->bonusRepository->store($validatedData);

    }

    /**
     * @throws \Exception
     */
    public function findBonusById($id, $select=['*'])
    {
        return $this->bonusRepository->find($id,$select);
    }

    /**
     * @throws \Exception
     */
    public function findBonusByMonth($month, $select=['*'])
    {
        return $this->bonusRepository->findByMonth($month, $select);
    }

    /**
     * @throws \Exception
     */
    public function updateDetail($bonusDetail, $validatedData)
    {
        return $this->bonusRepository->update($bonusDetail,$validatedData);
    }

    /**
     * @throws \Exception
     */
    public function pluckAllActiveBonus()
    {
        return $this->bonusRepository->pluckAllBonusLists();
    }

    /**
     * @throws \Exception
     */
    public function deleteBonusDetail($bonusDetail)
    {

        return $this->bonusRepository->delete($bonusDetail);
    }

    /**
     * @throws \Exception
     */
    public function changeBonusStatus($bonusDetail)
    {
        return $this->bonusRepository->toggleStatus($bonusDetail);
    }

}
