<?php $__env->startSection('title', 'Employee Location'); ?>

<?php $__env->startSection('action', 'Location Log'); ?>

<?php $__env->startSection('main-content'); ?>
    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.employees.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Employee Location Log Filter</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.employee.log')); ?>" method="get">
                <div class="row align-items-center">
                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option selected disabled><?php echo e(__('index.select_branch')); ?></option>
                                <?php if(isset($companyDetail)): ?>
                                    <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($branch->id); ?>"
                                            <?php echo e((isset($filterData['branch_id']) && $filterData['branch_id'] == $branch->id) ? 'selected': ''); ?>>
                                            <?php echo e(ucfirst($branch->name)); ?>

                                        </option>
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

                        <?php if($bsEnabled): ?>

                            <div class="col-lg-3 col-md-6 mb-4">
                                <input type="text" id="nepali_startDate" class="form-control nepaliDate" name="date"
                                       value="<?php echo e($filterData['date'] ?? \App\Helpers\AppHelper::getCurrentDateInBS()); ?>">
                            </div>
                        <?php else: ?>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <input type="date" class="form-control" name="date"
                                       value="<?php echo e($filterData['date'] ?? now()->format('Y-m-d')); ?>">
                            </div>
                        <?php endif; ?>

                    <div class="col-lg-3 col-md-6 d-md-flex">
                        <button type="submit" class="btn btn-block btn-success me-md-2 me-0 mb-md-4 mb-2">
                            <?php echo e(__('index.filter')); ?>

                        </button>
                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4"
                           href="<?php echo e(route('admin.employee.log')); ?>"><?php echo e(__('index.reset')); ?></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Location Logs</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="text-center">SN</th>
                            <th><?php echo e(__('index.employee_name')); ?></th>
                            <th class="text-center"><?php echo e(__('index.date')); ?></th>
                            <th class="text-center"><?php echo e(__('index.location')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $logData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userId => $logs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $user = $logs->first()->employee; // Get user details
                                $date = $logs->first()->created_at; // Get user details
                            ?>
                            <tr>
                                <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($user->name); ?></td>
                                <td class="text-center">

                                    <?php echo e(\App\Helpers\AppHelper::formatDateForView($date)); ?>

                                    <?php if($logs->count() > 1): ?>
                                        <span class="badge bg-primary ms-2"><?php echo e($logs->count()); ?> Records</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-xs toggle-details"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#details-<?php echo e($userId); ?>"
                                            aria-expanded="false"
                                            aria-controls="details-<?php echo e($userId); ?>">
                                        View Details
                                    </button>
                                </td>

                            </tr>
                            <tr class="collapse" id="details-<?php echo e($userId); ?>">
                                <td colspan="5">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                        <tr>
                                            <th class="text-center">SN</th>
                                            <th class="text-center">Time</th>
                                            <th class="text-center">Location</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                                <td class="text-center">
                                                    <?php echo e(\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView(\App\Helpers\AppHelper::check24HoursTimeAppSetting(), $log->created_at)); ?>

                                                </td>
                                                <td class="text-center">
                                                    <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                          title="Show Location"
                                                          data-bs-toggle="modal"
                                                          data-href="<?php echo e('https://maps.google.com/maps?q=' . ($log->latitude ?? '0') . ',' . ($log->longitude ?? '0') . '&t=&z=20&ie=UTF8&iwloc=&output=embed'); ?>"
                                                          data-bs-target="#addslider">
                                                        View Location
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5">
                                    <p class="text-center"><b><?php echo e(__('index.no_records_found')); ?></b></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Modal for Google Maps -->
                    <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addsliderLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addsliderLabel">Location   <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button></h5>

                                </div>
                                <div class="modal-body">
                                    <iframe id="locationFrame" width="100%" height="400" frameborder="0" style="border:0" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.attendance.common.filter_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script>
        $(document).ready(function () {
            // Handle location modal
            $('.checkLocation').on('click', function () {
                const mapUrl = $(this).data('href');
                $('#locationFrame').attr('src', mapUrl);
            });

            // Clear modal iframe when closed to prevent memory issues
            $('#addslider').on('hidden.bs.modal', function () {
                $('#locationFrame').attr('src', '');
            });

            $('.nepaliDate').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/employees/log.blade.php ENDPATH**/ ?>