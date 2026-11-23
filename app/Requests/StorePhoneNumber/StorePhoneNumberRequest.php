<?php

namespace App\Requests\StorePhoneNumber;

use App\Models\StorePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePhoneNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'max:50'],
            'operator' => ['required', 'string', Rule::in(array_keys(StorePhoneNumber::OPERATORS))],
            'sim_slot' => ['required', 'string', Rule::in(array_keys(StorePhoneNumber::SIM_SLOTS))],
            'purpose' => ['nullable', 'string', 'max:100'],
            'plan_type' => ['nullable', 'string', Rule::in(array_keys(StorePhoneNumber::PLAN_TYPES))],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'assigned_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(array_keys(StorePhoneNumber::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
