

<div class="row">

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="name" class="form-label"> <?php echo app('translator')->get('index.role_name'); ?> <span style="color: red">*</span> </label>
        <input type="text" class="form-control" id="name" required name="name" value="<?php echo e(( isset($roleDetail) ? $roleDetail->name: '' )); ?>" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label"><?php echo app('translator')->get('index.authorize_backend_login'); ?></label>
        <select class="form-select" id="exampleFormControlSelect1" name="backend_login_authorize">
            <option value="" <?php echo e(isset($roleDetail) ? '':'selected'); ?> ><?php echo app('translator')->get('index.select_status'); ?></option>
            <option value="1" <?php echo e(isset($roleDetail) && ($roleDetail->backend_login_authorize ) == 1 ? 'selected': old('backend_login_authorize')); ?>><?php echo app('translator')->get('index.yes'); ?></option>
            <option value="0" <?php echo e(isset($roleDetail) && ($roleDetail->backend_login_authorize ) == 0 ? 'selected': old('backend_login_authorize')); ?>><?php echo app('translator')->get('index.no'); ?></option>
        </select>
    </div>


    <div class="col-lg-6 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label"><?php echo app('translator')->get('index.status'); ?></label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value=""  disabled><?php echo app('translator')->get('index.select-status'); ?></option>
            <option value="1" <?php echo e(isset($roleDetail) && ($roleDetail->is_active ) == 1 ? 'selected': old('is_active')); ?>><?php echo app('translator')->get('index.active'); ?></option>
            <option value="0" <?php echo e(isset($roleDetail) && ($roleDetail->is_active ) == 0 ? 'selected': old('is_active')); ?>><?php echo app('translator')->get('index.inactive'); ?></option>
        </select>
    </div>



    <div class="col-lg-6 col-md-6 text-start mb-4 mt-md-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> <?php echo e(isset($roleDetail)? __('index.update'): __('index.create')); ?> <?php echo app('translator')->get('index.role'); ?></button>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/hr-ky-admin1/resources/views/admin/role/common/form.blade.php ENDPATH**/ ?>