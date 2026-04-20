<?php $__env->startSection('title',__('index.leave_requests')); ?>

<?php $__env->startSection('action',__('index.lists')); ?>

<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['create_leave_request','access_admin_leave'])): ?>
        <a href="<?php echo e(route('admin.leave-request.add')); ?>">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i><?php echo e(__('index.create_leave_request')); ?>

            </button>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>
        <?php
        if (\App\Helpers\AppHelper::ifDateInBsEnabled()) {
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

        <?php echo $__env->make('admin.leaveRequest.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><?php echo e(__('index.leave_request_filter')); ?></h6>
                </div>
                <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.leave-request.index')); ?>" method="get">

                    <div class="row align-items-center">

                        <?php if(!isset(auth()->user()->branch_id)): ?>
                            <div class="col-xxl col-xl-3 col-md-6 mb-4">
                                <select class="form-select" id="branch_id" name="branch_id" required>
                                    <option selected disabled><?php echo e(__('index.select_branch')); ?>

                                    </option>
                                    <?php if(isset($companyDetail)): ?>
                                        <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option <?php echo e($filterParameters['branch_id'] == $branch->id ? 'selected' : ''); ?> value="<?php echo e($branch->id); ?>"><?php echo e(ucfirst($branch->name)); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <!-- Departments Field -->
                        <div class="col-xxl col-xl-3 col-md-6 mb-4">
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option selected disabled><?php echo e(__('index.select_department')); ?></option>

                            </select>
                        </div>
                        <div class="col-xxl col-xl-3 col-md-6 mb-4">
                            <select class="form-select" id="requestedBy" name="requested_by" required>
                                <option selected disabled><?php echo e(__('index.select_employee')); ?></option>

                            </select>

                        </div>

                        <div class="col-xxl col-xl-3 col-md-6 mb-4">
                            <select class="form-select form-select-lg" name="leave_type" id="leaveType">
                                <option value="" <?php echo e(!isset($filterParameters['leave_type']) ? 'selected': ''); ?> ><?php echo e(__('index.all_leave_type')); ?></option>

                            </select>
                        </div>

                        <div class="col-xxl col-xl-3 col-md-6 mb-4">
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
                                <?php $__currentLoopData = \App\Models\LeaveRequestMaster::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($value); ?>" <?php echo e((isset($filterParameters['status']) && $value == $filterParameters['status'] ) ?'selected':''); ?> > <?php echo e(ucfirst($value)); ?> </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-xxl col-xl-3 mb-4">
                            <div class="d-flex">
                                <button type="submit"
                                        class="btn btn-block btn-secondary me-2"><?php echo e(__('index.filter')); ?></button>
                                <a class="btn btn-block btn-primary"
                                   href="<?php echo e(route('admin.leave-request.index')); ?>"><?php echo e(__('index.reset')); ?></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-body">

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th><?php echo e(__('index.type')); ?></th>
                                <th><?php echo e(__('index.from')); ?></th>
                                <th><?php echo e(__('index.to')); ?></th>
                                <th><?php echo e(__('index.requested_date')); ?></th>
                                <th><?php echo e(__('index.requested_by')); ?></th>
                                <th class="text-center"><?php echo e(__('index.requested_days')); ?></th>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['show_leave_request_detail','access_admin_leave'])): ?>
                                    <th class="text-center"><?php echo e(__('index.reason')); ?></th>
                                <?php endif; ?>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['update_leave_request','access_admin_leave'])): ?>
                                    <th class="text-center"><?php echo e(__('index.status')); ?></th>
                                <?php endif; ?>
                            </tr>
                            </thead>
                            <tbody>

                                <?php
                                $color = [
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending' => 'secondary',
                                    'cancelled' => 'danger'
                                ];

                                ?>
                            <?php $__empty_1 = true; $__currentLoopData = $leaveDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                <?php if(auth('admin')->user()): ?>
                                    <tr>
                                        <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($value->leaveType ? ucfirst($value->leaveType->name) : ''); ?></td>
                                        <td><?php echo e(\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_from)); ?></td>
                                        <td><?php echo e(\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_to)); ?></td>
                                        <td><?php echo e(\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)); ?></td>
                                        <td><?php echo e($value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'); ?> </td>
                                        <td class="text-center"><?php echo e(($value->no_of_days )); ?></td>

                                            <td class="text-center">
                                                <a href="#" class="showLeaveReason"
                                                   data-href="<?php echo e(route('admin.leave-request.show', $value->id)); ?>"
                                                   title="<?php echo e(__('index.show_leave_reason')); ?>">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>

                                            </td>
                                            <td class="text-center">
                                                <a href=""
                                                   class="leaveRequestUpdate"
                                                   data-href="<?php echo e(route('admin.leave-request.update-status',$value->id)); ?>"
                                                   data-status="<?php echo e($value->status); ?>"
                                                   data-remark="<?php echo e($value->admin_remark); ?>"
                                                   data-id="<?php echo e($value->id); ?>"
                                                >
                                                    <button class="btn btn-<?php echo e($color[$value->status]); ?> btn-xs">
                                                        <?php echo e(ucfirst($value->status)); ?>

                                                    </button>
                                                </a>
                                            </td>

                                    </tr>
                                <?php else: ?>
                                    <?php
                                        $inRole = false;
                                        $approver = null;
                                        // Get the next approver for pending leaves
                                        $approver = \App\Helpers\AppHelper::getNextApprover($value->id, $value->leave_type_id, $value->requested_by);
                                        $permissionKey = 'access_admin_leave';

                                        $roleArray = \App\Helpers\AppHelper::getRoleByPermission($permissionKey);

                                        if(auth()->user()){
                                            $inRole = in_array(auth()->user()->role_id, $roleArray);
                                        }

                                    ?>
                                    <?php if(($approver == auth()->user()->id && $value->status =='pending')  || ($inRole && $value->status =='pending')): ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration); ?></td>
                                            <td><?php echo e($value->leaveType ? ucfirst($value->leaveType->name) : ''); ?></td>
                                            <td><?php echo e(\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_from)); ?></td>
                                            <td><?php echo e(\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_to)); ?></td>
                                            <td><?php echo e(\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)); ?></td>
                                            <td><?php echo e($value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'); ?> </td>
                                            <td class="text-center"><?php echo e(($value->no_of_days )); ?></td>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['show_leave_request_detail','access_admin_leave'])): ?>
                                                <td class="text-center">
                                                    <a href="#" class="showLeaveReason"
                                                       data-href="<?php echo e(route('admin.leave-request.show', $value->id)); ?>"
                                                       title="<?php echo e(__('index.show_leave_reason')); ?>">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>

                                                </td>
                                            <?php endif; ?>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['update_leave_request','access_admin_leave'])): ?>

                                                <td class="text-center">
                                                    <a href=""
                                                       class="leaveRequestUpdate"
                                                       data-href="<?php echo e(route('admin.leave-request.update-status',$value->id)); ?>"
                                                       data-status="<?php echo e($value->status); ?>"
                                                       data-remark="<?php echo e($value->admin_remark); ?>"
                                                       data-id="<?php echo e($value->id); ?>"
                                                    >
                                                        <button class="btn btn-<?php echo e($color[$value->status]); ?> btn-xs">
                                                            <?php echo e(ucfirst($value->status)); ?>

                                                        </button>
                                                    </a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php elseif( ($value->requestApproval->where('leave_request_id', $value->id)->contains('approved_by', auth()->user()->id) || ($approver == auth()->user()->id && $value->status != 'pending')) || ($inRole && $value->status !='pending')): ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration); ?></td>
                                            <td><?php echo e($value->leaveType ? ucfirst($value->leaveType->name) : ''); ?></td>
                                            <td><?php echo e(\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_from)); ?></td>
                                            <td><?php echo e(\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_to)); ?></td>
                                            <td><?php echo e(\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)); ?></td>
                                            <td><?php echo e($value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'); ?> </td>
                                            <td class="text-center"><?php echo e(($value->no_of_days )); ?></td>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['show_leave_request_detail','access_admin_leave'])): ?>
                                                <td class="text-center">
                                                    <a href="#" class="showLeaveReason"
                                                       data-href="<?php echo e(route('admin.leave-request.show', $value->id)); ?>"
                                                       title="<?php echo e(__('index.show_leave_reason')); ?>">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>

                                                </td>
                                            <?php endif; ?>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['show_leave_request_detail','access_admin_leave'])): ?>
                                                <td class="text-center">

                                                    <?php
                                                        $approval = $value->requestApproval
                                                                   ->where('leave_request_id', $value->id)
                                                                   ->where('approved_by', auth()->user()->id)
                                                                   ->first();

                                                    ?>
                                                    <?php if(isset($approval)): ?>
                                                        <a href="javascript:void(0)" class="show-approval-info"
                                                           data-id="<?php echo e($value->id); ?>">
                                                            <button
                                                                class="btn btn-<?php echo e($value->status == 'rejected' ? 'danger' : ($approval->status == 1 ? 'success' : 'danger')); ?> btn-xs">
                                                                <?php echo e($value->status == 'rejected' ? 'Rejected' : ($approval->status == 1 ? 'Approved' : 'Rejected')); ?>

                                                            </button>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="javascript:void(0)" class="show-approval-info"
                                                           data-id="<?php echo e($value->id); ?>">
                                                            <button
                                                                class="btn btn-<?php echo e($value->status == 'rejected' ? 'danger' : 'success'); ?> btn-xs">
                                                                <?php echo e(ucfirst($value->status)); ?>

                                                            </button>
                                                        </a>

                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php else: ?>

                                    <?php endif; ?>
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
        <?php echo e($leaveDetails->appends($_GET)->links()); ?>

    </div>

    <?php echo $__env->make('admin.leaveRequest.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('admin.leaveRequest.common.form-model', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('admin.leaveRequest.common.approval-info-model', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.leaveRequest.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.showLeaveReason').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();
                    const url = this.getAttribute('data-href');

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {

                            if (data && data.data) {
                                const leaveRequest = data.data;
                                document.getElementById('referredBy').innerText = leaveRequest.name || 'Admin';
                                document.getElementById('description').innerText = leaveRequest.reasons || 'N/A';
                                document.getElementById('adminRemark').innerText = leaveRequest.admin_remark || 'N/A';

                                const modalElement = document.getElementById('addslider');

                                if (modalElement) {
                                    const modal = new bootstrap.Modal(modalElement);
                                    modal.show();
                                } else {
                                    console.error('Modal element not found');
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });


    </script>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/leaveRequest/index.blade.php ENDPATH**/ ?>