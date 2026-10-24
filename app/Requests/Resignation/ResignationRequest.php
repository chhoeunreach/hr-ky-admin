<?php

namespace App\Requests\Resignation;

use App\Enum\ResignationStatusEnum;
use App\Helpers\AppHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResignationRequest extends FormRequest
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

        foreach (['resignation_date', 'last_working_day'] as $field) {
            if ($this->filled($field) && AppHelper::ifDateInBsEnabled()) {
                try {
                    $this->merge([$field => AppHelper::getEnglishDate($this->input($field))]);
                } catch (\Throwable $exception) {
                    // Leave invalid input for the date validation rules.
                }
            }
        }

        if (!auth('admin')->check() && auth()->check()) {
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
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'employee_id' => ['required','exists:users,id'],
            'resignation_date' => ['required','date','date_format:Y-m-d'],
            'last_working_day' => ['required','date','date_format:Y-m-d','after:resignation_date'],
            'reason' => ['required', 'string'],
            'letter_details' => ['nullable', 'array:khmer_name,latin_name,gender,department,employee_code,branch,phone,joining_date,position,returned_property,made_at,letter_date'],
            'letter_details.returned_property' => ['nullable', 'string', 'max:10000'],
            'letter_details.joining_date' => ['nullable', 'date_format:Y-m-d'],
            'letter_details.letter_date' => ['nullable', 'date_format:Y-m-d'],
            'status'=>['nullable', Rule::enum(ResignationStatusEnum::class)],
            'document' => ['nullable','file', 'mimes:jpeg,png,jpg,webp,pdf','max:2048'],

        ];
        foreach (['khmer_name', 'latin_name', 'gender', 'department', 'employee_code', 'branch', 'phone', 'position', 'made_at'] as $field) {
            $rules['letter_details.' . $field] = ['nullable', 'string', 'max:255'];
        }
        if ($this->isMethod('put')) {
            $rules['admin_remark'] = [
                'nullable',
                Rule::requiredIf(function () {
                    return in_array($this->status, [
                        ResignationStatusEnum::approved->value,
                        ResignationStatusEnum::onReview->value,
                        ResignationStatusEnum::cancelled->value
                    ]);
                })
            ];
        } else {
            $rules['admin_remark'] = ['nullable'];
        }

        return $rules;

    }

}

