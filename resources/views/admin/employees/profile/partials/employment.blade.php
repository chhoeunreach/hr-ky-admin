<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i data-feather="briefcase" class="me-2 text-primary"></i>{{ __('index.employment_history') ?? 'Employment History' }}</h6>
    @can('employee.employment.manage')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEmploymentHistoryModal">
            <i data-feather="plus" class="me-1"></i> {{ __('index.add_employment_history') }}
        </button>
    @endcan
</div>

<div class="table-responsive">
    <table class="table table-sm employee-360-table align-middle">
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
                <td><span class="badge bg-light text-dark border">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->change_type) ? __('index.' . $record->change_type) : ucfirst(str_replace('_', ' ', $record->change_type)) }}</span></td>
                <td>{{ $record->reason ?: '—' }}</td>
                <td>{{ $record->note ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@can('employee.employment.manage')
    <div class="modal fade" id="addEmploymentHistoryModal" tabindex="-1" aria-labelledby="addEmploymentHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" action="{{ route('admin.employees.profile.employment.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addEmploymentHistoryModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_employment_history') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.effective_date') }}</label>
                            <input class="form-control" type="date" name="effective_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-lg-6 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.change_type') }}</label>
                            <select class="form-control" name="change_type" required>
                                @foreach(['promotion', 'transfer', 'demotion', 'department_change', 'branch_change', 'manager_change', 'employment_status_change', 'other'] as $type)
                                    <option value="{{ $type }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $type) ? __('index.' . $type) : ucfirst(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.reason') }}</label>
                            <input class="form-control" name="reason" placeholder="{{ __('index.reason') }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.notes') }}</label>
                            <textarea class="form-control" name="note" rows="3" placeholder="{{ __('index.notes') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_history') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan
