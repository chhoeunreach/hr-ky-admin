<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            box-sizing: border-box;
        }


        header {
            margin: 20px 0;
        }

        header div {
            display: flex;
            justify-content: center;
        }

        header img {
            width: 250px;
        }
        header h2, header p {
            text-align: center;
        }

        .separator {
            border-top: 1px solid #000;
            margin: 20px 0;
        }

        .payslip-heading {
            margin: 20px 0;
        }

        .employee-info {
            margin-bottom: 20px;
            text-align: left;
        }
        .employee-info table th,  .employee-info table td {
            padding: 8px 8px 8px 0;
        }

         .attendance-info {
            margin-bottom: 20px;
            text-align: left;
        }
        .attendance-info table th,  .attendance-info table td {
            padding: 8px 8px 8px 0;
        }

        .tables-wrapper {
            display: flex;
            margin: 20px 0;
        }

        .table-container {
            flex: 1; /* Adjust as needed */
            width: 50%;
            margin-right: 2px; /* Add margin between tables if needed */
        }

        .table-container table {
            width: 100%;
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
            border-collapse: collapse;
        }

        .table-container th{
            background-color: #dcdfe3;
            padding: 8px 8px 8px 0;
            text-align: left;
        }
        .table-container td {
            padding: 8px 0;
            text-align: left;
        }

        .other_info{
            display: flex;
            margin: 20px 0;
        }
        .other_info table {
            width: 100%;
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
            border-collapse: collapse;
        }

        .other_info th, .other_info td{
            padding: 8px 8px 8px 0;
            text-align: left;
        }
        .salary{
            background-color: #dcdfe3;
            border-top: 1px solid black;
            /*border-bottom: 1px solid black;*/
        }
        .totals{
            border-top: 1px solid black;
        }

        .horizontal-line {
            border-top: 1px solid #000;
            margin: 20px 0;
        }

        .net-salary {
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <header>
            <!-- Company Logo, Name, Address, Email, and Phone -->
                <div>
                    <img src="{{ asset($companyLogoPath.$payslipDetail->company_logo) }}" alt="Company Logo">
                </div>


            <h2>{{ $payslipDetail->company_name }}</h2>
            <p> {{ $payslipDetail->company_address }} | Email: {{ $payslipDetail->company_email }} | Phone: {{ $payslipDetail->company_phone }}</p>
        </header>

        <!-- Horizontal Line -->
        <div class="separator"></div>

        <!-- Payslip Heading -->
        <div class="payslip-heading">
            <h2>
                @if($payslipDetail->salary_cycle == 'monthly')
                    Payslip for the Month of {{ \App\Helpers\AttendanceHelper::payslipDuration($payslipDetail->salary_from, $payslipDetail->salary_to) }}
                @else
                    Payslip from {{ \App\Helpers\AttendanceHelper::payslipDate($payslipDetail->salary_from) }} to {{ \App\Helpers\AttendanceHelper::payslipDate($payslipDetail->salary_to) }}
                @endif

            </h2>
        </div>

        <!-- Employee Information -->
        <div class="employee-info">
            <table>
                <tr>
                    <th>Employee ID:</th><td>{{ $payslipDetail->employee_code }}</td>
                    <th>Name:</th><td>{{ $payslipDetail->employee_name }}</td>
                </tr>
                <tr>
                    <th>Salary Slip:</th><td>XYZ-001</td>
                    <th>Department:</th><td>{{ $payslipDetail->department }}</td>
                </tr>
                <tr>
                    <th>Designation:</th><td>{{ $payslipDetail->designation }}</td>
                    <th>Joining Date:</th><td>{{ isset($payslipDetail->joining_date) ? \App\Helpers\AttendanceHelper::payslipDate($payslipDetail->joining_date):'' }}</td>
                </tr>

            </table>

        </div>
        <div class="attendance-info">
            <table>
                <tr>
                    <th>Total Days</th>
                    <td>{{  $payslipDetail->total_days }}</td>
                    <th>Present</th>
                    <td>{{ $payslipDetail->present_days }}</td>
                    <th>Absent</th>
                    <td>{{ $payslipDetail->absent_days }}</td>
                    <th>Leave</th>
                    <td>{{ $payslipDetail->leave_days }}</td>
                    <th>Holidays</th>
                    <td>{{ $payslipDetail->holidays }}</td>
                    <th>Weekend</th>
                    <td>{{ $payslipDetail->weekends }}</td>
                </tr>

            </table>

        </div>
        <!-- Tables for Earnings and Deductions -->
        <div class="tables-wrapper">
            <!-- Table for Earnings -->
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>Earnings</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td> {{ ($payslipDetail->salary_cycle == 'weekly') ? $payslipDetail->weekly_basic_salary :$payslipDetail->monthly_basic_salary }}</td>
                        </tr>
                        @php $totalEarning = 0; @endphp
                        @forelse( $earnings as $earning)
                            <tr>
                                <td>{{ $earning['name'] }}</td>
                                <td>{{ $earning['amount'] }}</td>
                                @php $totalEarning+=$earning['amount']; @endphp
                            </tr>
                        @empty
                        @endforelse
                        <tr>
                            <td>Fixed Allowance</td>
                            <td> {{ ($payslipDetail->salary_cycle == 'weekly') ? $payslipDetail->weekly_fixed_allowance :  $payslipDetail->monthly_fixed_allowance }}</td>
                        </tr>
                        <tr class="totals">
                            <th>Gross Earnings</th>
                            <th> {{ $payslipDetail->gross_salary }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table for Deductions -->
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>Deductions</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                        @php $totalDeduction = $payslipDetail->tds; @endphp
                        @forelse( $deductions as $deduction)
                            <tr>
                                <td>{{ $deduction['name'] }}</td>
                                <td>{{ $deduction['amount'] }}</td>
                                @php $totalDeduction+=$deduction['amount']; @endphp
                            </tr>
                        @empty
                        @endforelse
                        <tr>
                            <td>TDS</td>
                            <td>{{ $payslipDetail->tds }}</td>
                        </tr>
                        <tr class="totals">
                            <th>Total Deductions</th>
                            <th> {{ $totalDeduction }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="other_info">
            <table>

                <tr class="salary">
                    <th>Actual Salary <span style="font-weight: normal">(Total Earning - Total Deductions)</span></th>
                    <th>{{ $currency.' '. $payslipDetail->gross_salary - $totalDeduction }}</th>
                </tr>

                @if($payslipDetail->include_advance_salary ==1)
                    <tr>
                        <th>Advance Salary <span style="font-weight: normal">(-)</span></th>
                        <td> {{ $payslipDetail->advance_salary ?? 0 }}</td>
                    </tr>
                @endif

                @if($payslipDetail->include_tada ==1)
                    <tr>
                        <th>TADA <span style="font-weight: normal">(+)</span></th>
                        <td> {{ $payslipDetail->tada ?? 0 }}</td>
                    </tr>
                @endif

                <tr>

                </tr>
                <tr>
                    <th>Absent Deduction <span style="font-weight: normal">((grossSalary/ total days) * absent days)</span></th>
                    <th>

                        {{ $payslipDetail->absent_deduction ?? 0 }}
                    </th>
                </tr>
                @if($payslipDetail->ot_status  == 1)
                <tr>
                    <th>OverTime Income</th>
                    <th>

                        {{ $payslipDetail->overtime }}
                    </th>
                </tr>
                @endif
                @if($payslipDetail->undertime  > 0)
                <tr>
                    <th>UnderTime Deduction</th>
                    <th>

                        {{ $payslipDetail->undertime }}
                    </th>
                </tr>
                @endif
            </table>
        </div>


        <!-- Horizontal Line -->
        <div class="horizontal-line"></div>

        <!-- Net Salary -->
        <div class="net-salary">
            <p> Net Salary: {{ $currency.' '. $payslipDetail->net_salary }}</p>
            @php $numberToWords = new \MilanTarami\NumberToWordsConverter\Services\NumberToWords(); @endphp
            <p>
                ({{ $numberToWords->get($payslipDetail->net_salary) }})</p>
            <p style="font-weight: normal">Net Salary = (Actual Salary - Advance Salary + TADA)</p>
        </div>
    </div>
</body>
<script>
    window.print();
    window.onfocus = function () {
        window.close();
    }
</script>
</html>
