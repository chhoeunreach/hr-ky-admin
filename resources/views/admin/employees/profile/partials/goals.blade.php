<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h6 class="mb-0 fw-bold"><i data-feather="check-circle" class="me-2 text-primary"></i>{{ __('index.goals_and_improvement') ?? 'Goals & Improvement Plans' }}</h6>
    @can('employee.goal.manage')
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                <i data-feather="plus" class="me-1"></i> {{ __('index.add_goal') }}
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addImprovementPlanModal">
                <i data-feather="plus" class="me-1"></i> {{ __('index.add_plan') }}
            </button>
        </div>
    @endcan
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i data-feather="flag" class="me-2 text-secondary"></i>{{ __('index.goals') }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm employee-360-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('index.title') }}</th>
                        <th>{{ __('index.target') }}</th>
                        <th>{{ __('index.due') }}</th>
                        <th>{{ __('index.progress') }}</th>
                        <th>{{ __('index.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($goals as $record)
                    <tr>
                        <td class="fw-semibold">{{ $record->title }}</td>
                        <td>{{ $record->target ?: '—' }}</td>
                        <td>{{ optional($record->due_date)->format('Y-m-d') ?: '—' }}</td>
                        <td style="width: 20%;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $record->progress }}%" aria-valuenow="{{ $record->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="small fw-semibold">{{ $record->progress }}%</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $goalStatusColors = ['completed' => 'bg-success', 'in_progress' => 'bg-primary', 'not_started' => 'bg-secondary', 'overdue' => 'bg-danger', 'cancelled' => 'bg-dark'];
                                $gClass = $goalStatusColors[$record->status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $gClass }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->status) ? __('index.' . $record->status) : ucfirst(str_replace('_', ' ', $record->status)) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i data-feather="trending-up" class="me-2 text-secondary"></i>{{ __('index.improvement_plans') }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm employee-360-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('index.period_start') }}</th>
                        <th>{{ __('index.period_end') }}</th>
                        <th>{{ __('index.status') }}</th>
                        <th>{{ __('index.reason') }}</th>
                        <th>{{ __('index.progress_notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($improvementPlans as $record)
                    <tr>
                        <td>{{ optional($record->start_date)->format('Y-m-d') ?: '—' }}</td>
                        <td>{{ optional($record->end_date)->format('Y-m-d') ?: '—' }}</td>
                        <td>
                            @php
                                $pipStatusColors = ['active' => 'bg-primary', 'completed' => 'bg-success', 'draft' => 'bg-secondary', 'failed' => 'bg-danger', 'cancelled' => 'bg-dark'];
                                $pipClass = $pipStatusColors[$record->status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $pipClass }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->status) ? __('index.' . $record->status) : ucfirst($record->status) }}</span>
                        </td>
                        <td>{{ $record->reason ?: '—' }}</td>
                        <td>{{ $record->progress_notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@can('employee.goal.manage')
    {{-- Add Goal Modal --}}
    <div class="modal fade" id="addGoalModal" tabindex="-1" aria-labelledby="addGoalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" action="{{ route('admin.employees.profile.goals.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addGoalModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_goal') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.title') }}</label>
                            <input class="form-control" name="title" placeholder="{{ __('index.title') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.target') }}</label>
                            <input class="form-control" name="target" placeholder="{{ __('index.target') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.period_start') }}</label>
                            <input class="form-control" type="date" name="start_date">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.due') }}</label>
                            <input class="form-control" type="date" name="due_date">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.progress') }} (%)</label>
                            <input class="form-control" type="number" min="0" max="100" name="progress" value="0">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                            <select class="form-control" name="status">
                                @foreach(['not_started','in_progress','completed','overdue','cancelled'] as $item)
                                    <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
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
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_goal') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Improvement Plan Modal --}}
    <div class="modal fade" id="addImprovementPlanModal" tabindex="-1" aria-labelledby="addImprovementPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" action="{{ route('admin.employees.profile.improvement-plans.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addImprovementPlanModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_improvement_plan') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.period_start') }}</label>
                            <input class="form-control" type="date" name="start_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.period_end') }}</label>
                            <input class="form-control" type="date" name="end_date">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                            <select class="form-control" name="status">
                                @foreach(['draft','active','completed','failed','cancelled'] as $item)
                                    <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst($item) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.reason') }}</label>
                            <textarea class="form-control" name="reason" rows="2" placeholder="{{ __('index.reason') }}"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.expectations') }}</label>
                            <textarea class="form-control" name="expectations" rows="3" placeholder="{{ __('index.expectations') }}"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.support_required') }}</label>
                            <textarea class="form-control" name="support_required" rows="3" placeholder="{{ __('index.support_required') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_plan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan
