<div class="row align-items-center">
    <?php if(!isset(auth()->user()->branch_id)): ?>
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label"><?php echo e(__('index.branch')); ?> <span style="color: red">*</span></label>
        <select class="form-select"  name="branch_id" id="branch_id" required>
            <option <?php echo e(!isset($departmentsDetail) ? 'selected' : ''); ?> disabled><?php echo e(__('index.select_branch')); ?></option>

            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($branch->id); ?>" <?php echo e(( isset($departmentsDetail) && $departmentsDetail->branch_id  ==
                    $branch->id)  ? 'selected' : ''); ?>><?php echo e(ucfirst($branch->name)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </select>
    </div>
    <?php endif; ?>
    <?php if(!auth('admin')->check() && auth()->check()): ?>
        <input type="hidden" disabled readonly id="branch_id" name="branch_id" value="<?php echo e(auth()->user()->branch_id); ?>">
    <?php endif; ?>
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="name" class="form-label"><?php echo e(__('index.department_name')); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="dept_name" required name="dept_name" value="<?php echo e(isset($departmentsDetail) ? $departmentsDetail->dept_name : ''); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="dept_head_id" class="form-label"><?php echo e(__('index.department_head')); ?></label>
        <select class="form-select" id="dept_head_id" name="dept_head_id">
            <?php if(isset($departmentsDetail)): ?>
                <?php $__currentLoopData = $filteredUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php echo e($user->id ==  $departmentsDetail->dept_head_id ? 'selected' : ''); ?>>
                        <?php echo e(ucfirst($user->name)); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <option selected  disabled><?php echo e(__('index.select_department_head')); ?></option>
            <?php endif; ?>
        </select>
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="address" class="form-label"><?php echo e(__('index.address')); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="address" required name="address" value="<?php echo e(isset($departmentsDetail) ? $departmentsDetail->address : old('address')); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="number" class="form-label"><?php echo e(__('index.phone_number')); ?> <span style="color: red">*</span></label>
        <input type="number" class="form-control" id="phone" required name="phone" value="<?php echo e(isset($departmentsDetail) ? $departmentsDetail->phone : old('phone')); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="status" class="form-label"><?php echo e(__('index.status')); ?></label>
        <select class="form-select" id="status" name="is_active">
            <option value="" <?php echo e(!isset($departmentsDetail) ? 'selected' : ''); ?> disabled><?php echo e(__('index.select_status')); ?></option>
            <option value="1" <?php echo e(isset($departmentsDetail) && $departmentsDetail->is_active == 1 ? 'selected' : old('is_active')); ?>><?php echo e(__('index.active')); ?></option>
            <option value="0" <?php echo e(isset($departmentsDetail) && $departmentsDetail->is_active == 0 ? 'selected' : old('is_active')); ?>><?php echo e(__('index.inactive')); ?></option>
        </select>
    </div>

    <div class="col-lg-12 mb-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> <?php echo e(isset($departmentsDetail) ? __('index.update_department') : __('index.create_department')); ?></button>
    </div>
</div>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/department/common/form.blade.php ENDPATH**/ ?>