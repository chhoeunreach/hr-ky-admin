@extends('admin.payroll.employeeSalary.common.form-layout')
@section('title', __('index.salary_increment'))
@section('action', __('index.increment'))
@section('button')
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.employee-salaries.index', ['employee_id' => $employeeDetail->id]) }}">{{ __('index.back') }}</a>
@endsection
@section('main-content')
<section class="content">
    @include('admin.section.flash_message')
    @php
        $periods = $employeeSalary->payment_type === 'weekly' ? 52 : 12;
        $currency = \App\Helpers\AppHelper::getCompanyPaymentCurrencySymbol();
    @endphp
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between gap-2 align-items-center">
            <div><h5 class="mb-1">{{ $employeeDetail->name }} · {{ __('index.salary_increment') }}</h5>
                <p class="text-muted small mb-0">{{ __('index.salary_manage_increase_help') }}</p>
            </div>
            @can('employee.profile.view')
                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.employees.profile.show', ['employee' => $employeeDetail->id, 'tab' => 'salary']) }}">{{ __('index.employee_profile') }}</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.employee-salaries.updated-salary-store') }}" data-payroll-increase
                  data-annual="{{ $employeeSalary->annual_salary }}" data-basic-type="{{ $employeeSalary->basic_salary_type }}"
                  data-basic-value="{{ $employeeSalary->basic_salary_value }}" data-periods="{{ $periods }}"
                  data-earnings='@json(app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->earningStructure($employeeSalary))'
                  data-currency="{{ $currency }}" data-error="{{ __('index.salary_manage_invalid_amount') }}">
                @csrf
                    @if(request('salary_modal') === '1')
                        <input type="hidden" name="salary_modal" value="1">
                    @endif
                @if(request('employee_modal') === '1')
                    <input type="hidden" name="employee_modal" value="1">
                @endif
                <input type="hidden" name="employee_id" value="{{ $employeeDetail->id }}">
                <input type="hidden" name="current_salary" value="{{ $employeeSalary->annual_salary }}">
                <fieldset class="mb-4">
                    <legend class="h6 border-bottom pb-2">1. {{ __('index.salary_manage_choose') }}</legend>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="increase_type">{{ __('index.calculation_type') }}</label>
                            <select class="form-select" name="increase_type" id="increase_type">
                                <option value="amount" @selected(old('increase_type', 'amount') === 'amount')>{{ __('index.increase_amount') }}</option>
                                <option value="percent" @selected(old('increase_type') === 'percent')>{{ __('index.increase_percentage') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4" data-amount-options>
                            <label class="form-label" for="increase_period">{{ __('index.payment_type') }}</label>
                            <select class="form-select" name="increase_period" id="increase_period">
                                <option value="period" @selected(old('increase_period', 'period') === 'period')>{{ __('index.'.$employeeSalary->payment_type) }}</option>
                                <option value="annual" @selected(old('increase_period') === 'annual')>{{ __('index.annual_salary') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4" data-amount-options>
                            <label class="form-label" for="increment_amount">{{ __('index.increase_amount') }} ({{ $currency }})</label>
                            <input type="number" min="0.01" step="0.01" id="increment_amount" name="increment_amount" class="form-control" value="{{ old('increment_amount') }}">
                        </div>
                        <div class="col-md-4" data-percent-options>
                            <label class="form-label" for="increment_percent">{{ __('index.increase_percentage') }} (%)</label>
                            <input type="number" min="0.01" max="10000" step="0.01" id="increment_percent" name="increment_percent" class="form-control" value="{{ old('increment_percent') }}">
                        </div>
                    </div>
                </fieldset>
                <fieldset class="mb-4">
                    <legend class="h6 border-bottom pb-2">2. {{ __('index.salary_manage_preview') }}</legend>
                    <p class="small text-muted">{{ __('index.salary_manage_basic_help') }}</p>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>{{ __('index.salary_component') }}</th><th class="text-end">{{ __('index.asset_activity_before') }}</th><th class="text-end">{{ __('index.asset_activity_after') }}</th></tr></thead>
                            <tbody>
                                @foreach(['gross' => 'total', 'basic' => 'basic_salary', 'allowance' => 'fixed_allowance', 'earnings' => 'salary_manage_earnings', 'annual' => 'annual_salary'] as $key => $label)
                                    <tr><th>{{ __('index.'.$label) }} @if($key !== 'annual')<small class="text-muted">({{ __('index.'.$employeeSalary->payment_type) }})</small>@endif</th>
                                        <td class="text-end" data-salary-before="{{ $key }}"></td><td class="text-end fw-semibold" data-salary-after="{{ $key }}"></td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </fieldset>
                <fieldset class="mb-4">
                    <legend class="h6 border-bottom pb-2">3. {{ __('index.salary_manage_confirm') }}</legend>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="date_from">{{ __('index.effective_date') }} *</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" required max="{{ now()->toDateString() }}" value="{{ old('date_from', now()->toDateString()) }}">
                            <p class="form-text">{{ __('index.salary_manage_date_help') }}</p>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="salary_increase_reason">{{ __('index.reason') }} *</label>
                            <textarea class="form-control" id="salary_increase_reason" name="remark" required maxlength="2000" rows="3">{{ old('remark') }}</textarea>
                        </div>
                    </div>
                </fieldset>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('index.salary_manage_apply') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ request('salary_modal') === '1' ? route('admin.employee-salaries.index') : (request('employee_modal') === '1' ? route('admin.employees.profile.show', ['employee' => $employeeDetail->id, 'tab' => 'salary', 'employee_modal' => '1']) : route('admin.employee-salaries.index', ['employee_id' => $employeeDetail->id])) }}">{{ __('index.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
@section('scripts')
    <script defer src="{{ asset('assets/js/employee-salary-increase.js') }}?v={{ filemtime(public_path('assets/js/employee-salary-increase.js')) }}"></script>
@endsection
