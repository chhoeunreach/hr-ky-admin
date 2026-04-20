
<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['list_office_time'])): ?>
    <li class="nav-item  <?php echo e(request()->routeIs('admin.office-times.*')  ? 'active' : ''); ?>    ">
        <a class="nav-link"   data-href="#" data-bs-toggle="collapse" href="#shiftManagement" role="button" aria-expanded="false" aria-controls="shiftManagment">
            <i class="link-icon" data-feather="watch"></i>
            <span class="link-title"> <?php echo e(__('index.shift_management')); ?> </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.office-times.*') ?'' : 'collapse'); ?> " id="shiftManagement">
             <ul class="nav sub-menu">

                 <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_office_time')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.office-times.index')); ?>"
                           data-href="<?php echo e(route('admin.office-times.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.office-times.*') ? 'active' : ''); ?>"><?php echo e(__('index.office_time')); ?> </a>
                    </li>
                 <?php endif; ?>

            </ul>
        </div>
    </li>
<?php endif; ?>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/partial/shiftManagement.blade.php ENDPATH**/ ?>