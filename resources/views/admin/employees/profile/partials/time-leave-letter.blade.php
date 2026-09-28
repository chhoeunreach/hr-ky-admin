@php
    $notAvailable = __('index.not_available');
    $certificateLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : asset('assets/images/logo.png');
@endphp

<div class="employee-complete-toolbar employee-salary-certificate-toolbar mb-3">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="printTimeLeaveLetter()">
        <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
    </button>
</div>

<div class="employee-complete-paper employee-leave-letter-paper employee-time-leave-letter-paper employee-print-form">
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
            <span>{{ __('index.request_date') }}</span><input class="employee-print-form-input" type="text" aria-label="{{ __('index.request_date') }}">
        </div>
    </header>

    <div class="employee-salary-certificate-heading employee-print-form-heading">
        <span>{{ __('index.staff_form') }}</span>
        <h2>{{ __('index.time_leave_request_form') }}</h2>
    </div>

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
            <div class="employee-print-form-field is-blank"><span>{{ __('index.leave_date') }}</span><input type="date"></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.from_time') }}</span><input type="time"></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.to_time') }}</span><input type="time"></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.total_hours') }}</span><input type="number" min="0" step="0.5"></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.expected_return_time') }}</span><input type="time"></div>
            <div class="employee-print-form-field is-blank"><span>{{ __('index.destination') }}</span><input type="text"></div>
        </div>
    </section>

    <section class="employee-print-form-section">
        <h3>{{ __('index.reason_for_time_leave') }}</h3>
        <textarea class="employee-print-form-textarea" rows="3" aria-label="{{ __('index.reason_for_time_leave') }}"></textarea>
    </section>

    <section class="employee-print-form-section">
        <h3>{{ __('index.work_arrangement') }}</h3>
        <textarea class="employee-print-form-textarea" rows="3" aria-label="{{ __('index.work_arrangement') }}"></textarea>
    </section>

    <section class="employee-print-form-section employee-print-form-approval">
        <h3>{{ __('index.for_office_use') }}</h3>
        <div class="employee-print-form-options">
            <span>{{ __('index.decision') }}:</span>
            <label><input type="checkbox">{{ __('index.approved') }}</label>
            <label><input type="checkbox">{{ __('index.rejected') }}</label>
        </div>
        <textarea class="employee-print-form-textarea compact" rows="2" placeholder="{{ __('index.remark') }}" aria-label="{{ __('index.remark') }}"></textarea>
    </section>

    <div class="employee-print-form-signatures">
        <div><span>{{ __('index.employee_signature') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
        <div><span>{{ __('index.supervisor_signature') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
        <div><span>{{ __('index.hr_approval') }}</span><strong></strong><small>{{ __('index.date') }}: ____________</small></div>
    </div>
</div>
