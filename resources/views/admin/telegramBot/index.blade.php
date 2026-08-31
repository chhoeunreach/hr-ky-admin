@extends('layouts.master')

@section('title', 'Telegram Bot')

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

        <div class="card mb-4 border-top border-primary border-3">
            <div class="card-body">
                <h4 class="mb-5">Bot Credentials</h4>

                <form method="POST" action="{{ route('admin.telegram-bot.update') }}">
                    @csrf
                    @method('PUT')

                    <p class="mb-2">
                        Create a bot with @BotFather on Telegram, then paste its token here. This bot is used for employee Telegram alerts and profile linking.
                    </p>

                    <div class="mb-3">
                        <label for="telegram_bot_token" class="form-label fw-bold">Bot Token</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="telegram_bot_token" name="telegram_bot_token"
                                   placeholder="{{ $settings['bot_token_saved'] ? 'Saved - leave blank to keep current token' : 'Paste bot token' }}">
                            <button type="button" class="btn btn-outline-secondary" id="toggleBotToken" title="Show token">
                                <i class="link-icon" data-feather="eye"></i>
                            </button>
                        </div>
                        @if($settings['bot_token_saved'])
                            <div class="text-success mt-2">
                                <i class="link-icon" data-feather="check-circle"></i> A bot token is currently saved.
                            </div>
                        @else
                            <div class="text-danger mt-2">No bot token is saved.</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="telegram_bot_username" class="form-label fw-bold">Bot Username</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control" id="telegram_bot_username" name="telegram_bot_username"
                                   value="{{ old('telegram_bot_username', $settings['telegram_bot_username']) }}"
                                   placeholder="your_bot_username">
                        </div>
                        <div class="form-text">Used to build the connect link employees tap to link their Telegram account.</div>
                    </div>

                    <div class="mb-3">
                        <label for="telegram_webhook_secret" class="form-label fw-bold">Webhook Secret</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="telegram_webhook_secret" name="telegram_webhook_secret"
                                   value="{{ old('telegram_webhook_secret', $settings['telegram_webhook_secret']) }}">
                            <button type="button" class="btn btn-outline-secondary" id="generateWebhookSecret">Generate</button>
                        </div>
                        <div class="form-text">Verifies that incoming webhook calls really come from Telegram. Re-register the webhook after changing this.</div>
                    </div>

                    <div class="mb-4">
                        <label for="telegram_connect_link_validity_minutes" class="form-label fw-bold">Connect Link Validity (minutes)</label>
                        <input type="number" min="1" max="10080" class="form-control" id="telegram_connect_link_validity_minutes"
                               name="telegram_connect_link_validity_minutes"
                               value="{{ old('telegram_connect_link_validity_minutes', $settings['telegram_connect_link_validity_minutes']) }}"
                               style="max-width: 220px;">
                        <div class="form-text">How long a Connect Telegram link generated on an employee page stays valid before it expires.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="link-icon" data-feather="save"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-top border-secondary border-3">
            <div class="card-body">
                <h4 class="mb-5">
                    <i class="link-icon" data-feather="link"></i> Connection & Webhook
                </h4>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label fw-bold">Test the saved bot token:</label>
                        <form method="POST" action="{{ route('admin.telegram-bot.test-connection') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary" {{ $settings['bot_token_saved'] ? '' : 'disabled' }}>
                                Test Bot Token
                            </button>
                        </form>
                        <p class="text-muted mt-2 mb-0">
                            This checks the bot token only. Telegram group tests also require the bot to be a member of each saved chat ID.
                        </p>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <label class="form-label fw-bold" for="telegram_webhook_url">Webhook URL:</label>
                        <input type="text" readonly class="form-control mb-2" id="telegram_webhook_url" value="{{ $settings['webhook_url'] }}">

                        <form method="POST" action="{{ route('admin.telegram-bot.register-webhook') }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary" {{ $settings['bot_token_saved'] ? '' : 'disabled' }}>
                                <i class="link-icon" data-feather="link"></i> Register Webhook Now
                            </button>
                        </form>

                        @if($settings['telegram_webhook_registered_at'])
                            <p class="mb-3">
                                Last registered: {{ $settings['telegram_webhook_registered_at'] }}
                                @if($settings['telegram_webhook_registered_url'])
                                    ({{ $settings['telegram_webhook_registered_url'] }})
                                @endif
                            </p>
                        @else
                            <p class="mb-3 text-muted">Last registered: Never</p>
                        @endif

                        <p class="text-muted mb-0">
                            Telegram must be able to reach this URL over public HTTPS. If you are testing locally, expose your app first before registering.
                        </p>
                    </div>
                </div>
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
