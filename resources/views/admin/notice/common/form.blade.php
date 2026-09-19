<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-6 col-md-6 mb-4">
        <label for="branch_id" class="form-label">@lang('index.branch') <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option  {{!isset($noticeDetail) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
            </option>
            @if(isset($companyDetail))
                @foreach($companyDetail->branches()->get() as $key => $branch)
                    <option value="{{$branch->id}}"
                        {{ (isset($noticeDetail) && ($noticeDetail->branch_id ) == $branch->id) ? 'selected': '' }}>
                        {{ucfirst($branch->name)}}</option>
                @endforeach
            @endif
        </select>
    </div>
    @endif

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="title" class="form-label">@lang('index.notice_title') <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="title" name="title" required value="{{ isset($noticeDetail) ? $noticeDetail->title : old('title') }}" autocomplete="off" placeholder="@lang('index.notice_title')">
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="description" class="form-label">@lang('index.notice_description') <span style="color: red">*</span></label>
        <textarea class="form-control"  name="description" id="tinymceExample" rows="7">{!! isset($noticeDetail) ? $noticeDetail->description : old('description') !!}</textarea>
    </div>

    <div class="col-lg-6">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <label for="employee" class="form-label">@lang('index.notice_receiver') <span style="color: red">*</span></label>
                <br>
                <select class="col-md-12 form-select" id="notice" name="receiver[][notice_receiver_id]" multiple="multiple" required>

                </select>
                <div class="select-emp"><input class="mt-3" type="checkbox" id="checkbox">@lang('index.all_employees')</div>
            </div>

            <div class="col-lg-12 mb-4">
                <label for="is_active" class="form-label">@lang('index.status') <span style="color: red">*</span></label>
                <select class="form-select" id="is_active" name="is_active" required>
                    <option value="" {{ isset($noticeDetail) || old('is_active') ? '' : 'selected' }} >@lang('index.select_status')</option>
                    <option value="1" {{ isset($noticeDetail) && ($noticeDetail->is_active || old('is_active')) == 1 ? 'selected' : '' }}>@lang('index.active')</option>
                    <option value="0" {{ isset($noticeDetail) && ($noticeDetail->is_active || old('is_active')) == 0 ? 'selected' : '' }}>@lang('index.inactive')</option>
                </select>
            </div>
            <div class="col-lg-12 mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="send_work_alert" name="send_work_alert" value="1" checked>
                    <label class="form-check-label fw-bold" for="send_work_alert">
                        <i data-feather="bell" style="width: 15px; height: 15px;" class="text-warning"></i> @lang('index.send_work_alert_to_mobile')
                    </label>
                </div>
                <small class="text-muted">@lang('index.send_work_alert_hint')</small>
            </div>
        </div>
    </div>

    <div class="col-lg-12 mb-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <i data-feather="send" style="width: 14px; height: 14px;"></i> {{ isset($noticeDetail) ? __('index.update') : __('index.send_work_alert') }}
        </button>
    </div>
</div>
