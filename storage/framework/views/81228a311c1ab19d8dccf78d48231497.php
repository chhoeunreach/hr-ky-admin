<?php $__env->startSection('title', __('index.theme_color')); ?>


<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('index.dashboard')); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('index.theme_color')); ?></li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.theme_color')); ?></h6>
            </div>
            <div class="card-body pb-0">

                <?php if(isset($themeDetail)): ?>
                    <form class="forms-sample" method="POST" action="<?php echo e(route('admin.theme-color-setting.update', $themeDetail->id)); ?>">
                        <?php echo method_field('PUT'); ?>
                        <?php else: ?>
                            <form class="forms-sample" method="POST" action="<?php echo e(route('admin.theme-color-setting.store')); ?>">
                                <?php endif; ?>
                                <?php echo csrf_field(); ?>
                                <?php echo $__env->make('admin.themeColor.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            </form>
            </div>
        </div>

    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/themeColor/index.blade.php ENDPATH**/ ?>