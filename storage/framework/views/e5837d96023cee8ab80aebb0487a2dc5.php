<div class="modal fade" id="nightAttendanceForm" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('index.edit_attendance')); ?></h5>
            </div>
            <div class="modal-body">
                <div class="container">
                    <form class="forms-sample" id="editNightAttendance" action=""  method="post">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('put'); ?>
                        <div class="row">
                            <label for="nightCheckIn" class="form-label"><?php echo e(__('index.check_in_at')); ?></label>
                            <div class="col-lg-12 mb-3">
                                <input type="datetime-local" class="form-select" id="nightCheckIn" name="night_checkin" value="" />
                            </div>
                            <label for="nightCheckOut" class="form-label"><?php echo e(__('index.check_out_at')); ?></label>
                            <div class="col-lg-12 mb-3">
                                <input type="datetime-local" class="form-select" id="nightCheckOut" name="night_checkout" value="" />
                            </div>
                            <label for="nightEditRemark" class="form-label"><?php echo e(__('index.admin_edit_remark')); ?></label>
                            <div class="col-lg-12 mb-3">
                                <textarea class="form-select" id="nightEditRemark" minlength="10" name="edit_remark" required rows="3"></textarea>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-xs"><?php echo e(__('index.submit')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/attendance/common/edit-night-attendance-form.blade.php ENDPATH**/ ?>