<?php $__env->startSection('title', __('index.users')); ?>

<?php $__env->startSection('action', __('index.detail')); ?>

<?php $__env->startSection('button'); ?>
    <div class="d-md-flex">

            <a href="<?php echo e(route('admin.users.edit', $userDetail->id)); ?>">
                <button class="btn btn-secondary me-2">
                    <i class="link-icon" data-feather="edit"></i><?php echo e(__('index.edit_detail')); ?>

                </button>
            </a>


        <a href="<?php echo e(route('admin.users.index')); ?>">
            <button class="btn btn-primary "><i class="link-icon" data-feather="arrow-left"></i> <?php echo e(__('index.back')); ?></button>
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.users.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


        <div class="d-md-flex align-items-center text-md-start text-center mb-md-4 mb-2">
            <img class="wd-100 ht-100 rounded-circle" style="object-fit: cover"
                 src="<?php echo e(asset(\App\Models\Admin::AVATAR_UPLOAD_PATH . $userDetail->avatar)); ?>" alt="profile">
            <div class="ms-md-3 mt-md-0 mt-2">
                <span class="fw-bold"><?php echo e(ucfirst($userDetail->name)); ?></span>
                <p class=""><?php echo e(ucfirst($userDetail->email)); ?></p>
            </div>
        </div>

        <div class="row profile-body">
            <div class="col-lg-6 mb-4 d-flex">
                <div class="card rounded w-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0" style="align-content: center;"><?php echo e(__('index.user_detail')); ?></h6>
                    </div>
                    <div class="card-body card-profile py-2">

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.username')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->username); ?></p>
                            </div>

                        </div>





                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">


                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.is_active')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->is_active == 1 ? __('index.yes') : __('index.no')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>

    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/users/show2.blade.php ENDPATH**/ ?>