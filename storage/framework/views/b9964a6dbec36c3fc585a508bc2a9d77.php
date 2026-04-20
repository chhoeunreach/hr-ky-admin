<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['list_type','list_assets','asset_return_list'])): ?>
    <li class="nav-item <?php echo e(request()->routeIs('admin.asset-types.*') || request()->routeIs('admin.assets.*') || request()->routeIs('admin.asset-return.*') || request()->routeIs('admin.asset-assignment.*')
                        ? 'active' : ''); ?> ">
        <a class="nav-link" data-bs-toggle="collapse" href="#assets" data-href="#" role="button" aria-expanded="false"
           aria-controls="assets">
            <i class="link-icon" data-feather="loader"></i>
            <span class="link-title"><?php echo e(__('index.asset_management')); ?></span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.asset-types.*') || request()->routeIs('admin.assets.*') || request()->routeIs('admin.asset-return.*') || request()->routeIs('admin.asset-assignment.*')
                   ?'' : 'collapse'); ?>" id="assets">
            <ul class="nav sub-menu">

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_type')): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.asset-types.index')); ?>"
                            data-href="<?php echo e(route('admin.asset-types.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.asset-types.*') ? 'active' : ''); ?>"><?php echo e(__('index.asset_types')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_assets')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.assets.index')); ?>"
                           data-href="<?php echo e(route('admin.assets.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.assets.*') || request()->routeIs('admin.asset-assignment.*') ? 'active' : ''); ?>"><?php echo e(__('index.assets')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('asset_return_list')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.asset-return.index')); ?>"
                           data-href="<?php echo e(route('admin.asset-return.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.asset-return.*') ? 'active' : ''); ?>"><?php echo e(__('index.asset_return')); ?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </li>
<?php endif; ?>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/partial/assetManagement.blade.php ENDPATH**/ ?>