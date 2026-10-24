<div class="modal fade" id="assetActionModal" tabindex="-1" aria-labelledby="assetActionModalTitle" aria-hidden="true" data-assets-url="{{ route('admin.assets.index') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assetActionModalTitle">{{ __('index.asset_detail') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body p-0">
                <iframe class="w-100 border-0 d-block" style="height: 80vh;" title="{{ __('index.asset_detail') }}" data-asset-action-frame></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
