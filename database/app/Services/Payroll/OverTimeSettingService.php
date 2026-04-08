<?php

namespace App\Services\Payroll;

use App\Repositories\OverTimeEmployeeRepository;
use App\Repositories\OverTimeSettingRepository;
use Illuminate\Support\Facades\DB;

class OverTimeSettingService
{
    public function __construct(protected OverTimeSettingRepository $otRepo, protected OverTimeEmployeeRepository $otEmployeeRepo){}

    /**
     * @throws \Exception
     */
    public function getAllOTList($select=['*'])
    {
        return $this->otRepo->getAll($select);
    }

    /**
     * @throws \Exception
     */
    public function findOTById($id, $with=[])
    {
        return $this->otRepo->find($id, $with);
    }

    public function store($validatedData)
    {
        try{
            DB::beginTransaction();
            $overTime = $this->otRepo->save($validatedData);
            $otEmployeeData = $this->prepareDataForOverTimeEmployee($validatedData['overtime_employee']);
            if($overTime){
                $this->otEmployeeRepo->assignOverTimeToEmployees($overTime,$otEmployeeData);
            }
            DB::commit();;
            return $overTime;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function updateOverTime($otId, $validatedData)
    {
        try{
            $otDetail = $this->findOTById($otId);
            $otEmployeeData = $this->prepareDataForOverTimeEmployee($validatedData['overtime_employee']);
            DB::beginTransaction();
            $otData = $this->otRepo->update($otDetail,$validatedData);
            if($otData){
                $this->otEmployeeRepo->updateOverTimeEmployee($otDetail,$otEmployeeData);
            }
            DB::commit();
            return $otDetail;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteOTSetting($otId)
    {
        try{
            DB::beginTransaction();
            $otDetail = $this->findOTById($otId);
            $delete = $this->otRepo->delete($otDetail);
            if($delete){
                $this->otEmployeeRepo->removeAssignedEmployeeFromOverTime($otDetail);
            }
            DB::commit();
            return $delete;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function changeOTStatus($otId)
    {
        try{
            $otDetail = $this->findOTById($otId);
            DB::beginTransaction();
            $status = $this->otRepo->toggleIsActiveStatus($otDetail);
            DB::commit();
            return $status;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    private function prepareDataForOverTimeEmployee($validatedData): array
    {
        try {
            return collect($validatedData)
                ->map(function ($value) {
                    return ['employee_id' => $value];
                })
                ->all();
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
