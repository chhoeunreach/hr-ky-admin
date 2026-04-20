<?php $__env->startSection('title',__('index.role')); ?>

<?php $__env->startSection('action',__('index.lists')); ?>

<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_role')): ?>
        <a href="<?php echo e(route('admin.roles.create')); ?>">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i><?php echo app('translator')->get('index.add_role'); ?>
            </button>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('main-content'); ?>

    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.role.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo app('translator')->get('index.role_list'); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo app('translator')->get('index.role'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('index.status'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('index.can_login'); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('role_permission')): ?>
                                <th class="text-center"><?php echo app('translator')->get('index.action'); ?></th>
                            <?php endif; ?>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(++$key); ?></td>
                                <td><?php echo e(ucfirst($value->name)); ?></td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="<?php echo e(route('admin.roles.toggle-status',$value->id)); ?>"
                                               type="checkbox" <?php echo e(($value->is_active) == 1 ?'checked':''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <td class="text-center">
                                    <span><?php echo e($value->backend_login_authorize ? __('index.yes'):__('index.no')); ?></span>
                                </td>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('role_permission')): ?>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 align-items-center justify-content-center">

                                            <li class="me-2">
                                                <a href="<?php echo e(route('admin.roles.edit',$value->id)); ?>"
                                                   title="<?php echo app('translator')->get('index.edit'); ?>">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>

                                            <li>
                                                <a class="deleteRole"
                                                   data-href="<?php echo e(route('admin.roles.delete',$value->id)); ?>"
                                                   title="<?php echo app('translator')->get('index.delete'); ?>">
                                                    <i class="link-icon" data-feather="delete"></i>
                                                </a>
                                            </li>

                                            <li>
                                                <span class="ms-2">
                                                     <a href="<?php echo e(route('admin.roles.permission',$value->id)); ?>">
                                                        <button class="btn btn-xs btn-primary ">
                                                          <?php echo app('translator')->get('index.assign_permissions'); ?>
                                                        </button>
                                                     </a>
                                                </span>
                                            </li>

                                        </ul>
                                    </td>
                                <?php endif; ?>
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
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    } else if (result.isDenied) {
                        (status === 0) ? $(this).prop('checked', true) : $(this).prop('checked', false)
                    }
                })
            })

            $('.deleteRole').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '<?php echo app('translator')->get('index.confirm_role_deletion'); ?>',
                    showDenyButton: true,
                    confirmButtonText: `<?php echo app('translator')->get('index.yes'); ?>`,
                    denyButtonText: `<?php echo app('translator')->get('index.no'); ?>`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })
        });
    </script>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/role/index.blade.php ENDPATH**/ ?>