<div class="repair-review-heading">
    <h6 tabindex="-1">{{ __('repair.review_heading') }}</h6>
    <p class="text-muted small mb-3">{{ __('repair.review_help') }}</p>
</div>
<div class="table-responsive">
    <table class="table table-sm repair-review-table">
        <thead><tr>
            <th>{{ __('index.device_model') }}</th><th>{{ __('index.category') }}</th><th>{{ __('index.repair_service') }}</th>
            <th>{{ __('index.part_name') }}</th><th>{{ __('index.part_type') }}</th>
            <th>{{ __('index.part_cost') }}</th><th>{{ __('index.service_fee') }}</th><th>{{ __('index.selling_price') }}</th>
        </tr></thead>
        <tbody data-review-rows></tbody>
    </table>
</div>
<dl class="repair-review-details">
    @foreach(['warranty_days', 'estimated_minutes', 'availability_status', 'note'] as $field)
        <div><dt>{{ $field === 'note' ? __('repair.price_notes') : __('index.'.($field === 'availability_status' ? 'availability' : $field)) }}</dt><dd data-review-field="{{ $field }}"></dd></div>
    @endforeach
</dl>
<p class="text-muted small mb-0">{{ __('repair.independent_prices_help') }}</p>
