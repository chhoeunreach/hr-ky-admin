
<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any(['training_type_list'])): ?>
    <li class="nav-item  <?php echo e(request()->routeIs('admin.training-types.*') || request()->routeIs('admin.trainers.*') || request()->routeIs('admin.training.*')  ? 'active' : ''); ?>    ">
        <a class="nav-link"   data-href="#" data-bs-toggle="collapse" href="#trainingManagement" role="button" aria-expanded="false" aria-controls="shiftManagment">
            <i class="link-icon" data-feather="book"></i>
            <span class="link-title"> <?php echo e(__('index.training_management')); ?> </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="<?php echo e(request()->routeIs('admin.training-types.*') || request()->routeIs('admin.trainers.*') || request()->routeIs('admin.training.*') ?'' : 'collapse'); ?> " id="trainingManagement">
             <ul class="nav sub-menu">

                 <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('training_type_list')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.training-types.index')); ?>"
                           data-href="<?php echo e(route('admin.training-types.index')); ?>"
                           class="nav-link <?php echo e(request()->routeIs('admin.training-types.*') ? 'active' : ''); ?>"><?php echo e(__('index.training_type')); ?> </a>
                    </li>
                 <?php endif; ?>
                 <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_trainer')): ?>
                     <li class="nav-item">
                         <a href="<?php echo e(route('admin.trainers.index')); ?>"
                            data-href="<?php echo e(route('admin.trainers.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.trainers.*') ? 'active' : ''); ?>"><?php echo e(__('index.trainer')); ?> </a>
                     </li>
                 <?php endif; ?>

                 <?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check('list_training')): ?>
                     <li class="nav-item">
                         <a href="<?php echo e(route('admin.training.index')); ?>"
                            data-href="<?php echo e(route('admin.training.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.training.*') ? 'active' : ''); ?>"><?php echo e(__('index.training')); ?> </a>
                     </li>
                 <?php endif; ?>

             </ul>
        </div>
    </li>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/hr-ky-admin1/resources/views/admin/section/partial/trainingManagement.blade.php ENDPATH**/ ?>