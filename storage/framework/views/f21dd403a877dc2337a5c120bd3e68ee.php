
<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_event')): ?>
    <li class="nav-item <?php echo e(request()->routeIs('admin.event.*')  ? 'active' : ''); ?>">
        <a
            href="<?php echo e(route('admin.event.index')); ?>"
            data-href="<?php echo e(route('admin.event.index')); ?>"
            class="nav-link">
            <i class="link-icon" data-feather="tv"></i>
            <span class="link-title"><?php echo e(__('index.event')); ?></span>
        </a>
    </li>
<?php endif; ?>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/partial/event.blade.php ENDPATH**/ ?>