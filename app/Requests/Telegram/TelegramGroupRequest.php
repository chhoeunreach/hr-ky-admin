<?php

namespace App\Requests\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class TelegramGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'chat_id' => ['required', 'string', 'max:255'],
            'action_key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_\-]+$/'],
            'event_keys' => ['nullable', 'array'],
            'event_keys.*' => ['string', 'max:100', 'regex:/^[a-z0-9_\-]+$/'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'department_name' => ['nullable', 'string', 'max:255'],
            'send_for_all' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
