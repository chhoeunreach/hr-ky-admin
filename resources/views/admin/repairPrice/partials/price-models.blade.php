<fieldset class="repair-price-model-picker" data-can-view-cost="{{ $canViewCost ? 1 : 0 }}" data-device-context="{{ $filterParameters['device_id'] ?? '' }}">
    <legend class="form-label fs-6">{{ __('repair.price_device_models') }} <span class="text-danger">*</span></legend>
    <p class="form-text mt-0">{{ __('repair.multi_model_price_help') }}</p>
    <input type="search" class="form-control form-control-sm mb-2 repair-model-search" placeholder="{{ __('repair.search_models') }}" aria-label="{{ __('repair.search_models') }}">
    <div class="d-flex gap-2 mb-2">
        <button type="button" class="btn btn-sm btn-outline-secondary repair-models-all">{{ __('repair.select_all') }}</button>
        <button type="button" class="btn btn-sm btn-outline-secondary repair-models-clear">{{ __('repair.clear_selection') }}</button>
        <span class="align-self-center small text-muted repair-model-count" aria-live="polite"></span>
    </div>
    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
        @forelse($devices as $device)
            <label class="form-check repair-price-model-option">
                <input class="form-check-input" type="checkbox" name="repair_device_ids[]" value="{{ $device->id }}">
                <span class="form-check-label">{{ $device->brand?->name }} — {{ $device->name }}@if($device->model_number) ({{ $device->model_number }})@endif @if(!$device->status) ({{ __('index.inactive') }})@endif</span>
            </label>
        @empty
            <p class="text-muted mb-0">{{ __('index.no_records_found') }}</p>
        @endforelse
    </div>
    <div class="small text-danger repair-model-error mt-1" hidden>{{ __('repair.choose_models') }}</div>
</fieldset>
