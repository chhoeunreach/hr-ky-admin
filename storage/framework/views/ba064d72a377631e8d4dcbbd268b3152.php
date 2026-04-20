<?php $__env->startSection('title',__('index.leave_approval')); ?>

<?php $__env->startSection('action',__('index.lists')); ?>

<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_leave_approval')): ?>
        <a href="<?php echo e(route('admin.leave-approval.create')); ?>">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i><?php echo e(__('index.add_leave_approval')); ?>

            </button>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>
    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.leaveApproval.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.leave_approval_filter')); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.leave-approval.index')); ?>" method="get">

                <div class="row align-items-center">

                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-xxl col-xl-4 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option selected disabled><?php echo e(__('index.select_branch')); ?>

                                </option>
                                <?php if(isset($companyDetail)): ?>
                                    <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option
                                            <?php echo e(isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id ? 'selected' : ''); ?> value="<?php echo e($branch->id); ?>"><?php echo e(ucfirst($branch->name)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <!-- Departments Field -->
                    <div class="col-xxl col-xl-4 col-md-6 mb-4">
                        <select class="form-select" id="departments" multiple name="department_id[]">
                            <option disabled><?php echo e(__('index.select_department')); ?></option>

                        </select>
                    </div>

                    <div class="col-xxl col-xl-4 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="leave_type_id" id="related">
                            <option selected disabled ><?php echo e(__('index.select_leave_type')); ?></option>

                        </select>
                    </div>


                    <div class="col-xxl col-xl-4 mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-success me-2"><?php echo e(__('index.filter')); ?></button>
                            <a class="btn btn-block btn-primary"
                               href="<?php echo e(route('admin.leave-approval.index')); ?>"><?php echo e(__('index.reset')); ?></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.leave_approval_list')); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo e(__('index.name')); ?></th>
                            <th><?php echo e(__('index.related')); ?></th>
                            <th class="text-center"><?php echo e(__('index.status')); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['update_leave_approval','delete_leave_approval','show_leave_approval'])): ?>
                                <th class="text-center"><?php echo e(__('index.action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $leaveApprovals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(++$key); ?></td>
                                <td><?php echo e($value->subject); ?></td>
                                <td><?php echo e($value->leaveType?->name); ?></td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="<?php echo e(route('admin.leave-approval.toggle-status',$value->id)); ?>"
                                               type="checkbox"<?php echo e(($value->status) == 1 ?'checked':''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['update_leave_approval','delete_leave_approval','show_leave_approval'])): ?>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('update_leave_approval')): ?>
                                                <li class="me-2">
                                                    <a href="<?php echo e(route('admin.leave-approval.edit',$value->id)); ?>"
                                                       title="<?php echo e(__('index.edit')); ?>">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_leave_approval')): ?>
                                                <li class="me-2">
                                                    <a href="<?php echo e(route('admin.leave-approval.show',$value->id)); ?>"
                                                       title="<?php echo e(__('index.show')); ?>">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_leave_approval')): ?>
                                                <li>
                                                    <a class="delete"
                                                       data-title="<?php echo e($value->name); ?> Award Detail"
                                                       data-href="<?php echo e(route('admin.leave-approval.delete',$value->id)); ?>"
                                                       title="<?php echo e(__('index.delete')); ?>">
                                                        <i class="link-icon" data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                                <?php endif; ?>
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
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            <?php echo e($leaveApprovals->appends($_GET)->links()); ?>

        </div>

    </section>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.leaveApproval.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/leaveApproval/index.blade.php ENDPATH**/ ?>