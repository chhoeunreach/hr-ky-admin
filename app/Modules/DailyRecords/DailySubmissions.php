<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DailySubmissions
{
    private const CONTENT = ['summary', 'challenges', 'solutions', 'pending_work', 'recommendations'];

    public function __construct(private Entries $entries, private RecordEvents $events, private RecordFiles $files,
        private RecordAccess $access, private Tasks $tasks) {}

    public function query(Admin|User $actor, bool $manager): Builder
    {
        return $this->entries->query($actor, $manager)
            ->join('daily_report_submissions as submission', 'submission.entry_id', '=', 'e.id');
    }

    public function preview(User $actor, ?string $date): array
    {
        $reporting = $this->access->reporting($actor);
        $date ??= $reporting['reporting_today'];
        $report = $this->query($actor, false)->where('submission.report_date', $date)
            ->first(['e.id', 'e.status', 'e.revision', 'submission.version', 'submission.draft']);
        $eligible = $this->eligible($actor, $date)->orderBy('e.occurred_at')->orderBy('e.id');
        $available = (clone $eligible)
            ->simplePaginate(25, ['e.id', 'e.kind', 'e.title', 'e.occurred_at', 'e.created_at']);
        $tasks = $this->dayTasks($actor, false, $actor->id, $date);

        return [
            ...$reporting, 'report_date' => $date,
            'report' => $report ? (array) $report + ['report_date' => $date] : null,
            'records' => $available, 'tasks' => $tasks,
            'eligible_ids' => request()->integer('page', 1) === 1 && request()->integer('task_page', 1) === 1
                ? (clone $eligible)->limit(250)->pluck('e.id') : [],
            'employee' => ['id' => $actor->id, 'name' => $actor->name],
        ];
    }

    private function dayTasks(Admin|User $actor, bool $manager, int $employee, string $date)
    {
        [$start, $end] = $this->dayBounds($date);
        return $this->tasks->query($actor, $manager)->where('a.employee_id', $employee)->where('a.created_at', '<', $end)
            ->whereNull('a.superseded_at')->whereNull('a.archived_at')
            ->where(fn (Builder $q) => $q
                ->whereIn('a.status', [...Tasks::INCOMPLETE, 'submitted'])
                ->orWhere(fn (Builder $done) => $done->where('a.completed_at', '>=', $start)->where('a.completed_at', '<', $end)))
            ->join('daily_tasks as task', 'task.id', '=', 'a.task_id')
            ->orderBy('a.due_at')->simplePaginate(20, ['a.id', 'task.title', 'a.status', 'a.completed_at', 'a.due_at'], 'task_page');

    }

    public function save(User $actor, array $data, array $uploads): int
    {
        return $this->files->transaction($uploads, function (array $files) use ($actor, $data) {
            $date = $data['report_date'];
            DB::table('users')->where('id', $actor->id)->lockForUpdate()->value('id');
            $now = RecordTime::now();
            $this->access->requireReportSubmission($actor, $date, $now);
            $report = $this->query($actor, false)->where('submission.report_date', $date)
                ->lockForUpdate()->first(['e.*', 'submission.version', 'submission.draft']);
            $ids = array_map('intval', $data['record_ids'] ?? []);
            sort($ids);
            $content = array_intersect_key($data, array_flip(self::CONTENT));
            $content['record_ids'] = $ids;
            $draft = json_encode($content, JSON_THROW_ON_ERROR);
            if ($report && $report->status === 'submitted' && $data['action'] === 'submit'
                && (int) $report->revision === (int) $data['revision'] + 1
                && json_decode($report->draft, true) == $content) {
                return $report->id;
            }
            abort_if(($report && !in_array($report->status, ['draft', 'returned'], true))
                || (int) ($report->revision ?? 0) !== (int) $data['revision'], 409, 'This report changed. Reload the current version.');
            $eligible = $this->eligible($actor, $date)->whereIn('e.id', $ids)->lockForUpdate()->get(['e.id', 'e.kind', 'e.title', 'e.body', 'e.solution', 'e.occurred_at', 'e.status']);
            if ($eligible->count() !== count($ids)) {
                throw ValidationException::withMessages(['record_ids' => 'Choose eligible activity and incident records from this date.']);
            }

            $submitting = $data['action'] === 'submit';
            $version = ($report?->version ?? 0) + (int) $submitting;
            $status = $submitting ? 'submitted' : ($report?->status ?? 'draft');
            $changes = ['status' => $status, 'private' => $status === 'draft',
                'revision' => ($report?->revision ?? 0) + 1, 'updated_at' => $now];

            // Returned submissions keep the previously submitted text visible until a correction is submitted.
            if ($submitting || $status === 'draft') {
                $changes['body'] = $data['summary'] ?? null;
            }
            if ($submitting) {
                $changes['reviewed_at'] = null;
            }

            $submission = [
                'draft' => $draft, 'version' => $version,
                ...($submitting ? ['submitted_at' => $now, 'reviewed_by' => null, 'reviewer_type' => null] : []),
            ];
            if ($report) {
                $id = $report->id;
                DB::table('daily_entries')->where('id', $id)->update($changes);
                DB::table('daily_report_submissions')->where('entry_id', $id)->update($submission);
            } else {
                $id = DB::table('daily_entries')->insertGetId($changes + [
                    'employee_id' => $actor->id, 'company_id' => $actor->company_id,
                    'department_id' => $actor->department_id, 'branch_id' => $actor->branch_id, 'kind' => 'report',
                    'title' => 'Daily summary · '.$date, 'occurred_at' => RecordTime::utc($date), 'created_at' => $now,
                ]);
                DB::table('daily_report_submissions')->insert($submission + [
                    'entry_id' => $id, 'employee_id' => $actor->id, 'company_id' => $actor->company_id, 'report_date' => $date,
                ]);
            }
            $this->files->attach($files, 'entry_id', $id);
            if ($submitting) {
                unset($content['record_ids']);
                $content['source_snapshots'] = $eligible->keyBy('id')->all();
                $content['attachments'] = DB::table('daily_record_files')->where('entry_id', $id)->pluck('id')->all();
                $versionId = DB::table('daily_report_versions')->insertGetId([
                    'submission_id' => $id, 'version' => $version, 'content' => json_encode($content, JSON_THROW_ON_ERROR),
                    'submitted_at' => $now,
                ]);
                if ($ids) {
                    DB::table('daily_report_items')->insert(array_map(fn ($entry) => ['version_id' => $versionId, 'entry_id' => $entry], $ids));
                }
                $event = $this->events->add($actor, 'submit_daily_summary', null, $id, ['version' => $version], $status);
                $this->events->notifyManagers(DB::table('daily_entries')->find($id), "event:$event",
                    'Daily summary submitted', 'An employee daily summary is ready for review.');
            }
            return $id;
        });
    }

    public function review(Admin|User $actor, int $id, array $data): void
    {
        DB::transaction(function () use ($actor, $id, $data) {
            $report = $this->query($actor, true)->where('e.id', $id)->lockForUpdate()
                ->first(['e.*', 'submission.version']);
            abort_unless($report, 404);
            abort_unless((int) $report->version === (int) $data['version'], 409, 'This submission has a newer version.');
            $action = $data['action'];
            $next = match ($action) {
                'start' => $report->status === 'submitted' ? 'under_review' : null,
                'approve' => in_array($report->status, ['submitted', 'under_review'], true) ? 'approved' : null,
                'return' => in_array($report->status, ['submitted', 'under_review'], true) ? 'returned' : null,
                'comment' => $report->status,
            };
            abort_unless($next, 409);
            $now = RecordTime::now();
            $final = in_array($action, ['approve', 'return'], true);
            DB::table('daily_entries')->where('id', $id)->update([
                'status' => $next, 'updated_at' => $now, 'revision' => $report->revision + 1,
                ...($final ? ['reviewed_at' => $now] : []),
            ]);
            if ($action !== 'comment') {
                DB::table('daily_report_submissions')->where('entry_id', $id)->update([
                    'reviewed_by' => $actor->id, 'reviewer_type' => $this->access->type($actor),
                ]);
            }
            $event = $this->events->add($actor, 'review_daily_summary', null, $id, [
                'decision' => $action, 'version' => $report->version, 'message' => $data['message'] ?? null,
            ], $next);
            if ($final) {
                $this->events->notify($report->employee_id, "event:$event",
                    $next === 'returned' ? 'Daily summary returned' : 'Daily summary approved',
                    'Open Daily Records to see the review.');
            }
        }, 3);
    }

    public function detail(Admin|User $actor, int $id, bool $manager, ?int $wantedVersion = null): array
    {
        $report = $this->query($actor, $manager)->where('e.id', $id)
            ->join('users as staff', 'staff.id', '=', 'e.employee_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'e.department_id')
            ->leftJoin('branches as branch', 'branch.id', '=', 'e.branch_id')->first([
            'e.id', 'e.employee_id', 'e.status', 'e.revision', 'e.title', 'e.reviewed_at',
            'staff.name as employee', 'dept.dept_name as department', 'branch.name as location',
            'submission.report_date', 'submission.version', 'submission.submitted_at', 'submission.draft',
        ]);
        abort_unless($report, 404);
        $picked = $wantedVersion ?? $report->version;
        $version = $picked ? DB::table('daily_report_versions')->where('submission_id', $id)->where('version', $picked)->first() : null;
        abort_if($picked && !$version, 404);
        $items = collect();
        $content = $version ? json_decode($version->content, true) : null;
        if ($version) {
            $items = $this->entries->query($actor, $manager)
                ->join('daily_report_items as item', 'item.entry_id', '=', 'e.id')
                ->where('item.version_id', $version->id)->where('e.private', false)->where('e.sensitive', false)
                ->orderBy('e.occurred_at')->get(['e.id', 'e.kind', 'e.title', 'e.body', 'e.solution', 'e.occurred_at', 'e.status'])
                ->map(fn ($row) => (object) ($content['source_snapshots'][$row->id] ?? (array) $row));
        }
        unset($content['source_snapshots']);
        $draft = $manager ? null : json_decode($report->draft, true);
        unset($report->draft);

        return [
            'report' => $report, 'draft' => $draft,
            'content' => $content,
            'selected_version' => $version?->version, 'records' => $items,
            'tasks' => $this->dayTasks($actor, $manager, $report->employee_id, $report->report_date),
            'versions' => DB::table('daily_report_versions')->where('submission_id', $id)->orderByDesc('version')
                ->simplePaginate(20, ['version', 'submitted_at'], 'versions'),
        ];
    }

    public function missing(Admin|User $actor, string $date, array $filters): Builder
    {
        [$start, $end] = $this->dayBounds($date);
        $records = $this->entries->query($actor, true)
            ->join('users as employee', 'employee.id', '=', 'e.employee_id')
            ->whereIn('e.kind', ['activity', 'incident'])->where('e.sensitive', false)
            ->where('e.occurred_at', '>=', $start)->where('e.occurred_at', '<', $end)
            ->whereNotExists(fn (Builder $q) => $q->selectRaw('1')->from('daily_report_submissions as s')
                ->whereColumn('s.employee_id', 'e.employee_id')->whereColumn('s.company_id', 'e.company_id')
                ->where('s.report_date', $date)->whereNotNull('s.submitted_at'));
        foreach (['employee_id', 'department_id', 'branch_id'] as $field) {
            $records->when($filters[$field] ?? null, fn ($q, $id) => $q->where("e.$field", $id));
        }
        return $records->when($filters['search'] ?? null, fn ($q, $term) => $q->where('employee.name', 'like', '%'.$term.'%'))
            ->groupBy('e.employee_id', 'employee.name')->select('e.employee_id', 'employee.name as employee')
            ->selectRaw('COUNT(*) as eligible_records')->orderBy('employee.name')->orderBy('e.employee_id');
    }

    private function eligible(User $actor, string $date): Builder
    {
        [$start, $end] = $this->dayBounds($date);
        return $this->entries->query($actor, false)->whereIn('e.kind', ['activity', 'incident'])
            ->where('e.private', false)->where('e.sensitive', false)->whereNull('e.archived_at')
            ->where(fn (Builder $q) => $q->where(fn (Builder $occurred) => $occurred->where('e.occurred_at', '>=', $start)->where('e.occurred_at', '<', $end))
                ->orWhere(fn (Builder $legacy) => $legacy->whereNull('e.occurred_at')->where('e.created_at', '>=', $start)->where('e.created_at', '<', $end)));
    }

    private function dayBounds(string $date): array
    {
        $start = CarbonImmutable::parse($date, RecordTime::ZONE);
        return [$start->utc()->toDateTimeString(), $start->addDay()->utc()->toDateTimeString()];
    }
}
