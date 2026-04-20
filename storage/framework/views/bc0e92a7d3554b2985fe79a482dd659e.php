<?php $__env->startSection('title', __('index.company_profile')); ?>



<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('index.dashboard')); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('index.company_profile')); ?></li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-body pb-0">
                <h4 class="mb-4"><?php echo e(__('index.company_profile')); ?></h4>
                <form class="forms-sample" enctype="multipart/form-data" method="POST"
                      <?php if(!$companyDetail): ?>
                          action="<?php echo e(route('admin.company.store')); ?>"
                      <?php else: ?>
                          action="<?php echo e(route('admin.company.update', $companyDetail->id)); ?>"
                >
                    <?php echo method_field('PUT'); ?>
                    <?php endif; ?>

                    <?php echo csrf_field(); ?>
                    <?php echo $__env->make('admin.company.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </form>
            </div>
        </div>

    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/company/index.blade.php ENDPATH**/ ?>