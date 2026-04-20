<?php $__env->startSection('title', __('index.attendance')); ?>

<?php $__env->startSection('action', 'Attendance Log'); ?>

<?php $__env->startSection('main-content'); ?>
    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.attendance.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.log_filter')); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.attendance.log')); ?>" method="get">
                <div class="row align-items-center">
                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option selected disabled><?php echo e(__('index.select_branch')); ?></option>
                                <?php if(isset($companyDetail)): ?>
                                    <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($branch->id); ?>"
                                            <?php echo e((isset($filterData['branch_id']) && $filterData['branch_id'] == $branch->id) ? 'selected': ''); ?>>
                                            <?php echo e(ucfirst($branch->name)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option selected disabled><?php echo e(__('index.select_department')); ?></option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option selected disabled><?php echo e(__('index.select_employee')); ?></option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 d-md-flex">
                        <button type="submit" class="btn btn-block btn-success me-md-2 me-0 mb-md-4 mb-2"><?php echo e(__('index.filter')); ?></button>
                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4"
                           href="<?php echo e(route('admin.attendance.log')); ?>"><?php echo e(__('index.reset')); ?></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.attendance_logs')); ?></h6>
            </div>
            <div class="card-body">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="attendanceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual-logs" type="button" role="tab" aria-controls="manual-logs" aria-selected="true"><?php echo e(__('index.manual_logs')); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="biometric-tab" data-bs-toggle="tab" data-bs-target="#biometric-logs" type="button" role="tab" aria-controls="biometric-logs" aria-selected="false"><?php echo e(__('index.biometric_logs')); ?></button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="attendanceTabsContent">
                    <!-- Manual Attendance Logs -->
                    <div class="tab-pane fade show active" id="manual-logs" role="tabpanel" aria-labelledby="manual-tab">
                        <div class="table-responsive">
                            <table id="manualDataTable" class="table">
                                <thead>
                                <tr>
                                    <th>SN</th>
                                    <th><?php echo e(__('index.employee_name')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.attendance_type')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.identifier')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.date')); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $logData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($log->user?->name); ?></td>
                                        <td class="text-center"><?php echo e($log->attendance_type ?? 'N/A'); ?></td>
                                        <td class="text-center"><?php echo e($log->identifier ?? 'N/A'); ?></td>
                                        <td class="text-center"><?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDateTime(\App\Helpers\AppHelper::ifDateInBsEnabled(), $log->updated_at)); ?></td>
                                    </tr>
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
                        <div class="dataTables_paginate mt-3">
                            <?php echo e($logData->appends($_GET)->links()); ?>

                        </div>
                    </div>

                    <!-- Biometric Attendance Logs -->
                    <div class="tab-pane fade" id="biometric-logs" role="tabpanel" aria-labelledby="biometric-tab">
                        <div class="table-responsive">
                            <table id="biometricDataTable" class="table">
                                <thead>
                                <tr>
                                    <th>SN</th>
                                    <th><?php echo e(__('index.employee_name')); ?></th>
                                    <th><?php echo e(__('index.device_serial_number')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.attendance_status')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.date')); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $biometricLogData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($log->user?->name); ?></td>
                                        <td class="text-center"><?php echo e($log->sn ?? 'N/A'); ?></td>
                                        <td class="text-center"><?php echo e($log->attendance_status == 0 ? 'CheckIn' : 'CheckOut'); ?></td>
                                        <td class="text-center"><?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDateTime(\App\Helpers\AppHelper::ifDateInBsEnabled(), $log->timestamp)); ?></td>
                                    </tr>
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
                        <div class="dataTables_paginate mt-3">
                            <?php echo e($biometricLogData->appends($_GET)->links()); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        $(document).ready(function() {

            // Existing filter script
            $("#department_id").select2();
            $("#branch_id").select2();
            $("#employee_id").select2();

            const isAdmin = <?php echo e(auth('admin')->check() ? 'true' : 'false'); ?>;
            const defaultBranchId = <?php echo e(auth()->user()->branch_id ?? 'null'); ?>;
            const branchId = "<?php echo e($filterData['branch_id'] ?? null); ?>";
            const departmentId = "<?php echo e($filterData['department_id'] ?? ''); ?>";
            const employeeId = "<?php echo e($filterData['employee_id'] ?? ''); ?>";

            const loadDepartments = async (selectedBranchId) => {
                if (!selectedBranchId) return;
                try {
                    $('#department_id').empty().append('<option selected disabled><?php echo e(__("index.select_department")); ?></option>');
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
                } catch (error) {
                    $('#department_id').append('<option disabled><?php echo e(__("index.error_loading_departments")); ?></option>');
                }
            };

            const loadEmployees = async () => {
                const selectedDepartmentId = $('#department_id').val();
                if (!selectedDepartmentId) return;
                try {
                    $('#employee_id').empty().append('<option selected disabled><?php echo e(__("index.select_employee")); ?></option>');
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
                            $('#employee_id').append(`<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''} >${user.name}</option>`);
                        });
                    } else {
                        $('#employee_id').append('<option disabled><?php echo e(__("index.no_employees_found")); ?></option>');
                    }
                } catch (error) {
                    $('#employee_id').append('<option disabled><?php echo e(__("index.error_loading_employees")); ?></option>');
                }
            };

            const initializeDropdowns = async () => {
                let selectedBranchId;
                if (isAdmin) {
                    selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;
                    $('#branch_id').on('change', async () => {
                        const newBranchId = $('#branch_id').val();
                        await loadDepartments(newBranchId);
                        $('#employee_id').empty().append('<option selected disabled><?php echo e(__("index.select_employee")); ?></option>');
                        await loadEmployees();
                    });
                    if (selectedBranchId) {
                        $('#branch_id').trigger('change');
                    }
                } else {
                    selectedBranchId = defaultBranchId;
                    if (selectedBranchId) {
                        await loadDepartments(selectedBranchId);
                        await loadEmployees();
                    }
                }
                $('#department_id').on('change', loadEmployees);
                if (departmentId) {
                    $('#department_id').trigger('change');
                }
            };

            initializeDropdowns();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/attendance/log.blade.php ENDPATH**/ ?>