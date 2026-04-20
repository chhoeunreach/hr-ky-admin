<?php $__env->startSection('title', __('index.post')); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('index.dashboard')); ?></a></li>
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.posts.index')); ?>"><?php echo e(__('index.post_section')); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('index.posts')); ?></li>
            </ol>

            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_post')): ?>
                <a href="<?php echo e(route('admin.posts.create')); ?>">
                    <button class="btn btn-primary add_department">
                        <i class="link-icon" data-feather="plus"></i><?php echo e(__('index.add_post')); ?>

                    </button>
                </a>
            <?php endif; ?>
        </nav>


        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.post_filter')); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.posts.index')); ?>" method="get">

                <div class="row align-items-center">
                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  selected  disabled><?php echo e(__('index.select_branch')); ?>

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
                        <input type="text" placeholder="<?php echo e(__('index.search_by_post_name')); ?>" name="name" value="<?php echo e($filterParameters['name']); ?>" class="form-control">

                    </div>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select" name="per_page">
                            <option value="25" <?php echo e((string) ($filterParameters['per_page'] ?? '') === '25' ? 'selected' : ''); ?>>25</option>
                            <option value="50" <?php echo e((string) ($filterParameters['per_page'] ?? '') === '50' ? 'selected' : ''); ?>>50</option>
                            <option value="10" <?php echo e((string) ($filterParameters['per_page'] ?? '') === '10' ? 'selected' : ''); ?>>10</option>
                            <option value="all" <?php echo e((string) ($filterParameters['per_page'] ?? '') === 'all' ? 'selected' : ''); ?>>All</option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-6 mb-4 mb-md-4">
                        <div class="d-flex float-lg-end">
                            <button type="submit" class="btn btn-block btn-success me-2"><?php echo e(__('index.filter')); ?></button>
                            <a href="<?php echo e(route('admin.posts.index')); ?>" class="btn btn-block btn-primary"><?php echo e(__('index.reset')); ?></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card  support-main">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.post_lists')); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo e(__('index.post_name')); ?></th>
                            <th><?php echo e(__('index.department')); ?></th>
                            <th class="text-center"><?php echo e(__('index.total_employee')); ?></th>
                            <th class="text-center"><?php echo e(__('index.status')); ?></th>

                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_post','delete_post'])): ?>
                                <th class="text-center"><?php echo e(__('index.action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(($posts->firstItem() ?? 0) + $key); ?></td>
                                <td><?php echo e(ucfirst($value->post_name)); ?></td>
                                <td><?php echo e(ucfirst($value->department->dept_name)); ?></td>
                                <td class="text-center">
                                    <p class="btn btn-info btn-sm" id="showEmployee" data-employee="<?php echo e($value->employees); ?>">
                                        <?php echo e($value->employees_count); ?>

                                    </p>
                                </td>

                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="<?php echo e(route('admin.posts.toggle-status', $value->id)); ?>"
                                               type="checkbox" <?php echo e(($value->is_active == 1) ? 'checked' : ''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_post','delete_post'])): ?>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_post')): ?>
                                                <li class="me-2">
                                                    <a href="<?php echo e(route('admin.posts.edit', $value->id)); ?>">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_post')): ?>
                                                <li>
                                                    <a class="deletePost" href="#"
                                                       data-href="<?php echo e(route('admin.posts.delete', $value->id)); ?>">
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
                                    <p class="text-center"><b><?php echo e(__('index.no_records_found')); ?></b></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-4">
            <?php echo e($posts->appends($_GET)->links()); ?>

        </div>

        <?php echo $__env->make('admin.post.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.post.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/post/index.blade.php ENDPATH**/ ?>