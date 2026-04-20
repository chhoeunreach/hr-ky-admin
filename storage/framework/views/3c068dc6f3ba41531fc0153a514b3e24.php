<?php use App\Helpers\AttendanceHelper; ?>
<table>
    <thead>
    <tr>
        <th colspan="5" style="text-align: center">
            <strong><?php echo e(__('index.attendance_report')); ?> : <?php echo e($userName); ?> </strong>
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
    <tbody>
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

    ?>
    <?php $__empty_1 = true; $__currentLoopData = $attendanceData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayIndex => $dayData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $totalMinutes = 0;
            $isFirstIteration = true;
            $totalOverTime = 0;
            $totalUnderTime = 0;
        ?>
        <?php if(isset($dayData['data']) && is_array($dayData['data']) && count($dayData['data']) > 0): ?>
            <?php $__currentLoopData = $dayData['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $workingHours = $attendance['working_hour'];
                   if(!is_null($attendance['worked_hour'])){

                       $totalMinutes += $attendance['worked_hour'];
                       if($multipleAttendance <= 1 && count($dayData['data']) <= 1){
                           $totalOverTime += $attendance['overtime'];
                           $netTotalOverTime += $attendance['overtime'];
                           $totalUnderTime += $attendance['undertime'];
                           $netTotalUnderTime += $attendance['undertime'];
                       }
                       $netTotalMinutes += $attendance['worked_hour'];
                   }
                ?>
                <tr>
                    <?php if($isFirstIteration): ?>
                        <td><?php echo e($dayIndex); ?></td>
                        <?php
                            $isFirstIteration = false;
                        ?>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>

                    <?php if(isset($attendance['check_in_at'])): ?>
                        <td style="text-align: center;">


                            <?php echo e(AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_in_at'])); ?>

                        </td>
                    <?php elseif(isset($attendance['night_checkin'])): ?>

                        <td style="text-align: center;">
                            <?php echo e(AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkin'])); ?>

                        </td>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>
                    <td style="text-align: center;">
                        <?php echo e($attendance['check_in_latitude']); ?>

                    </td>
                    <td>
                        <?php echo e($attendance['check_in_longitude']); ?>

                    </td>
                    <?php if(isset($attendance['check_out_at'])): ?>

                        <td style="text-align: center;">
                            <?php echo e(AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_out_at'])); ?>

                        </td>
                    <?php elseif(isset($attendance['night_checkout'])): ?>

                        <td style="text-align: center;">
                            <?php echo e(AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkout'])); ?>

                        </td>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>
                    <td style="text-align: center;">
                        <?php echo e($attendance['check_out_latitude']); ?>

                    </td>
                    <td style="text-align: center;">
                        <?php echo e($attendance['check_out_longitude']); ?>

                    </td>

                    <td style="text-align: center;">

                        <?php echo e(!is_null($attendance['worked_hour']) ? AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['worked_hour']): ( isset($attendance['check_out_at']) ? AttendanceHelper::getWorkedHourInHourAndMinute($attendance['check_in_at'],$attendance['check_out_at']) : AttendanceHelper::getWorkedHourInHourAndMinute($attendance['night_checkin'],$attendance['night_checkout']) )); ?>

                    </td>
                        <td  >
                            <?php echo e(($multipleAttendance <= 1 && count($dayData['data']) <= 1) ? \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['overtime']) : ''); ?>

                        </td>
                        <td  >
                            <?php echo e(($multipleAttendance <= 1 && count($dayData['data']) <= 1) ?  \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['undertime']) : ''); ?>

                        </td>
                        <?php
                            $reason = (AttendanceHelper::getHolidayOrWeekendDetailForAttendance($dayIndex));
                        ?>
                        <?php if($reason): ?>

                            <td style="text-align: center;">
                                <span class="btn btn-outline-secondary btn-xs">
                                    <?php echo e($reason); ?>

                                </span>
                            </td>

                        <?php else: ?>
                            <td></td>
                        <?php endif; ?>

                    <td style="text-align: center;"><?php echo e(isset($attendance['shift']) ? ucfirst($attendance['shift']) : 'N/A'); ?></td>


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
                    <th class="text-center"><?php echo e($worked_hours); ?></th>
                    <th style="text-align: center;"><?php echo e(AttendanceHelper::getWorkedTimeInHourAndMinute($totalOverTime)); ?> </th>
                    <th style="text-align: center;"> <?php echo e(AttendanceHelper::getWorkedTimeInHourAndMinute($totalUnderTime)); ?></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            <?php endif; ?>
        <?php else: ?>
            <tr>
                <td><?php echo e($dayIndex); ?></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                <?php
                    $reason = (AttendanceHelper::getHolidayOrLeaveDetail($dayIndex, $userId));
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
                    <td style="text-align: center;">
                        <span class="btn btn-outline-secondary btn-xs">
                            <?php echo e($reason); ?>

                        </span>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="100%" class="text-center"><b><?php echo e(__('index.no_records_found')); ?></b></td>
        </tr>
    <?php endif; ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan="7" style="text-align: center;"><b><?php echo e(__('index.total')); ?></b></th>

        <th style="text-align: center;"><b><?php echo e(AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalMinutes)); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalOverTime)); ?></b></th>
        <th style="text-align: center;"><b><?php echo e(AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalUnderTime)); ?></b>
        </th>
        <th></th>


    </tr>

    <tr></tr>
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
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/attendance/export/attendance-report-export.blade.php ENDPATH**/ ?>