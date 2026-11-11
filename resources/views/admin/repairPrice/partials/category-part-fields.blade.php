<fieldset class="repair-category-part-fields" hidden>
    <legend class="form-label fs-6">{{ __('repair.category_part_details') }}</legend>
    <p class="form-text mt-0">{{ __('repair.category_part_details_help') }}</p>
    @foreach($categories as $category)
        @php($suggestions = $categoryPartSuggestions->get($category->id, collect()))
        <fieldset class="repair-category-part-row border rounded p-3 mb-2" data-category-id="{{ $category->id }}" hidden>
            <legend class="float-none w-auto fs-6 px-1 mb-1">{{ app()->getLocale() === 'km' && $category->name_kh ? $category->name_kh : $category->name }}</legend>
            <div class="row g-3">
                @foreach(['part_name', 'part_type'] as $field)
                    <div class="col-md-6">
                        <label class="form-label" for="add-category-{{ $category->id }}-{{ $field }}">{{ __('index.'.$field) }}</label>
                        <input type="text" class="form-control" id="add-category-{{ $category->id }}-{{ $field }}"
                               name="category_parts[{{ $category->id }}][{{ $field }}]" data-part-field="{{ $field }}"
                               list="category-{{ $category->id }}-{{ $field }}-suggestions"
                               maxlength="{{ $field === 'part_name' ? 255 : 100 }}" disabled>
                        <datalist id="category-{{ $category->id }}-{{ $field }}-suggestions">
                            @foreach($suggestions->pluck($field)->filter(fn ($value) => filled($value))->unique()->sort() as $suggestion)
                                <option value="{{ $suggestion }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                @endforeach
            </div>
        </fieldset>
    @endforeach
</fieldset>
