@php
    $notAvailable = __('index.not_available');
    $salaryCurrency = $contract?->salary_currency ?: 'USD';
    $formatMoney = fn ($amount) => filled($amount)
        ? ($salaryCurrency === 'KHR' ? 'KHR ' . number_format((float) $amount, 0) : '$' . number_format((float) $amount, 2))
        : $notAvailable;
    $formatDate = fn ($date) => $date
        ? \Illuminate\Support\Carbon::parse($date)->locale(app()->getLocale())->translatedFormat('F d, Y')
        : $notAvailable;
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
    $issueDate = now()->locale(app()->getLocale())->translatedFormat('F d, Y');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <title>{{ __('index.salary_certificate') }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument><w:View>Print</w:View><w:Zoom>90</w:Zoom></w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page SalaryCertificate { size: 595.3pt 841.9pt; margin: 0; mso-page-orientation: portrait; }
        div.SalaryCertificate { page: SalaryCertificate; }
        * { box-sizing: border-box; }
        body { background: #fff; color: #1e293b; font-family: "Khmer OS Battambang", "Khmer OS", Arial, sans-serif; font-size: 9pt; line-height: 1.45; margin: 0; padding: 0; }
        .page { border-top: 4pt solid #0f766e; min-height: 841.9pt; padding: 28pt 40pt 23pt; width: 595.3pt; }
        table { border-collapse: collapse; }
        .letterhead { border-bottom: 1pt solid #cbd5e1; padding-bottom: 10pt; width: 100%; }
        .letterhead td { vertical-align: top; }
        .brand-table { width: 100%; }
        .logo-cell { width: 46pt; }
        .logo { height: 39pt; width: 39pt; }
        .company-name { color: #17324d; font-size: 13pt; font-weight: bold; line-height: 1.25; }
        .company-subtitle, .label { color: #64748b; font-size: 7pt; font-weight: bold; text-transform: uppercase; }
        .meta { width: 190pt; }
        .meta-table { margin-left: auto; width: 100%; }
        .meta-table td { color: #334155; font-size: 7.5pt; padding: 1pt 0 1pt 6pt; text-align: right; }
        .meta-table .label { font-size: 6.5pt; white-space: nowrap; }
        .heading { margin: 17pt 0 13pt; text-align: center; }
        .heading .eyebrow { color: #0f766e; font-size: 7pt; font-weight: bold; text-transform: uppercase; }
        .heading h1 { color: #17324d; font-size: 19pt; margin: 2pt 0 0; text-transform: uppercase; }
        .recipient { background: #f1f5f9; border-left: 3pt solid #0f766e; margin-bottom: 10pt; padding: 6pt 9pt; }
        .recipient strong { color: #17324d; display: block; font-size: 8.5pt; }
        .body-copy { margin: 10pt 0; }
        .details, .salary, .facts, .signatures { table-layout: fixed; width: 100%; }
        .details { margin-bottom: 10pt; }
        .details td, .facts td { border: 1pt solid #dbe3ef; padding: 5pt 7pt; vertical-align: top; width: 50%; }
        .details strong, .facts strong { color: #1e293b; display: block; font-size: 8pt; margin-top: 1pt; }
        .salary { margin-bottom: 10pt; }
        .salary th, .salary td { border: 1pt solid #dbe3ef; font-size: 8pt; padding: 4pt 7pt; }
        .salary thead th { background: #17324d; color: #fff; font-weight: bold; text-align: left; }
        .salary .amount { text-align: right; }
        .salary .total th { background: #e8f5f2; color: #0f5f59; font-weight: bold; }
        .facts { margin-bottom: 10pt; }
        .benefits { background: #f8fafc; border: 1pt solid #dbe3ef; margin-bottom: 10pt; padding: 6pt 8pt; }
        .benefits strong { display: block; margin-bottom: 2pt; }
        .signatures { margin-top: 55pt; }
        .signatures td { border-top: 1pt solid #64748b; padding-top: 5pt; vertical-align: top; width: 50%; }
        .signatures td:first-child { padding-right: 14pt; }
        .signatures td:last-child { padding-left: 14pt; }
        .signature-title { color: #64748b; font-size: 7pt; font-weight: bold; text-transform: uppercase; }
        .signature-name { color: #1e293b; display: block; font-size: 8pt; min-height: 13pt; }
        .signature-role { color: #64748b; font-size: 7pt; }
        .footer { border-top: 1pt solid #dbe3ef; color: #64748b; font-size: 7pt; font-weight: bold; margin-top: 14pt; padding-top: 5pt; text-align: center; }
    </style>
</head>
<body>
<div class="SalaryCertificate">
    <div class="page">
        <table class="letterhead">
            <tr>
                <td>
                    <table class="brand-table">
                        <tr>
                            @if($certificateLogoData)
                                <td class="logo-cell"><img class="logo" src="{{ $certificateLogoData }}" alt=""></td>
                            @endif
                            <td>
                                <div class="company-name">{{ $employee->branch?->name ?: config('app.name') }}</div>
                                <div class="company-subtitle">{{ __('index.salary_certificate_hr_department') }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="meta">
                    <table class="meta-table">
                        <tr><td class="label">{{ __('index.salary_certificate_number') }}</td><td><strong>{{ $certificateNumber }}</strong></td></tr>
                        <tr><td class="label">{{ __('index.salary_certificate_issue_date') }}</td><td><strong>{{ $issueDate }}</strong></td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="heading">
            <div class="eyebrow">{{ __('index.salary_certificate_official_document') }}</div>
            <h1>{{ __('index.salary_certificate') }}</h1>
        </div>

        <div class="recipient">
            <span class="label">{{ __('index.salary_certificate_issued_to') }}</span>
            <strong>{{ __('index.salary_certificate_to_whom') }}</strong>
        </div>

        <p class="body-copy">{{ __('index.salary_certificate_intro', [
            'employee' => $employee->name ?: $employee->english_name ?: $notAvailable,
            'company' => $employee->branch?->name ?: config('app.name'),
        ]) }}</p>

        <table class="details">
            <tr>
                <td><span class="label">{{ __('index.salary_certificate_employee_id') }}</span><strong>{{ $employee->employee_code ?: $employee->username ?: $notAvailable }}</strong></td>
                <td><span class="label">{{ __('index.position') }}</span><strong>{{ $employee->post?->post_name ?: $notAvailable }}</strong></td>
            </tr>
            <tr>
                <td><span class="label">{{ __('index.department') }}</span><strong>{{ $employee->department?->dept_name ?: $notAvailable }}</strong></td>
                <td><span class="label">{{ __('index.salary_certificate_date_joined') }}</span><strong>{{ $formatDate($employee->joining_date) }}</strong></td>
            </tr>
            <tr>
                <td><span class="label">{{ __('index.branch') }}</span><strong>{{ $employee->branch?->name ?: $notAvailable }}</strong></td>
                <td><span class="label">{{ __('index.employment_status') }}</span><strong>{{ $employmentStatusLabel }}</strong></td>
            </tr>
            <tr>
                <td><span class="label">{{ __('index.phone_number') }}</span><strong>{{ $employee->phone ?: $notAvailable }}</strong></td>
                <td><span class="label">{{ __('index.national_id') }}</span><strong>{{ $profile->national_id ?: $notAvailable }}</strong></td>
            </tr>
        </table>

        <table class="salary">
            <thead><tr><th>{{ __('index.salary_certificate_monthly_compensation') }}</th><th class="amount">{{ __('index.salary_certificate_amount', ['currency' => $salaryCurrency]) }}</th></tr></thead>
            <tbody>
            @foreach($compensationRows as $label => $amount)
                <tr><td>{{ $label }}</td><td class="amount">{{ $formatMoney($amount) }}</td></tr>
            @endforeach
                <tr class="total"><th>{{ __('index.salary_certificate_gross_monthly') }}</th><th class="amount">{{ $totalMonthly > 0 ? $formatMoney($totalMonthly) : $notAvailable }}</th></tr>
            </tbody>
        </table>

        <table class="facts">
            <tr>
                <td><span class="label">{{ __('index.payment_method') }}</span><strong>{{ $profile->payment_method ?: __('index.salary_certificate_cash') }}</strong></td>
                <td><span class="label">{{ __('index.salary_payment_date') }}</span><strong>{{ $profile->salary_payment_date ? __('index.salary_certificate_payment_day', ['day' => $profile->salary_payment_date]) : __('index.salary_certificate_payment_period') }}</strong></td>
            </tr>
        </table>

        <div class="benefits">
            <strong>{{ __('index.other_benefits') }}</strong>
            {{ $profile->other_benefits ?: __('index.salary_certificate_benefits_default') }}
        </div>

        <p class="body-copy">{{ __('index.salary_certificate_purpose') }}</p>

        <table class="signatures">
            <tr>
                <td>
                    <span class="signature-title">{{ __('index.salary_certificate_prepared_by') }}</span>
                    <strong class="signature-name">{{ $preparedBy ?: __('index.salary_certificate_hr_department') }}</strong>
                    <span class="signature-role">{{ __('index.salary_certificate_hr_department') }}</span>
                </td>
                <td>
                    <span class="signature-title">{{ __('index.salary_certificate_authorized_signature') }}</span>
                    <strong class="signature-name">&nbsp;</strong>
                    <span class="signature-role">{{ __('index.salary_certificate_authorized_representative') }}</span>
                </td>
            </tr>
        </table>

        <div class="footer">{{ __('index.salary_certificate_footer') }}</div>
    </div>
</div>
</body>
</html>
