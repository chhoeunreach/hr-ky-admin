@include('admin.section.management-ui')
<div class="modal fade management-modal" id="departmentActionModal" tabindex="-1" aria-labelledby="departmentActionModalTitle" aria-hidden="true" data-index-url="{{ route('admin.departments.index') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentActionModalTitle">{{ __('index.edit_department') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body p-0">
                <iframe class="w-100 border-0 d-block" title="{{ __('index.edit_department') }}" data-department-action-frame></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
