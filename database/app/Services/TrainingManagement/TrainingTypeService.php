<?php

namespace App\Services\TrainingManagement;

use App\Repositories\TrainingTypeRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class TrainingTypeService
{
    public function __construct(
        protected TrainingTypeRepository $trainingTypeRepository
    ){}

    public function getAllTrainingTypes($select= ['*'], $with=[])
    {
        return $this->trainingTypeRepository->getAllTrainingTypes($select,$with);
    }

    public function getAllActiveTrainingTypes($select= ['*'])
    {
        return $this->trainingTypeRepository->getAllActiveTrainingTypes($select);
    }

    /**
     * @throws Exception
     */
    public function findTrainingTypeById($id, $select=['*'], $with=[])
    {

        return $this->trainingTypeRepository->find($id,$select,$with);

    }

    /**
     * @throws Exception
     */
    public function store($validatedData)
    {

        return $this->trainingTypeRepository->create($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateTrainingType($id, $validatedData)
    {

        $trainingTypeDetail = $this->findTrainingTypeById($id);
        return $this->trainingTypeRepository->update($trainingTypeDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteTrainingType($id): bool
    {

        $trainingTypeDetail = $this->findTrainingTypeById($id);

        return $this->trainingTypeRepository->delete($trainingTypeDetail);


    }

    /**
     * @throws Exception
     */
    public function toggleStatus($id): bool
    {
        $trainingTypeDetail = $this->findTrainingTypeById($id);
        return $this->trainingTypeRepository->toggleStatus($trainingTypeDetail);

    }

}
