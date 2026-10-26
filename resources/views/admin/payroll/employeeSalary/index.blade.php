@extends('layouts.master')

@section('title',__('index.employee_salary'))

@section('action',__('index.list'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-salary-table.css') }}?v={{ filemtime(public_path('assets/css/employee-salary-table.css')) }}">
@endsection

@section('main-content')

    <section class="content employee-salary-page">

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
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.employee-salaries.index') }}">{{ __('index.salary_manage_all') }}</a>
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
                            <a class="btn btn-outline-secondary" href="{{ route('admin.employee-salaries.index', request()->only('employee_id')) }}">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">{{ __('index.employee_salaries') }}</h6>
                @can('create_salary')
                    <a href="{{ route('admin.employee-salaries.import-csv.show') }}" class="btn btn-primary btn-sm">
                        <i class="link-icon" data-feather="upload"></i> {{ __('index.csv_import') }}
                    </a>
                @endcan
            </div>
            <div class="card-body">
                @include('admin.payroll.employeeSalary.common.toolkit')
                @php
                    $rowOffset = method_exists($employeeLists, 'firstItem') ? ($employeeLists->firstItem() ?? 1) : 1;
                @endphp
                <div class="table-responsive salary-table-scroll">
                    <table id="employeeSalaryTable" class="table align-middle salary-professional-table">
                        <thead>
                        <tr>
                            @foreach($salaryColumns as $column => $label)
                                <th data-salary-column="{{ $column }}" data-default-visible="{{ in_array($column, $defaultSalaryColumns, true) ? '1' : '0' }}"
                                    @if(!in_array($column, $defaultSalaryColumns, true)) hidden @endif
                                    @class(['text-end' => in_array($column, ['salary', 'basic_salary', 'allowance'], true)])>{{ $label }}</th>
                            @endforeach
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($employeeLists as $key => $value)
                                @php $savedSalary = $employeeSalaries->get($value->employee_id); @endphp
                                <tr data-salary-row>
                                    <td hidden>{{ $rowOffset + $key }}</td>
                                    <td>
                                        @can('employee.profile.view')
                                            <a href="{{ route('admin.employees.profile.show', ['employee' => $value->employee_id, 'tab' => 'salary']) }}">{{ $value->employee_name }}</a>
                                        @else
                                            {{ $value->employee_name }}
                                        @endcan
                                    </td>
                                    <td class="text-end">
                                        @if($savedSalary)
                                            <strong>{{ \App\Helpers\AppHelper::getCompanyPaymentCurrencySymbol() }}{{ number_format((float) $savedSalary->annual_salary / ($savedSalary->payment_type === 'weekly' ? 52 : 12), 2) }}</strong>

                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end salary-amount">{{ $savedSalary ? number_format((float) ($savedSalary->payment_type === 'weekly' ? $savedSalary->weekly_basic_salary : $savedSalary->monthly_basic_salary), 2) : '—' }}</td>
                                    <td class="text-end salary-amount">{{ $savedSalary ? number_format((float) ($savedSalary->payment_type === 'weekly' ? $savedSalary->weekly_fixed_allowance : $savedSalary->monthly_fixed_allowance), 2) : '—' }}</td>
                                    <td>{{ $savedSalary ? __('index.'.$savedSalary->payment_type) : '—' }}</td>
                                    <td><span class="badge rounded-pill {{ $savedSalary ? 'bg-success' : 'bg-light text-secondary border' }}">{{ __($savedSalary ? 'index.salary_setup_ready' : 'index.salary_setup_missing') }}</span></td>
                                    <td class="text-center" hidden>{{ in_array($value->marital_status, ['single', 'married'], true) ? __('index.marital_status_' . $value->marital_status) : ucfirst($value->marital_status ?? '') }}</td>
{{--                        <td>{{ number_format($value->salary) }}</td>--}}
                            <td class="text-center" hidden>
                                @if($value->account_id)
                                <select class="form-select form-select-sm salary-cycle-select"
                                        name="salary_cycle"
                                        data-employee="{{$value->employee_id}}"
                                        data-current="{{$value->salary_cycle}}"
                                        aria-label="{{ __('index.salary_cycle') }}"
                                        @cannot('change_salary_cycle') disabled @endcannot
                                >
                                    @if(!$value->salary_cycle)
                                        <option value="" selected disabled>{{ __('index.select_salary_cycle') }}</option>
                                    @endif
                                    @foreach(\App\Models\EmployeeAccount::SALARY_CYCLE as $salaryCycle)
                                            <option value="{{$salaryCycle}}" {{$value->salary_cycle == $salaryCycle ? 'selected' : '' }}>
                                                {{ __('index.' . $salaryCycle) }}
                                            </option>
                                    @endforeach
                                </select>
                                @can('change_salary_cycle')
                                    <form method="POST" class="salary-cycle-form d-none" data-url-template="{{ route('admin.employee-salaries.update-salary-cycle', [$value->employee_id, '__CYCLE__']) }}">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @endcan
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="text-center" hidden>{{ $value->salary_group_name ?: '—' }}</td>
                            <td class="text-center">
                                <button class="btn btn-outline-secondary btn-sm" type="button" id="payslipDropdown{{ $value->employee_id }}"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    title="{{ __('index.action') }}"
                                    aria-label="{{ __('index.action') }}"
                                ><i data-feather="more-vertical" style="width:16px;height:16px;"></i></button>

                                <div class="dropdown-menu p-0" aria-labelledby="payslipDropdown{{ $value->employee_id }}">
                                    <ul class="list-unstyled mb-0">
                                        @php
                                            $employeeSalaryStatus = $savedSalary ? 1 : 0
                                        @endphp

                                        @if($employeeSalaryStatus == 0)
                                            @can('add_salary')
                                                <li>
                                                    <a class="dropdown-item py-2" title="{{ __('index.add_salary') }}"
                                                        href="{{ route('admin.employee-salaries.add', $value->employee_id) }}">
                                                        @lang('index.add_salary')
                                                    </a>
                                                </li>
                                            @endcan
                                        @else
                                            @can('edit_salary')
                                                <li>
                                                    <a class="dropdown-item py-2" title="{{ __('index.edit_salary') }}"
                                                        href="{{ route('admin.employee-salaries.edit-salary', $value->employee_id) }}">
                                                        {{ __('index.edit_salary') }}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('salary_increment')
                                                <li>
                                                    <a class="dropdown-item py-2" title="{{ __('index.increase_salary') }}"
                                                        href="{{route('admin.employee-salaries.increase-salary',$value->employee_id)}}">
                                                        {{ __('index.increase_salary') }}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('show_salary_history')
                                                <li>
                                                    <a href="{{route('admin.employee-salaries.salary-revise-history.show',$value->employee_id)}}"
                                                        class="dropdown-item py-2 viewSalaryReviseHistory"
                                                        title="{{ __('index.salary_review_history') }}">
                                                        {{ __('index.salary_review_history') }}
                                                    </a>
                                                </li>
                                            @endcan
                                                @can('delete_salary')
                                                    <li>
                                                        <form method="POST" action="{{ route('admin.employee-salaries.delete-salary',$value->employee_id) }}" class="deleteEmployeeSalaryForm">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item py-2 text-danger">{{ __('index.delete') }}</button>
                                                        </form>
                                                    </li>
                                                @endcan
                                        @endif

                                    </ul>
                                </div>
                            </td>
                            @empty
                                <tr>
                                    <td colspan="{{ count($defaultSalaryColumns) + 1 }}" data-salary-empty>
                                        <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
                <div class="salary-table-footer">
                    <span>{{ __('index.showing') }} {{ $employeeLists->count() ? $rowOffset : 0 }} {{ __('index.to') }} {{ $employeeLists->count() ? $rowOffset + $employeeLists->count() - 1 : 0 }} {{ __('index.of') }} {{ method_exists($employeeLists, 'total') ? $employeeLists->total() : $employeeLists->count() }} {{ __('index.entries') }}</span>
                    @if(method_exists($employeeLists, 'links')){{ $employeeLists->links() }}@endif
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    @include('admin.payroll.employeeSalary.common.scripts')
    <script src="{{ asset('assets/js/employee-salary-table.js') }}?v={{ filemtime(public_path('assets/js/employee-salary-table.js')) }}"></script>
@endsection

