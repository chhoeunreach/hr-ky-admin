@can('employee.kpi.manage')
    <form method="post" action="{{ route('admin.employees.profile.responsibilities.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>{{ __('index.add_responsibility') }}</h6>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label">{{ __('index.title') }}</label><input class="form-control" name="title" required></div>
            <div class="col-md-3 mb-3"><label class="form-label">{{ __('index.kpi_target') }}</label><input class="form-control" name="kpi_target"></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.weight') }}</label><input class="form-control" type="number" step="0.01" name="weight"></div>
            <div class="col-md-3 mb-3"><label class="form-label">{{ __('index.status') }}</label><input class="form-control" name="status" value="active"></div>
            <div class="col-md-12 mb-3"><label class="form-label">{{ __('index.description') }}</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div>
        <button class="btn btn-outline-primary">{{ __('index.add_responsibility') }}</button>
    </form>

    <form method="post" action="{{ route('admin.employees.profile.kpis.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>{{ __('index.add_kpi') }}</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">{{ __('index.name') }}</label><input class="form-control" name="name" required></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.target') }}</label><input class="form-control" type="number" step="0.01" name="target_value"></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.actual') }}</label><input class="form-control" type="number" step="0.01" name="actual_value"></div>
            <div class="col-md-2 mb-3">
                <label class="form-label">{{ __('index.unit') }}</label>
                <select class="form-control" name="unit">@foreach(['number','percentage','currency','minutes','hours','days','custom'] as $item)<option value="{{ $item }}">{{ ucfirst($item) }}</option>@endforeach</select>
            </div>
            <div class="col-md-1 mb-3"><label class="form-label">{{ __('index.weight') }}</label><input class="form-control" type="number" step="0.01" name="weight"></div>
            <div class="col-md-2 mb-3"><label class="form-label">{{ __('index.score') }}</label><input class="form-control" type="number" step="0.01" name="score"></div>
            <div class="col-md-6 mb-3"><label class="form-label">{{ __('index.description') }}</label><textarea class="form-control" name="description" rows="2"></textarea></div>
            <div class="col-md-6 mb-3"><label class="form-label">{{ __('index.manager_comment') }}</label><textarea class="form-control" name="manager_comment" rows="2"></textarea></div>
        </div>
        <button class="btn btn-primary">{{ __('index.add_kpi') }}</button>
    </form>
@endcan

<div class="row">
    <div class="col-lg-6">
        <h6>{{ __('index.responsibilities') }}</h6>
        <div class="table-responsive">
            <table class="table table-sm employee-360-table">
                <thead><tr><th>{{ __('index.title') }}</th><th>{{ __('index.target') }}</th><th>{{ __('index.weight') }}</th><th>{{ __('index.status') }}</th></tr></thead>
                <tbody>
                @forelse($responsibilities as $record)
                    <tr><td>{{ $record->title }}</td><td>{{ $record->kpi_target }}</td><td>{{ $record->weight }}</td><td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->status) ? __('index.' . $record->status) : $record->status }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center">{{ __('index.no_records_found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <h6>KPIs</h6>
        <div class="table-responsive">
            <table class="table table-sm employee-360-table">
                <thead><tr><th>{{ __('index.name') }}</th><th>{{ __('index.target') }}</th><th>{{ __('index.actual') }}</th><th>{{ __('index.unit') }}</th><th>{{ __('index.score') }}</th></tr></thead>
                <tbody>
                @forelse($kpis as $record)
                    <tr><td>{{ $record->name }}</td><td>{{ $record->target_value }}</td><td>{{ $record->actual_value }}</td><td>{{ $record->unit }}</td><td>{{ $record->score }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center">{{ __('index.no_records_found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
