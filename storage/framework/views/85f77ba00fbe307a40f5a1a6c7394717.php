<?php $__env->startSection('title',__('index.router')); ?>

<?php $__env->startSection('action',__('index.lists')); ?>

<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_router')): ?>
        <a href="<?php echo e(route('admin.routers.create')); ?>">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i> <?php echo app('translator')->get('index.add_router'); ?>
            </button>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.router.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo app('translator')->get('index.router_filter'); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.routers.index')); ?>" method="get">

                <div class="row align-items-center">
                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  <?php echo e(!isset($filterParameters['branch_id']) || old('branch_id') ? 'selected': ''); ?>  disabled><?php echo e(__('index.select_branch')); ?>

                                </option>
                                <?php if(isset($companyDetail)): ?>
                                    <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($branch->id); ?>"
                                            <?php echo e((isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected': ''); ?>>
                                            <?php echo e(ucfirst($branch->name)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2"><?php echo app('translator')->get('index.filter'); ?></button>
                            <a class="btn btn-block btn-primary" href="<?php echo e(route('admin.routers.index')); ?>"><?php echo app('translator')->get('index.reset'); ?></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.router_lists')); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo app('translator')->get('index.router_bssid'); ?></th>
                            <th><?php echo app('translator')->get('index.branch'); ?> </th>
                            <th><?php echo app('translator')->get('index.company'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('index.status'); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_router','delete_router'])): ?>
                                <th class="text-center"><?php echo app('translator')->get('index.action'); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        <?php $__empty_1 = true; $__currentLoopData = $routers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e((($routers->currentPage()- 1 ) * (\App\Models\Router::RECORDS_PER_PAGE) + (++$key))); ?></td>
                                <td><?php echo e(($value->router_ssid)); ?></td>
                                <td><?php echo e(ucfirst($value->branch->name)); ?></td>
                                <td><?php echo e(ucfirst($value->company->name)); ?></td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="<?php echo e(route('admin.routers.toggle-status',$value->id)); ?>"
                                               type="checkbox" <?php echo e(($value->is_active) == 1 ?'checked':''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_router','delete_router'])): ?>
                                    <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center align-items-center">
                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_router')): ?>
                                            <li class="me-2">
                                                <a href="<?php echo e(route('admin.routers.edit',$value->id)); ?>" title="<?php echo e(__('index.edit')); ?>">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_router')): ?>
                                            <li>
                                                <a class="deleteRouter"
                                                   data-href="<?php echo e(route('admin.routers.delete',$value->id)); ?>" title="<?php echo e(__('index.delete')); ?>">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
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

        <div class="dataTables_paginate">
            <?php echo e($routers->appends($_GET)->links()); ?>

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
                    title: 'Are you sure you want to change status ?',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }else if (result.isDenied) {
                        (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            })

            $('.deleteRouter').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: 'Are you sure you want to Delete Router Detail ?',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
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


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/router/index.blade.php ENDPATH**/ ?>