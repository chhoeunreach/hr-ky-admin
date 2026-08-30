@can('employee.performance.create')
    @php
        $reviewFormOpen = request()->boolean('review_create') || $errors->any();
        $selectedReviewType = old('review_type', request('review_type', 'quarterly'));
    @endphp
    <div class="employee-360-section">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h6 class="mb-2 mb-md-0">Add Performance Review</h6>
            <div class="d-flex gap-2">
                <button type="button"
                        class="btn btn-outline-secondary btn-sm"
                        data-bs-toggle="collapse"
                        data-bs-target="#reviewCreateCollapse"
                        aria-expanded="{{ $reviewFormOpen ? 'true' : 'false' }}"
                        aria-controls="reviewCreateCollapse">
                    Expand / Collapse
                </button>
                <button type="submit" form="reviewCreateForm" class="btn btn-primary btn-sm">Add Review</button>
            </div>
        </div>

    <form id="reviewCreateForm"
          method="post"
          action="{{ route('admin.employees.profile.reviews.store', $employee->id) }}"
          class="collapse {{ $reviewFormOpen ? 'show' : '' }}">
        @csrf
        <div class="row">
            <div class="col-lg-2 col-md-6 mb-3">
                <label class="form-label">Type</label>
                <select class="form-control" name="review_type">@foreach(['monthly','quarterly','six_month','annual','probation','special'] as $item)<option value="{{ $item }}" @selected($selectedReviewType === $item)>{{ ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select>
            </div>
            <div class="col-lg-2 col-md-6 mb-3"><label class="form-label">Period Start</label><input class="form-control" type="date" name="period_start" value="{{ old('period_start', request('period_start')) }}"></div>
            <div class="col-lg-2 col-md-6 mb-3"><label class="form-label">Period End</label><input class="form-control" type="date" name="period_end" value="{{ old('period_end', request('period_end')) }}"></div>
            <div class="col-lg-2 col-md-6 mb-3"><label class="form-label">Review Date</label><input class="form-control" type="date" name="review_date" value="{{ old('review_date', request('review_date', now()->format('Y-m-d'))) }}"></div>
            <div class="col-lg-2 col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select class="form-control" name="status">@foreach(['draft','submitted','employee_acknowledged','manager_approved','hr_approved','completed','rejected'] as $item)<option value="{{ $item }}" @selected(old('status', 'draft') === $item)>{{ ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select>
            </div>
            <div class="col-lg-2 col-md-6 mb-3"><label class="form-label">Next Review</label><input class="form-control" type="date" name="next_review_date" value="{{ old('next_review_date') }}"></div>
        </div>
        <div class="table-responsive mb-3">
            <table class="table table-sm employee-360-table">
                <thead><tr><th>Criteria</th><th>Standard</th><th>Max</th><th>Score</th><th>Comment</th></tr></thead>
                <tbody>
                @foreach($defaultItems as [$criteria, $description, $max])
                    <tr>
                        <td><input class="form-control" name="criteria[]" value="{{ $criteria }}" required></td>
                        <td><input class="form-control" name="description[]" value="{{ $description }}"></td>
                        <td><input class="form-control" type="number" name="max_score[]" value="{{ $max }}" readonly></td>
                        <td><input class="form-control" type="number" step="0.01" name="score[]" value="0" data-review-score data-max-score="{{ $max }}"></td>
                        <td><input class="form-control" name="comment[]"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label">Strengths</label><textarea class="form-control" name="strengths" rows="2">{{ old('strengths') }}</textarea></div>
            <div class="col-md-4 mb-3"><label class="form-label">Areas for Improvement</label><textarea class="form-control" name="areas_for_improvement" rows="2">{{ old('areas_for_improvement') }}</textarea></div>
            <div class="col-md-4 mb-3"><label class="form-label">Final Recommendation</label><textarea class="form-control" name="final_recommendation" rows="2">{{ old('final_recommendation') }}</textarea></div>
        </div>
    </form>
    </div>
@endcan
<div class="table-responsive">
    <table class="table table-sm employee-360-table">
        <thead><tr><th>Date</th><th>Type</th><th>Period</th><th>Score</th><th>Grade</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($reviews as $review)
            <tr><td>{{ optional($review->review_date)->format('Y-m-d') }}</td><td>{{ $review->review_type }}</td><td>{{ optional($review->period_start)->format('Y-m-d') }} - {{ optional($review->period_end)->format('Y-m-d') }}</td><td>{{ $review->total_score }}</td><td>{{ $review->grade }}</td><td>{{ $review->status }}</td></tr>
        @empty
            <tr><td colspan="6" class="text-center">No records found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
