<?php

namespace App\Modules\DailyRecords;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DailyRecordsApiController
{
    public function __construct(private Tasks $tasks, private Entries $entries, private RecordRead $read,
        private RecordAccess $access, private RecordDirectory $directory, private RecordFiles $files) {}

    public function capabilities(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        return $this->data([
            'assign_tasks' => $this->access->manages($actor),
            'review_reports' => $this->access->manages($actor), 'work_records' => true,
        ] + $this->access->reporting($actor));
    }

    public function employees(Request $request): JsonResponse
    {
        $filter = RecordInput::filters($request);
        return $this->data($this->directory->employees($this->actor($request), $filter['search'] ?? null));
    }

    public function groups(Request $request): JsonResponse
    {
        return $this->data($this->directory->groups($this->actor($request)));
    }

    public function assign(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        abort_unless($this->access->manages($actor), 403);
        $id = $this->tasks->create($actor, RecordInput::task($request), $request->file('files', []));
        return $this->data(['id' => $id], 201);
    }

    public function tasks(Request $request): JsonResponse
    {
        $filters = $request->validate(['tab' => 'nullable|in:assigned,today,upcoming,overdue,completed,returned,history']);
        $rows = $this->read->employeeTasks($this->actor($request), $filters['tab'] ?? 'assigned')
            ->orderBy('a.due_at')->orderByDesc('a.id')->simplePaginate(20)
            ->through(fn ($row) => $this->read->taskData($row));
        return $this->data($rows);
    }

    public function task(Request $request, int $assignment): JsonResponse
    {
        return $this->data($this->read->task($this->actor($request), $assignment, false));
    }

    public function taskAction(Request $request, int $assignment): JsonResponse
    {
        $this->tasks->employeeAction($this->actor($request), $assignment, RecordInput::employeeTask($request), $request->file('files', []));
        return $this->task($request, $assignment);
    }

    public function records(Request $request): JsonResponse
    {
        $filter = $request->validate(['kind' => 'nullable|in:note,todo,incident,report,extension']);
        return $this->data($this->entries->query($this->actor($request), false)
            ->when($filter['kind'] ?? null, fn ($query, $kind) => $query->where('e.kind', $kind))
            ->orderByDesc('e.id')->simplePaginate(20, RecordRead::ENTRY_COLUMNS));
    }

    public function createRecord(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $id = $this->entries->create($actor, RecordInput::entry($request), $request->file('files', []));
        return $this->data(['id' => $id], 201);
    }

    public function record(Request $request, int $entry): JsonResponse
    {
        $actor = $this->actor($request);
        $record = $this->read->entry($actor, $entry, false);
        return $this->data($record + ($record['entry']->kind === 'report' ? $this->access->reporting($actor) : []));
    }

    public function recordAction(Request $request, int $entry): JsonResponse
    {
        $this->entries->employeeAction($this->actor($request), $entry, RecordInput::employeeEntry($request), $request->file('files', []));
        return $this->record($request, $entry);
    }

    public function taskFile(Request $request, int $assignment, int $file)
    {
        return $this->files->download($this->read->taskFile($this->actor($request), $assignment, $file, false));
    }

    public function recordFile(Request $request, int $entry, int $file)
    {
        return $this->files->download($this->read->entryFile($this->actor($request), $entry, $file, false));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user('api');
        abort_unless($actor instanceof User && $actor->is_active && $actor->company_id, 403);
        return $actor;
    }

    private function data(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data], $status, ['Cache-Control' => 'private, no-store']);
    }
}
