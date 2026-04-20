
<div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
            </div>
            <div class="modal-body">
                <div class="support-main">
                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong><?php echo e(__('index.query_by')); ?>:</strong> <p class="creator"> </p>
                    </div>

                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong><?php echo e(__('index.status')); ?> :</strong> <p class="status"> </p>
                    </div>

                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong><?php echo e(__('index.branch')); ?>:</strong> <p class="branch"> </p>
                    </div>

                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong><?php echo e(__('index.department_support_requested_from')); ?>:</strong> <p class="department"></p>
                    </div>

                    <div class="support-list d-flex align-items-center border-bottom pb-3 mb-3 gap-2">
                        <strong><?php echo e(__('index.department_support_requested_to')); ?>:</strong> <p class="requested"></p>
                    </div>
                </div>
                <div class="support-list border-bottom pb-3 mb-3">
                    <strong><?php echo e(__('index.description')); ?>:</strong> <p class="description"> </p>
                </div>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('update_query_status')): ?>
                    <form class="forms-sample" id="statusChange" action=""  method="post" >
                    <?php echo method_field('PUT'); ?>
                    <?php echo csrf_field(); ?>
                    <div class="support-main">
                        <label for="" class="form-label d-block"><?php echo e(__('index.change_query_status')); ?></label>
                        <div class="support-list border-bottom pb-3 mb-3">

                            <select class="form-select form-select-lg" name="status" id="changeStatus" required>
                                <option value="" ><?php echo e(__('index.select_status')); ?></option>
                                <?php $__currentLoopData = \App\Models\Support::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($value != 'pending'): ?>
                                        <option value="<?php echo e($value); ?>"> <?php echo e(removeSpecialChars($value)); ?> </option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary submit"><?php echo e(__('index.update')); ?></button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/support/show.blade.php ENDPATH**/ ?>