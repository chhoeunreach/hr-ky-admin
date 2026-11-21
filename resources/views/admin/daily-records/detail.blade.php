@php
    $task = $assignment['task'] ?? null;
    $entry = $record['entry'] ?? null;
    $detail = $record ?? $assignment;
    $close = request()->fullUrlWithoutQuery(['assignment', 'entry', 'history', 'records']);
    $action = $task ? route('admin.daily-records.task-action', $task['id']) : null;
@endphp
<div class="offcanvas-backdrop fade show" aria-hidden="true" style="opacity: 0.5;"></div>
<aside class="offcanvas offcanvas-end show dr-offcanvas" tabindex="-1" aria-labelledby="daily-record-heading"
    role="dialog" aria-modal="true" id="daily-record-detail" style="visibility: visible; width: min(100%, 720px); z-index: 1055;">
    
    {{-- Header --}}
    <div class="offcanvas-header dr-offcanvas-header">
        <div class="d-flex align-items-center gap-3">
            <div class="dr-hero-icon-box" style="width: 42px; height: 42px; font-size: 1.15rem;">
                <i class="{{ $entry ? ($entry->kind === 'incident' ? 'fa-solid fa-triangle-exclamation' : 'fa-regular fa-file-lines') : 'fa-solid fa-list-check' }}"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="dr-badge dr-badge-primary">
                        {{ $entry ? (__('daily_records.type_' . $entry->kind) !== 'daily_records.type_' . $entry->kind ? __('daily_records.type_' . $entry->kind) : ucfirst($entry->kind)) : __('daily_records.task_assignment') }}
                    </span>
                    @if ($task && ($task['overdue'] ?? false))
                        <span class="dr-overdue-tag">
                            <i class="fa-solid fa-triangle-exclamation"></i> {{ __('daily_records.overdue') }}
                        </span>
                    @endif
                </div>
                <h2 class="offcanvas-title h5 mb-0 fw-bold text-dark" id="daily-record-heading">
                    {{ $entry->title ?? $task['title'] }}
                </h2>
            </div>
        </div>
        <a class="btn-close" href="{{ $close }}" aria-label="Close details"></a>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body dr-offcanvas-body">
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 mb-3" role="alert">
                @foreach ($errors->all() as $error)
                    <p class="mb-1 d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i> {{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($entry)
            {{-- Entry Metadata --}}
            <div class="dr-meta-grid">
                <div class="dr-meta-item">
                    <span class="dr-meta-title">{{ __('daily_records.status') }}</span>
                    <div>
                        <span class="dr-badge dr-badge-info">
                            <span class="dr-badge-dot"></span>
                            {{ ucfirst($entry->status ?? 'Recorded') }}
                        </span>
                    </div>
                </div>
                <div class="dr-meta-item">
                    <span class="dr-meta-title">{{ __('daily_records.reporting_date_ict') }}</span>
                    <span class="dr-meta-val">
                        {{ $entry->occurred_at ? \App\Modules\DailyRecords\RecordTime::display($entry->occurred_at).' ICT' : '—' }}
                    </span>
                </div>
                @if ($entry->requested_due_at)
                    <div class="dr-meta-item">
                        <span class="dr-meta-title">{{ __('daily_records.deadline_ict') }}</span>
                        <span class="dr-meta-val text-primary">
                            {{ \App\Modules\DailyRecords\RecordTime::display($entry->requested_due_at) }} ICT
                        </span>
                    </div>
                @endif
                @if ($entry->follow_up_at)
                    <div class="dr-meta-item">
                        <span class="dr-meta-title">{{ __('daily_records.follow_up_target') }}</span>
                        <span class="dr-meta-val text-warning">
                            {{ \App\Modules\DailyRecords\RecordTime::display($entry->follow_up_at) }} ICT
                        </span>
                    </div>
                @endif
            </div>

            {{-- Entry Description / Body --}}
            @if ($entry->body)
                <div class="mb-3">
                    <h3 class="dr-form-label"><i class="fa-regular fa-file-lines me-1"></i> {{ __('daily_records.description_work_summary') }}</h3>
                    <div class="dr-instruction-box text-break" style="white-space: pre-wrap;">{{ $entry->body }}</div>
                </div>
            @endif

            @if ($entry->solution)
                <div class="mb-3">
                    <h3 class="dr-form-label text-success"><i class="fa-solid fa-lightbulb me-1"></i> {{ __('daily_records.solution_provided') }}</h3>
                    <div class="p-3 bg-success-subtle border border-success-subtle rounded text-break" style="white-space: pre-wrap;">{{ $entry->solution }}</div>
                </div>
            @endif

            @if ($entry->kind === 'activity')
                @php
                    $activityDetails = json_decode($entry->details ?? '{}', true);
                    $classification = json_decode($entry->classification ?? '{}', true);
                    $isKm = \Illuminate\Support\Facades\App::getLocale() === 'km';
                @endphp
                <div class="dr-card p-3 mb-3">
                    <h3 class="h6 mb-3 fw-bold text-dark"><i class="fa-solid fa-folder-tree me-1 text-primary"></i> {{ __('daily_records.activity_classification') }}</h3>
                    <dl class="row mb-0">
                        @if (isset($classification['category']))
                            <dt class="col-sm-4 text-muted small text-uppercase">{{ __('daily_records.category') }}</dt>
                            <dd class="col-sm-8 fw-semibold">
                                {{ $isKm && !empty($classification['category']['km']) ? $classification['category']['km'] : $classification['category']['en'] }}
                                @if (!empty($classification['subcategory']))
                                    · {{ $isKm && !empty($classification['subcategory']['km']) ? $classification['subcategory']['km'] : $classification['subcategory']['en'] }}
                                @endif
                            </dd>
                        @endif
                        @foreach (['result' => __('daily_records.completed_work'), 'challenges' => __('daily_records.challenges_faced')] as $key => $label)
                            @if (!empty($activityDetails[$key]))
                                <dt class="col-sm-4 text-muted small text-uppercase">{{ $label }}</dt>
                                <dd class="col-sm-8 text-break" style="white-space: pre-wrap;">{{ $activityDetails[$key] }}</dd>
                            @endif
                        @endforeach
                        @foreach ($classification['fields'] ?? [] as $field)
                            @if (isset($activityDetails['fields'][$field['key']]))
                                @php
                                    $value = $activityDetails['fields'][$field['key']];
                                    $fieldLabel = $isKm && !empty($field['label_km']) ? $field['label_km'] : ($field['label_en'] ?? $field['key']);
                                @endphp
                                <dt class="col-sm-4 text-muted small text-uppercase">{{ $fieldLabel }}</dt>
                                <dd class="col-sm-8">{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                            @endif
                        @endforeach
                    </dl>
                </div>
            @endif

            {{-- Action Shortcuts for Entry --}}
            <div class="d-flex flex-wrap gap-2 mb-3">
                @if ($entry->assignment_id)
                    <a class="dr-btn-outline" href="{{ request()->fullUrlWithQuery(['assignment' => $entry->assignment_id, 'entry' => null, 'history' => null]) }}">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> {{ __('daily_records.open_related_task') }}
                    </a>
                @endif
                @if ($entry->kind === 'incident')
                    <a class="dr-btn-primary" href="{{ route('admin.daily-records.incident', $entry->id) }}">
                        <i class="fa-solid fa-shield-halved"></i> {{ __('daily_records.manage_incident') }}
                    </a>
                @endif
                @if ($entry->consolidated)
                    <a class="dr-btn-outline" href="{{ route('admin.daily-records.submission', $entry->id) }}">
                        <i class="fa-regular fa-file-lines"></i> {{ __('daily_records.open_daily_submission') }}
                    </a>
                @endif
                @if ($entry->kind === 'report')
                    <a class="dr-btn-outline" href="{{ route('admin.daily-records.create', ['follow_up' => $entry->id]) }}">
                        <i class="fa-solid fa-plus"></i> {{ __('daily_records.create_follow_up_task') }}
                    </a>
                @endif
            </div>

            @if (($entry->kind === 'report' && $entry->status === 'submitted' && !$entry->assignment_id && !$entry->consolidated) || ($entry->kind === 'extension' && $entry->status === 'pending'))
                <div class="mb-3">
                    <h3 class="dr-form-label"><i class="fa-solid fa-clipboard-check me-1"></i> {{ __('daily_records.review_action') }}</h3>
                    @include('admin.daily-records.review-form', ['reviewAction' => route('admin.daily-records.review', $entry->id), 'extension' => $entry->kind === 'extension'])
                </div>
            @endif

        @else
            {{-- TASK ASSIGNMENT VIEW --}}
            @php
                $taskStatus = $task['status'] ?? 'assigned';
                $taskProgress = (int) ($task['progress'] ?? 0);
            @endphp
            <div class="dr-meta-grid">
                <div class="dr-meta-item">
                    <span class="dr-meta-title">{{ __('daily_records.status') }}</span>
                    <div>
                        <span class="dr-badge {{ match($taskStatus) { 'approved' => 'dr-badge-success', 'submitted' => 'dr-badge-warning', 'in_progress' => 'dr-badge-primary', default => 'dr-badge-info' } }}">
                            <span class="dr-badge-dot"></span>
                            {{ __('daily_records.kpi_' . $taskStatus) !== 'daily_records.kpi_' . $taskStatus ? __('daily_records.kpi_' . $taskStatus) : ucfirst(str_replace('_', ' ', $taskStatus)) }}
                        </span>
                    </div>
                </div>
                <div class="dr-meta-item">
                    <span class="dr-meta-title">{{ __('daily_records.progress') }}</span>
                    <div class="dr-progress-wrapper mt-1">
                        <div class="dr-progress-track">
                            <div class="dr-progress-fill {{ $taskProgress >= 100 ? 'fill-complete' : '' }}" style="width: {{ $taskProgress }}%;"></div>
                        </div>
                        <span class="dr-progress-text fw-bold">{{ $taskProgress }}%</span>
                    </div>
                </div>
                <div class="dr-meta-item">
                    <span class="dr-meta-title">{{ __('daily_records.deadline_ict') }}</span>
                    <span class="dr-meta-val {{ ($task['overdue'] ?? false) ? 'text-danger' : 'text-dark' }}">
                        <i class="fa-regular fa-clock me-1"></i>
                        {{ \App\Modules\DailyRecords\RecordTime::display($task['due_at']) }}
                    </span>
                </div>
                <div class="dr-meta-item">
                    <span class="dr-meta-title">{{ __('daily_records.kpi_completed') }} (ICT)</span>
                    <span class="dr-meta-val text-muted">
                        {{ $task['completed_at'] ? \App\Modules\DailyRecords\RecordTime::display($task['completed_at']) : '—' }}
                    </span>
                </div>
            </div>

            {{-- Instructions --}}
            @if ($task['instructions'])
                <div class="mb-3">
                    <h3 class="dr-form-label"><i class="fa-solid fa-align-left me-1"></i> {{ __('daily_records.detailed_instructions') }}</h3>
                    <div class="dr-instruction-box text-break" style="white-space: pre-wrap;">{{ $task['instructions'] }}</div>
                </div>
            @endif

            {{-- Checklist --}}
            @if (!empty($task['checklist']))
                <div class="mb-3">
                    <h3 class="dr-form-label"><i class="fa-solid fa-list-check me-1"></i> {{ __('daily_records.checklist_items') }}</h3>
                    <div class="d-grid gap-1">
                        @foreach ($task['checklist'] as $index => $item)
                            @php
                                $isChecked = in_array($index, $task['checked'] ?? [], true);
                            @endphp
                            <div class="dr-checklist-item {{ $isChecked ? 'is-completed' : '' }}">
                                <i class="{{ $isChecked ? 'fa-solid fa-circle-check text-success' : 'fa-regular fa-circle text-muted' }}"></i>
                                <span class="flex-grow-1">{{ $item }}</span>
                                <span class="badge {{ $isChecked ? 'bg-success-subtle text-success' : 'bg-light text-muted' }}">
                                    {{ $isChecked ? __('daily_records.kpi_completed') : 'Open' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Related Records --}}
            @if ($assignment['entries']->isNotEmpty())
                <div class="mb-3">
                    <h3 class="dr-form-label"><i class="fa-solid fa-link me-1"></i> {{ __('daily_records.related_activities_incidents') }}</h3>
                    <div class="d-grid gap-2">
                        @foreach ($assignment['entries'] as $related)
                            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between gap-2">
                                <a class="fw-semibold text-dark text-decoration-none" href="{{ request()->fullUrlWithQuery(['entry' => $related->id, 'assignment' => null, 'history' => null]) }}">
                                    {{ $related->title }}
                                </a>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="dr-badge dr-badge-primary">{{ __('daily_records.type_' . $related->kind) !== 'daily_records.type_' . $related->kind ? __('daily_records.type_' . $related->kind) : ucfirst($related->kind) }}</span>
                                    <span class="dr-badge dr-badge-secondary">{{ ucfirst($related->status ?? 'Recorded') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($assignment['entries']->hasPages())
                        <div class="pt-2">
                            {{ $assignment['entries']->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Submissions & Manager Review --}}
            @if (!$task['archived_at'] && !$task['superseded_at'])
                @if ($assignment['pending_report'])
                    <div class="dr-card p-3 mb-3 border-warning">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h3 class="h6 fw-bold text-dark mb-0">
                                <i class="fa-solid fa-inbox text-warning me-1"></i> {{ __('daily_records.current_submitted_report') }}
                            </h3>
                            <a class="dr-btn-outline py-1 px-2" href="{{ request()->fullUrlWithQuery(['entry' => $assignment['pending_report']->id, 'assignment' => null, 'history' => null]) }}">
                                <i class="fa-solid fa-paperclip"></i> {{ __('daily_records.evidence') }}
                            </a>
                        </div>
                        <div class="p-2 bg-light rounded text-break mb-3" style="white-space: pre-wrap;">{{ $assignment['pending_report']->body }}</div>
                        @include('admin.daily-records.review-form', [
                            'reviewAction' => $action,
                            'extension' => false,
                            'expectedReportId' => $assignment['pending_report']->id
                        ])
                    </div>
                @endif

                {{-- Task Manager Actions --}}
                @php
                    $actions = match ($task['status']) {
                        'assigned', 'acknowledged', 'in_progress', 'returned' => ['hold' => __('daily_records.hold'), 'cancel' => __('daily_records.cancel_task')],
                        'on_hold' => ['resume' => __('daily_records.resume'), 'cancel' => __('daily_records.cancel_task')],
                        'submitted' => ['cancel' => __('daily_records.cancel_task')],
                        'approved' => ['close' => __('daily_records.close_task'), 'reopen' => __('daily_records.reopen')],
                        'closed', 'cancelled' => ['reopen' => __('daily_records.reopen'), 'archive' => __('daily_records.archive')],
                        default => [],
                    };
                    if ($task['can_stop_series'] ?? false) $actions['stop_series'] = __('daily_records.stop_series');
                @endphp
                @if (!empty($actions))
                    <div class="mb-3">
                        <h3 class="dr-form-label"><i class="fa-solid fa-hand me-1"></i> {{ __('daily_records.quick_state_actions') }}</h3>
                        <form action="{{ $action }}" method="POST" class="d-flex gap-2 flex-wrap">
                            @csrf
                            @foreach ($actions as $key => $label)
                                @php
                                    $btnClass = match($key) {
                                        'hold' => 'btn-outline-warning',
                                        'cancel' => 'btn-outline-danger',
                                        'close' => 'btn-outline-secondary',
                                        'resume', 'reopen' => 'btn-outline-success',
                                        default => 'btn-outline-primary',
                                    };
                                @endphp
                                <button class="btn btn-sm {{ $btnClass }}" type="submit" name="action" value="{{ $key }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </form>
                    </div>
                @endif

                {{-- Edit & Reassign Accordions --}}
                @if (in_array($task['status'], \App\Modules\DailyRecords\Tasks::INCOMPLETE, true))
                    <div class="accordion mb-3" id="taskEditAccordion">
                        <div class="accordion-item border rounded">
                            <h2 class="accordion-header" id="headingEdit">
                                <button class="accordion-button collapsed py-2 px-3 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEdit" aria-expanded="false" aria-controls="collapseEdit">
                                    <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> {{ __('daily_records.edit_instructions_deadline') }}
                                </button>
                            </h2>
                            <div id="collapseEdit" class="accordion-collapse collapse" aria-labelledby="headingEdit" data-bs-parent="#taskEditAccordion">
                                <div class="accordion-body p-3">
                                    <form action="{{ $action }}" method="POST" class="d-grid gap-2">
                                        @csrf
                                        <input type="hidden" name="action" value="edit">
                                        <div>
                                            <label class="dr-form-label">{{ __('daily_records.detailed_instructions') }}</label>
                                            <textarea class="dr-form-control" name="instructions" rows="3" maxlength="10000">{{ old('instructions', $task['instructions']) }}</textarea>
                                        </div>
                                        <div>
                                            <label class="dr-form-label">{{ __('daily_records.deadline_ict') }}</label>
                                            <input class="dr-form-control" type="datetime-local" name="due_at" required
                                                value="{{ old('due_at', str_replace(' ', 'T', \App\Modules\DailyRecords\RecordTime::display($task['due_at']))) }}">
                                        </div>
                                        <div>
                                            <label class="dr-form-label">{{ __('daily_records.reminder_alert_ict') }}</label>
                                            <input class="dr-form-control" type="datetime-local" name="remind_at"
                                                value="{{ old('remind_at', $task['remind_at'] ? str_replace(' ', 'T', \App\Modules\DailyRecords\RecordTime::display($task['remind_at'])) : '') }}">
                                        </div>
                                        <button class="dr-btn-primary btn-sm mt-1" type="submit">
                                            <i class="fa-solid fa-check"></i> {{ __('daily_records.save_changes') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded mt-2">
                            <h2 class="accordion-header" id="headingReassign">
                                <button class="accordion-button collapsed py-2 px-3 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReassign" aria-expanded="false" aria-controls="collapseReassign">
                                    <i class="fa-solid fa-user-gear me-2 text-primary"></i> {{ __('daily_records.reassign_task') }}
                                </button>
                            </h2>
                            <div id="collapseReassign" class="accordion-collapse collapse" aria-labelledby="headingReassign" data-bs-parent="#taskEditAccordion">
                                <div class="accordion-body p-3">
                                    <form action="{{ $action }}" method="POST" class="d-grid gap-2">
                                        @csrf
                                        <input type="hidden" name="action" value="reassign">
                                        @include('admin.daily-records.employee-picker', ['pickerName' => 'employee_id'])
                                        <button class="dr-btn-primary btn-sm mt-1" type="submit">
                                            <i class="fa-solid fa-user-check"></i> {{ __('daily_records.confirm_reassign') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Message / Comment Form --}}
                <div class="dr-card p-3 mb-3">
                    <h3 class="h6 fw-bold mb-2"><i class="fa-regular fa-comment-dots text-primary me-1"></i> {{ __('daily_records.send_note_to_assignee') }}</h3>
                    <form action="{{ $action }}" method="POST" class="d-grid gap-2">
                        @csrf
                        <input type="hidden" name="action" value="comment">
                        <textarea class="dr-form-control" name="message" rows="2" placeholder="{{ __('daily_records.send_note_placeholder') }}" maxlength="10000" required></textarea>
                        <div class="d-flex justify-content-end">
                            <button class="dr-btn-primary btn-sm" type="submit">
                                <i class="fa-regular fa-paper-plane"></i> {{ __('daily_records.send_note') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @endif

        {{-- Attachments Section --}}
        @if ($detail['files']->isNotEmpty())
            <div class="dr-card p-3 mb-3">
                <h3 class="h6 fw-bold text-dark mb-2">
                    <i class="fa-solid fa-paperclip text-primary me-1"></i> {{ __('daily_records.attached_documents') }} ({{ $detail['files']->count() }})
                </h3>
                <div class="d-grid gap-1">
                    @foreach ($detail['files'] as $file)
                        <a class="p-2 border rounded bg-light text-decoration-none d-flex align-items-center gap-2"
                            href="{{ $entry ? route('admin.daily-records.record-file', [$entry->id, $file->id]) : route('admin.daily-records.task-file', [$task['id'], $file->id]) }}">
                            <i class="fa-regular fa-file text-primary"></i>
                            <span class="flex-grow-1 text-dark fw-semibold small">{{ $file->name }}</span>
                            <i class="fa-solid fa-download text-muted small"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Activity History Section --}}
        <div class="dr-card p-3 mb-3">
            <h3 class="h6 fw-bold text-dark mb-3">
                <i class="fa-solid fa-timeline text-primary me-1"></i> {{ __('daily_records.audit_event_history') }}
            </h3>
            <div class="dr-timeline" style="padding-left: 1.5rem;">
                @forelse ($detail['history'] as $event)
                    <div class="dr-timeline-item mb-3">
                        <div class="dr-timeline-node" style="width: 18px; height: 18px; left: -1.5rem; top: 2px;">
                            <i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <span class="fw-bold small text-dark">{{ ucfirst(str_replace('_', ' ', $event->action)) }}</span>
                                <span class="dr-meta-badge" style="font-size: 0.72rem;">
                                    {{ \App\Modules\DailyRecords\RecordTime::display($event->created_at) }} ICT
                                </span>
                            </div>
                            @foreach (json_decode($event->details, true) ?? [] as $key => $value)
                                @if (is_scalar($value) && $value !== '')
                                    <div class="small text-muted text-break mt-1" style="white-space: pre-wrap;">
                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ str_ends_with($key, '_at') && is_string($value) ? \App\Modules\DailyRecords\RecordTime::display($value).' ICT' : $value }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('daily_records.no_audit_history') }}</p>
                @endforelse
            </div>
            @if ($detail['history']->hasPages())
                <div class="pt-2 border-top">
                    {{ $detail['history']->withQueryString()->links() }}
                </div>
            @endif
        </div>
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
