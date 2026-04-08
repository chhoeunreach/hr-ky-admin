<?php

namespace App\Requests\Notice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoticeRequest extends FormRequest
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
            'branch_id' => 'required|exists:branches,id',
            'title' => 'required|string',
            'description' => 'required|string|min:10',
            'receiver' => 'required|array|min:1',
            'receiver.*.notice_receiver_id' => 'required|exists:users,id',
            'notice_publish_date' => 'nullable|date|after_or_equal:today',
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];
    }
}
