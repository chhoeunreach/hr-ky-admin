<?php

namespace App\Services\AwardManagement;

use App\Repositories\AwardTypeRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class AwardTypeService
{
    public function __construct(
        protected AwardTypeRepository $awardTypeRepository
    ){}

    public function getAllAwardTypes($select= ['*'], $with=[])
    {
        return $this->awardTypeRepository->getAllAwardTypes($select,$with);
    }

    public function getAllActiveAwardTypes($select= ['*'])
    {
        return $this->awardTypeRepository->getAllActiveAwardTypes($select);
    }

    /**
     * @throws Exception
     */
    public function findAwardTypeById($id, $select=['*'], $with=[])
    {

        return $this->awardTypeRepository->findAwardTypeById($id,$select,$with);

    }

    /**
     * @throws Exception
     */
    public function store($validatedData)
    {

        return $this->awardTypeRepository->create($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateAwardType($id, $validatedData)
    {

        $awardTypeDetail = $this->findAwardTypeById($id);

        return $this->awardTypeRepository->update($awardTypeDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteAwardType($id): bool
    {

        $awardTypeDetail = $this->findAwardTypeById($id);

        return $this->awardTypeRepository->delete($awardTypeDetail);



    }

    /**
     * @throws Exception
     */
    public function toggleStatus($id): bool
    {


        $clientDetail = $this->findAwardTypeById($id);
        return $this->awardTypeRepository->toggleStatus($clientDetail);


    }

}
