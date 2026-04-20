<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_content')): ?>
    <li class="nav-item">
        <a href="<?php echo e(route('admin.static-page-contents.index')); ?>"
           data-href="<?php echo e(route('admin.static-page-contents.index')); ?>" class="nav-link">
            <i class="link-icon" data-feather="book"></i>
            <span class="link-title"><?php echo e(__('index.content_management')); ?></span>
        </a>
    </li>
<?php endif; ?>
<?php /**PATH /var/www/hr-ky-admin/resources/views/admin/section/partial/staticPageContent.blade.php ENDPATH**/ ?>