<?php

namespace App\Modules\DailyRecords;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

final class WorkRecordsApiController
{
    public function __construct(private Entries $entries, private RecordRead $read, private WorkRecords $work,
        private DailySubmissions $submissions, private CategoryCatalog $catalog, private RecordFiles $files, private RecordAccess $access) {}

    public function catalog(Request $request)
    {
        $kind = $request->validate(['kind' => 'required|in:activity,incident'])['kind'];
        return $this->data(['categories' => $this->catalog->forEmployee($this->actor($request), $kind)]);
    }

    public function records(Request $request)
    {
        $actor = $this->actor($request);
        $filter = $request->validate([
            'kind' => 'nullable|in:activity,incident,note,todo,report,extension',
            'search' => 'nullable|string|max:100', 'tag' => 'nullable|string|max:36',
            'archive' => 'nullable|in:active,archived', 'assigned' => 'nullable|boolean',
        ]);
        $query = !empty($filter['assigned']) ? $this->work->assigned($actor) : $this->entries->query($actor, false);
        if (($filter['archive'] ?? null) === 'archived') {
            $query->whereNotNull('e.archived_at');
        } else {
            $query->whereNull('e.archived_at');
        }
        $rows = $query->when($filter['kind'] ?? null, fn ($q, $kind) => $q->where('e.kind', $kind))
            ->when($filter['search'] ?? null, fn ($q, $term) => $q->where('e.title', 'like', '%'.$term.'%'))
            ->when($filter['tag'] ?? null, fn ($q, $tag) => $q->whereJsonContains('e.tags', $tag))
            ->orderByDesc('e.pinned')->orderByDesc('e.updated_at')->orderByDesc('e.id')
            ->simplePaginate(20, RecordRead::ENTRY_COLUMNS);
        return $this->data($rows);
    }

    public function create(Request $request)
    {
        $id = $this->work->save($this->actor($request), null, WorkInput::record($request), $request->file('files', []));
        return $this->data(['id' => $id], 201);
    }

    public function update(Request $request, int $entry)
    {
        $actor = $this->actor($request);
        $row = $this->entries->query($actor, false)->where('e.id', $entry)->first();
        abort_unless($row, 404);
        $this->work->save($actor, $entry, WorkInput::record($request, $row->kind), $request->file('files', []));
        return $this->data($this->read->entry($actor, $entry, false));
    }

    public function noteAction(Request $request, int $entry)
    {
        $data = $request->validate(['action' => 'required|in:pin,unpin,archive,restore']);
        $this->work->noteAction($this->actor($request), $entry, $data['action']);
        return $this->data(['id' => $entry]);
    }

    public function assignedIncident(Request $request, int $entry)
    {
        $actor = $this->actor($request);
        return $this->data($this->read->entry($actor, $entry, false, $this->work->assigned($actor)));
    }

    public function assignedResponse(Request $request, int $entry)
    {
        $data = $request->validate(['message' => 'required|string|max:10000'] + WorkInput::attachments());
        $this->work->assignedResponse($this->actor($request), $entry, $data['message'], $request->file('files', []));
        return $this->assignedIncident($request, $entry);
    }

    public function assignedFile(Request $request, int $entry, int $file)
    {
        $actor = $this->actor($request);
        return $this->files->download($this->read->entryFile($actor, $entry, $file, false, $this->work->assigned($actor)));
    }

    public function draft(Request $request, string $key)
    {
        $actor = $this->actor($request);
        $stored = $this->draftQuery($actor)->where('draft_key', $key)->value('payload');
        return $this->data(['draft' => $stored ? json_decode(Crypt::decryptString($stored), true) : null]);
    }

