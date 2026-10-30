<div class="monthly-payroll-period-navigation">
    <nav class="monthly-payroll-period-tabs" aria-label="{{ __('index.monthly_payroll_period') }}">
        <a class="{{ request('view') !== 'yearly' ? 'active' : '' }}" @if(request('view') !== 'yearly') aria-current="page" @endif href="{{ route('admin.monthly-payroll.index', ['month' => $filters['month'], 'branch_id' => $filters['branch_id'], 'department_id' => $filters['department_id']]) }}">{{ __('index.monthly') }}</a>
        <a class="{{ request('view') === 'yearly' ? 'active' : '' }}" @if(request('view') === 'yearly') aria-current="page" @endif href="{{ route('admin.monthly-payroll.index', ['view' => 'yearly', 'year' => $year ?? substr($filters['month'], 0, 4), 'branch_id' => $filters['branch_id'], 'department_id' => $filters['department_id']]) }}">{{ __('index.monthly_payroll_yearly') }}</a>
    </nav>
    @can('generate_payroll')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#monthlyPayrollGenerateModal" data-payroll-month="{{ $filters['month'] }}"><i data-feather="plus-circle" aria-hidden="true"></i>{{ __('index.monthly_payroll_generate_preview') }}</button>
    @endcan
</div>
