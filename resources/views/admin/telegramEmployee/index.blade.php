@extends('layouts.master')

@section('title', 'Telegram Employees')

@section('styles')
    <style>
        .page-wrapper .page-content { padding: 0 !important; }
        .page-content > .grid-margin, .page-wrapper .footer { display: none !important; }
        .telegram-desk { height: calc(100vh - 62px); min-height: 680px; overflow: hidden; background: #eef2f6; }
        .telegram-desk * { letter-spacing: 0; }
        .telegram-topbar { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; align-items: center; min-height: 74px; padding: 14px 24px; background: #fff; border-bottom: 1px solid #d9e1ea; }
        .telegram-topbar h4 { margin: 0; color: #111827; font-size: 21px; font-weight: 800; }
        .telegram-topbar p { margin: 3px 0 0; color: #64748b; font-weight: 600; }
        .telegram-status-strip { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }
        .telegram-metric { display: inline-flex; align-items: center; gap: 8px; min-height: 38px; padding: 0 13px; border: 1px solid #d9e1ea; border-radius: 6px; color: #334155; background: #f8fafc; font-weight: 800; }
        .telegram-metric strong { color: #0f172a; }
        button.telegram-metric { cursor: pointer; transition: border-color .15s, background .15s, box-shadow .15s; }
        button.telegram-metric:hover { border-color: #3e9fe0; background: #eef6fd; box-shadow: 0 1px 4px rgba(62,159,224,.25); }
        button.telegram-metric:focus { outline: none; box-shadow: 0 0 0 3px rgba(62,159,224,.28); }
        .telegram-desk-shell { display: grid; grid-template-columns: minmax(330px, 420px) minmax(0, 1fr); height: calc(100% - 74px); min-height: 0; }
        .telegram-sidebar { display: grid; grid-template-rows: auto 1fr auto; min-width: 0; border-right: 1px solid #d9e1ea; background: #f8fafc; }
        .telegram-search { padding: 20px 20px 14px; border-bottom: 1px solid #e3e9f0; }
        .telegram-search-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 13px; }
        .telegram-search-title h3 { margin: 0; color: #0f172a; font-size: 22px; font-weight: 800; }
        .telegram-bot-pill { display: inline-flex; align-items: center; gap: 7px; min-height: 32px; padding: 0 10px; border-radius: 999px; color: #0369a1; background: #e0f2fe; font-weight: 800; }
        .telegram-bot-pill.missing { color: #b45309; background: #fef3c7; }
        .telegram-search .form-control, .telegram-search .form-select { min-height: 44px; border-color: #cfd8e3; border-radius: 6px; background-color: #fff; font-weight: 600; }
        .telegram-filter-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }
        .telegram-list { min-height: 0; overflow: auto; padding: 10px; }
        .telegram-row { display: grid; grid-template-columns: 52px minmax(0, 1fr) auto; gap: 11px; align-items: center; width: 100%; min-height: 74px; margin-bottom: 4px; padding: 10px; border: 1px solid transparent; border-radius: 6px; color: #111827; background: transparent; text-align: left; }
        .telegram-row:hover { background: #edf6fd; }
        .telegram-row.active { border-color: #b8d7f0; background: #dceeff; }
        .telegram-avatar { position: relative; width: 48px; height: 48px; flex: 0 0 48px; }
        .telegram-avatar img, .telegram-avatar-fallback { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; background: #dbeafe; }
        .telegram-avatar-fallback { display: inline-flex; align-items: center; justify-content: center; color: #1d4ed8; font-size: 18px; font-weight: 800; }
        .telegram-presence { position: absolute; right: 0; bottom: 1px; width: 13px; height: 13px; border: 2px solid #f8fafc; border-radius: 50%; background: #22c55e; }
        .telegram-presence.off { background: #94a3b8; }
        .telegram-row-name, .telegram-row-preview, .telegram-row-meta { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .telegram-row-name { color: #0f172a; font-size: 16px; font-weight: 800; }
        .telegram-row-preview { margin-top: 2px; color: #64748b; font-size: 13px; font-weight: 700; }
        .telegram-row-meta { color: #94a3b8; font-size: 12px; text-align: right; }
        .telegram-state { display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 26px; margin-top: 6px; padding: 0 8px; border-radius: 999px; color: #fff; background: #22c55e; font-size: 12px; font-weight: 800; }
        .telegram-state.warn { background: #ef4444; }
        .telegram-sidebar-footer { padding: 12px 14px; border-top: 1px solid #e3e9f0; background: #fff; }
        .telegram-sidebar-footer .pagination { margin-bottom: 0; }
        .telegram-load-more { display: none; padding: 14px 0; text-align: center; color: #64748b; font-size: 13px; font-weight: 700; }
        .telegram-load-more .spinner { display: inline-block; width: 18px; height: 18px; margin-right: 8px; border: 2px solid #3e9fe0; border-top-color: transparent; border-radius: 50%; vertical-align: middle; animation: telegram-spin .7s linear infinite; }
        @keyframes telegram-spin { to { transform: rotate(360deg); } }
        .telegram-list-end { display: none; padding: 10px 0; text-align: center; color: #94a3b8; font-size: 12px; font-weight: 700; }
        .telegram-detail-modal-overlay { position: fixed; inset: 0; z-index: 1060; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(15,23,42,.45); opacity: 0; pointer-events: none; transition: opacity .18s; }
        .telegram-detail-modal-overlay.show { opacity: 1; pointer-events: auto; }
        .telegram-detail-modal { width: min(680px, 100%); max-height: 85vh; overflow: auto; padding: 0; border-radius: 12px; background: #fff; box-shadow: 0 18px 50px rgba(15,23,42,.28); transform: translateY(10px); transition: transform .18s; }
        .telegram-detail-modal-overlay.show .telegram-detail-modal { transform: translateY(0); }
        .telegram-detail-modal-header { display: flex; align-items: center; gap: 14px; padding: 18px 20px; color: #fff; background: #3e9fe0; border-radius: 12px 12px 0 0; }
        .telegram-detail-modal-header h5 { margin: 0; color: #fff; font-size: 18px; font-weight: 800; }
        .telegram-detail-modal-header p { margin: 3px 0 0; color: rgba(255,255,255,.9); font-size: 13px; font-weight: 700; }
        .telegram-detail-modal-close { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; margin-left: auto; border: 0; border-radius: 50%; color: #fff; background: rgba(255,255,255,.18); cursor: pointer; }
        .telegram-detail-modal-close:hover { background: rgba(255,255,255,.3); }
        .telegram-detail-modal-body { padding: 20px; }
        .telegram-detail-empty { padding: 30px; text-align: center; color: #64748b; font-weight: 700; }
        .telegram-detail-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; border-bottom: 1px solid #eef2f6; }
        .telegram-detail-row:last-child { border-bottom: 0; }
        .telegram-detail-row-main { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .telegram-detail-row .telegram-avatar { width: 40px; height: 40px; flex: 0 0 40px; }
        .telegram-detail-row .telegram-avatar img, .telegram-detail-row .telegram-avatar-fallback { width: 40px; height: 40px; }
        .telegram-detail-row-name { display: block; overflow: hidden; color: #0f172a; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
        .telegram-detail-row-sub { display: block; overflow: hidden; color: #64748b; font-size: 12px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .telegram-detail-row-badge { flex: 0 0 auto; padding: 4px 10px; border-radius: 999px; background: #e6f4ea; color: #15803d; font-size: 11px; font-weight: 800; }
        .telegram-detail-row-badge.off { background: #fee2e2; color: #b91c1c; }
        .telegram-main { display: grid; grid-template-rows: auto 1fr auto; min-width: 0; background: radial-gradient(#d9e9f7 1px, transparent 1px), linear-gradient(180deg, #f8fbff 0%, #eef6fb 100%); background-size: 22px 22px, 100% 100%; }
        .telegram-chat-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 76px; padding: 14px 22px; color: #fff; background: #3e9fe0; }
        .telegram-chat-person { display: flex; align-items: center; gap: 13px; min-width: 0; }
        .telegram-chat-person h4 { margin: 0; overflow: hidden; color: #fff; font-size: 20px; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
        .telegram-chat-person p { margin: 3px 0 0; overflow: hidden; color: rgba(255,255,255,.9); font-size: 13px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .telegram-header-actions { display: flex; gap: 8px; flex: 0 0 auto; }
        .telegram-icon-button { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border: 0; border-radius: 50%; color: #fff; background: rgba(255,255,255,.18); text-decoration: none; }
        .telegram-icon-button:hover { color: #fff; background: rgba(255,255,255,.28); text-decoration: none; }
        .telegram-chat-body { min-height: 0; overflow: auto; padding: 30px; }
        .telegram-empty { display: flex; align-items: center; justify-content: center; height: 100%; color: #64748b; font-size: 18px; font-weight: 700; text-align: center; }
        .telegram-panel { display: none; }
        .telegram-panel.active { display: block; }
        .telegram-day { width: max-content; max-width: 100%; margin: 0 auto 22px; padding: 7px 17px; border-radius: 999px; color: #475569; background: rgba(255,255,255,.86); box-shadow: 0 1px 3px rgba(15,23,42,.1); font-weight: 800; }
        .telegram-message { width: min(740px, 94%); margin-bottom: 13px; padding: 13px 15px; border-radius: 8px; box-shadow: 0 1px 2px rgba(15,23,42,.1); }
        .telegram-message.in { margin-right: auto; background: #fff; }
        .telegram-message.out { margin-left: auto; background: #dcf8c6; }
        .telegram-message h5 { margin: 0 0 8px; color: #0f172a; font-size: 15px; font-weight: 800; }
        .telegram-message p { margin: 0; color: #334155; font-weight: 600; }
        .telegram-message small { display: block; margin-top: 8px; color: #64748b; font-weight: 700; }
        .telegram-form-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto; gap: 10px; align-items: end; margin-top: 12px; }
        .telegram-connect-grid { display: grid; grid-template-columns: 180px minmax(0, 1fr); gap: 16px; align-items: start; margin-top: 12px; }
        .telegram-qr { display: flex; align-items: center; justify-content: center; min-height: 180px; border: 1px solid #d9e1ea; border-radius: 6px; background: #fff; }
        .telegram-link-box { display: flex; gap: 8px; }
        .telegram-link-box input { min-width: 0; }
        .telegram-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 10px; }
        .telegram-detail { padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; background: rgba(255,255,255,.68); }
        .telegram-detail span { display: block; color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .telegram-detail strong { display: block; overflow: hidden; color: #111827; font-size: 14px; text-overflow: ellipsis; white-space: nowrap; }
        .telegram-composer { display: none; background: #fff; border-top: 1px solid #d9e1ea; }
        .telegram-composer.active { display: block; }
        .telegram-tools { display: flex; flex-wrap: wrap; gap: 9px; padding: 12px 20px; border-bottom: 1px solid #e5eaf0; }
        .telegram-tool { display: inline-flex; align-items: center; gap: 7px; min-height: 36px; padding: 0 13px; border: 1px solid #d5dee8; border-radius: 999px; color: #334155; background: #fff; font-weight: 800; }
        .telegram-tool:hover { background: #f1f5f9; }
        .telegram-send-form { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 14px 20px; }
        .telegram-send-form .form-control { min-height: 48px; border-color: #cfd8e3; border-radius: 999px; background: #f8fafc; }
        .telegram-file-label { display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; margin: 0; border: 1px solid #d5dee8; border-radius: 50%; color: #334155; cursor: pointer; }
        .telegram-send-button { width: 50px; height: 50px; border-radius: 50%; }
        .telegram-attach-name { grid-column: 2 / 3; margin-top: -6px; color: #64748b; font-size: 12px; font-weight: 700; }
        .telegram-broadcast { padding: 0 20px 14px; }
        .telegram-broadcast details { border-top: 1px solid #e5eaf0; padding-top: 12px; }
        .telegram-broadcast summary { cursor: pointer; color: #0369a1; font-weight: 800; }
        @media (max-width: 991.98px) {
            .telegram-desk { height: auto; min-height: 100vh; overflow: visible; }
            .telegram-topbar, .telegram-desk-shell { grid-template-columns: 1fr; }
            .telegram-status-strip { justify-content: flex-start; }
            .telegram-desk-shell { height: auto; }
            .telegram-sidebar, .telegram-main { min-height: 560px; }
            .telegram-form-grid, .telegram-connect-grid, .telegram-detail-grid, .telegram-send-form { grid-template-columns: 1fr; }
            .telegram-chat-body { padding: 22px 14px; }
        }
    </style>
@endsection

@section('main-content')
    @php
        $botUsername = trim((string) ($botSettings[\App\Support\TelegramBotSettings::BOT_USERNAME] ?? ''));
        $botReady = ! empty($botSettings['bot_token_saved']) && $botUsername !== '';
        $activeEmployeeId = (int) ($activeEmployeeId ?? 0);
    @endphp

    <section class="telegram-desk">
        @include('admin.section.flash_message')

        <div class="telegram-topbar">
            <div>
                <h4>Telegram Employees</h4>
                <p>Direct Telegram chat, employee linking, and broadcast alerts</p>
            </div>
            <div class="telegram-status-strip">
                <button type="button" class="telegram-metric telegram-stats-trigger" data-type="all" title="View all employees"><i data-feather="users"></i> <strong>{{ $stats['total'] ?? 0 }}</strong> Employees</button>
                <button type="button" class="telegram-metric telegram-stats-trigger" data-type="linked" title="View linked employees"><i data-feather="check-circle"></i> <strong>{{ $stats['linked'] ?? 0 }}</strong> Linked</button>
                <button type="button" class="telegram-metric telegram-stats-trigger" data-type="unlinked" title="View not linked employees"><i data-feather="alert-circle"></i> <strong>{{ $stats['unlinked'] ?? 0 }}</strong> Not Linked</button>
                <form method="POST" action="{{ route('admin.telegram-employees.sync-starts') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-success">
                        <i data-feather="download-cloud"></i> Sync Telegram Starts
                    </button>
                </form>
                <a href="{{ route('admin.telegram-bot.index') }}" class="btn btn-outline-primary"><i data-feather="settings"></i> Bot Settings</a>
            </div>
        </div>

        <div class="telegram-desk-shell">
            <aside class="telegram-sidebar">
                <form class="telegram-search" action="{{ route('admin.telegram-employees.index') }}" method="get">
                    <div class="telegram-search-title">
                        <h3>Chats</h3>
                        <span class="telegram-bot-pill {{ $botReady ? '' : 'missing' }}">
                            <i data-feather="{{ $botReady ? 'radio' : 'alert-triangle' }}"></i>
                            {{ $botReady ? '@' . ltrim($botUsername, '@') : 'Bot setup needed' }}
                        </span>
                    </div>
                    <input type="text" placeholder="Search name, phone, code, username" name="search" value="{{ $filters['search'] }}" class="form-control">
                    <div class="telegram-filter-grid">
                        <select class="form-select" name="branch_id">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $filters['branch_id'] === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <select class="form-select" name="department_id">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ (string) $filters['department_id'] === (string) $department->id ? 'selected' : '' }}>{{ $department->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="telegram-filter-grid">
                        <select class="form-select" name="linked">
                            <option value="">All Status</option>
                            <option value="yes" {{ $filters['linked'] === 'yes' ? 'selected' : '' }}>Linked</option>
                            <option value="no" {{ $filters['linked'] === 'no' ? 'selected' : '' }}>Not Linked</option>
                        </select>
                        <button type="submit" class="btn btn-primary"><i data-feather="search"></i> Search</button>
                    </div>
                </form>

                <div class="telegram-list" id="telegramList">
                    <div id="telegramEmployeeRows">
                    @forelse($employees as $employee)
                        @php
                            $isActive = (int) $employee->id === $activeEmployeeId;
                            $avatar = $employee->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $employee->avatar) : asset('assets/images/img.png');
                            $initial = mb_substr(trim($employee->name ?: $employee->username ?: 'U'), 0, 1);
                            $preview = $employee->telegram_chat_id ? ($employee->telegram_username ? '@' . $employee->telegram_username : 'Chat ID ' . $employee->telegram_chat_id) : 'Waiting for Telegram link';
                            $headerStatus = trim(implode(' · ', array_filter([$employee->phone, $employee->employee_code ?: $employee->username, $employee->branch?->name, $employee->telegram_chat_id ? 'connected via Telegram' : 'not connected'])));
                        @endphp
                        <button type="button" class="telegram-row {{ $isActive ? 'active' : '' }}" data-chat-target="employee-chat-{{ $employee->id }}" data-employee-id="{{ $employee->id }}" data-name="{{ $employee->name }}" data-status="{{ $headerStatus }}" data-avatar="{{ $avatar }}" data-has-avatar="{{ $employee->avatar ? '1' : '0' }}" data-initial="{{ $initial }}">
                            <span class="telegram-avatar">
                                @if($employee->avatar)
                                    <img src="{{ $avatar }}" alt="{{ $employee->name }}">
                                @else
                                    <span class="telegram-avatar-fallback">{{ $initial }}</span>
                                @endif
                                <span class="telegram-presence {{ $employee->telegram_chat_id ? '' : 'off' }}"></span>
                            </span>
                            <span class="min-width-0">
                                <span class="telegram-row-name">{{ $employee->name }}</span>
                                <span class="telegram-row-preview">{{ $preview }}</span>
                            </span>
                            <span class="telegram-row-meta">
                                {{ $employee->telegram_linked_at ? optional($employee->telegram_linked_at)->format('M d') : 'New' }}
                                <span class="telegram-state {{ $employee->telegram_chat_id ? '' : 'warn' }}">{{ $employee->telegram_chat_id ? 'OK' : 'Link' }}</span>
                            </span>
                        </button>
                    @empty
                        <div class="p-4 text-center text-muted"><strong>{{ __('index.no_records_found') }}</strong></div>
                    @endforelse
                    </div>
                    <div class="telegram-load-more" id="telegramLoadMore"><span class="spinner"></span> Loading more...</div>
                    <div class="telegram-list-end" id="telegramListEnd">End of list</div>
                </div>

                <div class="telegram-sidebar-footer" id="telegramSidebarFooter" @if($employees->hasMorePages() || $employees->isEmpty()) style="display: none;"@endif>
                    {{ $employees->appends(request()->query())->links() }}
                </div>
            </aside>

            <div class="telegram-main">
                <header class="telegram-chat-header">
                    <div class="telegram-chat-person">
                        <span class="telegram-avatar">
                            <img src="" alt="" id="activeEmployeeAvatar">
                            <span class="telegram-avatar-fallback" id="activeEmployeeInitial">?</span>
                        </span>
                        <span class="min-width-0">
                            <h4 id="activeEmployeeName">Select a conversation</h4>
                            <p id="activeEmployeeStatus">Choose an employee from the list</p>
                        </span>
                    </div>
                    <div class="telegram-header-actions">
                        <a href="{{ route('admin.telegram-employees.index') }}" class="telegram-icon-button" title="Refresh"><i data-feather="refresh-cw"></i></a>
                        <a href="{{ route('admin.telegram-bot.index') }}" class="telegram-icon-button" title="Telegram Bot Settings"><i data-feather="settings"></i></a>
                    </div>
                </header>

                <main class="telegram-chat-body" id="telegramChatBody">
                    <div class="telegram-empty" id="telegramEmpty" style="{{ $activeEmployeeId ? 'display: none;' : '' }}">Select a conversation from the left to start chatting.</div>

                    @foreach($employees as $employee)
                        @php
                            $isActive = (int) $employee->id === $activeEmployeeId;
                            $connectUrl = \App\Support\TelegramBotSettings::connectUrl($employee);
                            $username = $employee->telegram_username ? '@' . $employee->telegram_username : 'Not saved';
                        @endphp
                        <div class="telegram-panel {{ $isActive ? 'active' : '' }}" id="employee-chat-{{ $employee->id }}">
                            <div class="telegram-day">Today</div>
                            <div class="telegram-message in">
                                <h5>Employee Profile</h5>
                                <p>{{ $employee->employee_code ?: $employee->username ?: 'No employee code' }}</p>
                                <div class="telegram-detail-grid">
                                    <div class="telegram-detail"><span>Phone</span><strong>{{ $employee->phone ?: 'N/A' }}</strong></div>
                                    <div class="telegram-detail"><span>Branch</span><strong>{{ $employee->branch?->name ?: 'N/A' }}</strong></div>
                                    <div class="telegram-detail"><span>Department</span><strong>{{ $employee->department?->dept_name ?: 'N/A' }}</strong></div>
                                    <div class="telegram-detail"><span>Telegram</span><strong>{{ $employee->telegram_chat_id ? 'Connected' : 'Not linked' }}</strong></div>
                                </div>
                                <small>{{ now()->format('H:i') }}</small>
                            </div>

                            <div class="telegram-message out">
                                <h5>Connection Setup</h5>
                                @if($employee->telegram_chat_id)
                                    <p>This employee has been connected and can receive Telegram messages.</p>
                                    <div class="telegram-detail-grid">
                                        <div class="telegram-detail"><span>Chat ID</span><strong>{{ $employee->telegram_chat_id }}</strong></div>
                                        <div class="telegram-detail"><span>Username</span><strong>{{ $username }}</strong></div>
                                    </div>
                                    @if($employee->telegram_linked_at)
                                        <small>Linked at {{ optional($employee->telegram_linked_at)->format('Y-m-d H:i') }}</small>
                                    @endif
                                    <form method="POST" action="{{ route('admin.telegram-employees.unlink', ['employee' => $employee->id, 'active_employee' => $employee->id]) }}" class="mt-3 telegram-unlink-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i data-feather="unlink"></i> Unlink Telegram
                                        </button>
                                    </form>
                                @else
                                    <p>Share the QR code or link with the employee. After they open Telegram, the bot will save their chat ID automatically.</p>
                                    @if($connectUrl)
                                        <div class="telegram-connect-grid">
                                            <div class="telegram-qr">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(164)->margin(1)->generate($connectUrl) !!}</div>
                                            <div>
                                                <label class="form-label" for="connect_url_{{ $employee->id }}">Employee connect link</label>
                                                <div class="telegram-link-box">
                                                    <input type="text" id="connect_url_{{ $employee->id }}" class="form-control" value="{{ $connectUrl }}" readonly>
                                                    <button type="button" class="btn btn-outline-primary telegram-copy-button" data-copy-target="connect_url_{{ $employee->id }}"><i data-feather="copy"></i></button>
                                                    <a href="{{ $connectUrl }}" target="_blank" rel="noopener" class="btn btn-primary"><i data-feather="external-link"></i></a>
                                                </div>
                                                <small>Connect link expires after {{ \App\Support\TelegramBotSettings::connectLinkValidityMinutes() }} minutes.</small>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-warning mb-0">Bot username is missing. Open Bot Settings, test the token, then refresh this page.</p>
                                    @endif
                                @endif
                            </div>

                            <div class="telegram-message in">
                                <h5>Manual Link</h5>
                                <p>Paste the employee Telegram chat ID here if you already know it.</p>
                                <form method="POST" action="{{ route('admin.telegram-employees.update', ['employee' => $employee->id, 'active_employee' => $employee->id]) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="telegram-form-grid">
                                        <div>
                                            <label class="form-label" for="telegram_chat_id_{{ $employee->id }}">Chat ID</label>
                                            <input type="text" id="telegram_chat_id_{{ $employee->id }}" name="telegram_chat_id" class="form-control" value="{{ $employee->telegram_chat_id }}" placeholder="Example: 123456789">
                                        </div>
                                        <div>
                                            <label class="form-label" for="telegram_username_{{ $employee->id }}">Username</label>
                                            <input type="text" id="telegram_username_{{ $employee->id }}" name="telegram_username" class="form-control" value="{{ $employee->telegram_username ? '@' . $employee->telegram_username : '' }}" placeholder="@username">
                                        </div>
                                        <button type="submit" class="btn btn-primary"><i data-feather="save"></i> Save</button>
                                    </div>
                                </form>
                                <small>Employees can also send <code>/link {{ $employee->employee_code ?: $employee->username ?: 'EMPLOYEE_CODE' }}</code> to the bot.</small>
                            </div>
                        </div>
                    @endforeach
                </main>

                @foreach($employees as $employee)
                    @php $isActive = (int) $employee->id === $activeEmployeeId; @endphp
                    <div class="telegram-composer {{ $isActive ? 'active' : '' }}" id="employee-composer-{{ $employee->id }}">
                        <div class="telegram-tools">
                            <button type="button" class="telegram-tool telegram-template" data-message="Hello {{ $employee->name }}, please check your latest HR notification."><i data-feather="bell"></i> Alert</button>
                            <button type="button" class="telegram-tool telegram-template" data-message="Hello {{ $employee->name }}, please send your current location."><i data-feather="map-pin"></i> Location Request</button>
                            <button type="button" class="telegram-tool telegram-template" data-message="Hello {{ $employee->name }}, your payment or salary update is ready. Please contact HR if you have questions."><i data-feather="dollar-sign"></i> Pay</button>
                            <button type="button" class="telegram-tool telegram-template" data-message="Hello {{ $employee->name }}, please send the requested document to HR."><i data-feather="file-text"></i> Document Request</button>
                        </div>
                        <form method="POST" action="{{ route('admin.telegram-employees.send', ['employee' => $employee->id, 'active_employee' => $employee->id]) }}" enctype="multipart/form-data" class="telegram-send-form">
                            @csrf
                            <label class="telegram-file-label" for="attachment_{{ $employee->id }}" title="Attach image or document"><i data-feather="paperclip"></i></label>
                            <input type="file" class="d-none telegram-file-input" id="attachment_{{ $employee->id }}" name="attachment">
                            <input type="text" name="message" class="form-control telegram-message-input" placeholder="Write a message or attach a file..." {{ $employee->telegram_chat_id ? '' : 'disabled' }}>
                            <button type="submit" class="btn btn-primary telegram-send-button" {{ $employee->telegram_chat_id ? '' : 'disabled' }} title="Send"><i data-feather="send"></i></button>
                            <div class="telegram-attach-name"></div>
                        </form>
                        <div class="telegram-broadcast">
                            <details>
                                <summary>Broadcast message to linked employees</summary>
                                <form method="POST" action="{{ route('admin.telegram-employees.broadcast') }}" class="mt-3">
                                    @csrf
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <select class="form-select" name="branch_id">
                                                <option value="">All Branches</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" name="department_id">
                                                <option value="">All Departments</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->dept_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input class="form-control" name="message" placeholder="Broadcast message" value="{{ old('message') }}" required>
                                        </div>
                                        <div class="col-md-2 d-grid">
                                            <button type="submit" class="btn btn-primary"><i data-feather="send"></i> Send</button>
                                        </div>
                                    </div>
                                </form>
                            </details>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="telegram-detail-modal-overlay" id="telegramDetailModal">
        <div class="telegram-detail-modal" role="dialog" aria-modal="true">
            <div class="telegram-detail-modal-header">
                <div class="min-width-0">
                    <h5 id="telegramDetailTitle">Employees</h5>
                    <p id="telegramDetailSubtitle"></p>
                </div>
                <button type="button" class="telegram-detail-modal-close" id="telegramDetailClose" title="Close"><i data-feather="x"></i></button>
            </div>
            <div class="telegram-detail-modal-body" id="telegramDetailBody"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            var rows = $('.telegram-row');
            var emptyState = $('#telegramEmpty');
            var activeName = $('#activeEmployeeName');
            var activeStatus = $('#activeEmployeeStatus');
            var activeInitial = $('#activeEmployeeInitial');
            var activeAvatar = $('#activeEmployeeAvatar');
            var chatBody = $('#telegramChatBody');
            var employeeRowsContainer = $('#telegramEmployeeRows');
            var loadMoreEl = $('#telegramLoadMore');
            var listEndEl = $('#telegramListEnd');
            var listEl = document.getElementById('telegramList');

            function activateRow(row) {
                var target = row.data('chat-target');
                var employeeId = row.data('employee-id');
                var name = row.data('name') || 'Employee';
                var status = row.data('status') || '';
                var avatar = row.data('avatar') || '';
                var hasAvatar = String(row.data('has-avatar')) === '1';
                var initial = row.data('initial') || String(name).trim().charAt(0) || 'U';
                var url = new URL(window.location.href);

                $('.telegram-row').removeClass('active');
                row.addClass('active');
                emptyState.hide();
                $('.telegram-panel').removeClass('active');
                $('#' + target).addClass('active');
                $('.telegram-composer').removeClass('active');
                $('#employee-composer-' + employeeId).addClass('active');
                activeName.text(name);
                activeStatus.text(status);
                activeInitial.text(initial);

                if (hasAvatar && avatar) {
                    activeAvatar.attr('src', avatar).attr('alt', name).show();
                    activeInitial.hide();
                } else {
                    activeAvatar.hide();
                    activeInitial.show();
                }

                url.searchParams.set('active_employee', employeeId);
                window.history.replaceState({}, '', url.toString());
                chatBody.scrollTop(chatBody[0].scrollHeight);

                window.dispatchEvent(new Event('telegram:feathers'));
            }

            $(document).on('click', '.telegram-row', function () {
                var $row = $(this);
                var isModalRow = $row.closest('#telegramDetailBody').length > 0;
                activateRow($row);
                if (isModalRow) { detailModal.removeClass('show'); }
            });

            $('.telegram-template').on('click', function () {
                var composer = $(this).closest('.telegram-composer');
                composer.find('.telegram-message-input').val($(this).data('message')).trigger('focus');
            });

            $('.telegram-file-input').on('change', function () {
                var fileName = this.files && this.files.length ? this.files[0].name : '';
                $(this).closest('form').find('.telegram-attach-name').text(fileName ? 'Attached: ' + fileName : '');
            });

            $('.telegram-copy-button').on('click', function () {
                var target = document.getElementById($(this).data('copy-target'));
                if (! target) { return; }
                target.select();
                target.setSelectionRange(0, target.value.length);
                navigator.clipboard?.writeText(target.value).catch(function () { document.execCommand('copy'); });
            });

            $('.telegram-unlink-form').on('submit', function (event) {
                if (!confirm('Unlink this employee from Telegram?')) {
                    event.preventDefault();
                }
            });

            /* ---------------- Infinite scroll ---------------- */
            var isLoading = false;
            var hasMore = @json($employees->hasMorePages());

            function buildRowHtml(emp) {
                var avatarHtml = emp.has_avatar
                    ? '<img src="' + emp.avatar + '" alt="' + emp.name + '">'
                    : '<span class="telegram-avatar-fallback">' + emp.initial + '</span>';
                var presence = emp.has_chat ? '' : ' off';
                var state = emp.has_chat ? 'OK' : 'Link';
                var stateClass = emp.has_chat ? '' : ' warn';

                return '<button type="button" class="telegram-row" data-chat-target="employee-chat-' + emp.id + '" data-employee-id="' + emp.id + '"' +
                    ' data-name="' + $('<div>').text(emp.name).html() + '"' +
                    ' data-status="' + $('<div>').text(emp.header_status).html() + '"' +
                    ' data-avatar="' + emp.avatar + '" data-has-avatar="' + (emp.has_avatar ? '1' : '0') + '"' +
                    ' data-initial="' + emp.initial + '">' +
                    '<span class="telegram-avatar">' + avatarHtml + '<span class="telegram-presence' + presence + '"></span></span>' +
                    '<span class="min-width-0"><span class="telegram-row-name">' + $('<div>').text(emp.name).html() + '</span>' +
                    '<span class="telegram-row-preview">' + $('<div>').text(emp.preview).html() + '</span></span>' +
                    '<span class="telegram-row-meta">' + $('<div>').text(emp.linked_at).html() +
                    '<span class="telegram-state' + stateClass + '">' + state + '</span></span>' +
                    '</button>';
            }

            function buildDetailHtml(emp) {
                var avatarHtml = emp.has_avatar
                    ? '<img src="' + emp.avatar + '" alt="' + emp.name + '">'
                    : '<span class="telegram-avatar-fallback">' + emp.initial + '</span>';
                return '<button type="button" class="telegram-detail-row telegram-row" data-chat-target="employee-chat-' + emp.id + '" data-employee-id="' + emp.id + '"' +
                    ' data-name="' + $('<div>').text(emp.name).html() + '"' +
                    ' data-status="' + $('<div>').text(emp.header_status).html() + '"' +
                    ' data-avatar="' + emp.avatar + '" data-has-avatar="' + (emp.has_avatar ? '1' : '0') + '"' +
                    ' data-initial="' + emp.initial + '" style="width:100%;border:0;border-bottom:1px solid #eef2f6;background:transparent;text-align:left;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;cursor:pointer;">' +
                    '<span class="telegram-detail-row-main"><span class="telegram-avatar">' + avatarHtml + '</span>' +
                    '<span class="min-width-0"><span class="telegram-detail-row-name">' + $('<div>').text(emp.name).html() + '</span>' +
                    '<span class="telegram-detail-row-sub">' + $('<div>').text((emp.employee_code ? emp.employee_code : '') + (emp.phone ? ' · ' + emp.phone : '')).html() + '</span></span></span>' +
                    '<span class="telegram-detail-row-badge' + (emp.has_chat ? '' : ' off') + '">' + (emp.has_chat ? 'Linked' : 'Not Linked') + '</span>' +
                    '</button>';
            }

            function loadMore() {
                if (isLoading || !hasMore) { return; }
                isLoading = true;
                loadMoreEl.show();
                listEndEl.hide();

                var params = new URLSearchParams(window.location.search);
                var nextPage = Number(params.get('page') || 1) + 1;
                params.set('page', nextPage);

                fetch('{{ route('admin.telegram-employees.index') }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function (response) {
                    if (!response.ok) { throw new Error('request failed'); }
                    return response.json();
                }).then(function (data) {
                    data.employees.forEach(function (emp) {
                        employeeRowsContainer.append(buildRowHtml(emp));
                    });
                    hasMore = data.has_more;
                    window.dispatchEvent(new Event('telegram:feathers'));
                    if (hasMore) { window.dispatchEvent(new CustomEvent('telegram:page', { detail: data.next_page })); } else {
                        listEndEl.show();
                    }
                }).catch(function () {
                    listEndEl.text('Failed to load more. Scroll again to retry.').show();
                    hasMore = true;
                }).finally(function () {
                    isLoading = false;
                    loadMoreEl.hide();
                });
            }

            function onScrollCheck() {
                if (!listEl) { return; }
                var buffer = 160;
                if (listEl.scrollTop + listEl.clientHeight >= listEl.scrollHeight - buffer) {
                    loadMore();
                }
            }

            if (listEl) {
                listEl.addEventListener('scroll', onScrollCheck);
                setInterval(onScrollCheck, 1200);
            }

            /* ---------------- Clickable stats + detail modal ---------------- */
            var detailModal = $('#telegramDetailModal');
            var detailTitle = $('#telegramDetailTitle');
            var detailSubtitle = $('#telegramDetailSubtitle');
            var detailBody = $('#telegramDetailBody');

            function openDetailModal(type) {
                detailBody.html('<div class="telegram-detail-empty">Loading...</div>');
                detailTitle.text('Employees');
                detailSubtitle.text('');
                detailModal.addClass('show');

                var params = new URLSearchParams(window.location.search);
                params.delete('linked');
                params.delete('page');

                fetch('{{ route('admin.telegram-employees.detail', '__TYPE__') }}'.replace('__TYPE__', type) + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function (response) {
                    if (!response.ok) { throw new Error('request failed'); }
                    return response.json();
                }).then(function (data) {
                    detailTitle.text(data.label);
                    detailSubtitle.text(data.count + ' employee' + (data.count === 1 ? '' : 's'));
                    if (!data.employees.length) {
                        detailBody.html('<div class="telegram-detail-empty">No employees found.</div>');
                    } else {
                        detailBody.empty();
                        data.employees.forEach(function (emp) {
                            detailBody.append(buildDetailHtml(emp));
                        });
                    }
                    window.dispatchEvent(new Event('telegram:feathers'));
                }).catch(function () {
                    detailBody.html('<div class="telegram-detail-empty">Failed to load. Please try again.</div>');
                });
            }

            $('.telegram-stats-trigger').on('click', function () {
                openDetailModal($(this).data('type'));
            });

            $('#telegramDetailClose').on('click', function () {
                detailModal.removeClass('show');
            });
            detailModal.on('click', function (event) {
                if (event.target === this) { detailModal.removeClass('show'); }
            });
            $(document).on('keydown', function (event) {
                if (event.key === 'Escape') { detailModal.removeClass('show'); }
            });

            /* Re-init feather icons on dynamically added content */
            window.addEventListener('telegram:feathers', function () {
                if (window.feather && typeof window.feather.replace === 'function') { window.feather.replace(); }
            });

            /* ---------------- Sync starts ---------------- */
            function syncTelegramStarts() {
                return fetch('{{ route('admin.telegram-employees.sync-starts') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(function (response) {
                    return response.json();
                }).then(function (data) {
                    if (data.linked && Number(data.linked) > 0) {
                        window.location.reload();
                    }
                }).catch(function () {});
            }

            var syncAttempts = 0;
            var syncTimer = setInterval(function () {
                syncAttempts++;
                syncTelegramStarts();

                if (syncAttempts >= 12) {
                    clearInterval(syncTimer);
                }
            }, 5000);

            var selected = rows.filter('.active').first();
            if (selected.length) { activateRow(selected); } else { rows.first().trigger('click'); }
        });
    </script>
@endsection
