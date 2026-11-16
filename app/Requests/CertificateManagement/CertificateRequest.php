<?php

namespace App\Requests\CertificateManagement;

use Illuminate\Foundation\Http\FormRequest;

class CertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('certificate') ?: $this->route('id');

        return [
            'credential_id' => 'required|string|max:100|unique:certificates,credential_id,' . ($id ? (is_object($id) ? $id->id : $id) : 'NULL') . ',id',
            'template_id' => 'nullable|integer',
            'layout_template' => 'nullable|string|max:100',
            'category' => 'required|in:leader,service,course,custom',
            'language_mode' => 'nullable|string',
            'certificate_title' => 'required|string|max:255',
            'khmer_title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'khmer_subtitle' => 'nullable|string',
            'recipient_name' => 'required|string|max:255',
            'khmer_recipient_name' => 'nullable|string|max:255',
            'recipient_title' => 'nullable|string|max:255',
            'khmer_recipient_title' => 'nullable|string|max:255',
            'course_or_achievement_title' => 'required|string|max:255',
            'khmer_course_or_achievement_title' => 'nullable|string|max:255',
            'citation' => 'nullable|string',
            'khmer_citation' => 'nullable|string',
            'training_hours' => 'nullable|numeric|min:0',
            'compliance_status' => 'nullable|string|max:100',
            'user_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'issue_date' => 'required|string|max:100',
            'khmer_issue_date' => 'nullable|string|max:100',
            'signatory_1_name' => 'nullable|string|max:150',
            'signatory_1_khmer' => 'nullable|string|max:150',
            'signatory_1_role' => 'nullable|string|max:150',
            'signatory_1_khmer_role' => 'nullable|string|max:150',
            'signatory_2_name' => 'nullable|string|max:150',
            'signatory_2_khmer' => 'nullable|string|max:150',
            'signatory_2_role' => 'nullable|string|max:150',
            'signatory_2_khmer_role' => 'nullable|string|max:150',
        ];
    }
}
