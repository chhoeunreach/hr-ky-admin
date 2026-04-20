<?php use App\Helpers\AttendanceHelper; ?>
<?php use App\Models\Client; ?>
<?php use App\Models\User; ?>
<?php use App\Helpers\AppHelper; ?>


<?php $__env->startSection('title',__('index.digital_hr_dashboard')); ?>

<?php
$attendanceDetail = (AppHelper::employeeTodayAttendanceDetail());

$multipleEntries = count($attendanceDetail);
$firstAttendance = $attendanceDetail->first();
$lastAttendance = $attendanceDetail->last();

$checkInAt = $firstAttendance['check_in_at'] ?? '';
$checkOutAt = $lastAttendance['check_out_at'] ?? '';
$attendanceDate = $lastAttendance['attendance_date'] ?? '';
$viewCheckIn = $checkInAt ? AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $checkInAt) : '-:-:-';
$viewCheckOut = $checkOutAt ? AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $checkOutAt) : '-:-:-';
?>

<?php $__env->startSection('nav-head',__('index.welcome').' : ' .ucfirst($dashboardDetail?->company_name) ); ?>

<?php $__env->startSection('styles'); ?>
    <style>
        #clockContainer {
            background: url(<?php echo e(asset('assets/images/clock.png')); ?>) no-repeat;
            background-size: 100%;
        }

        .alert {
            display: flex;
            align-items: center;
        }

        .scrolling-message {
            display: inline-block;
            white-space: nowrap;
            position: absolute;
            animation: scroll-left 10s linear infinite;
        }

        @keyframes scroll-left {
            0% {
                transform: translateX(100%);
            }
            100% {
                transform: translateX(-100%);
            }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">
        <?php
        $projectPriority = [
            'low' => 'info',
            'medium' => 'warning',
            'high' => 'primary',
            'urgent' => 'primary'
        ];
        ?>

        <div id="flashAttendanceMessage" class="d-none">
            <div class="alert alert-danger errorStartWorking">
                <p class="errorStartWorkingMessage"></p>
            </div>

            <div class="alert alert-danger errorStopWorking">
                <p class="errorStopWorkingMessage"></p>
            </div>

            <div class="alert alert-success successStartWorking">
                <p class="successStartWorkingMessage"></p>
            </div>

            <div class="alert alert-success successStopWorking">
                <p class="successStopWorkingMessage"></p>
            </div>
        </div>

        <div id="loader" style="display:none;">
            <div class="loading">
                <div class="loading-content"></div>
            </div>
        </div>

        <div class="row">
            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('attendance_summary')): ?>
                <div class=" <?php echo e(isset(auth()->user()->id) ? 'col-xxl-9 col-xl-8': 'col-xxl-12 col-xl-12'); ?> d-flex">
                    <div class="row">
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0"><?php echo e(__('index.total_departments')); ?></h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3><?php echo e(number_format($dashboardDetail?->total_departments)); ?></h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="layers"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0"><?php echo e(__('index.total_employees')); ?></h6>
                                    </div>

                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3><?php echo e(number_format($dashboardDetail?->total_employee)); ?></h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="users"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0"><?php echo e(__('index.total_holidays')); ?></h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3><?php echo e(number_format($dashboardDetail?->total_holidays) ?? 0); ?></h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="umbrella"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0"><?php echo e(__('index.paid_leaves')); ?></h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3><?php echo e(number_format($dashboardDetail?->total_paid_leaves) ?? 0); ?></h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="file-text"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0"><?php echo e(__('index.on_leave_today')); ?></h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3><?php echo e(number_format($dashboardDetail?->total_on_leave) ?? 0); ?></h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="file-minus"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0"><?php echo e(__('index.pending_leave_requests')); ?></h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3><?php echo e(number_format($dashboardDetail?->total_pending_leave_requests) ?? 0); ?></h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="twitch"> </i>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0"><?php echo e(__('index.total_check_in_today')); ?></h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3><?php echo e(number_format($dashboardDetail?->total_checked_in_employee) ?? 0); ?></h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="log-in"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0"><?php echo e(__('index.total_check_out_today')); ?></h6>
                                    </div>
                                    <div class="row align-items-center d-md-fle">
                                        <div class="col-lg-6 col-md-6">
                                            <h3><?php echo e(number_format($dashboardDetail?->total_checked_out_employee) ?? 0); ?></h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="log-out"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endif; ?>
            <?php if(auth()->user()): ?>
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('allow_attendance')): ?>
                    <div class="col-xxl-3 col-xl-4 mb-4 d-flex">
                        <div class="card w-100">
                            <div class="card-body text-center clock-display">
                                <div id="clockContainer" class="mb-2">
                                    <div id="hour"></div>
                                    <div id="minute"></div>
                                    <div id="second"></div>
                                </div>

                                <p id="date"
                                   class="text-primary fw-bolder mb-2"> <?php echo e(AppHelper::getCurrentDate()); ?></p>

                                <div class="punch-btn mb-2 d-flex align-items-center justify-content-around">
                                    <?php if($multipleAttendance > 1): ?>
                                        <?php if($multipleEntries < $multipleAttendance || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at)): ?>

                                            <?php if((!isset($firstAttendance->check_in_at) && !isset($firstAttendance->check_out_at)) || ($lastAttendance->check_in_at && $lastAttendance->check_out_at)): ?>
                                                <button href="<?php echo e(route('admin.dashboard.takeAttendance','checkIn')); ?>"
                                                        class="btn btn-lg btn-danger "
                                                        id="startWorkingBtn"
                                                        data-audio="<?php echo e(asset('assets/audio/beep.mp3')); ?>"
                                                >
                                                    <?php echo e(__('index.punch_in')); ?>

                                                </button>

                                            <?php elseif(($firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendancess->check_in_at && !$lastAttendance->check_out_at)): ?>
                                                <button href="<?php echo e(route('admin.dashboard.takeAttendance','checkOut')); ?>"
                                                        class="btn btn-lg btn-danger"
                                                        id="stopWorkingBtn"
                                                        data-audio="<?php echo e(asset('assets/audio/beep.mp3')); ?>"
                                                >
                                                    <?php echo e(__('index.punch_out')); ?>

                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button href="<?php echo e(route('admin.dashboard.takeAttendance','checkIn')); ?>"
                                                class="btn btn-lg btn-danger  <?php echo e($checkInAt ? 'd-none' : ''); ?>"
                                                id="startWorkingBtn" data-audio="<?php echo e(asset('assets/audio/beep.mp3')); ?>"
                                        >
                                            <?php echo e(__('index.punch_in')); ?>

                                        </button>
                                        <button href="<?php echo e(route('admin.dashboard.takeAttendance','checkOut')); ?>"
                                                class="btn btn-lg btn-danger <?php echo e($checkOutAt ? 'd-none' : ''); ?>"
                                                id="stopWorkingBtn" data-audio="<?php echo e(asset('assets/audio/beep.mp3')); ?>"
                                        >
                                            <?php echo e(__('index.punch_out')); ?>

                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="check-text d-flex align-items-center justify-content-around">
                                    <span><?php echo e(__('index.check_in_at')); ?><p class="text-success fw-bold h5"
                                                                          id="checkInTime"><?php echo e($viewCheckIn); ?> </p></span>
                                    <span><?php echo e(__('index.check_out_at')); ?><p class="text-danger fw-bold h5"
                                                                           id="checkOutTime"><?php echo e($viewCheckOut); ?>  </p></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['project_detail','client_detail'])): ?>
            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('project_detail')): ?>
                <div class="projectManagement">
                    <h4 class="mb-4"><?php echo e(__('index.project_management')); ?> </h4>
                    <div class="row">
                        <div class="col-xxl-6 col-xl-6 d-flex mb-4">
                            <div class="card card-table flex-fill">
                                <div class="card-header">
                                    <h3 class="card-title mb-0"><?php echo e(__('index.projects_detail')); ?></h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="projectChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-6 col-xl-6 d-flex">
                            <div class="row">
                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2"><?php echo e(__('index.total_projects')); ?></h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3><?php echo e(number_format($projectCardDetail['total_projects'])); ?></h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2"><?php echo e(__('index.pending_projects')); ?></h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3><?php echo e(number_format($projectCardDetail['not_started'])); ?></h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2"><?php echo e(__('index.on_hold_projects')); ?></h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3><?php echo e(number_format($projectCardDetail['on_hold'])); ?></h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2"><?php echo e(__('index.in_progress_projects')); ?></h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3><?php echo e(number_format($projectCardDetail['in_progress'])); ?></h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2"><?php echo e(__('index.finished_projects')); ?></h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3><?php echo e(number_format($projectCardDetail['completed'])); ?></h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2"><?php echo e(__('index.cancelled_projects')); ?></h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3><?php echo e(number_format($projectCardDetail['cancelled'])); ?></h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('client_detail')): ?>
                    <div class="col-xxl-8 col-xl-8 mb-4 d-flex">
                        <div class="card card-table flex-fill">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0"><?php echo e(__('index.top_clients')); ?></h3>
                                <a href="<?php echo e(route('admin.clients.index')); ?>"><?php echo e(__('index.view_all_clients')); ?></a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table custom-table mb-0">
                                        <thead>
                                        <tr>
                                            <th><?php echo e(__('index.name')); ?></th>
                                            <th class="text-center"><?php echo e(__('index.email')); ?></th>
                                            <th class="text-center"><?php echo e(__('index.contact')); ?></th>
                                            <th class="text-center"><?php echo e(__('index.project')); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $topClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td class="table-avatar w-35">

                                                    <a href="<?php echo e(route('admin.clients.show',$client->id)); ?>"
                                                       class="avatar">
                                                        <img alt=""
                                                             src="<?php echo e(asset(Client::UPLOAD_PATH.$client->avatar)); ?>">
                                                        <span class="ms-1"><?php echo e(ucfirst($client->name)); ?></span>
                                                    </a>

                                                </td>
                                                <td class="text-center"><?php echo e($client->email); ?></td>
                                                <td class="text-center">
                                                    <?php echo e($client->contact_no); ?>

                                                </td>

                                                <td class="text-center">
                                                    <?php echo e($client->project_count); ?>

                                                </td>
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
                    </div>
                <?php endif; ?>

                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('project_detail')): ?>
                    <div class="col-xxl-4 col-xl-4 mb-4 d-flex">
                        <div class="card card-table flex-fill">
                            <div class="card-header text-center">
                                <h3 class="card-title mb-0"><?php echo e(__('index.task_details')); ?></h3>
                            </div>
                            <div class="card-body text-center">
                                <canvas id="tasksChart"></canvas>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('project_detail')): ?>
                <div class="card card-table flex-fill">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><?php echo e(__('index.recent_projects')); ?></h3>
                        <a href="<?php echo e(route('admin.projects.index')); ?>"><?php echo e(__('index.view_all_projects')); ?></a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table custom-table mb-0">
                                <thead>
                                <tr>
                                    <th class="w-25"><?php echo e(__('index.title')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.date_start')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.deadline')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.leader')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.completion')); ?></th>
                                    <th class="text-center"><?php echo e(__('index.priority')); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $recentProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="w-25">
                                            <a href="<?php echo e(route('admin.projects.show',$project->id)); ?>"><?php echo e(ucfirst($project->name)); ?> </a>
                                        </td>
                                        <td class="text-center"><?php echo e(AppHelper::formatDateForView($project->start_date)); ?></td>
                                        <td class="text-center">
                                            <?php echo e(AppHelper::formatDateForView($project->deadline)); ?>

                                        </td>

                                        <td class="member-listed text-center">
                                            <?php $__empty_2 = true; $__currentLoopData = $project->projectLeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $leader): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>

                                                <button type="button" class="p-0 border-0 bg-transparent ms-n3 "
                                                        disabled data-toggle="tooltip" data-placement="top"
                                                        title="<?php echo e($leader->user ? ucfirst($leader->user->name) : 'Project Leader'); ?>">
                                                    <img class="rounded-circle" style="object-fit: cover"
                                                         src="<?php echo e($leader->user ? asset(User::AVATAR_UPLOAD_PATH.$leader->user->avatar):
                                                                    asset('assets/images/img.png')); ?>"
                                                         alt="profile">
                                                </button>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>

                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress">
                                                <div class="progress-bar color2 rounded"
                                                     role="progressbar"
                                                     style="<?php echo e(AppHelper::getProgressBarStyle($project->getProjectProgressInPercentage())); ?>"
                                                     aria-valuenow="25"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                    <span><?php echo e(($project->getProjectProgressInPercentage())); ?> %</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                                    <span
                                                        class="btn btn-<?php echo e($projectPriority[$project->priority]); ?> btn-xs cursor-default">
                                                            <?php echo e(ucfirst($project->priority)); ?>

                                                    </span>
                                        </td>
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
            <?php endif; ?>
        <?php endif; ?>
    </section>
<?php $__env->stopSection(); ?>

<script src="<?php echo e(asset('assets/vendors/chartjs/Chart.min.js')); ?>"></script>

<?php $__env->startSection('scripts'); ?>
    <script>
        let translatedStrings = <?php echo json_encode(__('index'), 15, 512) ?>;
    </script>
    <?php echo $__env->make('admin.dashboard_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>










<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>