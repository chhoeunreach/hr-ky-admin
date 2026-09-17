@can('employee.interview.manage')
    <form method="post" action="{{ route('admin.employees.profile.interviews.store', $employee->id) }}" class="employee-360-section">
        @csrf
        <h6>{{ __('index.add_interview') }}</h6>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3"><label class="form-label">{{ __('index.date') }}</label><input class="form-control" type="date" name="interview_date"></div>
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">{{ __('index.stage') }}</label>
                <select class="form-control" name="interview_stage">@foreach(['screening','first_interview','second_interview','technical','manager','final'] as $item)<option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select>
            </div>
            <div class="col-lg-3 col-md-6 mb-3"><label class="form-label">{{ __('index.interviewer') }}</label><input class="form-control" name="interviewer_name"></div>
            <div class="col-lg-3 col-md-6 mb-3"><label class="form-label">{{ __('index.position') }}</label><input class="form-control" name="interviewer_position"></div>
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">{{ __('index.source') }}</label>
                <select class="form-control" name="recruitment_source">@foreach(['facebook','tiktok','referral','walk_in','recruitment_agency','other'] as $item)<option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">{{ __('index.result') }}</label>
                <select class="form-control" name="result">@foreach(['pending','passed','failed','selected','rejected'] as $item)<option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst($item) }}</option>@endforeach</select>
            </div>
            <div class="col-lg-3 col-md-6 mb-3"><label class="form-label">{{ __('index.score') }}</label><input class="form-control" type="number" step="0.01" name="score"></div>
            <div class="col-md-12 mb-3"><label class="form-label">{{ __('index.comments') }}</label><textarea class="form-control" name="comments" rows="2"></textarea></div>
        </div>
        <button class="btn btn-primary">{{ __('index.add_interview') }}</button>
    </form>
@endcan
<div class="table-responsive">
    <table class="table table-sm employee-360-table">
        <thead><tr><th>{{ __('index.date') }}</th><th>{{ __('index.stage') }}</th><th>{{ __('index.interviewer') }}</th><th>{{ __('index.source') }}</th><th>{{ __('index.result') }}</th><th>{{ __('index.score') }}</th></tr></thead>
        <tbody>
        @forelse($interviews as $record)
            <tr><td>{{ optional($record->interview_date)->format('Y-m-d') }}</td><td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->interview_stage) ? __('index.' . $record->interview_stage) : $record->interview_stage }}</td><td>{{ $record->interviewer_name }}</td><td>{{ $record->recruitment_source }}</td><td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->result) ? __('index.' . $record->result) : $record->result }}</td><td>{{ $record->score }}</td></tr>
        @empty
            <tr><td colspan="6" class="text-center">{{ __('index.no_records_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
