<?php

namespace App\Requests\Payroll\SalaryGroup;


use App\Models\SalaryGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalaryGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'salary_component_id' => ['nullable', 'array', 'min:1'],
            'salary_component_id.*' => [
                'nullable',
                Rule::exists('salary_components', 'id')->where('status', true)
            ],
            'salary_group_employee.*' => [
                'nullable',
                Rule::exists('users', 'id')
                ->where('is_active', true)
                ->where('status','verified'),
                Rule::unique('salary_group_employees','employee_id')->ignore($this->salary_group,'salary_group_id')
            ],
        ];
    }

    public function messages()
    {
        return [
          'salary_group_employee.*.unique' =>  'The employee cannot be in more than one group.'
        ];
    }

}
