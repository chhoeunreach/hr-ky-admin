<div data-daily-picker data-picker-name="{{ $pickerName }}"
    data-picker-initial='@json($pickerSelected ?? [], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG)'
    data-picker-url="{{ route('admin.daily-records.employees') }}">
    <label class="w-100">Employee{{ str_ends_with($pickerName, '[]') ? 's' : '' }}
        <input class="form-control" type="search" maxlength="100" placeholder="Search employee names" data-picker-search autocomplete="off">
    </label>
    <div class="d-flex gap-1 flex-wrap mt-1" data-picker-selected aria-live="polite"></div>
    <div class="mt-1 d-grid gap-1" data-picker-results></div>
</div>
