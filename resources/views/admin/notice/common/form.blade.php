<div class="notice-form-intro">
    <span class="notice-form-symbol"><i data-feather="file-text"></i></span>
    <div><h5>{{ isset($noticeDetail) ? __('notice.edit_heading') : __('index.create_notice') }}</h5><p>{{ __('notice.form_help') }}</p></div>
</div>
<div class="notice-form-workspace">
    <div class="notice-form-main">
        <div class="notice-form-section">
            <div class="notice-form-section-heading"><i data-feather="edit-3"></i><h6>{{ __('notice.content_heading') }}</h6></div>
            <div class="notice-form-fields">
                <div class="notice-form-title-field">
                    <label for="title" class="form-label">{{ __('index.notice_title') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required value="{{ old('title', $noticeDetail->title ?? '') }}" autocomplete="off" placeholder="{{ __('index.notice_title') }}">
                </div>
                @if(!isset(auth()->user()->branch_id))
                    <div>
                        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span class="text-danger">*</span></label>
                        @php($selectedBranchIds = array_map('strval', (array) old('branch_ids', isset($noticeDetail) ? ($noticeDetail->branches->isNotEmpty() ? $noticeDetail->branches->modelKeys() : [$noticeDetail->branch_id]) : [])))
                        <select class="form-select" id="branch_id" name="branch_ids[]" multiple required>
                            @foreach($companyDetail?->branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" @selected(in_array((string) $branch->id, $selectedBranchIds, true))>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="notice-form-editor">
                <label for="noticeDescription" class="form-label">{{ __('index.notice_description') }} <span class="text-danger">*</span></label>
                <textarea class="form-control" name="description" id="noticeDescription" rows="16">{{ old('description', $noticeDetail->description ?? '') }}</textarea>
            </div>
        </div>
    </div>
    <aside class="notice-form-sidebar">
        <div class="notice-form-section">
            <div class="notice-form-section-heading"><i data-feather="users"></i><h6>{{ __('index.notice_receiver') }}</h6></div>
            <p class="notice-form-help">{{ __('notice.recipients_help') }}</p>
            <label for="notice" class="form-label">{{ __('notice.select_recipients') }} <span class="text-danger">*</span></label>
            <select class="form-select" id="notice" name="receiver[][notice_receiver_id]" multiple="multiple" required>
                @foreach($noticeDetail->noticeReceiversDetail ?? [] as $receiver)
                    @if($receiver->employee)<option value="{{ $receiver->notice_receiver_id }}" selected>{{ $receiver->employee->name }}</option>@endif
                @endforeach
            </select>
            <label class="notice-select-all" for="checkbox"><input type="checkbox" id="checkbox">{{ __('index.all_employees') }}</label>
        </div>
        <div class="notice-form-section">
            <div class="notice-form-section-heading"><i data-feather="settings"></i><h6>{{ __('notice.publishing_heading') }}</h6></div>
            <label for="is_active" class="form-label">{{ __('index.status') }} <span class="text-danger">*</span></label>
            <select class="form-select" id="is_active" name="is_active" required>
                <option value="1" @selected((string) old('is_active', $noticeDetail->is_active ?? 1) === '1')>{{ __('index.active') }}</option>
                <option value="0" @selected((string) old('is_active', $noticeDetail->is_active ?? 1) === '0')>{{ __('index.inactive') }}</option>
            </select>
            <div class="notice-alert-option">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="send_work_alert" name="send_work_alert" value="1" @checked(old('send_work_alert', 1))>
                    <label class="form-check-label" for="send_work_alert">{{ __('index.send_work_alert_to_mobile') }}</label>
                </div>
                <p class="notice-form-help">{{ __('index.send_work_alert_hint') }}</p>
            </div>
        </div>
    </aside>
</div>
<div class="notice-form-actions">
    <span class="notice-form-required">{{ __('notice.required_fields') }}</span>
    <div>
        @if(request('notice_modal') === '1')
            <button type="button" class="btn btn-outline-secondary" data-notice-cancel>{{ __('index.cancel') }}</button>
        @else
            <a class="btn btn-outline-secondary" href="{{ route('admin.notices.index') }}">{{ __('index.cancel') }}</a>
        @endif
        <button type="submit" class="btn btn-primary"><i data-feather="check"></i>{{ isset($noticeDetail) ? __('notice.save_changes') : __('index.save') }}</button>
    </div>
</div>
