<div class="modal fade" id="employeeSavePrintFormModal" tabindex="-1" aria-labelledby="employeeSavePrintFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="employeeSavePrintFormTitle">{{ __('index.create_saved_print_form') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
        </div>
        <div class="modal-body">
            <p class="text-muted">{{ __('index.create_form_before_print') }}</p>
            <div data-save-print-paper></div>
            <p class="text-danger mt-2" data-save-print-error role="alert"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
            <button type="button" class="btn btn-primary" data-save-print-confirm>{{ __('index.create_saved_print_form') }}</button>
        </div>
    </div></div>
</div>
<div class="modal fade" id="employeeSavedFormHistory" tabindex="-1" aria-labelledby="employeeSavedFormHistoryTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="employeeSavedFormHistoryTitle">{{ __('index.saved_print_form_history') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
        </div>
        <div class="modal-body"><div data-saved-form-history role="status" aria-live="polite"></div></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" data-saved-form-more hidden>{{ __('index.saved_print_form_load_more') }}</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
        </div>
    </div></div>
</div>
@php
    $savedPrintConfig = [
        'saveUrl' => route('admin.employees.profile.form-records.store', $employee),
        'historyUrl' => route('admin.employees.profile.form-records.index', $employee),
        'warningNumberUrl' => route('admin.employees.profile.form-records.warning-number', $employee),
        'csrf' => csrf_token(), 'actions' => ['create' => __('index.create'), 'update' => __('index.update')],
        'create' => __('index.create_saved_print_form'), 'update' => __('index.update_saved_print_form'),
        'history' => __('index.saved_print_form_history'), 'newForm' => __('index.saved_print_form_new'),
        'saved' => __('index.saved_print_form_done'), 'number' => __('index.saved_print_form_number'),
        'warningNumber' => __('index.warning_number'),
        'generatingWarningNumber' => __('index.warning_number_generating'),
        'error' => __('index.saved_print_form_failed'), 'saving' => __('index.asset_assignment_saving'),
        'empty' => __('index.no_records_found'), 'checked' => __('index.print_record_checked'), 'unchecked' => __('index.print_record_unchecked'),
    ];
@endphp
<script type="application/json" id="employeeSavedPrintConfig">@json($savedPrintConfig)</script>
<script src="{{ asset('assets/js/employee-saved-print-forms.js') }}?v={{ filemtime(public_path('assets/js/employee-saved-print-forms.js')) }}" defer></script>
