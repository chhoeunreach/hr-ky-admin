<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i data-feather="message-square" class="me-2 text-primary"></i>{{ __('index.interviews') ?? 'Interviews' }}</h6>
    @can('employee.interview.manage')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInterviewModal">
            <i data-feather="plus" class="me-1"></i> {{ __('index.add_interview') }}
        </button>
    @endcan
</div>

<div class="table-responsive">
    <table class="table table-sm employee-360-table align-middle">
        <thead>
            <tr>
                <th>{{ __('index.date') }}</th>
                <th>{{ __('index.stage') }}</th>
                <th>{{ __('index.interviewer') }}</th>
                <th>{{ __('index.source') }}</th>
                <th>{{ __('index.result') }}</th>
                <th>{{ __('index.score') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($interviews as $record)
            <tr>
                <td>{{ optional($record->interview_date)->format('Y-m-d') }}</td>
                <td><span class="badge bg-light text-dark border">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->interview_stage) ? __('index.' . $record->interview_stage) : ucfirst(str_replace('_', ' ', $record->interview_stage)) }}</span></td>
                <td>{{ $record->interviewer_name ?: '—' }}</td>
                <td>{{ $record->recruitment_source ? ucfirst(str_replace('_', ' ', $record->recruitment_source)) : '—' }}</td>
                <td>
                    @php
                        $resColors = ['passed' => 'bg-success', 'selected' => 'bg-primary', 'pending' => 'bg-warning text-dark', 'failed' => 'bg-danger', 'rejected' => 'bg-danger'];
                        $resClass = $resColors[$record->result] ?? 'bg-secondary';
                    @endphp
                    <span class="badge {{ $resClass }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->result) ? __('index.' . $record->result) : ucfirst($record->result) }}</span>
                </td>
                <td class="fw-semibold">{{ $record->score !== null ? $record->score : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@can('employee.interview.manage')
    <div class="modal fade" id="addInterviewModal" tabindex="-1" aria-labelledby="addInterviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" action="{{ route('admin.employees.profile.interviews.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addInterviewModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_interview') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.date') }}</label>
                            <input class="form-control" type="date" name="interview_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.stage') }}</label>
                            <select class="form-control" name="interview_stage">
                                @foreach(['screening','first_interview','second_interview','technical','manager','final'] as $item)
                                    <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.interviewer') }}</label>
                            <input class="form-control" name="interviewer_name" placeholder="{{ __('index.interviewer') }}">
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.position') }}</label>
                            <input class="form-control" name="interviewer_position" placeholder="{{ __('index.position') }}">
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.source') }}</label>
                            <select class="form-control" name="recruitment_source">
                                @foreach(['facebook','tiktok','referral','walk_in','recruitment_agency','other'] as $item)
                                    <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.result') }}</label>
                            <select class="form-control" name="result">
                                @foreach(['pending','passed','failed','selected','rejected'] as $item)
                                    <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst($item) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.score') }}</label>
                            <input class="form-control" type="number" step="0.01" name="score" placeholder="0.00">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.comments') }}</label>
                            <textarea class="form-control" name="comments" rows="3" placeholder="{{ __('index.comments') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_interview') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan
