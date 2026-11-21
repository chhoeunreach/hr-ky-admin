<?php

namespace App\Modules\DailyRecords;

use App\Models\Admin;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

final class DailyRecordsAdminController
{
    public function __construct(private Tasks $tasks, private Entries $entries, private RecordRead $read,
        private RecordAccess $access, private RecordDirectory $directory, private Reporting $reports,
        private RecordFiles $files) {}

    public function index(Request $request)
    {
        $actor = $this->actor();
        [$view, $filters] = $this->selection($request);
        [$tasks, $entries] = $this->reports->queries($actor, $view, $filters);
        [$from, $to] = $this->reports->period($view, $filters);
        $groups = null;
        if (in_array($view, ['performance', 'department', 'location'], true)) {
            $groups = $this->reports->grouped(clone $tasks, clone $entries, $view, $from, $to)
                ->paginate(25)->appends($request->except('fragment'));
        }
        $data = [
            'mode' => $view, 'filters' => $filters, 'overdueAt' => min($to ?? RecordTime::now(), RecordTime::now()), 'summary' => $this->reports->summary($tasks, $entries, $from, $to, $view),
            'tasks' => $groups ? null : $tasks->orderBy($view === 'calendar' ? 'a.due_at' : 'a.updated_at',
                $view === 'calendar' ? 'asc' : 'desc')->orderByDesc('a.id')->paginate(25, ['*'], 'tasks')->appends($request->except('fragment')),
            'entries' => $groups || $view === 'calendar' ? null : $entries->orderByDesc('e.updated_at')->orderByDesc('e.id')
                ->paginate(25, ['*'], 'entries')->appends($request->except('fragment')),
            'groups' => $groups,
            'timeline' => $view === 'timeline' ? $this->reports->timeline((clone $tasks)->reorder(), (clone $entries)->reorder(), $from, $to)
                ->simplePaginate(35, ['*'], 'activity')->appends($request->except('fragment')) : null,
        ];
        if ($request->boolean('fragment')) {
            return response()->view('admin.daily-records.results', $data)->header('Cache-Control', 'private, no-store');
        }
        $detail = $request->validate(['assignment' => 'nullable|integer|min:1', 'entry' => 'nullable|integer|min:1']);
        $employeeLabel = empty($filters['employee_id']) ? null : $this->access->employees($actor)
            ->where('u.id', $filters['employee_id'])->value('u.name');
        return response()->view('admin.daily-records.index', $data + $this->directory->groups($actor, false) + [
            'assignment' => isset($detail['assignment']) ? $this->read->task($actor, $detail['assignment'], true) : null,
            'record' => isset($detail['entry']) ? $this->read->entry($actor, $detail['entry'], true) : null,
            'employeeLabel' => $employeeLabel,
            'catalog' => DB::table('daily_record_categories')->where(fn ($q) => $q->whereNull('department_id')
                ->orWhereIn('department_id', $this->access->employees($actor)->select('u.department_id')))
                ->orderBy('name_en')->get(['id', 'parent_id', 'name_en']),
            'categorySettings' => $this->access->allows($actor, RecordAccess::CATEGORIES),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function employees(Request $request)
    {
        $filters = RecordInput::filters($request);
        return response()->json($this->directory->employees($this->actor(), $filters['search'] ?? null), 200,
            ['Cache-Control' => 'private, no-store']);
    }

    public function create(Request $request)
    {
        $actor = $this->actor();
        $input = $request->validate(['follow_up' => 'nullable|integer|min:1']);
        $followUp = isset($input['follow_up']) ? $this->entries->query($actor, true)
            ->whereIn('e.kind', ['report', 'incident'])->where('e.sensitive', false)->where('e.id', $input['follow_up'])->first() : null;
        abort_if(isset($input['follow_up']) && !$followUp, 404);
        $selected = $followUp ? $this->access->employees($actor, true)->where('u.id', $followUp->employee_id)->first(['u.id', 'u.name']) : null;
        return response()->view('admin.daily-records.create', $this->directory->groups($actor) + [
            'followUp' => $followUp, 'selected' => $selected,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        $this->tasks->create($this->actor(), RecordInput::task($request), $request->file('files', []));
        return redirect()->route('admin.daily-records.index')->with('success', 'Task assigned.');
    }

    public function taskAction(Request $request, int $assignment)
    {
        $actor = $this->actor();
        $data = RecordInput::managerTask($request);
        try {
            if ($data['action'] === 'stop_series') {
                $this->tasks->stopSeries($actor, $assignment);
            } else {
                $this->tasks->managerAction($actor, $assignment, $data);
            }
        } catch (HttpException $error) {
            if ($request->expectsJson() || $error->getStatusCode() !== 409) {
                throw $error;
            }
            return back()->withInput()->withErrors(['task' => $error->getMessage() ?: 'The task status has changed.']);
        }
        return redirect()->route('admin.daily-records.index', ['assignment' => $assignment])->with('success', 'Task updated.');
    }

    public function bulkReview(Request $request)
    {
        $actor = $this->actor();
        $data = $request->validate(['approvals' => 'required|array|max:50', 'approvals.*' => 'integer|min:1|distinct']);
        foreach (array_keys($data['approvals']) as $id) {
            abort_unless(is_int($id) && $id > 0, 422);
        }
        try {
            $this->tasks->bulkApprove($actor, $data['approvals']);
        } catch (HttpException $error) {
            if ($request->expectsJson() || $error->getStatusCode() !== 409) {
                throw $error;
            }
            return back()->withErrors(['reviews' => $error->getMessage() ?: 'A selected report has changed. Reload before approving.']);
        }
        return redirect()->route('admin.daily-records.index', ['status' => 'submitted'])->with('success', 'Selected reports approved.');
    }

    public function review(Request $request, int $entry)
    {
        $this->entries->review($this->actor(), $entry, RecordInput::review($request));
        return redirect()->route('admin.daily-records.index', ['entry' => $entry])->with('success', 'Review saved.');
    }

    public function taskFile(int $assignment, int $file)
    {
        return $this->files->download($this->read->taskFile($this->actor(), $assignment, $file, true));
    }

    public function recordFile(int $entry, int $file)
    {
        return $this->files->download($this->read->entryFile($this->actor(), $entry, $file, true));
    }

    public function export(Request $request, string $format)
    {
        $actor = $this->actor();
        [$view, $filters] = $this->selection($request);
        [$tasks, $entries] = $this->reports->queries($actor, $view, $filters);
        [$from, $to] = $this->reports->period($view, $filters);
        $grouped = in_array($view, ['performance', 'department', 'location'], true);
        $rows = $grouped ? $this->reports->grouped(clone $tasks, clone $entries, $view, $from, $to)
            : $this->reports->exportRows(clone $tasks, clone $entries);
        $name = 'daily-records-'.$view;
        if ($format === 'xlsx') {
            abort_if(DB::query()->fromSub((clone $rows)->limit(10001), 'bounded_export')->count() > 10000, 422, 'Narrow the filters before exporting more than 10,000 rows.');
            return Excel::download(new ReportSpreadsheet($rows, $grouped), "$name.xlsx", headers: ['Cache-Control' => 'private, no-store']);
        }

        $export = $rows->limit(501)->get();
        abort_if($export->count() > 500, 422, 'Narrow the filters before exporting more than 500 rows.');
        return Pdf::loadView('admin.daily-records.pdf', [
            'heading' => ucfirst($view).' report',
            'period' => [$from, $to],
            'summary' => $this->reports->summary($tasks, $entries, $from, $to, $view),
            'columns' => ReportSpreadsheet::columns($grouped),
            'rows' => $export->map(fn ($row) => ReportSpreadsheet::values($row, $grouped)),
        ])->setPaper('a4', 'landscape')->download("$name.pdf")->setPrivate();
    }

    private function actor(): User|Admin
    {
        $actor = Auth::guard('admin')->user() ?: Auth::user();
        abort_unless(($actor instanceof Admin || $actor instanceof User) && $this->access->manages($actor), 403);
        return $actor;
    }

    private function selection(Request $request): array
    {
        $input = $request->validate(['view' => ['nullable', Rule::in(Reporting::VIEWS)]]);
        return [$input['view'] ?? 'tasks', RecordInput::filters($request)];
    }
}
