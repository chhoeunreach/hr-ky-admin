@include('admin.section.management-ui')
<div class="modal fade management-modal" id="telegramRuleActionModal" tabindex="-1" aria-labelledby="telegramRuleActionModalTitle" aria-hidden="true" data-index-url="{{ route('admin.telegram-rules.index') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-2.5 px-3 bg-white border-bottom">
                <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center gap-2 mb-0" id="telegramRuleActionModalTitle">
                    <i data-feather="bell" class="text-primary" style="width: 18px; height: 18px;"></i>
                    <span>{{ __('index.telegram_rules') ?? 'វិធានការបញ្ជូនសារ (Notification Rule)' }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body p-0 flex-grow-1" style="overflow: hidden;">
                <iframe class="w-100 h-100 border-0 d-block" title="Telegram Rule Action" data-telegram-rule-action-frame></iframe>
            </div>
        </div>
    </div>
</div>
