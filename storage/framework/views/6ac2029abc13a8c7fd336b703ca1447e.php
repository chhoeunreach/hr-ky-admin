<?php use App\Helpers\AppHelper; ?>
<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['view_company','list_branch','list_department','list_department'])): ?>
    <li class="nav-item  <?php echo e(request()->routeIs('admin.company.*') ||
                           request()->routeIs('admin.branch.*') ||
                           request()->routeIs('admin.departments.*') ||
                           request()->routeIs('admin.posts.*')
                        ? 'active' : ''); ?>   ">
        <a class="nav-link" data-bs-toggle="collapse"
           href="#company_management"
           data-href="#"
           role="button" aria-expanded="false" aria-controls="company">
            <i class="link-icon" data-feather="align-justify"></i>
            <span class="link-title"><?php echo e(__('index.company_management')); ?></span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.company.*') ||
                           request()->routeIs('admin.branch.*') ||
                           request()->routeIs('admin.departments.*') ||
                           request()->routeIs('admin.posts.*')   ?'' : 'collapse'); ?> " id="company_management">
            <ul class="nav sub-menu">
                <?php if(AppHelper::checkSuperAdmin()): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.company.index')); ?>"
                           data-href="<?php echo e(route('admin.company.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.company.*') ? 'active' : ''); ?>"><?php echo e(__('index.company')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if(AppHelper::checkSuperAdmin()): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.branch.index')); ?>"
                           data-href="<?php echo e(route('admin.branch.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.branch.*') ? 'active' : ''); ?>"><?php echo e(__('index.branch')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_department')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.departments.index')); ?>"
                           data-href="<?php echo e(route('admin.departments.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.departments.*') ? 'active' : ''); ?>"><?php echo e(__('index.department')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_post')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.posts.index')); ?>"
                           data-href="<?php echo e(route('admin.posts.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.posts.*') ? 'active' : ''); ?>"><?php echo e(__('index.post')); ?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </li>
<?php endif; ?>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/hr-ky-admin1/resources/views/admin/section/partial/company.blade.php ENDPATH**/ ?>