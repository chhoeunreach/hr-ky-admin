<div id="monthlyPayrollResults" aria-busy="false">
    <div class="row g-3 mb-3">
        @foreach(['records'=>'monthly_payroll_records','gross'=>'salary_table_gross','commission'=>'monthly_payroll_sales_commission','net'=>'net_salary'] as $key=>$label)
            <div class="col-md-3 col-6"><div class="card h-100"><div class="card-body py-3"><small class="text-muted">{{ __('index.'.$label) }}</small><strong class="d-block fs-5 mt-1">{{ $key === 'records' ? $summary[$key] : $currency.number_format((float) $summary[$key], 2) }}</strong></div></div></div>
        @endforeach
    </div>
    <div class="card"><div class="card-body">
        <h6 class="mb-2">{{ __('index.monthly_payroll') }} · {{ $month->format('Y-m') }}</h6>
        <p class="text-muted small">{{ __('index.monthly_payroll_commission_help') }}</p>
        <div class="table-responsive monthly-payroll-scroll">
            <table class="table monthly-payroll-table align-middle mb-0">
                <thead><tr>
                    <th>#</th><th class="monthly-code">{{ __('index.employee_code') }}</th><th>{{ __('index.monthly_payroll_photo') }}</th><th class="monthly-name">{{ __('index.employee_name') }}</th><th>{{ __('index.working_life') }}</th>
                    @foreach(['gross_salary'=>'salary_table_gross','present_days'=>'present_days','absent_days'=>'absent_days','paid_leave'=>'paid_leave','unpaid_leave'=>'unpaid_leave','bonus'=>'bonus'] as $key=>$label)<th>{{ __('index.'.$label) }}</th>@endforeach
                    @foreach($commissionTypes as $type)<th class="monthly-commission">{{ __('index.commission') }}<small class="d-block">{{ $type }}</small></th>@endforeach
                    @foreach(['overtime','tada','advance_salary','absent_deduction','undertime','tds','ssf_deduction','pf_deduction'] as $key)<th>{{ __('index.monthly_payroll_'.$key) }}</th>@endforeach
                    <th>{{ __('index.monthly_payroll_other_earnings') }}</th><th>{{ __('index.monthly_payroll_other_deductions') }}</th><th>{{ __('index.net_salary') }} ({{ $currency }})</th><th>{{ __('index.status') }}</th><th>{{ __('index.remark') }}</th><th>{{ __('index.action') }}</th>
                </tr></thead>
                <tbody>
                    @php
                        $offset = method_exists($payslips, 'firstItem') ? ($payslips->firstItem() ?? 1) : 1;
                        $money = fn ($value) => (float) $value === 0.0 ? '—' : $currency.number_format((float) $value, 2);
                    @endphp
                    @forelse($payslips as $index=>$payslip)
                        @php
                            $employee = $payslip->employee;
                            $end = \Carbon\Carbon::parse($payslip->salary_to);
                            $joined = $employee?->joining_date ? \Carbon\Carbon::parse($employee->joining_date) : null;
                            $tenure = $joined && $joined->lte($end) ? $joined->diff($end) : null;
                        @endphp
                        <tr>
                            <td>{{ $offset+$index }}</td><td class="monthly-code">{{ $employee?->employee_code ?: '—' }}</td>
                            <td><img class="monthly-payroll-avatar" src="{{ $employee?->avatar_url ?: asset('assets/images/img.png') }}" alt="" width="46" height="46" loading="lazy" style="--employee-avatar-ring: {{ \App\Support\EmployeeAvatar::borderColor($payslip->employee_id) }};" onerror="this.onerror=null;this.src='{{ asset('assets/images/img.png') }}';"></td>
                            <td class="monthly-name"><strong>{{ $employee?->name ?: '—' }}</strong><small class="d-block text-muted">{{ $employee?->username }}</small><small class="d-block text-muted">{{ $employee?->department?->dept_name }}</small></td>
                            <td>{{ $tenure ? __('index.monthly_payroll_tenure', ['years'=>$tenure->y,'months'=>$tenure->m,'days'=>$tenure->d]) : '—' }}</td>
                            <td class="money">{{ $money($payslip->gross_salary) }}</td>
                            @foreach(['present_days','absent_days','paid_leave','unpaid_leave'] as $key)<td class="text-center">{{ (float) $payslip->$key ?: '—' }}</td>@endforeach
                            <td class="money earning">{{ $money($payslip->bonus) }}</td>
                            @foreach($commissionTypes as $type)<td class="money earning">{{ $money($commissions[$payslip->employee_id][$type] ?? 0) }}</td>@endforeach
                            @foreach(['overtime','tada','advance_salary','absent_deduction','undertime','tds','ssf_deduction','pf_deduction'] as $key)<td class="money {{ in_array($key,['overtime','tada']) ? 'earning' : 'deduction' }}">{{ $money($payslip->$key) }}</td>@endforeach
                            <td class="money earning">{{ $money($service->componentTotal($payslip, 'earning')) }}</td><td class="money deduction">{{ $money($service->componentTotal($payslip, 'deductions')) }}</td>
                            <td class="money monthly-net">{{ $currency.number_format((float) $payslip->net_salary,2) }}</td>
                            <td><span class="badge {{ $payslip->status === 'paid' ? 'bg-success' : 'bg-light text-secondary border' }}">{{ __('index.'.$payslip->status) }}</span></td>
                            <td class="monthly-remark">{{ $payslip->remark ?: '—' }}</td>
                            <td><div class="d-flex gap-1">
                                @can('show_payroll_detail')<a class="btn btn-outline-primary btn-sm" data-open-employee-form data-employee-form-title="{{ __('index.employee_payslip') }} · {{ $employee?->name }}" href="{{ route('admin.employee-salary.payroll-detail',$payslip->id) }}">{{ __('index.view') }}</a>@endcan
                                @can('edit_payroll')
                                    @if($payslip->status !== 'paid')<a class="btn btn-outline-secondary btn-sm" data-open-employee-form data-employee-form-title="{{ __('index.edit') }} · {{ $employee?->name }}" href="{{ route('admin.employee-salary.payroll-edit',$payslip->id) }}">{{ __('index.edit') }}</a>@endif
                                @endcan
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 25+count($commissionTypes) }}" class="text-center py-5 text-muted">{{ __('index.monthly_payroll_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="monthly-payroll-footer mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-muted">{{ __('index.showing') }} {{ $payslips->count() ? $offset : 0 }} {{ __('index.to') }} {{ $payslips->count() ? $offset+$payslips->count()-1 : 0 }} {{ __('index.of') }} {{ $summary['records'] }}</small>
            @if(method_exists($payslips,'links')){{ $payslips->links() }}@endif
        </div>
    </div></div>
</div>
