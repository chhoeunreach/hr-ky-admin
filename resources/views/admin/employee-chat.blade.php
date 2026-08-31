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

        .chat-channel-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: max-content;
            max-width: 100%;
            min-height: 24px;
            margin-top: 6px;
            padding: 0 9px;
            border-radius: 999px;
            color: #0369a1;
            background: #e0f2fe;
            font-size: .75rem;
            font-weight: 800;
        }

        .chat-channel-badge.off {
            color: #64748b;
            background: #f1f5f9;
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

        .chat-main-actions span:not(.chat-delivery-pill) {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f5f3ff;
            color: #7c3aed;
        }

        .chat-main-actions a {
            text-decoration: none;
        }

        .chat-delivery-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: auto;
            height: 38px;
            min-height: 38px;
            padding: 0 13px;
            border-radius: 999px;
            color: #0369a1;
            background: #e0f2fe;
            font-weight: 800;
        }

        .chat-delivery-pill.off {
            color: #92400e;
            background: #fef3c7;
        }

        .chat-thread {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
        }

        .chat-bubble-row {
            display: flex;
            width: 100%;
            margin-bottom: 18px;
        }

        .chat-bubble-row.outgoing {
            justify-content: flex-end;
        }

        .chat-bubble {
            display: inline-block;
            width: fit-content;
            max-width: 68%;
            min-width: 0;
            padding: 14px 16px;
            border-radius: 22px;
            background: #f3f6fb;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.05);
            overflow-wrap: anywhere;
            word-break: break-word;
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

        .chat-bubble > div,
        .chat-bubble > a,
        .chat-bubble > audio,
        .chat-bubble > img {
            max-width: 100%;
        }

        .chat-bubble-meta {
            margin-top: 8px;
            font-size: .78rem;
            color: #94a3b8;
        }

        .chat-bubble-meta .chat-telegram-badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 999px;
            color: #0369a1;
            background: #e0f2fe;
            font-weight: 800;
            font-size: .7rem;
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

        .chat-helper-text.success {
            color: #15803d;
        }

        .chat-helper-text.warning {
            color: #b45309;
        }

        .chat-helper-text.danger {
            color: #b91c1c;
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

        .chat-filters {
            margin-bottom: 16px;
        }

        .chat-filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        .chat-filter-grid:last-child {
            margin-bottom: 0;
        }

        .chat-filter-grid .form-select,
        .chat-filter-grid .btn {
            min-height: 38px;
            font-size: .85rem;
            border-radius: 10px;
        }

        .chat-telegram-overlay {
            position: fixed;
            inset: 0;
            z-index: 1080;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.5);
            opacity: 0;
            pointer-events: none;
            transition: opacity .18s ease;
        }

        .chat-telegram-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        .chat-telegram-modal {
            position: relative;
            width: min(520px, 100%);
            max-height: 90vh;
            overflow: auto;
            padding: 28px 26px;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.3);
            transform: translateY(12px) scale(.98);
            transition: transform .18s ease;
        }

        .chat-telegram-overlay.show .chat-telegram-modal {
            transform: translateY(0) scale(1);
        }

        .chat-telegram-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 50%;
            color: #6b7280;
            background: #f1f5f9;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .chat-telegram-close:hover {
            background: #e2e8f0;
        }

        .chat-telegram-head {
            text-align: center;
            margin-bottom: 18px;
        }

        .chat-telegram-head h4 {
            margin: 0 0 6px;
            font-weight: 700;
        }

        .chat-telegram-head p {
            margin: 0;
            color: #6b7280;
            font-size: .9rem;
        }

        .chat-telegram-qr {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #fff;
            width: max-content;
        }

        .chat-telegram-qr svg,
        .chat-telegram-qr img {
            display: block;
        }

        .chat-telegram-link-row {
            display: flex;
            gap: 8px;
            margin-top: 14px;
        }

        .chat-telegram-link-row input {
            min-width: 0;
            flex: 1;
            font-size: .85rem;
            border-color: #e2e8f0;
            border-radius: 10px;
        }

        .chat-telegram-note {
            margin-top: 12px;
            color: #6b7280;
            font-size: .83rem;
            text-align: center;
        }

        .chat-telegram-bot-missing {
            padding: 24px;
            text-align: center;
            color: #b45309;
            background: #fef3c7;
            border-radius: 12px;
            font-weight: 600;
        }

        .chat-telegram-linked-note {
            margin-top: 14px;
            padding: 10px 12px;
            color: #15803d;
            background: #e6f4ea;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            font-size: .85rem;
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
        $botUsername = trim((string) ($botSettings[\App\Support\TelegramBotSettings::BOT_USERNAME] ?? ''));
        $botReady = ! empty($botSettings['bot_token_saved']) && $botUsername !== '';
        $connectUrl = $selectedStaff ? \App\Support\TelegramBotSettings::connectUrl($selectedStaff) : null;
        $connectValidity = \App\Support\TelegramBotSettings::connectLinkValidityMinutes();
        $connectQrHtml = $connectUrl ? \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->margin(1)->generate($connectUrl) : '';
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
                    <input id="chat-staff-search" type="text" placeholder="Search staff" value="{{ $filters['search'] }}">
                </div>

                <div class="chat-filters">
                    <div class="chat-filter-grid">
                        <select id="chat-filter-branch" class="form-select form-select-sm">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $filters['branch_id'] === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <select id="chat-filter-department" class="form-select form-select-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ (string) $filters['department_id'] === (string) $department->id ? 'selected' : '' }}>{{ $department->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="chat-filter-grid">
                        <select id="chat-filter-linked" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="yes" {{ $filters['linked'] === 'yes' ? 'selected' : '' }}>Linked</option>
                            <option value="no" {{ $filters['linked'] === 'no' ? 'selected' : '' }}>Not Linked</option>
                        </select>
                        <button type="button" id="chat-filter-reset" class="btn btn-outline-secondary btn-sm"><i data-feather="rotate-ccw"></i> Reset</button>
                    </div>
                </div>

                <div class="chat-staff-list" id="chat-staff-list">
                    @include('admin.chat.partials.staff', ['staffList' => $staffList, 'selectedStaff' => $selectedStaff])
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
                            <span class="chat-delivery-pill {{ $selectedStaff->telegram_chat_id ? '' : 'off' }}">
                                <i data-feather="{{ $selectedStaff->telegram_chat_id ? 'send' : 'wifi-off' }}"></i>
                                {{ $selectedStaff->telegram_chat_id ? 'System + Telegram' : 'System only' }}
                            </span>
                            <a href="javascript:void(0)" class="chat-delivery-pill" id="chat-telegram-connect" title="Show Telegram connect QR / link">
                                <i data-feather="send"></i>
                                Telegram
                            </a>
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
                                <input class="chat-composer-input" type="text" name="message" placeholder="{{ __('index.type_your_message') }}">
                                <button class="chat-send-btn" type="submit">{{ __('index.send') }}</button>
                            </form>
                            <div class="chat-helper-text" id="chat-status-text">
                                {{ $selectedStaff->telegram_chat_id ? 'Messages send to system chat and Telegram.' : 'Messages send to system chat only until Telegram is connected.' }}
                            </div>
                        @else
                            <div class="chat-helper-text" id="chat-status-text">{{ __('index.chat_read_only') }}</div>
                        @endcan
                    </div>
                @else
                    <div class="chat-empty">{{ __('index.select_staff_start_chatting') }}</div>
                @endif
            </main>

            <aside class="chat-card chat-info">
                <div class="chat-info-card">
                    <img src="{{ $selectedStaff && $selectedStaff->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $selectedStaff->avatar) : asset('assets/images/img.png') }}" alt="{{ $selectedStaff?->name ?? 'Employee' }}" class="chat-avatar">
                    <h4 class="mt-3 mb-1">{{ $selectedStaff?->name ?? 'Employee' }}</h4>
                    <p>{{ $selectedStaff?->branch?->name ?? 'Staff profile' }}</p>
                    @if($selectedStaff)
                        <div class="mt-3">
                            <span class="chat-channel-badge {{ $selectedStaff->telegram_chat_id ? '' : 'off' }}">
                                <i data-feather="{{ $selectedStaff->telegram_chat_id ? 'check-circle' : 'alert-circle' }}"></i>
                                {{ $selectedStaff->telegram_chat_id ? 'Telegram connected' : 'Telegram not connected' }}
                            </span>
                        </div>
                        @if($selectedStaff->telegram_chat_id)
                            <p class="mt-2 mb-0">{{ $selectedStaff->telegram_username ? '@' . $selectedStaff->telegram_username : $selectedStaff->telegram_chat_id }}</p>
                        @endif
                    @endif
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

    <div class="chat-telegram-overlay" id="chat-telegram-overlay">
        <div class="chat-telegram-modal" role="dialog" aria-modal="true">
            <button type="button" class="chat-telegram-close" id="chat-telegram-close" aria-label="Close"><i data-feather="x"></i></button>
            <div id="chat-telegram-modal-body">
                @if($botReady && $connectUrl)
                    <div class="chat-telegram-head">
                        <h4>Connect to Telegram</h4>
                        <p>Share this QR code or link with the employee. After they open Telegram and press Start, the bot will save their chat ID automatically.</p>
                    </div>
                    <div class="chat-telegram-qr">
                        {!! $connectQrHtml !!}
                    </div>
                    <div class="chat-telegram-link-row">
                        <input type="text" id="chat-telegram-connect-link" class="form-control" value="{{ $connectUrl }}" readonly>
                        <button type="button" class="btn btn-primary" id="chat-telegram-copy-btn"><i data-feather="copy"></i> Copy</button>
                        <a href="{{ $connectUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i data-feather="external-link"></i></a>
                    </div>
                    @if($selectedStaff && $selectedStaff->telegram_chat_id)
                        <p class="chat-telegram-linked-note">This employee is already connected to Telegram.</p>
                    @endif
                    <p class="chat-telegram-note">Connect link expires after {{ $connectValidity }} minutes.</p>
                @elseif(!$botReady)
                    <div class="chat-telegram-head">
                        <h4>Telegram connection</h4>
                        <p>Configure the Telegram bot first.</p>
                    </div>
                    <div class="chat-telegram-bot-missing">The Telegram bot is not set up yet. Open the bot settings, test the token and save the bot username, then come back here to get the employee connect link.</div>
                @else
                    <div class="chat-telegram-head">
                        <h4>Telegram connection</h4>
                        <p>Please select an employee first.</p>
                    </div>
                    <div class="chat-telegram-bot-missing">No employee selected.</div>
                @endif
            </div>
        </div>
    </div>
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

            const staffList = document.getElementById('chat-staff-list');
            const filterBranch = document.getElementById('chat-filter-branch');
            const filterDepartment = document.getElementById('chat-filter-department');
            const filterLinked = document.getElementById('chat-filter-linked');
            const filterReset = document.getElementById('chat-filter-reset');

            let selectedEmployeeId = {{ $selectedStaff?->id ?? 'null' }};

            const filterUrl = '{{ route('admin.employee-chat.staff') }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            let filterRequest = 0;
            const applyFilters = () => {
                if (!staffList) {
                    return;
                }
                const current = ++filterRequest;
                const params = new URLSearchParams();
                if (searchInput.value.trim()) {
                    params.set('search', searchInput.value.trim());
                }
                if (filterBranch.value) {
                    params.set('branch_id', filterBranch.value);
                }
                if (filterDepartment.value) {
                    params.set('department_id', filterDepartment.value);
                }
                if (filterLinked.value) {
                    params.set('linked', filterLinked.value);
                }
                if (selectedEmployeeId) {
                    params.set('employee_id', selectedEmployeeId);
                }

                staffList.style.opacity = '0.5';
                fetch(filterUrl + '?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then((response) => response.json()).then((data) => {
                    if (current !== filterRequest) {
                        return;
                    }
                    if (data.success) {
                        staffList.innerHTML = data.html;
                    }
                    staffList.style.opacity = '1';
                    if (window.feather) {
                        feather.replace();
                    }
                }).catch(() => {
                    if (current === filterRequest) {
                        staffList.style.opacity = '1';
                    }
                });
            };

            if (searchInput) {
                let searchTimer = null;
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(applyFilters, 300);
                });
            }

            if (filterBranch) {
                filterBranch.addEventListener('change', applyFilters);
            }
            if (filterDepartment) {
                filterDepartment.addEventListener('change', applyFilters);
            }
            if (filterLinked) {
                filterLinked.addEventListener('change', applyFilters);
            }
            if (filterReset) {
                filterReset.addEventListener('click', function () {
                    if (searchInput) { searchInput.value = ''; }
                    if (filterBranch) { filterBranch.value = ''; }
                    if (filterDepartment) { filterDepartment.value = ''; }
                    if (filterLinked) { filterLinked.value = ''; }
                    applyFilters();
                    if (window.feather) { feather.replace(); }
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
                    statusText.classList.remove('success', 'warning', 'danger');
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
                            statusText.classList.add('danger');
                            statusText.textContent = data.message || 'Unable to send message.';
                            return;
                        }

                        thread.innerHTML = data.html;
                        form.reset();
                        clearAttachmentPreview();
                        statusText.classList.add(data.telegram_status === 'failed' ? 'warning' : 'success');
                        statusText.textContent = data.telegram_message || 'Message sent successfully.';
                        scrollThreadToBottom();
                        if (window.feather) {
                            feather.replace();
                        }
                    } catch (error) {
                        statusText.classList.add('danger');
                        statusText.textContent = 'Unable to send message right now.';
                    }
                });
            }

            scrollThreadToBottom();
            setInterval(refreshMessages, 5000);

            /* ---------------- Telegram connect modal ---------------- */
            const telegramOverlay = document.getElementById('chat-telegram-overlay');
            const telegramConnect = document.getElementById('chat-telegram-connect');

            const closeTelegramModal = () => {
                if (telegramOverlay) {
                    telegramOverlay.classList.remove('show');
                    telegramOverlay.style.pointerEvents = 'none';
                }
            };

            const showTelegramModal = () => {
                if (!telegramOverlay) {
                    return;
                }
                telegramOverlay.classList.add('show');
                telegramOverlay.style.pointerEvents = 'auto';
                if (window.feather) {
                    feather.replace();
                }
            };

            if (telegramConnect) {
                telegramConnect.addEventListener('click', showTelegramModal);
            }

            const copyBtn = document.getElementById('chat-telegram-copy-btn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    const inputEl = document.getElementById('chat-telegram-connect-link');
                    if (!inputEl) {
                        return;
                    }
                    inputEl.select();
                    inputEl.setSelectionRange(0, inputEl.value.length);
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(inputEl.value).catch(() => {
                            document.execCommand('copy');
                        });
                    } else {
                        document.execCommand('copy');
                    }
                    const note = document.querySelector('.chat-telegram-note');
                    if (note) {
                        note.textContent = 'Link copied to clipboard!';
                    }
                });
            }

            document.getElementById('chat-telegram-close')?.addEventListener('click', closeTelegramModal);

            if (telegramOverlay) {
                telegramOverlay.addEventListener('click', function (event) {
                    if (event.target === telegramOverlay) {
                        closeTelegramModal();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeTelegramModal();
                }
            });
        })();
    </script>
@endsection
