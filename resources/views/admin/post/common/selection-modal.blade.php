<div class="modal fade" id="postSelectionModal" tabindex="-1" aria-labelledby="postSelectionTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postSelectionTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body">
                <label class="visually-hidden" for="postSelectionSearch">{{ __('index.search') }}</label>
                <input type="search" class="form-control mb-3" id="postSelectionSearch" placeholder="{{ __('index.search') }}" autocomplete="off">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-2">
                    <div class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="postSelectionAll">
                        <label class="form-check-label" for="postSelectionAll">{{ __('index.select_all') }}</label>
                    </div>
                    <span class="badge bg-light text-dark" id="postSelectionCount" aria-live="polite"></span>
                </div>
                <div id="postSelectionOptions"></div>
                <p class="text-muted text-center my-3" id="postSelectionEmpty" hidden>{{ __('index.no_records_found') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="postSelectionApply">{{ __('index.apply') }}</button>
            </div>
        </div>
    </div>
</div>
