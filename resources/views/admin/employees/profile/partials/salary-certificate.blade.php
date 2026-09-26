@php
    $notAvailable = __('index.not_available');
    $salaryCurrency = $contract->salary_currency ?: 'USD';
    $formatMoney = fn ($amount) => filled($amount)
        ? ($salaryCurrency === 'KHR' ? 'KHR ' . number_format((float) $amount, 0) : '$' . number_format((float) $amount, 2))
        : $notAvailable;
    $formatDate = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('F d, Y') : $notAvailable;
    $certificateLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : asset('assets/images/logo.png');
    $baseSalary = $profile->current_base_salary ?: ($latestSalary?->new_base_salary ?? null);
    $allowances = $profile->allowances ?: ($latestSalary?->allowance_after ?? null);
    $compensationRows = collect([
        __('index.starting_salary') => $profile->starting_salary,
        __('index.current_base_salary') => $baseSalary,
        __('index.allowances') => filled($allowances) ? $allowances : 20,
        __('index.commission') => filled($profile->commission) ? $profile->commission : 20,
        __('index.attendance_bonus') => filled($profile->attendance_bonus) ? $profile->attendance_bonus : 20,
        __('index.punctuality_bonus') => filled($profile->punctuality_bonus) ? $profile->punctuality_bonus : 20,
        __('index.overtime') => filled($profile->overtime) ? $profile->overtime : 20,
    ]);
    $totalMonthly = $compensationRows
        ->filter(fn ($value) => is_numeric($value))
        ->sum(fn ($value) => (float) $value);
    $employmentStatus = $profile->employment_status ?: ((int) $employee->is_active === 1 ? 'active' : 'inactive');
    $employmentStatusLabel = __('index.' . $employmentStatus);
    $preparedBy = auth('admin')->user()?->name ?: auth()->user()?->name;
    $certificateNumber = 'SAL-CERT-' . str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd');
    $certificateWordData = [
        'fileName' => 'Salary-Certificate-' . ($employee->employee_code ?: $employee->id) . '-' . now()->format('Ymd') . '.docx',
        'logoUrl' => $certificateLogo,
        'company' => $employee->branch?->name ?: config('app.name'),
        'hrDepartment' => __('index.salary_certificate_hr_department'),
        'certificateNumberLabel' => __('index.salary_certificate_number'),
        'certificateNumber' => $certificateNumber,
        'issueDateLabel' => __('index.salary_certificate_issue_date'),
        'issueDate' => now()->format('F d, Y'),
        'documentType' => __('index.salary_certificate_official_document'),
        'title' => __('index.salary_certificate'),
        'issuedToLabel' => __('index.salary_certificate_issued_to'),
        'issuedTo' => __('index.salary_certificate_to_whom'),
        'intro' => __('index.salary_certificate_intro', [
            'employee' => $employee->name ?: $employee->english_name ?: $notAvailable,
            'company' => $employee->branch?->name ?: config('app.name'),
        ]),
        'details' => [
            [__('index.salary_certificate_employee_id'), $employee->employee_code ?: $employee->username ?: $notAvailable],
            [__('index.position'), $employee->post?->post_name ?: $notAvailable],
            [__('index.department'), $employee->department?->dept_name ?: $notAvailable],
            [__('index.salary_certificate_date_joined'), $formatDate($employee->joining_date)],
            [__('index.branch'), $employee->branch?->name ?: $notAvailable],
            [__('index.employment_status'), $employmentStatusLabel],
        ],
        'compensationTitle' => __('index.salary_certificate_monthly_compensation'),
        'amountTitle' => __('index.salary_certificate_amount', ['currency' => $salaryCurrency]),
        'compensation' => $compensationRows
            ->map(fn ($amount, $label) => ['label' => $label, 'amount' => $formatMoney($amount)])
            ->values()
            ->all(),
        'grossLabel' => __('index.salary_certificate_gross_monthly'),
        'grossAmount' => $totalMonthly > 0 ? $formatMoney($totalMonthly) : $notAvailable,
        'paymentMethodLabel' => __('index.payment_method'),
        'paymentMethod' => $profile->payment_method ?: __('index.salary_certificate_cash'),
        'paymentDateLabel' => __('index.salary_payment_date'),
        'paymentDate' => $profile->salary_payment_date
            ? __('index.salary_certificate_payment_day', ['day' => $profile->salary_payment_date])
            : __('index.salary_certificate_payment_period'),
        'benefitsLabel' => __('index.other_benefits'),
        'benefits' => $profile->other_benefits ?: __('index.salary_certificate_benefits_default'),
        'purpose' => __('index.salary_certificate_purpose'),
        'preparedByLabel' => __('index.salary_certificate_prepared_by'),
        'preparedBy' => $preparedBy ?: __('index.salary_certificate_hr_department'),
        'authorizedSignatureLabel' => __('index.salary_certificate_authorized_signature'),
        'authorizedRepresentative' => __('index.salary_certificate_authorized_representative'),
        'footer' => __('index.salary_certificate_footer'),
    ];
@endphp

