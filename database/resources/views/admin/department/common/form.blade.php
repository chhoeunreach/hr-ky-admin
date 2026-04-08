<div class="row align-items-center">

    <div class="col-xxl-3 col-xl-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="exampleFormControlSelect1" name="branch_id" required>
            <option value="" {{ !isset($departmentsDetail) ? 'selected' : '' }} disabled>{{ __('index.select_branch') }}</option>

            @foreach($branches as $key => $branch)
                <option value="{{ $branch->id }}" {{(( isset($departmentsDetail) && $departmentsDetail->branch_id  ==
                    $branch->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $branch->id)) ? 'selected' : ''}}>{{ ucfirst($branch->name) }}</option>
            @endforeach

        </select>
    </div>

    <div class="col-xxl-3 col-xl-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.department_name') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="dept_name" required name="dept_name" value="{{ isset($departmentsDetail) ? $departmentsDetail->dept_name : '' }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-xxl-3 col-xl-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.department_head') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="dept_head_id">
            <option value="" {{ !isset($departmentsDetail) ? 'selected' : '' }} disabled>{{ __('index.select_department_head') }}</option>
            @foreach($users as $key => $user)
                <option value="{{ $user->id }}" @if(isset($departmentsDetail) && $departmentsDetail->dept_head_id ==$user->id) selected @endif >{{ ucfirst($user->name) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="address" class="form-label">{{ __('index.address') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="address" required name="address" value="{{ isset($departmentsDetail) ? $departmentsDetail->address : old('address') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="number" class="form-label">{{ __('index.phone_number') }} <span style="color: red">*</span></label>
        <input type="number" class="form-control" id="phone" required name="phone" value="{{ isset($departmentsDetail) ? $departmentsDetail->phone : old('phone') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.status') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value="" {{ !isset($departmentsDetail) ? 'selected' : '' }} disabled>{{ __('index.select_status') }}</option>
            <option value="1" {{ isset($departmentsDetail) && $departmentsDetail->is_active == 1 ? 'selected' : old('is_active') }}>{{ __('index.active') }}</option>
            <option value="0" {{ isset($departmentsDetail) && $departmentsDetail->is_active == 0 ? 'selected' : old('is_active') }}>{{ __('index.inactive') }}</option>
        </select>
    </div>

    <div class="col-lg-12 mb-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ isset($departmentsDetail) ? __('index.update_department') : __('index.create_department') }}</button>
    </div>
</div>
