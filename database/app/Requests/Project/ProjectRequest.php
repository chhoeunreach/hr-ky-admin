<?php

namespace App\Requests\Project;

use App\Helpers\AppHelper;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
        if (AppHelper::ifDateInBsEnabled()) {
            $this->merge([
                'start_date' => AppHelper::nepToEngDateInYmdFormat($this->start_date),
                'deadline' => AppHelper::nepToEngDateInYmdFormat($this->deadline),
            ]);
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
            'name' => ['required','string'],
            'client_id' => ['required',
                Rule::exists('clients','id')
                      ->where('is_active',1)
            ],
            'deadline' => 'required|date|after:start_date',
            'cost' => ['nullable','string'],
            'estimated_hours' => ['nullable','numeric'],
            'status' => ['nullable',Rule::in(Project::STATUS)],
            'priority' => ['nullable',Rule::in(Project::PRIORITY)],
            'description' => ['required','string'],
            'assigned_member' => ['required','array','min:1'],
            'assigned_member.*' => ['required',
                Rule::exists('users','id')
                    ->where('is_active',1)
            ],
            'project_leader' => ['required','array','min:1'],
            'project_leader.*' => ['required',
                Rule::exists('users','id')
                    ->where('is_active',1)
            ],
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
            'attachments' => ['sometimes','array','min:1'],
            'attachments.*.' => ['sometimes','file','mimes:pdf,jpeg,png,jpg,docx,doc,xls,txt,webp,zip','max:5048'],
            'notification'=> 'nullable',
        ];
        if($this->isMethod('put')) {
            $rules['start_date'] = 'required|date';
            $rules['cover_pic'] = ['sometimes','file', 'mimes:jpeg,png,jpg,webp','max:5048'];
        } else {
            $rules['start_date'] = 'required|date|after_or_equal:today';
            $rules['cover_pic'] = ['required' , 'file', 'mimes:jpeg,png,jpg,webp','max:5048'];
        }

        return $rules;

    }

}
