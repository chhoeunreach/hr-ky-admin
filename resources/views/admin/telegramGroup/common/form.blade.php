<div class="row">
    {{-- Group / Channel Name --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="name" class="form-label"><b>{{ __('index.channel_or_group_name') }}</b> <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" required
               value="{{ old('name', $telegramGroup->name ?? '') }}" placeholder="{{ __('index.channel_or_group_name_placeholder') }}">
        <div class="form-text small text-muted">{{ __('index.channel_or_group_name_hint') }}</div>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Telegram Chat ID --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="chat_ids" class="form-label"><b>{{ __('index.telegram_chat_id') }}</b> <span class="text-danger">*</span></label>
        @php
            $selectedChatIds = old('chat_ids', $telegramGroup->chat_ids ?? (isset($telegramGroup->chat_id) ? [$telegramGroup->chat_id] : []));
            $selectedChatIds = is_array($selectedChatIds) ? $selectedChatIds : [];
            $chatOptions = array_values(array_unique(array_merge($chatOptions ?? [], $selectedChatIds)));
        @endphp
        <select class="form-select @error('chat_ids') is-invalid @enderror" id="chat_ids" name="chat_ids[]" multiple required>
            @foreach($chatOptions as $chatId)
                <option value="{{ $chatId }}" {{ in_array($chatId, $selectedChatIds, true) ? 'selected' : '' }}>{{ $chatId }}</option>
            @endforeach
        </select>
        <div class="form-text small text-muted">{{ __('index.telegram_chat_id_hint') }}</div>
        @error('chat_ids')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Direct Telegram Link (Real link can direct) --}}
    <div class="col-lg-8 col-md-7 mb-3">
        <label for="invite_link" class="form-label">
            <b>{{ __('index.direct_telegram_link') }} ({{ __('index.invite_link') }})</b>
            <span class="text-muted small">{{ __('index.invite_link_optional_hint') }}</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-light text-primary">
                <i data-feather="external-link" style="width: 14px; height: 14px;"></i>
            </span>
            <input type="text"
                   class="form-control @error('invite_link') is-invalid @enderror"
                   id="invite_link"
                   name="invite_link"
                   value="{{ old('invite_link', $telegramGroup->invite_link ?? '') }}"
                   placeholder="{{ __('index.invite_link_placeholder') }}">
            <button type="button" class="btn btn-outline-secondary" id="btnTestDirectLink" title="{{ __('index.test_direct_link') }}">
                <i data-feather="compass" style="width: 14px; height: 14px;"></i> {{ __('index.test_direct_link') }}
            </button>
        </div>
        <div class="form-text small text-muted">
            {{ __('index.invite_link_hint') }}
        </div>
        @error('invite_link')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Topic ID / Thread ID --}}
    <div class="col-lg-4 col-md-5 mb-3">
        <label for="topic_id" class="form-label">
            <b>{{ __('index.topic_id') }}</b>
            <span class="text-muted small">({{ __('index.optional') }})</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i data-feather="hash" style="width: 14px; height: 14px;"></i></span>
            <input type="number" class="form-control @error('topic_id') is-invalid @enderror" id="topic_id" name="topic_id" min="1"
                   value="{{ old('topic_id', $telegramGroup->topic_id ?? '') }}" placeholder="{{ __('index.topic_id_placeholder') }}">
        </div>
        <div class="form-text small text-muted">{{ __('index.topic_id_hint') }}</div>
        @error('topic_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Status Toggle --}}
    <div class="col-lg-4 col-md-6 mb-3">
        <label class="form-label"><b>{{ __('index.status') }}</b></label>
        <div class="d-flex align-items-center p-2 border rounded bg-white" style="min-height: 42px;">
            <div class="form-check form-switch m-0">
                <input class="form-check-input"
                       type="checkbox"
                       id="is_active"
                       name="is_active"
                       value="1"
                       {{ old('is_active', $telegramGroup->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-success cursor-pointer" for="is_active">
                    {{ __('index.active') }}
                </label>
            </div>
        </div>
    </div>

    {{-- Description / Notes --}}
    <div class="col-lg-8 col-md-6 mb-3">
        <label for="description" class="form-label"><b>{{ __('index.description') }}</b></label>
        <input type="text" class="form-control" id="description" name="description"
               value="{{ old('description', $telegramGroup->description ?? '') }}"
               placeholder="{{ __('index.telegram_group_desc_placeholder') }}">
    </div>

    {{-- Info Notice pointing to Notification Rules --}}
    <div class="col-12 mb-3">
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3 m-0" style="background-color: #f0f7ff; border-left: 4px solid #0d6efd !important;">
            <div class="d-flex align-items-center gap-2">
                <i data-feather="info" class="text-primary" style="width: 20px; height: 20px; min-width: 20px;"></i>
                <div class="small">
                    <strong>{{ __('index.notification_events_matrix') }}:</strong>
                    {{ __('index.notification_matrix_notice') }}
                </div>
            </div>
            <a href="{{ route('admin.telegram-rules.index') }}" class="btn btn-sm btn-outline-primary text-nowrap">
                <i data-feather="bell" style="width: 13px; height: 13px;"></i> {{ __('index.go_to_notification_rules') }}
            </a>
        </div>
    </div>

    {{-- Quick Test Message Box --}}
    <div class="col-12 mb-3">
        <div class="card border shadow-none bg-light p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="mb-1 text-dark fw-bold">
                        <i data-feather="send" class="text-primary me-1" style="width: 16px; height: 16px;"></i>
                        {{ __('index.send_test_message') }}
                    </h6>
                    <div class="small text-muted">
                        {{ __('index.send_test_chat_hint') }}
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" id="btnSendTestChat">
                    <i data-feather="send" style="width: 14px; height: 14px;"></i> {{ __('index.send_test') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden Backward Compatibility Fields --}}
    <input type="hidden" name="action_key" value="{{ old('action_key', $telegramGroup->action_key ?? 'general') }}">
    <input type="hidden" name="send_for_all" value="1">
    <input type="hidden" name="send_location" value="1">
    <input type="hidden" name="send_selfie" value="1">

    {{-- Submit Buttons --}}
    <div class="col-12 mt-3 mb-3 d-flex align-items-center gap-2">
        <button type="submit" class="btn btn-primary px-4">
            <i class="link-icon" data-feather="check-circle"></i>
            {{ isset($telegramGroup) ? __('index.update') : __('index.save') }}
        </button>
        <button type="button" class="btn btn-secondary px-3" onclick="if(window.parent !== window){try{window.parent.bootstrap.Modal.getInstance(window.parent.document.getElementById('telegramGroupActionModal')).hide();}catch(e){window.location.href='{{ route('admin.telegram-groups.index') }}';}}else{window.location.href='{{ route('admin.telegram-groups.index') }}';}">
            {{ __('index.cancel') }}
        </button>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {
            // Select2 for chat_ids
            $('#chat_ids').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                placeholder: '{{ __('index.telegram_chat_id_hint') }}'
            });

            // Test Direct Link Button
            $('#btnTestDirectLink').on('click', function () {
                var url = $('#invite_link').val().trim();
                if (!url) {
                    var chatIds = $('#chat_ids').val() || [];
                    if (chatIds.length > 0) {
                        var firstId = String(chatIds[0]).trim();
                        if (firstId.startsWith('http://') || firstId.startsWith('https://')) {
                            url = firstId;
                        } else if (firstId.startsWith('@')) {
                            url = 'https://t.me/' + firstId.replace('@', '');
                        } else if (firstId.startsWith('-100')) {
                            url = 'https://t.me/c/' + firstId.substring(4) + '/1';
                        }
                    }
                }

                if (url) {
                    window.open(url, '_blank');
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: '{{ __('index.no_direct_link') }}',
                            text: '{{ __('index.invite_link_hint') }}',
                            confirmButtonText: '{{ __('index.ok') }}'
                        });
                    } else {
                        alert('{{ __('index.invite_link_hint') }}');
                    }
                }
            });

            // Quick Test Message
            $('#btnSendTestChat').on('click', function () {
                var chatIds = $('#chat_ids').val() || [];
                var topicId = $('#topic_id').val();
                if (chatIds.length === 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: '{{ __('index.telegram_chat_id') }}',
                            text: '{{ __('index.telegram_chat_id_hint') }}',
                            confirmButtonText: '{{ __('index.ok') }}'
                        });
                    } else {
                        alert('{{ __('index.telegram_chat_id_hint') }}');
                    }
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> {{ __('index.sending') }}');

                $.ajax({
                    url: "{{ route('admin.telegram-groups.send-test') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        chat_ids: chatIds,
                        topic_id: topicId,
                        name: $('#name').val() || 'Test Channel'
                    },
                    success: function (res) {
                        btn.prop('disabled', false).html('<i data-feather="send" style="width: 14px; height: 14px;"></i> {{ __('index.send_test') }}');
                        if (typeof feather !== 'undefined') feather.replace();

                        var msg = (res && res.message) ? res.message : '{{ __('index.telegram_test_sent') }}';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ __('index.success') }}',
                                text: msg,
                                confirmButtonText: '{{ __('index.ok') }}'
                            });
                        } else {
                            alert(msg);
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i data-feather="send" style="width: 14px; height: 14px;"></i> {{ __('index.send_test') }}');
                        if (typeof feather !== 'undefined') feather.replace();

                        var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : '{{ __('index.telegram_test_failed') }}';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('index.failed') }}',
                                text: err,
                                confirmButtonText: '{{ __('index.ok') }}'
                            });
                        } else {
                            alert(err);
                        }
                    }
                });
            });
        });
    </script>
@endsection
