@can('employee.goal.manage')
    <form method="post" action="{{ route('admin.employees.profile.goals.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>{{ __('index.add_goal') }}</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">{{ __('index.title') }}</label><input class="form-control" name="title" required></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.target') }}</label><input class="form-control" name="target"></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.period_start') }}</label><input class="form-control" type="date" name="start_date"></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.due') }}</label><input class="form-control" type="date" name="due_date"></div>
            <div class="col-md-1 mb-3"><label class="form-label">{{ __('index.progress') }}</label><input class="form-control" type="number" min="0" max="100" name="progress" value="0"></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.status') }}</label><select class="form-control" name="status">@foreach(['not_started','in_progress','completed','overdue','cancelled'] as $item)<option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select></div>
            <div class="col-md-12 mb-3"><label class="form-label">{{ __('index.description') }}</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div>
        <button class="btn btn-primary">{{ __('index.add_goal') }}</button>
    </form>

    <form method="post" action="{{ route('admin.employees.profile.improvement-plans.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>{{ __('index.add_improvement_plan') }}</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">{{ __('index.period_start') }}</label><input class="form-control" type="date" name="start_date"></div>
            <div class="col-md-3 mb-3"><label class="form-label">{{ __('index.period_end') }}</label><input class="form-control" type="date" name="end_date"></div>
            <div class="col-md-3 mb-3"><label class="form-label">{{ __('index.status') }}</label><select class="form-control" name="status">@foreach(['draft','active','completed','failed','cancelled'] as $item)<option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst($item) }}</option>@endforeach</select></div>
            <div class="col-md-12 mb-3"><label class="form-label">{{ __('index.reason') }}</label><textarea class="form-control" name="reason" rows="2"></textarea></div>
            <div class="col-md-6 mb-3"><label class="form-label">{{ __('index.expectations') }}</label><textarea class="form-control" name="expectations" rows="2"></textarea></div>
            <div class="col-md-6 mb-3"><label class="form-label">{{ __('index.support_required') }}</label><textarea class="form-control" name="support_required" rows="2"></textarea></div>
        </div>
        <button class="btn btn-outline-primary">{{ __('index.add_plan') }}</button>
    </form>
@endcan
<h6>{{ __('index.goals') }}</h6>
<div class="table-responsive mb-4"><table class="table table-sm employee-360-table"><thead><tr><th>{{ __('index.title') }}</th><th>{{ __('index.target') }}</th><th>{{ __('index.due') }}</th><th>{{ __('index.progress') }}</th><th>{{ __('index.status') }}</th></tr></thead><tbody>
@forelse($goals as $record)<tr><td>{{ $record->title }}</td><td>{{ $record->target }}</td><td>{{ optional($record->due_date)->format('Y-m-d') }}</td><td>{{ $record->progress }}%</td><td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->status) ? __('index.' . $record->status) : $record->status }}</td></tr>@empty<tr><td colspan="5" class="text-center">{{ __('index.no_records_found') }}</td></tr>@endforelse
</tbody></table></div>
<h6>{{ __('index.improvement_plans') }}</h6>
<div class="table-responsive"><table class="table table-sm employee-360-table"><thead><tr><th>{{ __('index.period_start') }}</th><th>{{ __('index.period_end') }}</th><th>{{ __('index.status') }}</th><th>{{ __('index.reason') }}</th><th>{{ __('index.progress_notes') }}</th></tr></thead><tbody>
@forelse($improvementPlans as $record)<tr><td>{{ optional($record->start_date)->format('Y-m-d') }}</td><td>{{ optional($record->end_date)->format('Y-m-d') }}</td><td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->status) ? __('index.' . $record->status) : $record->status }}</td><td>{{ $record->reason }}</td><td>{{ $record->progress_notes }}</td></tr>@empty<tr><td colspan="5" class="text-center">{{ __('index.no_records_found') }}</td></tr>@endforelse
</tbody></table></div>
