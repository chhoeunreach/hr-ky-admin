<table>
    <thead>
    <tr>
        <th colspan="5" style="text-align: center">
            <strong><?php echo e(ucfirst($employeeDetail->name)); ?>

                <?php if(count($attendanceRecordDetail) > 0): ?>
                    <?php if(\App\Helpers\AppHelper::ifDateInBsEnabled()): ?>
                        <?php echo e(\App\Helpers\AppHelper::MONTHS[date("n", strtotime($attendanceRecordDetail[0]['attendance_date']))]['np']); ?>

                    <?php else: ?>
                        <?php echo e(date("F", strtotime($attendanceRecordDetail[0]['attendance_date']))); ?>

                    <?php endif; ?>
                <?php endif; ?>
                <?php echo e(__('index.attendance_report')); ?>

            </strong>
        </th>
    </tr>
    <tr>
        <th><b><?php echo e(__('index.date')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.check_in_at')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.check_in_latitude')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.check_in_longitude')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.check_out_at')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.check_out_latitude')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.check_out_longitude')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.total_worked_hours')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.overtime')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.undertime')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.attendance_status')); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(__('index.shift')); ?></b></th>

    </tr>
    </thead>
    <?php
        $changeColor = [
            0 => 'danger',
            1 => 'success',
        ];
        $netTotalMinutes = 0;
        $netTotalOverTime = 0;
        $netTotalUnderTime = 0;
        $netTotalLeave = 0;
        $netTotalAbsent = 0;
        $workingHours = 0;
    ?>
    <?php $__empty_1 = true; $__currentLoopData = $attendanceRecordDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayIndex => $dayData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $totalMinutes = 0;
            $totalOverTime = 0;
            $totalUnderTime = 0;

            $isFirstIteration = true;

        ?>
        <tbody>
            <?php if(isset($dayData['data']) && count($dayData['data']) > 0): ?>
                <?php $__currentLoopData = $dayData['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <?php
                        $workingHours = $attendance['working_hour'];

                        $totalMinutes += $attendance['worked_hour'];
                        if($multipleAttendance <= 1 && count($dayData['data']) <= 1){
                            $totalOverTime += $attendance['overtime'];
                            $netTotalOverTime += $attendance['overtime'];
                            $totalUnderTime += $attendance['undertime'];
                            $netTotalUnderTime += $attendance['undertime'];
                        }

                        $netTotalMinutes += $attendance['worked_hour'];
                    ?>
                    <tr>

                        <?php if($isFirstIteration): ?>
                            <td><?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date'])); ?></td>
                            <?php
                                $isFirstIteration = false;
                            ?>
                        <?php else: ?>
                            <td></td>
                        <?php endif; ?>
                        <?php if($attendance['check_in_at']): ?>
                            <td >
                                <?php echo e(\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_in_at']) ?? ''); ?>

                            </td>
                        <?php elseif(isset($attendance['night_checkin'])): ?>
                            <td >
                                <?php echo e(\App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkin']) ?? ''); ?>

                            </td>
                        <?php else: ?>
                            <td></td>
                        <?php endif; ?>
                            <td>
                                <?php echo e($attendance['check_in_latitude']); ?>

                            </td>
                            <td>
                                <?php echo e($attendance['check_in_longitude']); ?>

                            </td>
                        <?php if(isset($attendance['check_out_at'])): ?>
                            <td >
                               <?php echo e($attendance['check_out_at'] ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting,  $attendance['check_out_at']) : ''); ?>

                            </td>
                        <?php elseif(isset($attendance['night_checkout'])): ?>
                            <td >
                                <?php echo e($attendance['night_checkout'] ? \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkout']) : ''); ?>

                            </td>
                        <?php else: ?>
                            <td></td>
                        <?php endif; ?>
                            <td>
                                <?php echo e($attendance['check_out_latitude']); ?>

                            </td>
                            <td>
                                <?php echo e($attendance['check_out_longitude']); ?>

                            </td>
                        <td  >
                            <?php echo e(\App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['worked_hour'])); ?>

                        </td>
                        <td  >
                            <?php echo e(($multipleAttendance <= 1 && count($dayData['data']) <= 1) ? \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['overtime']) : ''); ?>

                        </td>
                        <td  >
                            <?php echo e(($multipleAttendance <= 1 && count($dayData['data']) <= 1) ?  \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['undertime']) : ''); ?>

                        </td>
                            <?php
                                $reason = (\App\Helpers\AttendanceHelper::getHolidayOrWeekendDetailForAttendance($dayData['attendance_date']));
                            ?>
                            <?php if($reason): ?>
                                <td >
                                    <span class="btn btn-outline-secondary btn-xs">
                                        <?php echo e($reason); ?>

                                    </span>
                                </td>
                            <?php else: ?>
                                <td></td>
                            <?php endif; ?>

                        <td><?php echo e(ucfirst($attendance['shift'])); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($multipleAttendance > 1 && count($dayData['data']) > 1): ?>
                    <tr class="bg-gray-100">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
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
                            $totalOverTime = $totalUnderTime = 0;
                            $deficiency =  (int)$totalMinutes - (int)$workingHours;

                            if($deficiency > 0){
                                $totalOverTime = $deficiency;
                                $netTotalOverTime += $deficiency;
                            }else{
                                $totalUnderTime = abs($deficiency);
                                $netTotalUnderTime += abs($deficiency);
                            }

                        ?>
                        <th ><?php echo e($worked_hours); ?></th>
                        <th><?php echo e(\App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($totalOverTime)); ?> </th>
                        <th> <?php echo e(\App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($totalUnderTime)); ?></th>
                        <th></th>
                        <th></th>


                    </tr>
                <?php endif; ?>
            <?php else: ?>
                <tr>
                    <td><?php echo e(\App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date'])); ?></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>

                    <?php
                        $reason = (\App\Helpers\AttendanceHelper::getHolidayOrLeaveDetail($dayData['attendance_date'], $employeeDetail->id));
                    ?>
                    <?php if($reason): ?>
                        <?php
                            if($reason == 'Leave%'){
                                $netTotalLeave++;
                            }

                            if($reason == 'Absent'){
                                $netTotalAbsent++;
                            }
                        ?>
                        <td >
                            <span class="btn btn-outline-secondary btn-xs">
                                <?php echo e($reason); ?>

                            </span>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endif; ?>

        </tbody>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tbody>
        <tr>
            <td colspan="100%">
                <p ><b><?php echo e(__('index.no_records_found')); ?></b></p>
            </td>
        </tr>
        <tbody>
    <?php endif; ?>
        <tfoot>
            <tr>
                <th><b><?php echo e(__('index.total')); ?></b></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th  style="text-align: center;"><b><?php echo e(\App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalMinutes)); ?></b></th>
                <th style="text-align: center;"><b><?php echo e(\App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalOverTime)); ?></b></th>
                <th style="text-align: center;"><b><?php echo e(\App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalUnderTime)); ?></b></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>

            </tr>

            <tr> </tr>
            <tr>
                <th> Remarks:</th>
            </tr>

            <tr>
                <th> Total Leave:</th>
                <td><?php echo e($netTotalLeave); ?></td>
            </tr>
            <tr>
                <th> Total Absent:</th>
                <td><?php echo e($netTotalAbsent); ?></td>
            </tr>

        </tfoot>
</table>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/attendance/export/attendance-export.blade.php ENDPATH**/ ?>