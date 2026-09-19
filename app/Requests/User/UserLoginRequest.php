<?php

namespace App\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserLoginRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|string|max:80',
            'password' => 'required',
            'fcm_token' => 'required|string',
            'device_type' => ['required', 'string', Rule::in(User::DEVICE_TYPE)],
            'uuid' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'device_model' => ['nullable', 'string', 'max:255'],
            'os_version' => ['nullable', 'string', 'max:120'],
            'app_name' => ['nullable', 'string', 'max:255'],
            'app_version' => ['nullable', 'string', 'max:80'],
            'app_build' => ['nullable', 'string', 'max:80'],
            'accuracy' => ['nullable', 'numeric'],
            'battery_level' => ['nullable', 'integer'],
        ];

    }

}














