<?php

namespace App\Http\Requests\RepairPrice;

use Illuminate\Foundation\Http\FormRequest;

class RepairDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('repair_price.manage_devices') ?? false;
    }

    public function rules(): array
    {
        $deviceId = $this->route('device');
        if ($deviceId instanceof \App\Models\RepairPrice\RepairDevice) {
            $deviceId = $deviceId->id;
        }

        return [
            'repair_brand_id' => 'required|exists:repair_brands,id',
            'repair_device_type_id' => 'required|exists:repair_device_types,id',
            'repair_device_series_id' => 'nullable|exists:repair_device_series,id',
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('repair_devices', 'name')
                    ->where(fn ($query) => $query->where('repair_brand_id', $this->repair_brand_id))
                    ->ignore($deviceId),
            ],
            'model_number' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:2048',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A device model with this name already exists for this brand.',
        ];
    }
}
