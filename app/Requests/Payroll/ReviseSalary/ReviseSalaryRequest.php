<?php

namespace App\Requests\Payroll\ReviseSalary;

use App\Repositories\UserRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviseSalaryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        $this->merge(['date_from' => $this->input('date_from') ?: now()->toDateString()]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', Rule::exists('users', 'id')
                ->where('is_active', UserRepository::IS_ACTIVE)->where('status', UserRepository::STATUS_VERIFIED)],
            'current_salary' => ['required', 'numeric', 'min:0'],
            'increase_period' => ['required', Rule::in(['period', 'annual'])],
            'increase_type' => ['required', Rule::in(['amount', 'percent'])],
            'increment_percent' => ['nullable', 'required_if:increase_type,percent', 'numeric', 'gt:0', 'max:10000'],
            'increment_amount' => ['nullable', 'required_if:increase_type,amount', 'numeric', 'gt:0', 'max:99999999.99'],
            'remark' => ['required', 'string', 'max:2000'],
            'date_from' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }
}
