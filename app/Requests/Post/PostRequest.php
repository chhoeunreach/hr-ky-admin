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
        if (!auth('admin')->check() && auth()->check() && filled(auth()->user()->branch_id)) {
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
        $branchIds = collect((array) $this->input('branch_id'))->filter()->all();

        $branchRules = [
            'required',
            Rule::exists('branches', 'id')->where(function ($query) {
                return $query->where('company_id', AppHelper::getAuthUserCompanyId());
            }),
        ];

        return [
            'post_name' => 'required|string|max:50',
            'branch_id' => ['required', 'array', 'min:1'],
            'branch_id.*' => $branchRules,
            'dept_id' => ['required', 'array', 'min:1'],
            'dept_id.*' => [
                'required',
                Rule::exists('departments', 'id')->where(function ($query) use ($branchIds) {
                    return $query
                        ->where('company_id', AppHelper::getAuthUserCompanyId())
                        ->whereIn('branch_id', $branchIds);
                }),
            ],
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];
    }

}








