<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class WorkRecordsAdminController
{
    public function __construct(private RecordAccess $access, private RecordRead $read, private Entries $entries,
        private CategoryCatalog $catalog, private WorkRecords $work, private DailySubmissions $submissions,
        private RecordDirectory $directory) {}

    public function incident(int $entry)
    {
        $actor = $this->actor();
        $case = $this->entries->query($actor, true)->where('e.kind', 'incident')->where('e.id', $entry)->first();
        abort_unless($case, 404);
        return response()->view('admin.daily-records.incident', $this->read->entry($actor, $entry, true) + [
            'states' => $this->work->transitions($case),
            'findings' => $case->sensitive && $this->access->allows($actor, RecordAccess::SENSITIVE) ? WorkRecords::FINDINGS : [],
            'categories' => $this->catalog->forDepartment($case->department_id, 'incident'),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function incidentAction(Request $request, int $entry)
    {
        if ($request->filled('fields_json')) {
            $fields = json_decode($request->input('fields_json'), true, 32);
            if (!is_array($fields)) {
                throw ValidationException::withMessages(['fields_json' => 'Use a JSON object for field values.']);
            }
            $request->merge(['fields' => $fields]);
        }
        $this->work->incidentAction($this->actor(), $entry, WorkInput::incidentAction($request), $request->file('files', []));
        return redirect()->route('admin.daily-records.incident', $entry)->with('success', 'Incident updated.');
    }

    public function submissions(Request $request)
    {
        $actor = $this->actor();
        $filters = $request->validate([
            'status' => 'nullable|in:pending,submitted,under_review,returned,approved,unsubmitted',
            'date' => 'nullable|date_format:Y-m-d', 'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'employee_id' => 'nullable|integer|min:1', 'department_id' => 'nullable|integer|min:1',
            'branch_id' => 'nullable|integer|min:1', 'reviewer_id' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|in:25,50,100,200,all',
        ]);
        $status = $filters['status'] ?? 'pending';
        $q = $this->submissions->query($actor, true)->join('users as employee', 'employee.id', '=', 'e.employee_id');
        foreach (['employee_id', 'department_id', 'branch_id'] as $field) {
            $q->when($filters[$field] ?? null, fn ($q, $id) => $q->where("e.$field", $id));
        }
        $q->when($filters['reviewer_id'] ?? null, fn ($q, $id) => $q->where('submission.reviewed_by', $id));
        $q->when($filters['search'] ?? null, fn ($q, $term) => $q->where('employee.name', 'like', '%'.$term.'%'));
        $q->when($filters['date'] ?? null, fn ($q, $date) => $q->where('submission.report_date', $date));
        $q->when($filters['from'] ?? null, fn ($q, $date) => $q->where('submission.report_date', '>=', $date));
        $q->when($filters['to'] ?? null, fn ($q, $date) => $q->where('submission.report_date', '<=', $date));
        $day = $filters['date'] ?? \Carbon\CarbonImmutable::now(RecordTime::ZONE)->subDay()->toDateString();
        $statusCounts = (clone $q)->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN e.status = 'submitted' THEN 1 ELSE 0 END) as submitted")
            ->selectRaw("SUM(CASE WHEN e.status = 'under_review' THEN 1 ELSE 0 END) as under_review")
            ->selectRaw("SUM(CASE WHEN e.status = 'approved' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN e.status = 'returned' THEN 1 ELSE 0 END) as returned")
            ->first();
        $missingFilters = $filters;
        unset($missingFilters['reviewer_id']);
        $missingCount = DB::query()->fromSub($this->submissions->missing($actor, $day, $missingFilters), 'daily_missing')->count();
        if ($status === 'pending') {
            $q->whereIn('e.status', ['submitted', 'under_review']);
        } elseif ($status !== 'unsubmitted') {
            $q->where('e.status', $status);
        }
        $rowsQuery = $status === 'unsubmitted'
            ? $this->submissions->missing($actor, $day, $filters)
            : $q->orderByDesc('submission.report_date')->orderByDesc('e.id');
        $perPage = $this->resolvePerPage($rowsQuery, $filters['per_page'] ?? null, 25);
        return response()->view('admin.daily-records.submissions', $this->directory->groups($actor, false) + [
            // The submissions view displays the matching total, so use a length-aware
            // paginator here instead of SimplePaginator (which has no total()).
            'rows' => $status === 'unsubmitted'
                ? $rowsQuery->paginate($perPage)->withQueryString()
                : $rowsQuery->paginate($perPage, [
                    'e.id', 'e.status', 'employee.name as employee', 'submission.report_date', 'submission.version',
                ])->withQueryString(),
            'filters' => $filters, 'status' => $status, 'day' => $day,
            'statusCounts' => $statusCounts, 'missingCount' => $missingCount,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function branchRoutes(Request $request)
    {
        $actor = $this->actor();
        $filters = $request->validate([
            'status' => 'nullable|in:ongoing,in_progress,completed,cancelled',
            'date' => 'nullable|date_format:Y-m-d',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'employee_id' => 'nullable|integer|min:1',
            'department_id' => 'nullable|integer|min:1',
            'branch_id' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|in:25,50,100,200,all',
        ]);
        $query = $this->entries->query($actor, true)
            ->join('users as employee', 'employee.id', '=', 'e.employee_id')
            ->where('e.kind', 'activity')->whereJsonContains('e.details->fields->mission_type', 'branch_route')
            ->when($filters['employee_id'] ?? null, fn ($q, $id) => $q->where('e.employee_id', $id))
            ->when($filters['department_id'] ?? null, fn ($q, $id) => $q->where('e.department_id', $id))
            ->when($filters['branch_id'] ?? null, fn ($q, $id) => $q->where('e.branch_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(fn ($search) => $search
                ->where('employee.name', 'like', '%'.$term.'%')->orWhere('e.title', 'like', '%'.$term.'%')
                ->orWhere('e.details', 'like', '%'.$term.'%')))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('e.occurred_at', $date))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('e.occurred_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('e.occurred_at', '<=', $date));
        $total = (clone $query)->count();
        $completed = (clone $query)->where('e.details->fields->status', 'completed')->count();
        $cancelled = (clone $query)->where('e.details->fields->status', 'cancelled')->count();
        $ongoing = (clone $query)->whereIn('e.details->fields->status', ['ongoing', 'in_progress'])->count();
        $employees = (clone $query)->distinct()->count('e.employee_id');
        if (isset($filters['status'])) {
            $query->where('e.details->fields->status', $filters['status']);
        }
        $perPage = $this->resolvePerPage($query, $filters['per_page'] ?? null, 25);
        $rows = $query->orderByDesc('e.occurred_at')->orderByDesc('e.id')->paginate($perPage, [
            'e.id', 'e.title', 'e.body', 'e.details', 'e.occurred_at', 'e.ended_at', 'e.employee_id',
            'e.department_id', 'e.branch_id', 'employee.name as employee',
        ])->withQueryString();
        $files = DB::table('daily_record_files')->whereIn('entry_id', $rows->getCollection()->pluck('id'))
            ->where('restricted', false)->orderBy('id')->get(['id', 'entry_id', 'name', 'bytes'])->groupBy('entry_id');
        foreach ($rows as $row) {
            $rawRoute = json_decode($row->details ?? '{}', true)['fields'] ?? [];
            $row->route = BranchRouteCalculator::enrich($rawRoute);
            $row->attachments = $files->get($row->id, collect());
        }
        return response()->view('admin.daily-records.branch-routes', $this->directory->groups($actor, false) + [
            'rows' => $rows, 'filters' => $filters,
            'summary' => compact('total', 'completed', 'cancelled', 'ongoing', 'employees'),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function submission(Request $request, int $entry)
    {
        $actor = $this->actor();
        $version = $request->validate(['version' => 'nullable|integer|min:1'])['version'] ?? null;
        $data = $this->submissions->detail($actor, $entry, true, $version);
        return response()->view('admin.daily-records.submission', $data + $this->read->entry($actor, $entry, true))
            ->header('Cache-Control', 'private, no-store');
    }

    public function review(Request $request, int $entry)
    {
        try {
            $this->submissions->review($this->actor(), $entry, WorkInput::review($request));
        } catch (HttpException $exception) {
            if ($request->expectsJson() || $exception->getStatusCode() !== 409) {
                throw $exception;
            }
            return back()->withErrors(['version' => $exception->getMessage() ?: 'The submission is no longer available for this action.']);
        }
        return redirect()->route('admin.daily-records.submission', $entry)->with('success', 'Review saved.');
    }

    public function categories(Request $request)
    {
        $this->actor(RecordAccess::CATEGORIES);
        $filters = $request->validate([
            'edit' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:100',
            'kind' => 'nullable|in:activity,incident',
            'is_active' => 'nullable|in:0,1',
            'per_page' => 'nullable|in:30,50,100,200,all',
        ]);
        $id = $filters['edit'] ?? null;
        $query = DB::table('daily_record_categories')
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $query->where(fn ($nested) => $nested->where('name_en', 'like', $term)->orWhere('name_km', 'like', $term));
            })
            ->when($filters['kind'] ?? null, fn ($query, $kind) => $query->where('kind', $kind))
            ->when(($filters['is_active'] ?? null) !== null, function ($query) use ($filters) {
                $query->where('active', (int) $filters['is_active']);
            })
            ->orderBy('kind')->orderBy('parent_id')->orderBy('position')->orderBy('id');
        $perPage = $this->resolvePerPage($query, $filters['per_page'] ?? null, 30);
        return response()->view('admin.daily-records.categories', [
            'categories' => $query->paginate($perPage)->withQueryString(),
            'filters' => $filters,
            'parents' => DB::table('daily_record_categories')->whereNull('parent_id')->orderBy('name_en')->get(['id', 'kind', 'name_en']),
            'departments' => DB::table('departments')->orderBy('dept_name')->get(['id', 'dept_name']),
            'editing' => $id ? DB::table('daily_record_categories')->find($id) : null,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function categorySave(Request $request)
    {
        $this->actor(RecordAccess::CATEGORIES);
        $id = $request->validate(['id' => 'nullable|integer|min:1'])['id'] ?? null;
        $id = $this->catalog->save($id, WorkInput::category($request));
        return redirect()->route('admin.daily-records.categories', ['edit' => $id])->with('success', 'Category saved.');
    }

    private function resolvePerPage($query, mixed $requested, int $default): int
    {
        if ((string) ($requested ?? '') === 'all') {
            return max(1, (clone $query)->count());
        }
        $value = filter_var($requested, FILTER_VALIDATE_INT);
        return $value !== false && $value > 0 ? $value : $default;
    }

    private function actor(string $ability = RecordAccess::MANAGE): Admin|User
    {
        $actor = Auth::guard('admin')->user() ?: Auth::user();
        abort_unless(($actor instanceof Admin || $actor instanceof User) && $this->access->allows($actor, $ability), 403);
        return $actor;
    }
}
