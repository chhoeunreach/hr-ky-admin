<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h6 class="mb-0 fw-bold"><i data-feather="target" class="me-2 text-primary"></i>{{ __('index.kpi') ?? 'KPI & Responsibilities' }}</h6>
    @can('employee.kpi.manage')
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addResponsibilityModal">
                <i data-feather="plus" class="me-1"></i> {{ __('index.add_responsibility') }}
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addKpiModal">
                <i data-feather="plus" class="me-1"></i> {{ __('index.add_kpi') }}
            </button>
        </div>
    @endcan
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i data-feather="list" class="me-2 text-secondary"></i>{{ __('index.responsibilities') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm employee-360-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('index.title') }}</th>
                                <th>{{ __('index.kpi_target') }}</th>
                                <th>{{ __('index.weight') }}</th>
                                <th>{{ __('index.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($responsibilities as $record)
                            <tr>
                                <td class="fw-semibold">{{ $record->title }}</td>
                                <td>{{ $record->kpi_target ?: '—' }}</td>
                                <td>{{ $record->weight ? $record->weight . '%' : '—' }}</td>
                                <td>
                                    <span class="badge {{ $record->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ \Illuminate\Support\Facades\Lang::has('index.' . $record->status) ? __('index.' . $record->status) : ucfirst($record->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i data-feather="activity" class="me-2 text-secondary"></i>KPIs</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm employee-360-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('index.name') }}</th>
                                <th>{{ __('index.target') }}</th>
                                <th>{{ __('index.actual') }}</th>
                                <th>{{ __('index.unit') }}</th>
                                <th>{{ __('index.score') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($kpis as $record)
                            <tr>
                                <td class="fw-semibold">{{ $record->name }}</td>
                                <td>{{ $record->target_value !== null ? $record->target_value : '—' }}</td>
                                <td>{{ $record->actual_value !== null ? $record->actual_value : '—' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($record->unit) }}</span></td>
                                <td class="fw-bold text-primary">{{ $record->score !== null ? $record->score : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@can('employee.kpi.manage')
    {{-- Add Responsibility Modal --}}
    <div class="modal fade" id="addResponsibilityModal" tabindex="-1" aria-labelledby="addResponsibilityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" action="{{ route('admin.employees.profile.responsibilities.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addResponsibilityModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_responsibility') }}
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
                            <label class="form-label fw-semibold">{{ __('index.kpi_target') }}</label>
                            <input class="form-control" name="kpi_target" placeholder="{{ __('index.kpi_target') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.weight') }} (%)</label>
                            <input class="form-control" type="number" step="0.01" name="weight" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                            <select class="form-control" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
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
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_responsibility') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add KPI Modal --}}
    <div class="modal fade" id="addKpiModal" tabindex="-1" aria-labelledby="addKpiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" action="{{ route('admin.employees.profile.kpis.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addKpiModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_kpi') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.name') }}</label>
                            <input class="form-control" name="name" placeholder="{{ __('index.name') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.target') }}</label>
                            <input class="form-control" type="number" step="0.01" name="target_value" placeholder="0.00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.actual') }}</label>
                            <input class="form-control" type="number" step="0.01" name="actual_value" placeholder="0.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.unit') }}</label>
                            <select class="form-control" name="unit">
                                @foreach(['number','percentage','currency','minutes','hours','days','custom'] as $item)
                                    <option value="{{ $item }}">{{ ucfirst($item) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.weight') }} (%)</label>
                            <input class="form-control" type="number" step="0.01" name="weight" placeholder="0.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.score') }}</label>
                            <input class="form-control" type="number" step="0.01" name="score" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.description') }}</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="{{ __('index.description') }}"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.manager_comment') }}</label>
                            <textarea class="form-control" name="manager_comment" rows="3" placeholder="{{ __('index.manager_comment') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_kpi') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan
