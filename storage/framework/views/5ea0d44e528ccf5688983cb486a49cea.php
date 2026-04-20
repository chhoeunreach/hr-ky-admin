<?php use App\Models\LeaveRequestMaster; ?>
<?php use App\Enum\LeaveStatusEnum; ?>
<?php use App\Helpers\AppHelper; ?>


<?php $__env->startSection('title',__('index.time_leave_request')); ?>

<?php $__env->startSection('action',__('index.lists')); ?>

<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_time_leave_request')): ?>
        <a href="<?php echo e(route('admin.time-leave-request.create')); ?>">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i><?php echo e(__('index.create_time_leave_request')); ?>

            </button>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>
        <?php
        if (AppHelper::ifDateInBsEnabled()) {
            $filterData['min_year'] = '2076';
            $filterData['max_year'] = '2089';
            $filterData['month'] = 'np';
        } else {
            $filterData['min_year'] = '2020';
            $filterData['max_year'] = '2033';
            $filterData['month'] = 'en';
        }
        ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.timeLeaveRequest.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.time_leave_request_filter')); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.time-leave-request.index')); ?>" method="get">

                <div class="row align-items-center">

                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-xxl col-xl-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option selected disabled><?php echo e(__('index.select_branch')); ?>

                                </option>
                                <?php if(isset($companyDetail)): ?>
                                    <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option
                                            <?php echo e($filterParameters['branch_id'] == $branch->id ? 'selected' : ''); ?> value="<?php echo e($branch->id); ?>"><?php echo e(ucfirst($branch->name)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <!-- Departments Field -->
                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select" id="department_id" name="department_id">
                            <option selected disabled><?php echo e(__('index.select_department')); ?></option>

                        </select>
                    </div>
                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select" id="requestedBy" name="requested_by">
                            <option selected disabled><?php echo e(__('index.select_employee')); ?></option>

                        </select>

                    </div>

                    <div class="col-xxl col-xl-3 col-md-6  mb-4">
                        <input type="number" min="<?php echo e($filterData['min_year']); ?>"
                               max="<?php echo e($filterData['max_year']); ?>" step="1"
                               placeholder="<?php echo e(__('index.leave_requested_year')); ?> : <?php echo e($filterData['min_year']); ?>"
                               id="year"
                               name="year" value="<?php echo e($filterParameters['year']); ?>"
                               class="form-control">
                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="month" id="month">
                            <option
                                value="" <?php echo e(!isset($filterParameters['month']) ? 'selected': ''); ?> ><?php echo e(__('index.all_month')); ?></option>
                            <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($key); ?>" <?php echo e((isset($filterParameters['month']) && $key == $filterParameters['month'] ) ?'selected':''); ?> >
                                    <?php echo e($value[$filterData['month']]); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="status" id="status">
                            <option
                                value="" <?php echo e(!isset($filterParameters['status']) ? 'selected': ''); ?> ><?php echo e(__('index.all_status')); ?></option>
                            <?php $__currentLoopData = LeaveRequestMaster::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($value); ?>" <?php echo e((isset($filterParameters['status']) && $value == $filterParameters['status'] ) ?'selected':''); ?> > <?php echo e(ucfirst($value)); ?> </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-xxl col-xl-3  mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-secondary me-2"><?php echo e(__('index.filter')); ?></button>
                            <a class="btn btn-block btn-primary"
                               href="<?php echo e(route('admin.time-leave-request.index')); ?>"><?php echo e(__('index.reset')); ?></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo app('translator')->get('index.time_leave_list'); ?></h6>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo e(__('index.leave_date')); ?></th>
                            <th><?php echo e(__('index.start_time')); ?></th>
                            <th><?php echo e(__('index.end_time')); ?></th>
                            <th><?php echo e(__('index.requested_by')); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('time_leave_list')): ?>
                                <th class="text-center"><?php echo e(__('index.reason')); ?></th>
                            <?php endif; ?>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('update_time_leave')): ?>
                                <th class="text-center"><?php echo e(__('index.status')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <?php
                            $color = [
                                LeaveStatusEnum::approved->value => 'success',
                                LeaveStatusEnum::rejected->value => 'danger',
                                LeaveStatusEnum::pending->value => 'secondary',
                                LeaveStatusEnum::cancelled->value => 'danger'
                            ];

                            ?>
                        <?php $__empty_1 = true; $__currentLoopData = $timeLeaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e(AppHelper::timeLeaverequestDate($value->issue_date)); ?></td>
                                <td><?php echo e(AppHelper::convertLeaveTimeFormat($value->start_time)); ?></td>
                                <td><?php echo e(AppHelper::convertLeaveTimeFormat($value->end_time)); ?></td>
                                <td><?php echo e($value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'); ?> </td>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('time_leave_list')): ?>
                                    <td class="text-center">
                                        <a href="#" class="showTimeLeaveReason"
                                           data-href="<?php echo e(route('admin.time-leave-request.show', $value->id)); ?>"
                                           title="<?php echo e(__('index.show_leave_reason')); ?>">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('update_time_leave')): ?>
                                    <td class="text-center">
                                        <a href=""
                                           id="leaveRequestUpdate"
                                           data-href="<?php echo e(route('admin.time-leave-request.update-status',$value->id)); ?>"
                                           data-status="<?php echo e($value->status); ?>"
                                           data-remark="<?php echo e($value->admin_remark); ?>"
                                        >
                                            <button class="btn btn-<?php echo e($color[$value->status]); ?> btn-xs">
                                                <?php echo e(ucfirst($value->status)); ?>

                                            </button>
                                        </a>
                                    </td>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b><?php echo e(__('index.no_records_found')); ?></b></p>
                                </td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>
    <div class="dataTables_paginate mt-3">
        <?php echo e($timeLeaves->appends($_GET)->links()); ?>

    </div>

    <?php echo $__env->make('admin.timeLeaveRequest.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('admin.timeLeaveRequest.common.form-model', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.timeLeaveRequest.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.showTimeLeaveReason').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();
                    const url = this.getAttribute('data-href');

                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data && data.data) {
                                const leaveRequest = data.data;
                                document.getElementById('referral').innerText = leaveRequest.name || 'Admin';
                                document.getElementById('description').innerText = leaveRequest.reasons || 'N/A';
                                document.getElementById('adminRemark').innerText = leaveRequest.admin_remark || 'N/A';

                                const modal = new bootstrap.Modal(document.getElementById('addslider'));
                                modal.show();
                            } else {
                                console.error('Data format is incorrect or data is missing:', data);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });

        $(document).ready(function () {
            // Pre-selected values from $filterParameters
            const branchId = String(<?php echo e($filterParameters['branch_id'] ?? 'null'); ?>);
            const departmentId = String(<?php echo e($filterParameters['department_id'] ?? 'null'); ?>);
            const employeeId = String(<?php echo e($filterParameters['requested_by'] ?? 'null'); ?>);

            const isAdmin = <?php echo e(auth('admin')->check() ? 'true' : 'false'); ?>;
            const defaultBranchId = <?php echo e(auth()->user()->branch_id ?? 'null'); ?>;

            const loadDepartments = async (selectedBranchId) => {
                if (!selectedBranchId) return;

                try {
                    $('#department_id').empty().append('<option selected disabled><?php echo e(__("index.select_department")); ?></option>');
                    $('#requestedBy').empty().append('<option selected disabled><?php echo e(__("index.select_employee")); ?></option>');

                    const response = await $.ajax({
                        type: 'GET',
                        url: `<?php echo e(url('admin/departments/get-All-Departments')); ?>/${selectedBranchId}`,
                    });

                    if (!response || !response.data || response.data.length === 0) {
                        $('#department_id').append('<option disabled><?php echo e(__("index.no_departments_found")); ?></option>');
                        return;
                    }

                    response.data.forEach(data => {
                        $('#department_id').append(`<option value="${data.id}" ${data.id == departmentId ? 'selected' : ''}>${data.dept_name}</option>`);
                    });

                    // If departmentId is pre-selected, load employees
                    if (departmentId) {
                        await loadEmployees();
                    }
                } catch (error) {
                    console.error('Error loading departments:', error);
                    $('#department_id').append('<option disabled><?php echo e(__("index.error_loading_departments")); ?></option>');
                }
            };

            const loadEmployees = async () => {
                const selectedDepartmentId = $('#department_id').val();
                if (!selectedDepartmentId) return;

                try {
                    $('#requestedBy').empty().append('<option selected disabled><?php echo e(__("index.select_employee")); ?></option>');

                    const response = await fetch(`<?php echo e(url('admin/employees/get-all-employees')); ?>/${selectedDepartmentId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        }
                    });

                    const data = await response.json();

                    if (data.data && data.data.length > 0) {
                        data.data.forEach(user => {
                            $('#requestedBy').append(`<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''}>${user.name}</option>`);
                        });
                    } else {
                        $('#requestedBy').append('<option disabled><?php echo e(__("index.no_employees_found")); ?></option>');
                    }
                } catch (error) {
                    console.error('Error loading employees:', error);
                    $('#requestedBy').append('<option disabled><?php echo e(__("index.error_loading_employees")); ?></option>');
                }
            };

            // Load departments and employees based on pre-selected branch_id
            const initializeDropdowns = async () => {
                let selectedBranchId;

                if (isAdmin) {
                    selectedBranchId = $('#branch_id').val() || branchId; // Use DOM value or fallback to filterParameters
                    $('#branch_id').change(() => loadDepartments($('#branch_id').val())); // Bind change event
                } else {
                    selectedBranchId = defaultBranchId; // Non-admin users use their default branch
                }

                if (selectedBranchId) {
                    await loadDepartments(selectedBranchId);
                }
            };

            // Call initialization
            initializeDropdowns();

            // Bind change event for department_id
            $('#department_id').change(loadEmployees);
        });

    </script>

<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/timeLeaveRequest/index.blade.php ENDPATH**/ ?>