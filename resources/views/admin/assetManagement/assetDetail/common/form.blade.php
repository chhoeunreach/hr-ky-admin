@if(request('asset_modal') === '1')
    <input type="hidden" name="asset_modal" value="1">
@endif

<fieldset class="management-form-section">
    <legend>{{ __('index.management_asset_identity') }}</legend>
<div class="row">
    @if(auth('admin')->check() || !auth()->user()?->branch_id)
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id" required>
            <option value="" @selected(!old('branch_id', $assetDetail->branch_id ?? null)) disabled>{{ __('index.select_branch') }}
            </option>
            @if(isset($companyDetail))
                @foreach($companyDetail->branches()->get() as $key => $branch)
                    <option value="{{$branch->id}}"
                        @selected(old('branch_id', $assetDetail->branch_id ?? null) == $branch->id)>
                        {{ucfirst($branch->name)}}</option>
                @endforeach
            @endif
        </select>
    </div>
    @else
        <input type="hidden" id="branch_id" name="branch_id" value="{{ auth()->user()?->branch_id }}">
    @endif
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="type" class="form-label">{{ __('index.type') }} <span style="color: red">*</span></label>
        <select class="form-select" id="type" name="type_id" required>
            <option value="" @selected(!old('type_id', $assetDetail->type_id ?? null)) disabled>{{ __('index.select_asset_type') }}</option>
            @foreach($assetTypes ?? collect(isset($assetDetail) && $assetDetail->type ? [$assetDetail->type] : []) as $assetType)
                <option value="{{ $assetType->id }}" @selected(old('type_id', $assetDetail->type_id ?? null) == $assetType->id)>{{ $assetType->name }}</option>
            @endforeach

        </select>
        <p class="form-text" id="assetTypeStatus" role="status" aria-live="polite"></p>
        @error('type_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.name') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control"
               id="name"
               name="name"
               value="{{ old('name', $assetDetail->name ?? '') }}"
               required autocomplete="off"
               placeholder="{{ __('index.enter_name') }}">
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="assetCode" class="form-label">{{ __('index.asset_code') }}</label>
        <input type="text" class="form-control"
               id="assetCode"
               name="asset_code"
               value="{{ isset($assetDetail) ? old('asset_code', $assetDetail->asset_code) : ($nextAssetCode ?? '') }}"
               @if(!isset($assetDetail)) readonly aria-describedby="assetCodeHelp" @endif
               autocomplete="off"
               placeholder="{{ isset($assetDetail) ? __('index.enter_asset_code') : __('index.asset_code_auto_generated') }}">
        @if(!isset($assetDetail))
            <div class="form-text" id="assetCodeHelp">{{ __('index.asset_code_auto_generated') }}</div>
        @endif
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="asset_serial_no" class="form-label">{{ __('index.asset_serial_number') }}</label>
        <input type="text" class="form-control"
               id="asset_serial_no"
               name="asset_serial_no"
               value="{{ old('asset_serial_no', $assetDetail->asset_serial_no ?? '') }}"
               autocomplete="off"
               placeholder="{{ __('index.enter_asset_serial_number') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="phone_numbers" class="form-label">{{ __('index.phone_number') }}</label>
        <textarea class="form-control" id="phone_numbers" name="phone_numbers" rows="3" maxlength="2000" aria-describedby="assetPhoneHelp">{{ old('phone_numbers', $assetDetail->phone_numbers ?? '') }}</textarea>
        <p class="form-text" id="assetPhoneHelp">{{ __('index.asset_phone_numbers_help') }}</p>
        @error('phone_numbers')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

</div>
</fieldset>
<fieldset class="management-form-section">
    <legend>{{ __('index.management_device_setup') }}</legend>
<div class="row">
    @include('admin.assetManagement.assetDetail.common.device-form')
</div>
</fieldset>
<fieldset class="management-form-section">
    <legend>{{ __('index.management_purchase_condition') }}</legend>
