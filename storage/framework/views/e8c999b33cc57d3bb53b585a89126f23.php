<?php $__env->startSection('title', __('index.users')); ?>

<?php $__env->startSection('action', __('index.lists')); ?>

<?php $__env->startSection('button'); ?>

    <a href="<?php echo e(route('admin.users.create')); ?>">
        <button class="btn btn-primary d-flex align-items-center gap-2">
            <i class="link-icon" data-feather="plus"></i><?php echo e(__('index.add_user')); ?>

        </button>
    </a>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.users.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


        <div class="card">
        <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.user_list')); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th><?php echo e(__('index.full_name')); ?></th>
                            <th class="text-center"><?php echo e(__('index.email')); ?></th>
                            <th class="text-center"><?php echo e(__('index.is_active')); ?></th>
                            <th class="text-center"><?php echo e(__('index.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <?php
                            $changeColor = [
                                0 => 'success',
                                1 => 'primary',
                            ]
                            ?>
                        <?php $__empty_1 = true; $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center">
                                    <a href="<?php echo e(route('admin.users.show', $value->id)); ?>" id="showOfficeTimeDetail">
                                        <i class="link-icon" data-feather="eye"></i>
                                    </a>
                                </td>
                                <td><?php echo e(ucfirst($value->name)); ?> </td>
                                <td class="text-center"><?php echo e($value->email); ?></td>

                                    <td class="text-center">
                                        <label class="switch">
                                            <input class="toggleStatus"
                                                   href="<?php echo e(route('admin.users.toggle-status', $value->id)); ?>"
                                                   type="checkbox" <?php echo e($value->is_active == 1 ? 'checked' : ''); ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>


                                        <td class="text-center">
                                            <a class="nav-link dropdown-toggle p-0" href="#" id="profileDropdown"
                                               role="button"
                                               data-bs-toggle="dropdown"
                                               aria-haspopup="true"
                                               aria-expanded="false"
                                               title="<?php echo e(__('index.action')); ?>"
                                            >
                                            </a>

                                            <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                                                <ul class="list-unstyled p-1 mb-0">

                                                    <?php if($value->id == auth('admin')->user()->id): ?>
                                                        <li class="dropdown-item py-2">
                                                            <a href="<?php echo e(route('admin.users.edit', $value->id)); ?>">
                                                                <button class="btn btn-primary btn-xs"><?php echo e(__('index.edit_detail')); ?></button>
                                                            </a>
                                                        </li>

                                                    <?php endif; ?>


                                                        <?php if($value->id != auth('admin')->user()->id || $value->id != 1): ?>
                                                            <li class="dropdown-item py-2">
                                                                <a class="deleteEmployee"
                                                                   data-href="<?php echo e(route('admin.users.delete', $value->id)); ?>">
                                                                    <button class="btn btn-primary btn-xs"><?php echo e(__('index.delete_user')); ?></button>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>


                                                    <?php if($value->id == auth('admin')->user()->id): ?>
                                                        <li class="dropdown-item py-2">
                                                            <a class="changePassword"
                                                               data-href="<?php echo e(route('admin.users.change-password', $value->id)); ?>">
                                                                <button class="btn btn-primary btn-xs"><?php echo e(__('index.change_password')); ?></button>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>


                                                </ul>
                                            </div>
                                        </td>

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
            <?php echo e($admins->appends($_GET)->links()); ?>

        </div>

    </section>
    <?php echo $__env->make('admin.users.common.password', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.users.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/users/index.blade.php ENDPATH**/ ?>