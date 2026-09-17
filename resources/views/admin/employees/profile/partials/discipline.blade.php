@php
    $disciplineTypes = ['verbal_warning','written_warning','final_warning','suspension','disciplinary_action','other'];
    $disciplineStatuses = ['draft','active','resolved','cancelled'];
@endphp

@can('employee.discipline.manage')
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.employees.profile.discipline.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>{{ __('index.add_staff_warning') }}</h6>
        <div class="row">
            <div class="col-md-2 mb-3">
                <label class="form-label">{{ __('index.incident_date') }}</label>
                <input class="form-control" type="date" name="incident_date" value="{{ old('incident_date') }}">
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">{{ __('index.type') }}</label>
                <select class="form-control" name="record_type">
                    @foreach($disciplineTypes as $item)
                        <option value="{{ $item }}" @selected(old('record_type') === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">{{ __('index.severity') }}</label>
                <input class="form-control" name="severity" value="{{ old('severity') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">{{ __('index.title') }}</label>
                <input class="form-control" name="title" value="{{ old('title') }}" required>
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">{{ __('index.status') }}</label>
                <select class="form-control" name="status">
                    @foreach($disciplineStatuses as $item)
                        <option value="{{ $item }}" @selected(old('status', 'active') === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst($item) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 mb-3">
                <label class="form-label">{{ __('index.level') }}</label>
                <input class="form-control" name="warning_level" value="{{ old('warning_level') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('index.description') }}</label>
                <textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('index.action_taken') }}</label>
                <textarea class="form-control" name="action_taken" rows="2">{{ old('action_taken') }}</textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">{{ __('index.attachment') }}</label>
                <input class="form-control" type="file" name="attachment">
            </div>
        </div>
        <button class="btn btn-primary">{{ __('index.add_staff_warning') }}</button>
    </form>
@endcan

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
                <td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->record_type) ? __('index.' . $record->record_type) : ucfirst(str_replace('_', ' ', $record->record_type)) }}</td>
                <td>{{ $record->severity ?: 'N/A' }}</td>
                <td>{{ $record->title }}</td>
                <td>{{ $record->warning_level ?: 'N/A' }}</td>
                <td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->status) ? __('index.' . $record->status) : ucfirst($record->status) }}</td>
                <td>{{ $record->description ?: 'N/A' }}</td>
                <td>{{ $record->action_taken ?: 'N/A' }}</td>
                <td>{{ $record->attachment ? __('index.attached') : 'N/A' }}</td>
                @can('employee.discipline.manage')
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-outline-primary btn-xs" data-bs-toggle="modal" data-bs-target="#disciplineEditModal{{ $record->id }}">
                            {{ __('index.edit') }}
                        </button>
                        <form method="post" action="{{ route('admin.employees.profile.discipline.destroy', [$employee->id, $record->id]) }}" class="d-inline" onsubmit="return confirm('{{ __('index.delete_staff_warning_confirm') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-xs">{{ __('index.delete') }}</button>
                        </form>
                    </td>
                @endcan
            </tr>
        @empty
            <tr>
                <td colspan="@can('employee.discipline.manage') 10 @else 9 @endcan" class="text-center">{{ __('index.no_records_found') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@can('employee.discipline.manage')
    @foreach($discipline as $record)
        <div class="modal fade" id="disciplineEditModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form method="post" enctype="multipart/form-data" action="{{ route('admin.employees.profile.discipline.update', [$employee->id, $record->id]) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('index.update_staff_warning') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('index.incident_date') }}</label>
                                <input class="form-control" type="date" name="incident_date" value="{{ optional($record->incident_date)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('index.type') }}</label>
                                <select class="form-control" name="record_type">
                                    @foreach($disciplineTypes as $item)
                                        <option value="{{ $item }}" @selected($record->record_type === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('index.severity') }}</label>
                                <input class="form-control" name="severity" value="{{ $record->severity }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('index.status') }}</label>
                                <select class="form-control" name="status">
                                    @foreach($disciplineStatuses as $item)
                                        <option value="{{ $item }}" @selected($record->status === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst($item) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">{{ __('index.title') }}</label>
                                <input class="form-control" name="title" value="{{ $record->title }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('index.level') }}</label>
                                <input class="form-control" name="warning_level" value="{{ $record->warning_level }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('index.description') }}</label>
                                <textarea class="form-control" name="description" rows="4">{{ $record->description }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('index.action_taken') }}</label>
                                <textarea class="form-control" name="action_taken" rows="4">{{ $record->action_taken }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('index.attachment') }}</label>
                                <input class="form-control" type="file" name="attachment">
                                @if($record->attachment)
                                    <small class="text-muted d-block mt-1">Current file attached. Upload a new file to replace it.</small>
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
