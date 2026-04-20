<?php if(isset($errors) && count($errors) > 0): ?>
    <div class="alert alert-danger">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <p><?php echo e($error); ?></p>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>


<?php if(Session::has('success')): ?>
    <div style="max-width: 100%" id="message"   class="d-flex justify-content-between alert alert-success <?php echo e(Session::has('success_important') ? 'alert-important': ''); ?> ">
        
        <?php if(Session::has('success_important')): ?>
        <?php endif; ?>
        <?php echo e(session('success')); ?>

        <button type="button" style="color:white !important;opacity: 1 !important;" class="close bg-dark border-0 rounded-1" data-dismiss="alert" aria-hidden="true">×</button>
    </div>
<?php endif; ?>

<?php if(Session::has('danger')): ?>
    <div style="max-width: 100%" id="message" class="d-flex justify-content-between alert alert-danger <?php echo e(Session::has('danger_important') ? 'alert-important': ''); ?>">
        
        <?php if(Session::has('danger_important')): ?>
        <?php endif; ?>
        <?php echo e(session('danger')); ?>

        <button type="button" style="color:white !important;opacity: 1 !important;" class="close bg-dark border-0 rounded-1" data-dismiss="alert" aria-hidden="true">×</button>
    </div>
<?php endif; ?>

<?php if(Session::has('info')): ?>
    <div style="max-width: 100%" id="message" class="d-flex justify-content-between alert alert-info <?php echo e(Session::has('info_important') ? 'alert-important': ''); ?>">
        
        <?php if(Session::has('info_important')): ?>
        <?php endif; ?>
        <?php echo e(session('info')); ?>

        <button type="button" style="color:white !important;opacity: 1 !important;" class="close bg-dark border-0 rounded-1" data-dismiss="alert" aria-hidden="true">×</button>
    </div>
<?php endif; ?>

<?php if(Session::has('warning')): ?>
    <div style="max-width: 100%" id="message" class="d-flex justify-content-between alert alert-warning <?php echo e(Session::has('warning_important') ? 'alert-important': ''); ?>">
        
        <?php if(Session::has('warning_important')): ?>
        <?php endif; ?>
        <?php echo e(session('warning')); ?>

        <button type="button" style="color:white !important;opacity: 1 !important;" class="close bg-dark border-0 rounded-1" data-dismiss="alert" aria-hidden="true">×</button>
    </div>
<?php endif; ?>
<script>
    setTimeout(function() {
        let elementToHide = document.getElementById('message');
        if (elementToHide) {
            elementToHide.style.display = 'none';
        }
    }, 2000);
</script>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/flash_message.blade.php ENDPATH**/ ?>