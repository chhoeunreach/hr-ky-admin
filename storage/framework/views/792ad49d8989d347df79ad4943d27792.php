<?php use App\Models\User; ?>
<?php use App\Models\EmployeeAccount; ?>
<div class="mb-2 text-md-start text-center"><small><?php echo __('index.all_fields_required'); ?></small></div>
<style>
    .is-invalid {
        border-color: red !important;
    }

    .is-invalid + .error-message {
        display: block;
        color: red !important;
    }

    .error-message {
        display: none;
        color: red !important;
    }
</style>
<div class="card mb-4">
    <div class="card-body pb-2">
        <div class="profile-detail">
            <h5 class="mb-3 border-bottom pb-3"><?php echo e(__('index.personal_detail')); ?></h5>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="employee_code" class="form-label"><?php echo e(__('index.employee_code')); ?> </label>
                    <input type="text" class="form-control"
                           id="employee_code"
                           name="employee_code"
                           value="<?php echo e(( isset($userDetail) ? $userDetail->employee_code: $employeeCode )); ?>" autocomplete="off"
                           placeholder="<?php echo e(__('index.employee_code')); ?>" required>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="name" class="form-label"> <?php echo e(__('index.name')); ?> <span style="color: red">*</span></label>
                    <input type="text" class="form-control"
                           id="name"
                           name="name"
                           value="<?php echo e(( isset($userDetail) ? $userDetail->name: old('name') )); ?>" autocomplete="off"
                           placeholder="<?php echo e(__('index.enter_name')); ?>" required>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="address" class="form-label"> <?php echo e(__('index.address')); ?></label>
                    <input type="text"
                           class="form-control"
                           id="address"
                           name="address"
                           value="<?php echo e((isset($userDetail) ? ($userDetail->address): old('address'))); ?>"
                           autocomplete="off" placeholder="<?php echo e(__('index.enter_employee_address')); ?>">
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="email" class="form-label"><?php echo e(__('index.email')); ?> <span style="color: red">*</span></label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo e(( isset($userDetail) ? $userDetail->email: old('email') )); ?>" required
                           autocomplete="off" placeholder="<?php echo e(__('index.enter_email')); ?>">
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="number" class="form-label"><?php echo e(__('index.phone_no')); ?></label>
                    <input type="number" class="form-control" id="phone" name="phone"
                           value="<?php echo e(isset($userDetail)? $userDetail->phone: old('phone')); ?>"
                           autocomplete="off" placeholder="<?php echo e(__('index.phone_no')); ?>">
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="dob" class="form-label"> <?php echo e(__('index.dob')); ?> </label>
                    <?php if($bsEnabled): ?>
                        <input type="text" class="form-control birthDate" id="dob" name="dob"
                               value="<?php echo e((isset($userDetail->dob) ? \App\Helpers\AppHelper::taskDate($userDetail->dob): old('dob') )); ?>"
                               autocomplete="off"
                               placeholder="<?php echo e(__('index.dob')); ?>">
                    <?php else: ?>
                        <input type="date" class="form-control" id="dob" name="dob"
                               value="<?php echo e(( isset($userDetail) ? ($userDetail->dob): old('dob') )); ?>"
                               autocomplete="off" placeholder="<?php echo e(__('index.dob')); ?>">
                    <?php endif; ?>

                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="gender" class="form-label"><?php echo e(__('index.gender')); ?></label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="" <?php echo e(isset($userDetail) || old('gender') ? '' : 'selected'); ?>  disabled><?php echo e(__('index.select_gender')); ?>

                        </option>
                        <?php $__currentLoopData = User::GENDER; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option
                                value="<?php echo e($value); ?>" <?php echo e(isset($userDetail) && ($userDetail->gender ) == $value || old('gender') == $value ? 'selected': ''); ?>>
                                <?php echo e(ucfirst($value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="marital_status" class="form-label"><?php echo e(__('index.marital_status')); ?></label>
                    <select class="form-select" id="marital_status" name="marital_status" required>
                        <option value="" <?php echo e(isset($userDetail) || old('marital_status') ? '' : 'selected'); ?>  disabled>
                            <?php echo e(__('index.choose_marital_status')); ?>

                        </option>
                        <?php $__currentLoopData = User::MARITAL_STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>"
                                <?php echo e(isset($userDetail) && ($userDetail->marital_status ) == $value || old('marital_status') == $value ? 'selected': ''); ?>>
                                <?php echo e(ucfirst($value)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>


                <div class="col-lg-4 mb-3">
                    <label for="avatar" class="form-label"><?php echo e(__('index.upload_avatar')); ?> <span style="color: red">*</span> </label>
                    <input class="form-control"
                           type="file"
                           id="avatar"
                           name="avatar"
                           accept="image/*"
                           value="<?php echo e(isset($userDetail) ? $userDetail->avatar: old('avatar')); ?>" <?php echo e(isset($userDetail) ? '': 'required'); ?> >

                    <img class="mt-2 rounded <?php echo e((isset($userDetail) && $userDetail->avatar) ? '': 'd-none'); ?>"
                         id="image-preview"
                         src="<?php echo e((isset($userDetail) && $userDetail->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$userDetail->avatar) : ''); ?>"
                         style="object-fit: contain"
                         width="100"
                         height="100"
                    >
                </div>
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-6 mb-3 empl-desc">
                            <label for="remarks" class="form-label"><?php echo e(__('index.description')); ?></label>
                            <textarea class="form-control" name="remarks" id="tinymceExample"
                                    rows="2"><?php echo e(( isset($userDetail) ? $userDetail->remarks: old('remarks') )); ?></textarea>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-12 col-md-4 mb-3">
                                    <label for="username" class="form-label"><?php echo e(__('index.username')); ?> <span style="color: red">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="<?php echo e(( isset($userDetail) ? $userDetail->username: old('username') )); ?>"
                                        required
                                        autocomplete="off" placeholder="<?php echo e(__('index.enter_username')); ?>">
                                </div>
                                <?php if(!isset($userDetail)): ?>
                                    <div class="col-lg-12 col-md-4 mb-3">
                                        <label for="password" class="form-label"><?php echo e(__('index.password')); ?> <span style="color: red">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            value="<?php echo e(old('password')); ?>" autocomplete="off" placeholder="<?php echo e(__('index.enter_password')); ?>" required>
                                    </div>
                                <?php endif; ?>

                                <div class="col-lg-12 col-md-4 mb-3">
                                    <label for="role" class="form-label"><?php echo e(__('index.role')); ?> <span style="color: red">*</span></label>
                                    <select class="form-select" id="role" name="role_id" required>
                                        <option value="" <?php echo e(isset($userDetail) || old('role_id')  ? '': 'selected'); ?>  disabled><?php echo e(__('index.select_role')); ?>

                                        </option>
                                        <?php if($roles): ?>
                                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>  $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($value->id); ?>"
                                                    <?php echo e(isset($userDetail) && ($userDetail->role_id ) == $value->id  || old('role_id') == $value->id ? 'selected': ''); ?>> <?php echo e(ucfirst($value->name)); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body pb-2">
        <div class="company-detail">
            <h5 class="mb-3 border-bottom pb-3"><?php echo e(__('index.company_detail')); ?></h5>
            <div class="row">
                <?php if(!isset(auth()->user()->branch_id)): ?>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="branch_id" class="form-label"><?php echo e(__('index.branch')); ?></label>
                    <select class="form-select" id="branch" name="branch_id">
                        <option value="" <?php echo e(!isset($userDetail) || old('branch_id') ? 'selected': ''); ?>  disabled><?php echo e(__('index.select_branch')); ?>

                        </option>
                        <?php if(isset($companyDetail)): ?>
                            <?php $__currentLoopData = $companyDetail->branches()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($branch->id); ?>"
                                    <?php echo e((isset($userDetail) && ($userDetail->branch_id ) == $branch->id)  ? 'selected': ''); ?>>
                                    <?php echo e(ucfirst($branch->name)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="department" class="form-label"><?php echo e(__('index.departments')); ?></label>
                    <select class="form-select" id="department" name="department_id">
                        <option selected disabled><?php echo e(__('index.select_department')); ?>

                        </option>
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="post" class="form-label"><?php echo e(__('index.post')); ?></label>
                    <select class="form-select" id="post" name="post_id">
                        <?php if(isset($userDetail)): ?>
                            <?php $__currentLoopData = $filteredPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($post->id); ?>" <?php echo e($post->id ==  $userDetail->post_id ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($post->post_name)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <option selected disabled><?php echo e(__('index.select_post')); ?></option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="supervisor" class="form-label"><?php echo e(__('index.supervisor')); ?></label>
                    <select class="form-select" id="supervisor" name="supervisor_id">
                        <?php if(isset($userDetail)): ?>
                            <?php $__currentLoopData = $filteredSupervisor; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supervisor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if( $supervisor->id !== $userDetail->id): ?>
                                    <option
                                        value="<?php echo e($supervisor->id); ?>" <?php echo e($supervisor->id ==  $userDetail->supervisor_id ? 'selected' : ''); ?>>
                                        <?php echo e(ucfirst($supervisor->name)); ?>

                                    </option>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <option selected disabled><?php echo e(__('index.select_supervisor')); ?></option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="employment_type" class="form-label"><?php echo e(__('index.employment_type')); ?>

                    </label>
                    <select class="form-select" id="employment_type" name="employment_type">
                        <option value="" <?php echo e(isset($userDetail) || old('employment_type') ? '': 'selected'); ?>  disabled>
                            <?php echo e(__('index.select_employment_type')); ?>

                        </option>
                        <?php $__currentLoopData = User::EMPLOYMENT_TYPE; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option
                                value="<?php echo e($value); ?>" <?php echo e(isset($userDetail) && ($userDetail->employment_type ) == $value || old('employment_type') == $value ? 'selected': ''); ?>>
                                <?php echo e(ucfirst($value)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="officeTime" class="form-label"><?php echo e(__('index.office_time')); ?></label>
                    <select class="form-select" id="officeTime" name="office_time_id">

                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="joining_date" class="form-label"><?php echo e(__('index.joining_date')); ?></label>
                    <?php if($bsEnabled): ?>
                        <input type="text" class="form-control joiningDate" id="joining_date" name="joining_date"
                               value="<?php echo e((isset($userDetail->joining_date) ? \App\Helpers\AppHelper::taskDate($userDetail->joining_date): old('joining_date') )); ?>"
                               autocomplete="off"
                               placeholder="<?php echo e(__('index.enter_joining_date')); ?>">
                    <?php else: ?>
                        <input type="date" class="form-control" id="joining_date" name="joining_date"
                               value="<?php echo e((isset($userDetail) ? ($userDetail->joining_date): old('joining_date') )); ?>"
                               autocomplete="off"
                               placeholder="<?php echo e(__('index.enter_joining_date')); ?>">
                    <?php endif; ?>

                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="workspace_type" class="form-label"><?php echo e(__('index.workspace')); ?></label>
                    <select class="form-select" id="workspace_type" name="workspace_type">
                        <option value="" <?php echo e(isset($userDetail) || old('workspace_type') ? '': 'selected'); ?>  disabled>
                            <?php echo e(__('index.select_workspace')); ?>

                        </option>
                        <option value="<?php echo e(User::FIELD); ?>"
                            <?php echo e(isset($userDetail) && ($userDetail->workspace_type ) == User::FIELD || old('workspace_type') == User::FIELD ? 'selected': ''); ?>>
                            <?php echo e(__('index.field')); ?>

                        </option>
                        <option value="<?php echo e(User::OFFICE); ?>"
                            <?php echo e(isset($userDetail) && ($userDetail->workspace_type ) == User::OFFICE || old('workspace_type') == User::OFFICE ? 'selected': ''); ?>>
                            <?php echo e(__('index.office')); ?>

                        </option>

                    </select>
                </div>
                <div class="col-lg-4 col-md-6 mt-5">
                    <input type="checkbox" name="allow_holiday_check_in" id="allow_holiday_check_in" <?php echo e(isset($userDetail) && ($userDetail->allow_holiday_check_in == 1) ? 'checked': ''); ?>>
                    <?php echo e(__('index.allow_holiday_check_in')); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-lg-6 d-flex">
        <div class="card mb-4 w-100">
            <div class="card-body">
                <div class="bank-detail">
                    <h5 class="mb-3 border-bottom pb-3"><?php echo e(__('index.leave_detail')); ?></h5>
                    <label for="leave_allocated" class="form-label"><?php echo e(__('index.leave_allocated')); ?></label>
                    <input type="number" class="form-control mb-2" min="0"
                           id="leave_allocated"
                           name="leave_allocated"
                           oninput="validity.valid||(value='');"
                           value="<?php echo e(isset($userDetail) ? $userDetail->leave_allocated : old('leave_allocated')); ?>"
                           autocomplete="off" placeholder="<?php echo e(__('index.leave_allocated')); ?>">

                    <div id="error-message" style="color: red !important; display: none;"></div>
                    <table class="table table-responsive">
                        <h5 class="my-3"><?php echo e(__('index.assigned_leaves')); ?></h5>
                        <thead>
                        <tr>
                            <th><?php echo e(__('index.leave')); ?></th>
                            <th><?php echo e(__('index.no_of_days')); ?></th>
                            <th><?php echo e(__('index.is_active')); ?></th>
                        </tr>
                        </thead>
                        <tbody id="leave-types-table">
                        <?php if(isset($leaveTypes)): ?>
                            <?php for($k = 0; $k < count($leaveTypes); $k++): ?>
                                <tr>
                                    <td>
                                        <?php echo e($leaveTypes[$k]->name); ?>

                                        <input type="hidden" name="leave_type_id[<?php echo e($k); ?>]" value="<?php echo e($leaveTypes[$k]->id); ?>">
                                    </td>
                                    <?php if(isset($employeeLeaveTypes[$k])): ?>
                                        <?php $leaveType = $employeeLeaveTypes[$k]; ?>
                                    <?php endif; ?>
                                    <td>
                                        <input type="number" min="0" class="form-control leave-days"
                                               value="<?php echo e($leaveType->days ?? ''); ?>"
                                               oninput="validity.valid||(value='');"
                                               placeholder="<?php echo e(__('index.total_leave_days')); ?>"
                                               name="days[<?php echo e($k); ?>]">
                                        <span class="error-message" style="display: none; color: red;"><?php echo e(__('index.required_field')); ?>.</span>
                                    </td>
                                    <td>
                                        <input class="me-1 is-active-checkbox" type="checkbox"
                                               <?php echo e(isset($leaveType->is_active) && $leaveType->is_active == 1 ? 'checked' : ''); ?>

                                               name="is_active[<?php echo e($k); ?>]" value="1"><?php echo e(__('index.is_active')); ?>

                                    </td>
                                </tr>
                            <?php endfor; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 d-flex">
        <div class="card mb-4 w-100">
            <div class="card-body pb-0">
                <div class="bank-detail">
                    <h5 class="mb-3 border-bottom pb-3"><?php echo e(__('index.bank_detail')); ?></h5>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <label for="bank_name" class="form-label"><?php echo e(__('index.bank_name')); ?> <span
                                    style="color: red">*</span></label>
                            <input type="text" class="form-control"
                                   id="bank_name"
                                   name="bank_name"
                                   value="<?php echo e(isset($userDetail?->accountDetail) ? $userDetail?->accountDetail?->bank_name: old('bank_name')); ?>"
                                   autocomplete="off" placeholder="<?php echo e(__('index.bank_name')); ?>" required>
                        </div>

                        <div class="col-lg-6 col-md-6 mb-4">
                            <label for="bank_account_no" class="form-label"><?php echo e(__('index.bank_account_number')); ?> <span
                                    style="color: red">*</span></label>
                            <input type="number"
                                   class="form-control"
                                   id="bank_account_no"
                                   name="bank_account_no"
                                   value="<?php echo e(isset($userDetail?->accountDetail) ? $userDetail?->accountDetail?->bank_account_no: old('bank_account_no')); ?>"
                                   autocomplete="off"
                                   placeholder=" <?php echo e(__('index.bank_account_number')); ?>" required>
                        </div>

                        <div class="col-lg-6 col-md-6 mb-4">
                            <label for="account_holder" class="form-label"><?php echo e(__('index.account_holder_name')); ?> <span style="color: red">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="account_holder"
                                   name="account_holder"
                                   value="<?php echo e(isset($userDetail) ? $userDetail?->accountDetail?->account_holder: old('account_holder')); ?>"
                                   autocomplete="off"
                                   required
                                   placeholder="<?php echo e(__('index.account_holder_name')); ?>">
                        </div>

                        <div class="col-lg-6 col-md-6 mb-4">
                            <label for="bank_account_type" class="form-label"><?php echo e(__('index.bank_account_type')); ?><span style="color: red">*</span></label>
                            <select class="form-select" id="bank_account_type" name="bank_account_type" required>
                                <option value="" <?php echo e(isset($userDetail) || old('bank_account_type') ? '': 'selected'); ?> >
                                    <?php echo e(__('index.select_account_type')); ?>

                                </option>
                                <?php $__currentLoopData = EmployeeAccount::BANK_ACCOUNT_TYPE; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($value); ?>" <?php echo e(isset($userDetail?->accountDetail) && ($userDetail?->accountDetail?->bank_account_type ) == $value || old('bank_account_type') == $value ? 'selected': ''); ?>>
                                        <?php echo e(ucfirst($value)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<button type="submit" class="btn btn-primary">
    <i class="link-icon" data-feather="plus"></i> <?php echo e(isset($userDetail)? __('index.update_user'):__('index.create_user')); ?>

</button>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/employees/common/form.blade.php ENDPATH**/ ?>