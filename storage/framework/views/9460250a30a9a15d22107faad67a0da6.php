<?php $__env->startSection('title', __('index.logout_requests')); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('index.dashboard')); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('index.logout_requests')); ?></li>
            </ol>
        </nav>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.logout_request_filter')); ?></h6>
            </div>

            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.logout-requests.index')); ?>" method="get">
                <div class="row align-items-center">

                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  selected  disabled><?php echo e(__('index.select_branch')); ?>

                                </option>
                                <?php if(isset($companyDetail)): ?>
                                    <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($branch->id); ?>"
                                            <?php echo e((isset($filterData['branch_id']) && $filterData['branch_id']  == $branch->id) ? 'selected': ''); ?>>
                                            <?php echo e(ucfirst($branch->name)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?>


                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option  selected  disabled><?php echo e(__('index.select_department')); ?>

                            </option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option  selected  disabled><?php echo e(__('index.select_employee')); ?>

                            </option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 d-md-flex gap-2">
                        <button type="submit" class="btn btn-block btn-success mb-4"><?php echo e(__('index.filter')); ?></button>

                        <a class="btn btn-block btn-primary  mb-4"
                           href="<?php echo e(route('admin.logout-requests.index')); ?>"><?php echo e(__('index.reset')); ?></a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.logout_requests')); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo e(__('index.employee_name')); ?></th>
                            <th><?php echo e(__('index.logout_request_status')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $logoutRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(++$key); ?></td>
                                <td><strong><?php echo e(removeSpecialChars($value->name)); ?></strong></td>
                                <td>
                                    <button class="btn btn-primary btn-xs acceptLogoutRequest"
                                            data-href="<?php echo e(route('admin.logout-requests.accept', $value->id)); ?>">
                                        <?php echo e(__('index.take_action')); ?>

                                    </button>
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

    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.acceptLogoutRequest').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '<?php echo e(__('index.confirm_accept_logout_request')); ?>',
                    showDenyButton: true,
                    confirmButtonText: `<?php echo e(__('index.yes')); ?>`,
                    denyButtonText: `<?php echo e(__('index.no')); ?>`,
                    padding: '10px 50px 10px 50px',
                    // width:'500px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })
        });

    </script>
    <?php echo $__env->make('admin.attendance.common.filter_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/logoutRequest/index.blade.php ENDPATH**/ ?>