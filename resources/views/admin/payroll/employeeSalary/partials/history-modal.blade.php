@can('show_salary_history')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-salary-history.css') }}?v={{ filemtime(public_path('assets/css/employee-salary-history.css')) }}">
    <div class="modal fade" id="salaryReviewHistoryModal" tabindex="-1" aria-labelledby="salaryReviewHistoryTitle" aria-hidden="true"
         data-load-error="{{ __('index.salary_review_load_error') }}" data-loading="{{ __('index.loading') }}">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="salaryReviewHistoryTitle">{{ __('index.salary_review_history') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                </div>
                <div class="modal-body" aria-busy="false">
                    <div role="status" aria-live="polite" data-history-status></div>
                    <button class="btn btn-outline-secondary btn-sm mb-3" type="button" data-history-retry hidden>{{ __('index.salary_review_retry') }}</button>
                    <div data-history-body></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endcan
