<?php $__env->startSection('title', __('index.edit_branch')); ?>

<?php $__env->startSection('button'); ?>
    <a href="<?php echo e(route('admin.branch.index')); ?>">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> <?php echo e(__('index.button_back')); ?></button>
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.branch.common.breadcrumb',['title'=>__('index.edit')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="<?php echo e(route('admin.branch.update', $branch->id)); ?>" enctype="multipart/form-data" method="post">
                    <?php echo method_field('PUT'); ?>
                    <?php echo csrf_field(); ?>
                    <?php echo $__env->make('admin.branch.common.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </form>
            </div>
        </div>

    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/branch/edit.blade.php ENDPATH**/ ?>