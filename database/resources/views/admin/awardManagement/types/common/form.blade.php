<div class="row align-items-center">
    <div class="col-lg-6">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option value="{{ $value->id }}" {{ ((isset($awardTypeDetail) && $awardTypeDetail->branch_id == $value->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $value->id)) ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-6">
        <label for="name" class="form-label">{{ __('index.title') }}Title<span style="color: red">*</span></label>
        <input type="text" class="form-control" id="title"
               required
               name="title"
               value="{{ ( isset($awardTypeDetail) ? ($awardTypeDetail->title): old('title') )}}"
               autocomplete="off"
               placeholder=""
        >
    </div>

    @canany(['create_type','edit_type'])
        <div class="col-lg-6 mt-4">
            <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="{{isset($awardTypeDetail)? 'edit-2':'plus'}}"></i>
                {{isset($awardTypeDetail)? __('index.update'):__('index.create')}}
            </button>
        </div>
    @endcanany
</div>
