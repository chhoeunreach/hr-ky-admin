<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_notice')): ?>
    <li class="nav-item <?php echo e(request()->routeIs('admin.notices.*')  ? 'active' : ''); ?>">
        <a
            href="<?php echo e(route('admin.notices.index')); ?>"
            data-href="<?php echo e(route('admin.notices.index')); ?>"
            class="nav-link">
            <i class="link-icon" data-feather="calendar"></i>
            <span class="link-title"><?php echo e(__('index.notice')); ?></span>
        </a>
    </li>
<?php endif; ?>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/partial/notice.blade.php ENDPATH**/ ?>