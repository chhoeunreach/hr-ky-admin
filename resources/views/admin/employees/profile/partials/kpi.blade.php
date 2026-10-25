@php
    $departmentDefaults = app(\App\Services\DepartmentPerformanceDefaults::class)->forEmployee($employee);
@endphp
<div class="card mb-3">
    <div class="card-body">
        <h6>{{ __('index.department_performance_defaults') }} · {{ $departmentDefaults['label'] }}</h6>
        <p class="small text-muted">{{ __('index.department_defaults_help') }}</p>
        @can('employee.kpi.manage')
            <form method="post" action="{{ route('admin.employees.profile.department-defaults.apply', $employee->id) }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">{{ __('index.apply_employee_defaults') }}</button>
            </form>
        @endcan
        @include('admin.department.partials.performance-defaults', ['defaults' => $departmentDefaults])
    </div>
</div>
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
                                @can('employee.kpi.manage')<th>{{ __('index.action') }}</th>@endcan
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($responsibilities as $record)
                            <tr>
                                <td class="fw-semibold">{{ $record->title }}
                                    @if($record->description)
                                        <details class="small fw-normal mt-1"><summary>{{ __('index.description') }}</summary><div style="white-space: pre-line; max-width: 380px">{{ $record->description }}</div></details>
                                    @endif
                                </td>
                                <td>{{ $record->kpi_target ?: '—' }}</td>
                                <td>{{ $record->weight !== null ? $record->weight . '%' : '—' }}</td>
                                <td>
                                    <span class="badge {{ $record->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ \Illuminate\Support\Facades\Lang::has('index.' . $record->status) ? __('index.' . $record->status) : ucfirst($record->status) }}
                                    </span>
                                </td>
                                @include('admin.employees.profile.partials.performance-record-actions', ['kind' => 'responsibility'])
                            </tr>
                        @empty
                            <tr><td colspan="{{ \Illuminate\Support\Facades\Gate::allows('employee.kpi.manage') ? 5 : 4 }}" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
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
                <h6 class="mb-0 fw-bold"><i data-feather="activity" class="me-2 text-secondary"></i>{{ __('index.department_defaults_kpis') }}</h6>
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
                                <th>{{ __('index.weight') }}</th>
                                <th>{{ __('index.score') }}</th>
                                @can('employee.kpi.manage')<th>{{ __('index.action') }}</th>@endcan
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($kpis as $record)
                            <tr>
                                <td class="fw-semibold">{{ $record->name }}
                                    @if($record->description)
                                        <details class="small fw-normal mt-1"><summary>{{ __('index.department_defaults_measurement') }}</summary><div style="white-space: pre-line; max-width: 420px">{{ $record->description }}</div></details>
                                    @endif
                                </td>
                                <td>{{ $record->target_value !== null ? $record->target_value : '—' }}</td>
                                <td>{{ $record->actual_value !== null ? $record->actual_value : '—' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ \Illuminate\Support\Facades\Lang::has('index.performance_unit_'.$record->unit) ? __('index.performance_unit_'.$record->unit) : $record->unit }}</span></td>
                                <td>{{ $record->weight !== null ? $record->weight.'%' : '—' }}</td>
                                <td class="fw-bold text-primary">{{ $record->score !== null ? $record->score : '—' }}</td>
                                @include('admin.employees.profile.partials.performance-record-actions', ['kind' => 'kpi'])
                            </tr>
                        @empty
                            <tr><td colspan="{{ \Illuminate\Support\Facades\Gate::allows('employee.kpi.manage') ? 7 : 6 }}" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
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
            <form method="post" action="{{ route('admin.employees.profile.responsibilities.store', $employee->id) }}" class="modal-content"
                  data-performance-form="responsibility"
                  data-store-url="{{ route('admin.employees.profile.responsibilities.store', $employee->id) }}"
                  data-update-url="{{ route('admin.employees.profile.responsibilities.update', ['employee' => $employee->id, 'responsibility' => '__record__']) }}">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" name="performance_record_type" value="responsibility">
                <input type="hidden" name="performance_record_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addResponsibilityModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_responsibility') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if(old('performance_record_type') === 'responsibility' && $errors->any())
                        <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
                    @endif
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
                            <input class="form-control" type="number" step="0.01" name="weight" min="0" max="100" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                            <select class="form-control" name="status">
                                <option value="active">{{ __('index.active') }}</option>
                                <option value="inactive">{{ __('index.inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('index.start_date') }}</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('index.end_date') }}</label>
                            <input type="date" name="end_date" class="form-control">
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
            <form method="post" action="{{ route('admin.employees.profile.kpis.store', $employee->id) }}" class="modal-content"
                  data-performance-form="kpi"
                  data-store-url="{{ route('admin.employees.profile.kpis.store', $employee->id) }}"
                  data-update-url="{{ route('admin.employees.profile.kpis.update', ['employee' => $employee->id, 'kpi' => '__record__']) }}">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" name="performance_record_type" value="kpi">
                <input type="hidden" name="performance_record_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addKpiModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_kpi') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if(old('performance_record_type') === 'kpi' && $errors->any())
                        <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
                    @endif
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
                                    <option value="{{ $item }}">{{ __('index.performance_unit_'.$item) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.weight') }} (%)</label>
                            <input class="form-control" type="number" step="0.01" name="weight" min="0" max="100" placeholder="0.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.score') }}</label>
                            <input class="form-control" type="number" step="0.01" name="score" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('index.performance_achievement') }} (%)</label>
                            <input class="form-control" type="number" step="0.01" min="0" name="achievement_percentage">
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

@can('employee.kpi.manage')
    @include('admin.employees.profile.partials.performance-form-scripts')
@endcan
