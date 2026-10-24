@php
    $notAvailable = __('index.not_available');
    $certificateLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : asset('assets/images/logo.png');
    $joiningDate = $employee->joining_date
        ? \Illuminate\Support\Carbon::parse($employee->joining_date)->format('Y-m-d')
        : '';
    $genderLabel = match (strtolower((string) $employee->gender)) {
        'male' => __('index.male'),
        'female' => __('index.female'),
        default => $employee->gender,
    };
@endphp

@include('admin.employees.profile.partials.print-request-selector', [
    'requestTab' => 'resignation-letter', 'requestField' => 'resignation_request_id',
    'printRequests' => $resignationPrintRequests, 'selectedPrintRequest' => $selectedResignationPrintRequest,
    'requestListUrl' => route('admin.resignation.index', ['employee_id' => $employee->id]),
    'requestCreateUrl' => route('admin.resignation.create', ['employee_id' => $employee->id, 'return_to_profile' => 1]),
    'canCreatePrintRequest' => auth('admin')->check() || \Illuminate\Support\Facades\Gate::allows('create_resignation'),
])

<div class="employee-complete-toolbar employee-request-actions mb-3">
    <div class="employee-request-action-copy"><strong>{{ __('index.request_step_print') }}</strong><p>{{ $selectedResignationPrintRequest ? __('index.print_optional_help') : __('index.select_saved_request_before_print') }}</p></div>
    <div class="employee-request-action-buttons">
    @if($canPrintResignationForm)
        <button type="button" class="btn btn-primary" onclick="printResignationLetter()" data-print-busy-label="{{ __('index.saving_print_record') }}" data-save-linked-print @disabled(!$selectedResignationPrintRequest)>
            <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
        </button>
    @endif
    @if($canExportResignationForm)
        <button type="button"
                class="btn btn-outline-secondary btn-sm"
                data-export-type="resignationForm"
                data-paper-selector="#resignation-letter .employee-resignation-letter-paper"
                data-file-name="Resignation-Letter-{{ $employee->employee_code ?: $employee->id }}-{{ now()->format('Ymd') }}.docx"
                onclick="downloadEmployeeFormWord(this)" @disabled(!$selectedResignationPrintRequest)>
            <i class="link-icon" data-feather="file-text"></i> {{ __('index.export_word') }}
        </button>
    @endif
    </div>
</div>

@if($selectedResignationPrintRequest)
<div class="employee-complete-toolbar employee-request-preview-heading"><strong>{{ __('index.a4_request_preview') }}</strong><span>{{ __('index.review_before_print') }}</span></div>
<div class="employee-complete-paper employee-leave-letter-paper employee-resignation-letter-paper employee-print-form" data-source-request-id="{{ $selectedResignationPrintRequest->id }}" data-request-tab="resignation-letter">
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
            <span>{{ __('index.form_number') }}</span><input class="employee-print-form-input" type="text" aria-label="{{ __('index.form_number') }}" value="{{ $selectedResignationPrintRequest->id }}" readonly>
            <span>{{ __('index.request_date') }}</span><input class="employee-print-form-input" type="date" aria-label="{{ __('index.request_date') }}" value="{{ $selectedResignationPrintRequest->resignation_date }}" readonly>
        </div>
    </header>

    <div class="employee-salary-certificate-heading employee-print-form-heading">
        <h2>{{ __('index.resignation_request_form') }}</h2>
        <div class="employee-resignation-title-rule"></div>
    </div>

    <section class="employee-print-form-section employee-resignation-information">
        <h3>{{ __('index.employee_information') }}</h3>
        <div class="employee-print-form-grid employee-print-form-grid-3">
            <div class="employee-print-form-field"><span>{{ __('index.khmer_name') }}</span><input type="text" value="{{ $employee->name }}"></div>
            <div class="employee-print-form-field"><span>{{ __('index.latin_name') }}</span><input type="text" value="{{ $employee->english_name }}"></div>
            <div class="employee-print-form-field"><span>{{ __('index.gender') }}</span><input type="text" value="{{ $genderLabel }}"></div>
            <div class="employee-print-form-field"><span>{{ __('index.department') }}</span><input type="text" value="{{ $employee->department?->dept_name }}"></div>
            <div class="employee-print-form-field"><span>{{ __('index.employee_id') }}</span><input type="text" value="{{ $employee->employee_code ?: $employee->username }}"></div>
            <div class="employee-print-form-field"><span>{{ __('index.branch') }}</span><input type="text" value="{{ $employee->branch?->name }}"></div>
            <div class="employee-print-form-field"><span>{{ __('index.phone') }}</span><input type="text" value="{{ $employee->phone }}"></div>
            <div class="employee-print-form-field"><span>{{ __('index.joining_date') }}</span><input type="date" value="{{ $joiningDate }}"></div>
            <div class="employee-print-form-field"><span>{{ __('index.position') }}</span><input type="text" value="{{ $employee->post?->post_name }}"></div>
        </div>
    </section>

    <div class="employee-resignation-addressee">
        <span>{{ __('index.respectfully_submitted_to') }}</span>
        <strong>{{ __('index.chief_executive_officer') }} {{ $employee->branch?->name ?: config('app.name') }}</strong>
    </div>

    <div class="employee-resignation-reference">
        <div><strong>{{ __('index.resignation_subject_label') }}:</strong><span>{{ __('index.resignation_subject') }}</span></div>
        <div><strong>{{ __('index.through') }}:</strong><span>{{ __('index.administration_hr_manager') }}</span></div>
    </div>

    <div class="employee-resignation-body">
        <p>{{ __('index.resignation_intro') }}</p>

        <label for="employeeResignationReason">{{ __('index.resignation_reason') }}</label>
        <textarea id="employeeResignationReason" class="employee-print-form-textarea" rows="4" readonly>{{ html_entity_decode(strip_tags($selectedResignationPrintRequest->reason ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</textarea>

        <label for="employeeReturnedProperty">{{ __('index.company_property_returned') }}</label>
        <textarea id="employeeReturnedProperty" class="employee-print-form-textarea compact" rows="2"></textarea>

        <div class="employee-resignation-last-day">
            <span>{{ __('index.resignation_last_working_day') }}</span>
            <input type="date" aria-label="{{ __('index.resignation_last_working_day') }}" value="{{ $selectedResignationPrintRequest->last_working_day }}" readonly>
        </div>

        <p>{{ __('index.resignation_closing') }}</p>
    </div>

    <div class="employee-resignation-date">
        <span>{{ __('index.made_at') }}</span><input type="text">
        <span>{{ __('index.date') }}</span><input type="date">
    </div>

    <div class="employee-print-form-signatures employee-resignation-signatures">
        <div><span>{{ __('index.branch_manager_signature') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
        <div><span>{{ __('index.hr_signature') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
        <div><span>{{ __('index.resignation_requester') }}</span><strong></strong><small>{{ $employee->name ?: $employee->english_name ?: $notAvailable }}</small></div>
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
