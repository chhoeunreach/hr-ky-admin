<?php use App\Helpers\AppHelper; ?>
<?php use App\Helpers\AttendanceHelper; ?>


<?php $__env->startSection('title', __('index.attendance')); ?>

<?php $__env->startSection('action', __('index.employee_attendance_detail')); ?>

<?php $__env->startSection('button'); ?>
    <a href="<?php echo e(route('admin.attendances.index')); ?>">
        <button class="btn btn-sm btn-primary"><i class="link-icon"
                                                  data-feather="arrow-left"></i> <?php echo e(__('index.back')); ?></button>
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>
    <?php
    if ($isBsEnabled) {
        $filterData['min_year'] = '2076';
        $filterData['max_year'] = '2089';
        $filterData['month'] = 'np';
        $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
        $filterData['current_year'] = $nepaliDate['year'];
        $filterData['current_month'] = $nepaliDate['month'];
    } else {
        $filterData['min_year'] = '2020';
        $filterData['max_year'] = '2033';
        $filterData['current_year'] = now()->format('Y');
        $filterData['current_month'] = now()->month;
        $filterData['month'] = 'en';
    }
    ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.attendance.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.attendance_of') . ' ' .ucfirst($userDetail->name)); ?></h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="<?php echo e(route('admin.attendances.show', $userDetail->id)); ?>"
                    method="get">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-3 mb-4">
                            <input type="number" min="<?php echo e($filterData['min_year']); ?>"
                                max="<?php echo e($filterData['max_year']); ?>" step="1"
                                placeholder="<?php echo e(__('index.attendance_year_example', ['year' => $filterData['min_year']])); ?>"
                                id="year"
                                name="year"
                                value="<?php echo e($filterParameter['year']); ?>"
                                class="form-control">
                        </div>

                        <div class="col-lg-4 col-md-3 mb-4">
                            <select class="form-select form-select-lg" name="month" id="month">
                                <option
                                    value="" <?php echo e(!isset($filterParameter['month']) ? 'selected' : ''); ?>><?php echo e(__('index.all_month')); ?></option>
                                <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($key); ?>" <?php echo e((isset($filterParameter['month']) && $key == $filterParameter['month']) ? 'selected' : ''); ?>>
                                        <?php echo e($value[$filterData['month']]); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-lg col-md-3 mb-4">
                            <button type="submit"
                                    class="btn btn-block btn-success"><?php echo e(__('index.filter')); ?></button>
                        </div>

                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_csv_export')): ?>
                            <div class="col-lg col-md-3 mb-4">
                                <button type="button" id="download-excel"
                                        data-href="<?php echo e(route('admin.attendances.show', $userDetail->id)); ?>"
                                        class="btn btn-block btn-secondary">
                                    <?php echo e(__('index.csv_export')); ?>

                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="col-lg col-md-3 mb-4">
                            <a class="btn btn-block btn-primary"
                            href="<?php echo e(route('admin.attendances.show', $userDetail->id)); ?>"><?php echo e(__('index.reset')); ?></a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end"><?php echo e(__('index.total_days_in_month')); ?></h6>
                        <h5 class="text-primary ps-5 text-nowrap"><?php echo e($attendanceSummary ? number_format($attendanceSummary['totalDays']) : 0); ?></h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end"><?php echo e(__('index.present_days')); ?></h6>
                        <h5 class="text-primary ps-5 text-nowrap"><?php echo e($attendanceSummary ? number_format($attendanceSummary['totalPresent']) : 0); ?></h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end"><?php echo e(__('index.absent_days')); ?></h6>
                        <h5 class="text-primary ps-5 text-nowrap"><?php echo e($attendanceSummary ? number_format($attendanceSummary['totalAbsent']) : 0); ?></h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end"><?php echo e(__('index.weekend_days')); ?></h6>
                        <h5 class="text-primary ps-5 text-nowrap"><?php echo e($attendanceSummary ? number_format($attendanceSummary['totalWeekend']) : 0); ?></h5>
                    </div>
                </div>
            </div>

            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end"><?php echo e(__('index.holiday_days')); ?></h6>
                        <h5 class="text-primary ps-5 text-nowrap"><?php echo e($attendanceSummary ? number_format($attendanceSummary['totalHoliday']) : 0); ?></h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end"><?php echo e(__('index.leave_days')); ?></h6>
                        <h5 class="text-primary ps-5 text-nowrap"><?php echo e($attendanceSummary ? number_format($attendanceSummary['totalLeave']) : 0); ?></h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end"><?php echo e(__('index.working_hours')); ?></h6>
                        <h6 class="text-primary ps-5 text-nowrap"><?php echo e($attendanceSummary ? $attendanceSummary['totalWorkingHours'] : '-'); ?></h6>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end"><?php echo e(__('index.worked_hours')); ?></h6>
                        <h6 class="text-primary ps-5 text-nowrap"><?php echo e($attendanceSummary ? $attendanceSummary['totalWorkedHours'] : '-'); ?></h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.attendance_details_of', ['month' => $monthName])); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th><?php echo e(__('index.date')); ?></th>
                            <th style="text-align: center;"><?php echo e(__('index.check_in_at')); ?></th>
                            <th style="text-align: center;"><?php echo e(__('index.check_out_at')); ?></th>
                            <th style="text-align: center;"><?php echo e(__('index.worked_hour')); ?></th>
                            <th style="text-align: center;"><?php echo e(__('index.status')); ?></th>
                            <th style="text-align: center;"><?php echo e(__('index.shift')); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_update')): ?>
                                <th style="text-align: center;"><?php echo e(__('index.action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>

                            <?php
                            $changeColor = [
                                0 => 'danger',
                                1 => 'success',
                            ]
                            ?>

                        <?php $__empty_1 = true; $__currentLoopData = $attendanceDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayIndex => $dayData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $totalMinutes = 0;
                                $isFirstIteration = true;

                            ?>
                        <tbody>
                            <?php if(isset($dayData['data']) && count($dayData['data']) > 0): ?>
                                <?php $__currentLoopData = $dayData['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <?php
                                        $totalMinutes += $attendance['worked_hour'];
                                    ?>
                                    <tr>

                                        <?php if($isFirstIteration): ?>
                                            <td><?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date'])); ?></td>
                                            <?php
                                                $isFirstIteration = false; // Set to false after displaying the date for the first time
                                            ?>
                                        <?php else: ?>
                                            <td></td>
                                        <?php endif; ?>
                                            <?php if(isset($attendance['shift'])  && ($attendance['shift'] == \App\Enum\ShiftTypeEnum::night->value)): ?>
                                                <?php if(isset($attendance['night_checkin'])): ?>
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="<?php echo e($attendance['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($attendance['check_in_type']).' '.__('index.checkin')); ?>"
                                                                data-bs-toggle="modal"
                                                                data-href="<?php echo e('https://maps.google.com/maps?q='.$attendance['check_in_latitude'].','.$attendance['check_in_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'); ?>"
                                                                data-bs-target="<?php echo e('#addslider'); ?>">
                                                            <?php echo e(\App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkin'])); ?>

                                                        </span>
                                                    </td>
                                                <?php else: ?>
                                                    <td></td>
                                                <?php endif; ?>
                                                <?php if(isset($attendance['night_checkout'])): ?>
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="<?php echo e($attendance['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($attendance['check_out_type']).' '.__('index.checkout')); ?>"
                                                                data-bs-toggle="modal"
                                                                data-href="<?php echo e('https://maps.google.com/maps?q='.$attendance['check_out_latitude'].','.$attendance['check_out_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'); ?>"
                                                                data-bs-target="<?php echo e('#addslider'); ?>">
                                                            <?php echo e(\App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkout'])); ?>

                                                        </span>
                                                    </td>
                                                <?php else: ?>
                                                    <td></td>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if(isset($attendance['check_in_at'])): ?>
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="<?php echo e($attendance['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($attendance['check_in_type']).' '.__('index.checkin')); ?>"
                                                                data-bs-toggle="modal"
                                                                data-href="<?php echo e('https://maps.google.com/maps?q='.$attendance['check_in_latitude'].','.$attendance['check_in_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'); ?>"
                                                                data-bs-target="<?php echo e('#addslider'); ?>">
                                                            <?php echo e(\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_in_at'])); ?>

                                                        </span>
                                                    </td>
                                                <?php else: ?>
                                                    <td></td>
                                                <?php endif; ?>
                                                <?php if(isset($attendance['check_out_at'])): ?>
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="<?php echo e($attendance['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($attendance['check_out_type']).' '.__('index.checkout')); ?>"
                                                                data-bs-toggle="modal"
                                                                data-href="<?php echo e('https://maps.google.com/maps?q='.$attendance['check_out_latitude'].','.$attendance['check_out_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'); ?>"
                                                                data-bs-target="<?php echo e('#addslider'); ?>">
                                                            <?php echo e(\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting,  $attendance['check_out_at'])); ?>

                                                        </span>
                                                    </td>
                                                <?php else: ?>
                                                    <td></td>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <td  class="text-center">
                                            <?php echo e(\App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['worked_hour'])); ?>

                                        </td>
                                        <?php if(!is_null($attendance['attendance_status'])): ?>
                                            <td class="text-center">
                                                <a class="btn btn-<?php echo e($changeColor[$attendance['attendance_status']]); ?> btn-xs"
                                                     title="<?php echo e(__('index.change_attendance_status')); ?>">
                                                    <?php echo e(($attendance['attendance_status'] == \App\Models\Attendance::ATTENDANCE_APPROVED) ? __('index.present') : __('index.rejected')); ?>

                                                </a>
                                            </td>
                                        <?php else: ?>
                                            <td  class="text-center">
                                                <span class="btn btn-light btn-xs disabled">
                                                    <?php echo e(__('index.pending')); ?>

                                                </span>
                                            </td>
                                        <?php endif; ?>
                                            <?php if($attendance['shift']): ?>
                                                <td class="text-center">
                                                    <span class="btn btn-info btn-xs">
                                                        <?php echo e(ucfirst($attendance['shift'])); ?>

                                                    </span>
                                                </td>
                                            <?php else: ?>
                                                <td></td>
                                            <?php endif; ?>
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_update')): ?>
                                                <td class="text-center">

                                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                        <?php if(isset($attendance['shift'])  && ($attendance['shift'] == \App\Enum\ShiftTypeEnum::night->value)): ?>
                                                            <li class="me-2">
                                                                <a href=""
                                                                    class="editNightAttendance"
                                                                    data-href="<?php echo e(route('admin.night_attendances.update', $attendance['id'])); ?>"
                                                                    data-in="<?php echo e($attendance['night_checkin']); ?>"
                                                                    data-out="<?php echo e($attendance['night_checkout'] ?? null); ?>"
                                                                    data-remark="<?php echo e($attendance['edit_remark']); ?>"
                                                                    data-date="<?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $attendance['attendance_date'])); ?>"
                                                                    data-name="<?php echo e(ucfirst($userDetail->name)); ?>"
                                                                    title="<?php echo e(__('index.edit_attendance_time')); ?>"
                                                                >
                                                                    <i class="link-icon" data-feather="edit"></i>
                                                                </a>
                                                            </li>
                                                        <?php else: ?>
                                                            <?php if(count($dayData['data']) < $multipleAttendance && isset($attendance['check_out_at'])): ?>
                                                                <li class="me-2">
                                                                    <a href=""
                                                                    class="addEmployeeAttendance"
                                                                    data-href="<?php echo e(route('admin.attendances.store')); ?>"
                                                                    data-name="<?php echo e(ucfirst($userDetail->name)); ?>"
                                                                    data-date="<?php echo e(date('Y-m-d', strtotime($dayData['attendance_date']))); ?>"
                                                                    data-cdate="<?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $attendance['attendance_date'])); ?>"
                                                                    data-user_id="<?php echo e($userDetail->id); ?>"
                                                                    title="<?php echo e(__('index.add_attendance_time')); ?>">
                                                                    <i class="link-icon" data-feather="plus-circle"></i>
                                                                </a>
                                                                </li>
                                                            <?php endif; ?>
                                                            <?php if(isset($attendance['id'])): ?>
                                                                <li class="me-2">
                                                                    <a href=""
                                                                        class="editAttendance"
                                                                        data-href="<?php echo e(route('admin.attendances.update', $attendance['id'])); ?>"
                                                                        data-in="<?php echo e(date('H:i', strtotime($attendance['check_in_at']))); ?>"
                                                                        data-out="<?php echo e($attendance['check_out_at'] ? date('H:i', strtotime($attendance['check_out_at'])) : null); ?>"
                                                                        data-remark="<?php echo e($attendance['edit_remark']); ?>"
                                                                        data-date="<?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $attendance['attendance_date'])); ?>"
                                                                        data-name="<?php echo e(ucfirst($userDetail->name)); ?>"
                                                                        title="<?php echo e(__('index.edit_attendance_time')); ?>">
                                                                        <i class="link-icon" data-feather="edit"></i>
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_delete')): ?>
                                                            <li class="me-2">
                                                                <a class="deleteAttendance" href="<?php echo e(route('admin.attendance.delete', $attendance['id'])); ?>">
                                                                    <i class="link-icon"  data-feather="delete"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </td>
                                            <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if($multipleAttendance > 1 && count($dayData['data']) > 1): ?>
                                    <tr class="bg-light">
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <?php
                                            $hours = floor($totalMinutes / 60);
                                            $minutes = $totalMinutes % 60;
                                            if ($hours == 0 && $minutes == 0) {
                                                $worked_hours = '';
                                            } elseif ($hours == 0) {
                                                $worked_hours = $minutes . ' min';
                                            } elseif ($minutes == 0) {
                                                $worked_hours = $hours . ' hr';
                                            } else {
                                                $worked_hours = $hours . ' hr ' . $minutes . ' min';
                                            }
                                        ?>
                                        <th class="text-center"><?php echo e($worked_hours); ?></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>

                                    </tr>
                                <?php endif; ?>
                            <?php else: ?>
                                <tr>
                                    <td><?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date'])); ?></td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <?php
                                        $reason = (\App\Helpers\AttendanceHelper::getHolidayOrLeaveDetail($dayData['attendance_date'], $userDetail->id));
                                    ?>
                                    <?php if($reason): ?>
                                        <td class="text-center">
                                            <span class="btn btn-outline-secondary btn-xs">
                                                <?php echo e($reason); ?>

                                            </span>
                                        </td>
                                    <?php endif; ?>
                                    <td  class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td  class="text-center">
                                        <?php if(isset($reason) && $reason == 'Absent'): ?>
                                            <a href=""
                                                class="addEmployeeAttendance"
                                                data-href="<?php echo e(route('admin.attendances.store')); ?>"
                                                data-name="<?php echo e(ucfirst($userDetail->name)); ?>"
                                                data-date="<?php echo e(date('Y-m-d', strtotime($dayData['attendance_date']))); ?>"
                                                data-cdate="<?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date'])); ?>"
                                                data-user_id="<?php echo e($userDetail->id); ?>"
                                                title="<?php echo e(__('index.add_attendance_time')); ?>">
                                                <i class="link-icon" data-feather="plus-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tbody>
                                <tr>
                                    <td colspan="100%">
                                        <p class="text-center"><b><?php echo e(__('index.no_records_found')); ?></b></p>
                                    </td>
                                </tr>
                            </tbody>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <iframe id="iframeModalWindow" class="attendancelocation" height="500px" width="100%" src=""
                                name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $__env->make('admin.attendance.common.edit-attendance-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.attendance.common.create-attendance-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.attendance.common.edit-night-attendance-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.attendance.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/attendance/show.blade.php ENDPATH**/ ?>