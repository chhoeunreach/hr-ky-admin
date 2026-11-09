<div class="modal fade" id="departmentDetailsModal" tabindex="-1" aria-labelledby="departmentDetailsTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><div><small class="text-muted">{{ __('index.department_view_details') }}</small><h5 class="modal-title mt-1" id="departmentDetailsTitle"></h5></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button></div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted">{{ __('index.department_head') }}</small><div id="departmentDetailsHeads" class="mt-2 d-flex flex-column gap-1"></div></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted">{{ __('index.branch') }}</small><div id="departmentDetailsBranches" class="mt-2 d-flex flex-wrap gap-1"></div></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted">{{ __('index.total_employees') }}</small><div class="fs-3 fw-semibold mt-1" id="departmentDetailsEmployees"></div><small class="text-muted" id="departmentDetailsEmployeeStatus"></small></div></div>
                </div>
                <h6 class="mb-3">{{ __('index.department_branch_details') }}</h6>
                <div class="table-responsive"><table class="table table-bordered align-middle"><thead><tr><th>{{ __('index.branch') }}</th><th>{{ __('index.department_head') }}</th><th>{{ __('index.total_employees') }}</th><th>{{ __('index.address') }}</th><th>{{ __('index.phone') }}</th><th>{{ __('index.status') }}</th></tr></thead><tbody id="departmentDetailsEntries"></tbody></table></div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-primary me-auto" id="departmentDetailsDefaults" data-open-request-form data-request-modal-title="{{ __('index.department_performance_defaults') }}">{{ __('index.department_performance_defaults') }}</a>
                @can('edit_department')<a class="btn btn-primary" id="departmentDetailsEdit">{{ __('index.edit') }}</a>@endcan
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
@php
    $departmentDetailsMessages = ['empty' => __('index.not_available'), 'employees' => __('index.department_employee_status_filter')];
@endphp
<script type="application/json" id="departmentDetailsMessages">@json($departmentDetailsMessages)</script>
