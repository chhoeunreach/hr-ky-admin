@can('employee.employment.manage')
    <form method="post" action="{{ route('admin.employees.profile.employment.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add Employment History</h6>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">Effective Date</label>
                <input class="form-control" type="date" name="effective_date">
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">Change Type</label>
                <select class="form-control" name="change_type" required>
                    @foreach(['promotion', 'transfer', 'demotion', 'department_change', 'branch_change', 'manager_change', 'employment_status_change', 'other'] as $type)
                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Reason</label>
                <input class="form-control" name="reason">
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">Note</label>
                <textarea class="form-control" name="note" rows="2"></textarea>
            </div>
        </div>
        <button class="btn btn-primary">Add History</button>
    </form>
@endcan

<div class="table-responsive">
    <table class="table table-sm employee-360-table">
        <thead><tr><th>Date</th><th>Type</th><th>Reason</th><th>Note</th></tr></thead>
        <tbody>
        @forelse($employmentHistory as $record)
            <tr>
                <td>{{ optional($record->effective_date)->format('Y-m-d') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $record->change_type)) }}</td>
                <td>{{ $record->reason }}</td>
                <td>{{ $record->note }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">No records found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
