<?php

namespace App\Requests\Department;


use App\Helpers\AppHelper;
use App\Models\User;
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
            $this->merge([
                'branch_id' => [auth()->user()->branch_id],
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $branchRules = [
            'required',
            Rule::exists('branches', 'id')->where(function ($query) {
                return $query->where('company_id', AppHelper::getAuthUserCompanyId());
            }),
        ];

        return [
            'dept_name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'dept_head_ids' => ['nullable', 'array'],
            'dept_head_ids.*' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $branchId = substr($attribute, strlen('dept_head_ids.'));
                    $selectedBranchIds = array_map('strval', (array) $this->input('branch_id', []));
                    $isBranchEmployee = in_array($branchId, $selectedBranchIds, true)
                        && User::query()
                            ->where('id', $value)
                            ->where('company_id', AppHelper::getAuthUserCompanyId())
                            ->where('branch_id', $branchId)
                            ->where('status', 'verified')
                            ->where('is_active', 1)
                            ->exists();
                    if (!$isBranchEmployee) {
                        $fail(__('validation.exists', ['attribute' => __('index.department_head')]));
                    }
                },
            ],
            'branch_id' => ['required', 'array', 'min:1'],
            'branch_id.*' => $branchRules,
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];

    }

}








