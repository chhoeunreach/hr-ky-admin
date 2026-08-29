@can('employee.kpi.manage')
    <form method="post" action="{{ route('admin.employees.profile.responsibilities.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add Responsibility</h6>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
            <div class="col-md-3 mb-3"><label class="form-label">KPI Target</label><input class="form-control" name="kpi_target"></div>
            <div class="col-md-2 mb-3"><label class="form-label">Weight</label><input class="form-control" type="number" step="0.01" name="weight"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Status</label><input class="form-control" name="status" value="active"></div>
            <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div>
        <button class="btn btn-outline-primary">Add Responsibility</button>
    </form>

    <form method="post" action="{{ route('admin.employees.profile.kpis.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add KPI</h6>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
            <div class="col-md-2 mb-3"><label class="form-label">Target</label><input class="form-control" type="number" step="0.01" name="target_value"></div>
            <div class="col-md-2 mb-3"><label class="form-label">Actual</label><input class="form-control" type="number" step="0.01" name="actual_value"></div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Unit</label>
                <select class="form-control" name="unit">@foreach(['number','percentage','currency','minutes','hours','days','custom'] as $item)<option value="{{ $item }}">{{ ucfirst($item) }}</option>@endforeach</select>
            </div>
            <div class="col-md-1 mb-3"><label class="form-label">Weight</label><input class="form-control" type="number" step="0.01" name="weight"></div>
            <div class="col-md-2 mb-3"><label class="form-label">Score</label><input class="form-control" type="number" step="0.01" name="score"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
            <div class="col-md-6 mb-3"><label class="form-label">Manager Comment</label><textarea class="form-control" name="manager_comment" rows="2"></textarea></div>
        </div>
        <button class="btn btn-primary">Add KPI</button>
    </form>
@endcan

<div class="row">
    <div class="col-lg-6">
        <h6>Responsibilities</h6>
        <div class="table-responsive">
            <table class="table table-sm employee-360-table">
                <thead><tr><th>Title</th><th>Target</th><th>Weight</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($responsibilities as $record)
                    <tr><td>{{ $record->title }}</td><td>{{ $record->kpi_target }}</td><td>{{ $record->weight }}</td><td>{{ $record->status }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center">No records found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <h6>KPIs</h6>
        <div class="table-responsive">
            <table class="table table-sm employee-360-table">
                <thead><tr><th>Name</th><th>Target</th><th>Actual</th><th>Unit</th><th>Score</th></tr></thead>
                <tbody>
                @forelse($kpis as $record)
                    <tr><td>{{ $record->name }}</td><td>{{ $record->target_value }}</td><td>{{ $record->actual_value }}</td><td>{{ $record->unit }}</td><td>{{ $record->score }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center">No records found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
