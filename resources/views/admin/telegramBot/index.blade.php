@extends('layouts.master')

@section('title', 'Telegram Bot')

@section('styles')
    <style>
        .telegram-shell {
            display: grid;
            grid-template-columns: minmax(260px, 360px) minmax(0, 1fr);
            min-height: calc(100vh - 170px);
            background: #ffffff;
            border: 1px solid #dfe5ea;
            overflow: hidden;
        }

        .telegram-sidebar {
            border-right: 1px solid #dfe5ea;
            background: #ffffff;
            min-width: 0;
        }

        .telegram-search {
            padding: 14px;
            border-bottom: 1px solid #eef2f5;
        }

        .telegram-search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 42px;
            padding: 0 14px;
            border-radius: 24px;
            color: #7d858d;
            background: #f1f3f5;
            font-size: 15px;
        }

        .telegram-thread {
            display: flex;
            gap: 12px;
            align-items: center;
            min-height: 76px;
            padding: 10px 16px;
            color: #202124;
            text-decoration: none;
            border-bottom: 1px solid #f2f4f6;
        }

        .telegram-thread.active {
            color: #ffffff;
            background: #42a6dd;
        }

        .telegram-thread:hover {
            text-decoration: none;
        }

        .telegram-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            flex: 0 0 54px;
            border-radius: 50%;
            color: #ffffff;
            background: #2aa7df;
            font-size: 24px;
            font-weight: 700;
        }

        .telegram-avatar.success {
            background: #21c063;
        }

        .telegram-avatar.warning {
            background: #f5a623;
        }

        .telegram-thread-body {
            min-width: 0;
            flex: 1;
        }

        .telegram-thread-title,
        .telegram-thread-line {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .telegram-thread-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 16px;
            font-weight: 700;
        }

        .telegram-thread-line {
            margin-top: 4px;
            color: #85929d;
            font-size: 14px;
        }

        .telegram-thread.active .telegram-thread-line {
            color: rgba(255, 255, 255, .9);
        }

        .telegram-app {
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-width: 0;
            background:
                linear-gradient(rgba(121, 167, 106, .28), rgba(121, 167, 106, .28)),
                repeating-linear-gradient(45deg, rgba(255,255,255,.24) 0 2px, transparent 2px 18px),
                #bcd49d;
        }

        .telegram-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 72px;
            padding: 12px 22px;
            background: #ffffff;
            border-bottom: 1px solid #dfe5ea;
        }

        .telegram-title {
            min-width: 0;
        }

        .telegram-title h3 {
            margin: 0;
            color: #111827;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.25;
        }

        .telegram-title span {
            display: block;
            margin-top: 3px;
            color: #8b949e;
            font-size: 14px;
        }

        .telegram-header-actions {
            display: flex;
            gap: 10px;
            color: #8b949e;
        }

        .telegram-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 50%;
            color: inherit;
            background: transparent;
        }

        .telegram-chat {
            padding: 28px;
            overflow: auto;
        }

        .telegram-date {
            width: max-content;
            max-width: 100%;
            margin: 0 auto 22px;
            padding: 5px 14px;
            border-radius: 16px;
            color: #ffffff;
            background: rgba(74, 124, 79, .6);
            font-weight: 700;
        }

        .telegram-bubble {
            max-width: 760px;
            margin-bottom: 14px;
            padding: 14px 16px;
            border-radius: 8px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .08);
        }

        .telegram-bubble.incoming {
            margin-right: auto;
            background: #ffffff;
        }

        .telegram-bubble.outgoing {
            margin-left: auto;
            background: #e6ffd5;
        }

        .telegram-bubble-title {
            margin-bottom: 12px;
            color: #1f2937;
            font-size: 16px;
            font-weight: 700;
        }

        .telegram-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .telegram-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            max-width: 100%;
            padding: 5px 10px;
            border-radius: 16px;
            color: #51606d;
            background: rgba(255, 255, 255, .7);
            font-size: 13px;
        }

        .telegram-chip.success {
            color: #128a44;
            background: #dcfce7;
        }

        .telegram-chip.warning {
            color: #9a6700;
            background: #fff4ce;
        }

        .telegram-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .telegram-form-grid .full {
            grid-column: 1 / -1;
        }

        .telegram-bubble .form-control,
        .telegram-bubble .input-group-text {
            border-color: #d7e0e7;
        }

        .telegram-composer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            background: #ffffff;
            border-top: 1px solid #dfe5ea;
        }

        .telegram-composer form {
            margin: 0;
        }

        .telegram-webhook-url {
            min-width: min(100%, 420px);
            flex: 1;
        }

        @media (max-width: 991.98px) {
            .telegram-shell {
                grid-template-columns: 1fr;
            }

            .telegram-sidebar {
                display: none;
            }

            .telegram-chat {
                padding: 18px;
            }

            .telegram-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">@lang('index.dashboard')</a></li>
                <li class="breadcrumb-item active" aria-current="page">Telegram Bot</li>
            </ol>
            <a href="{{ route('admin.telegram-employees.index') }}" class="btn btn-outline-primary">
                <i class="link-icon" data-feather="send"></i> Employee Alerts
            </a>
        </nav>

        <div class="telegram-shell">
            <aside class="telegram-sidebar">
                <div class="telegram-search">
                    <div class="telegram-search-box">
                        <i class="link-icon" data-feather="search"></i>
                        <span>Search</span>
                    </div>
                </div>

                <a href="{{ route('admin.telegram-bot.index') }}" class="telegram-thread active">
                    <span class="telegram-avatar"><i class="link-icon" data-feather="send"></i></span>
                    <span class="telegram-thread-body">
                        <span class="telegram-thread-title">
                            <span>Telegram Bot</span>
                            <span>{{ now()->format('g:i A') }}</span>
                        </span>
                        <span class="telegram-thread-line">
                            {{ $settings['bot_token_saved'] ? 'Bot token saved' : 'No bot token saved' }}
                        </span>
                    </span>
                </a>

                <a href="{{ route('admin.telegram-groups.index') }}" class="telegram-thread">
                    <span class="telegram-avatar success"><i class="link-icon" data-feather="users"></i></span>
                    <span class="telegram-thread-body">
                        <span class="telegram-thread-title">
                            <span>Telegram Groups</span>
                            <span>Route</span>
                        </span>
                        <span class="telegram-thread-line">Chat IDs and event routing</span>
                    </span>
                </a>

                <a href="{{ route('admin.telegram-employees.index') }}" class="telegram-thread">
                    <span class="telegram-avatar warning"><i class="link-icon" data-feather="user-check"></i></span>
                    <span class="telegram-thread-body">
                        <span class="telegram-thread-title">
                            <span>Employee Alerts</span>
                            <span>DM</span>
                        </span>
                        <span class="telegram-thread-line">Employee private messages</span>
                    </span>
                </a>
            </aside>

            <div class="telegram-app">
                <header class="telegram-header">
                    <div class="telegram-title">
                        <h3>{{ $settings['telegram_bot_username'] ? '@' . $settings['telegram_bot_username'] : 'Telegram Bot' }}</h3>
                        <span>{{ $settings['bot_token_saved'] ? 'bot token saved recently' : 'waiting for bot token' }}</span>
                    </div>
                    <div class="telegram-header-actions">
                        <button type="button" class="telegram-icon-btn" title="Search">
                            <i class="link-icon" data-feather="search"></i>
                        </button>
                        <a href="{{ route('admin.telegram-groups.index') }}" class="telegram-icon-btn" title="Telegram groups">
                            <i class="link-icon" data-feather="users"></i>
                        </a>
                        <button type="button" class="telegram-icon-btn" title="More">
                            <i class="link-icon" data-feather="more-vertical"></i>
                        </button>
                    </div>
                </header>

                <main class="telegram-chat">
                    <div class="telegram-date">{{ now()->format('F j') }}</div>

                    <div class="telegram-bubble incoming">
                        <div class="telegram-bubble-title">Bot Token Status</div>
                        @if($settings['bot_token_saved'])
                            <div>Saved token: {{ $settings['bot_token_masked'] ?: 'Saved' }}</div>
                        @else
                            <div>No bot token is saved.</div>
                        @endif

                        <div class="telegram-meta">
                            @if($settings['env_bot_token_saved'])
                                <span class="telegram-chip {{ $settings['bot_token_matches_env'] ? 'success' : 'warning' }}">
                                    .env {{ $settings['env_bot_token_masked'] }}
                                </span>
                                <span class="telegram-chip {{ $settings['bot_token_matches_env'] ? 'success' : 'warning' }}">
                                    {{ $settings['bot_token_matches_env'] ? 'matches saved token' : 'different from saved token' }}
                                </span>
                            @else
                                <span class="telegram-chip">.env TELEGRAM_BOT_TOKEN empty</span>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.telegram-bot.update') }}" id="telegramSettingsForm">
                        @csrf
                        @method('PUT')

                        <div class="telegram-bubble outgoing">
                            <div class="telegram-bubble-title">Bot Credentials</div>

                            <div class="telegram-form-grid">
                                <div class="full">
                                    <label for="telegram_bot_token" class="form-label fw-bold">Bot Token</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="telegram_bot_token" name="telegram_bot_token"
                                               placeholder="{{ $settings['bot_token_saved'] ? 'Saved - leave blank to keep current token' : 'Paste bot token' }}">
                                        <button type="button" class="btn btn-outline-secondary" id="toggleBotToken" title="Show token">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label for="telegram_bot_username" class="form-label fw-bold">Bot Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input type="text" class="form-control" id="telegram_bot_username" name="telegram_bot_username"
                                               value="{{ old('telegram_bot_username', $settings['telegram_bot_username']) }}"
                                               placeholder="your_bot_username">
                                    </div>
                                </div>

                                <div>
                                    <label for="telegram_connect_link_validity_minutes" class="form-label fw-bold">Link Validity</label>
                                    <input type="number" min="1" max="10080" class="form-control" id="telegram_connect_link_validity_minutes"
                                           name="telegram_connect_link_validity_minutes"
                                           value="{{ old('telegram_connect_link_validity_minutes', $settings['telegram_connect_link_validity_minutes']) }}">
                                </div>

                                <div class="full">
                                    <label for="telegram_webhook_secret" class="form-label fw-bold">Webhook Secret</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="telegram_webhook_secret" name="telegram_webhook_secret"
                                               value="{{ old('telegram_webhook_secret', $settings['telegram_webhook_secret']) }}">
                                        <button type="button" class="btn btn-outline-secondary" id="generateWebhookSecret">Generate</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="telegram-bubble incoming">
                        <div class="telegram-bubble-title">Webhook</div>
                        <label class="form-label fw-bold" for="telegram_webhook_url">Webhook URL</label>
                        <input type="text" readonly class="form-control" id="telegram_webhook_url" value="{{ $settings['webhook_url'] }}">

                        @if($settings['telegram_webhook_registered_at'])
                            <div class="telegram-meta">
                                <span class="telegram-chip success">Last registered: {{ $settings['telegram_webhook_registered_at'] }}</span>
                            </div>
                            @if($settings['telegram_webhook_registered_url'])
                                <div class="mt-2 text-muted">{{ $settings['telegram_webhook_registered_url'] }}</div>
                            @endif
                        @else
                            <div class="telegram-meta">
                                <span class="telegram-chip">Last registered: Never</span>
                            </div>
                        @endif
                    </div>
                </main>

                <footer class="telegram-composer">
                    <button type="submit" form="telegramSettingsForm" class="btn btn-primary">
                        <i class="link-icon" data-feather="save"></i> Save Settings
                    </button>

                    <form method="POST" action="{{ route('admin.telegram-bot.import-env-token') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary" {{ $settings['env_bot_token_saved'] ? '' : 'disabled' }}>
                            Import .env Token
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.telegram-bot.test-connection') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-success" {{ $settings['bot_token_saved'] ? '' : 'disabled' }}>
                            Test Bot Token
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.telegram-bot.register-webhook') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary" {{ $settings['bot_token_saved'] ? '' : 'disabled' }}>
                            <i class="link-icon" data-feather="link"></i> Register Webhook
                        </button>
                    </form>
                </footer>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#toggleBotToken').on('click', function () {
                var input = $('#telegram_bot_token');
                input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
            });

            $('#generateWebhookSecret').on('click', function () {
                var bytes = new Uint8Array(24);
                window.crypto.getRandomValues(bytes);
                var secret = Array.from(bytes).map(function (byte) {
                    return byte.toString(16).padStart(2, '0');
                }).join('');

                $('#telegram_webhook_secret').val(secret);
            });
        });
    </script>
@endsection
