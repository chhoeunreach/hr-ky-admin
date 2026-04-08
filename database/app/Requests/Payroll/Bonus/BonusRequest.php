<?php

namespace App\Requests\Payroll\Bonus;

use App\Enum\BonusTypeEnum;
use App\Models\SalaryComponent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BonusRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'value_type' => ['required', Rule::in(array_column(BonusTypeEnum::cases(), 'value'))],
            'value' => ['required','numeric','min:0'],
            'is_active'=>['nullable'],
            'applicable_month'=>['nullable'],
        ];
        if ($this->isMethod('put')) {
            $rules['title'] = [
                'required',
                'string',
                Rule::unique('bonuses', 'title')->ignore($this->bonu)
            ];
        } else {
            $rules['title'] = ['required', 'string', Rule::unique('bonuses', 'title')];
        }
        return $rules;

    }

}
