@extends('layouts.master')

@section('title',__('index.employee_salary'))

@section('action',__('index.list'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-salary-table.css') }}?v={{ filemtime(public_path('assets/css/employee-salary-table.css')) }}">
@endsection

@section('main-content')

    <section class="content employee-salary-page" data-salary-refresh-error="{{ __('index.salary_refresh_error') }}" data-salary-refresh-success="{{ __('index.salary_refresh_success') }}">

        @include('admin.section.flash_message')

        @include('admin.payroll.employeeSalary.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="link-icon me-2" data-feather="filter"></i>{{ __('index.employee_salary_filter') }}</h6>
            </div>
            @if(request()->filled('employee_id'))
                <div class="card-body pb-0 d-flex flex-wrap gap-2">
                    @can('employee.profile.view')
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.employees.profile.show', ['employee' => request()->integer('employee_id'), 'tab' => 'salary']) }}">{{ __('index.employee_profile') }} · {{ __('index.salary') }}</a>
                    @endcan
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.employee-salaries.index') }}" data-salary-reset>{{ __('index.salary_manage_all') }}</a>
                </div>
            @endif
            <form id="employeeSalaryFilters" class="forms-sample card-body" action="{{route('admin.employee-salaries.index')}}" method="get">
                @if(request()->filled('employee_id'))<input type="hidden" name="employee_id" value="{{ request()->integer('employee_id') }}">@endif
                <div class="row g-3 align-items-end">
                    @if(!isset(auth()->user()->branch_id))
                    <div class="col-lg-2 col-md-6">
                        <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
                        <select class="form-select" id="branch_id" name="branch_id" >
                            <option value="">{{ __('index.all') }}</option>
                            @if(isset($companyDetail))
                                @foreach($companyDetail->branches()->get() as $key => $branch)
                                    <option value="{{$branch->id}}"
                                        {{ (isset($filterParameters['branch_id']) && ($filterParameters['branch_id']) == $branch->id) ? 'selected': '' }}>
                                        {{ucfirst($branch->name)}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif
                    <div class="col-lg-2 col-md-6">
                        <label for="department_id" class="form-label">{{ __('index.department') }}</label>
                        <select class="form-select" id="department_id" name="department_id" >
                            <option value="">{{ __('index.all') }}</option>

                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="employee_name" class="form-label">{{ __('index.employee_name') }}</label>
                        <input type="text" maxlength="200" id="employee_name" placeholder="{{ __('index.employee_name') }}" name="employee_name" value="{{$filterParameters['employee_name']}}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="salaryPaymentType" class="form-label">{{ __('index.payment_type') }}</label>
                        <select class="form-select" id="salaryPaymentType" name="payment_type">
                            <option value="">{{ __('index.all') }}</option>
                            @foreach(['monthly', 'weekly'] as $type)
                                <option value="{{ $type }}" @selected($filterParameters['payment_type'] === $type)>{{ __('index.'.$type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="salarySetupStatus" class="form-label">{{ __('index.status') }}</label>
                        <select class="form-select" id="salarySetupStatus" name="salary_status">
                            <option value="">{{ __('index.all') }}</option>
                            @foreach(['configured' => 'salary_setup_ready', 'missing' => 'salary_setup_missing'] as $value => $label)
                                <option value="{{ $value }}" @selected($filterParameters['salary_status'] === $value)>{{ __('index.'.$label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary me-2">{{ __('index.filter') }}</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.employee-salaries.index', request()->only('employee_id')) }}" data-salary-reset>{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <div data-salary-feedback role="status" aria-live="polite"></div>
        @include('admin.payroll.employeeSalary.partials.salary-list')
    </section>

@endsection

@section('scripts')
    @include('admin.payroll.employeeSalary.common.scripts')
    <script src="{{ asset('assets/js/employee-salary-table.js') }}?v={{ filemtime(public_path('assets/js/employee-salary-table.js')) }}"></script>
    <script src="{{ asset('assets/js/employee-salary-refresh.js') }}?v={{ filemtime(public_path('assets/js/employee-salary-refresh.js')) }}"></script>
@endsection

