@extends('layouts.master')
@section('title', __('index.monthly_payroll_yearly_title'))
@section('action', __('index.monthly_payroll_yearly_title'))
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/monthly-payroll.css') }}?v={{ filemtime(public_path('assets/css/monthly-payroll.css')) }}">
@endsection
@section('main-content')
<section class="content monthly-payroll-page">
    @include('admin.section.flash_message')
    <div class="monthly-payroll-heading"><h4 class="mb-1">{{ __('index.monthly_payroll_yearly_title') }} · {{ $year }}</h4><p class="text-muted mb-0">{{ __('index.monthly_payroll_yearly_help') }}</p></div>
    @include('admin.payroll.monthly.period-navigation')
    @include('admin.payroll.monthly.summary')
    <form method="get" action="{{ route('admin.monthly-payroll.index') }}" class="monthly-payroll-year-filters">
        <input type="hidden" name="view" value="yearly">
        <details class="monthly-payroll-filters">
            <summary class="monthly-payroll-filter-heading"><span class="monthly-payroll-filter-title"><i data-feather="sliders" aria-hidden="true"></i>{{ __('index.filters') }}</span><i data-feather="chevron-down" aria-hidden="true"></i></summary>
            <div class="monthly-payroll-filter-content">
                <div class="monthly-payroll-filter-grid">
                    <div class="monthly-payroll-field"><label for="yearlyPayrollYear">{{ __('index.year') }}</label><input id="yearlyPayrollYear" class="form-control" type="number" name="year" value="{{ $year }}" min="2000" max="2100" required></div>
                    <div class="monthly-payroll-field"><label for="yearlyPayrollBranch">{{ __('index.branch') }}</label><select id="yearlyPayrollBranch" class="form-select" name="branch_id"><option value="">{{ __('index.all_branches') }}</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string) $filters['branch_id'] === (string) $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
                    <div class="monthly-payroll-field"><label for="yearlyPayrollDepartment">{{ __('index.department') }}</label><select id="yearlyPayrollDepartment" class="form-select" name="department_id"><option value="">{{ __('index.all_departments') }}</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) $filters['department_id'] === (string) $department->id)>{{ $department->dept_name }}</option>@endforeach</select></div>
                </div>
                <div class="monthly-payroll-filter-actions"><button class="btn btn-primary" type="submit">{{ __('index.apply') }}</button></div>
            </div>
        </details>
    </form>
    <div class="card"><div class="card-body">
        <div class="table-responsive"><table class="table align-middle monthly-payroll-year-table">
            <thead><tr><th>{{ __('index.month') }}</th><th>{{ __('index.status') }}</th><th>{{ __('index.monthly_payroll_records') }}</th><th>{{ __('index.salary_table_gross') }}</th><th>{{ __('index.monthly_payroll_sales_commission') }}</th><th title="{{ __('index.monthly_payroll_loan_help') }}">ខ្ចីលុយ</th><th>{{ __('index.net_salary') }}</th><th>{{ __('index.action') }}</th></tr></thead>
            <tbody>
                @foreach($months as $entry)
                    <tr data-payroll-month-row="{{ $entry['month'] }}">
                        <th>{{ $entry['month'] }}</th>
                        <td><span class="badge {{ $entry['saved']->isEmpty() ? 'bg-light text-secondary border' : 'bg-success' }}">{{ __($entry['saved']->isEmpty() ? 'index.monthly_payroll_not_saved' : 'index.monthly_payroll_saved_record') }}</span>@if($entry['saved']->isNotEmpty())<small class="d-block text-muted mt-1">{{ $entry['saved']->pluck('branch_name')->join(' · ') }}</small>@endif</td>
                        <td>{{ $entry['summary']['records'] }}</td>
                        @foreach(['gross','commission','advance_salary','net'] as $key)<td>{{ $currency.number_format((float) $entry['summary'][$key], 2) }}</td>@endforeach
                        <td><div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.monthly-payroll.index', ['month'=>$entry['month'], 'branch_id'=>$filters['branch_id'], 'department_id'=>$filters['department_id']]) }}">{{ __('index.view') }}</a>
                            @can('generate_payroll')
                                @if(!$filters['branch_id'] || $entry['saved']->isEmpty())<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#monthlyPayrollGenerateModal" data-payroll-month="{{ $entry['month'] }}">{{ __('index.monthly_payroll_generate_save') }}</button>@endif
                            @endcan
                        </div></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot><tr><th colspan="2">{{ __('index.total') }}</th><td>{{ $summary['records'] }}</td>@foreach(['gross','commission','advance_salary','net'] as $key)<td class="fw-semibold">{{ $currency.number_format((float) $summary[$key], 2) }}</td>@endforeach<td></td></tr></tfoot>
        </table></div>
    </div></div>
    @include('admin.payroll.monthly.generate-modal')
</section>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/monthly-payroll-generate.js') }}?v={{ filemtime(public_path('assets/js/monthly-payroll-generate.js')) }}"></script>
@endsection
