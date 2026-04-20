<div class="row">
    <?php if(!isset(auth()->user()->branch_id)): ?>
    <div class="col-lg-6 col-md-6 mb-4">
        <label for="branch_id" class="form-label"><?php echo app('translator')->get('index.branch'); ?> <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option  <?php echo e(!isset($noticeDetail) || old('branch_id') ? 'selected': ''); ?>  disabled><?php echo e(__('index.select_branch')); ?>

            </option>
            <?php if(isset($companyDetail)): ?>
                <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($branch->id); ?>"
                        <?php echo e((isset($noticeDetail) && ($noticeDetail->branch_id ) == $branch->id) ? 'selected': ''); ?>>
                        <?php echo e(ucfirst($branch->name)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </select>
    </div>
    <?php endif; ?>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="title" class="form-label"><?php echo app('translator')->get('index.notice_title'); ?> <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="title" name="title" required value="<?php echo e(isset($noticeDetail) ? $noticeDetail->title : old('title')); ?>" autocomplete="off" placeholder="<?php echo app('translator')->get('index.notice_title'); ?>">
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="description" class="form-label"><?php echo app('translator')->get('index.notice_description'); ?> <span style="color: red">*</span></label>
        <textarea class="form-control"  name="description" id="tinymceExample" rows="7"><?php echo isset($noticeDetail) ? $noticeDetail->description : old('description'); ?></textarea>
    </div>

    <div class="col-lg-6">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <label for="employee" class="form-label"><?php echo app('translator')->get('index.notice_receiver'); ?> <span style="color: red">*</span></label>
                <br>
                <select class="col-md-12 form-select" id="notice" name="receiver[][notice_receiver_id]" multiple="multiple" required>

                </select>
                <div class="select-emp"><input class="mt-3" type="checkbox" id="checkbox"><?php echo app('translator')->get('index.all_employees'); ?></div>
            </div>

            <div class="col-lg-12 mb-4">
                <label for="is_active" class="form-label"><?php echo app('translator')->get('index.status'); ?> <span style="color: red">*</span></label>
                <select class="form-select" id="is_active" name="is_active" required>
                    <option value="" <?php echo e(isset($noticeDetail) || old('is_active') ? '' : 'selected'); ?> ><?php echo app('translator')->get('index.select_status'); ?></option>
                    <option value="1" <?php echo e(isset($noticeDetail) && ($noticeDetail->is_active || old('is_active')) == 1 ? 'selected' : ''); ?>><?php echo app('translator')->get('index.active'); ?></option>
                    <option value="0" <?php echo e(isset($noticeDetail) && ($noticeDetail->is_active || old('is_active')) == 0 ? 'selected' : ''); ?>><?php echo app('translator')->get('index.inactive'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="col-lg-12 mb-4">
        <button type="submit" class="btn btn-primary"><?php echo app('translator')->get('index.send_notice'); ?></button>
    </div>
</div>
<?php /**PATH /var/www/hr-ky-admin/resources/views/admin/notice/common/form.blade.php ENDPATH**/ ?>