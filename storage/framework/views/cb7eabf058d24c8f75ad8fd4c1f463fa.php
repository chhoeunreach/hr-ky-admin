<?php $__env->startSection('title',__('index.support')); ?>

<?php $__env->startSection('action',__('index.query_lists')); ?>


<?php $__env->startSection('main-content'); ?>


    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div id="showFlashMessageResponse">
            <div class="alert alert-danger error d-none">
                <p class="errorMessageDelete"></p>
            </div>
        </div>

        <?php echo $__env->make('admin.support.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.support_filter')); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.supports.index')); ?>" method="get">

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
                            <select class="form-control" id="department_id" name="department_id">
                                <option selected disabled><?php echo e(__('index.select_department')); ?></option>
                            </select>
                        </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="status" id="status">
                            <option value="" <?php echo e(!isset($filterParameters['status']) ? 'selected': ''); ?> ><?php echo e(__('index.all')); ?></option>
                            <?php $__currentLoopData = \App\Models\Support::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php echo e(isset($filterParameters['status']) && $filterParameters['status'] == $value  ? 'selected': ''); ?> >
                                    <?php echo e(removeSpecialChars($value)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="is_seen" id="is_seen">
                            <option value="" <?php echo e(!isset($filterParameters['is_seen']) ? 'selected': ''); ?> ><?php echo e(__('index.all')); ?></option>
                            <option value="0" <?php echo e(isset($filterParameters['is_seen']) && $filterParameters['is_seen'] == 0 ? 'selected': ''); ?> ><?php echo e(__('index.unseen')); ?></option>
                            <option value="1" <?php echo e(isset($filterParameters['is_seen']) && $filterParameters['is_seen'] == 1 ? 'selected': ''); ?> ><?php echo e(__('index.seen')); ?></option>
                        </select>
                    </div>

                    <?php if(\App\Helpers\AppHelper::ifDateInBsEnabled()): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text"  id="nepali-datepicker-from" name="query_from" value="<?php echo e($filterParameters['query_from']); ?>" placeholder="<?php echo e(__('index.from_date')); ?> (mm/dd/yyyy)" class="form-control queryFrom"/>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text" id="nepali-datepicker-to" name="query_to" value="<?php echo e($filterParameters['query_to']); ?>" placeholder="<?php echo e(__('index.to_date')); ?> (mm/dd/yyyy)" class="form-control queryTo"/>
                        </div>
                    <?php else: ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date" placeholder="<?php echo e(__('index.from_date')); ?> (mm/dd/yyyy)" value="<?php echo e($filterParameters['query_from']); ?>" name="query_from" class="form-control">
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date"  placeholder="<?php echo e(__('index.to_date')); ?> (mm/dd/yyyy)" value="<?php echo e($filterParameters['query_to']); ?>" name="query_to" class="form-control">
                        </div>
                    <?php endif; ?>

                    <div class="col-lg-3 col-md-6 mb-4">
                            <button type="submit" class="btn btn-block btn-secondary me-2"><?php echo e(__('index.filter')); ?></button>
                            <a class="btn btn-block btn-primary" href="<?php echo e(route('admin.supports.index')); ?>"><?php echo e(__('index.reset')); ?></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo app('translator')->get('index.support_list'); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo e(__('index.title')); ?></th>
                            <th class="text-center"><?php echo e(__('index.date')); ?> </th>
                            <th class="text-center"><?php echo e(__('index.query_by')); ?></th>
                            <th class="text-center"><?php echo e(__('index.branch')); ?></th>
                            <th class="text-center"><?php echo e(__('index.concerned_department')); ?> </th>
                            <th class="text-center"><?php echo e(__('index.status')); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['show_query_detail','delete_query'])): ?>
                                <th class="text-center"><?php echo e(__('index.action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                            $statusColor = [
                                'pending' => 'secondary',
                                'in_progress' => 'warning',
                                'solved' => 'success',
                            ]
                        ?>
                        <tr>

                        <?php $__empty_1 = true; $__currentLoopData = $supportQueries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="status<?php echo e($value->id); ?>">
                                <td><?php echo e((($supportQueries->currentPage()- 1 ) * (\App\Models\Support::RECORDS_PER_PAGE) + (++$key))); ?></td>
                                <td ><?php echo e(ucfirst($value->title)); ?></td>
                                <td class="text-center">
                                    <?php echo e(\App\Helpers\AppHelper::formatDateForView($value->created_at)); ?>

                                </td>
                                <td class="text-center"><?php echo e(ucfirst($value->createdBy?->name)); ?></td>
                                <td class="text-center"><?php echo e(ucfirst($value->createdBy?->branch?->name)); ?></td>

                                <td class="text-center"><?php echo e(ucfirst($value->departmentQuery?->dept_name)); ?></td>
                                <td class="text-center">
                                    <span class="cursor-default btn btn-xs white btn-<?php echo e($statusColor[$value->status]); ?>">
                                       <?php echo e(removeSpecialChars($value->status)); ?>

                                    </span>
                                </td>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['show_query_detail','delete_query'])): ?>
                                    <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">

                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_query_detail')): ?>
                                            <li class="me-2">
                                                <a href=""
                                                   data-href="<?php echo e(route('admin.supports.changeSeenStatus',$value->id)); ?>"
                                                   id="showDetail"
                                                   data-id="<?php echo e($value->id); ?>"
                                                   data-branch="<?php echo e($value->createdBy?->branch?->name); ?>"
                                                   data-department="<?php echo e($value->createdBy?->department?->dept_name); ?>"
                                                   data-requested="<?php echo e($value->departmentQuery?->dept_name); ?>"
                                                   data-description="<?php echo e($value->description); ?>"
                                                   data-title="<?php echo e($value?->title); ?>"
                                                   data-status="<?php echo e(removeSpecialChars($value?->status)); ?>"
                                                   data-submitted="<?php echo e($value->createdBy?->name); ?>"
                                                   data-action="<?php echo e(route('admin.supports.updateStatus',$value->id)); ?>"
                                                >
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_query')): ?>
                                            <li>
                                                <a class="delete"
                                                   data-title="Query"
                                                   data-href="<?php echo e(route('admin.supports.delete',$value->id)); ?>"
                                                   title="<?php echo e(__('index.delete')); ?>">
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

        <div class="dataTables_paginate mt-3">
            <?php echo e($supportQueries->appends($_GET)->links()); ?>

        </div>
    </section>
    <?php echo $__env->make('admin.support.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php echo $__env->make('admin.support.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/support/index.blade.php ENDPATH**/ ?>