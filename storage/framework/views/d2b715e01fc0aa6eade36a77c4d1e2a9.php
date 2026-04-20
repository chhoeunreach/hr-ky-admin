<div class="theme-color">
    <div class="row">
        <div class="col-lg-6 col-md-6 pe-5">
            <div class="theme-color-list">
                <h5 class="border-bottom pb-3 mb-3">Light Theme</h5>
                <div class="theme-primary-color mb-3">
                    <label for="primary_color" class="form-label d-block"><?php echo e(__('index.primary_color')); ?></label>
                    <input type="color" class="form-control form-control-color <?php $__errorArgs = ['primary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        name="primary_color" id="primary_color"
                        value="<?php echo e(isset($themeDetail) ? $themeDetail->primary_color : old('primary_color', '#000000')); ?>"
                        title="Choose your color" />
                    <?php $__errorArgs = ['primary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="theme-hover-color mb-3">
                    <label for="hover_color" class="form-label d-block"><?php echo e(__('index.hover_color')); ?></label>
                    <input type="color" class="form-control form-control-color <?php $__errorArgs = ['hover_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        name="hover_color" id="hover_color"
                        value="<?php echo e(isset($themeDetail) ? $themeDetail->hover_color : old('hover_color', '#000000')); ?>"
                        title="Choose your color" />
                    <?php $__errorArgs = ['hover_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 ps-5">
            <div class="theme-color-list">
                <h5 class="border-bottom pb-3 mb-3">Dark Theme</h5>
                <div class="theme-primary-color mb-3">
                    <label for="dark_primary_color" class="form-label d-block"><?php echo e(__('index.dark_primary_color')); ?></label>
                    <input type="color" class="form-control form-control-color <?php $__errorArgs = ['dark_primary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        name="dark_primary_color" id="dark_primary_color"
                        value="<?php echo e(isset($themeDetail) ? $themeDetail->dark_primary_color : old('dark_primary_color', '#000000')); ?>"
                        title="Choose your color" />
                    <?php $__errorArgs = ['dark_primary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="theme-primary-color mb-3">
                    <label for="dark_hover_color" class="form-label d-block"><?php echo e(__('index.dark_hover_color')); ?></label>
                    <input type="color" class="form-control form-control-color <?php $__errorArgs = ['dark_hover_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        name="dark_hover_color" id="dark_hover_color"
                        value="<?php echo e(isset($themeDetail) ? $themeDetail->dark_hover_color : old('dark_hover_color', '#000000')); ?>"
                        title="Choose your color" />
                    <?php $__errorArgs = ['dark_hover_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div> 
        <div class="col-lg-12">
            <div class="border-top pt-4 mb-4">
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('theme_setting')): ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="link-icon" data-feather="plus"></i>
                        <?php echo e($themeDetail ? __('index.update') : __('index.save')); ?>

                    </button>
                <?php endif; ?>
            </div>
        </div>   
    </div>
</div>

<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/themeColor/form.blade.php ENDPATH**/ ?>