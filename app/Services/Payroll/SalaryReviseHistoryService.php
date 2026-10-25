<?php

namespace App\Services\Payroll;

use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\SalaryReviseHistoryRepository;
use Exception;

class SalaryReviseHistoryService
{
    public function __construct(
        public SalaryReviseHistoryRepository $salaryHistoryRepo,
        public EmployeeSalaryRepository $employeeSalaryRepository,
        public SalaryGroupService $salaryGroupService,
    ){}

    public function getEmployeeAllSalaryHistory($employeeId,$select=['*'],$with=[])
    {
        return $this->salaryHistoryRepo->getAllEmployeeSalaryHistoryList($employeeId,$select,$with);
    }
    public function getEmployeeSalaryHistory($employeeId, $periodEnd = null)
    {
        return $this->salaryHistoryRepo->getSalaryHistoryByEmployee($employeeId, $periodEnd);
    }

    /**
     * @param $validatedData
     * @return void
     * @throws Exception
     */
    public function store($validatedData): void
    {
        app(EmployeeSalaryManagementService::class)->revise($validatedData);
    }
}
