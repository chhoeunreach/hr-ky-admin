<div class="modal fade" id="departmentDeleteModal" tabindex="-1" aria-labelledby="departmentDeleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentDeleteTitle">{{ __('index.are_you_sure_delete_department') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body">
                <p id="departmentDeleteLoading" role="status">{{ __('index.loading') }}</p>
                <div class="alert alert-danger" id="departmentDeleteError" role="alert" hidden></div>
                <div id="departmentDeleteDetails" hidden>
                    <h6 id="departmentDeleteName"></h6>
                    <p class="text-muted small" id="departmentDeleteScope"></p>
                    <div class="d-flex flex-wrap gap-1 mb-3" id="departmentDeleteBranches"></div>
                    <div class="border rounded p-3 mb-3 bg-light" id="departmentDeleteCorrections" hidden>
                        <h6>{{ __('index.department_delete_correct_title') }}</h6>
                        <p class="small text-muted mb-2">{{ __('index.department_delete_correct_help') }}</p>
                    </div>
                    <h6>{{ __('index.department_delete_positions') }} <span class="badge bg-light text-dark" id="departmentDeletePositionCount"></span></h6>
                    <ul class="list-group mb-3" id="departmentDeletePositions"></ul>
                    <div class="alert alert-warning" id="departmentDeleteBlockers" hidden>
                        <strong>{{ __('index.department_delete_blockers') }}</strong>
                        <ul class="mb-2 mt-2" id="departmentDeleteBlockerList"></ul>
                        <p class="mb-0">{{ __('index.department_delete_reassign') }}</p>
                    </div>
                    <div class="border rounded p-3 mb-3" id="departmentDeleteUnlinkOption" hidden>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="departmentDeleteUnlinkRecords">
                            <label class="form-check-label fw-semibold" for="departmentDeleteUnlinkRecords">{{ __('index.department_delete_unlink') }} <span class="badge bg-light text-dark" id="departmentDeleteUnlinkCount"></span></label>
                        </div>
                        <p class="small text-muted mb-0 mt-2">{{ __('index.department_delete_unlink_help') }}</p>
                    </div>
                    <div class="form-check mb-2" id="departmentDeleteRemoveOption" hidden>
                        <input class="form-check-input" type="checkbox" id="departmentDeleteRemovePositions">
                        <label class="form-check-label" for="departmentDeleteRemovePositions">{{ __('index.department_delete_remove_positions') }}</label>
                    </div>
                    <p class="text-muted small" id="departmentDeletePermission" hidden>{{ __('index.department_delete_permission') }}</p>
                    <p class="text-muted small mb-0">{{ __('index.department_delete_confirm') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex flex-wrap gap-2 w-100" id="departmentDeleteFooterCorrections" hidden>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="departmentDeleteCorrectEmployees" hidden>{{ __('index.department_delete_correct_employees') }}</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="departmentDeleteCorrectPositions" hidden>{{ __('index.department_delete_correct_positions') }}</button>
                </div>
                <button type="button" class="btn btn-outline-primary me-auto" id="departmentDeleteRefresh">{{ __('index.department_delete_refresh') }}</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="departmentDeleteConfirm" disabled>{{ __('index.department_delete_button') }}</button>
            </div>
        </div>
    </div>
</div>
@php
    $departmentDeleteMessages = [
        'scope' => __('index.department_delete_scope'),
        'empty' => __('index.department_delete_none'),
        'inactive' => __('index.inactive'),
        'failed' => __('index.department_delete_failed'),
        'move' => __('index.department_position_move'),
        'department' => __('index.select_department'),
        'save' => __('index.save'),
        'cancel' => __('index.cancel'),
        'employeesFirst' => __('index.department_position_employees_first'),
        'noDepartments' => __('index.department_position_no_destinations'),
        'canUnlink' => __('index.department_delete_can_unlink'),
    ];
@endphp
<script type="application/json" id="departmentDeleteMessages">@json($departmentDeleteMessages)</script>
