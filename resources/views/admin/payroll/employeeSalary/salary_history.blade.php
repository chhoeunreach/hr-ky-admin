@extends('admin.payroll.employeeSalary.common.form-layout')

@section('title',__('index.salary_review_history'))

@section('action',__('index.log'))

@section('button')
    <div class="float-end d-flex gap-2">
        @can('employee.profile.view')
            <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.employees.profile.show', ['employee' => $employeeDetail->id, 'tab' => 'salary']) }}">{{ __('index.employee_profile') }}</a>
        @endcan
        <a href="{{route('admin.employee-salaries.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.employeeSalary.common.breadcrumb')

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ucfirst($employeeDetail->name)}} {{ __('index.salary_increment_log') }}</h6>
            </div>
            <div class="card-body">

                @include('admin.payroll.employeeSalary.partials.history-content')
            </div>
        </div>

    </section>
@endsection
