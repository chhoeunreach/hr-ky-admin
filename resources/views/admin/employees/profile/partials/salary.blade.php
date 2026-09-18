@if($canViewSalary)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0 fw-bold"><i data-feather="dollar-sign" class="me-2 text-primary"></i>{{ __('index.salary_history') ?? 'Salary History' }}</h6>
        @can('employee.salary.history.manage')
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSalaryHistoryModal">
                <i data-feather="plus" class="me-1"></i> {{ __('index.add_salary_history') }}
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
                </tr>
            </thead>
            <tbody>
            @forelse($salaryHistory as $record)
                <tr>
                    <td>{{ optional($record->effective_date)->format('Y-m-d') }}</td>
                    <td>${{ number_format((float)$record->old_base_salary, 2) }}</td>
                    <td>
                        <span class="text-success fw-semibold">+${{ number_format((float)$record->increase_amount, 2) }}</span>
                        <small class="text-muted">({{ $record->increase_percentage }}%)</small>
                    </td>
                    <td class="fw-bold text-dark">${{ number_format((float)$record->new_base_salary, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->approval_status) ? __('index.' . $record->approval_status) : ucfirst($record->approval_status) }}</span></td>
                    <td>{{ $record->reason ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @can('employee.salary.history.manage')
        <div class="modal fade" id="addSalaryHistoryModal" tabindex="-1" aria-labelledby="addSalaryHistoryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form method="post" action="{{ route('admin.employees.profile.salary.store', $employee->id) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="addSalaryHistoryModalLabel">
                            <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_salary_history') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
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
                                    <input class="form-control" type="{{ $type }}" step="0.01" name="{{ $field }}" @if($field === 'new_base_salary') required @endif>
                                </div>
                            @endforeach
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                                <select class="form-control" name="approval_status">
                                    @foreach(['draft', 'pending', 'approved', 'rejected', 'cancelled'] as $status)
                                        <option value="{{ $status }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $status) ? __('index.' . $status) : ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.reason') }}</label>
                                <textarea class="form-control" name="reason" rows="2" placeholder="{{ __('index.reason') }}"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.notes') }}</label>
                                <textarea class="form-control" name="note" rows="2" placeholder="{{ __('index.notes') }}"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="check" class="me-1"></i> {{ __('index.add_salary_history') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@else
    <p class="text-muted mb-0">Salary information is restricted.</p>
@endif
