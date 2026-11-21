<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class Reporting
{
    public const VIEWS = ['tasks', 'daily', 'monthly', 'performance', 'calendar', 'department', 'location', 'timeline'];

    public function __construct(private RecordRead $read, private Entries $entries, private Tasks $tasks) {}

    public function period(string $view, array $filters): array
    {
        $today = CarbonImmutable::now(RecordTime::ZONE);
        $date = $filters['date'] ?? $today->toDateString();
        $month = $filters['month'] ?? $today->format('Y-m');
        [$from, $to] = match ($view) {
            'daily' => [$date, $date],
            'monthly', 'performance', 'calendar' => [$month.'-01', CarbonImmutable::parse($month.'-01', RecordTime::ZONE)->endOfMonth()->toDateString()],
            default => [null, null],
        };
        $from = $filters['from'] ?? $from;
        $to = $filters['to'] ?? $to;

        return [
            $from ? RecordTime::utc($from) : null,
            $to ? CarbonImmutable::parse($to, RecordTime::ZONE)->addDay()->utc()->toDateTimeString() : null,
        ];
    }

    public function queries(Admin|User $actor, string $view, array $filters): array
    {
        [$from, $to] = $this->period($view, $filters);
        $tasks = $this->read->assignments($actor, true)
            ->join('users as staff', 'staff.id', '=', 'a.employee_id')
            ->leftJoin('departments as department', 'department.id', '=', 'a.department_id')
            ->leftJoin('branches as location', 'location.id', '=', 'a.branch_id')
            ->addSelect('staff.name as employee', 'department.dept_name as department', 'location.name as location')
            ->selectSub($this->read->pendingReport()->whereColumn('assignment_id', 'a.id')->select('id')->limit(1), 'pending_report_id');
        $entries = $this->entries->query($actor, true)
            ->join('users as staff', 'staff.id', '=', 'e.employee_id')
            ->leftJoin('departments as department', 'department.id', '=', 'e.department_id')
            ->leftJoin('branches as location', 'location.id', '=', 'e.branch_id')
            ->select('e.*', 'staff.name as employee', 'department.dept_name as department', 'location.name as location');

        $this->filters($tasks, 'a', 't.title', $filters);
        $this->filters($entries, 'e', 'e.title', $filters);
        if (!empty($filters['kind'])) {
            if ($filters['kind'] === 'task') {
                $entries->whereRaw('1 = 0');
            } else {
                $tasks->whereRaw('1 = 0');
                $entries->where('e.kind', $filters['kind']);
            }
        }
        foreach (['category_id', 'subcategory_id', 'reviewer_id'] as $field) {
            if (!empty($filters[$field])) {
                $tasks->whereRaw('1 = 0');
                if ($field === 'reviewer_id') {
                    $entries->where(fn ($q) => $q->where('e.reviewer_id', $filters[$field])
                        ->orWhereIn('e.id', DB::table('daily_report_submissions')->where('reviewed_by', $filters[$field])->select('entry_id')));
                } else {
                    $entries->where("e.$field", $filters[$field]);
                }
            }
        }
        if (!empty($filters['priority'])) {
            $tasks->where('t.priority', $filters['priority']);
            $entries->where('e.priority', $filters['priority']);
        }
        $status = $filters['status'] ?? null;
        if ($status === 'archived') {
            $tasks->where(fn (Builder $q) => $q->whereNotNull('a.archived_at')->orWhereNotNull('a.superseded_at'));
        } else {
            $tasks->whereNull('a.superseded_at');
            if (in_array($view, ['tasks', 'calendar'], true)) {
                $tasks->whereNull('a.archived_at');
            }
        }
        if ($status === 'overdue') {
            $this->tasks->overdue($tasks, min($to ?? RecordTime::now(), RecordTime::now()));
        } elseif ($status === 'outstanding_incidents') {
            $tasks->whereRaw('1 = 0');
            $entries->where('e.kind', 'incident')->whereNotIn('e.status', ['resolved', 'closed', 'cancelled']);
        } elseif ($status === 'review') {
            $tasks->where('a.status', 'submitted');
            $entries->where(fn (Builder $q) => $q
                ->where(fn (Builder $reports) => $reports->where('e.kind', 'report')->whereIn('e.status', ['submitted', 'under_review']))
                ->orWhere(fn (Builder $extensions) => $extensions->where('e.kind', 'extension')->where('e.status', 'pending')));
        } elseif ($status && $status !== 'archived') {
            $tasks->where('a.status', $status);
            $entries->where('e.status', $status);
        }

        if ($from || $to) {
            $tasks->where(function (Builder $q) use ($from, $to, $view) {
                $this->range($q, $view === 'calendar' ? 'a.due_at' : 'a.created_at', $from, $to);
                if ($view === 'calendar') {
                    return;
                }
                $q->orWhere($this->eventExists('assignment_id', 'a.id', null, $from, $to))
                    ->orWhere(fn (Builder $due) => $this->range($due, 'a.due_at', $from, $to))
                    ->orWhere(function (Builder $pending) use ($to) {
                        $pending->whereIn('a.status', [...Tasks::INCOMPLETE, 'submitted']);
                        if ($to) {
                            $pending->where('a.created_at', '<', $to);
                        }
                    });
            });
            $entries->where(function (Builder $period) use ($from, $to) {
                $period->where(fn (Builder $dated) => $this->range($dated->whereNotNull('e.occurred_at'), 'e.occurred_at', $from, $to))
                    ->orWhere(fn (Builder $legacy) => $legacy->whereNull('e.occurred_at')
                        ->where(function (Builder $q) use ($from, $to) {
                            $this->range($q, 'e.created_at', $from, $to);
                            $q->orWhere($this->eventExists('entry_id', 'e.id', null, $from, $to))
                                ->orWhere(function (Builder $pending) use ($to) {
                                    $pending->whereIn('e.status', ['submitted', 'pending', 'open']);
                                    if ($to) {
                                        $pending->where('e.created_at', '<', $to);
                                    }
                                });
                        }));
            });
        }

        if ($status === 'overdue') {
            // Related records remain available for an overdue assignment.
            $entries->whereIn('e.assignment_id', (clone $tasks)->select('a.id'));
        } elseif ($status === 'archived') {
            $entries->whereIn('e.assignment_id', (clone $tasks)->select('a.id'));
        }

        return [$tasks, $entries];
    }

    public function summary(Builder $tasks, Builder $entries, ?string $from, ?string $to, string $view = 'tasks'): array
    {
        $counts = $this->taskCounts(clone $tasks, $from, $to)->first();
        $entryCounts = (clone $entries)->select([])->selectRaw("COUNT(CASE WHEN e.kind = 'incident' AND e.status NOT IN ('resolved','closed','cancelled') THEN 1 END) as incidents,
                COUNT(CASE WHEN e.kind = 'incident' THEN 1 END) as incident_total,
                COUNT(CASE WHEN e.kind = 'incident' AND e.status IN ('resolved','closed') THEN 1 END) as resolved,
                COUNT(CASE WHEN e.kind = 'activity' THEN 1 END) as activities")
            ->selectRaw("COUNT(CASE WHEN (e.kind = 'report' AND e.assignment_id IS NULL AND e.status IN ('submitted','under_review'))
                OR (e.kind = 'extension' AND e.status = 'pending') THEN 1 END) as pending")
            ->first();

        $summary = [
            'Total activities' => $entryCounts->activities,
            'Total incidents' => $entryCounts->incident_total,
            'Resolved incidents' => $entryCounts->resolved,
            'Total tasks' => $counts->distinct_tasks,
            'Total assignments' => $counts->total,
            'Assigned' => $counts->assigned,
            'In progress' => $counts->in_progress,
            'Work completed' => $counts->completed,
            'Submitted' => $counts->submitted,
            'Approved' => $counts->approved,
            'Overdue' => $counts->overdue,
            'Open incidents' => $entryCounts->incidents,
            'Pending reviews' => $counts->submitted + $entryCounts->pending,
        ];
        if (in_array($view, ['daily', 'monthly'], true)) {
            $summary += [
                'Outstanding tasks' => $counts->outstanding,
                'Reports submitted' => $this->range((clone $entries)->where('e.kind', 'report'), 'e.created_at', $from, $to)->count(),
                'Reports approved' => (clone $entries)->where('e.kind', 'report')
                    ->where($this->eventExists('entry_id', 'e.id', ['approve', 'review_report', 'review_daily_summary'], $from, $to, 'approved'))->count(),
                'Reports returned' => (clone $entries)->where('e.kind', 'report')
                    ->where($this->eventExists('entry_id', 'e.id', ['return', 'review_report', 'review_daily_summary'], $from, $to, 'returned'))->count(),
                'Incidents resolved' => $this->range((clone $entries)->where('e.kind', 'incident')
                    ->whereNotNull('e.resolved_at'), 'e.resolved_at', $from, $to)->count(),
            ];
        }
        return $summary;
    }

    public function grouped(Builder $tasks, Builder $entries, string $group, ?string $from, ?string $to): Builder
    {
        [$field, $label] = match ($group) {
            'department' => ['department_id', 'department.dept_name'],
            'location' => ['branch_id', 'location.name'],
            default => ['employee_id', 'staff.name'],
        };
        $taskStats = $this->taskCounts($tasks, $from, $to)
            ->addSelect("a.$field as group_id", "$label as label")->groupBy("a.$field", $label);
        $incidentStats = $this->range($entries->where('e.kind', 'incident'), 'e.resolved_at', $from, $to)
            ->whereNotNull('e.resolved_at')->select("e.$field as group_id", "$label as label")
            ->selectRaw('COUNT(*) as resolved')->groupBy("e.$field", $label);
        $groups = DB::query()->fromSub(clone $taskStats, 't')->select('group_id', 'label')
            ->union(DB::query()->fromSub(clone $incidentStats, 'i')->select('group_id', 'label'));

        $result = DB::query()->fromSub($groups, 'g')->select('g.group_id', 'g.label')
            ->leftJoinSub($taskStats, 't', fn ($join) => $join->on('g.group_id', '=', 't.group_id')
                ->orOn(fn ($blank) => $blank->whereNull('g.group_id')->whereNull('t.group_id')))
            ->leftJoinSub($incidentStats, 'i', fn ($join) => $join->on('g.group_id', '=', 'i.group_id')
                ->orOn(fn ($blank) => $blank->whereNull('g.group_id')->whereNull('i.group_id')));

        foreach (['total', 'new_assignments', 'approved', 'on_time', 'overdue', 'returned', 'submitted'] as $metric) {
            $result->selectRaw("COALESCE(t.$metric, 0) as $metric");
        }
        return $result->selectRaw('COALESCE(i.resolved, 0) as resolved')->orderBy('g.label')->orderBy('g.group_id');
    }

    public function timeline(Builder $tasks, Builder $entries, ?string $from, ?string $to): Builder
    {
        $taskEvents = DB::table('daily_record_events as event')
            ->joinSub($tasks->select('a.id', 'staff.name as employee', 't.title'), 'task', 'task.id', '=', 'event.assignment_id')
            ->whereNull('event.entry_id')->select('event.id', 'event.created_at', 'event.action', 'event.status',
                'event.assignment_id', 'event.entry_id', 'task.employee', 'task.title');
        $entryEvents = DB::table('daily_record_events as event')
            ->joinSub($entries->select('e.id', 'staff.name as employee', 'e.title'), 'entry', 'entry.id', '=', 'event.entry_id')
            ->select('event.id', 'event.created_at', 'event.action', 'event.status',
                'event.assignment_id', 'event.entry_id', 'entry.employee', 'entry.title');
        $timeline = DB::query()->fromSub($taskEvents->unionAll($entryEvents), 'timeline');
        return $this->range($timeline, 'created_at', $from, $to)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function exportRows(Builder $tasks, Builder $entries): Builder
    {
        $tasks = $tasks->select([
            'a.id', 'staff.name as employee', 'department.dept_name as department', 'location.name as location', 't.title', 'a.status',
            'a.progress', 'a.due_at', 'a.created_at', 'a.updated_at',
        ])->selectRaw("'Task' as kind, CASE WHEN a.instructions_override IS NULL THEN t.instructions ELSE a.instructions_override END as body,
            NULL as solution, NULL as occurred_at");
        $entries = $entries->select('e.id', 'staff.name as employee', 'department.dept_name as department', 'location.name as location',
            'e.title', 'e.status')->selectRaw('NULL as progress, e.requested_due_at as due_at')
            ->addSelect('e.created_at', 'e.updated_at', 'e.kind', 'e.body', 'e.solution', 'e.occurred_at');
        return DB::query()->fromSub($tasks->unionAll($entries), 'report_rows')->orderByDesc('updated_at')->orderBy('kind')->orderBy('id');
    }

    private function taskCounts(Builder $tasks, ?string $from, ?string $to): Builder
    {
        $at = min($to ?? RecordTime::now(), RecordTime::now());
        $dateConditions = [];
        $bindings = [];
        foreach (['created_at', 'approved_at', 'completed_at'] as $column) {
            $dateConditions[$column] = "a.$column IS NOT NULL";
            $bindings[$column] = [];
            if ($from) {
                $dateConditions[$column] .= " AND a.$column >= ?";
                $bindings[$column][] = $from;
            }
            if ($to) {
                $dateConditions[$column] .= " AND a.$column < ?";
                $bindings[$column][] = $to;
            }
        }

        $completed = $this->range(DB::table('daily_record_events')->selectRaw('1')
            ->whereColumn('assignment_id', 'a.id')->where('action', 'complete_work'), 'created_at', $from, $to);
        $review = $this->range(DB::table('daily_record_events')->select('on_time')
            ->whereColumn('assignment_id', 'a.id')->where('action', 'approve'), 'created_at', $from, $to);
        $period = $from || $to;
        $approved = $period ? 'EXISTS ('.$review->toSql().')' : $dateConditions['approved_at'];
        $approvedBindings = $period ? $review->getBindings() : $bindings['approved_at'];
        $onTime = $period ? '('.$review->orderByDesc('id')->limit(1)->toSql().') = TRUE' : "$approved AND a.completed_at <= a.due_at";
        $completion = $period ? 'EXISTS ('.$completed->toSql().')' : "a.status <> 'cancelled' AND ".$dateConditions['completed_at'];
        $completionBindings = $period ? $completed->getBindings() : $bindings['completed_at'];

        $returned = DB::table('daily_record_events')->selectRaw('1')->whereColumn('assignment_id', 'a.id')->where('action', 'return');
        $this->range($returned, 'created_at', $from, $to);
        return $tasks->select([])->selectRaw('COUNT(*) as total, COUNT(DISTINCT a.task_id) as distinct_tasks')
            ->selectRaw("COUNT(CASE WHEN {$dateConditions['created_at']} THEN 1 END) as new_assignments", $bindings['created_at'])
            ->selectRaw("COUNT(CASE WHEN a.status IN ('assigned', 'acknowledged') THEN 1 END) as assigned")
            ->selectRaw("COUNT(CASE WHEN a.status = 'in_progress' THEN 1 END) as in_progress")
            ->selectRaw("COUNT(CASE WHEN a.status = 'submitted' THEN 1 END) as submitted")
            ->selectRaw("COUNT(CASE WHEN a.status IN ('assigned','acknowledged','in_progress','returned','on_hold') THEN 1 END) as outstanding")
            ->selectRaw("COUNT(CASE WHEN $completion THEN 1 END) as completed", $completionBindings)
            ->selectRaw("COUNT(CASE WHEN $approved THEN 1 END) as approved", $approvedBindings)
            ->selectRaw("COUNT(CASE WHEN $onTime THEN 1 END) as on_time", $approvedBindings)
            ->selectRaw("COUNT(CASE WHEN a.status IN ('assigned','acknowledged','in_progress','returned','on_hold') AND a.completed_at IS NULL AND a.due_at < ? AND a.archived_at IS NULL THEN 1 END) as overdue", [$at])
            ->selectRaw('COUNT(CASE WHEN EXISTS ('.$returned->toSql().') THEN 1 END) as returned', $returned->getBindings());
    }

    private function filters(Builder $query, string $alias, string $title, array $filters): void
    {
        foreach (['employee_id', 'department_id', 'branch_id'] as $field) {
            if (!empty($filters[$field])) {
                $query->where("$alias.$field", $filters[$field]);
            }
        }
        if (!empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(fn (Builder $search) => $search->where($title, 'like', $term)->orWhere('staff.name', 'like', $term));
        }
    }

    private function eventExists(string $link, string $id, ?array $actions, ?string $from, ?string $to, ?string $status = null): \Closure
    {
        return function (Builder $query) use ($link, $id, $actions, $from, $to, $status) {
            $query->whereExists(function (Builder $history) use ($link, $id, $actions, $from, $to, $status) {
                $history->selectRaw('1')->from('daily_record_events')->whereColumn($link, $id);
                if ($actions) {
                    $history->whereIn('action', $actions);
                }
                if ($status) {
                    $history->where('status', $status);
                }
                $this->range($history, 'created_at', $from, $to);
            });
        };
    }

    private function range(Builder $query, string $field, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->where($field, '>=', $from);
        }
        if ($to) {
            $query->where($field, '<', $to);
        }
        return $query;
    }
}
