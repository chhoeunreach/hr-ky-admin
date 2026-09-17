@can('employee.training.manage')
    <form method="post" action="{{ route('admin.employees.profile.training.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>{{ __('index.add_training') }}</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">{{ __('index.date') }}</label><input class="form-control" type="date" name="training_date"></div>
            <div class="col-md-3 mb-3"><label class="form-label">{{ __('index.title') }}</label><input class="form-control" name="training_title" required></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.type') }}</label><select class="form-control" name="training_type">@foreach(['internal','external','online','on_job_training','orientation'] as $item)<option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.trainer') }}</label><input class="form-control" name="trainer_name"></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.score') }}</label><input class="form-control" type="number" step="0.01" name="score"></div>
            <div class="col-md-6 mb-3"><label class="form-label">{{ __('index.objective') }}</label><textarea class="form-control" name="objective" rows="2"></textarea></div>
            <div class="col-md-6 mb-3"><label class="form-label">{{ __('index.note') }}</label><textarea class="form-control" name="note" rows="2"></textarea></div>
        </div>
        <button class="btn btn-primary">{{ __('index.add_training') }}</button>
    </form>
@endcan
<div class="table-responsive"><table class="table table-sm employee-360-table"><thead><tr><th>{{ __('index.date') }}</th><th>{{ __('index.title') }}</th><th>{{ __('index.type') }}</th><th>{{ __('index.trainer') }}</th><th>{{ __('index.score') }}</th><th>{{ __('index.result') }}</th></tr></thead><tbody>
@forelse($training as $record)<tr><td>{{ optional($record->training_date)->format('Y-m-d') }}</td><td>{{ $record->training_title }}</td><td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->training_type) ? __('index.' . $record->training_type) : ($record->training_type ? ucfirst(str_replace('_', ' ', $record->training_type)) : '') }}</td><td>{{ $record->trainer_name }}</td><td>{{ $record->score }}</td><td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->result) ? __('index.' . $record->result) : $record->result }}</td></tr>@empty<tr><td colspan="6" class="text-center">{{ __('index.no_records_found') }}</td></tr>@endforelse
</tbody></table></div>

