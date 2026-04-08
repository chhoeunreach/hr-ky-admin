@extends('layouts.master')

@section('title',__('index.employee_tax_report'))

@section('action',__('index.tax_report_edit'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.taxReport.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <h4 class="mb-2"> {{ __('index.tax_report_detail_of') }} {{ $reportData->employee->name }} ({{ $reportData->fiscalYear->year }})</h4>

            </div>
        </div>
    </section>
    <section>
        <div class="row my-5">
            <h5 class="text-center mb-2">{{ __('index.salary_sheet') }}</h5>
            <form action="{{ route('admin.payroll.tax-report.update', $reportData->id) }}" method="post">
                @csrf
                @method('PUT')
                <table class="table table-bordered table-responsive mb-3">
                    <thead class="thead-dark">
                    @php
                        $monthData = json_decode($reportData->months, true);
                        // Count the number of months
                        $totalMonth = count($monthData);

                        $startMonth = 4; // Default start month (Shrawan)
                        $endMonth = 3;   // Default end month (Asar)
                        // Create an array of all months
                        $allMonths = array_merge(range(4, 12), range(1, 3));

//                        $incomeComponents = [
//                            'Basic Salary' => $reportData['total_basic_salary']/$totalMonth,
//                            'Allowance' => $reportData['total_allowance']/$totalMonth,
//                            'SSF Contribution' => $reportData['total_ssf_contribution']/$totalMonth
//                        ];
                        $totalAnnualIncome = 0;
                        $monthlyTotals = array_fill(1, 12, 0);

                        // Accessing 'earning' type components
                        $earningComponents = $reportData->componentDetail->groupBy('type')->get('earning', collect());

                        // Accessing 'deduction' type components
                        $deductionComponents = $reportData->componentDetail->groupBy('type')->get('deductions', collect());

                        // access basic,salary, fixed allowance and  ssf data
                        $monthlyBasicSalary = [];
                        $monthlyFixedAllowance = [];
                        $monthlySsfContribution = [];
                        $monthlySsfDeduction = [];
                        if($reportData->reportDetail){
                            foreach ($reportData->reportDetail as $detail) {
                                $month = $detail->month; // get the month

                                $monthlyBasicSalary[$month] = $detail->basic_salary;
                                $monthlyFixedAllowance[$month] = $detail->fixed_allowance;
                                $monthlySsfContribution[$month] = $detail->ssf_contribution;
                                $monthlySsfDeduction[$month] = $detail->ssf_deduction;
                            }
                        }

                    @endphp

                    <tr>
                        <th colspan="2">{{ __('index.particular') }}</th>
                        <th>{{ __('index.total') }}</th>
                        @foreach($allMonths as $month)
                            <th>{{ $months[$month] }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th colspan="{{ 3+count($allMonths) }}" class="bg-light">{{ __('index.income') }}</th>
                    </tr>

                    <tr>
                        <td></td>
                        <td>{{ __('index.basic_salary') }}</td>

                        @php


                            $totalAnnualIncome += $reportData->total_basic_salary;
                        @endphp
                        <td>{{ $currency.' '.number_format($reportData->total_basic_salary, 2) }}</td>
                        @foreach($allMonths as $month)
                            @php

                                $showData = in_array($month, $monthData);
                                $monthlyBasicSalaryValue = $monthlyBasicSalary[$month] ?? 0;
                            @endphp
                            @if($showData)
                                @php
                                    $monthlyTotals[$month] += $monthlyBasicSalaryValue;
                                @endphp
                                <td>{{ $currency.' '.number_format($monthlyBasicSalaryValue, 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ __('index.fixed_allowance') }}</td>

                        @php

                            $totalAnnualIncome += $reportData->total_allowance;
                        @endphp
                        <td>{{ $currency.' '.number_format( $reportData->total_allowance, 2) }}</td>
                        @foreach($allMonths as $month)
                            @php

                                $showData = in_array($month, $monthData);
                                $monthlyFixedAllowanceValue = $monthlyFixedAllowance[$month] ?? 0;

                            @endphp
                            @if($showData)
                                @php
                                    $monthlyTotals[$month] += $monthlyFixedAllowanceValue;
                                @endphp
                                <td>{{ $currency.' '.number_format($monthlyFixedAllowanceValue, 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{ __('index.ssf_contribution') }}</td>

                        @php

                            $totalAnnualIncome += $reportData->total_ssf_contribution;
                        @endphp
                        <td>{{ $currency.' '.number_format($reportData->total_ssf_contribution, 2) }}</td>
                        @foreach($allMonths as $month)
                            @php

                                $showData = in_array($month, $monthData);
                                $monthlySsfContributionValue = $monthlySsfContribution[$month] ?? 0;

                            @endphp
                            @if($showData)
                                @php
                                    $monthlyTotals[$month] += $monthlySsfContributionValue;
                                @endphp
                                <td>{{ $currency.' '.number_format($monthlySsfContributionValue, 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>

                    @php
                        $totalEarning = 0;
                        $totalDeduction = 0;
                        $totalBonusAmount = 0;

                    @endphp
                    @foreach($earningComponents as $earning)
                        <tr>
                            <td></td>
                            <td>{{ $earning->salaryComponent->name }}</td>
                            @php
                                $annualAmount = 0;
                                foreach($allMonths as $month) {
                                    if (in_array($month, $monthData)) {
                                        $annualAmount += $earning->amount;
                                    }
                                }
                                $totalEarning += $annualAmount;
                                $totalAnnualIncome += $annualAmount;
                            @endphp
                            <td>{{ $currency.' '.number_format($annualAmount, 2) }}</td>
                            @foreach($allMonths as $month)
                                @if(in_array($month, $monthData))
                                    @php $monthlyTotals[$month] += $earning->amount; @endphp
                                    <td>{{ $currency.' '.number_format($earning->amount, 2) }}</td>
                                @else
                                    <td></td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach

                    @if(!empty($reportData->bonusDetail))
                        @foreach($reportData->bonusDetail as $bonus)
                            @if(in_array($bonus['month'], $allMonths))
                                @php
                                    $showBonus = false;
                                    $totalBonus = 0;
                                    foreach($allMonths as $month) {
                                        if (in_array($month, $monthData)) {
                                            if ($bonus['month'] == $month) {
                                                $showBonus = true;
                                                $totalBonus = $bonus['amount'];
                                                $totalBonusAmount += $bonus['amount'];
                                                $totalAnnualIncome += $totalBonus;
                                                $totalEarning += $totalBonus;
                                                break;
                                            }
                                        }
                                    }
                                @endphp

                                @if($showBonus)
                                    <tr>
                                        <td></td>
                                        <td>{{ $bonus->bonus->title }}</td>
                                        <td>{{ $currency.' '.number_format($totalBonus, 2) }}</td>
                                        @foreach($allMonths as $month)
                                            @if(in_array($month, $monthData))
                                                @php
                                                    $bonusAmount = ($bonus['month'] == $month) ? $bonus['amount'] : 0;
                                                    $monthlyTotals[$month] += $bonusAmount;
                                                @endphp
                                                <td>{{ $bonusAmount > 0 ? $currency.' '.number_format($bonusAmount, 2) : '' }}</td>
                                            @else
                                                <td></td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                            @endif
                        @endforeach
                    @endif

                    <tr class="highlight">
                        <td colspan="2">{{ __('index.total_income') }}</td>
                        <td>{{ number_format($totalAnnualIncome, 2) }}</td>
                        @foreach($allMonths as $month)
                            @if(in_array($month, $monthData))
                                <td>{{ number_format($monthlyTotals[$month], 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>

                    <tr>
                        <th colspan="{{ 3+count($allMonths) }}" class="bg-light">{{ __('index.deductions') }}</th>
                    </tr>
                    @php
                        $totalMonthlyDeduction = array_fill(1, 12, 0);
                        $totalAnnualDeduction = $reportData['total_ssf_deduction'];

                    @endphp

                    <tr>
                        <td></td>
                        <td>{{ __('index.ssf_deduction') }}</td>
                        <td>{{ $currency.' '.($reportData['total_ssf_deduction']) }}</td>
                        @foreach($allMonths as $month)
                            @php
                                $monthlySsfDeductionValue =$monthlySsfDeduction[$month] ?? 0;
                                $totalMonthlyDeduction[$month] += ($monthlySsfDeductionValue);
                            @endphp
                            @if(in_array($month, $monthData))
                                <td>{{ $currency.' '.$monthlySsfDeductionValue }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>

                    @foreach($deductionComponents as $deduction)
                        <tr>
                            <td></td>
                            <td>{{ $deduction->salaryComponent->name }}</td>

                            @php
                                $annualAmount = 0;
                                foreach($allMonths as $month) {
                                    if (in_array($month, $monthData)) {
                                        $annualAmount += $deduction->amount;
                                    }
                                    $totalMonthlyDeduction[$month] += $deduction->amount;
                                }
                                $totalAnnualDeduction += $annualAmount;
                                $totalDeduction += $annualAmount;
                            @endphp
                            <td>{{ $currency.' '.number_format($annualAmount, 2) }}</td>
                            @foreach($allMonths as $month)

                                @if(in_array($month, $monthData))

                                    <td>{{ $currency.' '.number_format($deduction->amount, 2) }}</td>
                                @else
                                    <td></td>
                                @endif
                            @endforeach

                        </tr>
                    @endforeach

                    <tr class="highlight">
                        <td colspan="2">{{ __('index.total_deduction') }}</td>
                        <td>{{ $currency.' '.number_format($totalAnnualDeduction, 2) }}</td>
                        @foreach($allMonths as $month)
                            @php   $showData = in_array($month, $monthData); @endphp
                            @if($showData)
                                <td>{{ number_format($totalMonthlyDeduction[$month], 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach

                    </tr>
                    <tr id="monthlyTds">
                        <td colspan="2">{{ __('index.tds_paid') }}</td>
                        @php
                            $totalTdsPaid = 0;
                            $monthlyTDS = array_fill(1, 12, 0); // Initialize an array for 12 months with 0
                            foreach ($reportData->tdsDetail as $tdsDetail) {

                                $monthlyTDS[$tdsDetail->month] = ($tdsDetail->is_paid == 1) ? $tdsDetail->amount : 0;
                                $totalTdsPaid +=  ($tdsDetail->is_paid == 1) ? $tdsDetail->amount : 0;
                            }
                        @endphp
                        <td>{{ number_format($totalTdsPaid, 2) }}</td>

                        @foreach($allMonths as $month)
                            @if(in_array($month, $monthData))
                                <td><input type="number" class="form-control" name="tds_paid[{{$month}}]" id="tds_paid[{{$month}}]" value="{{ $monthlyTDS[$month] ??  0 }}" step="0.01"></td>
                            @else
                                <td></td>
                            @endif
                        @endforeach

                    </tr>
                    <tr class="highlight">
                        <td colspan="2">{{ __('index.total_payable') }}</td>
                        @php
                            $annualTotalPayable = $totalAnnualIncome - $totalAnnualDeduction - 0;
                        @endphp
                        <td>{{ number_format($annualTotalPayable, 2) }}</td>
                        @foreach($allMonths as $month)

                            @php
                                $monthlyTotalPayable = $monthlyTotals[$month] - $totalMonthlyDeduction[$month] - ($monthlyTDS[$month] ?? 0);
                                  $showData = in_array($month, $monthData);
                            @endphp
                            @if($showData)
                                <td>{{ number_format($monthlyTotalPayable, 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach

                    </tr>
                    </tbody>
                </table>
                <h5 class="text-center mb-4">{{ __('index.additional_information') }}</h5>
                <table class="table table-bordered">
                    <thead class="thead-dark">
                    <tr>
                        <th colspan="3">{{ __('index.particular') }}</th>
                        <th>{{ __('index.amount') }}</th>
                        <th>{{ __('index.amount') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="3">{{ __('index.total_income') }}</td>
                        <td></td>
                        <td>{{ number_format($totalAnnualIncome, 2) }}</td>
                    </tr>
                    @php
                        $ssfContribution = 0;
                        $totalRetirementContribution = 0;
                        $totalMonthlyDeduction = array_fill(1, 12, 0);

                        $totalRetirementContribution = $totalAnnualDeduction;

                    @endphp

                    <tr>
                        <td>less</td>
                        <td colspan="2">{{ __('index.total_retirement_contribution_allowed') }}</td>
                        <td></td>
                        <td>{{ number_format($totalRetirementContribution, 2) }}</td>
                    </tr>

                    <tr>
                        <td></td>
                        <td></td>
                        <td>{{ __('index.ssf_contribution_salary') }}</td>
                        <td>{{ number_format($reportData['total_ssf_deduction'], 2) }}</td>
                        <td></td>
                    </tr>

                    @foreach($deductionComponents as $deduction)
                        <tr>
                            <td></td>
                            <td></td>
                            <td>{{ $deduction->salaryComponent->name }}</td>

                            @php
                                $annualAmount = 0;
                                foreach($allMonths as $month) {
                                    if (in_array($month, $monthData)) {
                                        $annualAmount += $deduction->amount;
                                    }
                                }
                            @endphp
                            <td>{{ $currency.' '.number_format($annualAmount, 2) }}</td>
                        </tr>
                    @endforeach


                    @php
                        $annualSalary =  $reportData['total_basic_salary']+$reportData['total_allowance']+$totalEarning - $totalDeduction;
                        $basicSalary = $reportData['total_basic_salary']/$totalMonth;
                        $totalOtherComponents = 0;
                    @endphp

                    @foreach ($reportData->additionalDetail as $component)
                        @php
                            // Adjust for joining date
                            $monthlyComponentValue = $component->amount;
                            $adjustedComponentValue = 0;
                            foreach($allMonths as $month) {
                                if (in_array($month, $monthData)) {
                                    $adjustedComponentValue += $monthlyComponentValue;
                                }
                            }

                            $totalOtherComponents += $adjustedComponentValue;
                        @endphp
                        <tr>
                            <td></td>
                            <td>{{ $component->salaryComponent->name }}</td>
                            <td></td>
                            <td></td>
                            <td>
                                <input type="number"
                                       name="other_component[{{ $component->id }}]"
                                       value="{{ $adjustedComponentValue }}"
                                       step="0.01"
                                       class="form-control other-component-input"
                                       data-id="{{ $component->id }}">
                            </td>
                        </tr>
                    @endforeach

                    @php
                        $totalDeductions = $totalRetirementContribution + $totalOtherComponents;
                        $taxableIncome = $totalAnnualIncome - $totalDeductions;
                    @endphp

                    <tr class="highlight">
                        <td colspan="3">{{ __('index.taxable_income') }}</td>
                        <td></td>
                        <td id="taxableIncome">{{ number_format($taxableIncome, 2) }}</td>
                    </tr>
                    </tbody>
                </table>

                @if (isset($taxData[$reportData->employee->marital_status]))
                    <h5 class="text-center mb-4">{{ __('index.tax_calculation_on_taxable_income') }} ({{ ucfirst($reportData->employee->marital_status) }})</h5>
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                        <tr>
                            <th>{{ __('index.from') }}</th>
                            <th>{{ __('index.to') }}</th>
                            <th>{{ __('index.income') }}</th>
                            <th>{{ __('index.percent') }}</th>
                            <th>{{ __('index.tax_amount') }}</th>
                        </tr>
                        </thead>
                        <tbody id="tax-calculation-body">
                        @php
                            $remainingIncome = $taxableIncome;
                            $totalTax = 0;
                            $isFirstBracket = true;
                        @endphp

                        @foreach($taxData[$reportData->employee->marital_status] as $bracket)
                            @php
                                $from = $bracket->annual_salary_from;
                                $to = $bracket->annual_salary_to >= 1.0E+20 ? null : $bracket->annual_salary_to;
                                $percent = $bracket->tds_in_percent;
                                $bracketIncome = min(max($remainingIncome, 0), $to ? $to - $from : $remainingIncome);
                                $taxAmount = $bracketIncome * ($percent / 100);

                                // Adjust the condition to check for SSF contribution
                                if ($isFirstBracket && $reportData['total_ssf_deduction'] > 0) {
                                    $taxAmount = 0;
                                }

                                $totalTax += $taxAmount;
                                $remainingIncome -= $bracketIncome;
                                $isFirstBracket = false;
                            @endphp
                            <tr>
                                <td>{{ number_format($from, 2) }}</td>
                                <td>{{ $to ? number_format($to, 2) : '' }}</td>
                                <td>{{ number_format($bracketIncome, 2) }}</td>
                                <td>{{ number_format($percent, 2) }}%</td>
                                <td>{{ number_format($taxAmount, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>

                        <tr class="highlight">
                            <td>{{ __('index.total') }}</td>
                            <td></td>
                            <td id="taxableIncome">{{ number_format($taxableIncome, 2) }}</td>
                            <td></td>
                            <td id="total-tax">{{ number_format($totalTax, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('index.less_tax_deduction') }}</td>
                            <td>{{ __('index.medical_claim') }}</td>
                            <td></td>
                            <td></td>
                            <td><input type="number" class="form-control editable-amount" name="medical_claim" id="medical_claim" value="{{ $reportData->medical_claim }}" step="0.01" oninput="updateTotal()"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>{{ __('index.female_discount') }}</td>
                            <td></td>
                            <td></td>
                            <td><input type="number" class="form-control editable-amount" name="female_discount" id="female_discount" value="{{ $reportData->female_discount }}" step="0.01" oninput="updateTotal()"></td>
                        </tr>
                        <tr style="border-bottom: 2px solid;">
                            <td></td>
                            <td>{{ __('index.other_discount') }}</td>
                            <td></td>
                            <td></td>
                            <td><input type="number" class="form-control editable-amount" name="other_discount" id="other_discount" value="{{ $reportData->other_discount }}" step="0.01" oninput="updateTotal()"></td>
                        </tr>
                        <tr>
                            <td>{{ __('index.total_payable_tds') }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <input type="hidden" name="">
                            <td id="totalPayableTax">{{ number_format( ($totalTax - ($reportData->medical_claim+$reportData->female_discount+$reportData->other_discount)), 2) }}</td>
                        </tr>

                        <tr>
                            <td>{{ __('index.total_paid_tds') }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><input type="number" class="form-control editable-amount" name="total_paid_tds" id="total-paid-tds" value="{{ $reportData->total_paid_tds }}" step="0.01"></td>
                        </tr>
                        <tr>
                            <td>{{ __('index.total_due_tds') }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td id="dueTds">{{ $totalTax - ($reportData->medical_claim+$reportData->female_discount+$reportData->other_discount) - $reportData->total_paid_tds }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('index.tds_calculation_msg') }}  </th>
                            <th></th>
                            <th>{{ __('index.remaining_month') }} </th>
                            <th> <input type="number" class="form-control editable-amount" name="total_month" id="totalMonth" value="{{ $totalMonth }}" step="0.01"> </th>
                            <th> <span id="remainTdsByMonth">{{ $reportData->total_due_tds/$totalMonth }}</span> {{ __('index.remain_tds_formula') }} </th>
                        </tr>
                        </tfoot>
                    </table>
                @else
                    <p class="text-center">{{ __('index.tax_data_not_available') }} ({{ $reportData->employee->name }}).</p>
                @endif

                <div class="row justify-content-center mt-4">
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">{{ __('index.update') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const otherComponentInputs = document.querySelectorAll('.other-component-input');
            const taxableIncomeElement = document.getElementById('taxableIncome');
            const totalTaxElement = document.getElementById('total-tax');
            const totalPayableTaxElement = document.getElementById('totalPayableTax');
            const totalPaidTdsInput = document.getElementById('total-paid-tds');
            const totalMonthInput = document.getElementById('totalMonth');
            const totalDueTdsElement = document.getElementById('dueTds');
            const remainTdsByMonthElement = document.getElementById('remainTdsByMonth');
            const taxCalculationBody = document.getElementById('tax-calculation-body');

            let totalAnnualIncome = {{ $totalAnnualIncome }};
            let totalRetirementContribution = {{ $totalRetirementContribution }};
            let taxBrackets = @json($taxData[$reportData?->employee?->marital_status] ?? []);

            let totalMonths = {{ $totalMonth }};
            let ssfDeduction = {{ $reportData['total_ssf_deduction'] }};


            function recalculateTaxableIncome() {
                let totalOtherComponents = Array.from(otherComponentInputs).reduce((sum, input) => {
                    return sum + (parseFloat(input.value) || 0);
                }, 0);

                const totalDeductions = totalRetirementContribution + totalOtherComponents;
                const taxableIncome = totalAnnualIncome - totalDeductions;

                taxableIncomeElement.textContent = taxableIncome.toFixed(2);

                recalculateTax(taxableIncome);
            }

            function recalculateTax(taxableIncome) {
                let remainingIncome = taxableIncome;
                let totalTax = 0;
                let isFirstBracket = true;

                taxCalculationBody.innerHTML = '';

                taxBrackets.forEach(bracket => {
                    let from = bracket.annual_salary_from;
                    let to = bracket.annual_salary_to >= 1.0E+20 ? null : bracket.annual_salary_to;
                    let percent = bracket.tds_in_percent;
                    let bracketIncome = Math.min(Math.max(remainingIncome, 0), to ? to - from : remainingIncome);
                    let taxAmount = bracketIncome * (percent / 100);

                    if (isFirstBracket && ssfDeduction > 0) {
                        taxAmount = 0;
                    }

                    totalTax += taxAmount;
                    remainingIncome -= bracketIncome;
                    isFirstBracket = false;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                                <td>${number_format(from, 2)}</td>
                                <td>${to ? number_format(to, 2) : ''}</td>
                                <td>${number_format(bracketIncome, 2)}</td>
                                <td>${number_format(percent, 2)}%</td>
                                <td>${number_format(taxAmount, 2)}</td>
                            `;
                    taxCalculationBody.appendChild(row);
                });

                totalTaxElement.textContent = totalTax.toFixed(2);

                updateTotalPayableTax(totalTax);
            }

            function updateTotalPayableTax(totalTax) {
                const medicalClaim = parseFloat(document.getElementById('medical_claim').value) || 0;
                const femaleDiscount = parseFloat(document.getElementById('female_discount').value) || 0;
                const otherDiscount = parseFloat(document.getElementById('other_discount').value) || 0;

                const totalPayableTDS = totalTax - (medicalClaim + femaleDiscount + otherDiscount);

                updateTotalDueTds(totalPayableTDS);
            }

            function updateTotalDueTds(totalPayableTDS) {
                const totalPaidTds = parseFloat(totalPaidTdsInput.value) || 0;
                const totalDueTds = totalPayableTDS - (totalPaidTds || 0);
                totalDueTdsElement.textContent = totalDueTds.toFixed(2);

                const totalMonth = parseFloat(totalMonthInput.value) || 1;
                let duePerMonth = 0;
                if(totalMonths == totalMonth){
                    duePerMonth = totalDueTds;
                }else{
                    duePerMonth = (totalDueTds / totalMonths) * totalMonth;
                }

                remainTdsByMonthElement.textContent = duePerMonth.toFixed(2);
            }

            function number_format(number, decimals) {
                return parseFloat(number).toFixed(decimals);
            }

            otherComponentInputs.forEach(input => {
                input.addEventListener('input', recalculateTaxableIncome);
            });

            document.querySelectorAll('.editable-amount').forEach(input => {
                input.addEventListener('input', () => {
                    updateTotalPayableTax(parseFloat(totalTaxElement.textContent));
                });
            });

            recalculateTaxableIncome();
        });
    </script>
@endsection

