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
    public function attributes(): array
    {
        $labels = [
            'branch_id' => 'branch', 'department_id' => 'department', 'employee_id' => 'employee',
            'resignation_date' => 'resignation_date', 'last_working_day' => 'last_working_day',
            'reason' => 'resignation_reason_label', 'status' => 'status', 'document' => 'resignation_supporting_document',
            'admin_remark' => 'admin_remark', 'letter_details' => 'resignation_letter_information',
            'letter_details.khmer_name' => 'khmer_name', 'letter_details.latin_name' => 'latin_name',
            'letter_details.gender' => 'gender', 'letter_details.department' => 'department',
            'letter_details.employee_code' => 'employee_id', 'letter_details.branch' => 'branch',
            'letter_details.phone' => 'phone', 'letter_details.joining_date' => 'joining_date',
            'letter_details.position' => 'position', 'letter_details.returned_property' => 'company_property_returned',
            'letter_details.made_at' => 'resignation_place_of_signing', 'letter_details.letter_date' => 'resignation_letter_date',
        ];

        return array_map(fn ($label) => __('index.' . $label), $labels);
    }

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

