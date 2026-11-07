<?php

namespace App\Requests\AssetManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetTypeRequest extends FormRequest
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
        if (in_array($this->input('branch_id'), ['', 'all', null], true)) {
            $this->merge(['branch_id' => null]);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $branchRule = Rule::exists('branches', 'id');
        if (!auth('admin')->check() && auth()->user()?->branch_id) {
            $branchRule->where('id', auth()->user()->branch_id);
        }
        return [
            'branch_id' => ['nullable', $branchRule],
            'name' => ['required', 'string', 'max:255', Rule::unique('asset_types')->ignore($this->asset_type)],
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];

    }

}

