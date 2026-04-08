<div class="row">
    <!-- Subject Field -->
    <div class="col-md-6 mb-4">
        <label for="subject" class="form-label">{{ __('index.subject') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="subject" name="subject" value="{{ ( isset( $leaveApprovalDetail) ?  $leaveApprovalDetail->subject: old('subject') )}}" required>
    </div>

    <!-- Related Field -->
    <div class="col-md-6 mb-4">
        <label for="related" class="form-label">{{ __('index.leave_types') }} <span style="color: red">*</span></label>
        <select class="form-select" id="related" name="leave_type_id" required>
            <option value="" disabled selected>{{ __('index.select_leave_type') }}</option>
            @foreach($leaveTypes as $key=>$leave)
                <option value="{{ $key }}" {{ (isset($leaveApprovalDetail) && ($leaveApprovalDetail->leave_type_id) == $key) || (old('leave_type_id') == $key) ? 'selected': '' }}>{{ $leave }}</option>
            @endforeach
        </select>
    </div>

    <!-- Departments Field -->
    <div class="col-md-6 mb-4">
        <label for="departments" class="form-label">{{ __('index.department') }}</label>
        <select class="form-select" id="departments" multiple name="department_id[]">
            <option disabled>{{ __('index.select_department') }}</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" {{ isset($leaveApprovalDetail) && in_array($department->id,$departmentId)  ? 'selected' : '' }}>{{ $department->dept_name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Role Field -->
{{--    <div class="col-md-6 mb-4">--}}
{{--        <label for="role" class="form-label">{{ __('index.role') }}</label>--}}
{{--        <select class="form-select" id="role" multiple name="role_id[]">--}}
{{--            <option disabled>{{ __('index.select_role') }}</option>--}}
{{--            @foreach($roles as $role)--}}
{{--                <option value="{{ $role->id }}" {{ isset($leaveApprovalDetail) && in_array($role->id,$roleId)  ? 'selected' : '' }}> {{ $role->name }}</option>--}}
{{--            @endforeach--}}
{{--        </select>--}}
{{--    </div>--}}

    <!-- Notification Recipient Field -->
{{--    <div class="col-md-6 mb-4">--}}
{{--        <label for="notification_recipient" class="form-label">{{ __('index.notification_recipient') }}</label>--}}
{{--        <select class="form-select" id="notification_recipient" multiple name="notification_recipient[]">--}}
{{--            <option disabled>{{ __('index.select_notification_recipient') }}</option>--}}
{{--            @foreach($recipients as $recipient)--}}
{{--                <option value="{{ $recipient->id }}" {{ isset($leaveApprovalDetail) && in_array($recipient->id,$receiverId)  ? 'selected' : '' }}>{{ $recipient->name }}</option>--}}
{{--            @endforeach--}}
{{--        </select>--}}
{{--    </div>--}}

    <!-- Maximum Days to Sign Field -->
{{--    <div class="col-md-6 mb-4">--}}
{{--        <label for="days_to_sign" class="form-label">{{ __('index.max_days_to_sign') }}</label>--}}
{{--        <input type="number" class="form-control" id="days_to_sign" oninput="validity.valid||(value='');" name="max_days_limit" value="{{ ( isset( $leaveApprovalDetail) ?  $leaveApprovalDetail->max_days_limit: old('max_days_limit') )}}" min="0">--}}
{{--    </div>--}}


    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5>{{ __('index.approval_process') }}</h5>
            <button type="button" class="btn btn-success btn-sm" id="add-approver">+</button>
        </div>
        <div class="approved-list mt-3">
            <ul id="sortable">
                @if(isset($leaveApprovalDetail))
                    @foreach($leaveApprovalDetail->approvalProcess as $index => $process)
                        <li class="ui-state-default approver-row" data-id="{{ $process->id }}">
                            <div class="row">
                                <div class="col-md-3 mb-4">
                                    <label for="approver" class="form-label">{{ __('index.approver') }}</label>
                                    <select class="form-select approver-select" name="approver[]">
                                        <option disabled>{{ __('index.select_approver') }}</option>
                                        @foreach(\App\Enum\LeaveApproverEnum::cases() as $approver)
                                            <option value="{{ $approver->value }}" {{ $approver->value == $process->approver ? 'selected' : '' }}>
                                                {{ __('index.' . $approver->value) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-4 employee-wrapper" style="{{ $process->approver === 'specific_personnel' ? '' : 'display:none;' }}">
                                    <label for="staff" class="form-label">{{ __('index.role') }}</label>
                                    <select class="form-select staff-select" name="role_id[]">
                                        <option selected disabled>{{ __('index.select_role') }}</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ $role->id == $process->role_id ? 'selected' : '' }}>
                                                {{ ucfirst($role->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-4 employee-wrapper" style="{{ $process->approver === 'specific_personnel' ? '' : 'display:none;' }}">
                                    <label for="staff" class="form-label">{{ __('index.employee') }}</label>
                                    <select class="form-select user-dropdown" name="user_id[]">
                                        <option selected disabled>{{ __('index.select_employee') }}</option>
                                        @foreach($process->users as $user)
                                        <option value="{{ $user->id }}" {{ $user->id == $process->user_id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 mb-4 d-flex align-items-center mt-sm-4">
                                    <button type="button" class="btn btn-danger btn-sm remove-approver">x</button>
                                </div>
                                <div class="col-md-1 mb-4 d-flex align-items-center justify-content-md-end mt-sm-4">
                                    <i class="link-icon" data-feather="move"></i>
                                </div>

                            </div>
                        </li>
                    @endforeach
                @else
                    <li class="ui-state-default approver-row">
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <label for="approver" class="form-label">{{ __('index.approver') }}</label>
                                <select class="form-select approver-select" name="approver[]">
                                    <option disabled>{{ __('index.select_approver') }}</option>
                                    @foreach(\App\Enum\LeaveApproverEnum::cases() as $approver)
                                        <option value="{{ $approver->value }}">{{ __('index.' . $approver->value) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-4 employee-wrapper" style="display:none;">
                                <label for="role_id" class="form-label">{{ __('index.role') }}</label>
                                <select class="form-select staff-select" name="role_id[]">
                                    <option selected disabled>{{ __('index.select_role') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-4 employee-wrapper" style="display:none;">
                                <label for="user_id" class="form-label">{{ __('index.employee') }}</label>
                                <select class="form-select user-dropdown" name="user_id[]">
                                    <option selected disabled>{{ __('index.select_employee') }}</option>

                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-center">

                            </div>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Save Button -->
    <div class="col-12">
        <button type="submit" class="btn btn-primary">{{ __('index.save') }}</button>
    </div>
</div>
