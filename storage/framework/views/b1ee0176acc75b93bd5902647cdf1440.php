<?php $__env->startSection('title', __('auth.login')); ?>

<?php $__env->startSection('auth-content'); ?>
    <section class="content">
            <div class="main-wrapper">
                <div class="page-wrapper full-page">
                <div class="page-content d-flex align-items-center justify-content-center">
                    <div class="row w-100 mx-0 auth-page">
                        <div class="col-md-8 col-xl-6 mx-auto">
                            <div class="card">

                                <div class="row align-items-center">
                                    <div class="col-md-4 pe-md-0">
                                        <div class="auth-side-wrapper p-4">
                                          <img src="
                                            <?php echo e($companyDetail && $companyDetail->logo ?
                                                asset(\App\Models\Company::UPLOAD_PATH.$companyDetail->logo) :
                                                asset('assets/images/img.png')); ?>"
                                               style="object-fit: cover"
                                               width="100%"
                                               height="100%"
                                               alt="<?php echo e(__('auth.company_logo_alt')); ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-8 ps-md-0">

                                        <div class="auth-form-wrapper px-4 py-5">
                                            <a href="#" class="noble-ui-logo d-block mb-2"><?php echo e($companyDetail  ? ucfirst($companyDetail->name) : ''); ?></a>
                                            <h5 class="text-muted fw-normal mb-4"><?php echo e(__('auth.welcome_back')); ?></h5>

                                            <form class="forms-sample" method="POST" action="<?php echo e(route('admin.login.process')); ?>">
                                                <?php echo csrf_field(); ?>
                                                <div class="mb-3">
                                                    <label for="userEmail" class="form-label"><?php echo e(__('auth.user_type')); ?></label>
                                                    <select class="form-select <?php $__errorArgs = ['user_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="exampleFormControlSelect1" name="user_type">
                                                        <option selected value="admin">Admin</option>
                                                        <option value="employee">Employee</option>
                                                    </select>
                                                    <?php if($errors->has('user_type')): ?>
                                                        <span class="text-danger">
                                                        <strong><?php echo e($errors->first('user_type')); ?></strong>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="userEmail" class="form-label"><?php echo e(__('auth.email_username')); ?></label>
                                                    <input
                                                        class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                        name="email" value="<?php echo e(old('email')); ?>"
                                                        required
                                                        autocomplete="email"
                                                        autofocus
                                                    >
                                                    <?php if($errors->has('username')): ?>
                                                        <span class="text-danger">
                                                        <strong><?php echo e($errors->first('username')); ?></strong>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="userPassword" class="form-label"><?php echo e(__('auth.password')); ?></label>
                                                    <input id="password"
                                                           type="password"
                                                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                           name="password"
                                                           required
                                                           autocomplete="current-password"
                                                    >
                                                    <?php if($errors->has('password')): ?>
                                                        <span class="text-danger">
                                                        <strong><?php echo e($errors->first('password')); ?></strong>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>








                                                <div>
                                                    <button type="submit" class=" btn btn-primary me-2 mb-2 mb-md-0 text-white">
                                                        <?php echo e(__('auth.login')); ?>

                                                    </button>

                                                    <?php if(Route::has('password.request')): ?>
                                                        <a class="btn btn-link" href="<?php echo e(route('password.request')); ?>">
                                                            <?php echo e(__('auth.forgot_password')); ?>

                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            </div>
        </section>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('auth.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/auth/login.blade.php ENDPATH**/ ?>