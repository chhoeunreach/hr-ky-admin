@php
    $task = $assignment['task'] ?? null;
    $entry = $record['entry'] ?? null;
    $detail = $record ?? $assignment;
    $close = request()->fullUrlWithoutQuery(['assignment', 'entry', 'history', 'records']);
    $action = $task ? route('admin.daily-records.task-action', $task['id']) : null;
@endphp
<div class="offcanvas-backdrop fade show" aria-hidden="true"></div>
<aside class="offcanvas offcanvas-end show" tabindex="-1" aria-labelledby="daily-record-heading"
    role="dialog" aria-modal="true" id="daily-record-detail" style="visibility:visible;width:min(100%, 680px)">
    <div class="offcanvas-header border-bottom">
        <div>
            <div class="small text-muted">{{ $entry ? ucfirst($entry->kind) : 'Task assignment' }}</div>
            <h2 class="offcanvas-title h5" id="daily-record-heading">{{ $entry->title ?? $task['title'] }}</h2>
        </div>
        <a class="btn-close" href="{{ $close }}" aria-label="Close details"></a>
    </div>
    <div class="offcanvas-body">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<p class="mb-1">{{ $error }}</p>@endforeach</div>
        @endif
        @if ($entry)
            <p class="badge bg-secondary">{{ ucfirst($entry->status ?? 'Recorded') }}</p>
            @if ($entry->body)<p class="text-break" style="white-space:pre-wrap">{{ $entry->body }}</p>@endif
            @if ($entry->solution)<h3 class="h6">Solution</h3><p class="text-break" style="white-space:pre-wrap">{{ $entry->solution }}</p>@endif
            @if ($entry->kind === 'activity')
                @php
                    $activityDetails = json_decode($entry->details ?? '{}', true);
                    $classification = json_decode($entry->classification ?? '{}', true);
                @endphp
                <dl>
                    @if (isset($classification['category']))
                        <dt>Category</dt><dd>{{ $classification['category']['en'] }}
                            @if ($classification['subcategory']) · {{ $classification['subcategory']['en'] }} @endif</dd>
                    @endif
                    @foreach (['result' => 'Work result', 'challenges' => 'Challenges'] as $key => $label)
                        @if (!empty($activityDetails[$key]))
                            <dt>{{ $label }}</dt><dd class="text-break" style="white-space:pre-wrap">{{ $activityDetails[$key] }}</dd>
                        @endif
                    @endforeach
                    @foreach ($classification['fields'] ?? [] as $field)
                        @if (isset($activityDetails['fields'][$field['key']]))
                            @php
                                $value = $activityDetails['fields'][$field['key']];
                            @endphp
                            <dt>{{ $field['label_en'] }}</dt><dd>{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                        @endif
                    @endforeach
                    @if (!empty($activityDetails['follow_up']))
                        <dt>Follow-up</dt><dd>{{ $entry->follow_up_at ? \App\Modules\DailyRecords\RecordTime::display($entry->follow_up_at).' ICT' : 'Required' }}</dd>
                    @endif
                </dl>
            @endif
            @if ($entry->requested_due_at)<p>Requested deadline {{ \App\Modules\DailyRecords\RecordTime::display($entry->requested_due_at) }} ICT</p>@endif
            @if ($entry->assignment_id)
                <p><a href="{{ request()->fullUrlWithQuery(['assignment' => $entry->assignment_id, 'entry' => null, 'history' => null]) }}">Open related task</a></p>
            @endif
            @if ($entry->occurred_at)<p>Occurred or reporting date {{ \App\Modules\DailyRecords\RecordTime::display($entry->occurred_at) }} ICT</p>@endif
            @if ($entry->kind === 'incident')
                <a class="btn btn-sm btn-outline-primary mb-3" href="{{ route('admin.daily-records.incident', $entry->id) }}">Manage incident</a>
            @endif
            @if ($entry->consolidated)
                <a class="btn btn-sm btn-outline-primary mb-3" href="{{ route('admin.daily-records.submission', $entry->id) }}">Open daily submission</a>
            @endif
            @if ($entry->kind === 'report')
                <a class="btn btn-sm btn-outline-primary mb-3" href="{{ route('admin.daily-records.create', ['follow_up' => $entry->id]) }}">Create follow-up task</a>
            @endif
            @if (($entry->kind === 'report' && $entry->status === 'submitted' && !$entry->assignment_id && !$entry->consolidated) || ($entry->kind === 'extension' && $entry->status === 'pending'))
                @include('admin.daily-records.review-form', ['reviewAction' => route('admin.daily-records.review', $entry->id), 'extension' => $entry->kind === 'extension'])
            @endif
        @else
            <dl class="row mb-2">
                <dt class="col-4">Status</dt><dd class="col-8">{{ ucfirst(str_replace('_', ' ', $task['status'])) }} @if ($task['overdue'])<span class="badge bg-danger">Overdue</span>@endif</dd>
                <dt class="col-4">Progress</dt><dd class="col-8">{{ $task['progress'] }}%</dd>
                <dt class="col-4">Deadline (ICT)</dt><dd class="col-8">{{ \App\Modules\DailyRecords\RecordTime::display($task['due_at']) }}</dd>
                <dt class="col-4">Completed (ICT)</dt><dd class="col-8">{{ \App\Modules\DailyRecords\RecordTime::display($task['completed_at']) }}</dd>
                <dt class="col-4">Approved (ICT)</dt><dd class="col-8">{{ \App\Modules\DailyRecords\RecordTime::display($task['approved_at']) }}</dd>
            </dl>
            @if ($task['instructions'])
                <h3 class="h6">Instructions</h3><p class="text-break" style="white-space:pre-wrap">{{ $task['instructions'] }}</p>
            @endif
            @if ($task['checklist'])
                <h3 class="h6">Checklist</h3><ul>
                    @foreach ($task['checklist'] as $index => $item)
                        <li>{{ $item }} · {{ in_array($index, $task['checked'], true) ? 'Complete' : 'Open' }}</li>
                    @endforeach
                </ul>
            @endif
            @if ($assignment['entries']->isNotEmpty())
                <h3 class="h6">Related records</h3>
                @foreach ($assignment['entries'] as $related)
                    <section class="border rounded p-2 mb-2">
                        <a href="{{ request()->fullUrlWithQuery(['entry' => $related->id, 'assignment' => null, 'history' => null]) }}">{{ $related->title }}</a>
                        <span class="small text-muted"> · {{ ucfirst($related->kind) }} · {{ ucfirst($related->status ?? 'Recorded') }}</span>
                    </section>
                @endforeach
                {{ $assignment['entries']->withQueryString()->links() }}
            @endif

            @if (!$task['archived_at'] && !$task['superseded_at'])
                @if ($assignment['pending_report'])
                    <h3 class="h6">Current submission</h3>
                    <p class="text-break" style="white-space:pre-wrap">{{ $assignment['pending_report']->body }}</p>
                    <p><a href="{{ request()->fullUrlWithQuery(['entry' => $assignment['pending_report']->id, 'assignment' => null, 'history' => null]) }}">Open submission and evidence</a></p>
                    @include('admin.daily-records.review-form', ['reviewAction' => $action, 'extension' => false, 'expectedReportId' => $assignment['pending_report']->id])
                @endif
                @php
                    $actions = match ($task['status']) {
                        'assigned', 'acknowledged', 'in_progress', 'returned' => ['hold' => 'Put on hold', 'cancel' => 'Cancel task'],
                        'on_hold' => ['resume' => 'Resume', 'cancel' => 'Cancel task'],
                        'submitted' => ['cancel' => 'Cancel task'],
                        'approved' => ['close' => 'Close task', 'reopen' => 'Reopen'],
                        'closed', 'cancelled' => ['reopen' => 'Reopen', 'archive' => 'Archive'],
                        default => [],
                    };
                    if ($task['can_stop_series'] ?? false) $actions['stop_series'] = 'Stop recurrence';
                @endphp
                <form action="{{ $action }}" method="POST" class="d-flex gap-2 flex-wrap my-3">
                    @csrf
                    @foreach ($actions as $key => $label)
                        <button class="btn btn-sm btn-outline-secondary" type="submit" name="action" value="{{ $key }}">{{ $label }}</button>
                    @endforeach
                </form>
                @if (in_array($task['status'], \App\Modules\DailyRecords\Tasks::INCOMPLETE, true))
                    <details class="mb-3 border rounded p-2">
                        <summary>Edit instructions and deadline</summary>
                        <form action="{{ $action }}" method="POST" class="d-grid gap-2 mt-2">
                            @csrf
                            <input type="hidden" name="action" value="edit">
                            <label>Instructions<textarea class="form-control" name="instructions" maxlength="10000">{{ old('instructions', $task['instructions']) }}</textarea></label>
                            <label>Deadline (ICT)<input class="form-control" type="datetime-local" name="due_at" required
                                value="{{ old('due_at', str_replace(' ', 'T', \App\Modules\DailyRecords\RecordTime::display($task['due_at']))) }}"></label>
                            <label>Reminder (ICT)<input class="form-control" type="datetime-local" name="remind_at"
                                value="{{ old('remind_at', $task['remind_at'] ? str_replace(' ', 'T', \App\Modules\DailyRecords\RecordTime::display($task['remind_at'])) : '') }}"></label>
                            <button class="btn btn-sm btn-primary" type="submit">Save changes</button>
                        </form>
                    </details>
                    <details class="mb-3 border rounded p-2">
                        <summary>Reassign task</summary>
                        <form action="{{ $action }}" method="POST" class="d-grid gap-2 mt-2">
                            @csrf
                            <input type="hidden" name="action" value="reassign">
                            @include('admin.daily-records.employee-picker', ['pickerName' => 'employee_id'])
                            <button class="btn btn-sm btn-primary" type="submit">Reassign</button>
                        </form>
                    </details>
                @endif
                <form action="{{ $action }}" method="POST" class="d-grid gap-2 mb-3">
                    @csrf
                    <input type="hidden" name="action" value="comment">
                    <label>Message for employee<textarea class="form-control" name="message" maxlength="10000" required></textarea></label>
                    <button class="btn btn-sm btn-outline-primary" type="submit">Send message</button>
                </form>
            @endif
        @endif

        @if ($detail['files']->isNotEmpty())
            <h3 class="h6">Attachments</h3><ul>
                @foreach ($detail['files'] as $file)
                    <li><a href="{{ $entry ? route('admin.daily-records.record-file', [$entry->id, $file->id]) : route('admin.daily-records.task-file', [$task['id'], $file->id]) }}">{{ $file->name }}</a></li>
                @endforeach
            </ul>
        @endif
        <h3 class="h6">Activity history</h3>
        <ol class="list-group list-group-flush">
            @forelse ($detail['history'] as $event)
                <li class="list-group-item px-0">
                    <div><strong>{{ ucfirst(str_replace('_', ' ', $event->action)) }}</strong>
                        <span class="text-muted small"> · {{ \App\Modules\DailyRecords\RecordTime::display($event->created_at) }} ICT</span>
                    </div>
                    @foreach (json_decode($event->details, true) ?? [] as $key => $value)
                        @if (is_scalar($value) && $value !== '')
                            <div class="text-break" style="white-space:pre-wrap">{{ ucfirst(str_replace('_', ' ', $key)) }} · {{ str_ends_with($key, '_at') && is_string($value) ? \App\Modules\DailyRecords\RecordTime::display($value).' ICT' : $value }}</div>
                        @endif
                    @endforeach
                </li>
            @empty
                <li class="list-group-item px-0">No activity yet.</li>
            @endforelse
        </ol>
        {{ $detail['history']->withQueryString()->links() }}
    </div>
</aside>
<script>
(() => {
    const drawer = document.getElementById('daily-record-detail');
    const close = drawer.querySelector('.btn-close');
    document.body.style.overflow = 'hidden';
    drawer.focus();
    window.addEventListener('keydown', event => {
        if (event.key === 'Escape') window.location.assign(close.href);
        if (event.key !== 'Tab') return;
        const focusable = [...drawer.querySelectorAll('a[href], button, input, textarea, select, summary')]
            .filter(element => !element.disabled && element.getClientRects().length);
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && (document.activeElement === first || document.activeElement === drawer)) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });
})();
</script>
