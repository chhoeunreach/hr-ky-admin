<?php $__env->startSection('title', __('index.show_user_details')); ?>

<?php $__env->startSection('action', __('index.detail')); ?>

<?php $__env->startSection('button'); ?>
    <div class="d-md-flex">
        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_employee')): ?>
            <a href="<?php echo e(route('admin.employees.edit', $userDetail->id)); ?>">
                <button class="btn btn-secondary me-2">
                    <i class="link-icon" data-feather="edit"></i><?php echo e(__('index.edit_detail')); ?>

                </button>
            </a>
        <?php endif; ?>

        <a href="<?php echo e(route('admin.employees.index')); ?>">
            <button class="btn btn-primary "><i class="link-icon" data-feather="arrow-left"></i> <?php echo e(__('index.back')); ?></button>
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.employees.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


        <div class="d-md-flex align-items-center text-md-start text-center mb-md-4 mb-2">
            <img class="wd-100 ht-100 rounded-circle" style="object-fit: cover"
                 src="<?php echo e(asset(\App\Models\User::AVATAR_UPLOAD_PATH . $userDetail->avatar)); ?>" alt="profile">
            <div class="ms-md-3 mt-md-0 mt-2">
                <span class="fw-bold"><?php echo e(ucfirst($userDetail->name)); ?></span>
                <p class="fw-bold"><?php echo e($userDetail->employee_code); ?></p>
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

                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.gender')); ?>:</label>
                                <p class="d-inline-block"><?php echo e(ucfirst($userDetail->gender)); ?></p>
                            </div>
                        </div>

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.marital_status')); ?>:</label>
                                <p class="d-inline-block"><?php echo e(ucfirst($userDetail->marital_status)); ?></p>
                            </div>

                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.address')); ?>:</label>
                                <p class="d-inline-block"><?php echo e(ucfirst($userDetail->address)); ?></p>
                            </div>
                        </div>

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.phone_number')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->phone); ?></p>
                            </div>

                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.date_of_birth')); ?>:</label>
                                <p class="d-inline-block"> <?php echo e(isset($userDetail->dob) ? \App\Helpers\AppHelper::formatDateForView($userDetail->dob) : ''); ?></p>
                            </div>
                        </div>

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.role')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->role ? ucfirst($userDetail->role->name) : __('index.not_applicable')); ?></p>
                            </div>

                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.is_active')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->is_active == 1 ? __('index.yes') : __('index.no')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4 d-flex">
                <div class="card rounded w-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0" style="align-content: center;"><?php echo e(__('index.office_detail')); ?></h6>
                    </div>
                    <div class="card-body card-profile py-2">

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.branch_name')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->branch ? ucfirst($userDetail->branch->name) : __('index.not_applicable')); ?></p>
                            </div>

                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.department_name')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->department ? ucfirst($userDetail->department->dept_name) : __('index.not_applicable')); ?></p>
                            </div>
                        </div>

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.post_name')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->post ? ucfirst($userDetail->post->post_name) : __('index.not_applicable')); ?></p>
                            </div>

                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.employment_type')); ?>:</label>
                                <p class="d-inline-block"><?php echo e(ucfirst($userDetail->employment_type)); ?></p>
                            </div>
                        </div>

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.joining_date')); ?>:</label>
                                <p class="d-inline-block"><?php echo e(isset($userDetail->joining_date) ? \App\Helpers\AppHelper::formatDateForView($userDetail->joining_date) : __('index.not_applicable')); ?></p>
                            </div>

                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.workspace')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->workspace_type == 1 ? __('index.office') : __('index.home')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4 d-flex">
                <div class="card rounded w-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0" style="align-content: center;"><?php echo e(__('index.account_detail')); ?></h6>
                    </div>
                    <div class="card-body card-profile py-2">

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.bank_name')); ?>:</label>
                                <p class="d-inline-block"><?php echo e(ucfirst($userDetail->accountDetail->bank_name ?? __('index.not_available'))); ?></p>
                            </div>

                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.account_number')); ?>:</label>
                                <p class="d-inline-block"><?php echo e($userDetail->accountDetail->bank_account_no ?? __('index.not_available')); ?></p>
                            </div>
                        </div>

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.account_type')); ?>:</label>
                                <p class="d-inline-block"><?php echo e(ucfirst($userDetail->accountDetail->bank_account_type ?? __('index.not_available'))); ?></p>
                            </div>

                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4"><?php echo e(__('index.account_holder')); ?>:</label>
                                <p class="d-inline-block"><?php echo e(ucfirst($userDetail->accountDetail->account_holder ?? __('index.not_available'))); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/employees/show2.blade.php ENDPATH**/ ?>