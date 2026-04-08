<?php

namespace App\Requests\Leave;

use App\Helpers\AppHelper;
use App\Models\LeaveRequestMaster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class LeaveRequestAdd extends FormRequest
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
        $startDate =  AppHelper::getEnglishDate($this->input('leave_from'));
        $endDate = AppHelper::getEnglishDate($this->input('leave_to'));
        $leaveFromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate);
        $leaveToDate = \Carbon\Carbon::createFromFormat('Y-m-d', $endDate);

        $this->merge([
//            'current_time' => now()->format('Y-m-d h:i:s'),
            'leave_from' => $leaveFromDate->format('Y-m-d'),
            'leave_to' => $leaveToDate->format('Y-m-d'),
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
            'requested_by' => 'required',
            'leave_from' => 'required|date',
            'leave_to' => 'required|date|after_or_equal:leave_from',
            'status' => ['sometimes', 'string', Rule::in(LeaveRequestMaster::STATUS)],
            'leave_type_id' => 'required|exists:leave_types,id',
            'reasons' => 'required|string',
            'early_exit' => ['nullable', 'boolean', Rule::in([1, 0])],
            'start_time' =>  ['nullable'],
            'end_time' =>  ['nullable'],
        ];
    }


}

















