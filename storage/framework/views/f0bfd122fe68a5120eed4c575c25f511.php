












<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['view_project_list','view_task_list','view_client_list'])): ?>
    <li class="nav-item <?php echo e(request()->routeIs('admin.projects.*') || request()->routeIs('admin.clients.*') || request()->routeIs('admin.tasks.*')
                        ? 'active' : ''); ?> ">
        <a class="nav-link" data-bs-toggle="collapse" href="#projectMenu" data-href="#" role="button" aria-expanded="false"
           aria-controls="projectMenu">
            <i class="link-icon" data-feather="layout"></i>
            <span class="link-title"><?php echo e(__('index.project_management')); ?></span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.projects.*') || request()->routeIs('admin.clients.*') || request()->routeIs('admin.tasks.*')
                   ?'' : 'collapse'); ?>" id="projectMenu">
            <ul class="nav sub-menu">

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('view_project_list')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.projects.*')
                        ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('admin.projects.index')); ?>"
                           data-href="<?php echo e(route('admin.projects.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.projects.*') ? 'active' : ''); ?>"><?php echo e(__('index.project')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('view_task_list')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.tasks.*')
                        ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('admin.tasks.index')); ?>"
                           data-href="<?php echo e(route('admin.tasks.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.tasks.*') ? 'active' : ''); ?>"><?php echo e(__('index.tasks')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('view_client_list')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.clients.*')
                        ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('admin.clients.index')); ?>"
                           data-href="<?php echo e(route('admin.clients.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.clients.*') ? 'active' : ''); ?>"><?php echo e(__('index.clients')); ?></a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </li>
<?php endif; ?>

<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/partial/projectManagement.blade.php ENDPATH**/ ?>