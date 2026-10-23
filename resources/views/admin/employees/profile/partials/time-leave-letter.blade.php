@php
    $notAvailable = __('index.not_available');
    $certificateLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : asset('assets/images/logo.png');
@endphp

@include('admin.employees.profile.partials.print-request-selector', [
    'requestTab' => 'time-leave-letter', 'requestField' => 'time_leave_request_id',
    'printRequests' => $timeLeavePrintRequests, 'selectedPrintRequest' => $selectedTimeLeavePrintRequest,
    'requestListUrl' => route('admin.time-leave-request.index', ['requested_by' => $employee->id]),
    'requestCreateUrl' => route('admin.time-leave-request.create', ['requested_by' => $employee->id]),
    'canCreatePrintRequest' => auth('admin')->check() || \Illuminate\Support\Facades\Gate::allows('create_time_leave_request'),
])

<div class="employee-complete-toolbar employee-request-actions mb-3">
    <div class="employee-request-action-copy"><strong>{{ __('index.request_step_print') }}</strong><p>{{ $selectedTimeLeavePrintRequest ? __('index.print_optional_help') : __('index.select_saved_request_before_print') }}</p></div>
    <div class="employee-request-action-buttons">
    @if($canPrintTimeLeaveForm)
        <button type="button" class="btn btn-primary" onclick="printTimeLeaveLetter()" data-print-busy-label="{{ __('index.saving_print_record') }}" data-save-linked-print @disabled(!$selectedTimeLeavePrintRequest)>
            <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
        </button>
    @endif
    @if($canExportTimeLeaveForm)
        <button type="button"
                class="btn btn-outline-secondary btn-sm"
                data-export-type="timeLeaveForm"
                data-paper-selector="#time-leave-letter .employee-time-leave-letter-paper"
                data-file-name="Time-Leave-Request-{{ $employee->employee_code ?: $employee->id }}-{{ now()->format('Ymd') }}.docx"
                onclick="downloadEmployeeFormWord(this)" @disabled(!$selectedTimeLeavePrintRequest)>
            <i class="link-icon" data-feather="file-text"></i> {{ __('index.export_word') }}
        </button>
    @endif
    </div>
</div>

