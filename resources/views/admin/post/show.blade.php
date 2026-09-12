<div class="modal fade" id="showEmployees" tabindex="-1" aria-labelledby="showEmployeesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-light border-bottom px-4 py-3 align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="link-icon" data-feather="users" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="showEmployeesLabel">{{ __('index.employee_list_title') }}</h5>
                        <p class="text-muted small mb-0 modal-post-subtitle">Post Members</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill px-3 py-2 modal-employee-count" style="font-size: 12px; font-weight: 600;">0 Employees</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="px-4 pt-3 pb-2 bg-white border-bottom">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;">
                        <i class="link-icon text-muted" data-feather="search" style="width: 15px; height: 15px;"></i>
                    </span>
                    <input type="text"
                           id="modalEmployeeSearch"
                           class="form-control bg-light border-start-0 ps-0"
                           style="border-radius: 0 10px 10px 0;"
                           placeholder="Search employees by name...">
                </div>
            </div>

            <div class="modal-body p-4" style="min-height: 250px; background-color: #f8fafc;">
                <div class="row g-3 employeeList">
                    <!-- Dynamic employee cards injected via JS -->
                </div>

                <div class="postEmptyCase d-none text-center py-5">
                    <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="link-icon" data-feather="user-x" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h6 class="fw-semibold text-secondary mb-1">{{ __('index.post_empty') }}</h6>
                    <p class="text-muted small mb-0">No employees currently assigned to this position.</p>
                </div>

                <div class="modalSearchEmpty d-none text-center py-5">
                    <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="link-icon" data-feather="search" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h6 class="fw-semibold text-secondary mb-1">No Matching Employees</h6>
                    <p class="text-muted small mb-0">Try a different search term.</p>
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-2 border-top">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
