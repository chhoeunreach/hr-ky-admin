<?php $__env->startSection('title', __('index.users')); ?>

<?php $__env->startSection('action', __('index.edit')); ?>

<?php $__env->startSection('button'); ?>
    <div class="float-end">
        <a href="<?php echo e(route('admin.users.index')); ?>">
            <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> <?php echo e(__('index.back')); ?></button>
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.users.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card-user">
            <form class="forms-sample" id="employeeDetail" action="<?php echo e(route('admin.users.update', $userDetail->id)); ?>" enctype="multipart/form-data" method="post">
                <?php echo method_field('PUT'); ?>
                <?php echo csrf_field(); ?>
                <?php echo $__env->make('admin.users.common.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </form>
        </div>

    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>

    <?php echo $__env->make('admin.users.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/users/edit.blade.php ENDPATH**/ ?>