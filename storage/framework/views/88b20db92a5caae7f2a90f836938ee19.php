<nav class="page-breadcrumb d-flex align-items-center justify-content-between">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('index.dashboard')); ?></a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.departments.index')); ?>"><?php echo e(__('index.department_section')); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo e($title); ?></li>
    </ol>

    <?php echo $__env->yieldContent('button'); ?>
</nav>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/department/common/breadcrumb.blade.php ENDPATH**/ ?>