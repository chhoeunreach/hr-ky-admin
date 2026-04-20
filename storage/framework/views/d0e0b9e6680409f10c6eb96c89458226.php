<?php use App\Models\User; ?>


<?php $__env->startSection('title', __('index.employees_title')); ?>

<?php $__env->startSection('action', __('index.employees_action')); ?>

<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_employee')): ?>
        <div class="float-md-end d-flex align-items-center gap-2 justify-content-center">

            <a href="<?php echo e(route('admin.employees.create')); ?>">
                <button class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="link-icon" data-feather="plus"></i><?php echo e(__('index.add_employee')); ?>

                </button>
            </a>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.employees.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.employee_lists')); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.employees.index')); ?>" id="employeeFilterForm" method="get">
                <div class="row align-items-center">
                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                            <select class="form-control" id="branch" name="branch_id">
                                <option selected disabled><?php echo e(__('index.select_branch')); ?></option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        <?php echo e(($filterParameters['branch_id'] == $branch->id) ? 'selected' : ''); ?> value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <select class="form-control" id="department" name="department_id">
                            <option selected disabled><?php echo e(__('index.select_department')); ?></option>
                        </select>
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="text" placeholder="<?php echo e(__('index.employee_name')); ?>" id="employeeName"
                               name="employee_name" value="<?php echo e($filterParameters['employee_name']); ?>"
                               class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="text" placeholder="<?php echo e(__('index.employee_email')); ?>" id="email" name="email"
                               value="<?php echo e($filterParameters['email']); ?>" class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="number" placeholder="<?php echo e(__('index.employee_phone')); ?>" id="phone" name="phone"
                               value="<?php echo e($filterParameters['phone']); ?>" class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <select class="form-control" id="per_page" name="per_page">
                            <option value="25" <?php echo e((string)($filterParameters['per_page'] ?? '') === '25' ? 'selected' : ''); ?>>25</option>
                            <option value="50" <?php echo e((string)($filterParameters['per_page'] ?? '') === '50' ? 'selected' : ''); ?>>50</option>
                            <option value="100" <?php echo e((string)($filterParameters['per_page'] ?? '') === '100' ? 'selected' : ''); ?>>100</option>
                            <option value="all" <?php echo e((string)($filterParameters['per_page'] ?? '') === 'all' ? 'selected' : ''); ?>>All</option>
                        </select>
                    </div>

                    <div class="col-xxl-4 col-xl-4 col-md-6">
                        <div class="d-md-flex align-items-center gap-2">
                            <button type="submit" value="filter" class="btn btn-block btn-success mb-4"><?php echo e(__('index.filter')); ?></button>

                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_employee')): ?>
                            <button type="button" id="export_employee" data-href="<?php echo e(route('admin.employees.index')); ?>" value="export"
                                            class="btn btn-block btn-secondary mb-4"><?php echo e(__('index.export_csv')); ?></button>

                            <?php endif; ?>
                            <a class="btn btn-block btn-primary mb-4" href="<?php echo e(route('admin.employees.index')); ?>"><?php echo e(__('index.reset')); ?></a>
                        </div>
                    </div>

                </div>


            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.employee_lists')); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="employeeTable" class="table">
                        <thead>
                        <tr>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_detail_employee')): ?>
                                <th>#</th>
                            <?php endif; ?>
                            <th class="text-center"><?php echo e(__('index.employee_code')); ?></th>
                            <th><?php echo e(__('index.full_name')); ?></th>
                            <th><?php echo e(__('index.address')); ?></th>
                            <th class="text-center"><?php echo e(__('index.email')); ?></th>
                            <th class="text-center"><?php echo e(__('index.designation')); ?></th>
                            <th class="text-center"><?php echo e(__('index.department')); ?></th>
                            <th class="text-center"><?php echo e(__('index.role')); ?></th>
                            <th class="text-center"><?php echo e(__('index.shift')); ?></th>
                            <th class="text-center"><?php echo e(__('index.holiday_check_in')); ?></th>
                            <th class="text-center"><?php echo e(__('index.workplace')); ?></th>
                            <th class="text-center"><?php echo e(__('index.is_active')); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_employee','delete_employee','change_password','force_logout'])): ?>
                                <th class="text-center"><?php echo e(__('index.action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <?php
                            $changeColor = [
                                0 => 'success',
                                1 => 'primary',
                            ]
                            ?>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_detail_employee')): ?>
                                    <td>
                                        <a href="<?php echo e(route('admin.employees.show', $value->id)); ?>"
                                           id="showOfficeTimeDetail">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                                <td class="text-center"><?php echo e($value->employee_code ?: 'N/A'); ?></td>
                                <td>
                                    <p><?php echo e(ucfirst($value->name)); ?></p>
                                    <small class="text-muted">(<?php echo e(ucfirst($value->role ? $value->role->name : 'N/A')); ?>

                                        )</small>
                                </td>
                                <td><?php echo e(ucfirst($value->address)); ?></td>
                                <td class="text-center"><?php echo e($value->email); ?></td>
                                <td class="text-center"><?php echo e($value->post ? ucfirst($value->post->post_name) : 'N/A'); ?></td>
                                <td class="text-center"><?php echo e($value->department ? ucfirst($value->department->dept_name) : 'N/A'); ?></td>
                                <td class="text-center"><?php echo e($value->role ? ucfirst($value->role->name) : 'N/A'); ?></td>
                                <td class="text-center"><?php echo e($value->officeTime ? ucfirst($value->officeTime->shift) : 'N/A'); ?></td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleHolidayCheckIn"
                                               href="<?php echo e(route('admin.employees.toggle-holiday-checkin', $value->id)); ?>"
                                               type="checkbox" <?php echo e($value->allow_holiday_check_in == 1 ? 'checked' : ''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <a class="changeWorkPlace btn btn-<?php echo e($changeColor[$value->workspace_type]); ?> btn-xs"
                                       data-href="<?php echo e(route('admin.employees.change-workspace', $value->id)); ?>"
                                       title="Change workspace">
                                        <?php echo e($value->workspace_type == User::FIELD ? 'Field' : 'Office'); ?>

                                    </a>
                                </td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="<?php echo e(route('admin.employees.toggle-status', $value->id)); ?>"
                                               type="checkbox" <?php echo e($value->is_active == 1 ? 'checked' : ''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_employee','delete_employee','change_password','force_logout'])): ?>
                                    <td class="text-center">
                                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown"
                                           role="button"
                                           data-bs-toggle="dropdown"
                                           aria-haspopup="true"
                                           aria-expanded="false"
                                           title="<?php echo e(__('index.action')); ?>"
                                        >
                                        </a>

                                        <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                                            <ul class="list-unstyled p-1 mb-0">
                                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_employee')): ?>
                                                    <li class="dropdown-item py-2">
                                                        <a href="<?php echo e(route('admin.employees.edit', $value->id)); ?>">
                                                            <button
                                                                class="btn btn-primary btn-xs"><?php echo e(__('index.edit_detail')); ?></button>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_employee')): ?>
                                                    <?php if( (isset(auth()->user()->id) && $value->id != auth()->user()->id) || $value->id != 1): ?>
                                                        <li class="dropdown-item py-2">
                                                            <a class="deleteEmployee"
                                                               data-href="<?php echo e(route('admin.employees.delete', $value->id)); ?>">
                                                                <button
                                                                    class="btn btn-primary btn-xs"><?php echo e(__('index.delete_user')); ?></button>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('change_password')): ?>
                                                    <li class="dropdown-item py-2">
                                                        <a class="changePassword"
                                                           data-href="<?php echo e(route('admin.employees.change-password', $value->id)); ?>">
                                                            <button
                                                                class="btn btn-primary btn-xs"><?php echo e(__('index.change_password')); ?></button>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('force_logout')): ?>
                                                    <li class="dropdown-item py-2">
                                                        <a class="forceLogOut"
                                                           data-href="<?php echo e(route('admin.employees.force-logout', $value->id)); ?>">
                                                            <button
                                                                class="btn btn-primary btn-xs"><?php echo e(__('index.force_logout')); ?></button>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b><?php echo e(__('index.no_records_found')); ?></b></p>
                                </td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            <?php echo e($users->appends($_GET)->links()); ?>

        </div>

    </section>
    <?php echo $__env->make('admin.employees.common.password', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.employees.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/employees/index.blade.php ENDPATH**/ ?>