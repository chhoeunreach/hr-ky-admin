<div class="row align-items-center">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-select" id="branch_id" name="branch_id">
                <option selected disabled>{{ __('index.select_branch') }}</option>
                @foreach($branch as $value)
                    <option
                        value="{{ $value->id }}" {{ (isset($projectDetail) && $projectDetail->branch_id == $value->id) ? 'selected' : '' }}>
                        {{ ucfirst($value->name) }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
    @if(!auth('admin')->check() && auth()->check())
        <input type="hidden" id="branch_id" readonly name="branch_id" value="{{ auth()->user()->branch_id }}">
    @endif
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
