<link rel="stylesheet" href="{{ asset('assets/css/asset-assignment.css') }}?v={{ filemtime(public_path('assets/css/asset-assignment.css')) }}">
<div class="modal fade" id="assetAssignmentModal" tabindex="-1" aria-labelledby="assetAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assetAssignmentModalLabel">{{ __('index.assign_asset') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">{{ __('index.asset_assignment_selection_help') }}</p>
                <div class="asset-assignment-summary">
                    @foreach(['name' => 'asset_name', 'code' => 'asset_code', 'serial' => 'asset_serial_number', 'branch' => 'branch'] as $field => $label)
                        <div><span>{{ __('index.' . $label) }}</span><strong data-assignment-summary="{{ $field }}">—</strong></div>
                    @endforeach
                </div>
                <p class="small text-muted" data-assignment-status role="status" aria-live="polite"></p>
                <div class="alert alert-danger d-none" data-assignment-error role="alert">
                    <span data-assignment-error-text></span>
                    <button type="button" class="btn btn-outline-danger btn-sm ms-2 d-none" data-assignment-retry>{{ __('index.asset_assignment_retry') }}</button>
                </div>
                <form id="assetAssignmentForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="asset_id" id="assignment_asset_id">
                    <div class="mb-3">
                        <label for="assignment_user_id" class="form-label">{{ __('index.employee') }} <span class="text-danger">*</span></label>
                        <select name="user_id" id="assignment_user_id" class="form-select" required disabled aria-describedby="assignmentEmployeeHelp">
                            <option value="">{{ __('index.select_employee') }}</option>
                        </select>
                        <div class="form-text" id="assignmentEmployeeHelp">{{ __('index.asset_assignment_employee_search') }}</div>
                        <div class="text-danger small" data-assignment-field-error="user_id"></div>
                    </div>
                    <div class="mb-3">
                        <label for="assignment_branch_id" class="form-label">{{ __('index.branch') }} <span class="text-danger">*</span></label>
                        <select name="branch_id" id="assignment_branch_id" class="form-select" required disabled>
                            <option value="">{{ __('index.select_branch') }}</option>
                        </select>
                        <div class="form-text" data-assignment-branch-help>{{ __('index.asset_assignment_branch_help') }}</div>
                        <div class="text-danger small" data-assignment-field-error="branch_id"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="assignment_department_id" class="form-label">{{ __('index.department') }} <span class="text-danger">*</span></label>
                            <select name="department_id" id="assignment_department_id" class="form-select" required disabled>
                                <option value="">{{ __('index.asset_assignment_all_departments') }}</option>
                            </select>
                            <div class="text-danger small" data-assignment-field-error="department_id"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="assigned_date" class="form-label">{{ __('index.assigned_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="assigned_date" id="assigned_date" class="form-control" required value="{{ now()->toDateString() }}">
                            <div class="text-danger small" data-assignment-field-error="assigned_date"></div>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0 mt-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
                        <button type="submit" class="btn btn-primary" disabled>{{ __('index.assign_asset') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@php
    $assignmentConfig = [
    'branch' => __('index.select_branch'), 'chooseBranch' => __('index.asset_assignment_choose_branch'),
    'noBranches' => __('index.asset_assignment_no_branches'),
    'employee' => __('index.select_employee'), 'departments' => __('index.asset_assignment_all_departments'),
    'loading' => __('index.asset_assignment_loading'), 'loadError' => __('index.asset_assignment_load_error'),
    'empty' => __('index.no_employees_found'), 'noResults' => __('index.resignation_no_results'),
    'searching' => __('index.resignation_searching'), 'unavailable' => __('index.not_available'),
    'saving' => __('index.asset_assignment_saving'), 'saveError' => __('index.asset_assignment_error'),
    'success' => __('index.asset_assigned_success'), 'successTitle' => __('index.success'),
    'ok' => __('index.ok'), 'today' => now()->toDateString(),
];
@endphp
<script type="application/json" id="assetAssignmentConfig">@json($assignmentConfig)</script>
