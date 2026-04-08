<div class="row align-items-center">
    <div class="col-lg-3 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.department_label') }} <span style="color: red">*</span></label>
        <select class="form-select" id="exampleFormControlSelect1" name="dept_id" required>
            <option value="" disabled>{{ __('index.select_department') }}</option>
            @foreach($departmentDetail as $key => $department)
                <option value="{{ $department->id }}" {{ (isset($postDetail) && $department->id === $postDetail->dept_id) ? 'selected' : '' }}>
                    {{ ucfirst($department->dept_name) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.post_name_label') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="post_name" required name="post_name" value="{{ isset($postDetail) ? $postDetail->post_name : '' }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-3 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.status_label') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value="" disabled>{{ __('index.select_status') }}</option>
            <option value="1" {{ isset($postDetail) && $postDetail->is_active == 1 ? 'selected' : old('is_active') }}>{{ __('index.active_option') }}</option>
            <option value="0" {{ isset($postDetail) && $postDetail->is_active == 0 ? 'selected' : old('is_active') }}>{{ __('index.inactive_option') }}</option>
        </select>
    </div>

    <div class="col-lg-3 mb-4 mt-lg-4">
        <button type="submit" class="btn btn-primary">
            <i class="link-icon" data-feather="plus"></i>
            {{ isset($postDetail) ? __('index.update_post_button') : __('index.create_post_title') }}
        </button>
    </div>
</div>
