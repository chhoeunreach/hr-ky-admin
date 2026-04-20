<?php $__env->startSection('title', __('index.edit_notice')); ?>

<?php $__env->startSection('action', __('index.edit')); ?>

<?php $__env->startSection('button'); ?>
    <a href="<?php echo e(route('admin.notices.index')); ?>">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> <?php echo app('translator')->get('index.back'); ?></button>
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.notice.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card">
            <div class="card-body">
                <form id="notification" class="forms-sample" action="<?php echo e(route('admin.notices.update', $noticeDetail->id)); ?>" method="post">
                    <?php echo method_field('PUT'); ?>
                    <?php echo csrf_field(); ?>
                    <?php echo $__env->make('admin.notice.common.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </form>
            </div>
        </div>

    </section>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.notice.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/notice/edit.blade.php ENDPATH**/ ?>