<?php $__env->startSection('title', __('index.attendance')); ?>

<?php $__env->startSection('action', __('index.employee_attendance_lists')); ?>


<?php $__env->startSection('main-content'); ?>

    <section class="content">
        <?php
        if($isBsEnabled){
            $currentDate = \App\Helpers\AppHelper::getCurrentDateInBS();

        }else{
            $currentDate = \App\Helpers\AppHelper::getCurrentDateInYmdFormat();
        }
        ?>

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.attendance.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.attendance_filter')); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.attendances.index')); ?>" method="get">

                <div class="row align-items-center">

                    <div class="col-lg col-md-6 mb-4">
                        <input id="attendance_date"
                               name="attendance_date"
                               value="<?php echo e($filterParameter['attendance_date']); ?>"
                               <?php if($isBsEnabled): ?>
                                   class="form-control dayAttendance"
                               type="text"
                               placeholder="<?php echo e(__('index.date_placeholder_bs')); ?>"
                               <?php else: ?>
                                   class="form-control"
                               type="date"
                            <?php endif; ?>
                        />
                    </div>
                    <?php if(!isset(auth()->user()->branch_id)): ?>
                    <div class="col-lg col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="branch_id" id="branch_id">
                            <option value="" <?php echo e(!isset($filterParameter['branch_id']) ? 'selected' : ''); ?>><?php echo e(__('index.select_branch')); ?></option>
                            <?php $__currentLoopData = $branch; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>  $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value->id); ?>" <?php echo e((isset($filterParameter['branch_id']) && $value->id == $filterParameter['branch_id']) ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($value->name)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <?php endif; ?>
                    <div class="col-lg col-md-6 mb-4">
                        <select class="form-select " name="department_id" id="department_id">
                            <option selected disabled><?php echo e(__('index.select_department')); ?></option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="d-md-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-block btn-success mb-4"><?php echo e(__('index.filter')); ?></button>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_csv_export')): ?>
                            <button type="button" id="download-daywise-attendance-excel"
                                    data-href="<?php echo e(route('admin.attendances.index')); ?>"
                                    class="btn btn-block btn-secondary mb-4"><?php echo e(__('index.csv_export')); ?>

                            </button>
                            <?php endif; ?>
                            <a class="btn btn-block btn-primary me-0 mb-4" href="<?php echo e(route('admin.attendances.index')); ?>"><?php echo e(__('index.reset')); ?></a>
                        </div>
                    </div>

                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.attendance_of_the_day')); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                        <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_show')): ?>
                                    <th></th>
                                <?php endif; ?>
                                <th class="text-center"><?php echo e(__('index.employee_code')); ?></th>
                                <th><?php echo e(__('index.employee_name')); ?></th>
                                    <?php if($multipleAttendance > 1): ?>
                                        <th class="text-center"><?php echo e(__('index.total_worked_hours')); ?></th>
                                    <?php else: ?>
                                        <th class="text-center"><?php echo e(__('index.check_in_at')); ?></th>
                                        <th class="text-center"><?php echo e(__('index.check_out_at')); ?></th>
                                        <th class="text-center"><?php echo e(__('index.worked_hour')); ?></th>
                                    <?php endif; ?>

                                <th class="text-center"><?php echo e(__('index.attendance_status')); ?></th>
                                <th class="text-center"><?php echo e(__('index.shift')); ?></th>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['attendance_create', 'attendance_update', 'attendance_delete'])): ?>
                                    <th class="text-center"><?php echo e(__('index.action')); ?></th>
                                <?php endif; ?>
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                $changeColor = [
                                    0 => 'danger',
                                    1 => 'success',
                                ]
                               ?>

                                <?php $__empty_1 = true; $__currentLoopData = $attendanceDetail->groupBy('user_id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userId => $userAttendances): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                    <?php
                                        $firstAttendance = $userAttendances->first();
                                        $totalWorkedMinutes = $userAttendances->sum('worked_hour');
                                        $lastAttendance = $userAttendances->last();

                                        $hours = floor($totalWorkedMinutes / 60);
                                        $minutes = $totalWorkedMinutes % 60;

                                        $workedHours = '';
                                        if ($hours > 0) {
                                            $workedHours .= $hours . ' h ';
                                        }
                                        if ($minutes > 0) {
                                            $workedHours .= $minutes . ' m';
                                        }
                                        $workedHours = trim($workedHours);

                                        $multipleEntries = $userAttendances->count();

                                        $nightShift = \App\Helpers\AppHelper::isOnNightShift($userId);

                                    ?>

                                    <tr>
                                    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_show')): ?>
                                        <td>
                                            <ul class="text-center list-unstyled mb-0">
                                                <li class="me-2">
                                                    <a href="<?php echo e(route('admin.attendances.show', $userId)); ?>"
                                                       title="<?php echo e(__('index.show_detail')); ?>">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    <?php endif; ?>

                                    <td class="text-center">
                                        <?php echo e($firstAttendance->employee_code ?: 'N/A'); ?>

                                    </td>

                                    <td>
                                        <?php echo e(ucfirst($firstAttendance->user_name)); ?>

                                    </td>

                                    <?php if($nightShift): ?>
                                        <?php if($multipleAttendance <= 1): ?>
                                            <?php if(isset($firstAttendance->night_checkin)): ?>
                                                <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="<?php echo e($firstAttendance->check_in_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($firstAttendance->check_in_type).' '.__('index.checkin')); ?>"
                                                      data-bs-toggle="modal"
                                                      data-href="<?php echo e('https://maps.google.com/maps?q='.$firstAttendance->check_in_latitude.','.$firstAttendance->check_in_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed'); ?>"
                                                      data-bs-target="<?php echo e('#addslider'); ?>"
                                                >
                                                    <?php echo e(\App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $firstAttendance->night_checkin) ?? ''); ?>

                                                </span>
                                                </td>
                                            <?php else: ?>
                                                <td class="text-center"></td>
                                            <?php endif; ?>

                                            <?php if( isset($firstAttendance->night_checkout)): ?>
                                                <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="<?php echo e($firstAttendance->check_out_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($firstAttendance->check_out_type).' '.__('index.checkout')); ?>"
                                                      data-bs-toggle="modal"
                                                      data-href="<?php echo e('https://maps.google.com/maps?q='.$firstAttendance->check_out_latitude.','.$firstAttendance->check_out_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed'); ?>"
                                                      data-bs-target="<?php echo e('#addslider'); ?>"
                                                >
                                                   <?php echo e(\App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $firstAttendance->night_checkout)  ??  ''); ?>

                                                </span>
                                                </td>
                                            <?php else: ?>
                                                <td class="text-center"></td>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                            <td class="text-center">
                                                <?php echo e(\App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($firstAttendance->worked_hour)); ?>

                                            </td>
                                    <?php elseif($multipleAttendance > 1): ?>
                                        <td class="text-center">
                                            <?php echo e($workedHours); ?>

                                        </td>
                                    <?php else: ?>
                                        <?php if(isset($firstAttendance->check_in_at)): ?>
                                            <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="<?php echo e($firstAttendance->check_in_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($firstAttendance->check_in_type).' '.__('index.checkin')); ?>"
                                                      data-bs-toggle="modal"
                                                      data-href="<?php echo e('https://maps.google.com/maps?q='.$firstAttendance->check_in_latitude.','.$firstAttendance->check_in_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed'); ?>"
                                                      data-bs-target="<?php echo e('#addslider'); ?>"
                                                >
                                                    <?php echo e(\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->check_in_at) ?? ''); ?>

                                                </span>
                                            </td>
                                        <?php else: ?>
                                            <td class="text-center"></td>
                                        <?php endif; ?>

                                        <?php if(isset($firstAttendance->check_out_at) ): ?>
                                            <td class="text-center">
                                                <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                      title="<?php echo e($firstAttendance->check_out_type == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($firstAttendance->check_out_type).' '.__('index.checkout')); ?>"
                                                      data-bs-toggle="modal"
                                                      data-href="<?php echo e('https://maps.google.com/maps?q='.$firstAttendance->check_out_latitude.','.$firstAttendance->check_out_longitude.'&t=&z=20&ie=UTF8&iwloc=&output=embed'); ?>"
                                                      data-bs-target="<?php echo e('#addslider'); ?>"
                                                >
                                                   <?php echo e(\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $firstAttendance->check_out_at) ??  ''); ?>

                                                </span>
                                            </td>
                                        <?php else: ?>
                                            <td class="text-center"></td>
                                        <?php endif; ?>

                                        <td class="text-center">
                                            <?php echo e(\App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($firstAttendance->worked_hour)); ?>

                                        </td>
                                    <?php endif; ?>

                                    <?php if(!is_null($firstAttendance->attendance_status)): ?>
                                        <td class="text-center">
                                            <a class="btn btn-<?php echo e($changeColor[$firstAttendance->attendance_status]); ?> btn-xs"
                                               title="<?php echo e($firstAttendance->attendance_status == \App\Models\Attendance::ATTENDANCE_APPROVED ? __('index.approved') : __('index.rejected')); ?>">
                                                <?php echo e($firstAttendance->attendance_status == \App\Models\Attendance::ATTENDANCE_APPROVED ? __('index.approved') : __('index.rejected')); ?>

                                            </a>
                                        </td>
                                    <?php else: ?>
                                        <td class="text-center">
                                           <span class="btn btn-light btn-xs disabled">
                                                <?php echo e(__('index.pending')); ?>

                                            </span>
                                        </td>
                                    <?php endif; ?>

                                    <?php if($firstAttendance->shift): ?>
                                        <td class="text-center">
                                            <span class="btn btn-warning btn-xs">
                                                <?php echo e(ucfirst($firstAttendance->shift)); ?>

                                            </span>
                                        </td>
                                    <?php else: ?>
                                        <td class="text-center">
                                        </td>
                                    <?php endif; ?>

                                    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['attendance_create','attendance_update'])): ?>
                                        <?php if($nightShift && $filterParameter['attendance_date'] ==  $currentDate): ?>

                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">
                                                    <?php
                                                        $nightAttendance = \App\Helpers\AttendanceHelper::checkNightShiftCheckOut($userId);

                                                    ?>
                                                    <?php if($nightAttendance == 'checkout'): ?>
                                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_update')): ?>
                                                            <li class="me-2">
                                                                <a href="<?php echo e(route('admin.employees.check-out', [$firstAttendance->company_id, $firstAttendance->user_id])); ?>"
                                                                   id="checkOut"
                                                                   data-href=""
                                                                   data-id="">
                                                                    <button class="btn btn-danger btn-xs"><?php echo e(__('index.check_out')); ?></button>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    <?php elseif($nightAttendance == 'checkin'): ?>
                                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_create')): ?>
                                                            <li class="me-2">
                                                                <a href="<?php echo e(route('admin.employees.check-in', [$firstAttendance->company_id, $firstAttendance->user_id])); ?>"
                                                                   id="checkIn"
                                                                   data-href=""
                                                                   data-id="">
                                                                    <button class="btn btn-success btn-xs"><?php echo e(__('index.check_in')); ?></button>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    <?php else: ?>

                                                    <?php endif; ?>

                                                    <?php if($firstAttendance->attendance_id): ?>
                                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_update')): ?>
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="editNightAttendance"
                                                                   data-href="<?php echo e(route('admin.night_attendances.update', $firstAttendance->attendance_id)); ?>"
                                                                   data-in="<?php echo e($firstAttendance->night_checkin); ?>"
                                                                   data-out="<?php echo e($firstAttendance->night_checkout ?? null); ?>"
                                                                   data-remark="<?php echo e($firstAttendance->edit_remark); ?>"
                                                                   data-date="<?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $firstAttendance->attendance_date)); ?>"
                                                                   data-name="<?php echo e(ucfirst($firstAttendance->user_name)); ?>"
                                                                   title="<?php echo e(__('index.edit_attendance_time')); ?>"
                                                                >
                                                                    <i class="link-icon"
                                                                       data-feather="edit"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_delete')): ?>
                                                            <li class="me-2">
                                                                <a class="deleteAttendance" href="<?php echo e(route('admin.attendance.delete', $firstAttendance->attendance_id)); ?>">
                                                                    <i class="link-icon"  data-feather="delete"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if($attendanceNote): ?>
                                                            <li class="me-2">
                                                                <a href="#"
                                                                   class="noteLink"
                                                                   data-checkout_note="<?php echo e($firstAttendance->check_out_note); ?>"
                                                                   data-checkin_note="<?php echo e($firstAttendance->check_in_note); ?>">
                                                                    Note
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </ul>
                                            </td>
                                        <?php elseif($multipleAttendance > 1): ?>
                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">

                                                    <?php if($filterParameter['attendance_date'] == $currentDate && ($multipleEntries < $multipleAttendance || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at))): ?>

                                                        <?php if((!$firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendance->check_in_at && $lastAttendance->check_out_at)): ?>
                                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_create')): ?>
                                                                <li class="me-2">
                                                                    <a href="<?php echo e(route('admin.employees.check-in', [$firstAttendance->company_id, $firstAttendance->user_id])); ?>"
                                                                       id="checkIn"
                                                                       data-href=""
                                                                       data-id="">
                                                                        <button
                                                                            class="btn btn-success btn-xs"><?php echo e(__('index.check_in')); ?></button>
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        <?php elseif(($firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at)): ?>
                                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_update')): ?>
                                                                <li class="me-2">
                                                                    <a href="<?php echo e(route('admin.employees.check-out', [$firstAttendance->company_id, $firstAttendance->user_id])); ?>"
                                                                       id="checkOut"
                                                                       data-href=""
                                                                       data-id="">
                                                                        <button
                                                                            class="btn btn-danger btn-xs"><?php echo e(__('index.check_out')); ?></button>
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                    <?php endif; ?>
                                                    <?php if($attendanceNote): ?>
                                                        <li class="me-2">
                                                            <a href="#"
                                                               class="noteLink"
                                                               data-checkout_note="<?php echo e($firstAttendance->check_out_note); ?>"
                                                               data-checkin_note="<?php echo e($firstAttendance->check_in_note); ?>">
                                                                Note
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </td>
                                        <?php else: ?>
                                            <td class="text-center">
                                                <ul class="d-flex text-center list-unstyled mb-0 justify-content-center align-items-center">

                                                    <?php if($filterParameter['attendance_date'] ==  $currentDate): ?>
                                                            <?php if(!$firstAttendance->check_in_at): ?>
                                                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_create')): ?>
                                                                    <li class="me-2">
                                                                        <a href="<?php echo e(route('admin.employees.check-in', [$firstAttendance->company_id, $firstAttendance->user_id])); ?>"
                                                                           id="checkIn"
                                                                           data-href=""
                                                                           data-id="">
                                                                            <button class="btn btn-success btn-xs"><?php echo e(__('index.check_in')); ?></button>
                                                                        </a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            <?php endif; ?>


                                                            <?php if($firstAttendance->check_in_at && !$firstAttendance->check_out_at): ?>
                                                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_update')): ?>
                                                                    <li class="me-2">
                                                                        <a href="<?php echo e(route('admin.employees.check-out', [$firstAttendance->company_id, $firstAttendance->user_id])); ?>"
                                                                           id="checkOut"
                                                                           data-href=""
                                                                           data-id="">
                                                                            <button class="btn btn-danger btn-xs"><?php echo e(__('index.check_out')); ?></button>
                                                                        </a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                    <?php if($firstAttendance->attendance_id): ?>
                                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_update')): ?>
                                                            <li class="me-2">
                                                                <a href=""
                                                                   class="editAttendance"
                                                                   data-href="<?php echo e(route('admin.attendances.update', $firstAttendance->attendance_id)); ?>"
                                                                   data-in="<?php echo e(date('H:i', strtotime($firstAttendance->check_in_at))); ?>"
                                                                   data-out="<?php echo e($firstAttendance->check_out_at ? date('H:i', strtotime($firstAttendance->check_out_at)) : null); ?>"
                                                                   data-remark="<?php echo e($firstAttendance->edit_remark); ?>"
                                                                   data-date="<?php echo e($filterParameter['attendance_date']); ?>"
                                                                   data-name="<?php echo e(ucfirst($firstAttendance->user_name)); ?>"
                                                                   title="<?php echo e(__('index.edit_attendance_time')); ?>"
                                                                >
                                                                    <i class="link-icon"
                                                                       data-feather="edit"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_delete')): ?>
                                                            <li class="me-2">
                                                                <a class="deleteAttendance" href="<?php echo e(route('admin.attendance.delete', $firstAttendance->attendance_id)); ?>">
                                                                    <i class="link-icon"  data-feather="delete"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                            <?php if($attendanceNote): ?>
                                                                <li class="me-2">
                                                                    <a href="#"
                                                                       class="noteLink"
                                                                       data-checkout_note="<?php echo e($firstAttendance->check_out_note); ?>"
                                                                       data-checkin_note="<?php echo e($firstAttendance->check_in_note); ?>">
                                                                        Note
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                    <?php endif; ?>

                                                </ul>
                                            </td>
                                        <?php endif; ?>
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


        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <iframe id="iframeModalWindow" class="attendancelocation" height="500px" width="100%" src="" name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $__env->make('admin.attendance.common.edit-attendance-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.attendance.common.edit-night-attendance-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <!-- note for checkin and checkout -->
        <div id="noteModal" class="modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Attendance Notes</h5>
                    </div>
                    <div class="modal-body">
                        <p><strong>Check-in Note:</strong> <span id="checkinNote"></span></p>
                        <p><strong>Check-out Note:</strong> <span id="checkoutNote"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.attendance.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script>
        $(document).ready(function () {
            const loadDepartments = async () => {

                const isAdmin = <?php echo e(auth('admin')->check() ? 'true' : 'false'); ?>;
                const defaultBranchId = <?php echo e(auth()->user()->branch_id ?? 'null'); ?>;
                const selectedBranchId = isAdmin ? $('#branch_id option:selected').val() : defaultBranchId;


                let departmentId = "<?php echo e($filterParameter['department_id'] ?? ''); ?>";
                console.log(departmentId);
                $('#department_id').empty();
                if (selectedBranchId) {
                    $.ajax({
                        type: 'GET',
                        url: "<?php echo e(url('admin/departments/get-All-Departments')); ?>" + '/' + selectedBranchId,
                    }).done(function (response) {
                        if (!departmentId) {
                            $('#department_id').append('<option disabled  selected ><?php echo e(__('index.select_department')); ?></option>');
                        }
                        response.data.forEach(function (data) {
                            $('#department_id').append('<option ' + ((data.id == departmentId) ? "selected" : '') + ' value="' + data.id + '" >' + data.dept_name + '</option>');
                        });
                    });
                }
            };

            const isAdmin = <?php echo e(auth('admin')->check() ? 'true' : 'false'); ?>;
            if (isAdmin) {
                $('#branch_id').on('change', loadDepartments);
                $('#branch_id').trigger('change');
            } else {
                loadDepartments(); // Load directly for regular users
            }

        });

        document.addEventListener('DOMContentLoaded', function() {
            const noteModal = new bootstrap.Modal(document.getElementById('noteModal'));

            document.querySelectorAll('.noteLink').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const checkinNote = this.getAttribute('data-checkin_note');
                    const checkoutNote = this.getAttribute('data-checkout_note');

                    document.getElementById('checkinNote').textContent = checkinNote || '';
                    document.getElementById('checkoutNote').textContent = checkoutNote || '';

                    noteModal.show();
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/attendance/index.blade.php ENDPATH**/ ?>