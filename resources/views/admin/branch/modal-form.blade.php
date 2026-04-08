<div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('index.add_branch') }}</h5>
            </div>
            <div class="modal-body pb-0">
                <form class="forms-sample" id="branch_form" action="{{ route('admin.branch.store') }}" method="post">
                    @csrf
                    <div class="row">
                        <input type="hidden" class="form-control" id="company_id" readonly name="company_id" value="" autocomplete="off" placeholder="">

                        <div class="col-lg-6 mb-4">
                            <label for="name" class="form-label">{{ __('index.branch_name') }} <span style="color: red">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="" autocomplete="off" placeholder="{{ __('index.branch_name') }}">
                        </div>

                        <div class="col-lg-6 mb-4">
                            <label for="address" class="form-label">{{ __('index.address') }} <span style="color: red">*</span></label>
                            <input type="text" class="form-control" id="address" required value="" name="address" autocomplete="off" placeholder="{{ __('index.address') }}">
                        </div>

                        <div class="col-lg-6 mb-4">
                            <label for="exampleFormControlSelect1" class="form-label">{{ __('index.branch_head') }}</label>
                            <select class="form-select branch_head" id="branch_head" name="branch_head_id">
                                <option value="">{{ __('index.select_branch_head') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <label for="phone" class="form-label">{{ __('index.phone_number') }} <span style="color: red">*</span></label>
                            <input type="number" class="form-control mobile" required id="phone" name="phone" value="" autocomplete="off" placeholder="{{ __('index.phone_number') }}">
                        </div>

                        <div class="col-lg-6 mb-4">
                            <label for="branch_location_latitude" class="form-label">{{ __('index.branch_location_latitude') }} <span style="color: red">*</span></label>
                            <input type="text" class="form-control" id="branch_location_latitude" name="branch_location_latitude" value="" autocomplete="off" required placeholder="{{ __('index.enter_branch_location_latitude') }}">
                        </div>

                        <div class="col-lg-6 mb-4">
                            <label for="branch_location_longitude" class="form-label">{{ __('index.branch_location_longitude') }} <span style="color: red">*</span></label>
                            <input type="text" class="form-control" id="branch_location_longitude" name="branch_location_longitude" value="" autocomplete="off" required placeholder="{{ __('index.enter_branch_location_longitude') }}">
                        </div>

                        <div class="col-lg-6 mb-4">
                            <label for="exampleFormControlSelect1" class="form-label">{{ __('index.status') }}</label>
                            <select class="form-select" id="status" name="is_active">
                                <option value="" disabled>{{ __('index.select_status') }}</option>
                                <option value="1">{{ __('index.active') }}</option>
                                <option value="0">{{ __('index.inactive') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4 mt-lg-4">
                        <input type="hidden" name="_method" value="post" id="update">
                        <button type="submit" id="submit-btn" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ __('index.create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
