<?php use App\Helpers\AppHelper; ?>


<?php $__env->startSection('title',__('index.settings')); ?>



<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo app('translator')->get('index.dashboard'); ?></a></li>
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.attendance-settings.index')); ?>"><?php echo app('translator')->get('index.attendance_settings'); ?></a></li>
            </ol>
        </nav>


        <div class="card mb-4">
            <div class="card-header">

                <h5><?php echo e(__('index.attendance_settings')); ?></h5>

            </div>
            <div class="card-body">



                <div class="table-responsive">
                    <form class="forms-sample" id="attendanceSettingForm" action="<?php echo e(route('admin.attendance-settings.update')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <table id="dataTableExample" class="table">
                            <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $attendanceSettings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $datum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <?php echo e(ucfirst(__('seeder.' . $datum->slug))); ?> <span style="color: red">*</span>
                                    </td>
                                    <td>
                                        <?php if($datum->slug == 'attendance_method'): ?>
                                            <select class="form-select" id="attendanceMethod" name="attendance_method[]" multiple>
                                                <?php $__currentLoopData = \App\Models\AttendanceSetting::ATTENDANCE_METHOD; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($enum); ?>" <?php echo e(in_array($enum, $datum->values ?? []) ? 'selected' : ''); ?>> <?php echo e(ucfirst($enum)); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        <?php elseif($datum->value === null && $datum->values === null): ?>
                                            <label class="switch">
                                                <input class="toggleStatus" data-href="<?php echo e(route('admin.attendance-settings.toggle-status', $datum->id)); ?>" type="checkbox" <?php echo e($datum->status == 1 ? 'checked' : ''); ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        <?php else: ?>
                                            <input type="number" class="form-control" min="1" oninput="validity.valid||(value='');" name="attendance_limit" value="<?php echo e($datum->value); ?>" autocomplete="off">
                                        <?php endif; ?>
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
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="link-icon" data-feather="plus"></i> <?php echo app('translator')->get('index.update'); ?>
                        </button>
                    </form>

                </div>



            </div>
        </div>

    </section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
    <script>
        $(document).ready(function () {
            $("#attendanceMethod").select2();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.toggleStatus').change(function (event) {
                event.preventDefault();
                let input = $(this);
                let checked = input.prop('checked');
                let href = input.data('href');
                Swal.fire({
                    title: '<?php echo app('translator')->get('index.change_status_confirm'); ?>',
                    showDenyButton: true,
                    confirmButtonText: `<?php echo app('translator')->get('index.yes'); ?>`,
                    denyButtonText: `<?php echo app('translator')->get('index.no'); ?>`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'GET',
                            url: href,
                            dataType: 'json',
                            success: function (data) {
                                if (data.success) {
                                    Swal.fire('Success', data.message.toString(), 'success');
                                } else {
                                    Swal.fire('Error', data.message.toString(), 'error');
                                    input.prop('checked', !checked);
                                }
                            },
                            error: function (xhr, status, error) {
                                let msg = xhr.responseJSON?.message || error || 'An error occurred';
                                Swal.fire('Error', msg.toString(), 'error');
                                input.prop('checked', !checked);
                            }
                        });
                    } else if (result.isDenied) {
                        input.prop('checked', !checked);
                    }
                });
            });

            $('#attendanceSettingForm').submit(function (event) {
                event.preventDefault();
                let form = $(this);

                $.ajax({
                    type: 'PUT',
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                confirmButtonColor: '#3085d6'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Something went wrong.',
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            let errors = '';
                            let err = xhr.responseJSON.errors;
                            for (let key in err) {
                                if (err.hasOwnProperty(key)) {
                                    errors += err[key][0] + '\n';
                                }
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errors,
                                confirmButtonColor: '#d33'
                            });
                        } else {
                            // Other errors
                            let msg = xhr.responseJSON?.message || 'An error occurred.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg,
                                confirmButtonColor: '#d33'
                            });
                        }
                    }
                });
            });

        });
    </script>
<?php $__env->stopSection(); ?>






<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/attendanceSetting/index.blade.php ENDPATH**/ ?>