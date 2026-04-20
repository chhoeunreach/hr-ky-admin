<?php $__env->startSection('title',__('index.leave_type')); ?>

<?php $__env->startSection('action',__('index.lists')); ?>

<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['leave_type_create','access_admin_leave'])): ?>
        <button class="btn btn-primary create-leaveType mb-3">
            <i class="link-icon" data-feather="plus"></i> <?php echo e(__('index.add_leave_type')); ?>

        </button>

    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/dataTables.dataTables.min.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.leaveType.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo app('translator')->get('index.leave_type_filter'); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.leaves.index')); ?>" method="get">

                <div class="row align-items-center">
                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <select class="form-select" id="branch" name="branch_id">
                                <option
                                    <?php echo e(!isset($filterParameters['branch_id']) || old('branch_id') ? 'selected': ''); ?>  disabled><?php echo e(__('index.select_branch')); ?>

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

                    <div class="col-lg-4 col-md-6 mb-4">
                        <input type="text" class="form-control" name="type" id="title" placeholder="<?php echo e(__('index.leave_type')); ?>"
                               value="<?php echo e($filterParameters['type']); ?>">
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-success me-2"><?php echo app('translator')->get('index.filter'); ?></button>
                            <a class="btn btn-block btn-primary"
                               href="<?php echo e(route('admin.leaves.index')); ?>"><?php echo app('translator')->get('index.reset'); ?></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th><?php echo e(__('index.type')); ?></th>
                            <th class="text-center"><?php echo e(__('index.is_paid')); ?></th>
                            <th class="text-center"><?php echo e(__('index.allocated_days')); ?></th>
                            <th class="text-center"><?php echo e(__('index.status')); ?></th>
                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['leave_type_edit','leave_type_delete','access_admin_leave'])): ?>
                                <th class="text-center"><?php echo e(__('index.action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>


                        <?php $__empty_1 = true; $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e(++$key); ?></td>
                                <td><?php echo e(ucfirst($value->name)); ?></td>
                                <td class="text-center"><?php echo e(($value->leave_allocated) ? __('index.yes'):__('index.no')); ?></td>
                                <td class="text-center"><?php echo e(($value->leave_allocated) ?? '-'); ?></td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="<?php echo e(route('admin.leaves.toggle-status',$value->id)); ?>"
                                               type="checkbox" <?php echo e(($value->is_active) == 1 ?'checked':''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['leave_type_edit','leave_type_delete','access_admin_leave'])): ?>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['leave_type_edit','access_admin_leave'])): ?>
                                                <li class="me-2">
                                                    <a class="edit-leaveType"  data-id="<?php echo e($value->id); ?>" data-href="<?php echo e(route('admin.leaves.edit', $value->id)); ?>">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>

                                                </li>
                                            <?php endif; ?>

                                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['leave_type_delete','access_admin_leave'])): ?>
                                                <li>
                                                    <a class="deleteLeaveType"
                                                       data-href="<?php echo e(route('admin.leaves.delete',$value->id)); ?>"
                                                       title="<?php echo e(__('index.delete_leave_type')); ?>">
                                                        <i class="link-icon" data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                            <?php endif; ?>

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

    <div class="modal fade" id="leaveTypeModal" tabindex="-1" aria-labelledby="leaveTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title" id="leaveTypeModalLabel"><?php echo e(__('index.add_leave_type')); ?></h5>
                </div>
                <div class="modal-body">
                    <form id="leaveTypeForm" class="forms-sample" enctype="multipart/form-data" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="_method" id="formMethod" value="POST">


                        <div class="row">
                            <?php if(!isset(auth()->user()->branch_id)): ?>
                                <div class="col-lg-6 mb-4">
                                    <label for="branch_id" class="form-label"><?php echo e(__('index.branch')); ?> <span style="color: red">*</span></label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option selected disabled><?php echo e(__('index.select_branch')); ?></option>
                                        <?php if(isset($companyDetail)): ?>
                                            <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($branch->id); ?>"><?php echo e(ucfirst($branch->name)); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="col-lg-6 mb-4">
                                <label for="name" class="form-label"><?php echo e(__('index.leave_type_name')); ?>  <span style="color: red">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo e(old('name')); ?>" required autocomplete="off" placeholder="<?php echo e(__('index.leave_type_placeholder')); ?>">
                            </div>
                            <div class="col-lg-6 mb-4">
                                <label for="gender" class="form-label"><?php echo e(__('index.applies_to_gender')); ?></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="" <?php echo e(isset($leaveDetail) ? '': 'selected'); ?> disabled><?php echo e(__('index.select_gender')); ?></option>
                                    <?php $__currentLoopData = $genders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gender): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($gender->value); ?>" <?php echo e((isset($leaveDetail) && ($leaveDetail->gender ) == $gender->value) ? 'selected':old('gender')); ?> >
                                            <?php echo e(ucfirst($gender->name)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label for="leave_paid" class="form-label"><?php echo e(__('index.is_paid_leave')); ?> <span style="color: red">*</span></label>
                                <select class="form-select" id="leave_paid" required name="leave_paid">
                                    <option selected disabled></option>
                                    <option value="1"><?php echo e(__('index.yes')); ?></option>
                                    <option value="0"><?php echo e(__('index.no')); ?></option>
                                </select>
                            </div>

                            <div class="col-lg-6 mb-4 leaveAllocated " >
                                <label for="leave_allocated" class="form-label"><?php echo e(__('index.leave_allocated_days')); ?> <span style="color: red">*</span></label>
                                <input type="number" min="1" class="form-control" id="leave_allocated"  name="leave_allocated" value="<?php echo e(old('leave_allocated')); ?>" autocomplete="off" placeholder="">
                            </div>

                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="link-icon" data-feather="plus"></i> <span id="submitButtonText"><?php echo e(__('index.save')); ?></span>
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('index.cancel')); ?></button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>

    <script src="<?php echo e(asset('assets/js/dataTables.min.js')); ?>"></script>
    <script>
        <?php if($leaveTypes->isNotEmpty()): ?>
        let table = new DataTable('#dataTableExample', {
            pageLength: <?php echo json_encode(getRecordPerPage(), 15, 512) ?>,
            searching: false,
            paging: true,
        });
        <?php endif; ?>

    </script>
  <?php echo $__env->make('admin.leaveType.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/leaveType/index.blade.php ENDPATH**/ ?>