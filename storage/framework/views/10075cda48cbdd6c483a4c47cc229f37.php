
<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['view_query_list'])): ?>
    <li class="nav-item <?php echo e(request()->routeIs('admin.supports.*')  ? 'active' : ''); ?>">
        <a
            href="<?php echo e(route('admin.supports.index')); ?>"
            data-href="<?php echo e(route('admin.supports.index')); ?>"
            class="nav-link">
            <i class="link-icon" data-feather="help-circle"></i>
            <span class="link-title"><?php echo e(__('index.support')); ?></span>
        </a>
    </li>
<?php endif; ?>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/partial/ticket.blade.php ENDPATH**/ ?>