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
        $existingAsset = $assetId ? Asset::findOrFail($assetId) : null;
        $existingTypeId = ($this->isMethod('put') || $this->isMethod('patch')) ? $existingAsset?->type_id : null;
        $setByRule = Rule::exists('users', 'id')->whereNull('deleted_at')->where(fn ($query) => $query
            ->where('is_active', 1)->when($existingAsset?->set_by, fn ($query, $id) => $query->orWhere('id', $id)));
        if (!auth('admin')->check() && auth()->user()?->branch_id) $setByRule->where('branch_id', auth()->user()->branch_id);
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
            'icloud' => ['nullable', 'string', 'max:255'],
            'asset_password' => ['nullable', 'string', 'max:1000', 'prohibited_if:clear_asset_password,1'],
            'screen_time_passcode' => ['nullable', 'string', 'max:50', 'prohibited_if:clear_screen_time_passcode,1'],
            'clear_asset_password' => ['nullable', 'boolean'],
            'clear_screen_time_passcode' => ['nullable', 'boolean'],
            'set_by' => ['nullable', 'integer', $setByRule],
            'is_working' => ['nullable','string',Rule::in(Asset::IS_WORKING)],
            'warranty_available' => ['required','boolean', Rule::in([1, 0])],
            'is_available' => ['required', 'boolean', Rule::in([1, 0])],
            'assigned_to' => ['nullable',
                Rule::exists('users','id')
                    ->where('is_active',1)
            ],
            'note' => ['nullable','string'],
        ];

        $rules['images'] = ['nullable', 'array', 'max:10'];
        $rules['images.*'] = ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5048'];
        $rules['remove_images'] = ['nullable', 'array'];
        $rules['remove_images.*'] = ['string', Rule::in($assetId ? Asset::findOrFail($assetId)->imageFiles() : [])];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['purchased_date'] = ['required','date'];
            $rules['image'] = ['nullable','file', 'mimes:jpeg,png,jpg,webp','max:5048'];
        } else {
            $rules['purchased_date'] = ['required','date','before_or_equal:today'];
            $rules['image'] = ['required_without:images','nullable','file','mimes:jpeg,png,jpg,webp','max:5048'];
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

