<?php $__env->startSection('title',__('index.permission_setting')); ?>

<?php $__env->startSection('action',__('index.assign')); ?>

<?php $__env->startSection('button'); ?>
    <a href="<?php echo e(route('admin.roles.index')); ?>" class="btn btn-primary btn-sm"> <i class="link-icon" data-feather="arrow-left"></i> <?php echo app('translator')->get('index.back'); ?> </a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('styles'); ?>

    <style>
        li.item{position: relative;}
        li.item label p.grant_leave {
            visibility: hidden;
            transition: all ease-in-out 0.3s;
            width:550px;
            background-color: #fcfcfc;
            border:1px solid #f23e6d;
            padding: 10px;
            position: absolute;
            top: -20px;
            z-index: 1;
            left: 100%;
            border-radius: 10px;
            line-height: 1.5;
        }

        li.item label:hover p.grant_leave {
            visibility: visible;
            transition: all ease-in-out 0.3s;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('main-content'); ?>
    <section class="content">
        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.role.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card">
            <div class="card-header card-nav">
                <ul class="nav nav-tabs d-md-flex d-block text-center">
                    <?php $__currentLoopData = $allRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="nav-link my-md-0 my-1 d-inline-block <?php echo e($value->id == $role->id ? 'active': ''); ?>" href="<?php echo e(route('admin.roles.permission',$value->id)); ?>">
                            <button class="btn btn-md btn-<?php echo e($value->id == $role->id ? 'primary':'secondary'); ?>"><?php echo e(ucfirst($value->name)); ?> </button>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <div class="card-body card-nav-content">
                <form class="forms-sample" action="<?php echo e(route('admin.role.assign-permissions',$role->id)); ?>" method="post">
                    <?php echo method_field('PUT'); ?>
                    <?php echo csrf_field(); ?>
                    <?php echo $__env->make('admin.role.common.permission', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </form>
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

            $(function() {
                $('.js-check-all').on('click', function() {
                    // Get the checked state of the "check all" checkbox itself
                    let isChecked = $(this).prop('checked');

                    // Apply this state to all child checkboxes
                    $(this).parent().parent().siblings().children('.item').children()
                        .find('.module_checkbox').prop('checked', isChecked);
                });
            });

        });

    </script>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/role/permission.blade.php ENDPATH**/ ?>