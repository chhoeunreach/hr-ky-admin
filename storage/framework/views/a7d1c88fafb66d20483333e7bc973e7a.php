<div class="row">

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label"> <?php echo e(__('index.company_name')); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo e(( $companyDetail ? $companyDetail->name: '' )); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="owner" class="form-label"><?php echo e(__('index.company_owner')); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="owner" name="owner_name" value="<?php echo e(($companyDetail? $companyDetail->owner_name: old('name') )); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="address" class="form-label"> <?php echo e(__('index.address')); ?> <span style="color: red">*</span> </label>
        <input type="text" class="form-control" id="address" name="address" value="<?php echo e(($companyDetail? $companyDetail->address: old('address') )); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="email" class="form-label"><?php echo e(__('index.email_address')); ?> <span style="color: red">*</span></label>
        <input type="email" class="form-control" id="address" name="email" value="<?php echo e(($companyDetail? $companyDetail->email: old('email') )); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="number" class="form-label"><?php echo e(__('index.phone_no')); ?> <span style="color: red">*</span></label>
        <input type="number" class="form-control" id="phone" name="phone" value="<?php echo e(($companyDetail? $companyDetail->phone: old('phone') )); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="website" class="form-label"> <?php echo e(__('index.website_url')); ?></label>
        <input type="url" class="form-control" id="website" name="website_url" value="<?php echo e(($companyDetail? $companyDetail->website_url: old('website_url') )); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-6 mb-4">
        <label for="weekend" class="form-label"> <?php echo e(__('index.check_office_off_days')); ?>  </label><br>
        <?php $__currentLoopData = \App\Helpers\AttendanceHelper::WEEK_DAY_IN_NEPALI; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <input type="checkbox" id="<?php echo e(\App\Helpers\AppHelper::ifDateInBsEnabled() ? $value['np'] : $value['en']); ?>" name="weekend[]" value="<?php echo e($key); ?>"
            <?php if($companyDetail && !is_null($companyDetail->weekend)): ?>
                <?php $__currentLoopData = $companyDetail->weekend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $datum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($datum == $key ? 'checked' : ''); ?>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            >
            <label for="weekends"> <?php echo e($value['en']); ?></label><br>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    
    
    
    
    
    
    

    <div class="col-lg-6 mb-4">
        <label for="upload" class="form-label"><?php echo e(__('index.upload_logo')); ?></label>
        <input class="form-control" type="file" id="upload" name="logo" >
        <?php if(($companyDetail && $companyDetail->logo)): ?>
            <img  src="<?php echo e(asset(\App\Models\Company::UPLOAD_PATH.$companyDetail->logo)); ?>"
                  alt=""  style="object-fit: contain" class="mt-3 ht-150 wd-150"
            >
        <?php endif; ?>
    </div>

    <!-- <div class="col-lg-6 mb-3">
        <?php if(($companyDetail && $companyDetail->logo)): ?>
        <img  src="<?php echo e(asset(\App\Models\Company::UPLOAD_PATH.$companyDetail->logo)); ?>"
                  alt=""  style="object-fit: contain" class="mt-3 ht-150 wd-150"
            >
        <?php endif; ?>
    </div> -->

    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['create_company','edit_company'])): ?>
        <div class="col-lg-6 mb-4 text-start">
            <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> <?php echo e($companyDetail ? __('index.update') : __('index.save')); ?> <?php echo e(__('index.company')); ?></button>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/company/form.blade.php ENDPATH**/ ?>