@can('employee.interview.manage')
    <form method="post" action="{{ route('admin.employees.profile.interviews.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>Add Interview</h6>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3"><label class="form-label">Date</label><input class="form-control" type="date" name="interview_date"></div>
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">Stage</label>
                <select class="form-control" name="interview_stage">@foreach(['screening','first_interview','second_interview','technical','manager','final'] as $item)<option value="{{ $item }}">{{ ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select>
            </div>
            <div class="col-lg-3 col-md-6 mb-3"><label class="form-label">Interviewer</label><input class="form-control" name="interviewer_name"></div>
            <div class="col-lg-3 col-md-6 mb-3"><label class="form-label">Position</label><input class="form-control" name="interviewer_position"></div>
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">Source</label>
                <select class="form-control" name="recruitment_source">@foreach(['facebook','tiktok','referral','walk_in','recruitment_agency','other'] as $item)<option value="{{ $item }}">{{ ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">Result</label>
                <select class="form-control" name="result">@foreach(['pending','passed','failed','selected','rejected'] as $item)<option value="{{ $item }}">{{ ucfirst($item) }}</option>@endforeach</select>
            </div>
            <div class="col-lg-3 col-md-6 mb-3"><label class="form-label">Score</label><input class="form-control" type="number" step="0.01" name="score"></div>
            <div class="col-md-12 mb-3"><label class="form-label">Comments</label><textarea class="form-control" name="comments" rows="2"></textarea></div>
        </div>
        <button class="btn btn-primary">Add Interview</button>
    </form>
@endcan
<div class="table-responsive">
    <table class="table table-sm employee-360-table">
        <thead><tr><th>Date</th><th>Stage</th><th>Interviewer</th><th>Source</th><th>Result</th><th>Score</th></tr></thead>
        <tbody>
        @forelse($interviews as $record)
            <tr><td>{{ optional($record->interview_date)->format('Y-m-d') }}</td><td>{{ $record->interview_stage }}</td><td>{{ $record->interviewer_name }}</td><td>{{ $record->recruitment_source }}</td><td>{{ $record->result }}</td><td>{{ $record->score }}</td></tr>
        @empty
            <tr><td colspan="6" class="text-center">No records found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
