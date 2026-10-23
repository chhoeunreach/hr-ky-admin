<div class="modal fade" id="quickCompleteReviewModal" tabindex="-1" aria-labelledby="quickCompleteReviewTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            @csrf
            <input type="hidden" name="quick_complete" value="1">
            <input type="hidden" name="status" value="completed">
            <input type="hidden" name="review_type">
            <input type="hidden" name="period_start">
            <input type="hidden" name="period_end">
            <input type="hidden" name="criteria[]" value="{{ __('index.overall_performance') }}">
            <input type="hidden" name="max_score[]" value="100">
            <div class="modal-header">
                <h5 class="modal-title" id="quickCompleteReviewTitle">{{ __('index.quick_complete_review') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body">
                <p class="fw-semibold" data-review-summary></p>
                <p class="text-muted small">{{ __('index.quick_complete_review_help') }}</p>
                <div class="mb-3">
                    <label for="quickReviewDate" class="form-label">{{ __('index.review_date') }}</label>
                    <input id="quickReviewDate" class="form-control" type="date" name="review_date" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="mb-3">
                    <label for="quickReviewScore" class="form-label">{{ __('index.overall_review_score') }}</label>
                    <input id="quickReviewScore" class="form-control" type="number" name="score[]" min="0" max="100" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label for="quickReviewComment" class="form-label">{{ __('index.comment') }}</label>
                    <textarea id="quickReviewComment" class="form-control" name="manager_comment" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-primary me-auto" data-full-review>{{ __('index.full_review') }}</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                <button type="submit" class="btn btn-success">{{ __('index.mark_review_completed') }}</button>
            </div>
        </form>
    </div>
</div>
