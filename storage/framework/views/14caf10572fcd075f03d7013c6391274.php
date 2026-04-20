<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check(['list_attendance'])): ?>
    <li class="nav-item  <?php echo e(request()->routeIs('admin.attendances.*') || request()->routeIs('admin.attendance.*')  ? 'active' : ''); ?>   ">
        <a data-href="#"
           class="nav-link"
           data-bs-toggle="collapse"
           href="#attendance_management"
           role="button"
           aria-expanded="false"
           aria-controls="company">
            <i class="link-icon" data-feather="user-check"></i>
            <span class="link-title"><?php echo e(__('index.attendance_section')); ?></span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>

        <div class="<?php echo e(request()->routeIs('admin.attendances.*') || request()->routeIs('admin.attendance.*')  ? '' : 'collapse'); ?>"  id="attendance_management">
            <ul class="nav sub-menu">

                <li class="nav-item">
                    <a href="<?php echo e(route('admin.attendances.index')); ?>"
                       data-href="<?php echo e(route('admin.attendances.index')); ?>"
                       class="nav-link <?php echo e(request()->routeIs('admin.attendances.*') ? 'active' : ''); ?>"><?php echo e(__('index.attendance')); ?></a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo e(route('admin.attendance.log')); ?>"
                       data-href="<?php echo e(route('admin.attendance.log')); ?>"
                       class="nav-link <?php echo e(request()->routeIs('admin.attendance.log') ? 'active' : ''); ?>"><?php echo e(__('index.attendance_logs')); ?></a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.attendance.export')); ?>"
                       data-href="<?php echo e(route('admin.attendance.export')); ?>"
                       class="nav-link <?php echo e(request()->routeIs('admin.attendance.export') ? 'active' : ''); ?>"><?php echo e(__('index.attendance_report')); ?></a>
                </li>

            </ul>
        </div>
    </li>
<?php endif; ?>



<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/hr-ky-admin1/resources/views/admin/section/partial/attendance.blade.php ENDPATH**/ ?>