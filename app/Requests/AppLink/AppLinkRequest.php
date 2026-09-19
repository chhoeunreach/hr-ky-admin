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

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'link_type' => ['required', Rule::in(array_keys(AppLink::LINK_TYPES))],
            'url' => 'required|url|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:5120',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ];
    }
}
