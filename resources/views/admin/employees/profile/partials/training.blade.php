<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i data-feather="book-open" class="me-2 text-primary"></i>{{ __('index.training') ?? 'Training History' }}</h6>
    @can('employee.training.manage')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTrainingModal">
            <i data-feather="plus" class="me-1"></i> {{ __('index.add_training') }}
        </button>
    @endcan
</div>

<div class="table-responsive">
    <table class="table table-sm employee-360-table align-middle">
        <thead>
            <tr>
                <th>{{ __('index.date') }}</th>
                <th>{{ __('index.title') }}</th>
                <th>{{ __('index.type') }}</th>
                <th>{{ __('index.trainer') }}</th>
                <th>{{ __('index.score') }}</th>
                <th>{{ __('index.result') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($training as $record)
            <tr>
                <td>{{ optional($record->training_date)->format('Y-m-d') }}</td>
                <td class="fw-semibold">{{ $record->training_title }}</td>
                <td><span class="badge bg-light text-dark border">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->training_type) ? __('index.' . $record->training_type) : ($record->training_type ? ucfirst(str_replace('_', ' ', $record->training_type)) : '—') }}</span></td>
                <td>{{ $record->trainer_name ?: '—' }}</td>
                <td>{{ $record->score !== null ? $record->score : '—' }}</td>
                <td><span class="badge bg-success">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->result) ? __('index.' . $record->result) : ($record->result ?: 'Completed') }}</span></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@can('employee.training.manage')
    <div class="modal fade" id="addTrainingModal" tabindex="-1" aria-labelledby="addTrainingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" action="{{ route('admin.employees.profile.training.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addTrainingModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_training') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.date') }}</label>
                            <input class="form-control" type="date" name="training_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.title') }}</label>
                            <input class="form-control" name="training_title" placeholder="{{ __('index.title') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.type') }}</label>
                            <select class="form-control" name="training_type">
                                @foreach(['internal','external','online','on_job_training','orientation'] as $item)
                                    <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.trainer') }}</label>
                            <input class="form-control" name="trainer_name" placeholder="{{ __('index.trainer') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.score') }}</label>
                            <input class="form-control" type="number" step="0.01" name="score" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.objective') }}</label>
                            <textarea class="form-control" name="objective" rows="3" placeholder="{{ __('index.objective') }}"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.note') }}</label>
                            <textarea class="form-control" name="note" rows="3" placeholder="{{ __('index.note') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_training') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan
