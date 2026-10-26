@php
    $printColumns = [
        'number' => ['label' => '#', 'width' => .5, 'class' => 'number'],
        'employee_code' => ['label' => __('index.employee_code'), 'width' => 1, 'class' => ''],
        'photo' => ['label' => __('index.monthly_payroll_photo'), 'width' => .8, 'class' => 'employee-photo'],
        'employee' => ['label' => __('index.employee_name'), 'width' => 1.7, 'class' => 'employee-name'],
        'tenure' => ['label' => __('index.working_life'), 'width' => 1.3, 'class' => ''],
        'gross_salary' => ['label' => __('index.salary_table_gross'), 'width' => 1.1, 'class' => 'money'],
        'present_days' => ['label' => __('index.present_days'), 'width' => .85, 'class' => 'day-count'],
        'absent_days' => ['label' => __('index.absent_days'), 'width' => .85, 'class' => 'day-count unpaid-days'],
        'paid_leave' => ['label' => __('index.paid_leave'), 'width' => .85, 'class' => 'day-count paid-days'],
        'unpaid_leave' => ['label' => __('index.unpaid_leave'), 'width' => .85, 'class' => 'day-count unpaid-days'],
        'advance_salary' => ['label' => __('index.monthly_payroll_advance_salary'), 'width' => 1, 'class' => 'money deduction'],
        'absent_deduction' => ['label' => __('index.monthly_payroll_absent_deduction'), 'width' => 1, 'class' => 'money deduction'],
    ];
    foreach ($commissionTypes as $type) {
        $printColumns['commission:'.$type] = ['label' => __('index.commission').' · '.$type, 'width' => .95, 'class' => 'money earning'];
    }
    foreach ([
        'overtime' => 'earning', 'tada' => 'earning', 'bonus' => 'earning', 'undertime' => 'deduction',
        'tds' => 'deduction', 'ssf_deduction' => 'deduction', 'pf_deduction' => 'deduction',
    ] as $key => $class) {
        $printColumns[$key] = ['label' => __($key === 'bonus' ? 'index.bonus' : 'index.monthly_payroll_'.$key), 'width' => .95, 'class' => 'money '.$class];
    }
    $printColumns += [
        'other_earnings' => ['label' => __('index.monthly_payroll_other_earnings'), 'width' => 1, 'class' => 'money earning'],
        'other_deductions' => ['label' => __('index.monthly_payroll_other_deductions'), 'width' => 1, 'class' => 'money deduction'],
        'net_salary' => ['label' => __('index.net_salary'), 'width' => 1.1, 'class' => 'money net-pay'],
        'salary_note' => ['label' => __('index.monthly_payroll_print_note'), 'width' => 2.6, 'class' => 'salary-note'],
    ];
    $totalWidth = array_sum(array_column($printColumns, 'width'));
    $money = fn ($amount) => (float) $amount === 0.0 ? '' : $currency.number_format((float) $amount, 2);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('index.monthly_payroll') }} · {{ $month->format('Y-m') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/monthly-payroll-print.css') }}?v={{ filemtime(public_path('assets/css/monthly-payroll-print.css')) }}">
    <style id="payrollPrintPageStyle">@page { size: {{ $paper }} {{ $orientation }}; margin: 6mm; }</style>
</head>
<body>
    <nav class="payroll-print-controls" aria-label="{{ __('index.monthly_payroll_print_options') }}">
        <label for="payrollPrintPaper">{{ __('index.monthly_payroll_paper') }}
            <select id="payrollPrintPaper">@foreach(['A3','A4'] as $size)<option value="{{ $size }}" @selected($paper === $size)>{{ $size }}</option>@endforeach</select>
        </label>
        <label for="payrollPrintOrientation">{{ __('index.monthly_payroll_orientation') }}
            <select id="payrollPrintOrientation">@foreach(['landscape','portrait'] as $direction)<option value="{{ $direction }}" @selected($orientation === $direction)>{{ __('index.'.$direction) }}</option>@endforeach</select>
        </label>
        <button type="button" data-print-payroll>{{ $format === 'pdf' ? __('index.export_pdf') : __('index.print') }}</button>
        <p class="payroll-print-help">{{ __('index.monthly_payroll_print_help') }}</p>
    </nav>
    <div class="payroll-print-scroll">
        <main class="payroll-print-sheet" data-payroll-print-sheet data-auto-print="1">
            <header class="payroll-report-heading">
                <div><h1>{{ __('index.monthly_payroll') }}</h1><p>{{ __('index.monthly_payroll_source') }}</p></div>
                <div class="payroll-report-period"><strong>{{ $month->format('Y-m') }}</strong><p>{{ __('index.monthly_payroll_records') }}: {{ $summary['records'] }} · {{ $currency }}</p></div>
            </header>
            <p class="payroll-source-note">{{ __('index.monthly_payroll_commission_help') }}</p>
            @forelse($payslips as $index => $payslip)
                @php
                    $employee = $payslip->employee;
                    $end = \Carbon\Carbon::parse($payslip->salary_to);
                    $joined = $employee?->joining_date ? \Carbon\Carbon::parse($employee->joining_date) : null;
                    $tenure = $joined && $joined->lte($end) ? $joined->diff($end) : null;
                    $values = [
                        'number' => $index+1, 'employee_code' => $employee?->employee_code ?: '—',
                        'employee' => $employee?->name ?: '—',
                        'tenure' => $tenure ? __('index.monthly_payroll_tenure', ['years'=>$tenure->y, 'months'=>$tenure->m, 'days'=>$tenure->d]) : '—',
                        'other_earnings' => $money($service->componentTotal($payslip, 'earning')),
                        'other_deductions' => $money($service->componentTotal($payslip, 'deductions')),
                        'salary_note' => $payslip->remark ?: __('index.monthly_payroll_print_net_note', ['amount'=>$currency.number_format((float) $payslip->net_salary, 2)]),
                    ];
                    foreach (['present_days','absent_days','paid_leave','unpaid_leave'] as $key) $values[$key] = (float) $payslip->$key ?: '';
                    foreach (['gross_salary','advance_salary','absent_deduction','overtime','tada','bonus','undertime','tds','ssf_deduction','pf_deduction'] as $key) $values[$key] = $money($payslip->$key);
                    $values['net_salary'] = $currency.number_format((float) $payslip->net_salary, 2);
                    foreach ($commissionTypes as $type) $values['commission:'.$type] = $money($commissions[$payslip->employee_id][$type] ?? 0);
                @endphp
                <section class="payroll-employee-block" aria-label="{{ $employee?->name }}">
                    <table class="payroll-sample-table">
                        @include('admin.payroll.monthly.print-headings')
                        <tbody><tr>
                            @foreach($printColumns as $key => $column)
                                <td class="{{ $column['class'] }}">
                                    @if($key === 'photo')
                                        <img src="{{ $employee?->avatar_url ?: asset('assets/images/img.png') }}" alt="" width="44" height="57" onerror="this.onerror=null;this.src='{{ asset('assets/images/img.png') }}';">
                                    @else
                                        {{ $values[$key] ?? '' }}
                                        @if($key === 'employee' && $employee?->username)<small>{{ $employee->username }}</small>@endif
                                    @endif
                                </td>
                            @endforeach
                        </tr></tbody>
                    </table>
                </section>
            @empty
                <p class="payroll-print-empty">{{ __('index.monthly_payroll_empty') }}</p>
            @endforelse
            <table class="payroll-print-totals">
                <tbody><tr>
                    @foreach(['records'=>'monthly_payroll_records','gross'=>'salary_table_gross','bonus'=>'bonus','commission'=>'monthly_payroll_sales_commission','net'=>'net_salary'] as $key=>$label)
                        <th>{{ __('index.'.$label) }}</th><td>{{ $key === 'records' ? $summary[$key] : $currency.number_format((float) $summary[$key], 2) }}</td>
                    @endforeach
                </tr></tbody>
            </table>
            <div class="payroll-print-signatures">
                @foreach(['monthly_payroll_prepared','monthly_payroll_checked','approved_by'] as $label)<div>{{ __('index.'.$label) }}</div>@endforeach
            </div>
        </main>
    </div>
    <script src="{{ asset('assets/js/monthly-payroll-print.js') }}?v={{ filemtime(public_path('assets/js/monthly-payroll-print.js')) }}"></script>
</body>
</html>
