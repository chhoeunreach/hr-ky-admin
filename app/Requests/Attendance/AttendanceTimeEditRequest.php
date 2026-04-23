<?php

namespace App\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AttendanceTimeEditRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if (in_array($this->input('check_out_at'), ['', 'null', 'undefined'], true)) {
            $this->merge([
                'check_out_at' => null,
            ]);
        }
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $checkOutRequiredRule = $this->canLeaveCheckoutBlank() ? 'nullable' : 'required';

        return [
            'check_in_at' => 'required|date_format:H:i',
            'check_out_at' => $checkOutRequiredRule . '|date_format:H:i|after:check_in_at',
            'edit_remark' => 'required|string|min:10'
        ];
    }

    private function canLeaveCheckoutBlank(): bool
    {
        return Auth::guard('admin')->check() || Gate::allows('allow_attendance_without_checkout');
    }

}











