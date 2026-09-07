<div class="row">
    <div class="col-lg-6 mb-3">
        <label for="name" class="form-label">@lang('index.name') <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name"
               name="name"
               value="{{ ( isset($generalSettingDetail) ? $generalSettingDetail->name: old('name') )}}"
               autocomplete="off"
               placeholder="">
    </div>

    <div class="col-lg-6 mb-3">
        <label for="exampleFormControlSelect1" class="form-label">@lang('index.type')</label>
        <select class="form-select" id="type" required name="type">
            <option value="" {{ isset($generalSettingDetail) ? '':'selected'}} disabled></option>
            @foreach(\App\Enum\GeneralSettingEnum::cases() as $key => $enum)
                <option value="{{$enum->value}}" {{ isset($generalSettingDetail) && $generalSettingDetail->value == $enum->value ? 'selected':''}} > {{ __('index.'.$enum->value) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 mb-3">
        <label for="" class="form-label">@lang('index.key')</label>
        <select class="form-select" id="key" required name="key">
            <option value="" {{ isset($generalSettingDetail) ? '':'selected'}} disabled>@lang('index.select_general_setting_key')</option>
            @foreach(\App\Models\GeneralSetting::GENERAL_SETTING_KEY as $value)
                <option value="{{$value}}" {{ isset($generalSettingDetail) && $generalSettingDetail->key == $value ? 'selected':''}} > {{ __('seeder.'.$value) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 mb-3 " >
        <label for="leave_allocated" class="form-label">@lang('index.value')</label>
        <input type="text" class="form-control"
               id="value"
               name="value"
               value="{{ isset($generalSettingDetail)? $generalSettingDetail->value: old('value') }}"
               autocomplete="off" >
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-primary">
            <i class="link-icon" data-feather="plus"></i>
            {{ isset($generalSettingDetail) ? __('index.update_general_setting') : __('index.create_general_setting') }}
        </button>
    </div>
</div>
