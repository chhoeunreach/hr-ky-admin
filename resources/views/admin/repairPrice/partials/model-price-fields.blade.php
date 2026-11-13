<input type="hidden" name="per_pair_pricing" value="1">
<fieldset class="repair-model-price-fields">
    <legend class="form-label fs-6">{{ __('repair.per_pair_prices') }}</legend>
    <p class="form-text mt-0">{{ __('repair.per_pair_prices_help') }}</p>
    <p class="form-text mt-0 text-primary"><i data-feather="zap" style="width:14px;height:14px;"></i> {{ __('repair.price_split_help', ['part' => $partSharePercent]) }}</p>
    <p class="text-muted small repair-model-prices-empty">{{ __('repair.select_models_and_services') }}</p>
    <div class="table-responsive">
        <table class="table table-sm align-middle" role="table">
            <thead><tr>
                <th>{{ __('index.device_model') }}</th>
                <th>{{ __('index.category') }}</th>
                <th>{{ __('index.repair_service') }}</th>
                <th>{{ __('index.part_cost') }} ($) <span class="text-muted fw-normal">{{ $partSharePercent }}%</span></th>
                <th>{{ __('index.service_fee') }} ($) <span class="text-muted fw-normal">{{ __('repair.auto_calculated') }}</span></th>
                <th>{{ __('index.selling_price') }} ($) <span class="text-danger">*</span></th>
            </tr></thead>
            <tbody class="repair-pair-price-body"></tbody>
        </table>
    </div>
    <template class="repair-pair-price-template">
        <tr class="repair-model-price-row" role="row">
            <th scope="row" data-pair-model data-label="{{ __('index.device_model') }}"></th>
            <td data-pair-category data-label="{{ __('index.category') }}"></td>
            <td data-pair-service data-label="{{ __('index.repair_service') }}"></td>
            @foreach(['part_cost', 'service_fee', 'selling_price'] as $field)
                <td data-label="{{ __('index.'.$field) }} ($)">
                    <input type="number" class="form-control form-control-sm" style="min-width: 100px;"
                           data-price-field="{{ $field }}" data-field-label="{{ __('index.'.$field) }}"
                           min="0" step="0.01" placeholder="0.00" @if($field === 'selling_price') required @endif @if($field === 'service_fee') readonly @endif disabled>
                    @if($field !== 'selling_price')<span data-private-cost hidden>{{ __('repair.copied_cost') }}</span>@endif
                </td>
            @endforeach
        </tr>
    </template>
</fieldset>
