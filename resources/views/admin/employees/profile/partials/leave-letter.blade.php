@php
    $notAvailable = __('index.not_available');
    $certificateLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : asset('assets/images/logo.png');
@endphp

@include('admin.employees.profile.partials.print-request-selector', [
    'requestTab' => 'leave-letter', 'requestField' => 'leave_request_id',
    'printRequests' => $leavePrintRequests, 'selectedPrintRequest' => $selectedLeavePrintRequest,
    'requestListUrl' => route('admin.leave-request.index', ['requested_by' => $employee->id]),
    'requestCreateUrl' => route('admin.leave-request.add', ['requested_by' => $employee->id]),
    'canCreatePrintRequest' => auth('admin')->check() || \Illuminate\Support\Facades\Gate::any(['request_leave', 'access_admin_leave']),
])

<div class="employee-complete-toolbar employee-request-actions mb-3">
    <div class="employee-request-action-copy"><strong>{{ __('index.request_step_print') }}</strong><p>{{ $selectedLeavePrintRequest ? __('index.print_optional_help') : __('index.select_saved_request_before_print') }}</p></div>
    <div class="employee-request-action-buttons">
    @if($canPrintLeaveForm)
        <button type="button" class="btn btn-primary" onclick="printLeaveLetter()" data-print-busy-label="{{ __('index.saving_print_record') }}" data-save-linked-print @disabled(!$selectedLeavePrintRequest)>
            <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
        </button>
    @endif
    @if($canExportLeaveForm)
        <button type="button"
                class="btn btn-outline-secondary btn-sm"
                data-export-type="leaveForm"
                data-paper-selector="#leave-letter .employee-leave-letter-paper"
                data-file-name="Leave-Request-{{ $employee->employee_code ?: $employee->id }}-{{ now()->format('Ymd') }}.docx"
                onclick="downloadEmployeeFormWord(this)" @disabled(!$selectedLeavePrintRequest)>
            <i class="link-icon" data-feather="file-text"></i> {{ __('index.export_word') }}
        </button>
    @endif
    </div>
</div>

@if($selectedLeavePrintRequest)
<div class="employee-complete-toolbar employee-request-preview-heading"><strong>{{ __('index.a4_request_preview') }}</strong><span>{{ __('index.review_before_print') }}</span></div>
<div class="employee-complete-paper employee-leave-letter-paper employee-print-form" data-source-request-id="{{ $selectedLeavePrintRequest?->id }}" data-request-tab="leave-letter">
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
            <span>{{ __('index.form_number') }}</span><input class="employee-print-form-input" type="text" aria-label="{{ __('index.form_number') }}" value="{{ $selectedLeavePrintRequest?->id }}" readonly>
            <span>{{ __('index.request_date') }}</span><input class="employee-print-form-input" type="text" aria-label="{{ __('index.request_date') }}" value="{{ $selectedLeavePrintRequest?->leave_requested_date ? \Illuminate\Support\Carbon::parse($selectedLeavePrintRequest->leave_requested_date)->format('d M Y') : $notAvailable }}" readonly>
        </div>
    </header>

    <div class="employee-salary-certificate-heading employee-print-form-heading">
        <h2>{{ __('index.leave_request_form') }}</h2>
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
        <h3>{{ __('index.leave_details') }}</h3>
        <div class="employee-print-form-field"><span>{{ __('index.leave_type') }}</span><strong>{{ $selectedLeavePrintRequest?->leaveType?->name ?: $notAvailable }}</strong></div>
        <div class="employee-print-form-grid employee-print-form-grid-3">
            <div class="employee-print-form-field"><span>{{ __('index.leave_from') }}</span><strong>{{ $selectedLeavePrintRequest?->leave_from ? \Illuminate\Support\Carbon::parse($selectedLeavePrintRequest->leave_from)->format('d M Y') : $notAvailable }}</strong></div>
            <div class="employee-print-form-field"><span>{{ __('index.leave_to') }}</span><strong>{{ $selectedLeavePrintRequest?->leave_to ? \Illuminate\Support\Carbon::parse($selectedLeavePrintRequest->leave_to)->format('d M Y') : $notAvailable }}</strong></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.number_of_days') }}</span><input type="number" min="0" step="0.5" value="{{ $selectedLeavePrintRequest?->no_of_days }}" readonly></div>
        </div>

    </section>

    <section class="employee-print-form-section">
        <h3>{{ __('index.reason_for_leave') }}</h3>
        <textarea class="employee-print-form-textarea" rows="3" aria-label="{{ __('index.reason_for_leave') }}" readonly>{{ $selectedLeavePrintRequest?->reasons }}</textarea>
    </section>

    <section class="employee-print-form-section">
        <h3>{{ __('index.work_handover') }}</h3>
        <div class="employee-print-form-grid">
            <div class="employee-print-form-field is-blank"><span>{{ __('index.handover_to') }}</span><input type="text"></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.contact_during_leave') }}</span><input type="text"></div>
        </div>
        <textarea class="employee-print-form-textarea compact" rows="2" aria-label="{{ __('index.work_handover') }}"></textarea>
    </section>

    <section class="employee-print-form-section employee-print-form-approval">
        <h3>{{ __('index.for_office_use') }}</h3>
        <div class="employee-print-form-options"><span>{{ __('index.status') }}:</span><strong>{{ $selectedLeavePrintRequest ? __('index.' . $selectedLeavePrintRequest->status) : $notAvailable }}</strong></div>
        <textarea class="employee-print-form-textarea compact" rows="2" placeholder="{{ __('index.remark') }}" aria-label="{{ __('index.remark') }}" readonly>{{ $selectedLeavePrintRequest?->admin_remark }}</textarea>
    </section>

    <div class="employee-print-form-signatures">
        <div><span>{{ __('index.employee_signature') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
        <div><span>{{ __('index.supervisor_signature') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
        <div><span>{{ __('index.hr_approval') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
    </div>

    <div class="employee-print-form-notes">
        <strong>{{ __('index.note') }}:</strong>
        <ol>
            <li>{{ __('index.leave_form_notice_advance') }}</li>
            <li>{{ __('index.leave_form_notice_medical') }}</li>
        </ol>
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
