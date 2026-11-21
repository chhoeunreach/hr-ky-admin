<?php

namespace App\Modules\DailyRecords;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class WorkInput
{
    public static function record(Request $request, ?string $kind = null): array
    {
        self::payload($request);
        if ($kind) {
            $request->merge(['kind' => $kind]);
        }
        return $request->validate([
            'kind' => 'required|in:activity,incident,note', 'request_id' => 'nullable|uuid', 'revision' => 'nullable|integer|min:0',
            'note_document' => 'prohibited_unless:kind,note|nullable|string|max:32000',
            'title' => 'required|string|max:180', 'body' => 'required_unless:kind,note|nullable|string|max:10000',
            'category_id' => 'required_unless:kind,note|nullable|integer|min:1',
            'subcategory_id' => 'required_if:kind,incident|nullable|integer|min:1',
            'occurred_at' => 'required_if:kind,incident|nullable|date_format:Y-m-d\TH:i',
            'activity_date' => 'required_if:kind,activity|nullable|date_format:Y-m-d',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'follow_up_at' => 'nullable|date_format:Y-m-d\TH:i',
            'follow_up' => 'sometimes|boolean', 'private' => 'sometimes|boolean',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'solution' => 'nullable|string|max:10000',
            'result' => 'nullable|string|max:3000', 'challenges' => 'nullable|string|max:3000',
            'immediate_action' => 'nullable|string|max:3000', 'proposed_solution' => 'nullable|string|max:3000',
            'assignment_id' => 'nullable|integer|min:1',
            'fields' => 'nullable|array|max:30',
        ] + self::tags() + self::attachments());
    }

    public static function incidentAction(Request $request): array
    {
        self::payload($request);
        return $request->validate([
            'action' => 'required|in:state,assign,comment,request_info,escalate,verify,reclassify',
            'state' => ['required_if:action,state', Rule::in(WorkRecords::INCIDENT_STATES)],
            'message' => 'required_if:action,comment,request_info,verify,escalate|nullable|string|max:10000',
            'solution' => 'required_if:state,resolved|nullable|string|max:10000',
            'responsible_id' => 'required_if:action,assign|nullable|integer|min:1',
            'follow_up_at' => 'nullable|date_format:Y-m-d\TH:i',
            'verification' => ['required_if:action,verify', Rule::in(WorkRecords::FINDINGS)],
            'employee_response' => 'nullable|string|max:10000', 'decision' => 'nullable|string|max:10000',
            'category_id' => 'required_if:action,reclassify|integer|min:1',
            'subcategory_id' => 'required_if:action,reclassify|integer|min:1',
            'fields' => 'nullable|array|max:30',
        ] + self::attachments());
    }

    public static function report(Request $request): array
    {
        self::payload($request);
        return $request->validate([
            'report_date' => 'required|date_format:Y-m-d',
            'revision' => 'required|integer|min:0',
            'action' => 'required|in:save,submit',
            'record_ids' => 'nullable|array|max:250', 'record_ids.*' => 'integer|min:1|distinct',
            'summary' => 'required_if:action,submit|nullable|string|max:10000',
            'challenges' => 'nullable|string|max:5000', 'solutions' => 'nullable|string|max:5000',
            'pending_work' => 'nullable|string|max:5000', 'recommendations' => 'nullable|string|max:5000',
        ] + self::attachments());
    }

    public static function review(Request $request): array
    {
        return $request->validate([
            'action' => 'required|in:start,approve,return,comment',
            'version' => 'required|integer|min:1',
            'message' => 'required_if:action,return,comment|nullable|string|max:10000',
        ]);
    }

    public static function category(Request $request): array
    {
        foreach (['fields', 'tags', 'workflow'] as $key) {
            if ($request->filled($key) && is_string($request->input($key))) {
                $request->merge([$key => self::decode($request->input($key), $key)]);
            }
        }
        return $request->validate([
            'kind' => 'required|in:activity,incident', 'parent_id' => 'nullable|integer|min:1',
            'name_en' => 'required|string|max:120', 'name_km' => 'required|string|max:120',
            'icon' => 'nullable|alpha_dash|max:24', 'department_id' => 'nullable|integer|exists:departments,id',
            'position' => 'required|integer|min:0|max:100000',
            'active' => 'required|boolean', 'sensitive' => 'required|boolean',
            'follow_up_days' => 'nullable|integer|min:0|max:365',
            'workflow' => 'nullable|array|max:12', 'workflow.*' => ['distinct', Rule::in(WorkRecords::INCIDENT_STATES)],
            'fields' => 'nullable|array|max:25',
            'fields.*' => 'required|array:key,label_en,label_km,type,required,options',
            'fields.*.key' => 'required|regex:/^[a-z][a-z0-9_]{0,31}$/|distinct',
            'fields.*.label_en' => 'required|string|max:100', 'fields.*.label_km' => 'required|string|max:100',
            'fields.*.type' => 'required|in:text,multiline,number,date,datetime,select,multi',
            'fields.*.required' => 'required|boolean',
            'fields.*.options' => 'required_if:fields.*.type,select,multi|array|max:30',
            'fields.*.options.*' => 'required|string|max:120',
        ] + self::tags());
    }

    public static function attachments(): array
    {
        return ['files' => 'nullable|array|max:4', 'files.*' => RecordFiles::RULE];
    }

    private static function tags(): array
    {
        return ['tags' => 'nullable|array|max:12', 'tags.*' => 'required|string|max:36|distinct'];
    }

    private static function payload(Request $request): void
    {
        if ($request->has('payload')) {
            $request->merge(self::decode($request->input('payload'), 'payload'));
        }
    }

    private static function decode(mixed $raw, string $name): array
    {
        $value = is_string($raw) && strlen($raw) <= 100000 ? json_decode($raw, true, 32) : null;
        if (!is_array($value)) {
            throw ValidationException::withMessages([$name => 'Supply valid JSON.']);
        }
        return $value;
    }
}
