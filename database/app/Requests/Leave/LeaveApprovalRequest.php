<?php

namespace App\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveApprovalRequest extends FormRequest
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
        return [
            'subject' => ['required'],
            'leave_type_id' => ['required'],
            'department_id' => ['required', 'array', 'min:1'],
            'department_id.*' => [
                'required',
                Rule::exists('departments', 'id')->where('is_active', 1)
            ],

            'approver' => ['required', 'array', 'min:1'],
            'approver.*' => ['required'],
            'role_id' => ['nullable', 'array', 'min:1'],
            'role_id.*' => [
                'nullable',
                Rule::exists('roles', 'id')->where('is_active', 1)
            ],
            'user_id' => ['nullable', 'array', 'min:1'],
            'user_id.*' => [
                'nullable',
                Rule::exists('users', 'id')->where('is_active', 1)
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $approvers = $this->input('approver');
            $userIds = $this->input('user_id');

            if (is_array($approvers)) {
                foreach ($approvers as $index => $approver) {
                    // Check if the corresponding approver is 'specific_personnel'
                    if ($approver === 'specific_personnel') {
                        // Check if the corresponding user_id is empty or not provided
                        if (empty($userIds[$index] ?? null)) {
                            $validator->errors()->add("user_id.$index", "User ID is required for approver $approver.");
                        }
                    }
                }
            }
        });
    }


}















