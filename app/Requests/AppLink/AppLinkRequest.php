<?php

namespace App\Requests\AppLink;

use App\Models\AppLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppLinkRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('account_type')) $this->merge(['account_type' => null]);
        if (!$this->has('manager_ids')) $this->merge(['manager_ids' => []]);
    }

    public function rules()
    {
        $platform = $this->input('link_type');
        $types = is_string($platform) ? (AppLink::accountTypes()[$platform] ?? []) : [];
        return [
            'name' => 'required|string|max:255',
            'link_type' => ['required', Rule::in(array_keys(AppLink::LINK_TYPES))],
            'account_type' => ['nullable', 'required_if:link_type,telegram,facebook', Rule::in(array_keys($types))],
            'username' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'max:1000', 'prohibited_if:clear_password,1'],
            'clear_password' => ['nullable', 'boolean'],
            'manager_ids' => ['nullable', 'array', 'max:100'],
            'manager_ids.*' => ['integer', 'distinct', Rule::in(AppLink::managerOptions(
                AppLink::find($this->route('app_link') ?? $this->route('id'))
            )->modelKeys())],
            'url' => 'required|url|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:5120',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ];
    }
}
