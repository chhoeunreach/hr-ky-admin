@php
    $printColumns = $columns;
    $totalWidth = array_sum(array_column($printColumns, 'width'));
    $money = fn ($amount) => $amount === null || (float) $amount === 0.0 ? '' : $currency.number_format((float) $amount, 2);
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
                    $values = $service->rowValues($payslip, $commissions[$payslip->employee_id] ?? [], $index + 1);
                @endphp
                <section class="payroll-employee-block" aria-label="{{ $employee?->name }}">
                    <table class="payroll-sample-table">
                        @include('admin.payroll.monthly.print-headings')
                        <tbody><tr>
                            @foreach($printColumns as $key => $column)
                                <td class="{{ $column['class'] }}">
                                    @if($key === 'photo')
                                        <img src="{{ $values['photo'] ?: asset('assets/images/img.png') }}" alt="" width="44" height="57" onerror="this.onerror=null;this.src='{{ asset('assets/images/img.png') }}';">
                                    @else
                                        {{ $column['format'] === 'money' ? ($key === 'net_salary' && $values[$key] !== null ? $currency.number_format((float) $values[$key], 2) : $money($values[$key])) : ($values[$key] ?? '') }}
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
                    @foreach(['records'=>'monthly_payroll_records','gross'=>'salary_table_gross','bonus'=>'bonus','commission'=>'monthly_payroll_sales_commission','advance_salary'=>'monthly_payroll_loan_total','net'=>'net_salary'] as $key=>$label)
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
