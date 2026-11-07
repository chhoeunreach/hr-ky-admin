<div class="row align-items-center">
    <div class="col-lg-4 col-md-6 mb-4">
        @include('admin.assetManagement.types.common.branch-select')
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.name') }}<span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name"
               required
               name="name"
               value="{{ ( isset($assetTypeDetail) ? ($assetTypeDetail->name): old('name') )}}"
               autocomplete="off"
               placeholder=""
        >
    </div>

    @canany(['create_type','edit_type'])
        <div class="col-lg-4 col-md-6 mb-4 mt-lg-4">
            <button type="submit" class="btn btn-primary"><i class="link-icon"
                                                             data-feather="{{isset($assetTypeDetail)? 'edit-2':'plus'}}"></i>
                {{isset($assetTypeDetail)? __('index.update'):__('index.create') }}
            </button>
        </div>
    @endcanany
</div>
