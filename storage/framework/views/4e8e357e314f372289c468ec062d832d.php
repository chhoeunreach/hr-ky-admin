
<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['list_employee','list_logout_request'])): ?>
    <li class="nav-item  <?php echo e(request()->routeIs('admin.employees.*') ||
                           request()->routeIs('admin.employee.log') ||
                           request()->routeIs('admin.logout-requests.*')
                        ? 'active' : ''); ?>   ">
        <a data-href="#"
           class="nav-link"
           data-bs-toggle="collapse"
           href="#employee_management"
           role="button"
           aria-expanded="false"
           aria-controls="company">
            <i class="link-icon" data-feather="users"></i>
            <span class="link-title"><?php echo e(__('index.employee_management')); ?></span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>

        <div class="<?php echo e(request()->routeIs('admin.employees.*') ||
                         request()->routeIs('admin.employee.log') ||
                            request()->routeIs('admin.logout-requests.*')?'' : 'collapse'); ?>"  id="employee_management">
            <ul class="nav sub-menu">
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_employee')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.employees.index')); ?>"
                           data-href="<?php echo e(route('admin.employees.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.employees.*') ? 'active' : ''); ?>"><?php echo e(__('index.employees')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.employee.log')); ?>"
                           data-href="<?php echo e(route('admin.employee.log')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.employee.log') ? 'active' : ''); ?>"> Location Logs</a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_logout_request')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.logout-requests.index')); ?>"
                           data-href="<?php echo e(route('admin.logout-requests.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.logout-requests.*') ? 'active' : ''); ?>"><?php echo e(__('index.logout_requests')); ?></a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </li>
<?php endif; ?>



<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/hr-ky-admin1/resources/views/admin/section/partial/user.blade.php ENDPATH**/ ?>