<div class="row">

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="purchased_date" class="form-label">{{ __('index.purchased_date') }} <span style="color: red">*</span></label>
        <input type="date"
               class="form-control"
               id="purchased_date"
               name="purchased_date"
               value="{{ old('purchased_date', $assetDetail->purchased_date ?? '') }}"
               required
               autocomplete="off" >
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="warranty_available" class="form-label">{{ __('index.warranty_available') }} <span style="color: red">*</span></label>
        <select class="form-select" id="warranty_available" name="warranty_available" required>
            <option value="" @selected(old('warranty_available', $assetDetail->warranty_available ?? null) === null) >{{ __('index.select_warranty_availability') }} </option>
            @foreach(\App\Models\Asset::BOOLEAN_DATA as $key => $value)
                <option value="{{$key}}" @selected((string) old('warranty_available', $assetDetail->warranty_available ?? '') === (string) $key)>
                    {{ __('index.' . $value) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="warranty_end_date" class="form-label">{{ __('index.warranty_end_date') }} <span id="warrantyDateRequired" class="text-danger" hidden>*</span></label>
        <input type="date"
               class="form-control"
               id="warranty_end_date"
               name="warranty_end_date"
               value="{{ old('warranty_end_date', $assetDetail->warranty_end_date ?? '') }}"
               autocomplete="off"
        >
        @error('warranty_end_date')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="is_available" class="form-label">{{ __('index.is_available_for_employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="is_available" name="is_available" required>
            <option value="" @selected(old('is_available', $assetDetail->is_available ?? null) === null) >{{ __('index.select_availability') }}  </option>
            @foreach(\App\Models\Asset::BOOLEAN_DATA as $key => $value)
                <option value="{{$key}}" @selected((string) old('is_available', $assetDetail->is_available ?? '') === (string) $key)>
                    {{ __('index.' . $value) }}
                </option>
            @endforeach
        </select>
    </div>

    @if(isset($assetDetail))
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="is_working" class="form-label">{{ __('index.is_working') }} <span class="text-danger">*</span></label>
            <select id="is_working" name="is_working" class="form-select" required>
                @foreach(\App\Models\Asset::IS_WORKING as $condition)
                    <option value="{{ $condition }}" @selected(old('is_working', $assetDetail->is_working ?? 'yes') === $condition)>{{ __('index.' . $condition) }}</option>
                @endforeach
            </select>
        </div>
    @endif
</div>
</fieldset>
<fieldset class="management-form-section">
    <legend>{{ __('index.management_files_notes') }}</legend>
<div class="row">
    <div class="col-lg-6 col-md-6 mb-4">
        <label for="images" class="form-label">{{ __('index.asset_upload_files') }} @if(!isset($assetDetail))<span style="color: red">*</span>@endif </label>
        <div class="d-flex align-items-center gap-3 border rounded p-2 position-relative">
            <input class="visually-hidden" type="file" id="images" name="images[]" multiple
                   aria-describedby="assetFileStatus assetFileHelp"
                   accept="{{ '.'.implode(',.', \App\Models\Asset::ATTACHMENT_EXTENSIONS) }}" {{ isset($assetDetail) ? '' : 'required' }}>
            <button class="btn btn-outline-secondary flex-shrink-0" type="button" id="assetFileChoose">{{ __('index.asset_file_choose') }}</button>
            <span class="small text-muted text-break" id="assetFileStatus" role="status" data-empty="{{ __('index.asset_file_none') }}">{{ __('index.asset_file_none') }}</span>
        </div>
        <div class="form-text" id="assetFileHelp">{{ __('index.asset_images_help') }}</div>
        @error('images')<div class="text-danger small">{{ $message }}</div>@enderror
        @foreach($errors->get('images.*') as $messages)
            @foreach($messages as $message)<div class="text-danger small">{{ $message }}</div>@endforeach
        @endforeach
        <div class="d-flex flex-wrap gap-3 mt-3" data-asset-gallery>
            @foreach(isset($assetDetail) ? $assetDetail->imageFiles() : [] as $file)
                <label class="border rounded p-2">
                    @if(\App\Models\Asset::isImageFile($file))
                        <a data-asset-image href="{{ asset(\App\Models\Asset::UPLOAD_PATH.$file) }}" target="_blank" rel="noopener">
                            <img src="{{ asset(\App\Models\Asset::UPLOAD_PATH.$file) }}" alt="{{ __('index.asset_image') }}" width="110" height="90" style="object-fit:contain">
                        </a>
                    @else
                        <a class="d-flex flex-column align-items-center gap-2 p-2" href="{{ asset(\App\Models\Asset::UPLOAD_PATH.$file) }}" download>
                            <i class="fa-regular fa-file-lines fa-2x" aria-hidden="true"></i>
                            <span>{{ strtoupper(pathinfo($file, PATHINFO_EXTENSION)) }} · {{ __('index.asset_file_download') }}</span>
                        </a>
                    @endif
                    <span class="d-block small mt-2"><input type="checkbox" name="remove_images[]" value="{{ $file }}" @checked(in_array($file, old('remove_images', [])))> {{ __('index.delete') }}</span>
                </label>
            @endforeach
        </div>
        <div id="asset-image-previews" data-asset-gallery class="d-flex flex-wrap gap-3 mt-3" aria-live="polite"></div>
    </div>

    <div class="col-lg-6 mb-4">
        <label for="tinymceExample" class="form-label">{{ __('index.description') }}</label>
        <textarea class="form-control" name="note" id="tinymceExample" rows="2">{{ old('note', $assetDetail->note ?? '') }}</textarea>
    </div>

</div>
</fieldset>
    @canany(['edit_assets','create_assets'])
        <div class="management-form-actions">
            @unless(request('asset_modal') === '1')<a class="btn btn-outline-secondary" href="{{ route('admin.assets.index') }}">{{ __('index.cancel') }}</a>@endunless
            <button type="submit" class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($assetDetail)? __('index.update'):__('index.create')}}
            </button>
        </div>
    @endcanany
