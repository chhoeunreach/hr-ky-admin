<?php

namespace App\Requests\Notice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoticeRequest extends FormRequest
{
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                'message' => __('index.dialog_validation_title'),
                'errors' => $validator->errors(),
            ], 422));
        }
        parent::failedValidation($validator);
    }

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
            $this->merge(['branch_ids' => [auth()->user()->branch_id]]);
        } elseif (!$this->has('branch_ids') && $this->filled('branch_id')) {
            $this->merge(['branch_ids' => [$this->input('branch_id')]]);
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
            'branch_ids' => 'required|array|min:1',
            'branch_ids.*' => ['required', 'integer', 'distinct', Rule::exists('branches', 'id')->where('company_id', \App\Helpers\AppHelper::getAuthUserCompanyId())],
            'title' => 'required|string',
            'description' => 'required|string|min:10',
            'receiver' => 'required|array|min:1',
            'receiver.*.notice_receiver_id' => 'required|exists:users,id',
            'notice_publish_date' => 'nullable|date|after_or_equal:today',
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
            'send_work_alert' => ['nullable', 'boolean'],
        ];
    }
}
