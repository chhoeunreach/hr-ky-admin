<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i data-feather="gift" class="me-2 text-primary"></i>{{ __('index.rewards') ?? 'Rewards & Recognitions' }}</h6>
    @can('employee.reward.manage')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRewardModal">
            <i data-feather="plus" class="me-1"></i> {{ __('index.add_reward') }}
        </button>
    @endcan
</div>

<div class="table-responsive">
    <table class="table table-sm employee-360-table align-middle">
        <thead>
            <tr>
                <th>{{ __('index.date') }}</th>
                <th>{{ __('index.type') }}</th>
                <th>{{ __('index.title') }}</th>
                <th>{{ __('index.reward_amount') }}</th>
                <th>{{ __('index.description') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rewards as $record)
            <tr>
                <td>{{ optional($record->reward_date)->format('Y-m-d') }}</td>
                <td><span class="badge bg-light text-dark border">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->reward_type) ? __('index.' . $record->reward_type) : ucfirst(str_replace('_', ' ', $record->reward_type)) }}</span></td>
                <td class="fw-semibold">{{ $record->title }}</td>
                <td class="fw-bold text-success">{{ $record->reward_amount ? '$' . number_format((float)$record->reward_amount, 2) : '—' }}</td>
                <td>{{ $record->description ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@can('employee.reward.manage')
    <div class="modal fade" id="addRewardModal" tabindex="-1" aria-labelledby="addRewardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" action="{{ route('admin.employees.profile.rewards.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addRewardModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_reward') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.reward_date') }}</label>
                            <input class="form-control" type="date" name="reward_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.type') }}</label>
                            <select class="form-control" name="reward_type">
                                @foreach(['praise','certificate','bonus','employee_of_month','achievement','promotion','other'] as $item)
                                    <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.reward_amount') }}</label>
                            <input class="form-control" type="number" step="0.01" name="reward_amount" placeholder="0.00">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.title') }}</label>
                            <input class="form-control" name="title" placeholder="{{ __('index.title') }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.description') }}</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="{{ __('index.description') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_reward') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan
