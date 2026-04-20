<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['view_payroll_list','view_tada_list','view_salary_list','view_advance_salary_list','view_tax_report','salary_component','salary_group','ssf','pf','bonus','salary_tds','advance_salary_limit','overtime_setting','undertime_setting','payment_method'])): ?>
    <li class="nav-item  <?php echo e(request()->routeIs('admin.bonus.*') ||
                request()->routeIs('admin.advance-salaries.*') ||
                request()->routeIs('admin.employee-salaries.*')||
                request()->routeIs('admin.payroll.tax-report.*')||
                request()->routeIs('admin.employee-salary.payroll*')||
                 request()->routeIs('admin.salary-components.*') ||
                      request()->routeIs('admin.payment-methods.*') ||
                      request()->routeIs('admin.salary-groups.*') ||
                      request()->routeIs('admin.bonus.*') ||
                      request()->routeIs('admin.overtime.*')||
                      request()->routeIs('admin.ssf.*')||
                      request()->routeIs('admin.pf.*')||
                      request()->routeIs('admin.under-time.*')||
                      request()->routeIs('admin.salary-tds.*') ||
                      request()->routeIs('admin.tadas.*')

                ? 'active' : ''); ?>"
    >
        <a class="nav-link" data-bs-toggle="collapse"
           href="#payroll"
           data-href="#"
           role="button" aria-expanded="false" aria-controls="settings">
            <i class="link-icon" data-feather="gift"></i>
            <span class="link-title"> <?php echo e(__('index.payroll_management')); ?> </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.bonus.*') ||
                request()->routeIs('admin.advance-salaries.*') ||
                request()->routeIs('admin.employee-salaries.*')||
                request()->routeIs('admin.payroll.tax-report.*')||
                request()->routeIs('admin.employee-salary.payroll*')||
                 request()->routeIs('admin.salary-components.*') ||
                      request()->routeIs('admin.payment-methods.*') ||
                      request()->routeIs('admin.salary-groups.*') ||
                      request()->routeIs('admin.bonus.*') ||
                      request()->routeIs('admin.overtime.*')||
                      request()->routeIs('admin.ssf.*')||
                      request()->routeIs('admin.pf.*')||
                      request()->routeIs('admin.under-time.*')||
                      request()->routeIs('admin.salary-tds.*') ||
                      request()->routeIs('admin.tadas.*')
               ? '' : 'collapse'); ?> " id="payroll">

            <ul class="nav sub-menu">
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('view_payroll_list')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.employee-salary.payroll')); ?>"
                           data-href="<?php echo e(route('admin.employee-salary.payroll')); ?>"
                           class="nav-link  <?php echo e(request()->routeIs('admin.employee-salary.payroll*') ? 'active':''); ?>"><?php echo e(__('index.payroll')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['salary_component','salary_group','ssf','bonus','salary_tds','advance_salary_limit','overtime_setting','undertime_setting','payment_method'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.salary-components.index')); ?>"
                           data-href="<?php echo e(route('admin.salary-components.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.salary-components.*') ||
                      request()->routeIs('admin.payment-methods.*') ||
                      request()->routeIs('admin.salary-groups.*') ||
                      request()->routeIs('admin.bonus.*') ||
                      request()->routeIs('admin.overtime.*')||
                      request()->routeIs('admin.ssf.*')||
                      request()->routeIs('admin.pf.*')||
                      request()->routeIs('admin.under-time.*')||
                      request()->routeIs('admin.salary-tds.*')

                      ? 'active' : ''); ?>"><?php echo e(__('index.payroll_setting')); ?>

                        </a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('view_advance_salary_list')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.advance-salaries.index')); ?>"
                           data-href="<?php echo e(route('admin.advance-salaries.index')); ?>"
                           class="nav-link  <?php echo e(request()->routeIs('admin.advance-salaries.*') ? 'active':''); ?>"><?php echo e(__('index.advance_salary')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('view_salary_list')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.employee-salaries.index')); ?>"
                           data-href="<?php echo e(route('admin.employee-salaries.index')); ?>"
                           class="nav-link  <?php echo e(request()->routeIs('admin.employee-salaries.*') ? 'active':''); ?>"><?php echo e(__('index.employee_salary')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('view_tax_report')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.payroll.tax-report.index')); ?>"
                           data-href="<?php echo e(route('admin.payroll.tax-report.index')); ?>"
                           class="nav-link  <?php echo e(request()->routeIs('admin.payroll.tax-report.*') ? 'active':''); ?>"><?php echo e(__('index.tax_report')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('view_tada_list')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.tadas.index')); ?>"
                           data-href="<?php echo e(route('admin.tadas.index')); ?>"
                           class="nav-link  <?php echo e(request()->routeIs('admin.tadas.*') ? 'active':''); ?>"><?php echo e(__('index.tada')); ?></a>
                    </li>
                <?php endif; ?>


            </ul>
        </div>
    </li>
<?php endif; ?>

<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/section/partial/payroll.blade.php ENDPATH**/ ?>