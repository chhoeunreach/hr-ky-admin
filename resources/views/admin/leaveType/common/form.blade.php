

<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option value="{{ $value->id }}" {{ ((isset($leaveDetail) && $leaveDetail->branch_id == $value->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $value->id)) ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="col-lg col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.leave_type_name') }}  <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" value="{{ ( isset($leaveDetail) ? $leaveDetail->name: old('name') )}}" required autocomplete="off" placeholder="{{ __('index.leave_type_placeholder') }}">
    </div>

    <div class="col-lg col-md-6 mb-4">
        <label for="gender" class="form-label">{{ __('index.applies_to_gender') }}</label>
        <select class="form-select" id="gender" name="gender" required>
            <option value="" {{isset($leaveDetail) ? '': 'selected'}} disabled>{{ __('index.select_gender') }}</option>
            @foreach($genders as $gender)
                <option value="{{ $gender->value }}" {{ (isset($leaveDetail) && ($leaveDetail->gender ) == $gender->value) ? 'selected':old('gender') }} >
                    {{ ucfirst($gender->name) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.is_paid_leave') }} <span style="color: red">*</span></label>
        <select class="form-select" id="leave_paid" required name="leave_paid">
            <option value="" {{ isset($leaveDetail) ? '':'selected'}} disabled></option>
            <option value="1" {{ isset($leaveDetail) && $leaveDetail->leave_allocated > 0  ? 'selected':''}} >{{ __('index.yes') }}</option>
            <option value="0"  {{ isset($leaveDetail) && is_null($leaveDetail->leave_allocated)      ? 'selected':'' }}>{{ __('index.no') }}</option>
        </select>
    </div>

    <div class="col-lg col-md-6 mb-4 leaveAllocated " >
        <label for="leave_allocated" class="form-label">{{ __('index.leave_allocated_days') }} <span style="color: red">*</span></label>
        <input type="number" min="1" class="form-control" id="leave_allocated"  name="leave_allocated" value="{{ isset($leaveDetail)? $leaveDetail->leave_allocated: old('leave_allocated') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-2 mt-lg-4 mb-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{isset($leaveDetail)?  __('index.update'): __('index.create')}} {{ __('index.leave_type') }} </button>
    </div>
</div>


