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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('asset_types')->ignore($this->asset_type)],
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];
        return $rules;

    }

}