@if($selectedTimeLeavePrintRequest)
<div class="employee-complete-toolbar employee-request-preview-heading"><strong>{{ __('index.a4_request_preview') }}</strong><span>{{ __('index.review_before_print') }}</span></div>
<div class="employee-complete-paper employee-leave-letter-paper employee-time-leave-letter-paper employee-print-form" data-source-request-id="{{ $selectedTimeLeavePrintRequest?->id }}" data-request-tab="time-leave-letter">
    <div class="employee-resignation-national-heading">
        <strong>{{ __('index.kingdom_of_cambodia') }}</strong>
        <span>{{ __('index.nation_religion_king') }}</span>
    </div>

    <header class="employee-salary-certificate-letterhead">
        <div class="employee-salary-certificate-company">
            <img class="employee-salary-certificate-logo" src="{{ $certificateLogo }}" alt="Company Logo">
            <div>
                <div class="employee-salary-certificate-company-name">{{ $employee->branch?->name ?: config('app.name') }}</div>
                <div class="employee-salary-certificate-company-subtitle">{{ __('index.hr_department') }}</div>
            </div>
        </div>
        <div class="employee-salary-certificate-document-meta employee-print-form-meta">
            <span>{{ __('index.form_number') }}</span><input class="employee-print-form-input" type="text" aria-label="{{ __('index.form_number') }}" value="{{ $selectedTimeLeavePrintRequest?->id }}" readonly>
            <span>{{ __('index.request_date') }}</span><input class="employee-print-form-input" type="date" aria-label="{{ __('index.request_date') }}" value="{{ $selectedTimeLeavePrintRequest?->created_at?->format('Y-m-d') }}" readonly>
        </div>
    </header>

    <div class="employee-salary-certificate-heading employee-print-form-heading">
        <h2>{{ __('index.time_leave_request_form') }}</h2>
        <div class="employee-print-form-title-rule"></div>
    </div>

    <section class="employee-print-form-routing">
        <strong>{{ __('index.respectfully_submitted_to') }}</strong>
        <div><span>{{ __('index.through_department_head') }}</span><input class="employee-print-form-input" type="text"></div>
        <div><span>{{ __('index.through_branch_manager') }}</span><input class="employee-print-form-input" type="text"></div>
        <div><span>{{ __('index.submitted_to_hr') }}</span><input class="employee-print-form-input" type="text"></div>
    </section>

    <section class="employee-print-form-section">
        <h3>{{ __('index.employee_information') }}</h3>
        <div class="employee-print-form-grid">
            <div class="employee-print-form-field"><span>{{ __('index.employee_name') }}</span><strong>{{ $employee->name ?: $employee->english_name ?: $notAvailable }}</strong></div>
            <div class="employee-print-form-field"><span>{{ __('index.employee_id') }}</span><strong>{{ $employee->employee_code ?: $employee->username ?: $notAvailable }}</strong></div>
            <div class="employee-print-form-field"><span>{{ __('index.department') }}</span><strong>{{ $employee->department?->dept_name ?: $notAvailable }}</strong></div>
            <div class="employee-print-form-field"><span>{{ __('index.position') }}</span><strong>{{ $employee->post?->post_name ?: $notAvailable }}</strong></div>
            <div class="employee-print-form-field"><span>{{ __('index.phone') }}</span><strong>{{ $employee->phone ?: $notAvailable }}</strong></div>
            <div class="employee-print-form-field"><span>{{ __('index.direct_supervisor') }}</span><strong>{{ $employee->supervisor?->name ?: $notAvailable }}</strong></div>
        </div>
    </section>

    <section class="employee-print-form-section">
        <h3>{{ __('index.time_leave_details') }}</h3>
        <div class="employee-print-form-grid employee-print-form-grid-3">
            <div class="employee-print-form-field is-blank"><span>{{ __('index.leave_date') }}</span><input type="date" value="{{ $selectedTimeLeavePrintRequest?->issue_date }}" readonly></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.from_time') }}</span><input type="time" value="{{ $selectedTimeLeavePrintRequest?->start_time }}" readonly></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.to_time') }}</span><input type="time" value="{{ $selectedTimeLeavePrintRequest?->end_time }}" readonly></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.total_hours') }}</span><input type="number" min="0" step="0.01" value="{{ $selectedTimeLeavePrintRequest ? round(\Illuminate\Support\Carbon::parse($selectedTimeLeavePrintRequest->start_time)->diffInMinutes(\Illuminate\Support\Carbon::parse($selectedTimeLeavePrintRequest->end_time)) / 60, 2) : '' }}" readonly></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.expected_return_time') }}</span><input type="time"></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.destination') }}</span><input type="text"></div>
        </div>
    </section>

    <section class="employee-print-form-section">
        <h3>{{ __('index.reason_for_time_leave') }}</h3>
        <textarea class="employee-print-form-textarea" rows="3" aria-label="{{ __('index.reason_for_time_leave') }}" readonly>{{ $selectedTimeLeavePrintRequest?->reasons }}</textarea>
    </section>

    <section class="employee-print-form-section">
        <h3>{{ __('index.work_arrangement') }}</h3>
        <textarea class="employee-print-form-textarea" rows="3" aria-label="{{ __('index.work_arrangement') }}"></textarea>
    </section>

    <section class="employee-print-form-section employee-print-form-approval">
        <h3>{{ __('index.for_office_use') }}</h3>
        <div class="employee-print-form-options"><span>{{ __('index.status') }}:</span><strong>{{ $selectedTimeLeavePrintRequest ? __('index.' . $selectedTimeLeavePrintRequest->status) : $notAvailable }}</strong></div>
        <textarea class="employee-print-form-textarea compact" rows="2" placeholder="{{ __('index.remark') }}" aria-label="{{ __('index.remark') }}" readonly>{{ $selectedTimeLeavePrintRequest?->admin_remark }}</textarea>
    </section>

    <div class="employee-print-form-signatures">
        <div><span>{{ __('index.employee_signature') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
        <div><span>{{ __('index.supervisor_signature') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
        <div><span>{{ __('index.hr_approval') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
    </div>
    <div class="employee-request-print-record" data-print-record>{{ __('index.print_record_number') }}: —</div>
</div>

@else
<div class="employee-complete-toolbar employee-request-preview-empty">
    <span class="employee-request-empty-icon" aria-hidden="true">▤</span>
    <h4>{{ __('index.request_preview_empty_title') }}</h4>
    <p>{{ __('index.request_preview_empty_help') }}</p>
</div>
@endif
