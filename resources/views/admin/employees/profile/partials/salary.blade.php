@if($canViewSalary)
    @can('employee.salary.history.manage')
        <form method="post" action="{{ route('admin.employees.profile.salary.store', $employee->id) }}" class="employee-360-section">
            @csrf
            <h6>Add Salary History</h6>
            <div class="row">
                @foreach([
                    'effective_date' => ['Effective Date', 'date'],
                    'old_base_salary' => ['Old Base Salary', 'number'],
                    'increase_amount' => ['Increase Amount', 'number'],
                    'increase_percentage' => ['Increase %', 'number'],
                    'new_base_salary' => ['New Base Salary', 'number'],
                    'allowance_before' => ['Allowance Before', 'number'],
                    'allowance_after' => ['Allowance After', 'number'],
                ] as $field => [$label, $type])
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label">{{ $label }}</label>
                        <input class="form-control" type="{{ $type }}" step="0.01" name="{{ $field }}" @if($field === 'new_base_salary') required @endif>
                    </div>
                @endforeach
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="approval_status">
                        @foreach(['draft', 'pending', 'approved', 'rejected', 'cancelled'] as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Reason</label>
                    <textarea class="form-control" name="reason" rows="2"></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Note</label>
                    <textarea class="form-control" name="note" rows="2"></textarea>
                </div>
            </div>
            <button class="btn btn-primary">Add Salary History</button>
        </form>
    @endcan

    <div class="table-responsive">
        <table class="table table-sm employee-360-table">
            <thead><tr><th>Date</th><th>Old</th><th>Increase</th><th>New</th><th>Status</th><th>Reason</th></tr></thead>
            <tbody>
            @forelse($salaryHistory as $record)
                <tr>
                    <td>{{ optional($record->effective_date)->format('Y-m-d') }}</td>
                    <td>{{ $record->old_base_salary }}</td>
                    <td>{{ $record->increase_amount }} ({{ $record->increase_percentage }}%)</td>
                    <td>{{ $record->new_base_salary }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst($record->approval_status) }}</span></td>
                    <td>{{ $record->reason }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No records found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@else
    <p class="text-muted mb-0">Salary information is restricted.</p>
@endif
