@extends('layouts.master')

@section('title',__('index.employee_tax_report'))

@section('action',__('index.tax_report_detail'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.taxReport.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-body d-md-flex align-items-center justify-content-between pb-3 text-md-start text-center">
                <h4 class="mb-2"> {{ __('index.tax_report_detail_of') }} {{ $reportData->employee->name }} ({{ $reportData->fiscalYear->year }})</h4>
                <div class="edit-print mb-2">
                    <a class="me-2" href="{{ route('admin.payroll.tax-report.print',$reportData->id) }}"
                        target="_blank">
                        <i class="link-icon" data-feather="printer"></i>
                    </a>
                    <a href="{{ route('admin.payroll.tax-report.edit',$reportData->id) }}">
                        <i class="link-icon" data-feather="edit"></i>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <div class="salary-sheet">

                <div class="table-responsive mb-4">
                    <h5 class="text-lg-start text-center mb-3">{{ __('index.salary_sheet') }}</h5>
                    <table class="table table-bordered">
                    <thead class="thead-dark">
                    @php
                        $monthData = json_decode($reportData->months, true);
                        // Count the number of months
                        $totalMonth = count($monthData);

                        $startMonth = 4; // Default start month (Shrawan)
                        $endMonth = 3;   // Default end month (Asar)
                        // Create an array of all months
                        $allMonths = array_merge(range(4, 12), range(1, 3));

                        $incomeComponents = [
                            'Basic Salary' => $reportData['total_basic_salary']/$totalMonth,
                            'Allowance' => $reportData['total_allowance']/$totalMonth,
                            'SSF Contribution' => $reportData['total_ssf_contribution']/$totalMonth
                        ];
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

                                $monthlyBasicSalary[$month] = $detail->basic_salary ?? 0;
                                $monthlyFixedAllowance[$month] = $detail->fixed_allowance ?? 0;
                                $monthlySsfContribution[$month] = $detail->ssf_contribution ?? 0;
                                $monthlySsfDeduction[$month] = $detail->ssf_deduction ?? 0;
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
                            <td>{{ number_format($annualAmount, 2) }}</td>
                            @foreach($allMonths as $month)
                                @if(in_array($month, $monthData))
                                    @php $monthlyTotals[$month] += $earning->amount; @endphp
                                    <td>{{ number_format($earning->amount, 2) }}</td>
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
                                        <td>{{ number_format($totalBonus, 2) }}</td>
                                        @foreach($allMonths as $month)
                                            @if(in_array($month, $monthData))
                                                @php
                                                    $bonusAmount = ($bonus['month'] == $month) ? $bonus['amount'] : 0;
                                                    $monthlyTotals[$month] += $bonusAmount;
                                                @endphp
                                                <td>{{ $bonusAmount > 0 ? number_format($bonusAmount, 2) : '' }}</td>
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
                        <td>{{  number_format($totalAnnualIncome, 2) }}</td>
                        @foreach($allMonths as $month)
                            @if(in_array($month, $monthData))
                                <td>{{  number_format($monthlyTotals[$month], 2) }}</td>
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
                                    $totalMonthlyDeduction[$month] += $monthlySsfDeductionValue;
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
                            <td>{{ number_format($annualAmount, 2) }}</td>
                            @foreach($allMonths as $month)

                                @if(in_array($month, $monthData))

                                    <td>{{ number_format($deduction->amount, 2) }}</td>
                                @else
                                    <td></td>
                                @endif
                            @endforeach

                        </tr>
                    @endforeach

                    <tr class="highlight">
                        <td colspan="2">{{ __('index.total_deduction') }}</td>
                        <td>{{ number_format($totalAnnualDeduction, 2) }}</td>
                        @foreach($allMonths as $month)
                            @php   $showData = in_array($month, $monthData); @endphp
                            @if($showData)
                                <td>{{  number_format($totalMonthlyDeduction[$month], 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach

                    </tr>
                    <tr>
                        <td colspan="2">{{ __('index.tds_paid') }}</td>
                        <td></td>
                        @php
                            $monthlyTDS = array_fill(1, 12, 0); // Initialize an array for 12 months with 0
                            foreach ($reportData->tdsDetail as $tdsDetail) {

                                $monthlyTDS[$tdsDetail->month] = ($tdsDetail->is_paid == 1) ? $tdsDetail->amount : 0;
                            }
                        @endphp
                        @foreach($allMonths as $month)
                            @if(in_array($month, $monthData))
                                <td>{{ $monthlyTDS[$month] ?  number_format($monthlyTDS[$month], 2) : '' }}</td>
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
                        <td>{{  number_format($annualTotalPayable, 2) }}</td>
                        @foreach($allMonths as $month)

                            @php
                                $monthlyTotalPayable = $monthlyTotals[$month] - $totalMonthlyDeduction[$month] - ($monthlyTDS[$month] ?? 0);
                                $showData = in_array($month, $monthData);
                            @endphp
                            @if($showData)
                                <td>{{  number_format($monthlyTotalPayable, 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach

                    </tr>
                    </tbody>
                    </table>
                </div>

                <div class="table-responsive">
                    <h5 class="text-lg-start text-center mb-3">{{ __('index.additional_information') }}</h5>
                    <table class="table table-bordered mb-5">
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
                            <td>{{ __('index.less') }}</td>
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
                                <td>{{ number_format($annualAmount, 2) }}</td>
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
                                    {{ $adjustedComponentValue }}
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
                            <td class="taxable-income">{{ number_format($taxableIncome, 2) }}</td>
                        </tr>
                        </tbody>
                            </table>

                    @if (isset($taxData[$reportData->employee->marital_status]))
                        <h5 class="text-lg-start text-center mb-3">{{ __('index.tax_calculation_on_taxable_income') }} ({{ ucfirst($reportData->employee->marital_status) }})</h5>
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
                                $totalBonusTax = 0;
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
                            <tr class="highlight">
                                <td>{{ __('index.total') }}</td>
                                <td></td>
                                <td>{{ number_format($taxableIncome, 2) }}</td>
                                <td></td>
                                <td>{{ number_format($totalTax, 2) }}</td>
                            </tr>
                            @php
                                $taxDeduction = $reportData->medical_claim+$reportData->female_discount+$reportData->other_discount;
                            @endphp
                            <tr>
                                <td>{{ __('index.less_tax_deduction') }}</td>
                                <td>{{ __('index.medical_claim') }}</td>
                                <td></td>
                                <td></td>
                                <td>{{ $reportData->medical_claim }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>{{ __('index.female_discount') }}</td>
                                <td></td>
                                <td></td>
                                <td>{{ $reportData->female_discount }}</td>
                            </tr>
                            <tr style="border-bottom: 2px solid #232323;">
                                <td></td>
                                <td>{{ __('index.other_discount') }}</td>
                                <td></td>
                                <td></td>
                                <td>{{ $reportData->other_discount }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('index.total_payable_tds') }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <input type="hidden" name="">
                                <td>{{ number_format(($totalTax - $taxDeduction), 2) }}</td>
                            </tr>

                            <tr>
                                <td>{{ __('index.total_paid_tds') }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>{{ $reportData->total_paid_tds }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('index.total_due_tds') }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td id="dueTds">{{  number_format(($totalTax - $taxDeduction - $reportData->total_paid_tds),2) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    @else
                        <p class="text-center">{{ __('index.tax_data_not_available') }} ({{ $reportData->employee->name }}).</p>
                @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')


@endsection

