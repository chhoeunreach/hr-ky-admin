<?php

namespace App\Requests\Post;

use App\Helpers\AppHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
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
        $branchId = $this->input('branch_id');

        return [
            'post_name' => 'required|string|max:50',
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where(function ($query) {
                    return $query->where('company_id', AppHelper::getAuthUserCompanyId());
                }),
            ],
            'dept_id' => [
                'required',
                Rule::exists('departments', 'id')->where(function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                }),
            ],
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];
    }

}










