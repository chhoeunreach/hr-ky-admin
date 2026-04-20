
<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['list_leave_type','list_leave_request','access_admin_leave','list_leave_approval','time_leave_list'])): ?>
    <li class="nav-item <?php echo e(request()->routeIs('admin.leaves.*') ||
                            request()->routeIs('admin.time-leave-request.*') ||
                            request()->routeIs('admin.leave-approval.*') ||
                        request()->routeIs('admin.leave-request.*')
                        ? 'active' : ''); ?> ">
        <a class="nav-link" data-bs-toggle="collapse" href="#leaveMenu" data-href="#" role="button" aria-expanded="false"
           aria-controls="leaveMenu">
            <i class="link-icon" data-feather="pause-circle"></i>
            <span class="link-title"><?php echo e(__('index.leave')); ?></span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.leaves.*') ||
                            request()->routeIs('admin.time-leave-request.*') ||
                            request()->routeIs('admin.leave-approval.*') ||
                        request()->routeIs('admin.leave-request.*')
                   ?'' : 'collapse'); ?>" id="leaveMenu">
            <ul class="nav sub-menu">

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['list_leave_type','access_admin_leave'])): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.leaves.*')
                        ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('admin.leaves.index')); ?>"
                           data-href="<?php echo e(route('admin.leaves.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.leaves.*') ? 'active' : ''); ?>"><?php echo e(__('index.leave_types')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['list_leave_request','access_admin_leave'])): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.leave-request.*')
                        ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('admin.leave-request.index')); ?>"
                           data-href="<?php echo e(route('admin.leave-request.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.leave-request.*') ? 'active' : ''); ?>"><?php echo e(__('index.leave_request')); ?></a>
                    </li>
                    <?php endif; ?>
                    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('time_leave_list')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.time-leave-request.*')
                        ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('admin.time-leave-request.index')); ?>"
                           data-href="<?php echo e(route('admin.time-leave-request.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.time-leave-request.*') ? 'active' : ''); ?>"><?php echo e(__('index.time_leave_request')); ?></a>
                    </li>
                <?php endif; ?>
                    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_leave_approval')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.leave-approval.*')
                        ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('admin.leave-approval.index')); ?>"
                           data-href="<?php echo e(route('admin.leave-approval.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.leave-approval.*') ? 'active' : ''); ?>"><?php echo e(__('index.leave_approval')); ?></a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </li>
<?php endif; ?>

<?php /**PATH /var/www/hr-ky-admin/resources/views/admin/section/partial/leave.blade.php ENDPATH**/ ?>