<?php

namespace App\Rules;

use App\Helpers\AppHelper;
use App\Models\EmployeeSalary;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class AdvanceSalaryAmountRule implements Rule
{
    public $maxAllowedAmount = '';

    public function passes($attribute, $value)
    {
        $employee = EmployeeSalary::query()->select('annual_salary')->where('employee_id',getAuthUserCode())->first();

        $employeeSalary = ($employee->annual_salary / 12) ?? 0;
        $limitInPercent = AppHelper::getMaxAllowedAdvanceSalaryLimit();
        $maxAdvanceAllowed = ($limitInPercent / 100) * $employeeSalary;

        return $value <= $maxAdvanceAllowed;
    }


    public function message()
    {
        $limitInPercent = AppHelper::getMaxAllowedAdvanceSalaryLimit();
        return 'The advance salary amount cannot exceed ' . $limitInPercent . '% of your salary.';
    }
}
