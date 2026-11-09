<div class="modal fade" id="departmentEmployeesModal" tabindex="-1" aria-labelledby="departmentEmployeesTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentEmployeesTitle">{{ __('index.employee_list_title') }}</h5>
                <span class="badge bg-light text-primary border ms-2" id="departmentEmployeesCount"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body">
                <p id="departmentEmployeesLoading" role="status">{{ __('index.loading') }}</p>
                <div class="alert alert-danger" id="departmentEmployeesError" role="alert" hidden></div>
                <div class="alert alert-success" id="departmentEmployeesSuccess" role="status" hidden></div>
                <p class="text-muted" id="departmentEmployeesEmpty" hidden>{{ __('index.post_empty') }}</p>
                <div class="d-flex flex-column gap-3" id="departmentEmployeesList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
<template id="departmentEmployeeRow">
    <div class="card employee-assignment-row">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-3">
                <img class="employee-avatar rounded-circle" width="44" height="44" style="object-fit: cover" alt="">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2"><span class="employee-name fw-semibold"></span><span class="employee-status badge bg-light border"></span></div>
                    <small class="employee-assignment text-muted"></small>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm employee-change">{{ __('index.department_employee_change') }}</button>
            </div>
            <form class="employee-assignment-form border-top pt-3 mt-3" hidden>
                <div class="employee-assignment-error alert alert-danger py-2 small" role="alert" hidden></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label employee-department-label">{{ __('index.department') }}</label>
                        <select class="form-select employee-department" required></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('index.position') }}</label>
                        <div class="form-control bg-light employee-position-current" aria-readonly="true"></div>
                    </div>
                </div>
                <p class="small text-muted mt-2 mb-3">{{ __('index.department_employee_branch_note') }}</p>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm employee-cancel">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm employee-save" disabled>{{ __('index.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>
@php
    $departmentEmployeeMessages = [
        'title' => __('index.employee_list_title'), 'department' => __('index.select_department'),
        'position' => __('index.select_post'), 'emptyPositions' => __('index.department_employee_no_positions'),
        'failed' => __('index.department_employee_failed'), 'loading' => __('index.loading'),
        'active' => __('index.active'), 'inactive' => __('index.inactive'),
    ];
@endphp
<script type="application/json" id="departmentEmployeeMessages">@json($departmentEmployeeMessages)</script>
