<div class="row">

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="office_contribution" class="form-label">{{ __('index.office_contribution') }}</label>
        <input type="number" oninput="validity.valid||(value='');" class="form-control" step="0.1" id="office_contribution" name="office_contribution" value="{{ ( $ssfDetail ? $ssfDetail->office_contribution: old('office_contribution') )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="employee_contribution" class="form-label">{{ __('index.employee_contribution') }}</label>
        <input type="number" oninput="validity.valid||(value='');" class="form-control" id="employee_contribution" name="employee_contribution" value="{{ ($ssfDetail? $ssfDetail->employee_contribution: old('employee_contribution') )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.status') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value="" {{ isset($ssfDetail) ? '' :'selected' }} disabled>{{ __('index.select_status') }}</option>
            <option value="1" @selected( old('is_active',isset($ssfDetail) && $ssfDetail->is_active ) == 1)>{{ __('index.active') }}</option>
            <option value="0" @selected( old('is_active',isset($ssfDetail) && $ssfDetail->is_active ) == 0)>{{ __('index.inactive') }}</option>
        </select>
    </div>



    @can('ssf')
        <div class="col-lg-12 text-start">
            <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ $ssfDetail ? __('index.update') : __('index.save') }}    {{ __('index.ssf') }}</button>
        </div>
    @endcan
</div>
