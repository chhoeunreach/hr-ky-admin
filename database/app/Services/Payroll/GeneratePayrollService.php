<?php

namespace App\Services\Payroll;

use App\Enum\BonusTypeEnum;
use App\Enum\PayslipStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\NepaliDate;
use App\Helpers\PayrollHelper;
use App\Models\OverTimeEmployee;
use App\Models\UnderTimeSetting;
use App\Repositories\EmployeePayslipDetailRepository;
use App\Repositories\EmployeePayslipRepository;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\SalaryGroupRepository;
use App\Repositories\TadaRepository;
use App\Repositories\UserRepository;
use App\Services\FiscalYear\FiscalYearService;
use Exception;
use Illuminate\Support\Facades\Log;

class GeneratePayrollService
{
    public function __construct(protected UserRepository $userRepo, protected SalaryGroupRepository $groupRepository,
                                protected TadaRepository $tadaRepository, protected EmployeePayslipRepository $payslipRepository,
                                protected EmployeePayslipDetailRepository $payslipDetailRepository, protected EmployeeSalaryRepository $employeeSalaryRepository,
                                protected SSFService $ssfService, protected BonusService $bonusService, protected SalaryComponentService $salaryComponentService,
                                protected FiscalYearService $fiscalYearService, protected SalaryReviseHistoryService $salaryReviseHistoryService){}

    /**
     * @throws Exception
     */
    public function getEmployeeAccountDetailToCreatePayslip($employeePayslipId): array
    {
        $payslipData = $this->payslipRepository->getAllEmployeePayslipData($employeePayslipId);

        $componentData = $this->payslipDetailRepository->getAll($employeePayslipId);
        $components = $componentData->toArray();
        $earnings = array_values(array_filter($components, function ($component) {
            return $component['component_type'] == 'earning';
        }));

        $deductions = array_values(array_filter($components, function ($component) {
            return $component['component_type'] == 'deductions';
        }));

        return [
            'payslipData'=>$payslipData,
            "earnings"=>$earnings,
            "deductions"=>$deductions,
        ];
    }

