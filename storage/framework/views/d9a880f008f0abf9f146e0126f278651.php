


<ul class="nav nav-tabs" id="myTab" role="tablist">
    <?php $__currentLoopData = $permissionGroupTypeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $permissionGroupType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e($key == 0 ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#tab-<?php echo e($permissionGroupType->slug); ?>" type="button" role="tab" aria-controls="tab-<?php echo e($permissionGroupType->slug); ?>" aria-selected="<?php echo e($key == 0 ? 'true' : 'false'); ?>" id="<?php echo e($permissionGroupType->slug); ?>">
                <?php echo e($permissionGroupType->name); ?> <?php echo app('translator')->get('index.permissions'); ?>
            </button>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<div class="tab-content mt-4 px-4" id="myTabContent">
    <?php $__currentLoopData = $permissionGroupTypeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $permissionGroupType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $permissionModules = $permissionGroupTypeList->where('slug', $permissionGroupType->slug)->first();
        ?>

        <div class="tab-pane fade <?php echo e($key == 0 ? 'show active' : ''); ?>" id="tab-<?php echo e($permissionGroupType->slug); ?>" role="tabpanel" aria-labelledby="<?php echo e($permissionGroupType->slug); ?>">
            <div class="row mb-2 <?php echo e($permissionModules->slug); ?>">
                <?php $__currentLoopData = $permissionModules->permissionGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <?php
                    $collectionArray = $value->getPermission->pluck('id')->toArray();

                    $checkAll = '';

                    if(count($role_permission) > 0){
                        $diff = array_diff($collectionArray, $role_permission);

                         if (empty($diff)) {
                           $checkAll = 'checked';
                         }
                    }

                ?>
                <div class="col-lg-12">
                    <div class="group-checkbox border-bottom pb-3 mb-4 w-100">
                        <div class="title-ch mb-2">
                            <h5 style="color:#e82e5f;"><?php echo e($value->name); ?> <?php echo app('translator')->get('index.module'); ?>:</h5>
                        </div>
                        <div class="head-checkbox d-flex align-items-center gap-3 flex-wrap">

                            <div class="checkAll">
                                <label class="label-ch lh-1">
                                    <input class="js-check-all" type="checkbox" name=""
                                           data-check-all="website" <?php echo e($checkAll); ?>>
                                    <span class="text fw-bold"><?php echo app('translator')->get('index.check_all'); ?></span>
                                </label>
                            </div>
                            <ul class="js-check-all-target list-ch d-flex align-items-center justify-content-start gap-3 p-0 flex-wrap" data-check-all="website">
                                <?php $__currentLoopData = $value->getPermission; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keys => $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $checked='';
                                        if(count($role_permission) > 0){
                                            if(in_array($permission->id,$role_permission)){
                                                $checked = "checked = 'checked'";
                                            }
                                        }
                                    ?>
                                    <li class="item">
                                        <label class="label lh-1">
                                            <input class="module_checkbox"
                                                   type="checkbox"
                                                   id="<?php echo e($permission->permission_key); ?>"
                                                   name="permission_value[]"
                                                   value="<?php echo e($permission->id); ?>"
                                                <?php echo e($checked); ?>>
                                            <span class="text"><?php echo e($permission->name); ?>

                                    </span>
                                            <?php if($permission->permission_key == 'access_admin_leave'): ?>
                                                <p class="grant_leave">
                                                    <?php echo e(__('index.admin_permission_msg')); ?>


                                                </p>
                                            <?php endif; ?>
                                        </label>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="text-start">
    <button type="submit" class="btn btn-success btn-md">
        <?php echo e($isEdit ? __('index.update'): __('index.save')); ?>

    </button>
</div>

<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/role/common/permission.blade.php ENDPATH**/ ?>