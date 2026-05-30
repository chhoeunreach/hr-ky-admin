<div class="modal fade" id="assetReturnModal" tabindex="-1" aria-labelledby="assetReturnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="assetReturnModalLabel">{{ __('index.return_asset') }}</h5>
            </div>
            <div class="modal-body">
                <form id="assetReturnForm" method="POST" action="" class="needs-validation" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="is_working" class="form-label">{{ __('index.is_working') }}</label>
                        <select name="is_working" id="is_working" class="form-select" required>
                            <option selected disabled>{{ __('index.select') }}</option>
                            <option value="yes">{{ __('index.yes') }}</option>
                            <option value="no">{{ __('index.no') }}</option>
                        </select>
                        <div class="invalid-feedback">
                            {{ __('index.asset_working_required') }}
                        </div>
                    </div>

                    <div class="mb-3 notes-field">
                        <label for="notes" class="form-label">{{ __('index.notes') }}</label>
                        <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="{{ __('index.enter_any_notes') }}"></textarea>
                        <div class="invalid-feedback">
                            {{ __('index.asset_notes_required') }}
                        </div>
                    </div>

                    <div class="modal-footer justify-content-start">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.return_asset') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
