<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class RecordRead
{
    public const ENTRY_COLUMNS = ['e.id', 'e.assignment_id', 'e.kind', 'e.title', 'e.body', 'e.solution',
        'e.private', 'e.critical', 'e.status', 'e.requested_due_at', 'e.resolved_at', 'e.reviewed_at', 'e.created_at', 'e.updated_at',
        'e.category_id', 'e.subcategory_id', 'e.classification', 'e.occurred_at', 'e.ended_at', 'e.follow_up_at',
        'e.closed_at', 'e.priority', 'e.tags', 'e.archived_at', 'e.pinned', 'e.sensitive', 'e.verification', 'e.revision'];

    public function __construct(private Tasks $tasks, private Entries $entries, private RecordAccess $access) {}

    public function assignments(Admin|User $actor, bool $manager): Builder
    {
        return $this->tasks->query($actor, $manager)
            ->join('daily_tasks as t', 't.id', '=', 'a.task_id')
            ->select('a.*', 't.title', 't.instructions', 't.priority', 't.checklist', 't.series_id', 't.follow_up_entry_id');
    }

    public function employeeTasks(User $actor, string $tab): Builder
    {
        $query = $this->assignments($actor, false);
        if ($tab !== 'history') {
            $query->whereNull('a.superseded_at')->whereNull('a.archived_at');
        }
        $local = CarbonImmutable::now(RecordTime::ZONE);
        $today = $local->startOfDay()->utc()->toDateTimeString();
        $tomorrow = $local->addDay()->startOfDay()->utc()->toDateTimeString();
        return match ($tab) {
            'today' => $query->whereIn('a.status', Tasks::INCOMPLETE)->where('a.due_at', '>=', $today)->where('a.due_at', '<', $tomorrow),
            'upcoming' => $query->whereIn('a.status', Tasks::INCOMPLETE)->where('a.due_at', '>=', $tomorrow),
            'overdue' => $this->tasks->overdue($query),
            'completed' => $query->whereNotNull('a.completed_at')->where('a.status', '!=', 'cancelled'),
            'returned' => $query->where('a.status', 'returned'),
            'history' => $query,
            default => $query->whereIn('a.status', [...Tasks::INCOMPLETE, 'submitted']),
        };
    }

    public function task(Admin|User $actor, int $id, bool $manager): array
    {
        $task = $this->assignments($actor, $manager)->where('a.id', $id)->first();
        abort_unless($task, 404);
        $entries = $this->entries->query($actor, $manager)->where('e.assignment_id', $id)
            ->orderByDesc('e.id')->paginate(15, self::ENTRY_COLUMNS, 'records');

        $data = $this->taskData($task, $manager);
        $pending = $manager && $task->status === 'submitted'
            ? $this->pendingReport()->where('assignment_id', $id)->first() : null;
        if ($manager && $task->series_id) {
            $data['can_stop_series'] = DB::table('daily_task_series')->where('id', $task->series_id)
                ->where('actor_type', $actor instanceof Admin ? 'admin' : 'employee')
                ->where('actor_id', $actor->id)->whereNotNull('next_at')->exists();
        }
        return [
            'task' => $data,
            'work_records' => true,
            'entries' => $entries,
            'pending_report' => $pending,
            'history' => $this->events('assignment_id', $id)
                ->where(fn (Builder $events) => $events->whereNull('entry_id')->orWhereIn('entry_id',
                    $this->entries->query($actor, $manager)->select('e.id')))->paginate(25, ['*'], 'history'),
            'files' => $this->taskFiles($task)->get(['id', 'name', 'bytes']),
        ];
    }

    public function entry(Admin|User $actor, int $id, bool $manager, ?Builder $scope = null): array
    {
        $entry = ($scope ?? $this->entries->query($actor, $manager))->where('e.id', $id)
            ->first([...self::ENTRY_COLUMNS, 'e.details', 'e.responsible_id']);
        abort_unless($entry, 404);
        $entry->consolidated = $entry->kind === 'report' &&
            DB::table('daily_report_submissions')->where('entry_id', $id)->exists();
        return [
            'entry' => $entry,
            'history' => $this->events('entry_id', $id)->when(!$manager, fn ($q) => $q->where('action', '!=', 'verify_incident'))
                ->paginate(25, ['*'], 'history'),
            'files' => $this->entryFiles($actor, $id, $manager)->get(['id', 'name', 'bytes']),
        ];
    }

    public function taskFile(Admin|User $actor, int $task, int $file, bool $manager): object
    {
        $assignment = $this->assignments($actor, $manager)->where('a.id', $task)->first();
        abort_unless($assignment, 404);
        $match = $this->taskFiles($assignment)->where('id', $file)->first();
        abort_unless($match, 404);
        return $match;
    }

    public function entryFile(Admin|User $actor, int $entry, int $file, bool $manager, ?Builder $scope = null): object
    {
        abort_unless(($scope ?? $this->entries->query($actor, $manager))->where('e.id', $entry)->exists(), 404);
        $match = $this->entryFiles($actor, $entry, $manager)->where('id', $file)->first();
        abort_unless($match, 404);
        return $match;
    }

    private function entryFiles(Admin|User $actor, int $entry, bool $manager): Builder
    {
        $q = DB::table('daily_record_files')->where('entry_id', $entry);
        if (!$manager || !$this->access->allows($actor, RecordAccess::SENSITIVE)) {
            $q->where('restricted', false);
        }
        if ($manager && DB::table('daily_report_submissions')->where('entry_id', $entry)->exists()) {
            $content = DB::table('daily_report_versions')->where('submission_id', $entry)->orderByDesc('version')->value('content');
            $q->whereIn('id', json_decode($content ?? '{}', true)['attachments'] ?? []);
        }
        return $q;
    }

    public function taskData(object $task, bool $manager = false): array
    {
        $data = (array) $task;
        $data['instructions'] = $task->instructions_override ?? $task->instructions;
        $data['checklist'] = json_decode($task->checklist);
        $data['checked'] = json_decode($task->checked);
        $data['overdue'] = !$task->archived_at && !$task->superseded_at && !$task->completed_at
            && in_array($task->status, Tasks::INCOMPLETE, true) && $task->due_at < RecordTime::now();
        unset($data['instructions_override'], $data['company_id'], $data['deadline_version'],
            $data['reminder_sent_at'], $data['overdue_sent_at'], $data['held_status']);
        if (!$manager) {
            unset($data['follow_up_entry_id'], $data['branch_id'], $data['department_id']);
        }
        return $data;
    }

    public function pendingReport(): Builder
    {
        return DB::table('daily_entries')->where('kind', 'report')->where('status', 'submitted');
    }

    private function events(string $column, int $id): Builder
    {
        return DB::table('daily_record_events')->where($column, $id)->orderByDesc('id')
            ->select(['id', 'action', 'actor_type', 'status', 'details', 'created_at']);
    }

    private function taskFiles(object $task): Builder
    {
        $reference = $task->series_id
            ? DB::table('daily_task_series')->where('id', $task->series_id)->value('reference_task_id') : null;
        return DB::table('daily_record_files')->where(fn (Builder $query) => $query
            ->whereIn('task_id', array_values(array_filter([$task->task_id, $reference])))
            ->orWhere('assignment_id', $task->id));
    }
}
