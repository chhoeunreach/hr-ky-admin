<div data-daily-picker data-picker-name="{{ $pickerName }}"
    data-picker-initial='@json($pickerSelected ?? [], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG)'
    data-picker-url="{{ route('admin.daily-records.employees') }}" class="position-relative">
    <label class="dr-form-label"><i class="fa-solid fa-user-check me-1"></i> {{ str_ends_with($pickerName, '[]') ? __('daily_records.employees') : __('daily_records.employee') }}</label>
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0 text-muted" style="border-color: var(--dr-border, #e2e8f0);"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input class="dr-form-control border-start-0 ps-0" type="search" maxlength="100" placeholder="{{ __('daily_records.type_employee_name') }}" data-picker-search autocomplete="off">
    </div>
    <div class="d-flex gap-1 flex-wrap mt-2" data-picker-selected aria-live="polite"></div>
    <div class="mt-1 d-grid gap-1 position-absolute w-100 shadow-lg rounded bg-white p-2 border" style="z-index: 1050; display: none !important;" data-picker-results></div>
</div>
