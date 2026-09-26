@php
    $salaryCurrency = $contract->salary_currency ?: 'USD';
    $formatMoney = fn ($amount) => filled($amount)
        ? ($salaryCurrency === 'KHR' ? 'KHR ' . number_format((float) $amount, 0) : '$' . number_format((float) $amount, 2))
        : 'N/A';
    $formatDate = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('F d, Y') : 'N/A';
    $certificateLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : asset('assets/images/logo.png');
    $baseSalary = $profile->current_base_salary ?: ($latestSalary?->new_base_salary ?? null);
    $allowances = $profile->allowances ?: ($latestSalary?->allowance_after ?? null);
    $compensationRows = collect([
        __('index.starting_salary') => $profile->starting_salary,
        __('index.current_base_salary') => $baseSalary,
        __('index.allowances') => $allowances,
        __('index.commission') => $profile->commission,
        __('index.attendance_bonus') => $profile->attendance_bonus,
        __('index.punctuality_bonus') => $profile->punctuality_bonus,
        __('index.overtime') => $profile->overtime,
    ]);
    $totalMonthly = $compensationRows
        ->filter(fn ($value) => is_numeric($value))
        ->sum(fn ($value) => (float) $value);
    $preparedBy = auth('admin')->user()?->name ?: auth()->user()?->name;
    $certificateNumber = 'SAL-CERT-' . str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd');
@endphp

<div class="employee-complete-toolbar employee-salary-certificate-toolbar mb-3">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="printSalaryCertificate()">
        <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
    </button>
    <a class="btn btn-success btn-sm" href="{{ route('admin.employees.profile.salary-certificate.word', $employee->id) }}">
        <i class="link-icon" data-feather="file-text"></i> Download Word
    </a>
</div>

<div class="employee-complete-paper employee-salary-certificate-paper">
    <header class="employee-salary-certificate-letterhead">
        <div class="employee-salary-certificate-company">
            <img class="employee-salary-certificate-logo" src="{{ $certificateLogo }}" alt="Company Logo">
            <div>
                <div class="employee-salary-certificate-company-name">{{ $employee->branch?->name ?: config('app.name') }}</div>
                <div class="employee-salary-certificate-company-subtitle">Human Resources Department</div>
            </div>
        </div>
        <div class="employee-salary-certificate-document-meta">
            <span>Certificate No.</span>
            <strong>{{ $certificateNumber }}</strong>
            <span>Issue Date</span>
            <strong>{{ now()->format('F d, Y') }}</strong>
        </div>
    </header>

    <div class="employee-salary-certificate-heading">
        <span>Official Employment Document</span>
        <h2>Salary Certificate</h2>
    </div>

    <div class="employee-salary-certificate-recipient">
        <span>Issued To</span>
        <strong>To Whom It May Concern</strong>
    </div>

    <div class="employee-salary-certificate-body">
        <p>
            This letter certifies that <strong>{{ $employee->name ?: $employee->english_name ?: 'N/A' }}</strong>
            is currently employed by <strong>{{ $employee->branch?->name ?: config('app.name') }}</strong>.
            Based on our official HR records, the employee details and current monthly compensation are confirmed below.
        </p>
    </div>

    <div class="employee-salary-certificate-employee-grid">
        <div><span>Employee ID</span><strong>{{ $employee->employee_code ?: $employee->username ?: 'N/A' }}</strong></div>
        <div><span>Position</span><strong>{{ $employee->post?->post_name ?: 'N/A' }}</strong></div>
        <div><span>Department</span><strong>{{ $employee->department?->dept_name ?: 'N/A' }}</strong></div>
        <div><span>Date Joined</span><strong>{{ $formatDate($employee->joining_date) }}</strong></div>
        <div><span>Branch</span><strong>{{ $employee->branch?->name ?: 'N/A' }}</strong></div>
        <div><span>Employment Status</span><strong>{{ $profile->employment_status ? ucfirst($profile->employment_status) : 'N/A' }}</strong></div>
    </div>

    <table class="table table-sm employee-salary-certificate-table mb-3">
        <thead>
            <tr><th>Monthly Compensation</th><th class="text-end">Amount ({{ $salaryCurrency }})</th></tr>
        </thead>
        <tbody>
            @foreach($compensationRows as $label => $amount)
                <tr><td>{{ $label }}</td><td class="text-end">{{ $formatMoney($amount) }}</td></tr>
            @endforeach
            <tr class="employee-salary-certificate-total">
                <th>Gross Monthly Compensation</th>
                <th class="text-end">{{ $totalMonthly > 0 ? $formatMoney($totalMonthly) : 'N/A' }}</th>
            </tr>
        </tbody>
    </table>

    <div class="employee-salary-certificate-facts">
        <div><span>{{ __('index.payment_method') }}</span><strong>{{ $profile->payment_method ?: 'N/A' }}</strong></div>
        <div><span>{{ __('index.salary_payment_date') }}</span><strong>{{ $profile->salary_payment_date ? 'Day ' . $profile->salary_payment_date . ' of each month' : 'N/A' }}</strong></div>
    </div>

    <div class="employee-salary-certificate-benefits">
        <strong>{{ __('index.other_benefits') }}</strong>
        <p>{{ $profile->other_benefits ?: 'N/A' }}</p>
    </div>

    <div class="employee-salary-certificate-body">
        <p>This certificate is issued at the employee's request for official purposes. The information stated above is accurate as of the issue date.</p>
    </div>

    <div class="employee-salary-certificate-signature">
        <div>
            <span>Prepared by</span>
            <strong>{{ $preparedBy ?: 'Human Resources' }}</strong>
            <small>Human Resources Department</small>
        </div>
        <div>
            <span>Authorized signature and company stamp</span>
            <strong>&nbsp;</strong>
            <small>Authorized Representative</small>
        </div>
    </div>

    <div class="employee-salary-certificate-note">
        Confidential document &middot; Generated from verified HR salary records
    </div>
</div>
