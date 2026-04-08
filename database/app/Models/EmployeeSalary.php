<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $table = 'employee_salaries';

    protected $fillable = ['employee_id', 'annual_salary', 'basic_salary_type', 'basic_salary_value', 'monthly_basic_salary', 'annual_basic_salary',
        'monthly_fixed_allowance', 'annual_fixed_allowance', 'salary_group_id','hour_rate','weekly_basic_salary','weekly_fixed_allowance'];


}
