<?php $__env->startSection('title', __('index.attendance')); ?>

<?php $__env->startSection('action', __('index.employee_attendance_lists')); ?>
<?php $__env->startSection('styles'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.attendance.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.attendance_report')); ?></h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="<?php echo e(route('admin.attendance.export')); ?>" method="get">
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

                            <select class="form-select" name="department_id" id="department_id">
                                <option selected disabled> <?php echo e(__('index.select_department')); ?> </option>

                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">

                            <select class="form-select" name="employee_id" id="employee_id">
                                <option selected disabled><?php echo e(__('index.select_employee')); ?></option>
                            </select>
                        </div>
                        <?php if($isBsEnabled): ?>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <input type="text" class="form-control startNpDate" id="start_date" name="start_date"
                                       required value="" autocomplete="off" placeholder="Start Date">
                            </div>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <input type="text" class="form-control npDeadline" id="end_date" name="end_date"
                                       value=""
                                       autocomplete="off" placeholder="End Date">
                            </div>
                        <?php else: ?>
                            <div class="col-lg-3 col-md-4 mb-4">
                                <input type="text" class="form-control" id="attendance_date" name="attendance_date"
                                       value=""/>
                            </div>
                        <?php endif; ?>
                        <div class="col-lg-3 col-md-4 d-md-flex">
                            <button type="submit" class="btn btn-block btn-secondary me-md-2 me-0 mb-md-4 mb-2"><?php echo e(__('index.csv_export')); ?></button>
                            <a class="btn btn-block btn-primary me-md-2 me-0 mb-4"
                               href="<?php echo e(route('admin.attendance.export')); ?>"><?php echo e(__('index.reset')); ?></a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </section>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(function () {
            $('input[name="attendance_date"]').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('input[name="attendance_date"]').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                addParameterDownloadExcel();
            });

            $('input[name="attendance_date"]').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                addParameterDownloadExcel();
            });
        });

        $('#start_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

        $('#end_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });


    </script>
    <?php echo $__env->make('admin.attendance.common.filter_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/attendance/export.blade.php ENDPATH**/ ?>