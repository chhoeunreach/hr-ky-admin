@can('employee.training.manage')
    <form method="post" action="{{ route('admin.employees.profile.training.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add Training</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">Date</label><input class="form-control" type="date" name="training_date"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Title</label><input class="form-control" name="training_title" required></div>
            <div class="col-md-2 mb-3"><label class="form-label">Type</label><select class="form-control" name="training_type">@foreach(['internal','external','online','on_job_training','orientation'] as $item)<option value="{{ $item }}">{{ ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-3"><label class="form-label">Trainer</label><input class="form-control" name="trainer_name"></div>
            <div class="col-md-2 mb-3"><label class="form-label">Score</label><input class="form-control" type="number" step="0.01" name="score"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Objective</label><textarea class="form-control" name="objective" rows="2"></textarea></div>
            <div class="col-md-6 mb-3"><label class="form-label">Note</label><textarea class="form-control" name="note" rows="2"></textarea></div>
        </div>
        <button class="btn btn-primary">Add Training</button>
    </form>
@endcan
<div class="table-responsive"><table class="table table-sm employee-360-table"><thead><tr><th>Date</th><th>Title</th><th>Type</th><th>Trainer</th><th>Score</th><th>Result</th></tr></thead><tbody>
@forelse($training as $record)<tr><td>{{ optional($record->training_date)->format('Y-m-d') }}</td><td>{{ $record->training_title }}</td><td>{{ $record->training_type }}</td><td>{{ $record->trainer_name }}</td><td>{{ $record->score }}</td><td>{{ $record->result }}</td></tr>@empty<tr><td colspan="6" class="text-center">No records found</td></tr>@endforelse
</tbody></table></div>
