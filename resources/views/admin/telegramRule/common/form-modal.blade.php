@include('admin.section.management-ui')
<div class="modal fade management-modal" id="telegramRuleActionModal" tabindex="-1" aria-labelledby="telegramRuleActionModalTitle" aria-hidden="true" data-index-url="{{ route('admin.telegram-rules.index') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="telegramRuleActionModalTitle">{{ __('index.edit') }} Notification Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body p-0">
                <iframe class="w-100 border-0 d-block" title="Telegram Rule Action" data-telegram-rule-action-frame style="min-height: 580px;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
