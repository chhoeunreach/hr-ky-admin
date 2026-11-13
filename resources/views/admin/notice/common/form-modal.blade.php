<div class="modal fade" id="noticeFormModal" tabindex="-1" aria-labelledby="noticeFormModalTitle" aria-hidden="true"
     data-add-title="{{ __('notice.create_heading') }}" data-edit-title="{{ __('notice.edit_heading') }}"
     data-failed="{{ __('notice.list_failed') }}" data-saved-refresh-failed="{{ __('notice.refresh_failed') }}" data-loading="{{ __('index.loading') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="noticeFormModalTitle"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
        </div>
        <div class="modal-body p-0">
            <div class="p-4" id="noticeFormLoading" role="status">{{ __('index.loading') }}</div>
            <iframe id="noticeFormFrame" title="{{ __('index.notice') }}" style="width:100%;height:100%;border:0" hidden></iframe>
        </div>
    </div></div>
</div>
