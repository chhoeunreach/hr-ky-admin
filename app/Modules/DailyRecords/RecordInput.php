<?php

namespace App\Modules\DailyRecords;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class RecordInput
{
    public static function task(Request $request): array
    {
        $checklist = $request->input('checklist');
        if (is_string($checklist) && $request->hasSession()) {
            $request->merge(['checklist' => array_values(array_filter(array_map('trim', preg_split('/\R/u', $checklist)), 'strlen'))]);
        }
        return $request->validate([
            'request_id' => 'nullable|uuid',
            'title' => 'required|string|max:180', 'instructions' => 'nullable|string|max:10000',
            'priority' => 'required|in:low,normal,high,urgent',
            'due_at' => 'required|date_format:Y-m-d\TH:i',
            'remind_at' => 'nullable|date_format:Y-m-d\TH:i|before:due_at',
            'frequency' => 'nullable|in:daily,weekly,monthly',
            'checklist' => 'nullable|array|max:40', 'checklist.*' => 'required|string|max:240',
            'employee_ids' => 'required_without_all:department_id,branch_id|nullable|array|max:1000',
            'employee_ids.*' => 'required|integer|min:1|distinct',
            'department_id' => 'nullable|integer|min:1', 'branch_id' => 'nullable|integer|min:1',
            'follow_up_entry_id' => 'nullable|integer|min:1',
        ] + self::attachments());
    }

    public static function employeeTask(Request $request): array
    {
        $data = $request->validate([
            'action' => 'required|in:acknowledge,start,progress,submit,comment',
            'message' => 'required_if:action,submit,comment|nullable|string|max:10000',
            'progress' => 'required_if:action,progress|integer|min:0|max:100',
            'checked' => 'nullable|array|max:40', 'checked.*' => 'integer|min:0|max:39|distinct',
        ] + self::attachments());
        if (!in_array($data['action'], ['submit', 'comment'], true) && $request->hasFile('files')) {
            throw ValidationException::withMessages(['files' => 'Attach evidence to a task comment or report.']);
        }
        return $data;
    }

    public static function managerTask(Request $request): array
    {
        return $request->validate([
            'action' => 'required|in:approve,return,hold,resume,cancel,close,reopen,archive,edit,reassign,comment,stop_series',
            'message' => 'required_if:action,return,comment|nullable|string|max:10000',
            'expected_report_id' => 'nullable|integer|min:1',
            'instructions' => 'nullable|string|max:10000',
            'due_at' => 'required_if:action,edit|nullable|date_format:Y-m-d\TH:i',
            'remind_at' => 'nullable|date_format:Y-m-d\TH:i|before:due_at',
            'employee_id' => 'required_if:action,reassign|integer|min:1',
        ]);
    }

    public static function entry(Request $request): array
    {
        return $request->validate([
            'kind' => ['required', Rule::in(Entries::KINDS)],
            'request_id' => 'nullable|uuid',
            'assignment_id' => 'required_if:kind,extension|nullable|integer|min:1',
            'title' => 'required|string|max:180',
            'body' => 'required_if:kind,extension,report,incident|nullable|string|max:10000',
            'report_date' => 'exclude_unless:kind,report|nullable|date_format:Y-m-d',
            'private' => 'sometimes|boolean', 'critical' => 'sometimes|boolean',
            'solution' => 'nullable|string|max:10000',
            'requested_due_at' => 'required_if:kind,extension|nullable|date_format:Y-m-d\TH:i',
        ] + self::attachments());
    }

    public static function employeeEntry(Request $request): array
    {
        return $request->validate([
            'action' => 'required|in:complete,reopen,resolve,resubmit,comment',
            'message' => 'required_if:action,resolve,resubmit,comment|nullable|string|max:10000',
        ] + self::attachments());
    }

    public static function review(Request $request): array
    {
        return $request->validate([
            'action' => 'required|in:approve,return',
            'message' => 'required_if:action,return|nullable|string|max:10000',
        ]);
    }

    public static function filters(Request $request): array
    {
        return $request->validate([
            'kind' => 'nullable|in:task,activity,incident,note,report,extension',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'category_id' => 'nullable|integer|min:1', 'subcategory_id' => 'nullable|integer|min:1',
            'reviewer_id' => 'nullable|integer|min:1',
            'employee_id' => 'nullable|integer|min:1', 'department_id' => 'nullable|integer|min:1',
            'branch_id' => 'nullable|integer|min:1', 'search' => 'nullable|string|max:100',
            'status' => ['nullable', Rule::in([...Tasks::STATUSES, ...WorkRecords::INCIDENT_STATES, 'under_review', 'outstanding_incidents', 'overdue', 'archived', 'review', 'pending', 'declined', 'open', 'resolved', 'completed'])],
            'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'date' => 'nullable|date_format:Y-m-d', 'month' => 'nullable|date_format:Y-m',
        ]);
    }

    private static function attachments(): array
    {
        return ['files' => 'nullable|array|max:4', 'files.*' => RecordFiles::RULE];
    }
}
