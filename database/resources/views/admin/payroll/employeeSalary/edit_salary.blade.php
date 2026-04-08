@extends('layouts.master')

@section('title',__('index.employee_salary'))

@section('action',__('index.edit_salary'))

@section('button')
    <div class="float-md-end">
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
            <div class="card-body">
                <h4 class="mb-4"> {{ __('index.payroll_edit') }}- {{ $employee->name }}</h4>
                <form class="forms-sample" action="{{ route('admin.employee-salaries.update-salary',$employee->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" readonly name="employee_id" value ="{{$employee->id}}">
                    <div class="payroll-fil border-bottom mb-4 pb-4" x-data="createEmployeeSalary('{{$percentType}}', {{ json_encode($salaryComponents) }}, {{ json_encode($employeeSalary) }})">
                        <div class="d-flex align-items-center mb-3">
                            <div class=" p-2">
                                <label>{{ __('index.annual') }}</label>
                            </div>
                            <div class="">
                                <label class="switch">
                                    <input class="toggleStatus" type="checkbox" x-model="salary_base">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class=" p-2">
                                <label>{{ __('index.hourly') }}</label>
                            </div>
                        </div>
                        <!-- Conditional rendering based on the toggle switch -->
                        <div class="row">
                            <div class="col-lg-4 col-md-4 mb-4"  x-show="salary_base">
                                <label for="hourRate" class="form-label">{{ __('index.hourly_rate') }}</label>

                                <input type="number" min="0" step="0.01" x-model="hour_rate" class="form-control" @input="calculateAnnualSalary()" oninput="validity.valid||(value='');" placeholder="Enter Hourly Rate" name="hour_rate" id="hourRate">
                            </div>
                            <div class="col-lg-4 col-md-4 mb-4"  x-show="salary_base">
                                <label for="weeklyHour" class="form-label">{{ __('index.working_hours_in_week') }}</label>

                                <input type="number" min="0" step="0.1" x-model="weekly_hour" class="form-control" @input="calculateAnnualSalary()" oninput="validity.valid||(value='');" placeholder="Enter Hourly Rate" name="weekly_hour" id="weeklyHour">
                            </div>
                            <div class="col-lg-4 col-md-4 mb-4">
                                <label for="annualSalary" class="form-label">{{ __('index.annual_salary') }}</label>
                                <input type="number" min="0" step="0.1" x-model="annual_salary" class="form-control" @input="calculateSalary()" oninput="validity.valid||(value='');" placeholder="Enter Annual Salary"  name="annual_salary" id="annualSalary" x-bind:readonly="salary_base ? true : false">

                            </div>

                        </div>
                        <input type="hidden" readonly name="salary_group_id" x-model="salary_group_id">
                        <div class="row table-responsive">
                            <table class="table border-end">
                                <thead>
                                <tr>
                                    <th>{{ __('index.salary_component') }}</th>
                                    <th>{{ __('index.calculation_type') }}</th>
                                    <th>{{ __('index.monthly_amount') }} ({{ $currency }}) </th>
                                    <th>{{ __('index.annual_amount') }} ({{ $currency }}) </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="4"> <h4>{{ __('index.earnings') }}</h4></td>
                                </tr>
                                <tr>
                                    <td>{{ __('index.basic_salary') }} </td>
                                    <td>
                                        <div style="display: flex;">
                                            <input type="number" min="0" step="0.1" class="form-control me-2" @input="calculateSalary()" x-model="basic_salary_value" name="basic_salary_value" id="basicSalaryPercent">

                                            <select class="form-control" x-model="basic_salary_type" @change="calculateSalary()" name="basic_salary_type" >
                                                <option value="{{ $percentType }}">% of Salary</option>
                                                <option value="{{ $fixedType }}">{{ ucfirst($fixedType) }}</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" readonly x-model="monthly_basic_salary" class="form-control" name="monthly_basic_salary" id="monthlyBasicSalary">
                                    </td>
                                    <td>
                                        <input type="number" readonly class="form-control" x-model="annual_basic_salary" name="annual_basic_salary" id="annualBasicSalary">
                                    </td>

                                </tr>
                                <template x-for="(income, index) in incomes" :key="index">
                                    <tr>
                                        <td x-text="income.name"></td>
                                        <td>
                                            <div style="display: flex;">
                                                <input style="text-align:center; border:none; background: inherit;" type="text" readonly min="0" step="0.1" class="form-control" x-model="income.value_type" name="value_type">
                                                <input style="text-align:center; border:none; background: inherit;" type="number" readonly min="0" step="0.1" class="form-control" x-show="income.value_type !== 'fixed' " x-model="income.component_value_monthly" name="component_value_monthly">

                                            </div>
                                        </td>
                                        <td>
                                            <input x-bind:style="income.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="income.value_type !== 'adjustable'" x-model="income.monthly" class="form-control" :name="income.name+'_month_value'">
                                        </td>
                                        <td>
                                            <input x-bind:style="income.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="income.value_type !== 'adjustable'" class="form-control" x-model="income.annual" :name="income.name+'_annual_value'">
                                        </td>

                                    </tr>
                                </template>

                                <tr>
                                    <td>{{ __('index.fixed_allowance') }}</td>
                                    <td>{{ __('index.fixed_allowance') }}</td>
                                    <td>
                                        <input style="border:none; background: inherit;" type="number" class="form-control" readonly x-model="monthly_fixed_allowance" name="monthly_fixed_allowance" id="monthlyFixedAllowance">
                                    </td>
                                    <td>
                                        <input style="border:none; background: inherit;" type="number" class="form-control" readonly x-model="annual_fixed_allowance" name="annual_fixed_allowance" id="annualFixedAllowance">
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th>{{ $currency }} <span x-text="monthly_total"></span></th>
                                    <th>{{ $currency }} <span x-text="annual_total"></span></th>
                                </tr>
                                <tr>
                                    <td colspan="4"> <h4>{{ __('index.deductions') }} </h4></td>

                                </tr>
                                <template x-for="(deduction, index) in deductions" :key="index">
                                    <tr>
                                        <td x-text="deduction.name"></td>
                                        <td>
                                            <div style="display: flex;">
                                                <input style="text-align:center; border:none; background: inherit;" type="text" readonly min="0" step="0.1" class="form-control" x-model="deduction.value_type" name="value_type">
                                                <input style="text-align:center; border:none; background: inherit;" type="number" readonly min="0" step="0.1" class="form-control" x-show="deduction.value_type !== 'fixed' " x-model="deduction.component_value_monthly" name="component_value_monthly">

                                            </div>
                                        </td>
                                        <td>
                                            <input x-bind:style="deduction.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="deduction.value_type !== 'adjustable'" x-model="deduction.monthly" class="form-control" :name="deduction.name+'_month_value'">
                                        </td>
                                        <td>
                                            <input x-bind:style="deduction.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="deduction.value_type !== 'adjustable'" class="form-control" x-model="deduction.annual" :name="deduction.name+'_annual_value'">
                                        </td>

                                    </tr>
                                </template>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <td>{{ $currency }} <span x-text="total_monthly_deduction"></span></td>
                                    <td>{{ $currency }} <span x-text="total_annual_deduction"></span></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="2">Net Total</th>
                                    <th>{{ $currency }} <span x-text="net_monthly_salary"></span></th>
                                    <th>{{ $currency }} <span x-text="net_annual_salary"></span></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-12 mt-3 mb-3">
                        <button class="btn btn-primary submit-fn mt-2" type="submit">{{ __('index.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.payroll.employeeSalary.common.scripts')
    <script src="{{asset('assets/js/salary_calculation.js')}}"></script>

@endsection

