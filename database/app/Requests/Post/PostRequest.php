<?php

namespace App\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
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
            'post_name' => 'required|string|max:50',
            'dept_id' => 'required|exists:departments,id',
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];
    }

}












