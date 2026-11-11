<fieldset class="mb-3 repair-category-picker">
    <legend class="form-label fs-6">{{ __('repair.linked_categories') }}</legend>
    <p class="form-text mt-0">{{ __('repair.categories_help') }}</p>
    @canany(['repair_price.create', 'repair_price.update', 'repair_price.manage_prices'])
        <p class="form-text">{{ __('repair.inline_prices_help') }}</p>
    @endcanany
    <input type="search" class="form-control form-control-sm mb-2 repair-category-search" placeholder="{{ __('repair.search_categories') }}" aria-label="{{ __('repair.search_categories') }}">
    <div class="d-flex gap-2 mb-2">
        <button type="button" class="btn btn-sm btn-outline-secondary repair-categories-all">{{ __('repair.select_all') }}</button>
        <button type="button" class="btn btn-sm btn-outline-secondary repair-categories-clear">{{ __('repair.clear_selection') }}</button>
    </div>
    <div class="border rounded p-2" style="max-height: 420px; overflow-y: auto;">
        @forelse($categories as $category)
            <div class="repair-category-option border-bottom mb-2 pb-2">
            <label class="form-check">
                <input type="checkbox" class="form-check-input" name="category_ids[]" value="{{ $category->id }}">
                <span class="form-check-label">{{ app()->getLocale() === 'km' && $category->name_kh ? $category->name_kh : $category->name }}@if(!$category->status) ({{ __('index.inactive') }})@endif</span>
            </label>
            @canany(['repair_price.create', 'repair_price.update', 'repair_price.manage_prices'])
                <div class="repair-category-prices ps-4" data-category-id="{{ $category->id }}" hidden></div>
            @endcanany
            </div>
        @empty
            <span class="text-muted">{{ __('index.no_records_found') }}</span>
        @endforelse
    </div>
</fieldset>
