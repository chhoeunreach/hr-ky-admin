<?php use App\Helpers\AppHelper; ?>
<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any([
    'list_router',
    'list_nfc',
    'list_qr',
    'list_device',
    'attendance_setting'
])): ?>
    <li class="nav-item  <?php echo e(request()->routeIs('admin.routers.*') ||
                   request()->routeIs('admin.qr.*')||
                   request()->routeIs('admin.biometric-devices.*')||
                   request()->routeIs('admin.attendance-settings.*')||
                   request()->routeIs('admin.nfc.*')

                ? 'active' : ''); ?>"
    >
        <a class="nav-link" data-bs-toggle="collapse"
           href="#attendance_method"
           data-href="#"
           role="button" aria-expanded="false" aria-controls="settings">
            <i class="link-icon" data-feather="tool"></i>
            <span class="link-title"> <?php echo e(__('index.attendance_methods')); ?> </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.routers.*') ||
                      request()->routeIs('admin.qr.*') ||
                      request()->routeIs('admin.biometric-devices.*') ||
                      request()->routeIs('admin.attendance-settings.*') ||
                      request()->routeIs('admin.nfc.*')

                       ? '' : 'collapse'); ?> " id="attendance_method">

            <ul class="nav sub-menu">

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_device')): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.biometric-devices.index')); ?>"
                            data-href="<?php echo e(route('admin.biometric-devices.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.biometric-devices.*') ? 'active' : ''); ?>"><?php echo e(__('index.biometric_device')); ?>

                        </a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_router')): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.routers.index')); ?>"
                            data-href="<?php echo e(route('admin.routers.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.routers.*') ? 'active' : ''); ?>"><?php echo e(__('index.routers')); ?>

                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_nfc')): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.nfc.index')); ?>"
                            data-href="<?php echo e(route('admin.nfc.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.nfc.*') ? 'active' : ''); ?>"><?php echo e(__('index.nfc')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_qr')): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.qr.index')); ?>"
                            data-href="<?php echo e(route('admin.qr.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.qr.*') ? 'active' : ''); ?>"><?php echo e(__('index.qr')); ?></a>
                    </li>

                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_setting')): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.attendance-settings.index')); ?>"
                            data-href="<?php echo e(route('admin.attendance-settings.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.attendance-settings.*') ? 'active' : ''); ?>"><?php echo e(__('index.attendance_settings')); ?></a>
                    </li>

                <?php endif; ?>


            </ul>
        </div>
    </li>
<?php endif; ?>


<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any([
    'role_permission',
    'general_setting',
    'app_setting',
    'feature_control',
    'fiscal_year',
    'payment_currency',
    'notification',
    'theme_setting'
])): ?>
    <li class="nav-item  <?php echo e(request()->routeIs('admin.roles.*') ||
                      request()->routeIs('admin.general-settings.*') ||
                      request()->routeIs('admin.app-settings.*') ||
                      request()->routeIs('admin.notifications.*')||
                      request()->routeIs('admin.payment-currency.*')||
                      request()->routeIs('admin.fiscal_year.*')||
                      request()->routeIs('admin.theme-color-setting.*')||
                      request()->routeIs('admin.feature.index')
                ? 'active' : ''); ?>"
    >
        <a class="nav-link" data-bs-toggle="collapse"
           href="#setting"
           data-href="#"
           role="button" aria-expanded="false" aria-controls="settings">
            <i class="link-icon" data-feather="settings"></i>
            <span class="link-title"> <?php echo e(__('index.settings')); ?> </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.roles.*') ||
                      request()->routeIs('admin.general-settings.*') ||
                      request()->routeIs('admin.app-settings.*') ||
                      request()->routeIs('admin.notifications.*')||
                      request()->routeIs('admin.payment-currency.*')||

                      request()->routeIs('admin.fiscal_year.*')||
                      request()->routeIs('admin.theme-color-setting.*')||
                      request()->routeIs('admin.feature.index')

                       ? '' : 'collapse'); ?> " id="setting">

            <ul class="nav sub-menu">
                <?php if(AppHelper::checkSuperAdmin()): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.roles.index')); ?>"
                            data-href="<?php echo e(route('admin.roles.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.roles.*') ? 'active' : ''); ?>"><?php echo e(__('index.roles_permissions')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if(AppHelper::checkSuperAdmin()): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.general-settings.index')); ?>"
                            data-href="<?php echo e(route('admin.general-settings.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.general-settings.*') ? 'active' : ''); ?>"><?php echo e(__('index.general_settings')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if(AppHelper::checkSuperAdmin()): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.app-settings.index')); ?>"
                            data-href="<?php echo e(route('admin.app-settings.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.app-settings.*') ? 'active' : ''); ?>"><?php echo e(__('index.app_settings')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('notification')): ?>
                    <li class="nav-item">
                        <a
                            href="<?php echo e(route('admin.notifications.index')); ?>"
                            data-href="<?php echo e(route('admin.notifications.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.notifications.*') ? 'active' : ''); ?>"><?php echo e(__('index.notifications')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('payment_currency')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.payment-currency.*')  ? 'active' : ''); ?>">
                        <a
                            href="<?php echo e(route('admin.payment-currency.index')); ?>"
                            data-href="<?php echo e(route('admin.payment-currency.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.payment-currency.*') ? 'active' : ''); ?>"> <?php echo e(__('index.payment_currency')); ?></a>
                    </li>

                <?php endif; ?>
                <?php if(AppHelper::checkSuperAdmin()): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.feature.index')  ? 'active' : ''); ?>">
                        <a
                            href="<?php echo e(route('admin.feature.index')); ?>"
                            data-href="<?php echo e(route('admin.feature.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.feature.index') ? 'active' : ''); ?>"> <?php echo e(__('index.feature_control')); ?></a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('fiscal_year')): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.fiscal_year.*')  ? 'active' : ''); ?>">
                        <a
                            href="<?php echo e(route('admin.fiscal_year.index')); ?>"
                            data-href="<?php echo e(route('admin.fiscal_year.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.fiscal_year.*') ? 'active' : ''); ?>"> <?php echo e(__('index.fiscal_year')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if(AppHelper::checkSuperAdmin()): ?>
                    <li class="nav-item <?php echo e(request()->routeIs('admin.theme-color-setting.*')  ? 'active' : ''); ?>">
                        <a
                            href="<?php echo e(route('admin.theme-color-setting.index')); ?>"
                            data-href="<?php echo e(route('admin.theme-color-setting.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.theme-color-setting.*') ? 'active' : ''); ?>"> <?php echo e(__('index.theme_color')); ?></a>
                    </li>
                <?php endif; ?>


            </ul>
        </div>
    </li>
<?php endif; ?>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/partial/setting.blade.php ENDPATH**/ ?>