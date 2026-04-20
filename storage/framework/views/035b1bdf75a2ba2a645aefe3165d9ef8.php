<?php $__env->startSection('title',__('index.qr')); ?>
<?php $__env->startSection('styles'); ?>
    <style>
        .qr > svg {
            height: 100px;
            width: 100px;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_qr')): ?>
        <a href="<?php echo e(route('admin.qr.create')); ?>">
            <button class="btn btn-primary add_qr">
                <i class="link-icon" data-feather="plus"></i><?php echo app('translator')->get('index.add_qr'); ?>
            </button>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.qr.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.qr_filter')); ?></h6>
            </div>
            <form class="forms-sample card-body pb-0" action="<?php echo e(route('admin.qr.index')); ?>" method="get">
                <div class="row align-items-center">

                    <?php if(!isset(auth()->user()->branch_id)): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
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


                    <div class="col-lg-4 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option  selected  disabled><?php echo e(__('index.select_department')); ?>

                            </option>
                        </select>
                    </div>


                    <div class="col-lg-2 col-md-6 d-md-flex">
                        <button type="submit" class="btn btn-block btn-success me-md-2 me-0 mb-md-4 mb-2"><?php echo e(__('index.filter')); ?></button>

                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4"
                           href="<?php echo e(route('admin.qr.index')); ?>"><?php echo e(__('index.reset')); ?></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><?php echo e(__('index.qr_lists')); ?></h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo app('translator')->get('index.branch'); ?></th>
                            <th><?php echo app('translator')->get('index.title'); ?></th>
                            <th><?php echo app('translator')->get('index.qr_image'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('index.action'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        <?php $__empty_1 = true; $__currentLoopData = $qrData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($qr?->branch?->name); ?></td>
                                <td><?php echo e($qr->title); ?></td>
                                <td class="qr_code">
                                    <div class="qr"><?php echo $qr->qr_code; ?></div>
                                </td>

                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        <li class="me-2">
                                            <a href="<?php echo e(route('admin.qr.print',$qr->id)); ?>" target="_blank" class="text-success" title="<?php echo app('translator')->get('index.print'); ?>">
                                                <i class="link-icon" data-feather="printer"></i>
                                            </a>
                                        </li>
                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_qr')): ?>
                                            <li class="me-2">
                                                <a href="<?php echo e(route('admin.qr.edit',$qr->id)); ?>" class="text-warning" title="<?php echo app('translator')->get('index.edit'); ?> ">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_qr')): ?>
                                            <li class="me-2">
                                                <a class="deleteQR"
                                                   data-href="<?php echo e(route('admin.qr.destroy',$qr->id)); ?>" title="<?php echo app('translator')->get('index.delete'); ?>">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
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


            $('.deleteQR').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '<?php echo app('translator')->get('index.delete_confirmation'); ?>',
                    showDenyButton: true,
                    confirmButtonText: `<?php echo app('translator')->get('index.yes'); ?>`,
                    denyButtonText: `<?php echo app('translator')->get('index.no'); ?>`,

                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })



            const isAdmin = <?php echo e(auth('admin')->check() ? 'true' : 'false'); ?>;
            const defaultBranchId = <?php echo e(auth()->user()->branch_id ?? 'null'); ?>;
            const branchId = "<?php echo e($filterData['branch_id'] ?? null); ?>";
            const departmentId = "<?php echo e($filterData['department_id'] ?? ''); ?>";


            const loadDepartments = async (selectedBranchId) => {

                if (!selectedBranchId) return;

                try {
                    $('#department_id').empty().append('<option selected disabled><?php echo e(__("index.select_department")); ?></option>');

                    const response = await $.ajax({
                        type: 'GET',
                        url: `<?php echo e(url('admin/departments/get-All-Departments')); ?>/${selectedBranchId}`,
                    });

                    if (!response || !response.data || response.data.length === 0) {
                        $('#department_id').append('<option disabled><?php echo e(__("index.no_departments_found")); ?></option>');
                        return;
                    }


                    response.data.forEach(data => {
                        $('#department_id').append(`<option value="${data.id}" ${data.id == departmentId ? 'selected' : ''}>${data.dept_name}</option>`);
                    });
                } catch (error) {
                    $('#department_id').append('<option disabled><?php echo e(__("index.error_loading_departments")); ?></option>');
                }
            };


            const initializeDropdowns = async () => {
                let selectedBranchId;

                if (isAdmin) {
                    selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;

                    $('#branch_id').on('change', async () => {
                        const newBranchId = $('#branch_id').val();
                        await loadDepartments(newBranchId);
                    });

                    // Trigger initial load if branch is selected
                    if (selectedBranchId) {
                        $('#branch_id').trigger('change');
                    }
                } else {
                    selectedBranchId = defaultBranchId;
                    if (selectedBranchId) {
                        await loadDepartments(selectedBranchId);
                    }
                }

            };

            // Initialize everything
            initializeDropdowns();
        });
    </script>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/qr/index.blade.php ENDPATH**/ ?>