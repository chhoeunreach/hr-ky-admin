@extends('layouts.master')

@section('title',__('index.employee_payroll'))

@section('action',__('index.payroll_edit'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.employee-salary.payroll')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection
@section('style')
    <style>
        #net_salary:focus {
            outline: none !important;
            border: none !important;
        }
        .no-outline {
            outline: none !important;
            border: none !important;
        }
    </style>

@endsection
@section('main-content')

   <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.employeeSalary.common.breadcrumb')

        <div class="card">
            <div class="card-body" >

                <h4 class="mb-4">
                    {{ __('index.edit_salary_slip') }}

                </h4>

                <form action="{{ route('admin.employee-salary.payroll-update',$payrollData['payslipData']->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div>
                        <div class="payroll-personal">
                            <div class="row align-items-center justify-content-between border-bottom mb-4 pb-4">
                                <h5>{{ __('index.payslip') }}
                                    @if( isset($payroll['salary_cycle']) && $payroll['salary_cycle'] == 'monthly')
                                    {{__('index.for_the_month_of')}} {{ \App\Helpers\AppHelper::getMonthYear($payrollData['payslipData']->salary_from) }}
                                    @else
                                    {{ __('index.from') }} {{ \App\Helpers\AttendanceHelper::payslipDate($payrollData['payslipData']->salary_from) }} to {{ \App\Helpers\AttendanceHelper::payslipDate($payrollData['payslipData']->salary_to) }}
                                    @endif
                                </h5>
                            </div>
                            <div class="row mb-4">
                                <table class="table p-2">
                                    <tr>
                                        <td>{{ __('index.employee_name') }}</td> <td>{{ $payrollData['payslipData']->employee_name }}</td> <td>{{ __('index.joining_date') }}</td> <td>{{ $payrollData['payslipData']->joining_date ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('index.employee_id') }}</td> <td>{{ $payrollData['payslipData']->employee_code ?? 'N/A' }}</td> <td>{{ __('index.salary_group') }}</td> <td>{{ $payrollData['payslipData']->salary_group_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('index.designation') }}</td><td>{{ $payrollData['payslipData']->designation }}</td> <td>{{ __('index.marital_status') }}</td><td>{{ $payrollData['payslipData']->marital_status }}</td>
                                    </tr>
                                </table>


                            </div>
                        </div>

                        <div class="payroll-fil border-bottom mb-4">

                            <div class="row">
                                <div class="col-lg-4 col-md-2 mb-4">
                                    <h5 class="mb-3">{{ __('index.status') }}</h5>
                                    @if($payrollData['payslipData']->status ==  $paidStatus)
                                            <input type="hidden" name="status" readonly value="{{ $payrollData['payslipData']->status }}">
                                        <span class="p-2 alert alert-primary">{{ ucfirst($payrollData['payslipData']->status) }}</span>

                                    @else
                                        <select name="status" class="form-control select2" id="payroll_status">
                                            @forelse(\App\Enum\PayslipStatusEnum::cases() as $case)
                                                <option @if($payrollData['payslipData']->status == $case->value) selected @endif  value="{{ $case->value }}"> {{ ucfirst($case->value) }} </option>
                                            @empty
                                            @endforelse
                                        </select>
                                    @endif

                                </div>


                                <div class="col-lg-4 col-md-5 md-4 paidPayslip @if($payrollData['payslipData']->status !=  $paidStatus) d-none @endif">
                                    <h5 class="mb-3">{{ __('index.payment_method') }}</h5>
                                    <select name="payment_method_id" class="form-control">
                                        <option selected disabled>{{ __('index.payment_method_placeholder') }}</option>
                                        @foreach($paymentMethods as $method)
                                            <option @if($payrollData['payslipData']->payment_method_id  ==  $method['id']) selected @endif value="{{ $method['id'] }}"> {{ $method['name'] }}</option>
                                        @endforeach

                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-5 mb-4 paidPayslip @if($payrollData['payslipData']->status !=  $paidStatus) d-none @endif">
                                    <h5 class="mb-3">{{ __('index.paid_on') }}</h5>
                                    <input type="date" class="form-control" name="paid_on" value="{{ isset($payrollData['payslipData']->paid_on) ? date('Y-m-d', strtotime($payrollData['payslipData']->paid_on)) : date('Y-m-d') }}">
                                </div>

                            </div>
                        </div>

                        <div class="payroll-earn-ded">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 mb-4">
                                    <h4 class="mb-2">{{ __('index.earning') }}</h4>
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr class="earning">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ __('index.basic_salary') }}</strong>
                                                <input type="number" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif class="form-control w-50" step="0.1" oninput="validity.valid||(value='');" name="monthly_basic_salary" id="monthly_basic_salary" value="{{ ($payrollData['payslipData']->salary_cycle == 'weekly') ? $payrollData['payslipData']->weekly_basic_salary :$payrollData['payslipData']->monthly_basic_salary }}">
                                            </td>
                                        </tr>

                                        @forelse($payrollData['earnings'] as $earning)
                                            <tr class="earning">
                                                <td class="d-flex align-items-center justify-content-between">
                                                    <strong>{{ $earning['name'] }}</strong>
                                                    <input type="number" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif step="0.1" oninput="validity.valid||(value='');" id="amount[{{$earning['salary_component_id']}}]" name="amount[{{$earning['salary_component_id']}}]" class="form-control w-50 income_amount" value="{{ $earning['amount'] }}" >
                                                </td>
                                            </tr>
                                        @empty

                                        @endforelse
                                        <tr class="earning">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ __('index.fixed_allowance') }}</strong>
                                                <input type="number" readonly step="0.1" oninput="validity.valid||(value='');" class="form-control w-50" name="monthly_fixed_allowance" id="monthly_fixed_allowance" value="{{ ($payrollData['payslipData']->salary_cycle == 'weekly') ? $payrollData['payslipData']->weekly_fixed_allowance :$payrollData['payslipData']->monthly_fixed_allowance }}">
                                            </td>
                                        </tr>
                                        @if($payrollData['payslipData']->ssf_contribution > 0)
                                            <tr class="deductions">
                                                <td class="d-flex align-items-center justify-content-between">
                                                    <strong>{{ __('index.ssf_contribution') }}</strong>
                                                    <input type="number" id="ssf_contribution" name="ssf_contribution" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif step="0.1" min="0" class="form-control w-50"
                                                           value="{{ $payrollData['payslipData']->ssf_contribution }}">
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-lg-6 col-md-6 mb-4">
                                    <h4 class="mb-2">{{ __('index.deduction') }}</h4>
                                    <table class="table table-bordered">
                                        <tbody>
                                        @php $totalDeduction = $payrollData['payslipData']->ssf_deduction; @endphp
                                        @forelse($payrollData['deductions'] as $deduction)
                                            <tr class="deductions">
                                                <td class="d-flex align-items-center justify-content-between">
                                                    <strong>{{ $deduction['name'] }}</strong>
                                                    <input type="number" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif step="0.1" oninput="validity.valid||(value='');" name="amount[{{$deduction['salary_component_id']}}]" class="form-control w-50 deduction_amount" value="{{ $deduction['amount'] }}" >
                                                </td>
                                                @php $totalDeduction += $deduction['amount']; @endphp
                                            </tr>
                                        @empty

                                        @endforelse
                                        @if($payrollData['payslipData']->ssf_deduction > 0)
                                        <tr class="deductions">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ __('index.ssf_deduction') }}</strong>
                                                <input type="number" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif class="form-control w-50" step="0.1" name="ssf_deduction" id="ssf_deduction" value="{{ $payrollData['payslipData']->ssf_deduction }}">
                                            </td>
                                        </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-lg-12 border-top pt-4">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <label class="mb-1 fw-bold">{{ __('index.actual_salary') }}</label>{{ __('index.actual_salary_formula') }}
                                        </div>
                                        <div class="col-lg-3">
                                            <span class="h5" id="actual_salary">{{ $currency.' '. $payrollData['payslipData']->gross_salary - $totalDeduction  }}</span>
                                        </div>

                                    </div>
                                </div>
                                @if($payrollData['payslipData']->bonus > 0)
                                <div class="col-lg-12 border-top pt-4">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <label class="mb-1 fw-bold">{{ __('index.bonus') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input type="hidden" class="form-control" name="bonus" step="0.1" oninput="validity.valid||(value='');" id="bonus" value="{{ $payrollData['payslipData']->bonus }}">
                                            <span class="h5" id="bonusAmount">{{ $currency.' '. $payrollData['payslipData']->bonus }}</span>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-lg-12 border-top pt-4">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <label class="mb-1 fw-bold">{{ __('index.taxable_salary') }} </label> {{ __('index.taxable_salary_formula') }}
                                        </div>
                                        <div class="col-lg-3">
                                            <span class="h5" id="taxable_salary">{{ $currency.' '. $payrollData['payslipData']->gross_salary + $payrollData['payslipData']->bonus - $totalDeduction  }}</span>
                                        </div>

                                    </div>
                                </div>
                                @endif
                                <div class="col-lg-12 border-top pt-4">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <label class="mb-1 fw-bold">{{ __('index.tax') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input type="hidden" class="form-control" name="tds" step="0.1" oninput="validity.valid||(value='');" id="tds" value="{{ $payrollData['payslipData']->tds }}">
                                            <span class="h5" id="tax">{{ $currency.' '.$payrollData['payslipData']->tds }}</span>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-lg-12 border-top pt-4">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <label class="mb-1 fw-bold">{{ __('index.salary_after_tax') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <span class="h5" id="salaryAfterTax">{{ $currency.' '.$payrollData['payslipData']->gross_salary + $payrollData['payslipData']->bonus - $totalDeduction - $payrollData['payslipData']->tds }}</span>
                                        </div>

                                    </div>
                                </div>
                                @if($payrollData['payslipData']->include_tada == 1)
                                <div class="col-lg-6 col-md-6 border-top mt-4 pt-4">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <small style="color:#e82e5f;">{{ __('index.earning') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.expenses_claim') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input type="number" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif id="tada" step="0.1" oninput="validity.valid||(value='');" name="tada" value="{{ $payrollData['payslipData']->tada }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($payrollData['payslipData']->include_advance_salary == 1)

                                <div class="col-lg-6 col-md-6 border-top mt-4 pt-4">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.advance_salary') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input type="number" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif id="advanceSalary" step="0.1" oninput="validity.valid||(value='');" name="advance_salary" class="form-control" value="{{ $payrollData['payslipData']->advance_salary }}">
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-lg-6 col-md-6 border-top mt-4 pt-4">
                                    <div class="row">

                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 border-top mt-4 pt-4">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.absent') }}</label>
                                            {{ __('index.absent_deduction_formula') }}((grossSalary/ total days) * absent days)
                                        </div>
                                        <div class="col-lg-4">
                                           <input type="number" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif step="0.1" name="absent_deduction" class="form-control" id="absentDeduction" value="{{ $payrollData['payslipData']->absent_deduction }}">
                                        </div>
                                    </div>
                                </div>
                                @if(isset($payrollData['payslipData']->ot_status) && $payrollData['payslipData']->ot_status  == 1)

                                <div class="col-lg-6 col-md-6 border-top mt-4 pt-4">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <small style="color:#e82e5f;">{{ __('index.earning') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.overtime') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input type="number" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif step="0.1" oninput="validity.valid||(value='');" id="overtime" name="overtime" value="{{ $payrollData['payslipData']->overtime }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(isset($underTimeSetting) && $underTimeSetting->is_active  == 1)
                                <div class="col-lg-6 col-md-6 border-top mt-4 pt-4">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.undertime') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input type="number" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif step="0.1" oninput="validity.valid||(value='');" id="undertime" name="undertime" value="{{ $payrollData['payslipData']->undertime  }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="col-lg-12 border-top mt-4 pt-4">
                                    <input type="hidden" readonly name="net_salary" id="netSalary" value="{{ $payrollData['payslipData']->net_salary }}">
                                    <h4 class="mb-1">{{ __('index.net_salary') }} : {{ $currency }} <span id="net_salary">{{ $payrollData['payslipData']->net_salary }}</span></h4>
                                    {{ __('index.net_salary_formula') }}
                                </div>
                            </div>
                        </diV>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary">{{ __('index.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.payroll.employeeSalary.common.scripts')
    <script>

        let initialAdvanceSalary = {{ $payrollData['payslipData']->advance_salary }} ;
        let initialTada = {{ $payrollData['payslipData']->tada }} ;
        let initialAbsentDeduction = {{ $payrollData['payslipData']->absent_deduction }} ;
        let initialOverTime = {{ $payrollData['payslipData']->overtime }} ;
        let initialUnderTime = {{ $payrollData['payslipData']->undertime }} ;

        $('#monthly_basic_salary').on('input', function () {
            calculateAllowance();
        })
        function calculateAllowance(){
            let totalEarnings = 0;
            let grossSalary = parseFloat({{ $payrollData['payslipData']->gross_salary }});
            let newBasicSalary = parseFloat($('#monthly_basic_salary').val()) || 0;


            $('.income_amount').each(function () {
                let earningAmount = parseFloat($(this).val()) || 0;
                totalEarnings += earningAmount;
            });

            let newAllowance = (grossSalary - newBasicSalary - totalEarnings);
            $('#monthly_fixed_allowance').val(newAllowance.toFixed(2));

        }


        $('.income_amount').on('input', function () {
            calculateAllowance();
        });

        $('.deduction_amount').on('input', function () {
            changeNetSalary();
        });

        $('#ssf_deduction').on('input', function () {
            changeNetSalary();
        });
        $('#ssf_contribution').on('input', function () {
            changeNetSalary();
        });
        $('#bonus').on('input', function () {
            changeNetSalary();
        });

        function changeNetSalary(){
            let maritalStatus = '{{ $payrollData['payslipData']->marital_status }}';
            let grossSalary = parseFloat({{ $payrollData['payslipData']->gross_salary }});
            let ssf_deduction = parseFloat($('#ssf_deduction').val()) || 0;
            let ssf_contribution = parseFloat($('#ssf_contribution').val()) || 0;
            let bonus = parseFloat($('#bonus').val()) || 0;

            let totalDeduction = 0;
            $('.deduction_amount').each(function () {
                let deductionAmount = parseFloat($(this).val()) || 0;
                totalDeduction += deductionAmount;
            });

            let actualSalary = grossSalary + ssf_contribution  - (totalDeduction + ssf_deduction);
            let tadaAmount = parseFloat($('#tada').val()) || 0;
            let advanceSalary = parseFloat($('#advanceSalary').val()) || 0;
            let absentDeduction = parseFloat($('#absentDeduction').val()) || 0;
            let overtime = parseFloat($('#overtime').val()) || 0;
            let undertime = parseFloat($('#undertime').val()) || 0;

            let taxableNormalSalary = actualSalary * 12;
            let taxableBonusAmount = bonus *12;
            console.log('taxableNormalSalary'+taxableNormalSalary);
            console.log('taxableBonusAmount'+taxableBonusAmount);
            let taxableSalary = actualSalary + bonus;
            Promise.all([
                calculateTaxPromise(taxableNormalSalary, maritalStatus),
                calculateTaxPromise(taxableBonusAmount, maritalStatus)
            ]).then(([normalTaxData, bonusTaxData]) => {
                let monthlyTax = 0;
                let yearlyTax = normalTaxData.total_tax;
                let bonusTax = bonusTaxData.monthly_tax;
                console.log('yearlyTax'+yearlyTax);
                console.log('bonusTax'+bonusTax);
                if (ssf_contribution > 0) {
                    yearlyTax -= normalTaxData.sst;

                    monthlyTax = yearlyTax/12;
                }


                let totalTax = monthlyTax + bonusTax;

                $('#tds').val(totalTax.toFixed(2));
                $('#tax').text(totalTax.toFixed(2));

                let salaryAfterTax = taxableSalary - totalTax;
                let netSalaryFinal = salaryAfterTax - advanceSalary + tadaAmount - absentDeduction + overtime - undertime;

                $('#taxable_salary').text(taxableSalary.toFixed(2));
                $('#salaryAfterTax').text(salaryAfterTax.toFixed(2));
                $('#actual_salary').text(actualSalary.toFixed(2));
                $('#netSalary').val(netSalaryFinal.toFixed(2));
                $('#net_salary').text(netSalaryFinal.toFixed(2));
            });
            // let salaryAfterTax = taxableSalary - tds;
            // let netSalary = salaryAfterTax - advanceSalary + tadaAmount - absentDeduction + overtime - undertime;
            //
            // $('#taxable_salary').text(taxableSalary.toFixed(2));
            // $('#salaryAfterTax').text(salaryAfterTax.toFixed(2));
            // $('#actual_salary').text(actualSalary.toFixed(2));
            // $('#netSalary').val(netSalary.toFixed(2));
            // $('#net_salary').text(netSalary.toFixed(2));


        }

        let previousAdvanceSalary = parseFloat($('#advanceSalary').val()) || 0;
        $('#advanceSalary').on('input', function () {

            let currentAdvanceSalary = parseFloat($(this).val()) || 0;

            let newSalary = parseFloat($('#netSalary').val()) || 0;

            if (previousAdvanceSalary > currentAdvanceSalary) {
                newSalary += (previousAdvanceSalary - currentAdvanceSalary);
            } else {
                newSalary -= (currentAdvanceSalary - previousAdvanceSalary);
            }

            $('#netSalary').val(newSalary.toFixed(2));
            $('#net_salary').text(newSalary.toFixed(2));
            previousAdvanceSalary = currentAdvanceSalary;
        });

        let previousTada = parseFloat($('#tada').val()) || 0;
        $('#tada').on('input', function () {

            let currentTada = parseFloat($(this).val()) || 0;

            let newSalary = parseFloat($('#netSalary').val()) || 0;

            if (previousTada > currentTada) {
                newSalary -= (previousTada - currentTada);
            } else {
                newSalary += (currentTada - previousTada);
            }

            $('#netSalary').val(newSalary.toFixed(2));
            $('#net_salary').text(newSalary.toFixed(2));
            previousTada = currentTada;
        });

        let previousOverTime = parseFloat($('#overtime').val()) || 0;
        $('#overtime').on('input', function () {

            let currentOverTime = parseFloat($(this).val()) || 0;

            let newSalary = parseFloat($('#netSalary').val()) || 0;

            if (previousOverTime > currentOverTime) {
                newSalary -= (previousOverTime - currentOverTime);
            } else {
                newSalary += (currentOverTime - previousOverTime);
            }

            $('#netSalary').val(newSalary.toFixed(2));
            $('#net_salary').text(newSalary.toFixed(2));
            previousOverTime = currentOverTime;
        });

        let previousUnderTime = parseFloat($('#undertime').val()) || 0;
        $('#undertime').on('input', function () {

            let currentUnderTime = parseFloat($(this).val()) || 0;

            let newSalary = parseFloat($('#netSalary').val()) || 0;

            if (previousUnderTime > currentUnderTime) {
                newSalary += (previousUnderTime - currentUnderTime);
            } else {
                newSalary -= (currentUnderTime - previousUnderTime);
            }

            $('#netSalary').val(newSalary.toFixed(2));
            $('#net_salary').text(newSalary.toFixed(2));
            previousUnderTime = currentUnderTime;
        });

        let previousAbsentDeduction = parseFloat($('#absentDeduction').val()) || 0;
        $('#absentDeduction').on('input', function () {

            let currentAbsentDeduction = parseFloat($(this).val()) || 0;


            let newSalary = parseFloat($('#netSalary').val()) || 0;

            if (previousAbsentDeduction > currentAbsentDeduction) {
                newSalary += (previousAbsentDeduction - currentAbsentDeduction);
            } else {
                newSalary -= (currentAbsentDeduction - previousAbsentDeduction);
            }

            $('#netSalary').val(newSalary.toFixed(2));
            $('#net_salary').text(newSalary.toFixed(2));
            previousAbsentDeduction = currentAbsentDeduction;
        });


        // paid status

        $('#payroll_status').on('change', function (){

            let status = $(this).val();
           if(status === "{{ $paidStatus }}"){
               $('.paidPayslip').removeClass('d-none');
           }else{
               $('.paidPayslip').addClass('d-none');
           }
        });

        function calculateTaxPromise(salary, maritalStatus) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '{{ route('admin.get-tax') }}',
                    type: 'GET',
                    data: {
                        salary: salary,
                        marital_status: maritalStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject('Failed to calculate tax');
                        }
                    },
                    error: function(error) {
                        reject(error);
                    }
                });
            });
        }
    </script>
@endsection

