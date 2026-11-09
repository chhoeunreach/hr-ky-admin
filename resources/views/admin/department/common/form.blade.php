<div class="row align-items-center">
    @php
        $selectedBranchIds = collect(old('branch_id', $selectedBranchIds ?? (isset($departmentsDetail) ? [$departmentsDetail->branch_id] : [])))
            ->map(fn ($id) => (string) $id)
            ->all();
        if (!auth('admin')->check() && isset(auth()->user()->branch_id)) {
            $selectedBranchIds = [(string) auth()->user()->branch_id];
        }
        $selectedDepartmentHeads = old('dept_head_ids', $departmentHeadsByBranch ?? []);
    @endphp
    @if(!isset(auth()->user()->branch_id))
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" name="branch_id[]" id="branch_id" multiple required>
            <option value="" disabled>{{ __('index.select_branch') }}</option>

            @foreach($branches as $key => $branch)
                <option value="{{ $branch->id }}" {{ in_array((string) $branch->id, $selectedBranchIds, true) ? 'selected' : '' }}>{{ ucfirst($branch->name) }}</option>
            @endforeach

        </select>
    </div>
    @endif
    @if(!auth('admin')->check() && isset(auth()->user()->branch_id))
        <input type="hidden" readonly id="branch_id" name="branch_id[]" value="{{ auth()->user()->branch_id }}">
    @endif
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.department_name') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="dept_name" required name="dept_name" value="{{ old('dept_name', $departmentsDetail->dept_name ?? '') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-12 mb-4">
        <div class="row" id="departmentHeadsByBranch">
            @foreach($branches as $headBranch)
                @php
                    $branchSelected = in_array((string) $headBranch->id, $selectedBranchIds, true);
                    $selectedHeadId = $selectedDepartmentHeads[$headBranch->id] ?? '';
                    $headEmployees = $headEmployeesByBranch->get($headBranch->id, collect());
                @endphp
                <div class="col-xxl-4 col-xl-4 col-md-6 mb-3 department-head-row"
                     data-branch-id="{{ $headBranch->id }}" @if(!$branchSelected) style="display: none" @endif>
                    <label for="dept_head_{{ $headBranch->id }}" class="form-label">
                        {{ __('index.department_head') }} — {{ $headBranch->name }}
                    </label>
                    <select class="form-select department-head-select"
                            id="dept_head_{{ $headBranch->id }}"
                            name="dept_head_ids[{{ $headBranch->id }}]"
                            data-placeholder="{{ __('index.select_department_head') }}"
                            @if(!$branchSelected) disabled @endif>
                        <option value="">{{ __('index.select_department_head') }}</option>
                        @foreach($headEmployees as $user)
                            <option value="{{ $user->id }}" {{ (string) $selectedHeadId === (string) $user->id ? 'selected' : '' }}>{{ ucfirst($user->name) }}</option>
                        @endforeach
                    </select>
                    @if($headEmployees->isEmpty())
                        <small class="text-muted">{{ __('index.employee_not_found') }}</small>
                    @endif
                    @error('dept_head_ids.'.$headBranch->id)
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endforeach
        </div>
        <small class="text-muted" id="departmentHeadBranchHint" @if(count($selectedBranchIds)) style="display: none" @endif>{{ __('index.select_branch') }}</small>
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="address" class="form-label">{{ __('index.address') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="address" required name="address" value="{{ old('address', $departmentsDetail->address ?? '') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="number" class="form-label">{{ __('index.phone_number') }} <span style="color: red">*</span></label>
        <input type="number" class="form-control" id="phone" required name="phone" value="{{ old('phone', $departmentsDetail->phone ?? '') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="status" class="form-label">{{ __('index.status') }}</label>
        <select class="form-select" id="status" name="is_active">
            <option value="" {{ !isset($departmentsDetail) ? 'selected' : '' }} disabled>{{ __('index.select_status') }}</option>
            <option value="1" {{ isset($departmentsDetail) && $departmentsDetail->is_active == 1 ? 'selected' : old('is_active') }}>{{ __('index.active') }}</option>
            <option value="0" {{ isset($departmentsDetail) && $departmentsDetail->is_active == 0 ? 'selected' : old('is_active') }}>{{ __('index.inactive') }}</option>
        </select>
    </div>

    <div class="col-lg-12 mb-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ isset($departmentsDetail) ? __('index.update_department') : __('index.create_department') }}</button>
    </div>
</div>
