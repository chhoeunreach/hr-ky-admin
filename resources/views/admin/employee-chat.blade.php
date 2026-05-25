@extends('layouts.master')

@section('title', 'Chat Management')

@section('action', 'Chat Management')

@section('nav-head', 'Chat Management')

@section('styles')
    <style>
        .chat-layout {
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr) 320px;
            gap: 18px;
            min-height: calc(100vh - 240px);
        }

        .chat-card {
            background: #fff;
            border: 1px solid #e8eef6;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
        }

        .chat-sidebar {
            padding: 20px;
        }

        .chat-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .chat-title-row h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .chat-search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #f5f7fb;
            border-radius: 999px;
            margin-bottom: 16px;
            color: #6b7280;
        }

        .chat-search-box input {
            border: 0;
            outline: none;
            background: transparent;
            width: 100%;
            color: #111827;
        }

        .chat-staff-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: calc(100vh - 360px);
            overflow-y: auto;
            padding-right: 4px;
        }

        .chat-staff-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 18px;
            text-decoration: none;
            color: inherit;
            border: 1px solid transparent;
            transition: all .2s ease;
        }

        .chat-staff-link:hover,
        .chat-staff-link.active {
            background: #f8fbff;
            border-color: #d8e7ff;
        }

        .chat-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .chat-avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            object-fit: cover;
            background: #e5e7eb;
        }

        .chat-status-dot {
            position: absolute;
            width: 15px;
            height: 15px;
            right: 1px;
            bottom: 1px;
            border-radius: 50%;
            border: 3px solid #fff;
            background: #4ade80;
        }

        .chat-staff-meta {
            min-width: 0;
            flex: 1;
        }

        .chat-staff-name-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .chat-staff-name {
            margin: 0;
            font-weight: 700;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-staff-time {
            font-size: .78rem;
            color: #9ca3af;
            white-space: nowrap;
        }

        .chat-staff-preview {
            margin: 2px 0 0;
            color: #6b7280;
            font-size: .92rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-main {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 240px);
        }

        .chat-main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            border-bottom: 1px solid #edf2f7;
        }

        .chat-main-user {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .chat-main-user h4,
        .chat-info-card h4 {
            margin: 0;
            font-weight: 700;
        }

        .chat-main-user p,
        .chat-info-card p {
            margin: 0;
            color: #6b7280;
        }

        .chat-main-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-main-actions span {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f5f3ff;
            color: #7c3aed;
        }

        .chat-thread {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
        }

        .chat-bubble-row {
            display: flex;
            margin-bottom: 18px;
        }

        .chat-bubble-row.outgoing {
            justify-content: flex-end;
        }

        .chat-bubble {
            max-width: 68%;
            padding: 14px 16px;
            border-radius: 22px;
            background: #f3f6fb;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.05);
        }

        .chat-bubble.outgoing {
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
        }

        .chat-bubble-image {
            width: 240px;
            max-width: 100%;
            border-radius: 18px;
            display: block;
        }

        .chat-bubble audio {
            width: 260px;
            max-width: 100%;
        }

        .chat-bubble-meta {
            margin-top: 8px;
            font-size: .78rem;
            color: #94a3b8;
        }

        .chat-composer {
            border-top: 1px solid #edf2f7;
            padding: 16px;
            background: #fff;
        }

        .chat-paste-preview {
            display: none;
            position: relative;
            width: 140px;
            height: 140px;
            margin-bottom: 14px;
            border-radius: 26px;
            background: #f4f7fb;
            box-shadow: inset 0 0 0 1px #e5edf7;
            overflow: hidden;
        }

        .chat-paste-preview.is-visible {
            display: block;
        }

        .chat-paste-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .chat-paste-remove {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 0;
            background: rgba(255, 255, 255, 0.96);
            color: #111827;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.14);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .chat-composer-form {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-composer-file {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #eff6ff;
            color: #2563eb;
            cursor: pointer;
            flex-shrink: 0;
        }

        .chat-composer-file input {
            display: none;
        }

        .chat-composer-input {
            flex: 1;
            border: 0;
            background: #f5f7fb;
            border-radius: 999px;
            padding: 14px 18px;
            outline: none;
        }

        .chat-send-btn {
            border: 0;
            background: #2563eb;
            color: #fff;
            border-radius: 999px;
            padding: 12px 20px;
            font-weight: 600;
        }

        .chat-helper-text {
            margin-top: 10px;
            color: #6b7280;
            font-size: .86rem;
        }

        .chat-info {
            padding: 20px;
        }

        .chat-info-card {
            text-align: center;
            padding-bottom: 18px;
            border-bottom: 1px solid #edf2f7;
            margin-bottom: 18px;
        }

        .chat-info-card .chat-avatar {
            width: 84px;
            height: 84px;
        }

        .chat-info-section {
            padding: 14px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .chat-info-section:last-child {
            border-bottom: 0;
        }

        .chat-info-section-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-weight: 600;
            color: #111827;
        }

        .chat-empty {
            padding: 42px 24px;
            text-align: center;
            color: #6b7280;
        }

        @media (max-width: 1399px) {
            .chat-layout {
                grid-template-columns: 320px minmax(0, 1fr);
            }

            .chat-info {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 991px) {
            .chat-layout {
                grid-template-columns: 1fr;
            }

            .chat-bubble {
                max-width: 88%;
            }
        }
    </style>
@endsection

@section('main-content')
    @php
        $infoSections = [
            'Chat information',
            'Customize chat',
            'Group options',
            'Chat members',
            'Media, files and links',
            'Privacy and support',
        ];
    @endphp

    <section class="content">
        <div class="chat-layout">
            <aside class="chat-card chat-sidebar">
                <div class="chat-title-row">
                    <h3>ការជជែក</h3>
                    <div class="d-flex gap-2">
                        <span class="chat-composer-file"><i data-feather="message-circle"></i></span>
                        <span class="chat-composer-file"><i data-feather="edit-3"></i></span>
                    </div>
                </div>

                <div class="chat-search-box">
                    <i data-feather="search"></i>
                    <input id="chat-staff-search" type="text" placeholder="Search staff">
                </div>

                <div class="chat-staff-list" id="chat-staff-list">
                    @forelse($staffList as $staff)
                        @php
                            $latestMessage = $staff->chatConversation?->latestMessage;
                            $preview = $latestMessage?->message
                                ?: ($latestMessage?->message_type === \App\Models\ChatMessage::TYPE_IMAGE
                                    ? 'Sent a photo'
                                    : ($latestMessage?->message_type === \App\Models\ChatMessage::TYPE_VOICE
                                        ? 'Sent a voice message'
                                        : ($latestMessage?->message_type === \App\Models\ChatMessage::TYPE_LOCATION
                                            ? 'Sent a location'
                                            : ($staff->department?->dept_name ?? ($staff->phone ?: ($staff->username ?: 'Employee'))))));
                            $latestTime = $latestMessage?->created_at?->diffForHumans() ?? ($staff->branch?->name ?? 'Staff');
                        @endphp
                        <a href="{{ route('admin.employee-chat', ['employee_id' => $staff->id]) }}"
                           class="chat-staff-link {{ $selectedStaff && $selectedStaff->id === $staff->id ? 'active' : '' }}"
                           data-staff-name="{{ strtolower($staff->name) }}"
                           data-staff-preview="{{ strtolower($preview) }}">
                            <div class="chat-avatar-wrap">
                                <img src="{{ $staff->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $staff->avatar) : asset('assets/images/img.png') }}" alt="{{ $staff->name }}" class="chat-avatar">
                                @if((int) $staff->online_status === \App\Models\User::ONLINE)
                                    <span class="chat-status-dot"></span>
                                @endif
                            </div>
                            <div class="chat-staff-meta">
                                <div class="chat-staff-name-row">
                                    <p class="chat-staff-name">{{ $staff->name }}</p>
                                    <span class="chat-staff-time">{{ $latestTime }}</span>
                                </div>
                                <p class="chat-staff-preview">{{ $preview }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="chat-empty">No staff found.</div>
                    @endforelse
                </div>
            </aside>

            <main class="chat-card chat-main">
                @if($selectedStaff)
                    <div class="chat-main-header">
                        <div class="chat-main-user">
                            <div class="chat-avatar-wrap">
                                <img src="{{ $selectedStaff->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $selectedStaff->avatar) : asset('assets/images/img.png') }}" alt="{{ $selectedStaff->name }}" class="chat-avatar">
                                @if((int) $selectedStaff->online_status === \App\Models\User::ONLINE)
                                    <span class="chat-status-dot"></span>
                                @endif
                            </div>
                            <div>
                                <h4>{{ $selectedStaff->name }}</h4>
                                <p>{{ $selectedStaff->department?->dept_name ?? ($selectedStaff->phone ?: 'Employee') }}</p>
                            </div>
                        </div>

                        <div class="chat-main-actions">
                            <span><i data-feather="phone"></i></span>
                            <span><i data-feather="video"></i></span>
                            <span><i data-feather="info"></i></span>
                        </div>
                    </div>

                    <div class="chat-thread" id="chat-thread"
                         data-messages-url="{{ route('admin.employee-chat.messages', ['employee_id' => $selectedStaff->id]) }}">
                        @include('admin.chat.partials.messages', ['messages' => $messages])
                    </div>

                    <div class="chat-composer">
                        @can('send_employee_chat')
                            <div class="chat-paste-preview" id="chat-paste-preview">
                                <img id="chat-paste-preview-image" src="" alt="Attachment preview">
                                <button type="button" class="chat-paste-remove" id="chat-paste-preview-remove" aria-label="Remove attachment">
                                    <i data-feather="x"></i>
                                </button>
                            </div>
                            <form id="chat-send-form"
                                  class="chat-composer-form"
                                  action="{{ route('admin.employee-chat.store') }}"
                                  method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $selectedStaff->id }}">
                                <label class="chat-composer-file">
                                    <i data-feather="paperclip"></i>
                                    <input type="file" name="attachment" id="chat-attachment">
                                </label>
                                <input class="chat-composer-input" type="text" name="message" placeholder="Type your message">
                                <button class="chat-send-btn" type="submit">Send</button>
                            </form>
                            <div class="chat-helper-text" id="chat-status-text">You can send text, image, or voice files here. You can also paste a screenshot.</div>
                        @else
                            <div class="chat-helper-text" id="chat-status-text">You have view access only. Chat sending is disabled for your role.</div>
                        @endcan
                    </div>
                @else
                    <div class="chat-empty">Select a staff member to start chatting.</div>
                @endif
            </main>

            <aside class="chat-card chat-info">
                <div class="chat-info-card">
                    <img src="{{ $selectedStaff && $selectedStaff->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $selectedStaff->avatar) : asset('assets/images/img.png') }}" alt="{{ $selectedStaff?->name ?? 'Employee' }}" class="chat-avatar">
                    <h4 class="mt-3 mb-1">{{ $selectedStaff?->name ?? 'Employee' }}</h4>
                    <p>{{ $selectedStaff?->branch?->name ?? 'Staff profile' }}</p>
                </div>

                @foreach($infoSections as $section)
                    <div class="chat-info-section">
                        <div class="chat-info-section-header">
                            <span>{{ $section }}</span>
                            <i data-feather="chevron-down"></i>
                        </div>
                    </div>
                @endforeach
            </aside>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        (function () {
            const thread = document.getElementById('chat-thread');
            const form = document.getElementById('chat-send-form');
            const statusText = document.getElementById('chat-status-text');
            const searchInput = document.getElementById('chat-staff-search');
            const attachmentInput = document.getElementById('chat-attachment');
            const pastePreview = document.getElementById('chat-paste-preview');
            const pastePreviewImage = document.getElementById('chat-paste-preview-image');
            const pastePreviewRemove = document.getElementById('chat-paste-preview-remove');

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    document.querySelectorAll('#chat-staff-list .chat-staff-link').forEach((item) => {
                        const haystack = `${item.dataset.staffName} ${item.dataset.staffPreview}`;
                        item.style.display = haystack.includes(query) ? '' : 'none';
                    });
                });
            }

            const scrollThreadToBottom = () => {
                if (thread) {
                    thread.scrollTop = thread.scrollHeight;
                }
            };

            const refreshMessages = async () => {
                if (!thread) {
                    return;
                }

                try {
                    const response = await fetch(thread.dataset.messagesUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        thread.innerHTML = data.html;
                        scrollThreadToBottom();
                        if (window.feather) {
                            feather.replace();
                        }
                    }
                } catch (error) {
                    console.error('Unable to refresh chat messages.', error);
                }
            };

            const bindClipboardImagePaste = (target, fileInput, setStatus) => {
                if (!target || !fileInput) {
                    return;
                }

                target.addEventListener('paste', function (event) {
                    const items = event.clipboardData?.items || [];

                    for (const item of items) {
                        if (!item.type || !item.type.startsWith('image/')) {
                            continue;
                        }

                        const blob = item.getAsFile();
                        if (!blob) {
                            continue;
                        }

                        const extension = (blob.type.split('/')[1] || 'png').replace('jpeg', 'jpg');
                        const file = new File([blob], `pasted-screenshot-${Date.now()}.${extension}`, { type: blob.type });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        fileInput.files = dataTransfer.files;

                        if (typeof setStatus === 'function') {
                            setStatus(`Screenshot pasted: ${file.name}`);
                        }
                        event.preventDefault();
                        break;
                    }
                });
            };

            const showAttachmentPreview = (file) => {
                if (!pastePreview || !pastePreviewImage || !file || !file.type.startsWith('image/')) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    pastePreviewImage.src = event.target?.result || '';
                    pastePreview.classList.add('is-visible');
                    if (window.feather) {
                        feather.replace();
                    }
                };
                reader.readAsDataURL(file);
            };

            const clearAttachmentPreview = () => {
                if (attachmentInput) {
                    attachmentInput.value = '';
                }
                if (pastePreviewImage) {
                    pastePreviewImage.src = '';
                }
                if (pastePreview) {
                    pastePreview.classList.remove('is-visible');
                }
            };

            if (form) {
                bindClipboardImagePaste(form, attachmentInput, (message) => {
                    statusText.textContent = message;
                    const file = attachmentInput.files?.[0];
                    if (file) {
                        showAttachmentPreview(file);
                    }
                });

                attachmentInput?.addEventListener('change', function () {
                    const file = this.files?.[0];
                    if (file && file.type.startsWith('image/')) {
                        showAttachmentPreview(file);
                        statusText.textContent = `Image ready: ${file.name}`;
                    } else {
                        clearAttachmentPreview();
                    }
                });

                pastePreviewRemove?.addEventListener('click', function () {
                    clearAttachmentPreview();
                    statusText.textContent = 'Attachment removed.';
                });

                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    statusText.textContent = 'Sending message...';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: new FormData(form)
                        });
                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            statusText.textContent = data.message || 'Unable to send message.';
                            return;
                        }

                        thread.innerHTML = data.html;
                        form.reset();
                        clearAttachmentPreview();
                        statusText.textContent = 'Message sent successfully.';
                        scrollThreadToBottom();
                        if (window.feather) {
                            feather.replace();
                        }
                    } catch (error) {
                        statusText.textContent = 'Unable to send message right now.';
                    }
                });
            }

            scrollThreadToBottom();
            setInterval(refreshMessages, 5000);
        })();
    </script>
@endsection
