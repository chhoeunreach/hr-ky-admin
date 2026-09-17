<?php

namespace App\Requests\Warning;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarningRequest extends FormRequest
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

        $startDate = AppHelper::getEnglishDate($this->input('warning_date'));
        $fromDate = Carbon::createFromFormat('Y-m-d', $startDate);

        $this->merge([
            'warning_date' => $fromDate->format('Y-m-d'),
        ]);

        $employeeIds = collect($this->input('employee_id', []))->filter()->values();

        if ($employeeIds->isNotEmpty()) {
            $employees = DB::table('users')
                ->whereIn('id', $employeeIds)
                ->get(['id', 'branch_id', 'department_id']);

            if (!$this->filled('branch_id')) {
                $this->merge(['branch_id' => $employees->first()?->branch_id]);
            }

            if (empty($this->input('department_id'))) {
                $this->merge([
                    'department_id' => $employees
                        ->pluck('department_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all(),
                ]);
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
            'branch_id' => ['required','exists:branches,id'],
            'department_id' => 'required|array|min:1',
            'department_id.*' => 'required|exists:departments,id',
            'employee_id' => 'required|array|min:1',
            'employee_id.*' => 'required|exists:users,id',
            'message' => ['nullable'],
            'notification'=> 'nullable',
            'subject'=> 'required|string',
        ];

        if($this->isMethod('put')) {
            $rules['warning_date'] = ['required','date','date_format:Y-m-d'];
        } else {
            $rules['warning_date'] = ['required','date','date_format:Y-m-d','after_or_equal:today'];
        }


        return $rules;
    }




}