    public function saveDraft(Request $request, string $key)
    {
        $actor = $this->actor($request);
        $data = $request->validate(['payload' => 'nullable|string|max:50000']);
        DB::transaction(function () use ($actor, $key, $data) {
            DB::table('users')->where('id', $actor->id)->lockForUpdate()->value('id');
            $drafts = $this->draftQuery($actor);
            if (empty($data['payload'])) {
                $drafts->where('draft_key', $key)->delete();
                return;
            }
            abort_unless(is_array(json_decode($data['payload'], true, 32)), 422);
            $exists = (clone $drafts)->where('draft_key', $key)->exists();
            abort_if(!$exists && (clone $drafts)->count() >= 25, 422, 'Finish or clear a saved draft before starting another.');
            $drafts->updateOrInsert(
                ['employee_id' => $actor->id, 'company_id' => $actor->company_id, 'draft_key' => $key],
                ['payload' => Crypt::encryptString($data['payload']), 'updated_at' => RecordTime::now()],
            );
        }, 3);
        return $this->data(['saved' => true]);
    }

    private function draftQuery(User $actor)
    {
        return DB::table('daily_record_drafts')->where('employee_id', $actor->id)->where('company_id', $actor->company_id);
    }

    public function preview(Request $request)
    {
        $date = $request->validate(['date' => 'nullable|date_format:Y-m-d'])['date'] ?? null;
        return $this->data($this->submissions->preview($this->actor($request), $date));
    }

    public function saveSummary(Request $request)
    {
        $id = $this->submissions->save($this->actor($request), WorkInput::report($request), $request->file('files', []));
        return $this->data(['id' => $id]);
    }

    public function ownSummaries(Request $request)
    {
        return $this->summaries($request, false);
    }

    public function inbox(Request $request)
    {
        return $this->summaries($request, true);
    }

    public function ownSummary(Request $request, int $entry)
    {
        return $this->summary($request, $entry, false);
    }

    public function managerSummary(Request $request, int $entry)
    {
        return $this->summary($request, $entry, true);
    }

    public function review(Request $request, int $entry)
    {
        $this->submissions->review($this->actor($request), $entry, WorkInput::review($request));
        return $this->managerSummary($request, $entry);
    }

    public function reviewFile(Request $request, int $entry, int $file)
    {
        $actor = $this->actor($request);
        abort_unless($this->submissions->query($actor, true)->where('e.id', $entry)->exists(), 404);
        return $this->files->download($this->read->entryFile($actor, $entry, $file, true));
    }

    private function summaries(Request $request, bool $manager)
    {
        $actor = $this->actor($request);
        $filter = $request->validate(['status' => 'nullable|in:pending,submitted,under_review,returned,approved,draft']);
        $q = $this->submissions->query($actor, $manager)->join('users as employee', 'employee.id', '=', 'e.employee_id');
        if (($filter['status'] ?? null) === 'pending') {
            $q->whereIn('e.status', ['submitted', 'under_review']);
        } elseif (!empty($filter['status'])) {
            $q->where('e.status', $filter['status']);
        }
        $page = $q->orderByDesc('submission.report_date')->orderByDesc('e.id')
            ->simplePaginate(25, ['e.id', 'e.title', 'e.status', 'employee.name as employee', 'submission.report_date', 'submission.version']);
        return $this->data($page->toArray() + ($manager ? [] : $this->access->reporting($actor)));
    }

    private function summary(Request $request, int $entry, bool $manager)
    {
        $actor = $this->actor($request);
        $version = $request->validate(['version' => 'nullable|integer|min:1'])['version'] ?? null;
        $summary = $this->submissions->detail($actor, $entry, $manager, $version);
        $detail = $this->read->entry($actor, $entry, $manager);
        return $this->data($summary + [
            'history' => $detail['history'], 'files' => $detail['files'],
        ] + ($manager ? [] : $this->access->reporting($actor)));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user('api');
        abort_unless($actor instanceof User && $actor->is_active && $actor->company_id, 403);
        return $actor;
    }

    private function data(mixed $data, int $status = 200)
    {
        return response()->json(['data' => $data], $status, ['Cache-Control' => 'private, no-store']);
    }
}
