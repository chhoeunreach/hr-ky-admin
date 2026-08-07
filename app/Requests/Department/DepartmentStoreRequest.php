<?php

namespace App\Requests\Department;


use App\Helpers\AppHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    public function prepareForValidation()
    {

        if (!auth('admin')->check() && auth()->check()) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'dept_name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'dept_head_id' => 'nullable|exists:users,id',
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where(function ($query) {
                    return $query->where('company_id', AppHelper::getAuthUserCompanyId());
                }),
            ],
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];

    }

}










