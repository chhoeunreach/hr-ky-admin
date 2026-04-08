<?php

namespace App\Requests\Payroll\SalaryTDS;

use App\Models\SalaryTDS;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalaryTDSStoreRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        $rules =  [
            'marital_status' => ['required',Rule::in(SalaryTDS::MARITAL_STATUS)],
            'annual_salary_from.*' => 'sometimes|nullable|numeric',
            'annual_salary_to.*' => 'sometimes|nullable|numeric|gt:annual_salary_from.*',
            'tds_in_percent.*' => 'sometimes|required_with:annual_salary_from.*|numeric|between:0,100',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'annual_salary_from.*.numeric' => 'Annual Salary From must be a number',
            'annual_salary_to.*.numeric' => 'Annual Salary To must be a number',
            'annual_salary_to.*.gt'  => 'Annual Salary To must be greater than Annual Salary From',
            'tds_in_percent.*.required_with'  => 'TDS in Percent is required with Annual Salary From',
            'tds_in_percent.*.numeric' => 'TDS in Percent must be a number',
            'tds_in_percent.*.between' => 'TDS in Percent must be between 0 and 100',
        ];
    }
}
