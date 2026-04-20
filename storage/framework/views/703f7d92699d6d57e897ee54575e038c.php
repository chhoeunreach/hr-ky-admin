<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['award_type_list','award_list','termination_type_list','list_termination','list_promotion','list_transfer'])): ?>
    <li class="nav-item <?php echo e(request()->routeIs('admin.award-types.*') || request()->routeIs('admin.awards.*') || request()->routeIs('admin.holidays.*') || request()->routeIs('admin.termination-types.*')
 || request()->routeIs('admin.termination.*') || request()->routeIs('admin.resignation.*') || request()->routeIs('admin.warning.*') || request()->routeIs('admin.complaint.*')|| request()->routeIs('admin.promotion.*')
                      || request()->routeIs('admin.transfer.*')  ? 'active' : ''); ?> ">
        <a class="nav-link" data-bs-toggle="collapse" href="#awards" data-href="#" role="button" aria-expanded="false"
           aria-controls="awards">
            <i class="link-icon" data-feather="user-plus"></i>
            <span class="link-title"><?php echo e(__('index.hr_admin_setup')); ?></span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.award-types.*') || request()->routeIs('admin.awards.*') || request()->routeIs('admin.holidays.*') || request()->routeIs('admin.termination-types.*')
 || request()->routeIs('admin.termination.*') || request()->routeIs('admin.resignation.*') || request()->routeIs('admin.warning.*') || request()->routeIs('admin.complaint.*')|| request()->routeIs('admin.promotion.*')
                || request()->routeIs('admin.transfer.*')   ?'' : 'collapse'); ?>" id="awards">
            <ul class="nav sub-menu">
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('award_list')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.awards.*') || request()->routeIs('admin.award-types.*')
                        ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('admin.awards.index')); ?>"
                           data-href="<?php echo e(route('admin.awards.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.awards.*') || request()->routeIs('admin.award-types.*') ? 'active' : ''); ?>"><?php echo e(__('index.awards')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_termination')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.termination.*') || request()->routeIs('admin.termination-types.*')
                        ? 'active' : ''); ?> ">
                        <a href="<?php echo e(route('admin.termination.index')); ?>"
                           data-href="<?php echo e(route('admin.termination.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.termination.*') || request()->routeIs('admin.termination-types.*') ? 'active' : ''); ?>"><?php echo e(__('index.termination')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_resignation')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.resignation.*')
                        ? 'active' : ''); ?> ">
                        <a class="nav-link <?php echo e(request()->routeIs('admin.resignation.*') ? 'active' : ''); ?>"
                           href="<?php echo e(route('admin.resignation.index')); ?>"
                           data-href="<?php echo e(route('admin.resignation.index')); ?>">
                            <?php echo e(__('index.resignation')); ?>

                        </a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_warning')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.warning.*')
                        ? 'active' : ''); ?> ">
                        <a class="nav-link <?php echo e(request()->routeIs('admin.warning.*') ? 'active' : ''); ?>"
                           href="<?php echo e(route('admin.warning.index')); ?>"
                           data-href="<?php echo e(route('admin.warning.index')); ?>">
                            <?php echo e(__('index.warning')); ?>

                        </a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_complaint')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.complaint.*')
                        ? 'active' : ''); ?> ">
                        <a class="nav-link <?php echo e(request()->routeIs('admin.complaint.*') ? 'active' : ''); ?>"
                           href="<?php echo e(route('admin.complaint.index')); ?>"
                           data-href="<?php echo e(route('admin.complaint.index')); ?>">
                            <?php echo e(__('index.complaint')); ?>

                        </a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_promotion')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.promotion.*')
                        ? 'active' : ''); ?> ">
                        <a class="nav-link <?php echo e(request()->routeIs('admin.promotion.*') ? 'active' : ''); ?>"
                           data-href="<?php echo e(route('admin.promotion.index')); ?>"
                           href="<?php echo e(route('admin.promotion.index')); ?>">
                            <?php echo e(__('index.promotion')); ?>

                        </a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_transfer')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.transfer.*')
                        ? 'active' : ''); ?> ">
                        <a class="nav-link <?php echo e(request()->routeIs('admin.transfer.*') ? 'active' : ''); ?>"
                           data-href="<?php echo e(route('admin.transfer.index')); ?>"
                           href="<?php echo e(route('admin.transfer.index')); ?>"
                        >
                            <?php echo e(__('index.transfer')); ?>

                        </a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_holiday')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.holidays.*')  ? 'active' : ''); ?>">
                        <a
                            href="<?php echo e(route('admin.holidays.index')); ?>"
                            data-href="<?php echo e(route('admin.holidays.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.holidays.*') ? 'active' : ''); ?>">
                            <?php echo e(__('index.holidays')); ?>

                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </li>
<?php endif; ?>
<?php /**PATH /var/www/hr-ky-admin/resources/views/admin/section/partial/hrAdminSetup.blade.php ENDPATH**/ ?>