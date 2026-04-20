

<div class="row align-items-center">
    <?php if(!isset(auth()->user()->branch_id)): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label"><?php echo e(__('index.branch')); ?> <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled><?php echo e(__('index.select_branch')); ?></option>
            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option
                    value="<?php echo e($value->id); ?>" <?php echo e(((isset($qrData) && $qrData->branch_id == $value->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $value->id)) ? 'selected' : ''); ?>>
                    <?php echo e(ucfirst($value->name)); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <?php endif; ?>


















    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label"> <?php echo app('translator')->get('index.title'); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="title" required name="title" value="<?php echo e((old('title') || isset($qrData) ? $qrData->title : '' )); ?>" autocomplete="off" placeholder="QR Title">
    </div>

    <div class="col-lg-4 col-md-6 mb-4 mt-lg-4 text-start">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> <?php echo e(isset($qrData) ? __('index.update') : __('index.create')); ?> <?php echo app('translator')->get('index.qr'); ?></button>
    </div>
</div>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/qr/common/form.blade.php ENDPATH**/ ?>