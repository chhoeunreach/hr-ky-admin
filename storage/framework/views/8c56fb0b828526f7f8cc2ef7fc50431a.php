<?php if(\App\Helpers\AppHelper::checkSuperAdmin()): ?>
    <li class="nav-item  <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
        <a class="nav-link" data-bs-toggle="collapse"
           href="#user-management"
           data-href="#"
           role="button" aria-expanded="false" aria-controls="settings">
            <i class="link-icon" data-feather="user"></i>
            <span class="link-title"> <?php echo e(__('index.user_management')); ?> </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.users.*') ? '' : 'collapse'); ?> " id="user-management">

            <ul class="nav sub-menu">
                <li class="nav-item">
                    <a
                        href="<?php echo e(route('admin.users.index')); ?>"
                        data-href="<?php echo e(route('admin.users.index')); ?>"
                        class="nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>"><?php echo e(__('index.users')); ?></a>
                </li>
            </ul>
        </div>
    </li>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/hr-ky-admin1/resources/views/admin/section/partial/userManagement.blade.php ENDPATH**/ ?>