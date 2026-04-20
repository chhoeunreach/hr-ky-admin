<?php $__env->startSection('title', __('index.title_branch')); ?>
<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_branch')): ?>
        <a href="<?php echo e(route('admin.branch.create')); ?>">
            <button class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> <?php echo e(__('index.add_branch')); ?></button>
        </a>
    <?php endif; ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.branch.common.breadcrumb', ['title' => __('index.branch')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.branch_lists')); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.branch.index')); ?>" method="get">
                <div class="row align-items-center">

                    <div class="col-lg-4 col-md-4 mb-4">
                        <input type="text" placeholder="<?php echo e(__('index.search_by_branch_name')); ?>" name="name" value="<?php echo e(($filterParameters['name'])); ?>" class="form-control">
                    </div>

                    <div class="col-lg-4 col-md-4 mb-4">
                        <select class="form-select form-select-lg" name="per_page">
                            <option value="10" <?php echo e(($filterParameters['per_page']) == 10 ? 'selected': ''); ?>>10</option>
                            <option value="25" <?php echo e(($filterParameters['per_page']) == 25 ? 'selected': ''); ?>>25</option>
                            <option value="50" <?php echo e(($filterParameters['per_page']) == 50 ? 'selected': ''); ?>>50</option>
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-3 d-flex">
                        <button type="submit" class="btn btn-block btn-success me-2 mb-4"><?php echo e(__('index.filter')); ?></button>

                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4" href="<?php echo e(route('admin.branch.index')); ?>"><?php echo e(__('index.reset')); ?></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.branch_lists')); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo e(__('index.branch_name')); ?></th>
                            <th><?php echo e(__('index.address')); ?></th>
                            <th class="text-center"><?php echo e(__('index.phone')); ?></th>
                            <th class="text-center"><?php echo e(__('index.total_employee')); ?></th>
                            <th class="text-center"><?php echo e(__('index.status')); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check(['edit_branch','delete_branch'])): ?>
                                <th class="text-center"><?php echo e(__('index.action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e((($branches->currentPage()- 1 ) * (\App\Models\Branch::RECORDS_PER_PAGE) + (++$key))); ?></td>
                                <td><?php echo e(ucfirst($value->name)); ?></td>
                                <td><?php echo e($value->address); ?></td>
                                <td class="text-center"><?php echo e($value->phone); ?></td>
                                <td class="text-center">
                                    <p class="btn btn-info btn-sm mb-0" id="showBranchEmployees" data-employee='<?php echo json_encode($value->employees, 15, 512) ?>'>
                                        <?php echo e($value->employees_count); ?>

                                    </p>
                                </td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="<?php echo e(route('admin.branch.toggle-status',$value->id)); ?>"
                                               type="checkbox" <?php echo e(($value->is_active) == 1 ?'checked':''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_branch','delete_branch'])): ?>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_branch')): ?>
                                                <li class="me-2">
                                                    <a href="<?php echo e(route('admin.branch.edit',$value->id)); ?>">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_branch')): ?>
                                                <li>
                                                    <a class="deleteBranch" data-href="<?php echo e(route('admin.branch.delete',$value->id)); ?>"><i class="link-icon"  data-feather="delete"></i></a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                                <?php endif; ?>
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

        <div class="dataTables_paginate">
            <?php echo e($branches->appends($_GET)->links()); ?>

        </div>

        <div class="modal fade" id="showEmployees" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                    </div>
                    <div class="modal-body">
                        <div class="row employeeList"></div>
                        <p class="postEmptyCase d-none"><?php echo e(__('index.post_empty')); ?></p>
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
                    title: '<?php echo e(__('index.are_you_sure_change_status')); ?>',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }else if (result.isDenied) {
                        (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            })

            $('.deleteBranch').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '<?php echo e(__('index.are_you_sure_delete_branch')); ?>',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            });

            $('body').on('click', '#showBranchEmployees', function (event) {
                event.preventDefault();
                let employee = $(this).data('employee');
                $('.employee').remove();
                $('.modal-title').html('<?php echo e(__("index.employee_list_title")); ?>');

                if (employee.length > 0) {
                    $('.postEmptyCase').addClass('d-none');
                    employee.forEach(function (data) {
                        let avatar = data.avatar ? '<?php echo e(asset(\App\Models\User::AVATAR_UPLOAD_PATH)); ?>' + '/' + data.avatar : '<?php echo e(asset('assets/images/img.png')); ?>';
                        $('.employeeList').append(
                            '<div class="col-lg-6 d-flex align-items-center mb-3 employee">' +
                            '<img class="rounded-circle w-25 me-2 employeeImage" style="object-fit: cover" src="' + avatar + '" alt="profile">' +
                            '<span class="employeeName">' + data.name + '</span>' +
                            '</div>'
                        );
                    });
                } else {
                    $('.postEmptyCase').removeClass('d-none');
                }

                $('#showEmployees').modal('show');
            });


        });

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/branch/index.blade.php ENDPATH**/ ?>