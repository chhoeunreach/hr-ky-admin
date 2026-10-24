<?php

namespace App\Requests\AssetManagement;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetDetailRequest extends FormRequest
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

    public function prepareForValidation()
    {
        if (!auth('admin')->check() && auth()->user()?->branch_id) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $assetId = $this->route('asset');
        $existingTypeId = $this->isMethod('put') && $assetId
            ? Asset::findOrFail($assetId)->type_id : null;
        $rules = [
            'branch_id' => ['required','exists:branches,id'],
            'name' => ['required','string'],
            'type_id' => ['required',
                Rule::exists('asset_types','id')
                    ->where(fn ($query) => $query->where(function ($types) use ($existingTypeId) {
                        $types->where('is_active', 1);
                        if ($existingTypeId) $types->orWhere('id', $existingTypeId);
                    }))
            ],
            'asset_code' => ['nullable','string'],
            'asset_serial_no' => ['nullable','string'],
            'phone_numbers' => ['nullable', 'string', 'max:2000'],
            'is_working' => ['nullable','string',Rule::in(Asset::IS_WORKING)],
            'warranty_available' => ['required','boolean', Rule::in([1, 0])],
            'is_available' => ['required', 'boolean', Rule::in([1, 0])],
            'assigned_to' => ['nullable',
                Rule::exists('users','id')
                    ->where('is_active',1)
            ],
            'note' => ['nullable','string'],
        ];

        if ($this->isMethod('put')) {
            $rules['purchased_date'] = ['required','date'];
            $rules['image'] = ['nullable','file', 'mimes:jpeg,png,jpg,webp','max:5048'];
        } else {
            $rules['purchased_date'] = ['required','date','before_or_equal:today'];
            $rules['image'] = ['required','file','mimes:jpeg,png,jpg,webp','max:5048'];
        }

        $rules['warranty_end_date'] = ['nullable',
            'required_if:warranty_available,1',
            'date',
            'after:purchased_date'
        ];

        $rules['assigned_date'] = ['nullable',
            'required_unless:assigned_to,null',
            'date',
            'after_or_equal:purchased_date'
        ];

        return $rules;

    }

}

