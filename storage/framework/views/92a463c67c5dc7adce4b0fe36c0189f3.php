<?php $__env->startSection('title',__('index.notifications')); ?>
<?php $__env->startSection('action',__('index.lists')); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.notification.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo app('translator')->get('index.notification_lists'); ?></h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="<?php echo e(route('admin.notifications.index')); ?>" method="get">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-8 mb-4">
                            <select class="form-select" name="type" id="type">
                                <option value="" <?php echo e(!isset($filterParameters['type']) ? 'selected': ''); ?>   ><?php echo app('translator')->get('index.all_types'); ?></option>
                                <?php $__currentLoopData = \App\Models\Notification::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e((isset($filterParameters['type']) && $value == $filterParameters['type'] ) ?'selected':''); ?> >
                                        <?php echo e(ucfirst($value)); ?> </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 mb-4">
                            <button type="submit" class="btn btn-block btn-primary"><?php echo app('translator')->get('index.filter'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo app('translator')->get('index.title'); ?></th>
                            <th><?php echo app('translator')->get('index.published_date'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('index.type'); ?></th>

                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('notification')): ?>
                                <th class="text-center"><?php echo app('translator')->get('index.description'); ?></th>
                            <?php endif; ?>

                            <th class="text-center"><?php echo app('translator')->get('index.status'); ?></th>

                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('notification')): ?>
                                <th class="text-center"><?php echo app('translator')->get('index.action'); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e((($notifications->currentPage()- 1 ) * (\App\Models\Notification::RECORDS_PER_PAGE) + (++$key))); ?></td>
                                <td><?php echo e(removeSpecialChars($value->title)); ?></td>
                                <td><?php echo e(convertDateTimeFormat($value->notification_publish_date) ?? 'Not published yet'); ?></td>
                                <td class="text-center"><?php echo e(ucfirst($value->type)); ?></td>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('notification')): ?>
                                    <td class="text-center">
                                        <a href=""
                                           id="showNotificationDescription"
                                           data-href="<?php echo e(route('admin.notifications.show',$value->id)); ?>"
                                           data-id="<?php echo e($value->id); ?>" title="<?php echo app('translator')->get('index.show_detail'); ?>">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>

                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="<?php echo e(route('admin.notifications.toggle-status',$value->id)); ?>"
                                               type="checkbox" <?php echo e(($value->is_active) == 1 ?'checked':''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('notification')): ?>
                                    <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">

                                            <?php if($value->type == 'general'): ?>
                                                <li class="me-2">
                                                    <a href="<?php echo e(route('admin.notifications.edit',$value->id)); ?>" title="<?php echo app('translator')->get('index.edit'); ?> ">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <li class="me-2">
                                                <a class="deleteNotification"
                                                   data-href="<?php echo e(route('admin.notifications.delete',$value->id)); ?>" title="<?php echo app('translator')->get('index.delete'); ?>">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>













                                    </ul>
                                </td>
                                <?php endif; ?>


                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b><?php echo app('translator')->get('index.no_records_found'); ?></b></p>
                                </td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            <?php echo e($notifications->appends($_GET)->links()); ?>

        </div>
    </section>

    <?php echo $__env->make('admin.notification.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>

    <?php echo $__env->make('admin.notification.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/notification/index.blade.php ENDPATH**/ ?>