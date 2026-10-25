<div class="modal fade asset-detail-modal" id="assetDetail" tabindex="-1" aria-labelledby="assetDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <div>
                    <div class="asset-detail-eyebrow">{{ __('index.asset_detail') }}</div>
                    <h5 class="modal-title assetTitle" id="assetDetailLabel"></h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body p-4">
                <div class="asset-detail-layout">
                    <section class="asset-overview">
                        <h6 class="mb-3">{{ __('index.asset_overview') }}</h6>
                        <table class="table table-borderless">
                            <tbody>
                            @foreach(['name' => 'name', 'type' => 'type', 'asset_code' => 'asset_code', 'asset_serial_no' => 'asset_serial_number', 'phone_numbers' => 'phone_number', 'icloud' => 'asset_icloud', 'asset_password_status' => 'asset_device_password', 'screen_time_status' => 'asset_screen_time_passcode', 'set_by_name' => 'asset_set_by', 'is_working' => 'is_working', 'purchased_date' => 'purchased_date', 'is_available' => 'is_available_for_employee', 'note' => 'description', 'used_for' => 'used_for'] as $field => $label)
                                <tr><th scope="row" class="text-muted">{{ __('index.'.$label) }}</th><td class="{{ $field }} fw-medium"></td></tr>
                            @endforeach
                            </tbody>
                        </table>
                    </section>
                    <section>
                        <h6 class="mb-1">{{ __('index.asset_image_gallery') }}</h6>
                        <p class="text-muted small mb-3">{{ __('index.asset_image_gallery_help') }}</p>
                        <div class="asset-images" data-asset-gallery></div>
                    </section>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
@include('admin.assetManagement.assetDetail.common.image-viewer')
