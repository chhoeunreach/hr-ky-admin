@can('employee.goal.manage')
    <form method="post" action="{{ route('admin.employees.profile.goals.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add Goal</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
            <div class="col-md-2 mb-3"><label class="form-label">Target</label><input class="form-control" name="target"></div>
            <div class="col-md-2 mb-3"><label class="form-label">Start</label><input class="form-control" type="date" name="start_date"></div>
            <div class="col-md-2 mb-3"><label class="form-label">Due</label><input class="form-control" type="date" name="due_date"></div>
            <div class="col-md-1 mb-3"><label class="form-label">Progress</label><input class="form-control" type="number" min="0" max="100" name="progress" value="0"></div>
            <div class="col-md-2 mb-3"><label class="form-label">Status</label><select class="form-control" name="status">@foreach(['not_started','in_progress','completed','overdue','cancelled'] as $item)<option value="{{ $item }}">{{ ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select></div>
            <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div>
        <button class="btn btn-primary">Add Goal</button>
    </form>

    <form method="post" action="{{ route('admin.employees.profile.improvement-plans.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add Improvement Plan</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">Start</label><input class="form-control" type="date" name="start_date"></div>
            <div class="col-md-3 mb-3"><label class="form-label">End</label><input class="form-control" type="date" name="end_date"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Status</label><select class="form-control" name="status">@foreach(['draft','active','completed','failed','cancelled'] as $item)<option value="{{ $item }}">{{ ucfirst($item) }}</option>@endforeach</select></div>
            <div class="col-md-12 mb-3"><label class="form-label">Reason</label><textarea class="form-control" name="reason" rows="2"></textarea></div>
            <div class="col-md-6 mb-3"><label class="form-label">Expectations</label><textarea class="form-control" name="expectations" rows="2"></textarea></div>
            <div class="col-md-6 mb-3"><label class="form-label">Support Required</label><textarea class="form-control" name="support_required" rows="2"></textarea></div>
        </div>
        <button class="btn btn-outline-primary">Add Plan</button>
    </form>
@endcan
<h6>Goals</h6>
<div class="table-responsive mb-4"><table class="table table-sm employee-360-table"><thead><tr><th>Title</th><th>Target</th><th>Due</th><th>Progress</th><th>Status</th></tr></thead><tbody>
@forelse($goals as $record)<tr><td>{{ $record->title }}</td><td>{{ $record->target }}</td><td>{{ optional($record->due_date)->format('Y-m-d') }}</td><td>{{ $record->progress }}%</td><td>{{ $record->status }}</td></tr>@empty<tr><td colspan="5" class="text-center">No records found</td></tr>@endforelse
</tbody></table></div>
<h6>Improvement Plans</h6>
<div class="table-responsive"><table class="table table-sm employee-360-table"><thead><tr><th>Start</th><th>End</th><th>Status</th><th>Reason</th><th>Progress Notes</th></tr></thead><tbody>
@forelse($improvementPlans as $record)<tr><td>{{ optional($record->start_date)->format('Y-m-d') }}</td><td>{{ optional($record->end_date)->format('Y-m-d') }}</td><td>{{ $record->status }}</td><td>{{ $record->reason }}</td><td>{{ $record->progress_notes }}</td></tr>@empty<tr><td colspan="5" class="text-center">No records found</td></tr>@endforelse
</tbody></table></div>
