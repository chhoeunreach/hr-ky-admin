<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class Entries
{
    public const KINDS = ['note', 'todo', 'incident', 'report', 'extension'];

    public function __construct(private RecordAccess $access, private Tasks $tasks, private RecordEvents $events,
        private RecordFiles $files) {}

    public function query(Admin|User $actor, bool $manager): Builder
    {
        $query = $this->access->constrain(DB::table('daily_entries as e'), $actor, 'e', $manager);
        if ($manager) {
            $query->where('e.private', false)->whereNull('e.archived_at');
            if (!$this->access->allows($actor, RecordAccess::SENSITIVE)) {
                $query->where('e.sensitive', false);
            }
        }
        return $query;
    }

    public function create(User $actor, array $data, array $uploads = []): int
    {
        return $this->files->transaction($uploads, function (array $files) use ($actor, $data) {
            if ($data['kind'] === 'report' || !empty($data['request_id'])) {
                DB::table('users')->where('id', $actor->id)->lockForUpdate()->value('id');
            }
            $now = RecordTime::now();
            if ($data['kind'] === 'report') {
                $this->access->requireReportSubmission($actor, $data['report_date'] ?? RecordTime::day($now), $now);
            }
            if (!empty($data['request_id'])) {
                $existing = DB::table('daily_entries')->where('employee_id', $actor->id)
                    ->where('request_id', $data['request_id'])->value('id');
                if ($existing) {
                    return $existing;
                }
            }
            $assignment = null;
            $kind = $data['kind'];
            if (!empty($data['assignment_id'])) {
                $assignment = $this->tasks->locked($actor, $data['assignment_id'], false);
            }
            if ($kind === 'extension') {
                abort_unless($assignment && in_array($assignment->status, Tasks::INCOMPLETE, true), 409);
                abort_if($assignment->due_at >= RecordTime::utc($data['requested_due_at']), 422, 'Choose a later deadline.');
                abort_if(DB::table('daily_entries')->where('assignment_id', $assignment->id)
                    ->where('kind', 'extension')->where('status', 'pending')->exists(), 409, 'An extension request is already pending.');
            }
            abort_if($kind === 'report' && $assignment, 422, 'Submit task reports from My Tasks.');

            $private = $kind === 'todo' || ($kind === 'note' && ($data['private'] ?? true));
            $solution = $kind === 'incident' ? ($data['solution'] ?? null) : null;
            $critical = $kind === 'incident' && ($data['critical'] ?? false);
            $status = match ($kind) {
                'todo' => 'open',
                'incident' => $solution ? 'resolved' : 'open',
                'report' => 'submitted',
                'extension' => 'pending',
                default => null,
            };
            $id = DB::table('daily_entries')->insertGetId([
                'assignment_id' => $assignment?->id, 'employee_id' => $actor->id, 'company_id' => $actor->company_id,
                'department_id' => $assignment ? $assignment->department_id : $actor->department_id,
                'branch_id' => $assignment ? $assignment->branch_id : $actor->branch_id,
                'kind' => $kind, 'request_id' => $data['request_id'] ?? null, 'title' => $data['title'], 'body' => $data['body'] ?? null, 'solution' => $solution,
                'private' => $private, 'critical' => $critical, 'status' => $status,
                'requested_due_at' => $kind === 'extension' ? RecordTime::utc($data['requested_due_at']) : null,
                'resolved_at' => $solution ? $now : null, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->files->attach($files, 'entry_id', $id);
            $event = $this->events->add($actor, 'create_'.$kind, $private ? null : $assignment?->id, $id, status: $status);
            $notice = match (true) {
                $critical => ['Critical incident recorded', 'A critical incident needs attention in Daily Records.'],
                $kind === 'report' => ['Daily report submitted', 'An employee daily report is ready for review.'],
                $kind === 'extension' => ['Task deadline extension requested', 'Review an employee extension request in Daily Records.'],
                default => null,
            };
            if ($notice) {
                $this->events->notifyManagers($assignment ?? DB::table('daily_entries')->find($id), "event:$event", ...$notice);
            }
            return $id;
        });
    }

    public function employeeAction(User $actor, int $id, array $data, array $uploads = []): void
    {
        $this->files->transaction($uploads, function (array $files) use ($actor, $id, $data) {
            $row = $this->query($actor, false)->where('e.id', $id)->lockForUpdate()->first();
            abort_unless($row, 404);
            abort_if($row->archived_at, 409, 'Restore the record before updating it.');
            $isSummary = $row->kind === 'report' && DB::table('daily_report_submissions')->where('entry_id', $id)->exists();
            $action = $data['action'];
            abort_if($isSummary && $action === 'resubmit', 409, 'Resubmit this summary from Daily Summary.');
            $now = RecordTime::now();
            $message = $data['message'] ?? null;
            $changes = match ($action) {
                'complete' => $row->kind === 'todo' && $row->status === 'open' ? ['status' => 'completed', 'resolved_at' => $now] : null,
                'reopen' => $row->kind === 'todo' && $row->status === 'completed' ? ['status' => 'open', 'resolved_at' => null] : null,
                'resolve' => $row->kind === 'incident' && $row->status === 'open' && !$row->category_id ? ['status' => 'resolved', 'resolved_at' => $now, 'solution' => $message] : null,
                'resubmit' => $row->kind === 'report' && !$row->assignment_id && $row->status === 'returned'
                    ? ['status' => 'submitted', 'body' => $message, 'reviewed_at' => null] : null,
                'comment' => [],
            };
            abort_if($changes === null, 409, 'This action is unavailable for the current record status.');
            if ($action === 'resubmit') {
                $this->access->requireReportSubmission($actor, RecordTime::day($row->created_at), $now);
            }
            DB::table('daily_entries')->where('id', $id)->update($changes + ['updated_at' => $now]);
            $this->files->attach($files, 'entry_id', $id);
            $details = $message ? ['message' => $message] : [];
            if ($action === 'resubmit') {
                $details['previous_body'] = $row->body;
            }
            $event = $this->events->add($actor, $action, $row->private ? null : $row->assignment_id, $id, $details, $changes['status'] ?? null);
            if ($action === 'resubmit') {
                $this->events->notifyManagers($row, "event:$event", 'Daily report resubmitted', 'A corrected daily report is ready for review.');
            }
        });
    }

    public function review(Admin|User $actor, int $id, array $data): void
    {
        DB::transaction(function () use ($actor, $id, $data) {
            $row = $this->query($actor, true)->where('e.id', $id)->first();
            abort_unless($row, 404);
            abort_if($row->kind === 'report' && DB::table('daily_report_submissions')->where('entry_id', $id)->exists(),
                409, 'Review this submission from Daily Submissions.');
            $assignment = $row->assignment_id && $row->kind === 'extension'
                ? $this->tasks->locked($actor, $row->assignment_id, true) : null;
            $row = $this->query($actor, true)->where('e.id', $id)->lockForUpdate()->first();
            abort_unless($row, 404);
            $action = $data['action'];
            $now = RecordTime::now();
            $details = array_filter(['message' => $data['message'] ?? null]);
            $status = match (true) {
                $row->kind === 'report' && !$row->assignment_id && $row->status === 'submitted' =>
                    $action === 'approve' ? 'approved' : 'returned',
                $row->kind === 'extension' && $row->status === 'pending' =>
                    $action === 'approve' ? 'approved' : 'declined',
                default => null,
            };
            abort_unless($status, 409, 'This record is unavailable for review.');
            if ($assignment && $status === 'approved') {
                abort_unless(in_array($assignment->status, Tasks::INCOMPLETE, true)
                    && $assignment->due_at < $row->requested_due_at, 409, 'The task deadline or status has changed.');
                $details += ['previous_due_at' => $assignment->due_at, 'due_at' => $row->requested_due_at];
                DB::table('daily_task_assignments')->where('id', $assignment->id)->update([
                    'due_at' => $row->requested_due_at, 'remind_at' => $this->tasks->deadlineReminder($row->requested_due_at, null),
                    'deadline_version' => $assignment->deadline_version + 1, 'updated_at' => $now,
                    'reminder_sent_at' => null, 'overdue_sent_at' => null,
                ]);
            }
            DB::table('daily_entries')->where('id', $id)->update(['status' => $status, 'reviewed_at' => $now, 'updated_at' => $now]);
            $event = $this->events->add($actor, 'review_'.$row->kind, $row->assignment_id, $id, $details, $status);
            $this->events->notify($row->employee_id, "event:$event", 'Daily record reviewed', 'Open Daily Records to view the review decision.');
        }, 3);
    }
}
