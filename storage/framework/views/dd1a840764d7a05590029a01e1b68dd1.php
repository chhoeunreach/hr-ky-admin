

<div class="modal fade" id="statusUpdate" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('index.time_leave_status_update')); ?></h5>
            </div>
            <div class="modal-body">
                <div class="container">
                    <form class="forms-sample" id="updateLeaveStatus" action=""  method="post" >
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('put'); ?>
                        <div class="row">
                            <label for="exampleFormControlSelect1" class="form-label"><?php echo e(__('index.status')); ?> </label>
                            <div class="col-lg-12 mb-3">
                                <select class="form-select" id="status" name="status">
                                    <option value="<?php echo e(\App\Enum\LeaveStatusEnum::approved->value); ?>" ><?php echo e(__('index.approve')); ?></option>
                                    <option value="<?php echo e(\App\Enum\LeaveStatusEnum::rejected->value); ?>" ><?php echo e(__('index.reject')); ?></option>
                                </select>
                            </div>

                            <label for="exampleFormControlSelect1" class="form-label"><?php echo e(__('index.admin_remark')); ?></label>
                            <div class="col-lg-12 mb-3">
                                <textarea class="form-select" id="remark" minlength="10" name="admin_remark" rows="3" >
                                </textarea>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" id="submit-btn" class="btn btn-primary btn-xs"><?php echo e(__('index.submit')); ?> </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>





<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/timeLeaveRequest/common/form-model.blade.php ENDPATH**/ ?>