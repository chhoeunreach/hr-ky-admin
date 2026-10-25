<div class="col-lg-4 col-md-6 mb-4">
    <label for="icloud" class="form-label">{{ __('index.asset_icloud') }}</label>
    <input type="text" id="icloud" name="icloud" class="form-control" maxlength="255" autocomplete="off"
           value="{{ old('icloud', $assetDetail->icloud ?? '') }}">
    @error('icloud')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
@foreach(['asset_password' => 'asset_device_password', 'screen_time_passcode' => 'asset_screen_time_passcode'] as $field => $label)
<div class="col-lg-4 col-md-6 mb-4">
    <label for="{{ $field }}" class="form-label">{{ __('index.'.$label) }}</label>
    <div class="input-group">
        <input type="password" id="{{ $field }}" name="{{ $field }}" class="form-control" autocomplete="new-password"
               maxlength="{{ $field === 'asset_password' ? 1000 : 50 }}">
        <button class="btn btn-outline-secondary" type="button" data-asset-secret-toggle="{{ $field }}" aria-controls="{{ $field }}" aria-pressed="false">{{ __('index.link_show_password') }}</button>
    </div>
    @if(isset($assetDetail) && $assetDetail->getRawOriginal($field))
        <div class="form-text">{{ __('index.asset_secret_keep') }}</div>
        <label class="form-check-label mt-2"><input type="checkbox" name="clear_{{ $field }}" value="1" data-asset-secret-clear="{{ $field }}" @checked(old('clear_'.$field))> {{ __('index.asset_secret_remove') }}</label>
    @endif
    @error($field)<div class="text-danger small">{{ $message }}</div>@enderror
</div>
@endforeach
<div class="col-lg-4 col-md-6 mb-4">
    <label for="asset_set_by" class="form-label">{{ __('index.asset_set_by') }}</label>
    <select id="asset_set_by" name="set_by" class="form-select">
        <option value="">{{ __('index.asset_select_setup_person') }}</option>
        @foreach($setByOptions ?? [] as $person)
            <option value="{{ $person->id }}" @selected((string) old('set_by', $assetDetail->set_by ?? '') === (string) $person->id)>
                {{ $person->name }}{{ $person->employee_code ? ' · '.$person->employee_code : '' }}
            </option>
        @endforeach
    </select>
    @error('set_by')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
