@if($canViewSalary)
    @php
        $payrollSalary = $employee->employeeSalary;
        $completedSalaryReviews = $reviews->where('status', 'completed');
        $selectedSalaryReview = $completedSalaryReviews->firstWhere('id', old('performance_review_id', request('salary_review')));
        $salaryBase = $payrollSalary ? ($payrollSalary->payment_type === 'weekly'
            ? $payrollSalary->weekly_basic_salary : $payrollSalary->monthly_basic_salary) : $profile->current_base_salary;
        $salaryAllowance = $payrollSalary ? ($payrollSalary->payment_type === 'weekly'
            ? $payrollSalary->weekly_fixed_allowance : $payrollSalary->monthly_fixed_allowance) : $profile->allowances;
        $currencySymbol = \App\Helpers\AppHelper::getCompanyPaymentCurrencySymbol();
        $formatPayrollMoney = fn ($amount) => filled($amount)
            ? $currencySymbol . number_format((float) $amount, 2)
            : '—';
        $monthlyGrossSalary = $payrollSalary ? ((float) $payrollSalary->annual_salary / 12) : null;
        $weeklyGrossSalary = $payrollSalary ? ((float) $payrollSalary->annual_salary / 52) : null;
        $basicCalculation = $payrollSalary
            ? ($payrollSalary->basic_salary_type === 'percent'
                ? number_format((float) $payrollSalary->basic_salary_value, 2) . '%'
                : $formatPayrollMoney($payrollSalary->basic_salary_value))
            : '—';
    @endphp

    <div class="alert alert-light border mb-3">
        <p class="fw-semibold mb-1">{{ __('index.salary_manage_steps') }}</p>
        <p class="small text-muted mb-2">{{ __('index.salary_manage_help') }}</p>
        <div class="d-flex flex-wrap gap-2">
            @can('view_salary_list')
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.employee-salaries.index', ['employee_id' => $employee->id]) }}">{{ __('index.salary_manage_payroll') }}</a>
            @endcan
            @if($payrollSalary)
                @can('salary_increment')
                    <a class="btn btn-primary btn-sm" data-open-employee-form href="{{ route('admin.employee-salaries.increase-salary', $employee->id) }}">{{ __('index.increase_salary') }}</a>
                @endcan
                @can('show_salary_history')
                    <a class="btn btn-outline-secondary btn-sm" data-open-employee-form href="{{ route('admin.employee-salaries.salary-revise-history.show', $employee->id) }}">{{ __('index.salary_manage_payroll_history') }}</a>
                @endcan
            @endif
        </div>
    </div>
    <div class="employee-360-section">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h6 class="mb-0 fw-bold">
                <i data-feather="credit-card" class="me-2 text-primary"></i>{{ __('index.current_salary') }}
            </h6>
            @if($payrollSalary)
                @can('edit_salary')
                    <a class="btn btn-outline-primary btn-sm" data-open-employee-form href="{{ route('admin.employee-salaries.edit-salary', $employee->id) }}">
                        <i data-feather="edit-2" class="me-1"></i> {{ __('index.edit_salary') }}
                    </a>
                @endcan
            @else
                @can('add_salary')
                    <a class="btn btn-primary btn-sm" data-open-employee-form href="{{ route('admin.employee-salaries.add', $employee->id) }}">
                        <i data-feather="plus" class="me-1"></i> {{ __('index.add_salary') }}
                    </a>
                @endcan
            @endif
        </div>

        @if($payrollSalary)
            <div class="employee-360-grid mb-4">
                <div class="employee-360-metric">
                    <small>{{ __('index.payroll_type') }}</small>
                    <strong>{{ __('index.' . $payrollSalary->payroll_type) }}</strong>
                </div>
                <div class="employee-360-metric">
                    <small>{{ __('index.payment_type') }}</small>
                    <strong>{{ __('index.' . $payrollSalary->payment_type) }}</strong>
                </div>
                <div class="employee-360-metric">
                    <small>{{ __('index.annual_salary') }}</small>
                    <strong>{{ $formatPayrollMoney($payrollSalary->annual_salary) }}</strong>
                </div>
                @if($payrollSalary->payroll_type === 'hourly')
                    <div class="employee-360-metric">
                        <small>{{ __('index.hourly_rate') }}</small>
                        <strong>{{ $formatPayrollMoney($payrollSalary->hour_rate) }}</strong>
                    </div>
                    <div class="employee-360-metric">
                        <small>{{ $payrollSalary->payment_type === 'weekly' ? __('index.working_hours_in_week') : __('index.working_hours_in_month') }}</small>
                        <strong>{{ number_format((float) ($payrollSalary->payment_type === 'weekly' ? $payrollSalary->weekly_hours : $payrollSalary->monthly_hours), 2) }}</strong>
                    </div>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-sm employee-360-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('index.salary_component') }}</th>
                            <th>{{ __('index.calculation_type') }}</th>
                            @if($payrollSalary->payment_type === 'weekly')
                                <th class="text-end">{{ __('index.weekly_amount') }} ({{ $currencySymbol }})</th>
                            @else
                                <th class="text-end">{{ __('index.monthly_amount') }} ({{ $currencySymbol }})</th>
                            @endif
                            <th class="text-end">{{ __('index.annual_amount') }} ({{ $currencySymbol }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold">{{ __('index.basic_salary') }}</td>
                            <td>
                                <span class="fw-semibold">{{ $basicCalculation }}</span>
                                <small class="d-block text-muted">{{ __('index.' . $payrollSalary->basic_salary_type) }}</small>
                            </td>
                            @if($payrollSalary->payment_type === 'weekly')
                                <td class="text-end">{{ $formatPayrollMoney($payrollSalary->weekly_basic_salary) }}</td>
                            @else
                                <td class="text-end">{{ $formatPayrollMoney($payrollSalary->monthly_basic_salary) }}</td>
                            @endif
                            <td class="text-end">{{ $formatPayrollMoney($payrollSalary->annual_basic_salary) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{ __('index.fixed_allowance') }}</td>
                            <td>{{ __('index.fixed') }}</td>
                            @if($payrollSalary->payment_type === 'weekly')
                                <td class="text-end">{{ $formatPayrollMoney($payrollSalary->weekly_fixed_allowance) }}</td>
                            @else
                                <td class="text-end">{{ $formatPayrollMoney($payrollSalary->monthly_fixed_allowance) }}</td>
                            @endif
                            <td class="text-end">{{ $formatPayrollMoney($payrollSalary->annual_fixed_allowance) }}</td>
                        </tr>
                        @php $otherEarnings = max(0, (float) $payrollSalary->annual_salary - (float) $payrollSalary->annual_basic_salary - (float) $payrollSalary->annual_fixed_allowance); @endphp
                        @if($otherEarnings > 0.01)
                            <tr><td>{{ __('index.salary_manage_earnings') }}</td><td>—</td><td class="text-end">{{ $formatPayrollMoney($otherEarnings / ($payrollSalary->payment_type === 'weekly' ? 52 : 12)) }}</td><td class="text-end">{{ $formatPayrollMoney($otherEarnings) }}</td></tr>
                        @endif
                        <tr class="table-light">
                            <th>{{ __('index.total') }}</th>
                            <th></th>
                            @if($payrollSalary->payment_type === 'weekly')
                                <th class="text-end">{{ $formatPayrollMoney($weeklyGrossSalary) }}</th>
                            @else
                                <th class="text-end">{{ $formatPayrollMoney($monthlyGrossSalary) }}</th>
                            @endif
                            <th class="text-end">{{ $formatPayrollMoney($payrollSalary->annual_salary) }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">{{ __('index.no_records_found') }}</p>
        @endif
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0 fw-bold"><i data-feather="dollar-sign" class="me-2 text-primary"></i>{{ __('index.salary_history') }}</h6>
        @can('employee.salary.history.manage')
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSalaryHistoryModal">
                <i data-feather="plus" class="me-1"></i> {{ __('index.salary_manage_request') }}
            </button>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-sm employee-360-table align-middle">
            <thead>
                <tr>
                    <th>{{ __('index.date') }}</th>
                    <th>{{ __('index.old_base_salary') }}</th>
                    <th>{{ __('index.increase_amount') }}</th>
                    <th>{{ __('index.new_base_salary') }}</th>
                    <th>{{ __('index.status') }}</th>
                    <th>{{ __('index.reason') }}</th>
                    <th>{{ __('index.action') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($salaryHistory as $record)
                <tr>
                    <td>{{ optional($record->effective_date)->format('Y-m-d') }}</td>
                    <td>{{ $formatPayrollMoney($record->old_base_salary) }}</td>
                    <td>
                        <span class="text-success fw-semibold">+{{ $formatPayrollMoney($record->increase_amount) }}</span>
                        <small class="text-muted">({{ $record->increase_percentage }}%)</small>
                    </td>
                    <td class="fw-bold text-dark">{{ $formatPayrollMoney($record->new_base_salary) }}</td>
                    <td><span class="badge {{ $record->approval_status === 'approved' ? 'bg-success' : ($record->approval_status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->approval_status) ? __('index.' . $record->approval_status) : ucfirst($record->approval_status) }}</span></td>
                    <td>
                        {{ $record->reason ?: '—' }}
                        @if(($record->approved_by_name ?: $record->approvedBy?->name))
                            <small class="d-block text-muted">{{ __('index.approved_by') }}: {{ $record->approved_by_name ?: $record->approvedBy?->name }}</small>
                        @elseif(($record->requested_by_name ?: $record->requestedBy?->name))
                            <small class="d-block text-muted">{{ __('index.requested_by') }}: {{ $record->requested_by_name ?: $record->requestedBy?->name }}</small>
                        @endif
                        @if($record->performanceReview)
                            <a class="d-block small" href="{{ route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'evaluation']) }}">{{ __('index.linked_review') }} #{{ $record->performance_review_id }} · {{ $record->performanceReview->total_score }}</a>
                        @endif
                    </td>
                    <td>@include('admin.employees.profile.partials.salary-actions', ['salaryRecord' => $record])</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @can('employee.salary.history.manage')
        <div class="modal fade" id="addSalaryHistoryModal" tabindex="-1" aria-labelledby="addSalaryHistoryModalLabel" aria-hidden="true" data-reopen="{{ $errors->any() && old('salary_form') ? '1' : '0' }}">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form method="post" action="{{ route('admin.employees.profile.salary.store', $employee->id) }}" class="modal-content" data-salary-increase-form data-salary-increase-error="{{ __('index.salary_increase_must_be_greater') }}">
                    @csrf
                    <input type="hidden" name="salary_form" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="addSalaryHistoryModalLabel">
                            <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.salary_manage_request') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                    </div>
                    <div class="modal-body p-4">
                        @if($errors->any() && old('salary_form'))
                            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                        @endif
                        <p class="small text-muted">{{ __('index.salary_manage_request_help') }}</p>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="salaryPerformanceReview" class="form-label fw-semibold">{{ __('index.linked_performance_review') }}</label>
                                <select id="salaryPerformanceReview" class="form-control" name="performance_review_id">
                                    <option value="">{{ __('index.no_linked_review') }}</option>
                                    @foreach($completedSalaryReviews as $salaryReview)
                                        <option value="{{ $salaryReview->id }}" @selected($selectedSalaryReview?->id === $salaryReview->id)>#{{ $salaryReview->id }} · {{ __('index.' . $salaryReview->review_type) }} · {{ $salaryReview->review_date?->format('Y-m-d') }} · {{ $salaryReview->total_score }}</option>
                                    @endforeach
                                </select>
                                <p class="form-text">{{ __('index.linked_salary_increase_help') }}</p>
                            </div>
                            @foreach([
                                'effective_date' => [__('index.effective_date'), 'date'],
                                'old_base_salary' => [__('index.old_base_salary'), 'number'],
                                'increase_amount' => [__('index.increase_amount'), 'number'],
                                'increase_percentage' => [__('index.increase_percentage'), 'number'],
                                'new_base_salary' => [__('index.new_base_salary'), 'number'],
                                'allowance_before' => [__('index.allowance_before'), 'number'],
                                'allowance_after' => [__('index.allowance_after'), 'number'],
                            ] as $field => [$label, $type])
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <label class="form-label fw-semibold">{{ $label }}</label>
                                    <input class="form-control" type="{{ $type }}" @if($type === 'number') step="0.01" min="0" @endif name="{{ $field }}" value="{{ old($field, match ($field) { 'old_base_salary' => $salaryBase, 'effective_date' => now()->toDateString(), 'allowance_before', 'allowance_after' => $salaryAllowance, default => null }) }}" @if(in_array($field, ['old_base_salary', 'increase_amount', 'increase_percentage', 'allowance_before'])) readonly @endif @if(in_array($field, ['new_base_salary', 'effective_date', 'old_base_salary'])) required @endif>
                                </div>
                            @endforeach
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                                <select class="form-control" name="approval_status">
                                    @foreach(['draft', 'pending'] as $status)
                                        <option value="{{ $status }}" @selected(old('approval_status', 'pending') === $status)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $status) ? __('index.' . $status) : ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.reason') }}</label>
                                <textarea class="form-control" name="reason" rows="2" placeholder="{{ __('index.reason') }}">{{ old('reason') }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.notes') }}</label>
                                <textarea class="form-control" name="note" rows="2" placeholder="{{ __('index.notes') }}">{{ old('note') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="check" class="me-1"></i> {{ __('index.salary_manage_request') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@else
    <p class="text-muted mb-0">{{ __('index.salary_information_restricted') }}</p>
@endif
