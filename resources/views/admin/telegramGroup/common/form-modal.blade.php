@include('admin.section.management-ui')
<div class="modal fade management-modal" id="telegramGroupActionModal" tabindex="-1" aria-labelledby="telegramGroupActionModalTitle" aria-hidden="true" data-index-url="{{ route('admin.telegram-groups.index') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="telegramGroupActionModalTitle">{{ __('index.edit') }} Telegram Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body p-0">
                <iframe class="w-100 border-0 d-block" title="Telegram Group Action" data-telegram-group-action-frame style="min-height: 580px;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
