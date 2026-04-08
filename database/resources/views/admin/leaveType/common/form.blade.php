

<div class="row">

    <div class="col-lg col-md mb-4">
        <label for="name" class="form-label">{{ __('index.leave_type_name') }}  <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" value="{{ ( isset($leaveDetail) ? $leaveDetail->name: old('name') )}}" required autocomplete="off" placeholder="{{ __('index.leave_type_placeholder') }}">
    </div>

    <div class="col-lg col-md mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.is_paid_leave') }} <span style="color: red">*</span></label>
        <select class="form-select" id="leave_paid" required name="leave_paid">
            <option value="" {{ isset($leaveDetail) ? '':'selected'}} disabled></option>
            <option value="1" {{ isset($leaveDetail) && $leaveDetail->leave_allocated > 0  ? 'selected':''}} >{{ __('index.yes') }}</option>
            <option value="0"  {{ isset($leaveDetail) && is_null($leaveDetail->leave_allocated)      ? 'selected':'' }}>{{ __('index.no') }}</option>
        </select>
    </div>

    <div class="col-lg col-md mb-4 leaveAllocated " >
        <label for="leave_allocated" class="form-label">{{ __('index.leave_allocated_days') }} <span style="color: red">*</span></label>
        <input type="number" min="1" class="form-control" id="leave_allocated"  name="leave_allocated" value="{{ isset($leaveDetail)? $leaveDetail->leave_allocated: old('leave_allocated') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg col-md mt-md-4 mb-4 text-md-end">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{isset($leaveDetail)?  __('index.update'): __('index.create')}}{{ __('index.leave_type') }} </button>
    </div>
</div>


