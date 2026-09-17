@if($canViewSalary)
    @can('employee.salary.history.manage')
        <form method="post" action="{{ route('admin.employees.profile.salary.store', $employee->id) }}" class="employee-360-section">
            @csrf
            <h6>{{ __('index.add_salary_history') }}</h6>
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
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">{{ $label }}</label>
                        <input class="form-control" type="{{ $type }}" step="0.01" name="{{ $field }}" @if($field === 'new_base_salary') required @endif>
                    </div>
                @endforeach
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">{{ __('index.status') }}</label>
                    <select class="form-control" name="approval_status">
                        @foreach(['draft', 'pending', 'approved', 'rejected', 'cancelled'] as $status)
                            <option value="{{ $status }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $status) ? __('index.' . $status) : ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('index.reason') }}</label>
                    <textarea class="form-control" name="reason" rows="2"></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('index.notes') }}</label>
                    <textarea class="form-control" name="note" rows="2"></textarea>
                </div>
            </div>
            <button class="btn btn-primary">{{ __('index.add_salary_history') }}</button>
        </form>
    @endcan

    <div class="table-responsive">
        <table class="table table-sm employee-360-table">
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
                    <td>{{ $record->old_base_salary }}</td>
                    <td>{{ $record->increase_amount }} ({{ $record->increase_percentage }}%)</td>
                    <td>{{ $record->new_base_salary }}</td>
                    <td><span class="badge bg-secondary">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->approval_status) ? __('index.' . $record->approval_status) : ucfirst($record->approval_status) }}</span></td>
                    <td>{{ $record->reason }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">{{ __('index.no_records_found') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@else
    <p class="text-muted mb-0">Salary information is restricted.</p>
@endif
