

<div class="row">
    <?php if(!isset(auth()->user()->branch_id)): ?>
    <div class="col-lg-6 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label"><?php echo app('translator')->get('index.branch'); ?> <span style="color: red">*</span></label>
        <select class="form-select" id="exampleFormControlSelect1" name="branch_id" required>
            <option value="" <?php echo e(isset($routerDetail) ? '': 'selected'); ?>  disabled ><?php echo app('translator')->get('index.select_branch'); ?></option>
            <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($branch->id); ?>" <?php echo e((isset($routerDetail) && ($routerDetail->branch_id ) == $branch->id)  ? 'selected': old('branch_id')); ?>> <?php echo e(ucfirst($branch->name)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <?php endif; ?>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="router_ssid" class="form-label"><?php echo app('translator')->get('index.router_bssid'); ?>  <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="router_ssid" required name="router_ssid" value="<?php echo e(( isset($routerDetail) ? ($routerDetail->router_ssid): old('router_ssid') )); ?>" autocomplete="off" placeholder="00:00:00:00:00">
    </div>

    <div class="col-lg-6 text-start">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="<?php echo e(isset($routerDetail)? 'edit-2':'plus'); ?>"></i> <?php echo e(isset($routerDetail)? __('index.update'):__('index.add')); ?> <?php echo app('translator')->get('index.router'); ?></button>
    </div>
</div>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/router/common/form.blade.php ENDPATH**/ ?>