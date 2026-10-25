<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i data-feather="award" class="me-2 text-primary"></i>{{ __('index.performance_reviews') ?? 'Performance Reviews' }}</h6>
    @can('employee.performance.create')
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPerformanceReviewModal">
            <i data-feather="plus" class="me-1"></i> {{ __('index.add_performance_review') }}
        </button>
    @endcan
</div>

<div class="table-responsive">
    <table class="table table-sm employee-360-table align-middle">
        <thead>
            <tr>
                <th>{{ __('index.date') }}</th>
                <th>{{ __('index.type') }}</th>
                <th>{{ __('index.period') }}</th>
                <th>{{ __('index.score') }}</th>
                <th>{{ __('index.grade') }}</th>
                <th>{{ __('index.status') }}</th>
                @can('employee.salary.history.manage')
                    <th>{{ __('index.salary_adjustment') }}</th>
                @endcan
            </tr>
        </thead>
        <tbody>
        @forelse($reviews as $review)
            <tr>
                <td>{{ optional($review->review_date)->format('Y-m-d') }}</td>
                <td><span class="badge bg-light text-dark border">{{ \Illuminate\Support\Facades\Lang::has('index.' . $review->review_type) ? __('index.' . $review->review_type) : ucfirst(str_replace('_', ' ', $review->review_type)) }}</span></td>
                <td>{{ optional($review->period_start)->format('Y-m-d') }} - {{ optional($review->period_end)->format('Y-m-d') }}</td>
                <td class="fw-bold text-primary">{{ $review->total_score }}</td>
                <td><span class="badge bg-info text-dark">{{ $review->grade ?: '—' }}</span></td>
                <td><span class="badge bg-secondary">{{ \Illuminate\Support\Facades\Lang::has('index.' . $review->status) ? __('index.' . $review->status) : ucfirst($review->status) }}</span></td>
                @can('employee.salary.history.manage')
                    <td>
                        @if($review->status === 'completed')
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'salary', 'salary_review' => $review->id]) }}">{{ __('index.request_salary_increase') }}</a>
                        @endif
                    </td>
                @endcan
            </tr>
        @empty
            <tr><td colspan="{{ \Illuminate\Support\Facades\Gate::allows('employee.salary.history.manage') ? 7 : 6 }}" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@can('employee.performance.create')
    @php
        $selectedReviewType = old('review_type', request('review_type', 'quarterly'));
        $selectedReviewStatus = old('status', request()->boolean('review_create') ? 'completed' : 'draft');
        $reviewCriteria = old('criteria', array_column($defaultItems, 0)) ?: [''];
    @endphp
    <div class="modal fade" id="addPerformanceReviewModal" tabindex="-1" aria-labelledby="addPerformanceReviewModalLabel" aria-hidden="true" data-reopen="{{ old('review_form') ? '1' : '0' }}">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form id="reviewCreateForm" method="post" action="{{ route('admin.employees.profile.reviews.store', $employee->id) }}" class="modal-content">
                @csrf
                <input type="hidden" name="review_form" value="1">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addPerformanceReviewModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_performance_review') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                </div>
                <div class="modal-body p-4">
                    @isset($evaluationDefaults)
                        <p class="small text-muted">{{ $evaluationDefaults['label'] }} · {{ __('index.department_evaluation_defaults_help') }}</p>
                    @endisset
                    @if(old('review_form') && $errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.type') }}</label>
                            <select class="form-control" name="review_type">
                                @foreach(['monthly','quarterly','six_month','annual','probation','special'] as $item)
                                    <option value="{{ $item }}" @selected($selectedReviewType === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.period_start') }}</label>
                            <input class="form-control" type="date" name="period_start" value="{{ old('period_start', request('period_start')) }}">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.period_end') }}</label>
                            <input class="form-control" type="date" name="period_end" value="{{ old('period_end', request('period_end')) }}">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.review_date') }}</label>
                            <input class="form-control" type="date" name="review_date" value="{{ old('review_date', request('review_date', now()->format('Y-m-d'))) }}">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                            <select class="form-control" name="status">
                                @foreach(['draft','submitted','employee_acknowledged','manager_approved','hr_approved','completed','rejected'] as $item)
                                    <option value="{{ $item }}" @selected($selectedReviewStatus === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.next_review') }}</label>
                            <input class="form-control" type="date" name="next_review_date" value="{{ old('next_review_date') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">{{ __('index.evaluation_criteria') }}</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-add-review-criterion>{{ __('index.add_review_criterion') }}</button>
                    </div>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm employee-360-table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">{{ __('index.criteria') }}</th>
                                    <th style="width: 35%;">{{ __('index.standard') }}</th>
                                    <th style="width: 10%;">{{ __('index.max') }}</th>
                                    <th style="width: 10%;">{{ __('index.score') }}</th>
                                    <th style="width: 20%;">{{ __('index.comment') }}</th>
                                    <th>{{ __('index.action') }}</th>
                                </tr>
                            </thead>
                            <tbody data-review-criteria>
                            @foreach($reviewCriteria as $index => $criteria)
                                @include('admin.employees.profile.partials.review-criterion-row', [
                                    'criteria' => $criteria,
                                    'description' => old('description.' . $index, $defaultItems[$index][1] ?? ''),
                                    'max' => old('max_score.' . $index, $defaultItems[$index][2] ?? 10),
                                    'score' => old('score.' . $index, ''),
                                    'comment' => old('comment.' . $index, ''),
                                ])
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <template data-review-criterion-template>
                        @include('admin.employees.profile.partials.review-criterion-row', ['criteria' => '', 'description' => '', 'max' => 10, 'score' => '', 'comment' => ''])
                    </template>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.strengths') }}</label>
                            <textarea class="form-control" name="strengths" rows="3" placeholder="{{ __('index.strengths') }}">{{ old('strengths') }}</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.areas_for_improvement') }}</label>
                            <textarea class="form-control" name="areas_for_improvement" rows="3" placeholder="{{ __('index.areas_for_improvement') }}">{{ old('areas_for_improvement') }}</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.final_recommendation') }}</label>
                            <textarea class="form-control" name="final_recommendation" rows="3" placeholder="{{ __('index.final_recommendation') }}">{{ old('final_recommendation') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="check" class="me-1"></i> {{ __('index.add_review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endcan