<div class="employee-complete-toolbar employee-salary-certificate-toolbar mb-3">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="printSalaryCertificate()">
        <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
    </button>
    <button type="button"
            class="btn btn-success btn-sm"
            id="downloadSalaryCertificateWordButton"
            data-fallback-url="{{ route('admin.employees.profile.salary-certificate.word', $employee->id) }}"
            onclick="downloadSalaryCertificateWord()">
        <i class="link-icon" data-feather="file-text"></i> {{ __('index.salary_certificate_download_word') }}
    </button>
</div>

<script type="application/json" id="salaryCertificateWordData">{!! json_encode($certificateWordData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

<div class="employee-complete-paper employee-salary-certificate-paper">
    <header class="employee-salary-certificate-letterhead">
        <div class="employee-salary-certificate-company">
            <img class="employee-salary-certificate-logo" src="{{ $certificateLogo }}" alt="Company Logo">
            <div>
                <div class="employee-salary-certificate-company-name">{{ $employee->branch?->name ?: config('app.name') }}</div>
                <div class="employee-salary-certificate-company-subtitle">{{ __('index.salary_certificate_hr_department') }}</div>
            </div>
        </div>
        <div class="employee-salary-certificate-document-meta">
            <span>{{ __('index.salary_certificate_number') }}</span>
            <strong>{{ $certificateNumber }}</strong>
            <span>{{ __('index.salary_certificate_issue_date') }}</span>
            <strong>{{ now()->format('F d, Y') }}</strong>
        </div>
    </header>

    <div class="employee-salary-certificate-heading">
        <span>{{ __('index.salary_certificate_official_document') }}</span>
        <h2>{{ __('index.salary_certificate') }}</h2>
    </div>

    <div class="employee-salary-certificate-recipient">
        <span>{{ __('index.salary_certificate_issued_to') }}</span>
        <strong>{{ __('index.salary_certificate_to_whom') }}</strong>
    </div>

    <div class="employee-salary-certificate-body">
        <p>{{ __('index.salary_certificate_intro', [
            'employee' => $employee->name ?: $employee->english_name ?: $notAvailable,
            'company' => $employee->branch?->name ?: config('app.name'),
        ]) }}</p>
    </div>

    <div class="employee-salary-certificate-employee-grid">
        <div><span>{{ __('index.salary_certificate_employee_id') }}</span><strong>{{ $employee->employee_code ?: $employee->username ?: $notAvailable }}</strong></div>
        <div><span>{{ __('index.position') }}</span><strong>{{ $employee->post?->post_name ?: $notAvailable }}</strong></div>
        <div><span>{{ __('index.department') }}</span><strong>{{ $employee->department?->dept_name ?: $notAvailable }}</strong></div>
        <div><span>{{ __('index.salary_certificate_date_joined') }}</span><strong>{{ $formatDate($employee->joining_date) }}</strong></div>
        <div><span>{{ __('index.branch') }}</span><strong>{{ $employee->branch?->name ?: $notAvailable }}</strong></div>
        <div><span>{{ __('index.employment_status') }}</span><strong>{{ $employmentStatusLabel }}</strong></div>
    </div>

    <table class="table table-sm employee-salary-certificate-table mb-3">
        <thead>
            <tr><th>{{ __('index.salary_certificate_monthly_compensation') }}</th><th class="text-end">{{ __('index.salary_certificate_amount', ['currency' => $salaryCurrency]) }}</th></tr>
        </thead>
        <tbody>
            @foreach($compensationRows as $label => $amount)
                <tr><td>{{ $label }}</td><td class="text-end">{{ $formatMoney($amount) }}</td></tr>
            @endforeach
            <tr class="employee-salary-certificate-total">
                <th>{{ __('index.salary_certificate_gross_monthly') }}</th>
                <th class="text-end">{{ $totalMonthly > 0 ? $formatMoney($totalMonthly) : $notAvailable }}</th>
            </tr>
        </tbody>
    </table>

    <div class="employee-salary-certificate-facts">
        <div><span>{{ __('index.payment_method') }}</span><strong>{{ $profile->payment_method ?: __('index.salary_certificate_cash') }}</strong></div>
        <div><span>{{ __('index.salary_payment_date') }}</span><strong>{{ $profile->salary_payment_date ? __('index.salary_certificate_payment_day', ['day' => $profile->salary_payment_date]) : __('index.salary_certificate_payment_period') }}</strong></div>
    </div>

    <div class="employee-salary-certificate-benefits">
        <strong>{{ __('index.other_benefits') }}</strong>
        <p>{{ $profile->other_benefits ?: __('index.salary_certificate_benefits_default') }}</p>
    </div>

    <div class="employee-salary-certificate-body">
        <p>{{ __('index.salary_certificate_purpose') }}</p>
    </div>

    <div class="employee-salary-certificate-signature">
        <div>
            <span>{{ __('index.salary_certificate_prepared_by') }}</span>
            <strong>{{ $preparedBy ?: __('index.salary_certificate_hr_department') }}</strong>
            <small>{{ __('index.salary_certificate_hr_department') }}</small>
        </div>
        <div>
            <span>{{ __('index.salary_certificate_authorized_signature') }}</span>
            <strong>&nbsp;</strong>
            <small>{{ __('index.salary_certificate_authorized_representative') }}</small>
        </div>
    </div>

    <div class="employee-salary-certificate-note">
        {{ __('index.salary_certificate_footer') }}
    </div>
</div>
