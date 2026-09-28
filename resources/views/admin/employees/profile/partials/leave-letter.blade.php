@php
    $notAvailable = __('index.not_available');
    $certificateLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : asset('assets/images/logo.png');
@endphp

<div class="employee-complete-toolbar employee-salary-certificate-toolbar mb-3">
    @if($canPrintLeaveForm)
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="printLeaveLetter()">
            <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
        </button>
    @endif
    @if($canExportLeaveForm)
        <button type="button"
                class="btn btn-outline-secondary btn-sm"
                data-export-type="leaveForm"
                data-paper-selector="#leave-letter .employee-leave-letter-paper"
                data-file-name="Leave-Request-{{ $employee->employee_code ?: $employee->id }}-{{ now()->format('Ymd') }}.docx"
                onclick="downloadEmployeeFormWord(this)">
            <i class="link-icon" data-feather="file-text"></i> {{ __('index.export_word') }}
        </button>
    @endif
</div>

<div class="employee-complete-paper employee-leave-letter-paper employee-print-form">
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
            <span>{{ __('index.form_number') }}</span><input class="employee-print-form-input" type="text" aria-label="{{ __('index.form_number') }}">
            <span>{{ __('index.request_date') }}</span><input class="employee-print-form-input" type="date" aria-label="{{ __('index.request_date') }}">
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
        <div class="employee-print-form-options">
            <span>{{ __('index.leave_type') }}:</span>
            <label><input type="radio" name="leave_type">{{ __('index.annual_leave') }}</label>
            <label><input type="radio" name="leave_type">{{ __('index.special_leave') }}</label>
            <label><input type="radio" name="leave_type">{{ __('index.sick_leave') }}</label>
            <label><input type="radio" name="leave_type">{{ __('index.personal_leave') }}</label>
            <label><input type="radio" name="leave_type">{{ __('index.work_accident_leave') }}</label>
            <label><input type="radio" name="leave_type">{{ __('index.family_death_leave') }}</label>
            <label><input type="radio" name="leave_type">{{ __('index.employee_marriage_leave') }}</label>
            <label><input type="radio" name="leave_type">{{ __('index.other') }}</label>
        </div>
        <div class="employee-print-form-grid employee-print-form-grid-3">
            <div class="employee-print-form-field is-blank"><span>{{ __('index.leave_from') }}</span><input type="date"></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.leave_to') }}</span><input type="date"></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.number_of_days') }}</span><input type="number" min="0" step="0.5"></div>
        </div>
        <div class="employee-print-form-options">
            <span>{{ __('index.leave_duration') }}:</span>
            <label><input type="radio" name="leave_duration">{{ __('index.full_day') }}</label>
            <label><input type="radio" name="leave_duration">{{ __('index.half_day') }}</label>
            <label><input type="radio" name="leave_duration">{{ __('index.morning') }}</label>
            <label><input type="radio" name="leave_duration">{{ __('index.afternoon') }}</label>
        </div>
    </section>

    <section class="employee-print-form-section">
        <h3>{{ __('index.reason_for_leave') }}</h3>
        <textarea class="employee-print-form-textarea" rows="3" aria-label="{{ __('index.reason_for_leave') }}"></textarea>
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
        <div class="employee-print-form-options">
            <span>{{ __('index.decision') }}:</span>
            <label><input type="radio" name="leave_decision">{{ __('index.approved') }}</label>
            <label><input type="radio" name="leave_decision">{{ __('index.rejected') }}</label>
        </div>
        <textarea class="employee-print-form-textarea compact" rows="2" placeholder="{{ __('index.remark') }}" aria-label="{{ __('index.remark') }}"></textarea>
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
</div>
