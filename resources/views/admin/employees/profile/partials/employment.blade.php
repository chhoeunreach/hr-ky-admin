@can('employee.employment.manage')
    <form method="post" action="{{ route('admin.employees.profile.employment.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>{{ __('index.add_employment_history') }}</h6>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">{{ __('index.effective_date') }}</label>
                <input class="form-control" type="date" name="effective_date">
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">{{ __('index.change_type') }}</label>
                <select class="form-control" name="change_type" required>
                    @foreach(['promotion', 'transfer', 'demotion', 'department_change', 'branch_change', 'manager_change', 'employment_status_change', 'other'] as $type)
                        <option value="{{ $type }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $type) ? __('index.' . $type) : ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('index.reason') }}</label>
                <input class="form-control" name="reason">
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">{{ __('index.notes') }}</label>
                <textarea class="form-control" name="note" rows="2"></textarea>
            </div>
        </div>
        <button class="btn btn-primary">{{ __('index.add_history') }}</button>
    </form>
@endcan

<div class="table-responsive">
    <table class="table table-sm employee-360-table">
        <thead>
            <tr>
                <th>{{ __('index.date') }}</th>
                <th>{{ __('index.type') }}</th>
                <th>{{ __('index.reason') }}</th>
                <th>{{ __('index.notes') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($employmentHistory as $record)
            <tr>
                <td>{{ optional($record->effective_date)->format('Y-m-d') }}</td>
                <td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->change_type) ? __('index.' . $record->change_type) : ucfirst(str_replace('_', ' ', $record->change_type)) }}</td>
                <td>{{ $record->reason }}</td>
                <td>{{ $record->note }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">{{ __('index.no_records_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
