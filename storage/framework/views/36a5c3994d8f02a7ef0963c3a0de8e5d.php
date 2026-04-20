<?php $__env->startSection('title', __('index.notices')); ?>

<?php $__env->startSection('action', __('index.lists')); ?>

<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_notice')): ?>
        <a href="<?php echo e(route('admin.notices.create')); ?>">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i> <?php echo app('translator')->get('index.create_notice'); ?>
            </button>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.notice.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card mb-4">
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.notices.index')); ?>" method="get">
                <div class="row align-items-center">
                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-lg col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  <?php echo e(!isset($filterParameters['branch_id']) || old('branch_id') ? 'selected': ''); ?>  disabled><?php echo e(__('index.select_branch')); ?>

                                </option>
                                <?php if(isset($companyDetail)): ?>
                                    <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($branch->id); ?>"
                                            <?php echo e((isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected': ''); ?>>
                                            <?php echo e(ucfirst($branch->name)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="col-lg col-md-6 mb-4">
                        <select class="form-select" multiple name="notice_receiver[]" id="notice">

                        </select>
                    </div>

                    <?php if(\App\Helpers\AppHelper::ifDateInBsEnabled()): ?>
                        <div class="col-lg col-md-6 mb-4">

                            <input type="text" id="fromDate" name="publish_date_from" value="<?php echo e($filterParameters['publish_date_from']); ?>" placeholder="mm/dd/yyyy" class="form-control fromDate">
                        </div>

                        <div class="col-lg col-md-6 mb-4">
                            <input type="text" id="toDate" name="publish_date_to" value="<?php echo e($filterParameters['publish_date_to']); ?>" placeholder="mm/dd/yyyy" class="form-control toDate">
                        </div>
                    <?php else: ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date" id="fromDate" name="publish_date_from" value="<?php echo e($filterParameters['publish_date_from']); ?>" class="form-control fromDate">
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date" id="toDate" name="publish_date_to" value="<?php echo e($filterParameters['publish_date_to']); ?>" class="form-control toDate">
                        </div>
                    <?php endif; ?>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="d-flex float-md-end">
                            <button type="submit" class="btn btn-block btn-success me-2"><?php echo app('translator')->get('index.filter'); ?></button>
                            <a class="btn btn-block btn-primary" href="<?php echo e(route('admin.notices.index')); ?>"><?php echo app('translator')->get('index.reset'); ?></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo app('translator')->get('index.notice_lists'); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo app('translator')->get('index.title'); ?></th>
                            <th><?php echo app('translator')->get('index.publish_date'); ?></th>
                            <th><?php echo app('translator')->get('index.notice_receiver'); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_notice')): ?>
                                <th class="text-center"><?php echo app('translator')->get('index.description'); ?></th>
                            <?php endif; ?>
                            <th class="text-center"><?php echo app('translator')->get('index.status'); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_notice', 'delete_notice', 'send_notice'])): ?>
                                <th class="text-center"><?php echo app('translator')->get('index.action'); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e((($notices->currentPage() - 1) * (\App\Models\Notice::RECORDS_PER_PAGE)) + (++$key)); ?></td>
                                <td><?php echo e(ucfirst($value->title)); ?></td>
                                <td><?php echo e(convertDateTimeFormat($value->notice_publish_date) ?? __('index.not_published_yet')); ?></td>
                                <td class="notice-receiver">
                                    <ul class="mb-0">
                                        <?php $__currentLoopData = $value->noticeReceiversDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receiver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($receiver->employee ? ucfirst($receiver->employee->name) : 'N/A'); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </td>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_notice')): ?>
                                    <td class="text-center">
                                        <a href="#" id="showNoticeDescription" data-href="<?php echo e(route('admin.notices.show', $value->id)); ?>" data-id="<?php echo e($value->id); ?>" title="<?php echo app('translator')->get('index.show_notice_content'); ?>">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="<?php echo e(route('admin.notices.toggle-status', $value->id)); ?>" type="checkbox" <?php echo e($value->is_active ? 'checked' : ''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_notice', 'delete_notice', 'send_notice'])): ?>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center align-items-center gap-2">
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_notice')): ?>
                                                <li>
                                                    <a href="<?php echo e(route('admin.notices.edit', $value->id)); ?>" title="<?php echo app('translator')->get('index.edit_notice'); ?>">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_notice')): ?>
                                                <li>
                                                    <a class="delete" data-href="<?php echo e(route('admin.notices.delete', $value->id)); ?>" title="<?php echo app('translator')->get('index.delete_notice_detail'); ?>">
                                                        <i class="link-icon" data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('send_notice')): ?>
                                                <li>
                                                    <a class="sendNotice" data-href="<?php echo e(route('admin.notices.send-notice', $value->id)); ?>" title="<?php echo app('translator')->get('index.send_notice'); ?>">
                                                        <button class="btn btn-primary btn-xs text-nowrap"><?php echo app('translator')->get('index.send_notice'); ?></button>
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
            <?php echo e($notices->appends($_GET)->links()); ?>

        </div>

    </section>

    <?php echo $__env->make('admin.notice.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.notice.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/notice/index.blade.php ENDPATH**/ ?>