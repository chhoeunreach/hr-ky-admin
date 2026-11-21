<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class WorkRecords
{
    public const INCIDENT_STATES = ['reported', 'acknowledged', 'investigating', 'in_progress',
        'waiting_for_customer', 'waiting_for_supplier', 'waiting_for_employee', 'on_hold', 'resolved', 'closed', 'reopened', 'cancelled'];
    public const FINDINGS = ['reported', 'awaiting_verification', 'under_investigation', 'substantiated', 'unsubstantiated', 'inconclusive'];

    private const ACTIVE = ['investigating', 'in_progress', 'waiting_for_customer', 'waiting_for_supplier', 'waiting_for_employee', 'on_hold', 'resolved', 'cancelled'];
    private const TRANSITIONS = [
        'open' => ['acknowledged', 'investigating', 'in_progress', 'resolved', 'cancelled'],
        'reported' => ['acknowledged', 'investigating', 'cancelled'],
        'acknowledged' => self::ACTIVE, 'investigating' => self::ACTIVE, 'in_progress' => self::ACTIVE,
        'waiting_for_customer' => self::ACTIVE, 'waiting_for_supplier' => self::ACTIVE, 'waiting_for_employee' => self::ACTIVE,
        'on_hold' => self::ACTIVE, 'reopened' => self::ACTIVE,
        'resolved' => ['closed', 'reopened'], 'closed' => ['reopened'], 'cancelled' => ['reopened'],
    ];

    private const VERIFICATION = [
        'reported' => ['awaiting_verification', 'under_investigation'],
        'awaiting_verification' => ['under_investigation'],
        'under_investigation' => ['substantiated', 'unsubstantiated', 'inconclusive'],
        'substantiated' => ['under_investigation'], 'unsubstantiated' => ['under_investigation'], 'inconclusive' => ['under_investigation'],
    ];

    public function __construct(private Entries $entries, private RecordAccess $access, private RecordFiles $files,
        private RecordEvents $events, private CategoryCatalog $catalog, private Tasks $tasks) {}

    public function save(User $actor, ?int $id, array $data, array $uploads): int
    {
        return $this->files->transaction($uploads, function (array $files) use ($actor, $id, $data) {
            if (!$id && !empty($data['request_id'])) {
                DB::table('users')->where('id', $actor->id)->lockForUpdate()->value('id');
                $existing = DB::table('daily_entries')->where('employee_id', $actor->id)->where('company_id', $actor->company_id)
                    ->where('request_id', $data['request_id'])->value('id');
                if ($existing) {
                    return $existing;
                }
            }
            $row = $id ? $this->entries->query($actor, false)->where('e.id', $id)->lockForUpdate()->first() : null;
            abort_if($id && !$row, 404);
            abort_if($row && ($row->archived_at || (int) $row->revision !== (int) ($data['revision'] ?? -1)),
                409, 'This record changed. Reload it before editing.');
            $kind = $row?->kind ?? $data['kind'];
            abort_unless(in_array($kind, ['note', 'activity', 'incident'], true), 422);
            if ($row && $kind === 'incident') {
                abort_unless(in_array($row->status, ['open', 'reported'], true), 409, 'Add context as a comment after review starts.');
                abort_if((int) $row->category_id !== (int) $data['category_id'] || (int) $row->subcategory_id !== (int) ($data['subcategory_id'] ?? null),
                    422, 'Ask a manager to reclassify this incident.');
            }
            $changes = [
                'title' => $data['title'], 'body' => $data['body'] ?? null,
                'tags' => json_encode($data['tags'] ?? [], JSON_THROW_ON_ERROR),
                'revision' => ($row->revision ?? 0) + 1, 'updated_at' => RecordTime::now(),
            ];
            if ($kind === 'note') {
                $changes['private'] = $data['private'] ?? ($row->private ?? true);
                $details = json_decode($row->details ?? '{}', true, flags: JSON_THROW_ON_ERROR);
                if (isset($data['note_document'])) {
                    [$document, $plain] = NoteDocument::validate($data['note_document'], $id, $files);
                    $details['note_document'] = $document;
                    $changes['body'] = $plain !== '' ? $plain : null;
                } elseif (($data['body'] ?? null) !== ($row?->body ?? null)) {
                    unset($details['note_document']);
                }
                $changes['details'] = $details ? json_encode($details, JSON_THROW_ON_ERROR) : null;
            } else {
                $changes += $this->workFields($kind, $actor, $data, $row);
            }
            if ($id) {
                if ($kind === 'activity' && array_key_exists('assignment_id', $data)
                    && (int) $data['assignment_id'] !== (int) $row->assignment_id) {
                    $changes['assignment_id'] = $data['assignment_id']
                        ? $this->tasks->locked($actor, $data['assignment_id'], false)->id : null;
                }
                DB::table('daily_entries')->where('id', $id)->update($changes);
                $event = $this->events->add($actor, 'edit_'.$kind, null, $id, details: ['revision' => $changes['revision']]);
            } else {
                $assignment = empty($data['assignment_id']) || $kind === 'note' ? null : $this->tasks->locked($actor, $data['assignment_id'], false);
                $status = $kind === 'incident' ? 'reported' : null;
                $id = DB::table('daily_entries')->insertGetId($changes + [
                    'employee_id' => $actor->id, 'company_id' => $actor->company_id, 'department_id' => $actor->department_id,
                    'branch_id' => $actor->branch_id, 'created_at' => RecordTime::now(), 'status' => $status,
                    'kind' => $kind, 'assignment_id' => $assignment?->id, 'request_id' => $data['request_id'] ?? null,
                    'verification' => $kind === 'incident' && $changes['sensitive'] ? 'reported' : null,
                ]);
                $event = $this->events->add($actor, 'create_'.$kind, null, $id, status: $status);
            }
            $this->files->attach($files, 'entry_id', $id);
            if (!$row && $kind === 'incident') {
                $this->events->notifyManagers(DB::table('daily_entries')->find($id), "event:$event",
                    'Incident needs review', 'An incident is ready for review in Daily Records.', $changes['sensitive']);
            }
            return $id;
        });
    }

    public function noteAction(User $actor, int $id, string $action): void
    {
        DB::transaction(function () use ($actor, $id, $action) {
            $row = $this->entries->query($actor, false)->where('e.kind', 'note')->where('e.id', $id)->lockForUpdate()->first();
            abort_unless($row, 404);
            $column = match ($action) {
                'pin' => ['pinned' => true], 'unpin' => ['pinned' => false],
                'archive' => ['archived_at' => RecordTime::now()], 'restore' => ['archived_at' => null],
            };
            DB::table('daily_entries')->where('id', $id)->update($column + [
                'updated_at' => RecordTime::now(), 'revision' => $row->revision + 1,
            ]);
            $this->events->add($actor, $action.'_note', null, $id);
        }, 3);
    }

    public function incidentAction(Admin|User $actor, int $id, array $data, array $uploads = []): void
    {
        $this->files->transaction($uploads, function (array $files) use ($actor, $id, $data) {
            $row = $this->entries->query($actor, true)->where('e.id', $id)->where('e.kind', 'incident')->lockForUpdate()->first();
            abort_unless($row, 404);
            $action = $data['action'];
            $now = RecordTime::now();
            $audit = array_filter(['message' => $data['message'] ?? null]);
            $changes = [];
            if ($action === 'state') {
                $next = $data['state'];
                abort_unless(in_array($next, $this->transitions($row), true), 409, 'This incident transition is unavailable.');
                $changes['status'] = $next;
                $audit['previous_status'] = $row->status;
                if ($next === 'resolved') {
                    $changes += ['resolved_at' => $now, 'solution' => $data['solution']];
                }
                if ($next === 'closed') {
                    $changes['closed_at'] = $now;
                }
                if ($next === 'reopened') {
                    $changes += ['closed_at' => null, 'resolved_at' => null];
                }
            } elseif ($action === 'assign') {
                $target = $this->access->employees($actor, true)->where('u.id', $data['responsible_id'])
                    ->where('u.company_id', $row->company_id)->where('u.branch_id', $row->branch_id)->first();
                abort_unless($target, 404);
                if ($row->sensitive) {
                    $reviewer = User::withoutGlobalScope('branch')->find($target->id);
                    abort_unless($reviewer && $this->access->manages($reviewer) && $this->access->allows($reviewer, RecordAccess::SENSITIVE)
                        && $this->entries->query($reviewer, true)->where('e.id', $id)->exists(), 403);
                }
                $changes['responsible_id'] = $target->id;
                $audit += ['previous_responsible_id' => $row->responsible_id, 'responsible_id' => $target->id];
            } elseif ($action === 'verify') {
                abort_unless($row->sensitive && $this->access->allows($actor, RecordAccess::SENSITIVE), 403);
                $next = $data['verification'];
                abort_unless(in_array($next, self::VERIFICATION[$row->verification ?? 'reported'] ?? [], true), 409);
                $changes += ['verification' => $next, 'reviewer_id' => $actor instanceof User ? $actor->id : null];
                $audit += ['previous_finding' => $row->verification, 'finding' => $next,
                    'employee_response' => $data['employee_response'] ?? null, 'decision' => $data['decision'] ?? null];
                $files = array_map(fn ($file) => $file + ['restricted' => true], $files);
            } elseif ($action === 'reclassify') {
                [$snapshot, $fields, $sensitive] = $this->catalog->selection('incident', $row->department_id, $data, $row);
                abort_if($sensitive && !$this->access->allows($actor, RecordAccess::SENSITIVE), 403);
                $details = json_decode($row->details ?? '{}', true);
                $details['fields'] = $fields;
                $changes += [
                    'category_id' => $data['category_id'], 'subcategory_id' => $data['subcategory_id'],
                    'classification' => json_encode($snapshot, JSON_THROW_ON_ERROR), 'sensitive' => $sensitive,
                    'verification' => $sensitive ? ($row->verification ?? 'reported') : null,
                    'details' => json_encode($details, JSON_THROW_ON_ERROR),
                ];
                $audit += ['previous_classification' => json_decode($row->classification ?? 'null', true),
                    'classification' => $snapshot, 'previous_fields' => json_decode($row->details ?? '{}', true)['fields'] ?? []];
            } elseif ($action === 'escalate') {
                $changes += ['priority' => 'urgent', 'critical' => true];
            }
            if (!empty($data['follow_up_at'])) {
                $changes['follow_up_at'] = RecordTime::utc($data['follow_up_at']);
                $audit['follow_up_at'] = $changes['follow_up_at'];
            }
            DB::table('daily_entries')->where('id', $id)->update($changes + [
                'updated_at' => $now, 'revision' => $row->revision + 1,
            ]);
            $this->files->attach($files, 'entry_id', $id);
            $event = $this->events->add($actor, $action.'_incident', null, $id, $audit, $changes['status'] ?? null);
            if ($action === 'request_info') {
                $this->events->notify($row->employee_id, "event:$event", 'More information requested', 'Open Daily Records to add information.');
            }
            if ($action === 'assign' && !$row->sensitive) {
                $this->events->notify($target->id, "event:$event", 'Incident assigned', 'An incident is assigned to you in Daily Records.');
            }
            if ($action === 'escalate') {
                $this->events->notifyManagers($row, "event:$event", 'Incident escalated', 'An incident is ready for review in Daily Records.', (bool) $row->sensitive);
            }
        });
    }

    public function assigned(User $actor): Builder
    {
        return DB::table('daily_entries as e')->where('e.kind', 'incident')->where('e.company_id', $actor->company_id)
            ->where('e.branch_id', $actor->branch_id)->where('e.responsible_id', $actor->id)
            ->where('e.sensitive', false)->whereNull('e.archived_at');
    }

    public function assignedResponse(User $actor, int $id, string $message, array $uploads): void
    {
        $this->files->transaction($uploads, function (array $files) use ($actor, $id, $message) {
            $row = $this->assigned($actor)->where('e.id', $id)->lockForUpdate()->first();
            abort_unless($row, 404);
            abort_if(in_array($row->status, ['closed', 'cancelled'], true), 409);
            $this->files->attach($files, 'entry_id', $id);
            $this->events->add($actor, 'incident_response', null, $id, ['message' => $message]);
        });
    }

    public function transitions(object $row): array
    {
        $allowed = array_values(array_diff(self::TRANSITIONS[$row->status] ?? [], [$row->status]));
        $workflow = json_decode(DB::table('daily_record_categories')->where('id', $row->subcategory_id)->value('workflow') ?? '[]', true);
        if (!$workflow) {
            $workflow = json_decode(DB::table('daily_record_categories')->where('id', $row->category_id)->value('workflow') ?? '[]', true);
        }
        return $workflow ? array_values(array_intersect($allowed, $workflow)) : $allowed;
    }

    private function workFields(string $kind, User $actor, array $data, ?object $row): array
    {
        [$classification, $fields, $sensitive, $followUpDays] = $this->catalog->selection($kind, $row ? $row->department_id : $actor->department_id, $data, $row);
        $when = $kind === 'activity' ? $data['activity_date'].'T'.($data['start_time'] ?? '00:00') : $data['occurred_at'];
        $occurred = RecordTime::utc($when);
        $end = $kind === 'activity' && !empty($data['end_time']) ? RecordTime::utc($data['activity_date'].'T'.$data['end_time']) : null;
        if ($end && (empty($data['start_time']) || $end <= $occurred)) {
            throw ValidationException::withMessages(['end_time' => 'Enter a start time before the end time.']);
        }
        $followUp = !empty($data['follow_up_at']) ? RecordTime::utc($data['follow_up_at']) : null;
        if (!$followUp && $followUpDays !== null && !$row) {
            $followUp = CarbonImmutable::parse($occurred, 'UTC')->addDays($followUpDays)->toDateTimeString();
        }
        $details = array_intersect_key($data, array_flip(['result', 'challenges', 'immediate_action', 'proposed_solution', 'follow_up', 'start_time']));
        $details['fields'] = $fields;
        return [
            'category_id' => $data['category_id'], 'subcategory_id' => $data['subcategory_id'] ?? null,
            'classification' => json_encode($classification, JSON_THROW_ON_ERROR),
            'details' => json_encode($details, JSON_THROW_ON_ERROR), 'sensitive' => $sensitive, 'occurred_at' => $occurred,
            'ended_at' => $end, 'follow_up_at' => $followUp ?? $row?->follow_up_at,
            'priority' => $data['priority'] ?? $row?->priority ?? 'normal', 'solution' => $data['solution'] ?? $row?->solution,
            'critical' => ($data['priority'] ?? $row?->priority) === 'urgent',
        ];
    }
}
