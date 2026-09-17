
<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        @php
            $selectedEmployeeIds = collect(old('employee_id', $employeeIds ?? []))->map(fn ($id) => (string) $id)->all();
            $selectedDepartmentIds = collect(old('department_id', $departmentIds ?? []))->filter()->unique()->values();
        @endphp
        <select class="form-select" id="employee_id" name="employee_id[]" multiple>
            @foreach($employees ?? [] as $employee)
                <option value="{{ $employee->id }}"
                        data-branch-id="{{ $employee->branch_id }}"
                        data-department-id="{{ $employee->department_id }}"
                    {{ in_array((string) $employee->id, $selectedEmployeeIds, true) ? 'selected' : '' }}>
                    {{ ucfirst($employee->name) }}{{ $employee->username ? ' (' . $employee->username . ')' : '' }}
                </option>
            @endforeach
        </select>
        <input type="hidden" id="warning_branch_id" name="branch_id" value="{{ old('branch_id', $warningDetail->branch_id ?? auth()->user()->branch_id ?? '') }}">
        <div id="warning_department_inputs">
            @foreach($selectedDepartmentIds as $departmentId)
                <input type="hidden" name="department_id[]" value="{{ $departmentId }}">
            @endforeach
        </div>
    </div>
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-4 d-md-flex d-lg-block d-block justify-content-between gap-4">
                <div class="subject-field mb-4 w-100">
                    <label for="subject" class="form-label"> {{ __('index.subject') }} <span style="color: red">*</span></label>
                    <input type="text" class="form-control" id="subject" name="subject" value="{{ ( isset( $warningDetail) ?  $warningDetail->subject: old('subject') )}}"
                     autocomplete="off" placeholder="{{ __('index.subject') }}">
                </div>
                <div class="warning-date mb-4 w-100">
                    <label for="warning_date" class="form-label">@lang('index.warning_date') <span style="color: red">*</span> </label>
                    @if($isBsEnabled)
                        <input type="text" class="form-control nepali_date" id="warning_date" name="warning_date" required value="{{ ( isset( $warningDetail) ?  \App\Helpers\AppHelper::taskDate($warningDetail->warning_date): old('warning_date') )}}"
                            autocomplete="off" >
                    @else
                        <input type="date" class="form-control" name="warning_date" required value="{{ ( isset( $warningDetail) ?  $warningDetail->warning_date: old('warning_date') )}}"
                            autocomplete="off" >
                    @endif
                </div>
            </div>
            <div class="col-lg-8 mb-4">
                <label for="tinymceExample" class="form-label">{{ __('index.message') }}</label>
                <textarea class="form-control" name="message" id="tinymceExample" rows="1">{{ ( isset($warningDetail) ? $warningDetail->message: old('message') )}}</textarea>
            </div>
        </div>
    </div>


    <input type="hidden" readonly id="notification" name="notification" value="0">

@canany(['edit_warning','create_warning'])
        <div class="text-center text-md-start border-top pt-4">
            <button type="submit" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($warningDetail)?  __('index.update'): __('index.create')}}
            </button>

            <button type="submit" id="withNotification" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($warningDetail)?  __('index.update_send'): __('index.create_send')}}
            </button>
        </div>
    @endcanany
</div>


