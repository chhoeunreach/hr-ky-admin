<nav class="page-breadcrumb d-flex align-items-center justify-content-between">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo app('translator')->get('index.dashboard'); ?></a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.roles.index')); ?>"><?php echo app('translator')->get('index.role_section'); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $__env->yieldContent('action'); ?></li>
    </ol>

    <?php echo $__env->yieldContent('button'); ?>

</nav>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/role/common/breadcrumb.blade.php ENDPATH**/ ?>