<?php $__env->startSection('title',__('index.company_static_content')); ?>

<?php $__env->startSection('action',__('index.lists')); ?>

<?php $__env->startSection('button'); ?>
    <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('create_content')): ?>
        <a href="<?php echo e(route('admin.static-page-contents.create')); ?>">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i><?php echo e(__('index.add_content')); ?>

            </button>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main-content'); ?>

    <section class="content">

        <?php echo $__env->make('admin.section.flash_message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->make('admin.contentManagement.common.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo e(__('index.title')); ?></th>
                            <th><?php echo e(__('index.type')); ?></th>

                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_content')): ?>
                                <th class="text-center"><?php echo e(__('index.content')); ?></th>
                            <?php endif; ?>

                            <th class="text-center"><?php echo e(__('index.status')); ?></th>

                            <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_content','delete_content'])): ?>
                                <th class="text-center"><?php echo e(__('index.action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        <?php $__empty_1 = true; $__currentLoopData = $staticPageContents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(++$key); ?></td>
                                <td><?php echo e(removeSpecialChars($value->title)); ?></td>
                                <td><?php echo e(removeSpecialChars($value->content_type)); ?></td>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('show_content')): ?>
                                    <td class="text-center">
                                        <a href=""
                                           id="showStaticPageDescription"
                                           data-href="<?php echo e(route('admin.static-page-contents.show',$value->id)); ?>"
                                           data-id="<?php echo e($value->id); ?>" title="<?php echo e(__('index.show_detail')); ?>">
                                            <i class="link-icon" data-feather="eye"></i>

                                        </a>
                                    </td>
                                <?php endif; ?>

                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="<?php echo e(route('admin.static-page-contents.toggle-status',$value->id)); ?>"
                                               type="checkbox" <?php echo e(($value->is_active) == 1 ?'checked':''); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['edit_content','delete_content'])): ?>
                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('edit_content')): ?>
                                            <li class="me-2">
                                                <a href="<?php echo e(route('admin.static-page-contents.edit',$value->id)); ?>" title="<?php echo e(__('index.edit')); ?>">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('delete_content')): ?>
                                            <li>
                                                <a class="deleteStaticPageContent"
                                                   data-href="<?php echo e(route('admin.static-page-contents.delete',$value->id)); ?>" title="<?php echo e(__('index.delete')); ?>">
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
    </section>
    <?php echo $__env->make('admin.contentManagement.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>

   <?php echo $__env->make('admin.contentManagement.common.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/contentManagement/index.blade.php ENDPATH**/ ?>