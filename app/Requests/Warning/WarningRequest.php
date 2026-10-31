<?php

namespace App\Requests\Warning;

use App\Helpers\AppHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

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

        if ($this->filled('warning_date') && is_string($this->input('warning_date'))) {
            try {
                $this->merge(['warning_date' => AppHelper::getEnglishDate($this->input('warning_date'))]);
            } catch (\Throwable $exception) {
                // Keep malformed dates for the validator to report as field errors.
            }
        }

        $employeeIds = collect($this->input('employee_id', []))->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))->values();

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
            if (auth()->user()->branch_id) {
                $this->merge(['branch_id' => auth()->user()->branch_id]);
            }
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
            'branch_id' => ['required','integer','exists:branches,id'],
            'department_id' => 'required|array|min:1',
            'department_id.*' => 'required|integer|distinct|exists:departments,id',
            'employee_id' => 'required|array|min:1',
            'employee_id.*' => 'required|integer|distinct|exists:users,id',
            'message' => ['nullable', 'string'],
            'notification'=> ['nullable', 'boolean'],
            'subject'=> 'required|string|max:255',
        ];

        if($this->isMethod('put')) {
            $rules['warning_date'] = ['required','date','date_format:Y-m-d'];
        } else {
            $rules['warning_date'] = ['required','date','date_format:Y-m-d','after_or_equal:today'];
        }


        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $departmentIds = $this->input('department_id', []);
            $departmentCount = DB::table('departments')->whereIn('id', $departmentIds)
                ->where('branch_id', $this->input('branch_id'))->count();
            $employeeIds = $this->input('employee_id', []);
            $employeeCount = DB::table('users')->whereIn('id', $employeeIds)
                ->where('branch_id', $this->input('branch_id'))->whereIn('department_id', $departmentIds)
                ->whereNull('deleted_at')->count();
            if ($departmentCount !== count($departmentIds) || $employeeCount !== count($employeeIds)) {
                $validator->errors()->add('employee_id', __('index.warning_same_branch_required'));
            }
        });
    }




}
