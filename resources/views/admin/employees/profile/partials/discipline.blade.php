@php
    $disciplineTypes = ['verbal_warning','written_warning','final_warning','suspension','disciplinary_action','other'];
    $disciplineStatuses = ['draft','active','resolved','cancelled'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i data-feather="alert-triangle" class="me-2 text-warning"></i>{{ __('index.staff_warnings') ?? 'Staff Warnings & Discipline' }}</h6>
    @can('employee.discipline.manage')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffWarningModal">
            <i data-feather="plus" class="me-1"></i> {{ __('index.add_staff_warning') }}
        </button>
    @endcan
</div>

<div class="table-responsive">
    <table class="table table-sm employee-360-table align-middle">
        <thead>
        <tr>
            <th>{{ __('index.date') }}</th>
            <th>{{ __('index.type') }}</th>
            <th>{{ __('index.severity') }}</th>
            <th>{{ __('index.title') }}</th>
            <th>{{ __('index.level') }}</th>
            <th>{{ __('index.status') }}</th>
            <th>{{ __('index.description') }}</th>
            <th>{{ __('index.action_taken') }}</th>
            <th>{{ __('index.attachment') }}</th>
            @can('employee.discipline.manage')
                <th class="text-end">{{ __('index.manage') }}</th>
            @endcan
        </tr>
        </thead>
        <tbody>
        @forelse($discipline as $record)
            <tr>
                <td>{{ optional($record->incident_date)->format('Y-m-d') }}</td>
                <td><span class="badge bg-light text-dark border">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->record_type) ? __('index.' . $record->record_type) : ucfirst(str_replace('_', ' ', $record->record_type)) }}</span></td>
                <td>{{ $record->severity ?: '—' }}</td>
                <td class="fw-semibold">{{ $record->title }}</td>
                <td>{{ $record->warning_level ?: '—' }}</td>
                <td>
                    @php
                        $discStatusColors = ['active' => 'bg-danger', 'resolved' => 'bg-success', 'draft' => 'bg-secondary', 'cancelled' => 'bg-dark'];
                        $dsClass = $discStatusColors[$record->status] ?? 'bg-secondary';
                    @endphp
                    <span class="badge {{ $dsClass }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->status) ? __('index.' . $record->status) : ucfirst($record->status) }}</span>
                </td>
                <td>{{ $record->description ?: '—' }}</td>
                <td>{{ $record->action_taken ?: '—' }}</td>
                <td>
                    @if($record->attachment)
                        <a href="{{ route('admin.employees.profile.discipline.attachment', [$employee->id, $record->id]) }}" target="_blank" class="btn btn-outline-primary btn-xs text-nowrap">
                            <i data-feather="eye" class="feather-xs"></i> {{ __('index.view') ?? 'View' }}
                        </a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
                @can('employee.discipline.manage')
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-outline-primary btn-xs" data-bs-toggle="modal" data-bs-target="#disciplineEditModal{{ $record->id }}">
                            <i data-feather="edit-2" class="feather-xs"></i> {{ __('index.edit') }}
                        </button>
                        <form method="post" action="{{ route('admin.employees.profile.discipline.destroy', [$employee->id, $record->id]) }}" class="d-inline" onsubmit="return confirm('{{ __('index.delete_staff_warning_confirm') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-xs">
                                <i data-feather="trash-2" class="feather-xs"></i> {{ __('index.delete') }}
                            </button>
                        </form>
                    </td>
                @endcan
            </tr>
        @empty
            <tr>
                <td colspan="@can('employee.discipline.manage') 10 @else 9 @endcan" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@can('employee.discipline.manage')
    {{-- Add Staff Warning Modal --}}
    <div class="modal fade" id="addStaffWarningModal" tabindex="-1" aria-labelledby="addStaffWarningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" enctype="multipart/form-data" action="{{ route('admin.employees.profile.discipline.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addStaffWarningModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_staff_warning') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.incident_date') }}</label>
                            <input class="form-control" type="date" name="incident_date" value="{{ old('incident_date', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.type') }}</label>
                            <select class="form-control" name="record_type">
                                @foreach($disciplineTypes as $item)
                                    <option value="{{ $item }}" @selected(old('record_type') === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.severity') }}</label>
                            <input class="form-control" name="severity" value="{{ old('severity') }}" placeholder="{{ __('index.severity') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                            <select class="form-control" name="status">
                                @foreach($disciplineStatuses as $item)
                                    <option value="{{ $item }}" @selected(old('status', 'active') === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst($item) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.title') }}</label>
                            <input class="form-control" name="title" value="{{ old('title') }}" placeholder="{{ __('index.title') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.level') }}</label>
                            <input class="form-control" name="warning_level" value="{{ old('warning_level') }}" placeholder="{{ __('index.level') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.description') }}</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="{{ __('index.description') }}">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.action_taken') }}</label>
                            <textarea class="form-control" name="action_taken" rows="3" placeholder="{{ __('index.action_taken') }}">{{ old('action_taken') }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.attachment') }}</label>
                            <input class="form-control" type="file" name="attachment">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_staff_warning') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Staff Warning Modals --}}
    @foreach($discipline as $record)
        <div class="modal fade" id="disciplineEditModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form method="post" enctype="multipart/form-data" action="{{ route('admin.employees.profile.discipline.update', [$employee->id, $record->id]) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ __('index.update_staff_warning') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.incident_date') }}</label>
                                <input class="form-control" type="date" name="incident_date" value="{{ optional($record->incident_date)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.type') }}</label>
                                <select class="form-control" name="record_type">
                                    @foreach($disciplineTypes as $item)
                                        <option value="{{ $item }}" @selected($record->record_type === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.severity') }}</label>
                                <input class="form-control" name="severity" value="{{ $record->severity }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                                <select class="form-control" name="status">
                                    @foreach($disciplineStatuses as $item)
                                        <option value="{{ $item }}" @selected($record->status === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst($item) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.title') }}</label>
                                <input class="form-control" name="title" value="{{ $record->title }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.level') }}</label>
                                <input class="form-control" name="warning_level" value="{{ $record->warning_level }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.description') }}</label>
                                <textarea class="form-control" name="description" rows="3">{{ $record->description }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.action_taken') }}</label>
                                <textarea class="form-control" name="action_taken" rows="3">{{ $record->action_taken }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">{{ __('index.attachment') }}</label>
                                <input class="form-control" type="file" name="attachment">
                                @if($record->attachment)
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <small class="text-muted">Current file attached.</small>
                                        <a href="{{ route('admin.employees.profile.discipline.attachment', [$employee->id, $record->id]) }}" target="_blank" class="btn btn-outline-info btn-xs">
                                            <i data-feather="eye" class="feather-xs"></i> View Current File
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.update_staff_warning') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endcan
