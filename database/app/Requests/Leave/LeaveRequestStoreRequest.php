<?php

namespace App\Requests\Leave;

use App\Helpers\AppHelper;
use App\Models\LeaveRequestMaster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LeaveRequestStoreRequest extends FormRequest
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

        $this->merge([
            'current_time' => now()->format('Y-m-d'),
            'leave_from' => date('Y-m-d', strtotime($this->input('leave_from'))),
            'leave_to' => date('Y-m-d', strtotime($this->input('leave_to'))),
        ]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'leave_from'=>'required|date|after_or_equal:current_time',
            'leave_to'=>'required|date|after_or_equal:leave_from',
            'status' => ['sometimes', 'string', Rule::in(LeaveRequestMaster::STATUS)],
            'leave_type_id' => 'required|exists:leave_types,id',
            'reasons' => 'required|string|min:10',
            'early_exit' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];
    }



}
















