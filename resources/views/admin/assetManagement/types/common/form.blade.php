<div class="row align-items-start">
    <div class="col-lg-4 col-md-6 mb-4">
        @include('admin.assetManagement.types.common.branch-select')
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.name') }}<span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name"
               required
               name="name"
               value="{{ old('name', $assetTypeDetail->name ?? '') }}"
               autocomplete="off"
               placeholder=""
        >
    </div>

    @canany(['create_type','edit_type'])
        <div class="col-12 management-form-actions">
            <a class="btn btn-outline-secondary" href="{{ route('admin.asset-types.index') }}">{{ __('index.cancel') }}</a>
            <button type="submit" class="btn btn-primary"><i class="link-icon"
                                                             data-feather="{{isset($assetTypeDetail)? 'edit-2':'plus'}}"></i>
                {{isset($assetTypeDetail)? __('index.update'):__('index.create') }}
            </button>
        </div>
    @endcanany
</div>
