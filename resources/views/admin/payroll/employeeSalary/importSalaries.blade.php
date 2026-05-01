@extends('layouts.master')

@section('title', __('index.employee_salary'))

@section('action', __('index.csv_import'))

@section('button')
    <div class="float-end">
        <a href="{{ route('admin.employee-salaries.index') }}">
            <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back')}}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')
        @include('admin.payroll.employeeSalary.common.breadcrumb')

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="row">
                        <div class="card-body col-md-6">
                            <h4 class="mb-4">@lang('index.employee_salary_csv')</h4>
                            <form class="forms-sample" action="{{ route('admin.employee-salaries.import-csv.store') }}" enctype="multipart/form-data" method="POST">
                                @csrf

                                <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.txt">
                                <br>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">@lang('index.import')</button>
                                    <a href="{{ asset('templates/employee_salary_import_template.csv') }}" class="btn btn-secondary" download>
                                        <i class="link-icon" data-feather="download"></i> @lang('index.download')
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="card-body mt-2 col-md-6">
                            <h4 class="mb-4">@lang('index.salary_csv_example')</h4>
                            <div class="col-md-12">
                                <p>The import file should contain these headers:</p>
                                <ul class="mb-3">
                                    <li>username or employee_id</li>
                                    <li>payroll_type</li>
                                    <li>payment_type</li>
                                    <li>annual_salary</li>
                                    <li>basic_salary_type</li>
                                    <li>basic_salary_value</li>
                                    <li>monthly_hours</li>
                                    <li>monthly_basic_salary</li>
                                    <li>annual_basic_salary</li>
                                    <li>monthly_fixed_allowance</li>
                                    <li>annual_fixed_allowance</li>
                                    <li>salary_group_id</li>
                                    <li>hour_rate</li>
                                    <li>weekly_hours</li>
                                    <li>weekly_basic_salary</li>
                                    <li>weekly_fixed_allowance</li>
                                </ul>
                                <p class="mb-0">Existing employee salary rows will be updated if the employee already has salary data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection
