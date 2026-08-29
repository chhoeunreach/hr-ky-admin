@can('employee.discipline.manage')
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.employees.profile.discipline.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add Discipline Record</h6>
        <div class="row">
            <div class="col-md-2 mb-3"><label class="form-label">Incident Date</label><input class="form-control" type="date" name="incident_date"></div>
            <div class="col-md-2 mb-3"><label class="form-label">Type</label><select class="form-control" name="record_type">@foreach(['verbal_warning','written_warning','final_warning','suspension','disciplinary_action','other'] as $item)<option value="{{ $item }}">{{ ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-3"><label class="form-label">Severity</label><input class="form-control" name="severity"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
            <div class="col-md-2 mb-3"><label class="form-label">Status</label><select class="form-control" name="status">@foreach(['draft','active','resolved','cancelled'] as $item)<option value="{{ $item }}">{{ ucfirst($item) }}</option>@endforeach</select></div>
            <div class="col-md-1 mb-3"><label class="form-label">Level</label><input class="form-control" name="warning_level"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
            <div class="col-md-6 mb-3"><label class="form-label">Action Taken</label><textarea class="form-control" name="action_taken" rows="2"></textarea></div>
            <div class="col-md-4 mb-3"><label class="form-label">Attachment</label><input class="form-control" type="file" name="attachment"></div>
        </div>
        <button class="btn btn-primary">Add Discipline</button>
    </form>
@endcan
<div class="table-responsive"><table class="table table-sm employee-360-table"><thead><tr><th>Date</th><th>Type</th><th>Severity</th><th>Title</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($discipline as $record)<tr><td>{{ optional($record->incident_date)->format('Y-m-d') }}</td><td>{{ $record->record_type }}</td><td>{{ $record->severity }}</td><td>{{ $record->title }}</td><td>{{ $record->status }}</td><td>{{ $record->action_taken }}</td></tr>@empty<tr><td colspan="6" class="text-center">No records found</td></tr>@endforelse
</tbody></table></div>
