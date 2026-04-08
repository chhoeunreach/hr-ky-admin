<?php

namespace App\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeRequest extends FormRequest
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
        $rules =  [
            'early_exit' => ['sometimes', 'boolean', Rule::in([1, 0])],
            'leave_paid' => ['nullable', 'boolean', Rule::in([1, 0])],
            'leave_allocated' => ['nullable','required_if:leave_paid,1','numeric','min:1']
        ];
        if ($this->isMethod('put')) {
            $rules['name'] = ['required','string',Rule::unique('users')->ignore($this->id)];
        } else {
            $rules['name'] = ['required','string','unique:leave_types,name'];

        }
        return $rules;
    }

}















