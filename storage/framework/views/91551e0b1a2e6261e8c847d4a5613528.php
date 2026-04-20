<?php $__env->startSection('title',__('index.app_setting')); ?>
<?php $__env->startSection('main-content'); ?>

    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo app('translator')->get('index.dashboard'); ?> </a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo app('translator')->get('index.app_settings'); ?></li>
            </ol>
            <button
                class="btn btn-success btn-md"
                data-bs-toggle="modal"
                data-bs-target="#addslider">
                <?php echo app('translator')->get('index.export_database_data'); ?>
            </button>
        </nav>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center"><?php echo app('translator')->get('index.action'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $appSettings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                <tr>
                                    <td><strong> <?php echo e(( $value->name =='override bssid') ? __('index.check_router_bssid'):__('seeder.'.$value->slug)); ?> </strong> </td>
                                    <td class="text-center">
                                        <label class="switch">
                                            <input class="toggleStatus" href="<?php echo e(route('admin.app-settings.toggle-status',$value->id)); ?>"
                                                   type="checkbox" <?php echo e(($value->status) == 1 ?'checked':''); ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                </tr>


                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="100%">
                                        <p class="text-center"><b><?php echo app('translator')->get('index.no_records_found'); ?></b></p>
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
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo app('translator')->get('index.export_table_data'); ?></h5>
                    </div>
                    <div class="modal-body">
                        <a href="<?php echo e(route('admin.leave-type-export')); ?>">
                            <button class="btn btn-secondary btn-sm"><?php echo app('translator')->get('index.leave_types'); ?> </button>
                        </a>
                        <a href="<?php echo e(route('admin.leave-request-export')); ?>">
                            <button class="btn btn-success btn-sm"><?php echo app('translator')->get('index.leave_requests'); ?> </button>
                        </a>
                        <a href="<?php echo e(route('admin.employee-lists-export')); ?>">
                            <button class="btn btn-warning btn-sm"><?php echo app('translator')->get('index.employee_lists'); ?> </button>
                        </a>
                        <a href="<?php echo e(route('admin.attendance-lists-export')); ?>">
                            <button class="btn btn-danger btn-sm"><?php echo app('translator')->get('index.attendances'); ?>  </button>
                        </a>
                    </div>
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

            $('.toggleStatus').change(function (event) {
                event.preventDefault();
                var status = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: '<?php echo app('translator')->get('index.change_status_confirm'); ?>',
                    showDenyButton: true,
                    confirmButtonText: `<?php echo app('translator')->get('index.yes'); ?>`,
                    denyButtonText: `<?php echo app('translator')->get('index.no'); ?>`,
                    padding:'10px 50px 10px 50px',
                    // width:'500px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }else if (result.isDenied) {
                        (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            })


        });
    </script>
<?php $__env->stopSection(); ?>





<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/appSetting/index.blade.php ENDPATH**/ ?>