    public function getEmployeeSalariesToCreatePayslip($filterData): array
    {
        try {
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            $employeeSalary = [];
            $totalBasicSalary = 0;
            $totalNetSalary = 0;
            $totalAllowance = 0;
            $totalDeduction = 0;
            $otherPayment = 0;
            $totalOverTime = 0;
            $totalUnderTime = 0;


            if($filterData['salary_cycle'] == 'weekly'){
                list($startDate, $endDate) = explode(' to ', $filterData['week']);

                $firstDay = date('Y-m-d',strtotime($startDate));
                $lastDay = date('Y-m-d',strtotime($endDate));

                $duration = $firstDay.' to '.$lastDay;

            }else{

                if($isBsEnabled){
                    $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterData['year'], $filterData['month']);
                    $firstDay = date('Y-m-d',strtotime($dateInAD['start_date'])) ?? null;
                    $lastDay = date('Y-m-d',strtotime($dateInAD['end_date'])) ?? null;

                    $nepaliDate = new NepaliDate();
                    $nepaliMonth = $nepaliDate->getNepaliMonth($filterData['month']);
                    $duration = $nepaliMonth.' '. $filterData['year'];
                    $currentNepaliYearMonth = AppHelper::getCurrentYearMonth();
                    $currentYear = $currentNepaliYearMonth['year'];
                    $currentMonth = $currentNepaliYearMonth['month'];

                }else{

                    $duration = date('F', mktime(0, 0, 0, $filterData['month'], 1)).' '. $filterData['year'];

                    $firstDay = date($filterData['year'].'-'.$filterData['month'].'-01');
                    $lastDay = date($filterData['year'].'-'. $filterData['month'].'-'.date('t', strtotime($firstDay)));
                    $currentYear = date('Y');
                    $currentMonth = date('m');
                }
            }

            $employees = $this->employeeSalaryRepository->getAllEmployeeForPayroll($firstDay);

            foreach ($employees as $employee){

                $payrollData  = $this->payslipRepository->getEmployeePayslipSummary($employee->employee_id, $firstDay, $lastDay, $filterData, $isBsEnabled);

                if (!isset($payrollData->status) || $payrollData->status == PayslipStatusEnum::generated->value) {

                    $employeePayrollData = $this->userRepo->getEmployeeAccountDetailsToGeneratePayslip($employee->employee_id, $filterData);

                    if (isset($employeePayrollData[0])) {
                        $salaryReviseData = $this->salaryReviseHistoryService->getEmployeeSalaryHistory($employee->employee_id);


                        /** calculation of payroll if salary cycle is weekly */
                        if($employeePayrollData[0]->salary_cycle == 'weekly') {

                            if(isset($salaryReviseData) && (strtotime($salaryReviseData->date_from) > strtotime($lastDay)) ) {

                                $grossSalary = $weeklySalary = ($salaryReviseData->base_salary /52) ;

                            }else{
                                $grossSalary = $weeklySalary = ($employeePayrollData[0]->annual_salary / 52);

                            }
                            $totalIncome = 0;
                            $total_deduction = 0;


                            /** salary tds calculation */
                            $taxableIncome = $employeePayrollData[0]->annual_salary;

                            $taxes = PayrollHelper::salaryTDSCalculator($employeePayrollData[0]->marital_status, $taxableIncome);


                            $weeklySalary -= $taxes['weekly_tax'];

                            /** salary components calculation */
                            $employeeSalaryComponents = [];
                            if ($employeePayrollData[0]->salary_group_id) {
                                $components = $this->groupRepository->findSalaryGroupDetailById($employeePayrollData[0]->salary_group_id, ['*'], with(['salaryComponents']));

                                $employeeSalaryComponents = $this->calculateSalaryComponent($components->salaryComponents, $employeePayrollData[0]->annual_salary, $employeePayrollData[0]->monthly_basic_salary);


                                foreach ($employeeSalaryComponents as $component) {

                                    if ($component['type'] == 'earning') {
                                        $totalIncome += $component['weekly'];

                                    }

                                    if ($component['type'] == 'deductions') {
                                        $total_deduction += $component['weekly'];
                                    }
                                }

                                $totalAllowance += $totalIncome;
                                $totalDeduction += $total_deduction;

                                $weeklySalary += $totalIncome;

                                $weeklySalary -= $total_deduction;

                            }
                            /** get attendance data  for payroll caluculation */
                            $attendanceData = AttendanceHelper::getWeeklyDetail($employee->employee_id, $isBsEnabled, $firstDay, $lastDay);

                            $employeeSalary[$employee->employee_id]['attendanceSummary'] = $attendanceData;

                            $deductionFee = $grossSalary / $attendanceData['totalDays'];
                            $totalAbsentLeaveFee = 0;
                            /** get leave Data */
                            $leaveWiseData = PayrollHelper::getLeaveData($employee->employee_id, $firstDay, $lastDay);

                            $leaveData = $leaveWiseData['leaveTakenByType'];
                            $paidLeaveDays = $leaveData->where('leave_type', 'paid')->sum('total_days');
                            $unpaidLeaveDays = $leaveData->where('leave_type', 'unpaid')->sum('total_days');

                            /** calculate present, absent, leave data for payslip */
                            if (isset($filterData['attendance'])) {

                                $absentDays = $attendanceData['totalAbsent'];

                                $isOnOverTime = OverTimeEmployee::where('employee_id', $employee->employee_id)->first();
                                if (isset($isOnOverTime)) {
                                    $totalOverTime += $attendanceData['totalOverTime'];
                                }

                                $underTimeSetting = UnderTimeSetting::first();
                                if (isset($underTimeSetting)) {
                                    $totalUnderTime += $attendanceData['totalUnderTime'];
                                }

                                $totalAbsentLeaveFee = ($deductionFee * $absentDays) + ($deductionFee * $unpaidLeaveDays);

                                if($totalAbsentLeaveFee > $weeklySalary){
                                    $totalAbsentLeaveFee = $weeklySalary;
                                    $weeklySalary = 0;
                                }else{
                                    $weeklySalary -= $totalAbsentLeaveFee;
                                }

                            }

                            /** adjust absent days as paid */
//                            if ($filterData['absent_paid'] == 1) {
//                                $absentFees = $deductionFee * $attendanceData['totalAbsent'];
//
//                                $weeklySalary += $absentFees;
//                            }

                            /** adjust approved leaves as paid */
//                            if ($filterData['approved_paid_leaves'] == 1) {
//                                $leaveFees = $deductionFee * $unpaidLeaveDays;
//
//                                $weeklySalary += $leaveFees;
//                            }

                            /** overtime calculation */
                            $overTime = PayrollHelper::overTimeCalculator($employee->employee_id, $grossSalary);

                            if ($overTime['hourly_rate'] > 0) {
                                $totalOverTime += $attendanceData['totalOverTime'];
                            }


                            if ($attendanceData['totalOverTime'] > $overTime['weekly_limit']) {
                                $overTimeEarning = ($overTime['weekly_limit'] / 60) * $overTime['hourly_rate'];
                            } else {
                                $overTimeEarning = ($attendanceData['totalOverTime'] / 60) * $overTime['hourly_rate'];
                            }

                            $weeklySalary += $overTimeEarning;

                            /** undertime calculation */
                            $underTimeRate = PayrollHelper::underTimeCalculator($grossSalary);
                            if ($underTimeRate > 0) {
                                $totalUnderTime += $attendanceData['totalUnderTime'];
                            }
                            $underTimeDeduction = ($attendanceData['totalUnderTime'] / 60) * $underTimeRate;
                            $weeklySalary -= $underTimeDeduction;


                            /** advance salary adjustment */
                            if ($filterData['include_advance_salary'] == 1) {
                                $weeklySalary -= $employeePayrollData[0]->advance_salary_taken;

                            }

                            /** tada adjustment */
                            if ($filterData['include_tada'] == 1) {
                                $tada = $this->tadaRepository->getEmployeeUnsettledTadaLists($employee->employee_id);

                                $weeklySalary += $tada;

                            }

                            $employeePayslipData = array(
                                "employee_id" => $employee->employee_id,
                                "status" => 'generated',
                                "salary_group_id" => $employeePayrollData[0]->salary_group_id,
                                "salary_cycle" => $filterData['salary_cycle'],
                                "salary_from" => $firstDay,
                                "salary_to" => $lastDay,
                                "gross_salary" => $grossSalary,
                                "tds" => ($filterData['salary_cycle'] == 'monthly') ? $taxes['monthly_tax'] : $taxes['weekly_tax'],
                                "advance_salary" => ($filterData['include_advance_salary'] == 1) ? $employeePayrollData[0]->advance_salary_taken : 0,
                                "tada" => ($filterData['include_tada'] == 1) ? $tada : 0,
                                "net_salary" => $weeklySalary,
                                "total_days" => 7,
                                "present_days" => $attendanceData['totalPresent'],
                                "absent_days" => $attendanceData['totalAbsent'],
                                "leave_days" => $attendanceData['totalLeave'],
                                "created_by" => auth()->user()->id,
                                'include_tada' => $filterData['include_tada'],
                                'include_advance_salary' => $filterData['include_advance_salary'],
                                'attendance' => $filterData['attendance'] ?? 0,
                                'absent_paid' => $filterData['absent_paid'] ?? 0,
                                'approved_paid_leaves' => $filterData['approved_paid_leaves'] ?? 0,
                                'absent_deduction' => round($totalAbsentLeaveFee, 2),
                                'weekends' => $attendanceData['totalWeekend'],
                                'holidays' => $attendanceData['totalHoliday'],
                                'paid_leave' => $paidLeaveDays,
                                'unpaid_leave' => $unpaidLeaveDays,
                                'overtime' => $overTimeEarning,
                                'undertime' => $underTimeDeduction,
                                'is_bs_enabled' => $isBsEnabled,
                            );

                            $employeePaySlip = $this->payslipRepository->getEmployeePayslipData($employee->employee_id, $firstDay, $lastDay);
                            if ($employeePaySlip) {
                                if ($employeePaySlip->status == PayslipStatusEnum::generated->value) {
                                    $this->payslipRepository->update($employeePaySlip, $employeePayslipData);

                                    $this->payslipDetailRepository->deleteByPayslipId($employeePaySlip->id);
                                }

                                $employeeSalaryData = $employeePaySlip;

                            } else {
                                $employeeSalaryData = $this->payslipRepository->store($employeePayslipData);

                            }
                            /** add payslip detail data  */
                            if ($employeeSalaryData) {
                                if ($employeeSalaryData->status == PayslipStatusEnum::generated->value) {
                                    if (isset($employeeSalaryData->salary_group_id)) {
                                        foreach ($employeeSalaryComponents as $employeeSalaryComponent) {
                                            $employeePayslipDetail = [
                                                'employee_payslip_id' => $employeeSalaryData->id,
                                                'salary_component_id' => $employeeSalaryComponent['id'],
                                                'amount' => ($employeeSalaryData->salary_cycle == 'monthly') ? $employeeSalaryComponent['monthly'] : $employeeSalaryComponent['weekly'],
                                            ];

                                            $this->payslipDetailRepository->store($employeePayslipDetail);

                                        }
                                    }
                                }
                            }

                            $employeeSalary[$employee->employee_id] = [
                                'id' => $employeeSalaryData->id,
                                'employee_name' => $employeePayrollData[0]->employee_name,
                                'net_salary' => round($employeeSalaryData->net_salary, 2),
                                'salary_from' => $employeeSalaryData->salary_from,
                                'salary_to' => $employeeSalaryData->salary_to,
                                'status' => $employeeSalaryData->status,
                                'salary_cycle' => $employeeSalaryData->salary_cycle,
                            ];

                            /** summary data calculation */
                            $totalBasicSalary += $employeePayrollData[0]->monthly_basic_salary;
                            $totalNetSalary += $employeeSalaryData->net_salary;
                        } else {
                            /** payroll Calculation of employee with  monthly salary_cycle  */

                            if (isset($salaryReviseData) && strtotime($salaryReviseData->date_from) > strtotime($lastDay)) {

                                $grossSalary = $salaryReviseData->base_salary/12;
                                $annualGrossSalary = $salaryReviseData->base_salary;
                                $monthlyFixedAllowance = $salaryReviseData->base_monthly_allowance;
                                $monthlyBasicSalary = $salaryReviseData->base_monthly_salary;

                            }else{
                                $grossSalary = $employeePayrollData[0]->annual_salary / 12;
                                $annualGrossSalary =  $employeePayrollData[0]->annual_salary;
                                $monthlyFixedAllowance =  $employeePayrollData[0]->monthly_fixed_allowance;
                                $monthlyBasicSalary = $employeePayrollData[0]->monthly_basic_salary;
                            }
                            $monthSalary = $monthlyBasicSalary + $monthlyFixedAllowance;
                            $annualSalary = $monthSalary * 12;

//                            $grossSalary = $employeePayrollData[0]->annual_salary / 12;
//                            $monthSalary = $employeePayrollData[0]->monthly_basic_salary + $employeePayrollData[0]->monthly_fixed_allowance;
//                            $annualSalary = $monthSalary * 12;
                            $totalIncome = 0;
                            $total_deduction = 0;

                            /** get ssf data */
                            $ssfDeduction = 0;
                            $ssfContribution = 0;
                            if($isBsEnabled){
                                $ssfDetail = $this->ssfService->findSSFDetail();

                                /** office contribution */
                                $ssfContribution = isset($ssfDetail) ? ($ssfDetail->office_contribution * $monthlyBasicSalary)/100 : 0;
                                /** employee Deduction */
                                $ssfDeduction = isset($ssfDetail) ? ($ssfDetail->employee_contribution * $monthlyBasicSalary)/100 : 0;
                            }
                            $monthSalary+=$ssfContribution;
                            $monthSalary-=$ssfDeduction;

                            /** salary components calculation */
                            $employeeSalaryComponents = [];
                            if ($employeePayrollData[0]->salary_group_id) {
                                $components = $this->groupRepository->findSalaryGroupDetailById($employeePayrollData[0]->salary_group_id, ['*'], with(['salaryComponents']));

                                $employeeSalaryComponents = $this->calculateSalaryComponent($components->salaryComponents,$annualGrossSalary, $monthlyBasicSalary);

                                foreach ($employeeSalaryComponents as $component) {

                                    if ($component['type'] == 'earning') {
                                        $totalIncome += $component['monthly'];
                                    }

                                    if ($component['type'] == 'deductions') {
                                        $total_deduction += $component['monthly'];
                                    }
                                }

                                $totalAllowance += $totalIncome;
                                $totalDeduction += $total_deduction;

                                $monthSalary += $totalIncome;

                                $monthSalary -= $total_deduction;

                            }

                            /** Bonus Calculation */

                            $bonusTax = 0;
                            $bonusAmount = 0;
                            $bonus = $this->bonusCalculator($filterData['month'], $monthlyBasicSalary, $annualSalary, $employee->marital_status);

                            if(count($bonus) > 0){
                                $bonusTax = $bonus['tax'];
                                $bonusAmount = $bonus['amount'];
                                $monthSalary += $bonusAmount;
                                $monthSalary -= $bonusTax;
                            }


                            /** salary tds calculation */
                            $taxableIncome = $monthSalary *12 ;
                            $taxes = PayrollHelper::salaryTDSCalculator($employeePayrollData[0]->marital_status, $taxableIncome);
                            if($ssfContribution > 0){

                                $yearlyTax = $taxes['total_tax'] - $taxes['sst'];
                                $monthlyTax = $yearlyTax/12;
                                $monthSalary -= $monthlyTax;

                            }else{
                                $monthlyTax = $taxes['monthly_tax'];
                                $monthSalary -= $monthlyTax;
                            }


                            /** get attendance data for payroll calculation */
                            $attendanceData = AttendanceHelper::getMonthlyDetail($employee->employee_id, $isBsEnabled, $filterData['year'], $filterData['month']);

                            $employeeSalary[$employee->employee_id]['attendanceSummary'] = $attendanceData;

                            $deductionFee = $grossSalary / $attendanceData['totalDays'];

                            $weekends = AttendanceHelper::countWeekdaysInMonth($firstDay, $lastDay);

                            /**  get leave Data for leave deduction calculation  *///
                            $leaveWiseData = PayrollHelper::getLeaveData($employee->employee_id, $firstDay, $lastDay);

                            $leaveData = $leaveWiseData['leaveTakenByType'];
                            $paidLeaveDays = $leaveData->where('leave_type', 'paid')->sum('total_days');
                            $unpaidLeaveDays = $leaveData->where('leave_type', 'unpaid')->sum('total_days');
                            $totalAbsentLeaveFee = 0;
                            $absentDays = 0;
                            if (isset($filterData['attendance'])) {
                                if($currentYear == $filterData['year'] && $currentMonth == $filterData['month']){
                                    $absentDays = $attendanceData['totalDays']- $weekends -$attendanceData['totalPresent']-$attendanceData['totalHoliday']-$attendanceData['totalLeave'];
                                }else{
                                    $absentDays = $attendanceData['totalAbsent'];

                                }

                                $totalAbsentLeaveFee = ($deductionFee * $absentDays) + ($deductionFee * $unpaidLeaveDays);

                                if($totalAbsentLeaveFee > $monthSalary){
                                    $totalAbsentLeaveFee = $monthSalary;
                                    $monthSalary = 0;
                                }else{
                                    $monthSalary -= $totalAbsentLeaveFee;
                                }

                            }

                            /** adjust absent days as paid */
//                            if ($filterData['absent_paid'] == 1) {
//                                $absentFees = $deductionFee * $attendanceData['totalAbsent'];
//
//                                $monthSalary += $absentFees;
//                            }

                            /** adjust approved leaves as paid */
//                            if ($filterData['approved_paid_leaves'] == 1) {
//                                $leaveFees = $deductionFee * $unpaidLeaveDays;
//
//                                $monthSalary += $leaveFees;
//                            }


                            /** overtime calculation */
                            $overTime = PayrollHelper::overTimeCalculator($employee->employee_id, $grossSalary);

                            if ($overTime['hourly_rate'] > 0) {
                                $totalOverTime += $attendanceData['totalOverTime'];
                            }

                            if ($attendanceData['totalOverTime'] > $overTime['monthly_limit']) {
                                $overTimeEarning = ($overTime['monthly_limit'] / 60) * $overTime['hourly_rate'];
                            } else {
                                $overTimeEarning = ($attendanceData['totalOverTime'] / 60) * $overTime['hourly_rate'];
                            }

                            $monthSalary += $overTimeEarning;

                            /** undertime calculation */
                            $underTimeRate = PayrollHelper::underTimeCalculator($grossSalary);
                            if ($underTimeRate > 0) {
                                $totalUnderTime += $attendanceData['totalUnderTime'];
                            }
                            $underTimeDeduction = ($attendanceData['totalUnderTime'] / 60) * $underTimeRate;

                            $monthSalary -= $underTimeDeduction;


                            /** advance salary adjustment */
                            if ($filterData['include_advance_salary'] == 1) {
                                $monthSalary -= $employeePayrollData[0]->advance_salary_taken;
                            }

                            /** tada adjustment */
                            if ($filterData['include_tada'] == 1) {
                                $tada = $this->tadaRepository->getEmployeeUnsettledTadaLists($employee->employee_id);

                                $monthSalary += $tada;
                            }

                            $employeePayslipData = [
                                "employee_id" => $employee->employee_id,
                                "status" => 'generated',
                                "salary_group_id" => $employeePayrollData[0]->salary_group_id,
                                "salary_cycle" => $filterData['salary_cycle'],
                                "salary_from" => $firstDay,
                                "salary_to" => $lastDay,
                                "gross_salary" => $grossSalary,
                                "tds" => ($filterData['salary_cycle'] == 'monthly') ? $monthlyTax + $bonusTax : $taxes['weekly_tax'],
                                "advance_salary" => $filterData['include_advance_salary'] == 1 ? $employeePayrollData[0]->advance_salary_taken : 0,
                                "tada" => ($filterData['include_tada'] == 1) ? $tada : 0,
                                "net_salary" => $monthSalary,
                                "total_days" => $attendanceData['totalDays'],
                                "present_days" => $attendanceData['totalPresent'],
                                "absent_days" => $absentDays,
                                "leave_days" => $attendanceData['totalLeave'],
                                "created_by" => auth()->user()->id,
                                'include_tada' => $filterData['include_tada'],
                                'include_advance_salary' => $filterData['include_advance_salary'],
                                'attendance' => $filterData['attendance'],
                                'absent_paid' => $filterData['absent_paid'] ?? 0,
                                'approved_paid_leaves' => $filterData['approved_paid_leaves'] ?? 0,
                                'absent_deduction' => round($totalAbsentLeaveFee, 2),
                                'weekends' => $weekends,
                                'holidays' => $attendanceData['totalHoliday'],
                                'paid_leave' => $paidLeaveDays,
                                'unpaid_leave' => $unpaidLeaveDays,
                                'overtime' => $overTimeEarning,
                                'undertime' => $underTimeDeduction,
                                'is_bs_enabled' => $isBsEnabled,
                                'ssf_contribution'=>$ssfContribution,
                                'ssf_deduction'=>$ssfDeduction,
                                'bonus'=> $bonusAmount,

                            ];

                            $employeePaySlip = $this->payslipRepository->getEmployeePayslipData($employee->employee_id, $firstDay, $lastDay);


                            if ($employeePaySlip) {

                                if ($employeePaySlip->status == PayslipStatusEnum::generated->value) {
                                    $this->payslipRepository->update($employeePaySlip, $employeePayslipData);

                                    $this->payslipDetailRepository->deleteByPayslipId($employeePaySlip->id);
                                }

                                $employeeSalaryData = $employeePaySlip;

                            } else {
                                $employeeSalaryData = $this->payslipRepository->store($employeePayslipData);

                            }


                            /**  add payslip detail data  */
                            if ($employeeSalaryData) {
                                if ($employeeSalaryData->status == PayslipStatusEnum::generated->value) {
                                    if (isset($employeeSalaryData->salary_group_id)) {

                                        foreach ($employeeSalaryComponents as $employeeSalaryComponent) {
                                            $employeePayslipDetail = [
                                                'employee_payslip_id' => $employeeSalaryData->id,
                                                'salary_component_id' => $employeeSalaryComponent['id'],
                                                'amount' => ($employeeSalaryData->salary_cycle == 'monthly') ? $employeeSalaryComponent['monthly'] : $employeeSalaryComponent['weekly'],
                                            ];

                                            $this->payslipDetailRepository->store($employeePayslipDetail);

                                        }
                                    }
                                }
                            }

                            $employeeSalary[$employee->employee_id] = [
                                'id' => $employeeSalaryData->id,
                                'employee_name' => $employeePayrollData[0]->employee_name,
                                'net_salary' => round($employeeSalaryData->net_salary, 2),
                                'salary_from' => $employeeSalaryData->salary_from,
                                'salary_to' => $employeeSalaryData->salary_to,
                                'status' => $employeeSalaryData->status,
                                'salary_cycle' => $employeeSalaryData->salary_cycle,
                            ];

                            /** summary data */
                            $totalBasicSalary += $employeePayrollData[0]->monthly_basic_salary;
                            $totalNetSalary += $employeeSalaryData->net_salary;
                        }

                    }

                } else {

                    $payslipDetailData = $this->payslipDetailRepository->getAll($payrollData->id);

                    $employeeSalary[$employee->employee_id] = [
                        'id' => $payrollData->id,
                        'employee_name' => $payrollData->employee_name,
                        'net_salary' => $payrollData->net_salary,
                        'salary_from' => $payrollData->salary_from,
                        "salary_to" => $payrollData->salary_to,
                        "paid_on" => $payrollData->paid_on,
                        "status" => $payrollData->status,
                        'paid_by' => $payrollData->paid_by,
                        'salary_cycle' => $payrollData->salary_cycle,
                    ];

                    //summaryData
                    $totalBasicSalary += $payrollData->monthly_basic_salary;
                    $totalNetSalary += $payrollData->net_salary;
                    foreach ($payslipDetailData as $detail) {
                        if ($detail->component_type == 'earning') {
                            $totalAllowance += $detail->amount;
                        } else {
                            $totalDeduction += $detail->amount;
                        }
                    }

                }
            }

            $payrollSummary = [
                'duration'=>$duration,
                'totalBasicSalary'=>round($totalBasicSalary,2),
                'totalNetSalary'=>round($totalNetSalary,2),
                'totalAllowance'=>round($totalAllowance,2),
                'totalDeduction'=>round($totalDeduction,2),
                'otherPayment'=>round($otherPayment,2),
                'totalOverTime'=>round($totalOverTime,2),
                'totalUnderTime'=>round($totalUnderTime,2),
            ];


            usort($employeeSalary, function ($a, $b) {
                if ($a['status'] == $b['status']) {
                    return 0;
                }
                return ($a['status'] == 'generated') ? -1 : 1;
            });

            return [
                'payrollSummary'=>$payrollSummary,
                'employeeSalary'=>$employeeSalary
            ];

        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function calculateSalaryComponent($salaryComponents, $annualSalary, $basicSalary): array
    {
        $payslipComponents = [];
        if (count($salaryComponents) > 0) {
            foreach ($salaryComponents as $component) {
                $amount = $this->calculateComponent($component->value_type, $component->annual_component_value, $annualSalary, $basicSalary);

                $annual = $amount * 12;

                $weekly = $annual / 52;

                $payslipComponents[] = [
                    "id" => $component->id,
                    "name" => $component->name,
                    "type" => $component->component_type,
                    "annual" => $annual,
                    "monthly" => $amount,
                    "weekly" => round($weekly, 2),
                ];

            }
        }
        return $payslipComponents;

    }

    public function calculateComponent($valueType, $annualValue, $annualSalary, $basicSalary): float
    {

        $componentValue = 0;
        if ($valueType == 'fixed') {

            $componentValue = $annualValue/12;


        } else if ($valueType == 'ctc') {

            $componentValue = (($annualValue / 100) * $annualSalary)/12;


        } else if ($valueType == 'basic') {

            $componentValue = ($annualValue / 100) * $basicSalary;

        }

        return round($componentValue, 2);
    }

    public function getCurrentEmployeeSalaries(): array
    {
        try {

            $totalBasicSalary = 0;

            $totalNetSalary = 0;
            $totalAllowance = 0;
            $totalCommission = 0;
            $totalLoan = 0;
            $totalDeduction = 0;
            $otherPayment = 0;
            $totalOverTime = 0;
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            if($isBsEnabled){
                $currentNepaliYearMonth = AppHelper::getCurrentYearMonth();
                $year = $currentNepaliYearMonth['year'];
                $month = $currentNepaliYearMonth['month'] - 1;
                if($month == 0){
                    $month = 12;
                    $year = $currentNepaliYearMonth['year']-1;
                }
                $nepaliDate = new NepaliDate();
                $nepaliMonth = $nepaliDate->getNepaliMonth($month);
                $duration = $nepaliMonth.' '. $year;

                $dateInAd = AppHelper::findAdDatesFromNepaliMonthAndYear($year, $month);

                $firstDay =$dateInAd['start_date'];
                $lastDay =$dateInAd['end_date'];
            }else{
                $firstDay = date('Y-m-01', strtotime('first day of last month'));
                $lastDay = date('Y-m-t', strtotime('last day of last month'));
                $duration = date('F Y', strtotime('last month'));
            }

            $employeeSalary  = $this->payslipRepository->getEmployeeCurrentPayslipList($firstDay, $lastDay, $isBsEnabled);

            $payrollSummary = [
                'totalBasicSalary'=>$totalBasicSalary,
                'duration'=>$duration,
                'totalNetSalary'=>$totalNetSalary,
                'totalAllowance'=>$totalAllowance,
                'totalCommission'=>$totalCommission,
                'totalLoan'=>$totalLoan,
                'totalDeduction'=>$totalDeduction,
                'otherPayment'=>$otherPayment,
                'totalOverTime'=>$totalOverTime,
            ];

            return [
                'payrollSummary'=>$payrollSummary,
                'employeeSalary'=>$employeeSalary
            ];
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function getEmployeePayslip($employeeId, $startDate, $endDate, $isBsEnabled){
        return $this->payslipRepository->getEmployeePayslipList($employeeId, $startDate, $endDate, $isBsEnabled);
    }

    public function getEmployeePayslipDetail($employeePayslipId)
    {
        return $this->payslipRepository->getAllEmployeePayslipData($employeePayslipId);
    }

    public function getEmployeePayslipDetailData($employeePayslipId): array
    {
        try {

            return $this->payslipDetailRepository->getAll($employeePayslipId)->toArray();

        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function bonusCalculator($month, $monthlyBasicSalary, $annualSalary, $maritalStatus): array
    {
        $bonusAmount = 0;
        $bonus = $this->bonusService->findBonusByMonth($month);

        if(isset($bonus)){

            if ($bonus->value_type == BonusTypeEnum::fixed->value) {
                $bonusAmount = $bonus->value;
            } else if ($bonus->value_type ==  BonusTypeEnum::annual_percent->value) {
                $bonusAmount = ($bonus->value / 100) * $annualSalary;
            } else if ($bonus->value_type ==  BonusTypeEnum::basic_percent->value) {
                $bonusAmount = ($bonus->value / 100) * $monthlyBasicSalary;
            }
            /** Calculate tax for the bonus */
            $bonusTaxableIncome = $bonusAmount * 12; // Tax as if the bonus is annual
            $bonusTaxes = PayrollHelper::salaryTDSCalculator($maritalStatus, $bonusTaxableIncome);

            return [
                'id'=>$bonus->id,
                'title'=>$bonus->title,
                'month'=>$bonus->applicable_month,
                'amount'=>$bonusAmount,
                'tax'=> $bonusTaxes['monthly_tax'],
            ];

        }
        return [];
    }

}
