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
        ]);
        $status = $filters['status'] ?? 'pending';
        $q = $this->submissions->query($actor, true)->join('users as employee', 'employee.id', '=', 'e.employee_id');
        if ($status === 'pending') {
            $q->whereIn('e.status', ['submitted', 'under_review']);
        } elseif ($status !== 'unsubmitted') {
            $q->where('e.status', $status);
        }
        foreach (['employee_id', 'department_id', 'branch_id'] as $field) {
            $q->when($filters[$field] ?? null, fn ($q, $id) => $q->where("e.$field", $id));
        }
        $q->when($filters['reviewer_id'] ?? null, fn ($q, $id) => $q->where('submission.reviewed_by', $id));
        $q->when($filters['search'] ?? null, fn ($q, $term) => $q->where('employee.name', 'like', '%'.$term.'%'));
        $q->when($filters['date'] ?? null, fn ($q, $date) => $q->where('submission.report_date', $date));
        $q->when($filters['from'] ?? null, fn ($q, $date) => $q->where('submission.report_date', '>=', $date));
        $q->when($filters['to'] ?? null, fn ($q, $date) => $q->where('submission.report_date', '<=', $date));
        $day = $filters['date'] ?? \Carbon\CarbonImmutable::now(RecordTime::ZONE)->subDay()->toDateString();
        return response()->view('admin.daily-records.submissions', $this->directory->groups($actor, false) + [
            'rows' => $status === 'unsubmitted' ? $this->submissions->missing($actor, $day, $filters)->simplePaginate(25)->withQueryString()
                : $q->orderByDesc('submission.report_date')->orderByDesc('e.id')->simplePaginate(25, [
                    'e.id', 'e.status', 'employee.name as employee', 'submission.report_date', 'submission.version',
                ])->withQueryString(),
            'filters' => $filters, 'status' => $status, 'day' => $day,
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
        $id = $request->validate(['edit' => 'nullable|integer|min:1'])['edit'] ?? null;
        return response()->view('admin.daily-records.categories', [
            'categories' => DB::table('daily_record_categories')->orderBy('kind')->orderBy('parent_id')->orderBy('position')->orderBy('id')->paginate(30),
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

    private function actor(string $ability = RecordAccess::MANAGE): Admin|User
    {
        $actor = Auth::guard('admin')->user() ?: Auth::user();
        abort_unless(($actor instanceof Admin || $actor instanceof User) && $this->access->allows($actor, $ability), 403);
        return $actor;
    }
}
