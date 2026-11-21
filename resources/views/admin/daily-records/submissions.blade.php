@extends('layouts.master')
@section('title', 'Daily submissions')
@section('main-content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <h1 class="h4">Daily submissions</h1>
    <a class="btn btn-outline-secondary" href="{{ route('admin.daily-records.index') }}">Task &amp; Report Center</a>
</div>
<form method="get" action="{{ route('admin.daily-records.submissions') }}" class="card mb-3"><div class="card-body row g-2">
    <label class="col-md-3">Status<select class="form-select" name="status">
        @foreach (['pending'=>'Pending review', 'under_review'=>'Under review', 'returned'=>'Returned', 'approved'=>'Approved', 'unsubmitted'=>'Unsubmitted summaries'] as $key=>$label)
            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
        @endforeach
    </select></label>
    <label class="col-md-3">Report date (ICT)<input class="form-control" type="date" name="date" value="{{ $filters['date'] ?? ($status === 'unsubmitted' ? $day : '') }}"></label>
    <label class="col-md-3">Employee name<input class="form-control" name="search" maxlength="100" value="{{ $filters['search'] ?? '' }}"></label>
    <label class="col-md-3">Reviewer ID<input class="form-control" type="number" min="1" name="reviewer_id" value="{{ $filters['reviewer_id'] ?? '' }}"></label>
    <label class="col-md-3">Department<select class="form-select" name="department_id"><option value="">All</option>
        @foreach ($departments as $department)<option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>@endforeach
    </select></label>
    <label class="col-md-3">Location<select class="form-select" name="branch_id"><option value="">All</option>
        @foreach ($locations as $location)<option value="{{ $location->id }}" @selected(($filters['branch_id'] ?? null) == $location->id)>{{ $location->name }}</option>@endforeach
    </select></label>
    <label class="col-md-3">From (ICT)<input class="form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
    <label class="col-md-3">Through (ICT)<input class="form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
    <div><button class="btn btn-primary">Apply filters</button></div>
</div></form>
<div class="card"><div class="card-body">
    @if ($status === 'unsubmitted')<p class="small text-muted">Employees with eligible records on {{ $day }} and no submitted summary for that date.</p>@endif
    <div class="table-responsive"><table class="table mb-3">
        <thead><tr><th>Employee</th><th>Reporting date (ICT)</th><th>Status</th><th>{{ $status === 'unsubmitted' ? 'Eligible records' : 'Version' }}</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse ($rows as $row)
            <tr><td>{{ $row->employee }}</td><td>{{ $row->report_date ?? $day }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$row->status ?? 'Unsubmitted')) }}</td>
                <td>{{ $row->version ?? $row->eligible_records }}</td>
                <td>
                    @if (isset($row->id))
                        <a href="{{ route('admin.daily-records.submission', $row->id) }}">Open summary</a>
                    @else
                        <a href="{{ route('admin.daily-records.index', ['view' => 'daily', 'date' => $day, 'employee_id' => $row->employee_id]) }}">View records</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No matching summaries.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $rows->links() }}
</div></div>
@endsection
