<div class="modal fade" id="telegramChatModal" tabindex="-1" aria-labelledby="telegramChatModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-2.5 px-3 bg-white border-bottom align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center text-white rounded-circle" style="width: 32px; height: 32px; background: linear-gradient(135deg, #2AABEE, #229ED9);">
                        <i data-feather="message-circle" style="width: 16px; height: 16px;"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="telegramChatModalTitle">{{ __('index.telegram_channel_or_group') }}</h6>
                        <small class="text-muted" style="font-size: 11px;">{{ __('index.telegram_chat_details') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>

            <div class="modal-body p-3">
                <!-- Group Info Card -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="fw-bold text-dark mb-0" id="telegramChatModalName">—</h5>
                                <span id="telegramChatModalStatusBadge" class="badge bg-success" style="font-size: 11px;">{{ __('index.active') }}</span>
                                <span id="telegramChatModalTopicBadge" class="badge bg-secondary d-none" style="font-size: 11px;"></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2 mt-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small fw-semibold">Chat ID:</span>
                            <code id="telegramChatModalChatId" class="px-2 py-1 bg-white border rounded font-monospace text-dark fs-6">—</code>
                            <button type="button" class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1" id="telegramChatModalCopyChatIdBtn" title="{{ __('index.copy_chat_id') }}">
                                <i data-feather="copy" style="width: 12px; height: 12px;"></i> {{ __('index.copy_chat_id') }}
                            </button>
                        </div>

                        <div id="telegramChatModalInviteWrap" class="d-flex align-items-center gap-2 flex-wrap d-none">
                            <span class="text-muted small fw-semibold">{{ __('index.invite_link') }}:</span>
                            <a id="telegramChatModalInviteLink" href="#" target="_blank" rel="noopener noreferrer" class="font-monospace small text-truncate text-primary" style="max-width: 320px;"></a>
                            <button type="button" class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1" id="telegramChatModalCopyLinkBtn" title="{{ __('index.copy_link') }}">
                                <i data-feather="link" style="width: 12px; height: 12px;"></i> {{ __('index.copy_link') }}
                            </button>
                        </div>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                        <a id="telegramChatOpenWebBtn" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm px-3 py-1.5 fw-semibold" style="background: linear-gradient(135deg, #2AABEE, #229ED9); border: none;">
                            <i data-feather="external-link" style="width: 14px; height: 14px;"></i> {{ __('index.open_in_telegram_web') }}
                        </a>

                        <a id="telegramChatOpenAppBtn" href="#" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold">
                            <i data-feather="send" style="width: 14px; height: 14px;"></i> {{ __('index.open_in_telegram_app') }}
                        </a>

                        <button type="button" id="telegramChatModalSendTestBtn" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold">
                            <i data-feather="zap" style="width: 14px; height: 14px;"></i> <span>{{ __('index.send_test') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Web Preview Container -->
                <div class="card border rounded-3 overflow-hidden shadow-sm">
                    <div class="card-header bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="small fw-semibold text-muted d-flex align-items-center gap-1">
                            <i data-feather="monitor" style="width: 14px; height: 14px;"></i> {{ __('index.telegram_web_preview') }}
                        </span>
                        <span class="small text-muted" style="font-size: 11px;">
                            {{ __('index.telegram_iframe_hint') }}
                        </span>
                    </div>
                    <div class="position-relative" style="height: 440px; background: #f8fafc;">
                        <div id="telegramChatIframeLoader" class="position-absolute top-50 start-50 translate-middle text-center text-muted" style="z-index: 2;">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                            <div class="small">{{ __('index.loading') }}</div>
                        </div>
                        <iframe id="telegramChatModalIframe" class="w-100 h-100 border-0" sandbox="allow-same-origin allow-scripts allow-forms allow-popups" allow="clipboard-write" style="position: relative; z-index: 1;"></iframe>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2 px-3 bg-light justify-content-between">
                <div id="telegramChatModalFooterNotice" class="small text-muted">
                    <i data-feather="shield" style="width: 13px; height: 13px;" class="me-1"></i> Telegram Direct Messenger
                </div>
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
