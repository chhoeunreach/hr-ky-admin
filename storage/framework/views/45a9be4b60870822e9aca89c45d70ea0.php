<?php use App\Enum\EventStatusEnum;use App\Helpers\AppHelper; ?>


<?php $__env->startSection('title',__('index.event')); ?>


<?php $__env->startSection('action',$isBsEnabled ? __('index.list') : __('index.event_calendar')); ?>
<?php $__env->startSection('styles'); ?>
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.css" rel="stylesheet"/>
    <style>
        .fc-event {
            cursor: pointer;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_event')): ?>
        <a href="<?php echo e(route('admin.event.create')); ?>">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i><?php echo e(__('index.create_event')); ?>

            </button>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.event.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


        <?php if($isBsEnabled): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><?php echo app('translator')->get('index.event_filter'); ?></h6>
                </div>
                <form class="forms-sample card-body pb-0" id="filter_form" action="<?php echo e(route('admin.event.index')); ?>" method="get">

                    <div class="row align-items-center">
                        <?php if(!isset(auth()->user()->branch_id)): ?>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option selected disabled><?php echo e(__('index.select_branch')); ?>

                                    </option>
                                    <?php if(isset($companyDetail)): ?>
                                        <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($branch->id); ?>"
                                                <?php echo e((isset($filterParameters['branch_id']) && $filterParameters['branch_id']  == $branch->id) ? 'selected': ''); ?>>
                                                <?php echo e(ucfirst($branch->name)); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php endif; ?>


                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" multiple name="department_id[]" id="department_id">
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" multiple name="employee_id[]" id="employee_id">
                            </select>
                        </div>


                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text" id="nepali-datepicker-from"
                                   name="start_date"
                                   value="<?php echo e($filterParameters['start_date'] ?? ''); ?>"
                                   placeholder="mm/dd/yyyy"
                                   class="form-control nepali_date"/>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text" id="nepali-datepicker-from"
                                   name="end_date"
                                   value="<?php echo e($filterParameters['end_date'] ?? ''); ?>"
                                   placeholder="mm/dd/yyyy"
                                   class="form-control nepali_date"/>
                        </div>


                        <div class="col-lg-2 col-md-6 mb-4">
                            <div class="d-flex">
                                <button type="submit"
                                        class="btn btn-block btn-success me-2"><?php echo app('translator')->get('index.filter'); ?></button>
                                <a class="btn btn-block btn-primary"
                                   href="<?php echo e(route('admin.event.index')); ?>"><?php echo app('translator')->get('index.reset'); ?></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card support-main">
                <div class="card-header">
                    <h6 class="card-title mb-0"><?php echo e(__('index.event_list')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo e(__('index.title')); ?></th>
                                <th><?php echo e(__('index.host')); ?></th>
                                <th><?php echo e(__('index.location')); ?></th>
                                <th><?php echo e(__('index.date')); ?></th>
                                <th class="text-center"><?php echo e(__('index.status')); ?></th>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['show_event','delete_event','update_event'])): ?>
                                    <th class="text-center"><?php echo e(__('index.action')); ?></th>
                                <?php endif; ?>
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                $color = [
                                    EventStatusEnum::completed->value => 'primary',
                                    EventStatusEnum::ongoing->value => 'success',
                                    EventStatusEnum::pending->value => 'secondary',
                                    EventStatusEnum::cancelled->value => 'warning',
                                ];

                                ?>
                            <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(++$key); ?></td>
                                    <td><?php echo e($value->title); ?></td>
                                    <td><?php echo e($value->host ?? __('index.not_available')); ?></td>
                                    <td><?php echo e($value->location); ?></td>
                                    <td>
                                        <?php if(is_null($value->end_date) || strtotime($value->start_date) == strtotime($value->end_date)): ?>

                                            <?php echo e(AppHelper::formatDateForView($value->start_date)); ?>

                                        <?php else: ?>
                                            <?php echo e(AppHelper::formatDateForView($value->start_date)); ?>

                                            - <?php echo e(AppHelper::formatDateForView($value->end_date)); ?>

                                        <?php endif; ?>

                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-<?php echo e($color[$value->status]); ?> btn-xs">
                                            <?php echo e(ucfirst($value->status)); ?>

                                        </button>
                                    </td>

                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_event')): ?>
                                                <li class="me-2">
                                                    <a href="<?php echo e(route('admin.event.edit',$value->id)); ?>"
                                                       title="<?php echo e(__('index.edit_event_detail')); ?> "
                                                       class="d-flex pb-1 align-items-center">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_event')): ?>
                                                <li class="me-2">
                                                    <a href="javascript:void(0)"
                                                       onclick="showEventDetails('<?php echo e(route('admin.event.show',$value->id)); ?>')"
                                                       class="d-flex pb-1 align-items-center"
                                                       title="<?php echo e(__('index.show_event')); ?>">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_event')): ?>
                                                <li>
                                                    <a
                                                        data-href="<?php echo e(route('admin.event.delete',$value->id)); ?>"
                                                        title="<?php echo e(__('index.delete_event')); ?>"
                                                        class="d-flex align-items-center delete">
                                                        <i class="link-icon" data-feather="delete"></i>
                                                    </a>
                                                </li>

                                            <?php endif; ?>
                                        </ul>
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

            <div class="dataTables_paginate mt-3">
                <?php echo e($events->appends($_GET)->links()); ?>

            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div id="calendar"></div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="pill" href="#isUpcoming">Upcoming
                                        Events</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="pill" href="#isPast">Past Events</a>
                                </li>
                            </ul>

                            <div class="tab-content mt-4">
                                <div id="isUpcoming" class="tab-pane active">
                                    <div class="event-group-item p-0">
                                        <?php $__currentLoopData = $upcomingEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="event-list border-bottom pb-3 mb-3">
                                                <div class="d-flex justify-content-between w-100 mb-2">
                                                    <h5 style="color: <?php echo e($event->background_color ?? 'inherit'); ?>">
                                                        <?php echo e($event->title); ?>

                                                    </h5>

                                                    <div class="btn-group card-option">
                                                        <button type="button" class="btn p-0" data-bs-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                            <i class="link-icon" data-feather="more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end p-2" style="">

                                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_event')): ?>
                                                                <a href="<?php echo e(route('admin.event.edit',$event->id)); ?>"
                                                                   title="<?php echo e(__('index.edit_event_detail')); ?> "
                                                                   class="d-flex pb-1 align-items-center">
                                                                    <i class="link-icon me-2" data-feather="edit"></i>
                                                                    Edit
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_event')): ?>
                                                                <a href="javascript:void(0)"
                                                                   onclick="showEventDetails('<?php echo e(route('admin.event.show',$event->id)); ?>')"
                                                                   class="d-flex pb-1 align-items-center">
                                                                    <i class="link-icon me-2" data-feather="eye"></i>
                                                                    View
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_event')): ?>
                                                                <a
                                                                    data-href="<?php echo e(route('admin.event.delete',$event->id)); ?>"
                                                                    title="<?php echo e(__('index.delete_event')); ?>"
                                                                    class="d-flex align-items-center delete">
                                                                    <i class="link-icon me-2" data-feather="delete"></i>
                                                                    Delete
                                                                </a>
                                                            <?php endif; ?>

                                                        </div>
                                                    </div>
                                                </div>

                                                <p>

                                                    <?php if(is_null($event->end_date) || strtotime($event->start_date) == strtotime($event->end_date)): ?>

                                                        Date: <?php echo e(AppHelper::formatDateForView($event->start_date)); ?>

                                                    <?php else: ?>

                                                        Date
                                                        : <?php echo e(AppHelper::formatDateForView($event->start_date)); ?>

                                                        - <?php echo e(AppHelper::formatDateForView($event->end_date)); ?>

                                                    <?php endif; ?>
                                                    <br>
                                                    Time
                                                    : <?php echo e(AppHelper::convertLeaveTimeFormat($event->start_time)); ?>

                                                    - <?php echo e(AppHelper::convertLeaveTimeFormat($event->end_time)); ?>



                                                </p>


                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                </div>
                                <div id="isPast" class="tab-pane fade">
                                    <div class="event-group-item p-0">
                                        <?php $__currentLoopData = $pastEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="event-list border-bottom pb-3 mb-3">
                                                <div class="d-flex justify-content-between w-100 mb-2">
                                                    <h5 style="color: <?php echo e($event->background_color ?? 'inherit'); ?>">
                                                        <?php echo e($event->title); ?>

                                                    </h5>

                                                    <div class="btn-group card-option">
                                                        <button type="button" class="btn p-0" data-bs-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                            <i class="link-icon" data-feather="more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end p-2" style="">

                                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_event')): ?>
                                                                <a href="<?php echo e(route('admin.event.edit',$event->id)); ?>"
                                                                   title="<?php echo e(__('index.edit_event_detail')); ?> "
                                                                   class="d-flex pb-1 align-items-center">
                                                                    <i class="link-icon me-2" data-feather="edit"></i>
                                                                    Edit
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_event')): ?>
                                                                <a href="javascript:void(0)"
                                                                   onclick="showEventDetails('<?php echo e(route('admin.event.show',$event->id)); ?>')"
                                                                   class="d-flex pb-1 align-items-center">
                                                                    <i class="link-icon me-2" data-feather="eye"></i>
                                                                    View
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_event')): ?>
                                                                <a
                                                                    data-href="<?php echo e(route('admin.event.delete',$event->id)); ?>"
                                                                    title="<?php echo e(__('index.delete_event')); ?>"
                                                                    class="d-flex align-items-center delete">
                                                                    <i class="link-icon me-2" data-feather="delete"></i>
                                                                    Delete
                                                                </a>
                                                            <?php endif; ?>

                                                        </div>
                                                    </div>
                                                </div>

                                                <p>

                                                    <?php if(is_null($event->end_date) || strtotime($event->start_date) == strtotime($event->end_date)): ?>

                                                        Date: <?php echo e(AppHelper::formatDateForView($event->start_date)); ?>

                                                        <br>
                                                        Time: <?php echo e(AppHelper::convertLeaveTimeFormat($event->start_time)); ?>

                                                        - <?php echo e(AppHelper::convertLeaveTimeFormat($event->end_time)); ?>

                                                    <?php else: ?>

                                                        Date
                                                        : <?php echo e(AppHelper::formatDateForView($event->start_date)); ?>

                                                        - <?php echo e(AppHelper::formatDateForView($event->end_date)); ?>

                                                        <br>
                                                        Time
                                                        : <?php echo e(AppHelper::convertLeaveTimeFormat($event->start_time)); ?>

                                                        | <?php echo e(AppHelper::convertLeaveTimeFormat($event->end_time)); ?>

                                                    <?php endif; ?>

                                                </p>


                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        <?php endif; ?>

    </section>

    <?php echo $__env->make('admin.event.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.event.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let calendarEl = document.getElementById('calendar');
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                slotMinTime: '8:00:00',
                slotMaxTime: '19:00:00',
                events: <?php echo json_encode($events, 15, 512) ?>,
                eventDisplay: 'block', // This ensures the full block is colored
                eventColor: function (eventInfo) {
                    return eventInfo.event.extendedProps.color || eventInfo.event.color;
                },
                eventClick: function (info) {
                    const eventId = info.event.id;
                    const url = "<?php echo e(route('admin.event.show', ':id')); ?>".replace(':id', eventId);

                    showEventDetails(url);
                }
            });
            calendar.render();
        });


        function showEventDetails(url) {
            $.get(url, function (response) {
                if (response && response.data) {
                    const data = response.data;
                    let time = data.start_time + ' - ' + data.end_time;
                    let date = data.end_date !== '' ? data.start_date + ' - ' + data.end_date : data.start_date;
                    $('.meetingTitle').html('Event Detail');
                    $('.title').text(data.title);
                    $('.start_date').text(date);
                    $('.end_date').text(time);
                    $('.venue').text(data.location);
                    $('.description').text(data.description);
                    $('.creator').text(data.creator);
                    $('.host').text(data.host);

                    if (data.attachment) {
                        $('.image').attr('src', data.attachment).show();
                    } else {
                        $('.image').hide();
                    }

                    const modal = new bootstrap.Modal(document.getElementById('eventDetail'));
                    modal.show();
                }
            }).fail(function (xhr, status, error) {
                // Handle error
                alert('Error loading event details. Please try again.');
                console.error('Error:', error);
            });
        }

    </script>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/event/index.blade.php ENDPATH**/ ?>