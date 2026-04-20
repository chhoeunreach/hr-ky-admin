<?php $__env->startSection('title',__('index.router')); ?>

<?php $__env->startSection('action',__('index.create')); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.router.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo app('translator')->get('index.router_detail'); ?> </h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="<?php echo e(route('admin.routers.store')); ?>" enctype="multipart/form-data" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo $__env->make('admin.router.common.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </form>
            </div>
        </div>

    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/router/create.blade.php ENDPATH**/ ?>