<li class="nav-item <?php echo e(request()->routeIs('admin.dashboard.*')  ? 'active' : ''); ?> " >
    <a href="<?php echo e(route('admin.dashboard')); ?>"
       data-href="<?php echo e(route('admin.dashboard')); ?>"
       class="nav-link">
        <i class="link-icon" data-feather="box"></i>
        <span class="link-title"><?php echo e(__('index.dashboard')); ?></span>
    </a>
</li>
<?php /**PATH /var/www/hr-ky-admin/resources/views/admin/section/partial/dashboard.blade.php ENDPATH**/ ?>