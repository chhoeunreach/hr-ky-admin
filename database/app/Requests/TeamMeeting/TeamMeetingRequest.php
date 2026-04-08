<?php

namespace App\Requests\TeamMeeting;

use App\Helpers\AppHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class TeamMeetingRequest extends FormRequest
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
            'meeting_start_time' => date('H:i',strtotime($this->meeting_start_time)),
            'meeting_date' => (AppHelper::ifDateInBsEnabled()) ? AppHelper::dateInYmdFormatNepToEng($this->meeting_date) : $this->meeting_date,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|string',
            'description' => 'required|string|min:10',
            'venue' => 'required|string|min:3',
            'participator' => 'required|array|min:1',
            'participator.*.meeting_participator_id' => 'required|exists:users,id',
            'meeting_date' => 'required|date|after_or_equal:today',
            'meeting_start_time' => 'required|date_format:H:i',
            'image' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,svg|max:3048'],
            'notification'=> 'nullable',
        ];
        return $rules;
    }
}
