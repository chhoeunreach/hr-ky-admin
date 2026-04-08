<?php

namespace App\Services\TrainingManagement;

use App\Repositories\TrainingRepository;
use Exception;

class TrainingService
{
    public function __construct(
        protected TrainingRepository $trainingRepository
    )
    {
    }

    public function getAllTrainingPaginated($select = ['*'], $with = [])
    {
        return $this->trainingRepository->getAllTrainingPaginated($select, $with);
    }

    public function getApiTraining($perPage, $select = ['*'], $with = [], $isUpcoming = 0)
    {
        $this->updateStatus();
        return $this->trainingRepository->getEmployeeTrainingPaginated($perPage, $select, $with, $isUpcoming);
    }

    public function getRecentEmployeeTraining($select, $with, $employeeId = 0)
    {
        return $this->trainingRepository->getRecentTraining($select, $with, $employeeId);
    }

    public function getSummary($employeeId )
    {
        return $this->trainingRepository->getTrainingSummary($employeeId);
    }

    /**
     * @throws Exception
     */
    public function findTrainingById($id, $select = ['*'], $with = [])
    {
        return $this->trainingRepository->find($id, $select, $with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveTrainingDetail($validatedData)
    {
        $relationData = $this->getTrainingRelationData($validatedData);
        $validatedData['created_by'] = auth()->user()->id;

        $trainingDetail = $this->trainingRepository->store($validatedData);

        if ($trainingDetail) {
            $this->trainingRepository->saveEmployee($trainingDetail, $relationData['employee']);
            $this->trainingRepository->saveDepartment($trainingDetail, $relationData['department']);
            $this->trainingRepository->saveTrainer($trainingDetail, $relationData['trainer']);
        }
        return $trainingDetail;

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateTrainingDetail($id, $validatedData)
    {

        $relationData = $this->getTrainingRelationData($validatedData);
        $trainingDetail = $this->findTrainingById($id);
        $status = $this->trainingRepository->update($trainingDetail, $validatedData);

        if($status){
            $this->trainingRepository->updateEmployee($trainingDetail, $relationData['employee']);
            $this->trainingRepository->updateDepartment($trainingDetail, $relationData['department']);
            $this->trainingRepository->updateTrainer($trainingDetail, $relationData['trainer']);
        }

        return $trainingDetail;
    }

    /**
     * @throws Exception
     */
    public function deleteTraining($id)
    {
        $trainingDetail = $this->findTrainingById($id);
        return $this->trainingRepository->delete($trainingDetail);
    }

    public function updateStatus()
    {
        $this->trainingRepository->updateAllStatus();
    }


    private function getTrainingRelationData($validatedData): array
    {
        $data = [
            'employee' => array_map(fn($id) => ['employee_id' => $id], $validatedData['employee_id']),
            'department' => array_map(fn($id) => ['department_id' => $id], $validatedData['department_id']),
        ];

        $trainer_type = array_map(fn($id) => ['trainer_type' => $id], $validatedData['trainer_type']);
        $trainer = array_map(fn($id) => ['trainer_id' => $id], $validatedData['trainer_id']);

        $mergedTrainers = array_map(function ($trainer, $type) {
            return array_merge($trainer, $type);
        }, $trainer, $trainer_type);

        $data['trainer'] = $mergedTrainers;


        return  $data;
    }

    public function checkType($typeId)
    {
        return $this->trainingRepository->checkTrainingType($typeId);
    }
    public function checkTrainer($trainerId)
    {
        return $this->trainingRepository->checkTrainer($trainerId);
    }

}
