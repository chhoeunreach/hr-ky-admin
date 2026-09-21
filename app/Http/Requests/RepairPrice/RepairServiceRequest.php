<?php

namespace App\Http\Requests\RepairPrice;

use Illuminate\Foundation\Http\FormRequest;

class RepairServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('repair_price.manage_services') ?? false;
    }

    public function rules(): array
    {
        return [
            'repair_category_id' => 'required|exists:repair_categories,id',
            'name' => 'required|string|max:255',
            'name_kh' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ];
    }
}
