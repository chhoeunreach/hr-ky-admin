<div class="modal fade" id="employeeProfileFormModal" tabindex="-1" aria-labelledby="employeeProfileFormModalTitle" aria-hidden="true"
     data-directory-url="{{ route('admin.employees.profile.index') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeProfileFormModalTitle">{{ __('index.employee_profile') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body p-0">
                <iframe name="employeeProfileFormFrame" class="w-100 border-0 d-block" style="height: 82vh" title="{{ __('index.employee_profile') }}" data-employee-form-frame></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
