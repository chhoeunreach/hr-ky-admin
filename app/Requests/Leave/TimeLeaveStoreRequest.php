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

class TimeLeaveStoreRequest extends FormRequest
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

        $this->merge([
            'issue_date' => date('Y-m-d',strtotime($startDate)),
        ]);
        if (!auth('admin')->check() && auth()->check()) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return  [
            'branch_id' => ['required','exists:branches,id'],
            'department_id' => ['required','exists:departments,id'],
            'issue_date' => ['required','date'] ,
            'reasons' => ['required','string','min:10'],
            'leave_from' => 'required',
            'leave_to' => ['required','after:leave_from'],
            'requested_by' => 'required',
        ];

    }

    public function attributes(): array
    {
        return [
            'branch_id' => __('index.branch'),
            'department_id' => __('index.department'),
            'issue_date' => __('index.leave_date'),
            'requested_by' => __('index.requested_for'),
            'leave_from' => __('index.from_time'),
            'leave_to' => __('index.to_time'),
            'reasons' => __('index.reason_for_time_leave'),
        ];
    }

    public function messages(): array
    {
        return ['leave_to.after' => __('message.time_leave_end_after_start')];
    }
}
