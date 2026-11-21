<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class Tasks
{
    public const STATUSES = ['assigned', 'acknowledged', 'in_progress', 'submitted', 'approved', 'closed', 'returned', 'on_hold', 'cancelled'];
    public const INCOMPLETE = ['assigned', 'acknowledged', 'in_progress', 'returned', 'on_hold'];

    private const EMPLOYEE_TRANSITIONS = [
        'acknowledge' => ['assigned' => 'acknowledged'],
        'start' => ['acknowledged' => 'in_progress', 'returned' => 'in_progress'],
        'progress' => ['in_progress' => 'in_progress'],
        'submit' => ['in_progress' => 'submitted'],
    ];

    private const MANAGER_TRANSITIONS = [
        'approve' => ['submitted' => 'approved'],
        'return' => ['submitted' => 'returned'],
        'hold' => ['assigned' => 'on_hold', 'acknowledged' => 'on_hold', 'in_progress' => 'on_hold', 'returned' => 'on_hold'],
        'close' => ['approved' => 'closed'],
        'reopen' => ['closed' => 'in_progress', 'approved' => 'in_progress', 'cancelled' => 'assigned'],
    ];

    public function __construct(private RecordAccess $access, private RecordEvents $events, private RecordFiles $files) {}

    public function query(Admin|User $actor, bool $manager): Builder
    {
        return $this->access->constrain(DB::table('daily_task_assignments as a'), $actor, 'a', $manager);
    }

    public function overdue(Builder $query, ?string $at = null): Builder
    {
        return $query->whereIn('a.status', self::INCOMPLETE)->whereNull('a.completed_at')
            ->where('a.due_at', '<', $at ?? RecordTime::now())->whereNull('a.archived_at');
    }

    public function create(Admin|User $actor, array $data, array $uploads = []): int
    {
        return $this->files->transaction($uploads, function (array $files) use ($actor, $data) {
            if (!empty($data['request_id'])) {
                DB::table($actor->getTable())->where('id', $actor->id)->lockForUpdate()->value('id');
                $existing = DB::table('daily_tasks')->where('actor_type', $this->access->type($actor))
                    ->where('actor_id', $actor->id)->where('request_id', $data['request_id'])->value('id');
                if ($existing) {
                    return $existing;
                }
            }
            $data['due_at'] = RecordTime::utc($data['due_at']);
            $data['remind_at'] = $this->deadlineReminder($data['due_at'], $data['remind_at'] ?? null);
            $data['checklist'] = array_values($data['checklist'] ?? []);
            $targets = $this->targets($actor, $data);
            $now = RecordTime::now();
            $seriesId = null;
            $anchor = RecordTime::day($data['due_at']);

            if (!empty($data['follow_up_entry_id'])) {
                $followUp = $this->access->constrain(DB::table('daily_entries as e'), $actor, 'e', true)
                    ->where('e.id', $data['follow_up_entry_id'])->where('e.private', false)->where('e.sensitive', false)
                    ->whereNull('e.archived_at')->whereIn('e.kind', ['report', 'incident'])->exists();
                abort_unless($followUp, 404);
            }

            if (!empty($data['frequency'])) {
                $nextDue = $this->nextOccurrence($data['due_at'], $data['frequency'], $anchor);
                $seriesId = DB::table('daily_task_series')->insertGetId([
                    'actor_id' => $actor->id, 'actor_type' => $this->access->type($actor),
                    'frequency' => $data['frequency'], 'anchor_date' => $anchor,
                    'next_at' => $this->releaseAt($data, $nextDue), 'next_due_at' => $nextDue,
                    'template' => json_encode($data, JSON_THROW_ON_ERROR), 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $id = $this->insertTask($actor, $data, $targets, $seriesId, $files);
            if ($seriesId) {
                DB::table('daily_task_series')->where('id', $seriesId)->update(['reference_task_id' => $id]);
            }

            return $id;
        });
    }

    public function generate(int $id): void
    {
        DB::transaction(function () use ($id) {
            $series = DB::table('daily_task_series')->where('id', $id)->lockForUpdate()->first();
            if (!$series?->next_at || $series->next_at > RecordTime::now()) {
                return;
            }
            $actor = $series->actor_type === 'admin'
                ? Admin::find($series->actor_id)
                : User::withoutGlobalScope('branch')->find($series->actor_id);

            if (!$actor || !$this->access->manages($actor)) {
                DB::table('daily_task_series')->where('id', $id)->update(['next_at' => null, 'updated_at' => RecordTime::now()]);
                return;
            }

            $data = json_decode($series->template, true, flags: JSON_THROW_ON_ERROR);
            $nextDue = $this->nextOccurrence($series->next_due_at, $series->frequency, $series->anchor_date);
            $nextRelease = $this->releaseAt($data, $nextDue);
            $data['remind_at'] = $this->reminderAt($data, $series->next_due_at);
            $data['due_at'] = $series->next_due_at;
            unset($data['request_id']);
            $targets = $this->targets($actor, $data, strict: false);
            if ($targets) {
                $this->insertTask($actor, $data, $targets, $id, []);
            }

            DB::table('daily_task_series')->where('id', $id)->update([
                'next_at' => $nextRelease, 'next_due_at' => $nextDue,
                'updated_at' => RecordTime::now(),
            ]);
        }, 3);
    }

    public function stopSeries(Admin|User $actor, int $assignment): void
    {
        DB::transaction(function () use ($actor, $assignment) {
            $row = $this->locked($actor, $assignment, true);
            $series = DB::table('daily_task_series')->where('id', $row->series_id)->lockForUpdate()->first();
            abort_unless($series && $series->actor_type === $this->access->type($actor)
                && (int) $series->actor_id === (int) $actor->id, 403);
            DB::table('daily_task_series')->where('id', $series->id)->update(['next_at' => null, 'updated_at' => RecordTime::now()]);
            $this->events->add($actor, 'stop_series', $assignment);
        }, 3);
    }

    public function employeeAction(User $actor, int $id, array $data, array $uploads = []): void
    {
        $this->files->transaction($uploads, function (array $files) use ($actor, $id, $data) {
            $row = $this->locked($actor, $id, false);
            $action = $data['action'];
            $now = RecordTime::now();
            $status = self::EMPLOYEE_TRANSITIONS[$action][$row->status] ?? null;
            if ($action === 'comment') {
                $this->events->add($actor, $action, $id, details: ['message' => $data['message']]);
                $this->files->attach($files, 'assignment_id', $id);
                $this->update($id, []);
                return;
            }
            abort_unless($status, 409, 'This action is unavailable for the current task status.');

            $changes = ['status' => $status];
            if ($action === 'progress') {
                $checked = array_map('intval', array_unique($data['checked'] ?? []));
                $this->checkItems($row, $checked);
                $complete = $data['progress'] == 100 && count($checked) === count(json_decode($row->checklist, true));
                $changes += ['progress' => $data['progress'], 'checked' => json_encode($checked),
                    'completed_at' => $complete ? ($row->completed_at ?? $now) : null];
            }
            if ($action === 'submit') {
                $checked = json_decode($row->checked, true);
                if (count($checked) !== count(json_decode($row->checklist, true))) {
                    throw ValidationException::withMessages(['checked' => 'Complete every checklist item before submitting.']);
                }
                $changes += ['progress' => 100, 'completed_at' => $row->completed_at ?? $now, 'submitted_at' => $now];
                $entry = DB::table('daily_entries')->insertGetId($this->snapshot($row) + [
                    'assignment_id' => $id, 'kind' => 'report', 'title' => $row->title, 'body' => $data['message'],
                    'status' => 'submitted', 'created_at' => $now, 'updated_at' => $now,
                ]);
                $this->files->attach($files, 'entry_id', $entry);
            }
            $this->update($id, $changes);
            if (!empty($changes['completed_at']) && !$row->completed_at) {
                $this->events->add($actor, 'complete_work', $id);
            }
            $event = $this->events->add($actor, $action, $id, $entry ?? null,
                $action === 'progress' ? ['progress' => $changes['progress'], 'checked' => $checked] : [], $status);
            if ($action === 'submit') {
                $this->withdrawExtensions($actor, $id);
                $this->events->notifyManagers($row, "event:$event", 'Work report submitted', 'A task report is ready for review in Daily Records.');
            }
        });
    }

    public function managerAction(Admin|User $actor, int $id, array $data): void
    {
        DB::transaction(fn () => $this->applyManagerAction($actor, $id, $data), 3);
    }

    public function bulkApprove(Admin|User $actor, array $ids): void
    {
        ksort($ids);
        DB::transaction(function () use ($actor, $ids) {
            foreach ($ids as $id => $report) {
                $this->applyManagerAction($actor, (int) $id, ['action' => 'approve', 'expected_report_id' => $report]);
            }
        }, 3);
    }

    public function locked(Admin|User $actor, int $id, bool $manager): object
    {
        $row = $this->query($actor, $manager)->where('a.id', $id)
            ->join('daily_tasks as t', 't.id', '=', 'a.task_id')
            ->lockForUpdate()->first(['a.*', 't.title', 't.instructions', 't.checklist', 't.series_id', 't.priority']);
        abort_unless($row, 404);
        abort_if($row->superseded_at || $row->archived_at, 409, 'This assignment is read-only.');
        return $row;
    }

    public function snapshot(object $row): array
    {
        return [
            'employee_id' => $row->employee_id, 'company_id' => $row->company_id,
            'department_id' => $row->department_id, 'branch_id' => $row->branch_id,
        ];
    }

    private function applyManagerAction(Admin|User $actor, int $id, array $data): void
    {
        $row = $this->locked($actor, $id, true);
        $now = RecordTime::now();
        $action = $data['action'];
        $details = array_filter(['message' => $data['message'] ?? null]);
        $changes = [];

        if ($action === 'edit') {
            abort_unless(in_array($row->status, self::INCOMPLETE, true), 409);
            $due = RecordTime::utc($data['due_at']);
            $remind = $this->deadlineReminder($due, $data['remind_at'] ?? null);
            $instructions = $data['instructions'] ?? '';
            $changes = ['due_at' => $due, 'remind_at' => $remind, 'instructions_override' => $instructions];
            if ($due !== $row->due_at || $remind !== $row->remind_at) {
                $changes += ['deadline_version' => $row->deadline_version + 1, 'reminder_sent_at' => null, 'overdue_sent_at' => null];
            }
            $details += ['previous_instructions' => $row->instructions_override ?? $row->instructions,
                'instructions' => $instructions, 'previous_due_at' => $row->due_at, 'due_at' => $due,
                'previous_remind_at' => $row->remind_at, 'remind_at' => $remind];
        } elseif ($action === 'reassign') {
            abort_unless(in_array($row->status, self::INCOMPLETE, true), 409);
            $target = $this->access->employees($actor, true)->where('u.id', $data['employee_id'])->first();
            abort_unless($target && (int) $target->company_id === (int) $row->company_id, 404);
            abort_if($target->id == $row->employee_id, 409, 'Select another employee.');
            abort_if(DB::table('daily_task_assignments')->where('task_id', $row->task_id)
                ->where('employee_id', $target->id)->whereNull('superseded_at')->exists(), 409, 'This employee already has the task.');
            $newId = $this->insertAssignment($actor, $row->task_id, $target, $row->due_at, $row->remind_at, $row->instructions_override, 'reassigned');
            $changes = ['superseded_at' => $now, 'status' => 'cancelled'];
            $details += ['replacement_assignment_id' => $newId];
        } elseif ($action === 'archive') {
            abort_unless(in_array($row->status, ['closed', 'cancelled'], true), 409);
            $changes = ['archived_at' => $now];
        } elseif ($action !== 'comment') {
            $next = match ($action) {
                'resume' => $row->status === 'on_hold' ? $row->held_status : null,
                'cancel' => in_array($row->status, [...self::INCOMPLETE, 'submitted'], true) ? 'cancelled' : null,
                default => self::MANAGER_TRANSITIONS[$action][$row->status] ?? null,
            };
            abort_unless($next, 409, 'This action is unavailable for the current task status.');
            $changes = ['status' => $next, 'held_status' => $action === 'hold' ? $row->status : null];
            $details['previous_status'] = $row->status;
            if (in_array($action, ['approve', 'return', 'cancel'], true) && $row->status === 'submitted') {
                $review = DB::table('daily_entries')->where('assignment_id', $id)->where('kind', 'report')->where('status', 'submitted');
                $entry = (clone $review)->lockForUpdate()->value('id');
                abort_unless($entry, 409);
                abort_if(isset($data['expected_report_id']) && (int) $entry !== (int) $data['expected_report_id'],
                    409, 'A report has changed. Review the current submission before deciding.');
                $review->where('id', $entry)->update(['status' => $next, 'reviewed_at' => $now, 'updated_at' => $now]);
                $changes['approved_at'] = $action === 'approve' ? $now : null;
            }
            if (in_array($action, ['return', 'reopen'], true)) {
                $changes += ['completed_at' => null, 'submitted_at' => null, 'approved_at' => null, 'progress' => min($row->progress, 99)];
            }
            if ($action === 'reopen') {
                $changes += ['deadline_version' => $row->deadline_version + 1, 'reminder_sent_at' => null, 'overdue_sent_at' => null];
            }
        }
        $this->update($id, $changes);
        if (in_array($action, ['cancel', 'reassign'], true)) {
            $this->withdrawExtensions($actor, $id);
        }
        $onTime = $action === 'approve' ? $row->completed_at !== null && $row->completed_at <= $row->due_at : null;
        $event = $this->events->add($actor, $action, $id, $entry ?? null, $details, $changes['status'] ?? null, $onTime);
        $title = match ($action) {
            'return' => 'Task returned for correction',
            'edit', 'comment' => 'Task instructions updated',
            'reassign' => 'Task reassigned',
            'approve' => 'Task report approved',
            'cancel' => 'Task cancelled',
            default => null,
        };
        if ($title) {
            $this->events->notify($row->employee_id, "event:$event", $title, 'Open My Tasks in Daily Records to see the update.');
        }
    }

    public function deadlineReminder(string $due, ?string $local): string
    {
        return $local ? RecordTime::utc($local) : CarbonImmutable::parse($due, 'UTC')->subHour()->toDateTimeString();
    }

    private function withdrawExtensions(Admin|User $actor, int $id): void
    {
        $requests = DB::table('daily_entries')->where('assignment_id', $id)->where('kind', 'extension')
            ->where('status', 'pending')->lockForUpdate()->pluck('id');
        if ($requests->isEmpty()) {
            return;
        }
        $now = RecordTime::now();
        DB::table('daily_entries')->whereIn('id', $requests)->update([
            'status' => 'cancelled', 'reviewed_at' => $now, 'updated_at' => $now,
        ]);
        foreach ($requests as $entry) {
            $this->events->add($actor, 'withdraw_extension', $id, $entry, status: 'cancelled');
        }
    }

    private function update(int $id, array $changes): void
    {
        DB::table('daily_task_assignments')->where('id', $id)->update($changes + ['updated_at' => RecordTime::now()]);
    }

    private function checkItems(object $row, array $checked): void
    {
        $items = json_decode($row->checklist, true);
        foreach ($checked as $index) {
            if (!array_key_exists($index, $items)) {
                throw ValidationException::withMessages(['checked' => 'The checklist has changed. Reload the assignment.']);
            }
        }
    }

    private function targets(Admin|User $actor, array $data, bool $strict = true): array
    {
        $query = $this->access->employees($actor, true)->orderBy('u.id');
        $ids = array_unique($data['employee_ids'] ?? []);
        if ($ids) {
            $query->whereIn('u.id', $ids);
        }
        if (!empty($data['department_id'])) {
            $query->where('u.department_id', $data['department_id']);
        }
        if (!empty($data['branch_id'])) {
            $query->where('u.branch_id', $data['branch_id']);
        }
        $targets = $query->limit(1001)->lockForUpdate()->get()->all();
        if (count($targets) > 1000 || ($strict && (!$targets || ($ids && count($ids) !== count($targets))))) {
            throw ValidationException::withMessages(['employee_ids' => 'Choose up to 1,000 authorized employees or a smaller department or location.']);
        }
        return $targets;
    }

    private function insertTask(Admin|User $actor, array $data, array $employees, ?int $series, array $files): int
    {
        $now = RecordTime::now();
        $task = DB::table('daily_tasks')->insertGetId([
            'series_id' => $series, 'occurrence_at' => $series ? $data['due_at'] : null,
            'actor_id' => $actor->id, 'actor_type' => $this->access->type($actor), 'title' => $data['title'],
            'instructions' => $data['instructions'] ?? null, 'priority' => $data['priority'],
            'checklist' => json_encode($data['checklist'], JSON_THROW_ON_ERROR),
            'follow_up_entry_id' => $data['follow_up_entry_id'] ?? null, 'request_id' => $data['request_id'] ?? null, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->files->attach($files, 'task_id', $task);
        foreach ($employees as $employee) {
            $this->insertAssignment($actor, $task, $employee, $data['due_at'], $data['remind_at']);
        }
        return $task;
    }

    private function insertAssignment(Admin|User $actor, int $task, object $employee, string $due, ?string $remind,
        ?string $instructions = null, string $action = 'assigned'): int
    {
        $now = RecordTime::now();
        $id = DB::table('daily_task_assignments')->insertGetId([
            'task_id' => $task, 'employee_id' => $employee->id, 'company_id' => $employee->company_id,
            'department_id' => $employee->department_id, 'branch_id' => $employee->branch_id,
            'checked' => '[]', 'due_at' => $due, 'remind_at' => $remind, 'instructions_override' => $instructions,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $event = $this->events->add($actor, $action, $id, status: 'assigned');
        $this->events->notify($employee->id, "event:$event", 'New task assignment', 'Open My Tasks in Daily Records to view the assignment.');
        return $id;
    }

    private function reminderAt(array $template, string $due): ?string
    {
        if (!$template['remind_at']) {
            return null;
        }
        $lead = CarbonImmutable::parse($template['remind_at'], 'UTC')
            ->diffInSeconds(CarbonImmutable::parse($template['due_at'], 'UTC'));
        return CarbonImmutable::parse($due, 'UTC')->subSeconds($lead)->toDateTimeString();
    }

    private function releaseAt(array $template, string $due): string
    {
        $start = RecordTime::utc(RecordTime::day($due));
        return min($start, $this->reminderAt($template, $due) ?? $start);
    }

    private function nextOccurrence(string $due, string $frequency, string $anchor): string
    {
        $local = CarbonImmutable::parse($due, 'UTC')->setTimezone(RecordTime::ZONE);
        $next = match ($frequency) {
            'daily' => $local->addDay(),
            'weekly' => $local->addWeek(),
            'monthly' => $local->addMonthNoOverflow()->day(min((int) substr($anchor, -2), $local->addMonthNoOverflow()->daysInMonth)),
        };
        return $next->utc()->toDateTimeString();
    }
}
