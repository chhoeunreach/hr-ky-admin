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
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">{{ __('index.no_records_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@can('employee.performance.create')
    @php
        $selectedReviewType = old('review_type', request('review_type', 'quarterly'));
    @endphp
    <div class="modal fade" id="addPerformanceReviewModal" tabindex="-1" aria-labelledby="addPerformanceReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form id="reviewCreateForm" method="post" action="{{ route('admin.employees.profile.reviews.store', $employee->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addPerformanceReviewModalLabel">
                        <i data-feather="plus-circle" class="me-2 text-primary"></i>{{ __('index.add_performance_review') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
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
                                    <option value="{{ $item }}" @selected(old('status', 'draft') === $item)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label fw-semibold">{{ __('index.next_review') }}</label>
                            <input class="form-control" type="date" name="next_review_date" value="{{ old('next_review_date') }}">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-2">{{ __('index.evaluation_criteria') ?? 'Evaluation Criteria' }}</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm employee-360-table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">{{ __('index.criteria') }}</th>
                                    <th style="width: 35%;">{{ __('index.standard') }}</th>
                                    <th style="width: 10%;">{{ __('index.max') }}</th>
                                    <th style="width: 10%;">{{ __('index.score') }}</th>
                                    <th style="width: 20%;">{{ __('index.comment') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($defaultItems as [$criteria, $description, $max])
                                <tr>
                                    <td><input class="form-control form-control-sm" name="criteria[]" value="{{ $criteria }}" required></td>
                                    <td><input class="form-control form-control-sm" name="description[]" value="{{ $description }}"></td>
                                    <td><input class="form-control form-control-sm" type="number" name="max_score[]" value="{{ $max }}" readonly></td>
                                    <td><input class="form-control form-control-sm" type="number" step="0.01" name="score[]" value="0" data-review-score data-max-score="{{ $max }}"></td>
                                    <td><input class="form-control form-control-sm" name="comment[]" placeholder="{{ __('index.comment') }}"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

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
