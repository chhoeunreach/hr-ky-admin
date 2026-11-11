<input type="hidden" name="per_pair_pricing" value="1">
<fieldset class="repair-model-price-fields">
    <legend class="form-label fs-6">{{ __('repair.per_pair_prices') }}</legend>
    <p class="form-text mt-0">{{ __('repair.per_pair_prices_help') }}</p>
    <p class="text-muted small repair-model-prices-empty">{{ __('repair.select_models_and_services') }}</p>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr>
                <th>{{ __('index.device_model') }}</th>
                <th>{{ __('index.category') }}</th>
                <th>{{ __('index.repair_service') }}</th>
                <th>{{ __('index.part_cost') }} ($)</th>
                <th>{{ __('index.service_fee') }} ($)</th>
                <th>{{ __('index.selling_price') }} ($) <span class="text-danger">*</span></th>
            </tr></thead>
            <tbody class="repair-pair-price-body"></tbody>
        </table>
    </div>
    <template class="repair-pair-price-template">
        <tr class="repair-model-price-row">
            <th scope="row" data-pair-model></th>
            <td data-pair-category></td>
            <td data-pair-service></td>
            @foreach(['part_cost', 'service_fee', 'selling_price'] as $field)
                <td>
                    <input type="number" class="form-control form-control-sm" style="min-width: 100px;"
                           data-price-field="{{ $field }}" data-field-label="{{ __('index.'.$field) }}"
                           min="0" step="0.01" placeholder="0.00" @if($field === 'selling_price') required @endif disabled>
                    @if($field !== 'selling_price')<span data-private-cost hidden>{{ __('repair.copied_cost') }}</span>@endif
                </td>
            @endforeach
        </tr>
    </template>
</fieldset>
