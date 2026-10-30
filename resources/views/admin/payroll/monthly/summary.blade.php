<div id="monthlyPayrollSummary" class="monthly-payroll-summary" aria-busy="false">
    @foreach(['records'=>'monthly_payroll_records','gross'=>'salary_table_gross','commission'=>'monthly_payroll_sales_commission','net'=>'net_salary'] as $key=>$label)
        <div class="monthly-payroll-summary-card">
            <small class="text-muted">{{ __('index.'.$label) }}</small>
            <strong>{{ $key === 'records' ? $summary[$key] : $currency.number_format((float) $summary[$key], 2) }}</strong>
        </div>
    @endforeach
</div>
