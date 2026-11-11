<fieldset class="repair-price-service-picker" data-category-context="{{ $filterParameters['category_id'] ?? '' }}">
    <legend class="form-label fs-6">{{ __('repair.price_repair_services') }} <span class="text-danger">*</span></legend>
    <label class="form-label small" for="repair-service-category-filter">{{ __('index.category') }}</label>
    <select class="form-select form-select-sm mb-2 repair-service-category-filter" id="repair-service-category-filter">
        <option value="">{{ __('index.all_categories') }}</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ app()->getLocale() === 'km' && $category->name_kh ? $category->name_kh : $category->name }}</option>
        @endforeach
    </select>
    <input type="search" class="form-control form-control-sm mb-2 repair-service-search" placeholder="{{ __('repair.search_services') }}" aria-label="{{ __('repair.search_services') }}">
    <div class="d-flex gap-2 mb-2">
        <button type="button" class="btn btn-sm btn-outline-secondary repair-services-all">{{ __('repair.select_all') }}</button>
        <button type="button" class="btn btn-sm btn-outline-secondary repair-services-clear">{{ __('repair.clear_selection') }}</button>
        <span class="small text-muted align-self-center repair-service-count" aria-live="polite"></span>
    </div>
    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
        @forelse($services as $service)
            <label class="form-check repair-price-service-option" data-category-id="{{ $service->repair_category_id }}" data-category-name="{{ app()->getLocale() === 'km' && $service->category?->name_kh ? $service->category->name_kh : $service->category?->name }}">
                <input class="form-check-input" type="checkbox" name="repair_service_ids[]" value="{{ $service->id }}">
                <span class="form-check-label">{{ app()->getLocale() === 'km' && $service->name_kh ? $service->name_kh : $service->name }}
                    <span class="text-muted">({{ app()->getLocale() === 'km' && $service->category?->name_kh ? $service->category->name_kh : $service->category?->name }})</span>
                </span>
            </label>
        @empty
            <p class="text-muted mb-0">{{ __('index.no_records_found') }}</p>
        @endforelse
    </div>
    <div class="small text-danger repair-service-error mt-1" hidden>{{ __('repair.choose_services') }}</div>
</fieldset>
