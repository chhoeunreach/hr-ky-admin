<div class="row">
    {{-- Group / Channel Name --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="name" class="form-label"><b>Group / Channel Name</b> <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" required
               value="{{ old('name', $telegramGroup->name ?? '') }}" placeholder="ឧ. KY-HR | HQ | Management Channel">
        <div class="form-text small text-muted">ដាក់ឈ្មោះសម្គាល់ Channel ឬ Group ឱ្យងាយស្រួលចំណាំ</div>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Telegram Chat ID --}}
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="chat_ids" class="form-label"><b>Telegram Chat ID</b> <span class="text-danger">*</span></label>
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
        <div class="form-text small text-muted">បញ្ចូល Chat ID (ឧ. -1001234567890 ឬ @channel_username) រួចចុច Enter</div>
        @error('chat_ids')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Direct Telegram Link (Real link can direct) --}}
    <div class="col-lg-8 col-md-7 mb-3">
        <label for="invite_link" class="form-label">
            <b>តំណភ្ជាប់ផ្ទាល់ទៅកាន់ Chat (Direct Link / Invite Link)</b>
            <span class="text-muted small">(ណែនាំឱ្យបញ្ចូល ដើម្បីចុច Direct ចូលបាន)</span>
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
                   placeholder="ឧ. https://t.me/+AbCdEfGh ឬ https://t.me/your_channel">
            <button type="button" class="btn btn-outline-secondary" id="btnTestDirectLink" title="សាកល្បងបើកតំណភ្ជាប់នេះ">
                <i data-feather="compass" style="width: 14px; height: 14px;"></i> សាកល្បងបើក
            </button>
        </div>
        <div class="form-text small text-muted">
            បញ្ចូល Link ចូលក្រុម (Invite Link) ឬ Public Username Link ដើម្បីឱ្យ Admin អាចចុច Direct ចូលទៅកាន់ Telegram Chat ផ្ទាល់បានភ្លាមៗ។
        </div>
        @error('invite_link')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Topic ID / Thread ID --}}
    <div class="col-lg-4 col-md-5 mb-3">
        <label for="topic_id" class="form-label">
            <b>Topic ID / Thread ID</b>
            <span class="text-muted small">(ស្រេចចិត្ត)</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i data-feather="hash" style="width: 14px; height: 14px;"></i></span>
            <input type="number" class="form-control @error('topic_id') is-invalid @enderror" id="topic_id" name="topic_id" min="1"
                   value="{{ old('topic_id', $telegramGroup->topic_id ?? '') }}" placeholder="ឧ. 1, 102 (ទុកទទេបើគ្មាន)">
        </div>
        <div class="form-text small text-muted">សម្រាប់ Telegram Supergroup ដែលបើក Topics/Forum</div>
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
                    ដំណើរការ (Active)
                </label>
            </div>
        </div>
    </div>

    {{-- Description / Notes --}}
    <div class="col-lg-8 col-md-6 mb-3">
        <label for="description" class="form-label"><b>កំណត់ចំណាំ (Description / Notes)</b></label>
        <input type="text" class="form-control" id="description" name="description"
               value="{{ old('description', $telegramGroup->description ?? '') }}"
               placeholder="ឧ. ក្រុមទទួលដំណឹងវត្តមាន និងសុំច្បាប់សម្រាប់ថ្នាក់គ្រប់គ្រង">
    </div>

    {{-- Info Notice pointing to Notification Rules --}}
    <div class="col-12 mb-3">
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3 m-0" style="background-color: #f0f7ff; border-left: 4px solid #0d6efd !important;">
            <div class="d-flex align-items-center gap-2">
                <i data-feather="info" class="text-primary" style="width: 20px; height: 20px; min-width: 20px;"></i>
                <div class="small">
                    <strong>ការកំណត់ការបញ្ជូនសារ (Notification Events Matrix):</strong>
                    ប្រភេទ Events និងលក្ខខណ្ឌនៃការផ្ញើសារទាំងអស់ត្រូវបានគ្រប់គ្រងយ៉ាងពេញលេញនៅលើ
                    <strong>វិធានការបញ្ជូនសារ (Notification Rules)</strong>។
                </div>
            </div>
            <a href="{{ route('admin.telegram-rules.index') }}" class="btn btn-sm btn-outline-primary text-nowrap">
                <i data-feather="bell" style="width: 13px; height: 13px;"></i> ទៅកាន់វិធានការបញ្ជូនសារ
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
                        ផ្ញើសារសាកល្បងទៅកាន់ Chat ID (Send Test Message)
                    </h6>
                    <div class="small text-muted">
                        ចុចប៊ូតុងនេះដើម្បីផ្ញើសារសាកល្បងភ្លាមៗទៅកាន់ Chat ID ខាងលើ ដើម្បីផ្ទៀងផ្ទាត់ថា Telegram Bot អាចផ្ញើសារចូលបានដោយជោគជ័យ។
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" id="btnSendTestChat">
                    <i data-feather="send" style="width: 14px; height: 14px;"></i> ផ្ញើសារសាកល្បង
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
                placeholder: 'បញ្ចូល Chat ID រួចចុច Enter...'
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
                            title: 'គ្មាន Link សម្រាប់បើក',
                            text: 'សូមបញ្ចូល Invite Link ឬ Chat ID ជាមុនសិន!',
                            confirmButtonText: 'យល់ព្រម'
                        });
                    } else {
                        alert('សូមបញ្ចូល Invite Link ឬ Chat ID ជាមុនសិន!');
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
                            title: 'ខ្វះ Chat ID!',
                            text: 'សូមបញ្ចូល Telegram Chat ID យ៉ាងហោចណាស់មួយសិន!',
                            confirmButtonText: 'យល់ព្រម'
                        });
                    } else {
                        alert('សូមបញ្ចូល Telegram Chat ID យ៉ាងហោចណាស់មួយសិន!');
                    }
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> កំពុងផ្ញើ...');

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
                        btn.prop('disabled', false).html('<i data-feather="send" style="width: 14px; height: 14px;"></i> ផ្ញើសារសាកល្បង');
                        if (typeof feather !== 'undefined') feather.replace();

                        var msg = (res && res.message) ? res.message : 'សារសាកល្បងត្រូវបានផ្ញើដោយជោគជ័យ!';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'ជោគជ័យ!',
                                text: msg,
                                confirmButtonText: 'យល់ព្រម'
                            });
                        } else {
                            alert(msg);
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i data-feather="send" style="width: 14px; height: 14px;"></i> ផ្ញើសារសាកល្បង');
                        if (typeof feather !== 'undefined') feather.replace();

                        var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'ការផ្ញើសារសាកល្បងមិនជោគជ័យ។ សូមពិនិត្យមើល Bot Token និង Chat ID!';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'បរាជ័យ!',
                                text: err,
                                confirmButtonText: 'យល់ព្រម'
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
