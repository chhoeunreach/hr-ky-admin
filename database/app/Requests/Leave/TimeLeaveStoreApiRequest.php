<?php

namespace App\Requests\Leave;

use App\Helpers\AppHelper;
use App\Models\LeaveRequestMaster;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TimeLeaveStoreApiRequest extends FormRequest
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
        $startDate =  AppHelper::getEnglishDate($this->input('issue_date'));
        $leaveFromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate);
        $this->merge([
//            'current_time' => now()->format('Y-m-d'),
            'issue_date' => $leaveFromDate->format('Y-m-d'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return  [
            'issue_date' => 'required|date' ,
            'reasons' => 'required|string|min:10',
            'leave_from' => 'required',
            'leave_to' => 'required|after:leave_from',

        ];

    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'issue_date.required' => 'leave issue date field is required.',
            'issue_date.date' => 'leave issue date must be a valid date.',
            'issue_date.after_or_equal' => 'Leave issue date cannot be in past days.',
            'leave_from.required' => 'The leave from field is required when leave issue date is after today.',
            'leave_to.required' => 'The leave to field is required when leave issue date is after today.',
            'leave_from.after_or_equal' => 'The leave from time must be after or equal to the current time.',
            'leave_to.after' => 'The leave to time must be after the leave from time.',
            'reasons.required' => 'The reasons field is required.',
            'reasons.string' => 'The reasons must be a string.',
            'reasons.min' => 'The reasons must be at least :min characters.',
        ];
    }



}
