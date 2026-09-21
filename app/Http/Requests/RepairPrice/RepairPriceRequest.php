<?php

namespace App\Http\Requests\RepairPrice;

use Illuminate\Foundation\Http\FormRequest;

class RepairPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post')
            ? 'repair_price.create'
            : 'repair_price.update';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $priceId = $this->route('price');
        if ($priceId instanceof \App\Models\RepairPrice\RepairPrice) {
            $priceId = $priceId->id;
        }

        return [
            'repair_device_id' => 'required|exists:repair_devices,id',
            'repair_service_id' => 'required|exists:repair_services,id',
            'part_name' => 'nullable|string|max:255',
            'part_type' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('repair_prices', 'part_type')
                    ->where(fn ($query) => $query->where('repair_device_id', $this->repair_device_id)
                        ->where('repair_service_id', $this->repair_service_id))
                    ->ignore($priceId),
            ],
            'part_cost' => 'required|numeric|min:0',
            'service_fee' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'warranty_days' => 'nullable|integer|min:0',
            'estimated_minutes' => 'nullable|integer|min:0',
            'note' => 'nullable|string',
            'availability_status' => 'nullable|string|max:64',
        ];
    }

    public function messages(): array
    {
        return [
            'part_type.unique' => 'A repair price for this device model, service, and part type already exists.',
        ];
    }
}
