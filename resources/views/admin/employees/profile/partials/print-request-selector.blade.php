<div class="employee-complete-toolbar employee-request-workspace mb-3">
    <header class="employee-request-workspace-header">
        <div>
            <span class="employee-request-eyebrow">{{ match ($requestTab) { 'leave-letter' => __('index.leave_request_form'), 'resignation-letter' => __('index.resignation_request_form'), default => __('index.time_leave_request_form') } }}</span>
            <h3>{{ __('index.request_workspace_title') }}</h3>
            <p>{{ __('index.request_workspace_help') }}</p>
        </div>
        <div class="employee-request-header-actions">
            <a class="btn btn-outline-secondary btn-sm" href="{{ $requestListUrl }}"
               @if($requestTab === 'leave-letter') data-open-leave-request-form data-request-modal-title="{{ __('index.request_list') }}" @endif>{{ __('index.request_list') }}</a>
            @if($canCreatePrintRequest)
                <a class="btn btn-primary btn-sm" href="{{ $requestCreateUrl }}{{ request('request_modal') === '1' ? (str_contains($requestCreateUrl, '?') ? '&' : '?') . 'request_modal=1' : '' }}" @if($requestTab === 'leave-letter' && request('request_modal') !== '1') data-open-leave-request-form @endif><span aria-hidden="true">＋</span> {{ __('index.create_request_before_print') }}</a>
            @endif
        </div>
    </header>
    @include('admin.employees.profile.partials.request-workflow-steps', ['currentStep' => $selectedPrintRequest ? 3 : 1])
    <div class="employee-request-selection">
        <div class="employee-request-selection-heading">
            <strong>{{ __('index.saved_request') }}</strong>
            <span class="employee-request-count">{{ $printRequests->count() }}</span>
        </div>
        <p class="text-muted small mb-3">{{ __('index.linked_request_print_steps') }}</p>
    @if($printRequests->isNotEmpty())
    <form method="get" action="{{ route('admin.employees.profile.show', $employee->id) }}" class="employee-request-load-form">
        <input type="hidden" name="tab" value="{{ $requestTab }}">
        @if(request('request_modal') === '1')
            <input type="hidden" name="request_modal" value="1">
        @endif
        <div class="flex-grow-1">
            <label class="form-label" for="{{ $requestField }}">{{ __('index.saved_request') }}</label>
            <select class="form-select" id="{{ $requestField }}" name="{{ $requestField }}" required onchange="this.form.requestSubmit()">
                <option value="" disabled @selected(!$selectedPrintRequest)>{{ __('index.select_saved_request') }}</option>
                @foreach($printRequests as $printRequest)
                    <option value="{{ $printRequest->id }}" @selected($selectedPrintRequest?->id === $printRequest->id)>#{{ $printRequest->id }} · {{ $printRequest->leave_from ?? $printRequest->issue_date ?? $printRequest->resignation_date }}{{ $printRequest->leave_to ? ' → ' . $printRequest->leave_to : '' }} · {{ __('index.' . $printRequest->status) }}</option>
                @endforeach
            </select>
        </div>
    </form>
    @else
        <div class="employee-request-empty"><strong>{{ __('index.no_saved_requests') }}</strong><p>{{ __('index.create_request_to_continue') }}</p></div>
    @endif
    </div>
    @if($selectedPrintRequest)
        <dl class="employee-request-summary">
            <div><dt>{{ __('index.linked_saved_request') }}</dt><dd>#{{ $selectedPrintRequest->id }}</dd></div>
            <div><dt>{{ __('index.date') }}</dt><dd>{{ $selectedPrintRequest->leave_from ?? $selectedPrintRequest->issue_date ?? $selectedPrintRequest->resignation_date }}{{ $selectedPrintRequest->leave_to ? ' → ' . $selectedPrintRequest->leave_to : '' }}</dd></div>
            <div><dt>{{ __('index.status') }}</dt><dd><span class="badge bg-{{ match ($selectedPrintRequest->status) { 'approved' => 'success', 'rejected', 'cancelled' => 'danger', default => 'warning text-dark' } }}">{{ __('index.' . $selectedPrintRequest->status) }}</span></dd></div>
            <div><dt>{{ __('index.request_step_print') }}</dt><dd>{{ __('index.print_optional') }}</dd></div>
        </dl>
    @endif
    <p class="employee-request-result" data-print-request-result="{{ $requestTab }}" role="status" aria-live="polite"></p>
</div>
