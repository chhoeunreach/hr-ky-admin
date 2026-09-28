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

<div class="employee-complete-toolbar employee-salary-certificate-toolbar mb-3">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="printResignationLetter()">
        <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
    </button>
</div>

<div class="employee-complete-paper employee-leave-letter-paper employee-resignation-letter-paper employee-print-form">
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
        <span>{{ __('index.staff_form') }}</span>
        <h2>{{ __('index.resignation_request_form') }}</h2>
    </div>

    <section class="employee-print-form-section">
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
        <strong>{{ __('index.respectfully_submitted_to') }}</strong>
        <span>{{ __('index.chief_executive_officer') }} {{ $employee->branch?->name ?: config('app.name') }}</span>
    </div>

    <div class="employee-resignation-reference">
        <div><strong>{{ __('index.resignation_subject_label') }}:</strong><span>{{ __('index.resignation_subject') }}</span></div>
        <div><strong>{{ __('index.through') }}:</strong><span>{{ __('index.administration_hr_manager') }}</span></div>
    </div>

    <div class="employee-resignation-body">
        <p>{{ __('index.resignation_intro') }}</p>

        <label for="employeeResignationReason">{{ __('index.resignation_reason') }}</label>
        <textarea id="employeeResignationReason" class="employee-print-form-textarea" rows="4"></textarea>

        <label for="employeeReturnedProperty">{{ __('index.company_property_returned') }}</label>
        <textarea id="employeeReturnedProperty" class="employee-print-form-textarea compact" rows="2"></textarea>

        <div class="employee-resignation-last-day">
            <span>{{ __('index.resignation_last_working_day') }}</span>
            <input type="date" aria-label="{{ __('index.resignation_last_working_day') }}">
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
</div>
