<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label"><?php echo e(__('index.company_name')); ?> <span style="color: red">*</span></label>
        <select class="form-select" id="exampleFormControlSelect1" name="company_id">
            <option selected value="<?php echo e(isset($company) ? $company->id : ''); ?>"><?php echo e(isset($company) ? $company->name : ''); ?></option>
        </select>
    </div>


    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label"><?php echo e(__('index.branch_name')); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" required name="name" value="<?php echo e(isset($branch) ? $branch->name : ''); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label"><?php echo e(__('index.branch_head')); ?></label>
        <select class="form-select" id="exampleFormControlSelect1" name="branch_head_id">
            <option value="" <?php echo e(!isset($branch) ? 'selected' : ''); ?> disabled><?php echo e(__('index.select_branch_head')); ?></option>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($user->id); ?>" <?php echo e(isset($branch) && $branch->branch_head_id  == $user->id ? 'selected' : ''); ?>><?php echo e(ucfirst($user->name)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="address" class="form-label"><?php echo e(__('index.address')); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="address" required name="address" value="<?php echo e(isset($branch) ? $branch->address : old('address')); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="phone" class="form-label"><?php echo e(__('index.phone_number')); ?> <span style="color: red">*</span></label>
        <input type="number" class="form-control" id="phone" required name="phone" value="<?php echo e(isset($branch) ? $branch->phone : old('phone')); ?>" autocomplete="off" placeholder="">
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_location_latitude" class="form-label"><?php echo e(__('index.branch_location_latitude')); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="branch_location_latitude" required name="branch_location_latitude" value="<?php echo e(isset($branch) ? $branch->branch_location_latitude : old('branch_location_latitude')); ?>" autocomplete="off" placeholder="<?php echo e(__('index.enter_branch_location_latitude')); ?>">
    </div>

     <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_location_longitude" class="form-label"><?php echo e(__('index.branch_location_longitude')); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="branch_location_longitude" required name="branch_location_longitude" value="<?php echo e(isset($branch) ? $branch->branch_location_longitude : old('branch_location_longitude')); ?>" autocomplete="off" placeholder="<?php echo e(__('index.enter_branch_location_longitude')); ?>">
    </div>

    <div class="col-lg-4 mb-4">
        <label for="exampleFormControlSelect1" class="form-label"><?php echo e(__('index.status')); ?></label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value="" <?php echo e(!isset($branch) ? 'selected' : ''); ?> disabled><?php echo e(__('index.select_status')); ?></option>
            <option value="1" <?php echo e(isset($branch) && $branch->is_active == 1 ? 'selected' : old('is_active')); ?>><?php echo e(__('index.active')); ?></option>
            <option value="0" <?php echo e(isset($branch) && $branch->is_active == 0 ? 'selected' : old('is_active')); ?>><?php echo e(__('index.inactive')); ?></option>
        </select>
    </div>

    <div class="col-lg-6 mb-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> <?php echo e(isset($branch) ? __('index.update') : __('index.create')); ?></button>
    </div>
</div>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/branch/common/form.blade.php ENDPATH**/ ?>