<div id="monthlyPayrollResults" aria-busy="false">
    <div class="card"><div class="card-body">
        <div class="monthly-payroll-record-state mb-2">
            <span class="badge {{ $isSavedReport ? 'bg-success' : 'bg-light text-secondary border' }}">{{ !empty($isPreview) ? __('index.monthly_payroll_preview') : __($isSavedReport ? 'index.monthly_payroll_saved_record' : 'index.monthly_payroll_not_saved') }}</span>
            @if($isSavedReport)<small class="text-muted">{{ $savedRecords->pluck('branch_name')->join(' · ') }} · {{ $savedRecords->max('created_at')->format('Y-m-d H:i') }}</small>@endif
            <span class="small text-muted ms-auto" title="{{ __('index.monthly_payroll_loan_help') }}">{{ __('index.monthly_payroll_loan_total') }}: <strong>{{ $currency.number_format((float) $summary['advance_salary'], 2) }}</strong></span>
        </div>
        <h6 class="mb-2">{{ __('index.monthly_payroll') }} · {{ $month->format('Y-m') }}</h6>
        <p class="text-muted small">{{ __('index.monthly_payroll_commission_help') }}</p>
        <div class="table-responsive monthly-payroll-scroll">
            <table class="table monthly-payroll-table align-middle mb-0">
                <thead><tr>
                    @foreach($columns as $key => $column)<th scope="col" class="{{ $column['class'] }}" @if($key === 'deduction') title="{{ __('index.monthly_payroll_deduction_help') }}" @elseif($key === 'attendance_bonus') title="{{ __('index.monthly_payroll_attendance_bonus_help', [], 'km') }}" @elseif($key === 'pay') title="{{ __('index.monthly_payroll_unpaid_help', [], 'km') }}" @endif>{{ $column['label'] }}</th>@endforeach
                </tr></thead>
                <tbody>
                    @php
                        $offset = method_exists($payslips, 'firstItem') ? ($payslips->firstItem() ?? 1) : 1;
                        $money = fn ($value) => $value === null || (float) $value === 0.0 ? '—' : $currency.number_format((float) $value, 2);
                    @endphp
                    @forelse($payslips as $index => $payslip)
                        @php
                            $employee = $payslip->employee;
                            $values = $service->rowValues($payslip, $commissions[$payslip->employee_id] ?? [], $offset + $index);
                        @endphp
                        <tr>
                            @foreach($columns as $key => $column)
                                <td class="{{ $column['class'] }}">
                                    @if($key === 'photo')
                                        <img class="monthly-payroll-avatar" src="{{ $values['photo'] ?: asset('assets/images/img.png') }}" alt="" width="46" height="46" loading="lazy" style="--employee-avatar-ring: {{ \App\Support\EmployeeAvatar::borderColor($payslip->employee_id) }};" onerror="this.onerror=null;this.src='{{ asset('assets/images/img.png') }}';">
                                    @elseif($key === 'employee')
                                        <strong>{{ $values[$key] ?: '—' }}</strong>
                                        <small class="d-block text-muted">{{ $employee?->username }}</small>
                                        <span class="badge {{ $payslip->status === 'paid' ? 'bg-success' : 'bg-light text-secondary border' }}" title="{{ $payslip->remark }}">{{ __('index.'.$payslip->status) }}</span>
                                        <div class="d-flex gap-1 mt-1">
                                            @if(!$isSavedReport && empty($isPreview))
                                            @can('show_payroll_detail')<a class="btn btn-outline-primary btn-sm" data-open-employee-form data-employee-form-title="{{ __('index.employee_payslip') }} · {{ $employee?->name }}" href="{{ route('admin.employee-salary.payroll-detail', $payslip->id) }}">{{ __('index.view') }}</a>@endcan
                                            @can('edit_payroll')
                                                @if($payslip->status !== 'paid')<a class="btn btn-outline-secondary btn-sm" data-open-employee-form data-employee-form-title="{{ __('index.edit') }} · {{ $employee?->name }}" href="{{ route('admin.employee-salary.payroll-edit', $payslip->id) }}">{{ __('index.edit') }}</a>@endif
                                            @endcan
                                            @endif
                                        </div>
                                    @elseif($key === 'bonus')
                                        {{ $money($values[$key]) }}
                                        @if(!$isSavedReport && empty($isPreview) && in_array($payslip->status, ['generated', 'review'], true))
                                            @can('edit_payroll')
                                                <details class="monthly-incentive-editor">
                                                    <summary>{{ __('index.monthly_payroll_incentive_edit', [], 'km') }}</summary>
                                                    <form method="post" action="{{ route('admin.monthly-payroll.incentive', $payslip->id) }}">
                                                        @csrf
                                                        <strong>{{ $values['employee'] }} · {{ $month->format('Y-m') }}</strong>
                                                        <label for="incentive-{{ $payslip->id }}">{{ __('index.monthly_payroll_incentive_amount', [], 'km') }} ({{ $currency }})</label>
                                                        <input id="incentive-{{ $payslip->id }}" class="form-control form-control-sm" type="number" name="bonus" min="0" max="999999999" step="0.01" value="{{ number_format((float) $values['bonus'], 2, '.', '') }}" required>
                                                        <input type="hidden" name="expected_bonus" value="{{ number_format((float) $values['bonus'], 2, '.', '') }}">
                                                        <input type="hidden" name="expected_net" value="{{ number_format((float) $values['net_salary'], 2, '.', '') }}">
                                                        <small>{{ __('index.monthly_payroll_incentive_help', [], 'km') }}</small>
                                                        <button class="btn btn-primary btn-sm mt-2" type="submit">{{ __('index.save', [], 'km') }}</button>
                                                    </form>
                                                </details>
                                            @endcan
                                        @endif
                                    @elseif($key === 'deduction' && is_array($values['deduction_breakdown'] ?? null))
                                        <details class="monthly-deduction-details">
                                            <summary>{{ $currency.number_format((float) $values[$key], 2) }}<small>{{ __('index.monthly_payroll_deduction_details', [], 'km') }}</small></summary>
                                            <dl>
                                                @foreach($values['deduction_breakdown'] as $category => $amount)
                                                    <div><dt>{{ __('index.monthly_payroll_deduction_'.$category, [], 'km') }}</dt><dd>{{ $currency.number_format((float) $amount, 2) }}</dd></div>
                                                @endforeach
                                            </dl>
                                        </details>
                                    @elseif($column['format'] === 'money')
                                        {{ in_array($key, ['net_salary', 'deduction', 'attendance_bonus']) && $values[$key] !== null ? $currency.number_format((float) $values[$key], 2) : $money($values[$key]) }}
                                    @else
                                        {{ $values[$key] ?? '—' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($columns) }}" class="text-center py-5 text-muted">{{ __('index.monthly_payroll_empty') }}</td></tr>
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
