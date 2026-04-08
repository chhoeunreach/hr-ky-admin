<?php

namespace App\Services\Payroll;

use App\Repositories\SalaryComponentRepository;
use Illuminate\Support\Facades\DB;

class SalaryComponentService
{
    public function __construct(public SalaryComponentRepository $salaryComponentRepo){}
    /**
     * @throws \Exception
     */
    public function getAllSalaryComponentList($select=['*'],$with=[])
    {
        return $this->salaryComponentRepo->getAllSalaryComponentLists($select,$with);
    }

    /**
     * @throws \Exception
     */
    public function getGeneralSalaryComponents($select=['*'])
    {
        return $this->salaryComponentRepo->getGeneralSalaryComponentList($select);
    }

    public function store($validatedData)
    {
        try{
            DB::beginTransaction();
            $salaryComponent = $this->salaryComponentRepo->store($validatedData);
            DB::commit();;
            return $salaryComponent;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function findSalaryComponentById($id,$select=['*'])
    {
        try{
            return $this->salaryComponentRepo->findDetailById($id,$select);
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function updateDetail($salaryComponentDetail,$validatedData)
    {
        try{
            DB::beginTransaction();
                $update = $this->salaryComponentRepo->update($salaryComponentDetail,$validatedData);
            DB::commit();;
            return $update;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function pluckAllActiveSalaryComponent()
    {
        try{
            return $this->salaryComponentRepo->pluckAllSalaryComponentLists();
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function deleteSalaryComponentDetail($salaryComponentDetail)
    {
        try{
            DB::beginTransaction();
            $delete = $this->salaryComponentRepo->delete($salaryComponentDetail);
            DB::commit();;
            return $delete;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function changeSalaryComponentStatus($salaryComponentDetail)
    {
        try{
            DB::beginTransaction();
            $status = $this->salaryComponentRepo->toggleStatus($salaryComponentDetail);
            DB::commit();;
            return $status;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

}
