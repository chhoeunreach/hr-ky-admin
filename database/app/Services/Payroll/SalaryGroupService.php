<?php

namespace App\Services\Payroll;

use App\Repositories\SalaryGroupEmployeeRepository;
use App\Repositories\SalaryGroupRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalaryGroupService
{
    public function __construct(
        public SalaryGroupRepository $salaryGroupRepo,
        public SalaryGroupEmployeeRepository $salaryGroupEmployeeRepo
    ){}

    /**
     * @throws \Exception
     */
    public function getAllSalaryGroupDetailList($select=['*'], $with=[])
    {
        return $this->salaryGroupRepo->getAllSalaryGroupLists($select,$with);
    }

    /**
     * @throws \Exception
     */
    public function findOrFailSalaryGroupDetailById($id, $select=['*'], $with=[])
    {
        return $this->salaryGroupRepo->findSalaryGroupDetailById($id,$select,$with);
    }

    public function store($validatedData)
    {
        try{
            DB::beginTransaction();
            $validatedData['slug'] = Str::slug($validatedData['name']);
            $salaryGroup = $this->salaryGroupRepo->store($validatedData);
            if(isset($validatedData['salary_group_employee'])){
                $groupEmployeeData = $this->prepareDataForSalaryGroupEmployee($validatedData['salary_group_employee']);
                if($salaryGroup){
                    $this->salaryGroupRepo->attachComponentToGroup($salaryGroup,$validatedData['salary_component_id']);
                    $this->salaryGroupEmployeeRepo->assignEmployeeToSalaryGroup($salaryGroup,$groupEmployeeData);
                }
            }

            DB::commit();;
            return $salaryGroup;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function pluckAllActiveSalaryGroup($select)
    {
        return $this->salaryGroupRepo->pluckActiveSalaryGroup($select);
    }

    /**
     * @throws \Exception
     */
    public function updateDetail($salaryGroupDetail, $validatedData)
    {
        try{
            $validatedData['slug'] = Str::slug($validatedData['name']);
            $validatedData['salary_group_employee'] = $validatedData['salary_group_employee'] ?? [];
            $validatedData['salary_component_id'] = $validatedData['salary_component_id'] ?? [];

            $groupEmployeeData = $this->prepareDataForSalaryGroupEmployee($validatedData['salary_group_employee']);

            DB::beginTransaction();

            $salaryGroupUpdate = $this->salaryGroupRepo->update($salaryGroupDetail,$validatedData);
            if($salaryGroupUpdate){

                $this->salaryGroupRepo->syncSalaryComponentToSalaryGroup($salaryGroupUpdate,$validatedData['salary_component_id']);

                $this->salaryGroupEmployeeRepo->updateSalaryGroupEmployee($salaryGroupUpdate,$groupEmployeeData);
            }
            DB::commit();;
            return $salaryGroupUpdate;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteSalaryGroupDetail($salaryGroupDetail)
    {
        try{
            DB::beginTransaction();
            $salaryGroupIds = [];
            $delete = $this->salaryGroupRepo->delete($salaryGroupDetail);
            if($delete){
                $this->salaryGroupRepo->detachSalaryComponentFromSalaryGroup($salaryGroupDetail,$salaryGroupIds);
                $this->salaryGroupEmployeeRepo->removeAssignedEmployeeFromSalaryGroup($salaryGroupDetail);
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
    public function changeSalaryGroupStatus($salaryGroupDetail)
    {
        try{
            DB::beginTransaction();
            $status = $this->salaryGroupRepo->toggleIsActiveStatus($salaryGroupDetail);
            DB::commit();
            return $status;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    private function prepareDataForSalaryGroupEmployee($validatedData): array
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
