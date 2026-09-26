@php
    $salaryCurrency = $contract?->salary_currency ?: 'USD';
    $formatMoney = fn ($amount) => filled($amount)
        ? ($salaryCurrency === 'KHR' ? 'KHR ' . number_format((float) $amount, 0) : '$' . number_format((float) $amount, 2))
        : 'N/A';
    $formatDate = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('F d, Y') : 'N/A';
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
    $certificateNumber = 'SAL-CERT-' . str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd');
@endphp
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <title>Salary Certificate</title>
    <style>
        @page { size: A4; margin: 18mm; }
        body { color: #172033; font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.55; }
        .top { border-bottom: 3px solid #0f766e; padding-bottom: 12px; }
        .company { color: #0f766e; font-size: 18pt; font-weight: bold; }
        .department { color: #64748b; font-size: 9pt; text-transform: uppercase; }
        .meta { color: #475569; font-size: 9pt; margin-top: 8px; }
        h1 { color: #17324d; font-size: 24pt; margin: 28px 0 2px; text-align: center; text-transform: uppercase; }
        .subtitle { color: #0f766e; font-size: 9pt; font-weight: bold; text-align: center; text-transform: uppercase; }
        .recipient { background: #f1f5f9; border-left: 4px solid #0f766e; margin: 24px 0; padding: 10px 14px; }
        .details, .salary { border-collapse: collapse; margin: 16px 0; width: 100%; }
        .details td { border: 1px solid #d9e2ec; padding: 8px; width: 50%; }
        .label { color: #64748b; display: block; font-size: 8pt; text-transform: uppercase; }
        .salary th { background: #17324d; color: #ffffff; padding: 8px; text-align: left; }
        .salary td { border: 1px solid #d9e2ec; padding: 8px; }
        .salary .amount { text-align: right; }
        .salary .total td { background: #e8f5f2; color: #0f5f59; font-weight: bold; }
        .benefits { background: #f8fafc; border: 1px solid #d9e2ec; padding: 10px; }
        .signatures { margin-top: 55px; width: 100%; }
        .signatures td { border-top: 1px solid #64748b; padding-top: 8px; width: 50%; }
        .footer { border-top: 1px solid #d9e2ec; color: #64748b; font-size: 8pt; margin-top: 40px; padding-top: 8px; text-align: center; }
    </style>
</head>
<body>
    <div class="top">
        <div class="company">{{ $employee->branch?->name ?: config('app.name') }}</div>
        <div class="department">Human Resources Department</div>
        <div class="meta">Certificate No: {{ $certificateNumber }} &nbsp;&nbsp;|&nbsp;&nbsp; Issue Date: {{ now()->format('F d, Y') }}</div>
    </div>
    <h1>Salary Certificate</h1>
    <div class="subtitle">Official Employment Document</div>
    <div class="recipient"><span class="label">Issued To</span><strong>To Whom It May Concern</strong></div>
    <p>This letter certifies that <strong>{{ $employee->name ?: $employee->english_name ?: 'N/A' }}</strong> is currently employed by <strong>{{ $employee->branch?->name ?: config('app.name') }}</strong>. Based on our official HR records, the employee details and current monthly compensation are confirmed below.</p>
    <table class="details">
        <tr>
            <td><span class="label">Employee ID</span><strong>{{ $employee->employee_code ?: $employee->username ?: 'N/A' }}</strong></td>
            <td><span class="label">Position</span><strong>{{ $employee->post?->post_name ?: 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td><span class="label">Department</span><strong>{{ $employee->department?->dept_name ?: 'N/A' }}</strong></td>
            <td><span class="label">Date Joined</span><strong>{{ $formatDate($employee->joining_date) }}</strong></td>
        </tr>
        <tr>
            <td><span class="label">Branch</span><strong>{{ $employee->branch?->name ?: 'N/A' }}</strong></td>
            <td><span class="label">Employment Status</span><strong>{{ $profile->employment_status ? ucfirst($profile->employment_status) : 'N/A' }}</strong></td>
        </tr>
    </table>
    <table class="salary">
        <tr><th>Monthly Compensation</th><th class="amount">Amount ({{ $salaryCurrency }})</th></tr>
        @foreach($compensationRows as $label => $amount)
            <tr><td>{{ $label }}</td><td class="amount">{{ $formatMoney($amount) }}</td></tr>
        @endforeach
        <tr class="total"><td>Gross Monthly Compensation</td><td class="amount">{{ $totalMonthly > 0 ? $formatMoney($totalMonthly) : 'N/A' }}</td></tr>
    </table>
    <p><strong>Payment method:</strong> {{ $profile->payment_method ?: 'N/A' }}<br>
       <strong>Salary payment date:</strong> {{ $profile->salary_payment_date ? 'Day ' . $profile->salary_payment_date . ' of each month' : 'N/A' }}</p>
    <div class="benefits"><strong>{{ __('index.other_benefits') }}</strong><br>{{ $profile->other_benefits ?: 'N/A' }}</div>
    <p>This certificate is issued at the employee's request for official purposes. The information stated above is accurate as of the issue date.</p>
    <table class="signatures"><tr><td><strong>Prepared by</strong><br>Human Resources Department</td><td><strong>Authorized signature and company stamp</strong><br>Authorized Representative</td></tr></table>
    <div class="footer">Confidential document &middot; Generated from verified HR salary records</div>
</body>
</html>
