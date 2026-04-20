<nav class="page-breadcrumb d-flex align-items-center justify-content-between">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo app('translator')->get('index.dashboard'); ?></a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.qr.index')); ?>"><?php echo app('translator')->get('index.qr_section'); ?></a></li>
    </ol>

    <?php echo $__env->yieldContent('button'); ?>
</nav>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/qr/common/breadcrumb.blade.php ENDPATH**/ ?>